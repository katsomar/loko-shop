<?php
// --- AJAX handler at the very top ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fetch_supplier_products'])) {
    include '../includes/db.php';
    header('Content-Type: application/json');
    $supplier_id = intval($_POST['supplier_id'] ?? 0);
    $products = [];
    if ($supplier_id > 0) {
        $res = $conn->query("SELECT id, product_name, unit_price FROM supplier_products WHERE supplier_id = $supplier_id ORDER BY product_name ASC");
        while ($row = $res->fetch_assoc()) $products[] = $row;
    }
    echo json_encode(['success'=>true, 'products'=>$products]);
    exit;
}

// --- BEGIN: Handle form submissions and redirects BEFORE any output ---
include '../includes/db.php';

$message = "";
$amount = 0;

// Fetch branches and suppliers for dropdowns
$branches_res = $conn->query("SELECT id, name FROM branch ORDER BY name ASC");
$branches = $branches_res ? $branches_res->fetch_all(MYSQLI_ASSOC) : [];
$suppliers_res = $conn->query("SELECT id, name FROM suppliers ORDER BY name ASC");
$suppliers = $suppliers_res ? $suppliers_res->fetch_all(MYSQLI_ASSOC) : [];

// --- Fetch products for lookup (for table display) ---
$products_lookup = [];
$products_res = $conn->query("SELECT id, product_name FROM supplier_products");
while ($row = $products_res->fetch_assoc()) {
    $products_lookup[$row['id']] = $row['product_name'];
}

// Handle form submission (single product, only if cart_json is not set or empty)
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    !isset($_POST['fetch_supplier_products']) &&
    (empty($_POST['cart_json']) || $_POST['cart_json'] === '[]')
) {
    // NEW: Check if this is from "Other Expense" form
    $is_other_expense_form = isset($_POST['is_other_expense']) && $_POST['is_other_expense'] === '1';
    
    $category   = mysqli_real_escape_string($conn, $_POST['category']);
    $branch_id  = mysqli_real_escape_string($conn, $_POST['branch_id']);
    $date       = $_POST['date'];
    $spent_by   = mysqli_real_escape_string($conn, $_POST['spent_by']);
    $quantity   = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
    $unit_price = isset($_POST['unit_price']) ? floatval($_POST['unit_price']) : 0;
    $amount     = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
    $amount_paid = floatval($_POST['amount_paid'] ?? 0);
    
    if ($is_other_expense_form) {
        // OTHER EXPENSE FORM: type goes to product column, supplier is "-"
        $type = mysqli_real_escape_string($conn, $_POST['type']);
        $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
        
        if (!empty($category) && !empty($type) && !empty($branch_id) && $quantity > 0 && $unit_price > 0 && !empty($date)) {
            $sql = "INSERT INTO expenses (category, `branch-id`, supplier, product_name, quantity, unit_price, amount, description, date, `spent-by`) 
                    VALUES ('$category', '$branch_id', '-', '$type', $quantity, $unit_price, $amount, '$description', '$date', '$spent_by')";
            
            if ($conn->query($sql)) {
                $message = "Other expense added successfully.";
                header("Location: expense.php?added=1");
                exit;
            } else {
                $message = "Error: " . $conn->error;
            }
        } else {
            $message = "Please fill in all required fields.";
        }
    } else {
        // ORIGINAL EXPENSE FORM LOGIC
        $supplier_id = mysqli_real_escape_string($conn, $_POST['supplier_id']);
        $product    = mysqli_real_escape_string($conn, $_POST['product']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);

        // NEW: Handle "Other" supplier option
        $is_other_supplier = ($supplier_id === 'other');
        $business_name = '';
        $product_name = '';
        
        if ($is_other_supplier) {
            $business_name = mysqli_real_escape_string($conn, $_POST['business_name'] ?? '');
            $product_name = mysqli_real_escape_string($conn, $_POST['product_manual'] ?? '');
            $supplier_id = 0;
        }

        if (!empty($category) && !empty($branch_id) && $quantity > 0 && $unit_price > 0 && !empty($date)) {
            if ($is_other_supplier) {
                $sql = "INSERT INTO expenses (category, `branch-id`, supplier_id, supplier, product, product_name, quantity, unit_price, amount, description, date, `spent-by`) 
                        VALUES ('$category', '$branch_id', NULL, '$business_name', NULL, '$product_name', $quantity, $unit_price, $amount, '$description', '$date', '$spent_by')";
            } else {
                $sql = "INSERT INTO expenses (category, `branch-id`, supplier_id, product, product_name, quantity, unit_price, amount, description, date, `spent-by`) 
                        VALUES ('$category', '$branch_id', '$supplier_id', '$product', NULL, $quantity, $unit_price, $amount, '$description', '$date', '$spent_by')";
            }
            
            if ($conn->query($sql)) {
                if (!$is_other_supplier) {
                    // Insert into supplier_transactions
                    $products_res = $conn->query("SELECT product_name FROM supplier_products WHERE id = $product");
                    $product_name = '';
                    if ($products_res && $row = $products_res->fetch_assoc()) {
                        $product_name = $row['product_name'];
                    }
                    $branch_name = '';
                    $branch_res = $conn->query("SELECT name FROM branch WHERE id = $branch_id");
                    if ($branch_res && $brow = $branch_res->fetch_assoc()) {
                        $branch_name = $brow['name'];
                    }
                    $balance = $amount - $amount_paid;
                    $now = date('Y-m-d H:i:s');
                    $payment_method = '';
                    $stmt = $conn->prepare("INSERT INTO supplier_transactions (supplier_id, date_time, branch, products_supplied, quantity, unit_price, amount, payment_method, amount_paid, balance) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param(
                        "isssiddsdd",
                        $supplier_id,
                        $now,
                        $branch_name,
                        $product_name,
                        $quantity,
                        $unit_price,
                        $amount,
                        $payment_method,
                        $amount_paid,
                        $balance
                    );
                    $stmt->execute();
                    $stmt->close();
                    // --- End supplier_transactions insert ---
                }
                $message = "Expense added successfully.";
                // PRG pattern: redirect after successful insert
                header("Location: expense.php?added=1");
                exit;
            } else {
                $message = "Error: " . $conn->error;
            }

            // Update profits table
            $currentDate = date("Y-m-d");
            $result = $conn->query("SELECT * FROM profits WHERE date='$currentDate'");
            $profit_result = $result->fetch_assoc();

            if ($profit_result) {
                $total_expenses = $profit_result['expenses'] + $amount;
                $net_profit = $profit_result['total'] - $total_expenses;

                $update_sql = "UPDATE profits SET expenses=$total_expenses, `net-profits`=$net_profit 
                               WHERE date='$currentDate'";
                $conn->query($update_sql);
            }
        } else {
            $message = "Please fill in all required fields.";
        }
    }
}

// --- Handle form submission for multiple products (cart) ---
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    !isset($_POST['fetch_supplier_products']) &&
    !empty($_POST['cart_json']) && $_POST['cart_json'] !== '[]'
) {
    // NEW: Check if this is from "Other Expense" form
    $is_other_expense_form = isset($_POST['is_other_expense']) && $_POST['is_other_expense'] === '1';
    
    $cart = json_decode($_POST['cart_json'] ?? '[]', true);
    $branch_id  = mysqli_real_escape_string($conn, $_POST['branch_id']);
    $date       = $_POST['date'];
    $spent_by   = mysqli_real_escape_string($conn, $_POST['spent_by']);
    $category   = mysqli_real_escape_string($conn, $_POST['category']);
    
    if ($is_other_expense_form) {
        // OTHER EXPENSE FORM CART
        if (is_array($cart) && count($cart) > 0) {
            foreach ($cart as $item) {
                $type = mysqli_real_escape_string($conn, $item['type'] ?? '');
                $quantity   = isset($item['quantity']) ? intval($item['quantity']) : 0;
                $unit_price = isset($item['unit_price']) ? floatval($item['unit_price']) : 0;
                $amount     = isset($item['amount']) ? floatval($item['amount']) : 0;
                
                $sql = "INSERT INTO expenses (category, `branch-id`, supplier, product_name, quantity, unit_price, amount, date, `spent-by`) 
                        VALUES ('$category', '$branch_id', '-', '$type', $quantity, $unit_price, $amount, '$date', '$spent_by')";
                $conn->query($sql);
            }
            $message = "Other expenses added successfully.";
            header("Location: expense.php?added=1");
            exit;
        }
    } else {
        // ORIGINAL EXPENSE FORM CART LOGIC
        $supplier_id = mysqli_real_escape_string($conn, $_POST['supplier_id']);
        $is_other_supplier = ($supplier_id === 'other');
        $business_name = $is_other_supplier ? mysqli_real_escape_string($conn, $_POST['business_name'] ?? '') : '';
        if ($is_other_supplier) $supplier_id = 0;
        
        if (is_array($cart) && count($cart) > 0) {
            foreach ($cart as $item) {
                $quantity   = isset($item['quantity']) ? intval($item['quantity']) : 0;
                $unit_price = isset($item['unit_price']) ? floatval($item['unit_price']) : 0;
                $amount     = isset($item['amount']) ? floatval($item['amount']) : 0;
                $amount_paid = isset($item['amount_paid']) ? floatval($item['amount_paid']) : 0;
                
                // NEW: Handle product name for "Other" supplier
                $product_name = isset($item['product_name']) ? mysqli_real_escape_string($conn, $item['product_name']) : '';
                
                // FIX: Use new product_name column for manual entries
                if ($is_other_supplier) {
                    // For "Other" supplier: product=NULL, product_name=text, supplier=business_name
                    $sql = "INSERT INTO expenses (category, `branch-id`, supplier_id, supplier, product, product_name, quantity, unit_price, amount, date, `spent-by`) 
                            VALUES ('$category', '$branch_id', NULL, '$business_name', NULL, '$product_name', $quantity, $unit_price, $amount, '$date', '$spent_by')";
                } else {
                    // Regular supplier: product=ID, product_name=NULL
                    $product_id = mysqli_real_escape_string($conn, $item['product']);
                    $sql = "INSERT INTO expenses (category, `branch-id`, supplier_id, product, product_name, quantity, unit_price, amount, date, `spent-by`) 
                            VALUES ('$category', '$branch_id', '$supplier_id', '$product_id', NULL, $quantity, $unit_price, $amount, '$date', '$spent_by')";
                }
                $conn->query($sql);
                
                // NEW: Only insert into supplier_transactions if NOT "Other"
                if (!$is_other_supplier) {
                    // Insert into supplier_transactions
                    $products_res = $conn->query("SELECT product_name FROM supplier_products WHERE id = $product_id");
                    $product_name = '';
                    if ($products_res && $row = $products_res->fetch_assoc()) {
                        $product_name = $row['product_name'];
                    }
                    $branch_name = '';
                    $branch_res = $conn->query("SELECT name FROM branch WHERE id = $branch_id");
                    if ($branch_res && $brow = $branch_res->fetch_assoc()) {
                        $branch_name = $brow['name'];
                    }
                    $balance = $amount - $amount_paid;
                    $now = date('Y-m-d H:i:s');
                    $payment_method = '';
                    $stmt = $conn->prepare("INSERT INTO supplier_transactions (supplier_id, date_time, branch, products_supplied, quantity, unit_price, amount, payment_method, amount_paid, balance) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param(
                        "isssiddsdd",
                        $supplier_id,
                        $now,
                        $branch_name,
                        $product_name,
                        $quantity,
                        $unit_price,
                        $amount,
                        $payment_method,
                        $amount_paid,
                        $balance
                    );
                    $stmt->execute();
                    $stmt->close();
                }
            }
            $message = "Expenses added successfully.";
            header("Location: expense.php?added=1");
            exit;
        } else {
            $message = "Please add at least one product to the cart.";
        }
    }
}

// Show success message if redirected after creation
if (isset($_GET['added']) && $_GET['added'] == '1') {
    $message = "Expense(s) added successfully.";
}

// --- END: Handle form submissions and redirects BEFORE any output ---

include '../includes/auth.php';
require_role(["admin", "manager"]);
include '../pages/sidebar.php';
include '../includes/header.php';

// Filters
$branch_filter = $_GET['branch'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build WHERE clause for filters
$where = [];
if ($branch_filter) {
    $where[] = "e.`branch-id` = " . intval($branch_filter);
}
if ($date_from) {
    $where[] = "DATE(e.date) >= '" . $conn->real_escape_string($date_from) . "'";
}
if ($date_to) {
    $where[] = "DATE(e.date) <= '" . $conn->real_escape_string($date_to) . "'";
}
$whereClause = count($where) ? "WHERE " . implode(' AND ', $where) : "";

// Pagination setup for expenses table
$items_per_page = 30;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $items_per_page;

// Get total expenses count for pagination (filtered)
$count_result = $conn->query("SELECT COUNT(*) AS total FROM expenses e $whereClause");
$count_row = $count_result->fetch_assoc();
$total_items = $count_row['total'];
$total_pages = ceil($total_items / $items_per_page);

// Fetch expenses for current page (filtered)
$expenses_res = $conn->query("
    SELECT e.*, u.username, b.name AS branch_name
    FROM expenses e 
    LEFT JOIN users u ON e.`spent-by` = u.id 
    LEFT JOIN branch b ON e.`branch-id` = b.id
    $whereClause
    ORDER BY e.date DESC
    LIMIT $items_per_page OFFSET $offset
");

// Convert expenses to array for reuse in both tables
$expenses_arr = [];
if ($expenses_res && $expenses_res->num_rows > 0) {
    while ($row = $expenses_res->fetch_assoc()) {
        $expenses_arr[] = $row;
    }
}

// Get total expenses (filtered)
$total_result = $conn->query("SELECT SUM(e.amount) AS total_expenses FROM expenses e $whereClause");
$total_data = $total_result->fetch_assoc();
$total_expenses = $total_data['total_expenses'] ?? 0;
?>

<!-- Link external CSS -->
<link rel="stylesheet" href="assets/css/expense.css">

<div class="container-fluid mt-5">
    <?php if ($message): ?>
        <div class="alert alert-info"><?php echo $message; ?></div>
    <?php endif; ?>

    <!-- Add Expense Form -->
    <div class="card mb-4" style="border-left: 4px solid teal;">
        <div class="card-header title-card">➕ Add New Expense</div>
        <div class="card-body">
            <!-- NEW: Form Type Tab Switcher -->
            <ul class="nav nav-pills tm-main-tabs mb-4" id="formTypeTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link tm-tab-btn active"
                            id="add-expense-form-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#addExpenseFormTab"
                            type="button"
                            role="tab"
                            aria-controls="addExpenseFormTab"
                            aria-selected="true">
                        Add New Expense
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link tm-tab-btn"
                            id="other-expense-form-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#otherExpenseFormTab"
                            type="button"
                            role="tab"
                            aria-controls="otherExpenseFormTab"
                            aria-selected="false">
                        Other Expense
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="formTypeTabsContent">
                <!-- Add New Expense Form Tab -->
                <div class="tab-pane fade show active" id="addExpenseFormTab" role="tabpanel" aria-labelledby="add-expense-form-tab">
                    <form method="post" id="addExpenseForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="category" class="form-label fw-semibold">Category *</label>
                                <input type="text" name="category" id="category" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label for="branch_id" class="form-label fw-semibold">Branch *</label>
                                <select name="branch_id" id="branch_id" class="form-select" required>
                                    <option value="">Select branch</option>
                                    <?php foreach($branches as $b): ?>
                                        <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="supplier_id" class="form-label fw-semibold">Supplier *</label>
                                <select name="supplier_id" id="supplier_id" class="form-select" required>
                                    <option value="">Select supplier</option>
                                    <?php foreach($suppliers as $s): ?>
                                        <option value="<?= $s['id'] ?>">
                                            <?= htmlspecialchars($s['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-3" id="business_name_wrapper" style="display:none;">
                                <label for="business_name" class="form-label fw-semibold">Business Name *</label>
                                <input type="text" name="business_name" id="business_name" class="form-control">
                            </div>
                            <div class="col-md-3" id="product_wrapper">
                                <label for="product" class="form-label fw-semibold">Product *</label>
                                <select name="product" id="product" class="form-select">
                                    <option value="">Select product</option>
                                </select>
                            </div>
                            <div class="col-md-3" id="product_manual_wrapper" style="display:none;">
                                <label for="product_manual" class="form-label fw-semibold">Product *</label>
                                <input type="text" name="product_manual" id="product_manual" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label for="unit_price" class="form-label fw-semibold">Unit Price *</label>
                                <input type="number" id="unit_price" name="unit_price" class="form-control" step="0.01" min="0">
                            </div>
                            <div class="col-md-2">
                                <label for="quantity" class="form-label fw-semibold">Quantity *</label>
                                <input type="number" id="quantity" class="form-control" min="1">
                            </div>
                            <div class="col-md-2">
                                <label for="amount" class="form-label fw-semibold">Amount</label>
                                <input type="number" id="amount" class="form-control" readonly>
                            </div>
                            <div class="col-md-2">
                                <label for="amount_paid" class="form-label fw-semibold">Amount Paid</label>
                                <input type="number" id="amount_paid" class="form-control" min="0" step="0.01">
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="button" id="addToCartBtn" class="btn btn-primary w-100 add-to-cart-btn" title="Add to Cart" style="display:flex;align-items:center;justify-content:center;">
                                    <span style="font-size:1.4em;line-height:1;">
                                        <i class="bi bi-cart-plus"></i>
                                    </span>
                                </button>
                            </div>
                            <div class="col-md-2">
                                <label for="date" class="form-label fw-semibold">Date *</label>
                                <input type="date" name="date" id="date" class="form-control" required value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="spent_by" class="form-label fw-semibold">Spent By *</label>
                                <select name="spent_by" id="spent_by" class="form-select" required>
                                    <option value="">-- Select User --</option>
                                    <?php
                                    $users = $conn->query("SELECT id, username FROM users");
                                    while ($u = $users->fetch_assoc()) {
                                        echo "<option value='{$u['id']}'>{$u['username']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <!-- Cart Section -->
                        <div id="cartSection" style="display:none; margin-top:1.5rem;">
                            <h6 style="font-size:1.15rem; font-weight:bold; color:var(--primary-color); margin-bottom:1rem;">
                                <i class="bi bi-cart4"></i> Cart
                            </h6>
                            <div class="table-responsive">
                                <table class="cart-table align-middle shadow-sm" style="border-radius:12px; overflow:hidden;">
                                    <thead style="background:var(--primary-color);color:#fff;">
                                        <tr>
                                            <th>Product</th>
                                            <th>Quantity</th>
                                            <th>Unit Price</th>
                                            <th>Amount</th>
                                            <th>Amount Paid</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="cartItems"></tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-end fw-bold">Total</td>
                                            <td id="cartTotal" class="fw-bold" style="color:#1abc9c;">0</td>
                                            <td colspan="2"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <input type="hidden" name="cart_json" id="cart_json">
                        <div class="col-12 text-end mt-3">
                            <button type="submit" class="btn btn-primary">Add Expense</button>
                        </div>
                    </form>
                </div>

                <!-- Other Expense Form Tab -->
                <div class="tab-pane fade" id="otherExpenseFormTab" role="tabpanel" aria-labelledby="other-expense-form-tab">
                    <form method="post" id="otherExpenseForm">
                        <input type="hidden" name="is_other_expense" value="1">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="other_category" class="form-label fw-semibold">Category *</label>
                                <input type="text" name="category" id="other_category" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label for="other_type" class="form-label fw-semibold">Type *</label>
                                <input type="text" name="type" id="other_type" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label for="other_branch_id" class="form-label fw-semibold">Branch *</label>
                                <select name="branch_id" id="other_branch_id" class="form-select" required>
                                    <option value="">Select branch</option>
                                    <?php foreach($branches as $b): ?>
                                        <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="other_unit_price" class="form-label fw-semibold">Unit Price *</label>
                                <input type="number" name="unit_price" id="other_unit_price" class="form-control" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-2">
                                <label for="other_quantity" class="form-label fw-semibold">Quantity *</label>
                                <input type="number" name="quantity" id="other_quantity" class="form-control" min="1" required>
                            </div>
                            <div class="col-md-2">
                                <label for="other_amount" class="form-label fw-semibold">Amount</label>
                                <input type="number" name="amount" id="other_amount" class="form-control" readonly>
                            </div>
                            <div class="col-md-2">
                                <label for="other_amount_paid" class="form-label fw-semibold">Amount Paid</label>
                                <input type="number" name="amount_paid" id="other_amount_paid" class="form-control" min="0" step="0.01">
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="button" id="addToOtherCartBtn" class="btn btn-primary w-100 add-to-cart-btn" title="Add to Cart" style="display:flex;align-items:center;justify-content:center;">
                                    <span style="font-size:1.4em;line-height:1;">
                                        <i class="bi bi-cart-plus"></i>
                                    </span>
                                </button>
                            </div>
                            <div class="col-md-2">
                                <label for="other_date" class="form-label fw-semibold">Date *</label>
                                <input type="date" name="date" id="other_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="other_spent_by" class="form-label fw-semibold">Spent By *</label>
                                <select name="spent_by" id="other_spent_by" class="form-select" required>
                                    <option value="">-- Select User --</option>
                                    <?php
                                    $users->data_seek(0);
                                    while ($u = $users->fetch_assoc()) {
                                        echo "<option value='{$u['id']}'>{$u['username']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <!-- Cart Section for Other Expense -->
                        <div id="otherCartSection" style="display:none; margin-top:1.5rem;">
                            <h6 style="font-size:1.15rem; font-weight:bold; color:var(--primary-color); margin-bottom:1rem;">
                                <i class="bi bi-cart4"></i> Cart
                            </h6>
                            <div class="table-responsive">
                                <table class="cart-table align-middle shadow-sm" style="border-radius:12px; overflow:hidden;">
                                    <thead style="background:var(--primary-color);color:#fff;">
                                        <tr>
                                            <th>Type</th>
                                            <th>Quantity</th>
                                            <th>Unit Price</th>
                                            <th>Amount</th>
                                            <th>Amount Paid</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="otherCartItems"></tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-end fw-bold">Total</td>
                                            <td id="otherCartTotal" class="fw-bold" style="color:#1abc9c;">0</td>
                                            <td colspan="2"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <input type="hidden" name="cart_json" id="other_cart_json">
                        <div class="col-12 text-end mt-3">
                            <button type="submit" class="btn btn-primary">Add Other Expense</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Table -->
    <!-- Add pill styles (same as Till Management) -->
    <style>
    .tm-main-tabs { display:flex; flex-wrap:wrap; gap:.75rem; margin-top:.25rem; border:none; }
    .tm-main-tabs .tm-tab-btn {
        border:2px solid var(--primary-color);
        background:#fff;
        color:var(--primary-color);
        font-weight:600;
        border-radius:14px;
        padding:.45rem 1.1rem;
        box-shadow:0 2px 6px rgba(0,0,0,.08);
        transition:background .18s,color .18s,box-shadow .18s,transform .18s;
        font-size:.95rem;
    }
    .tm-main-tabs .tm-tab-btn:hover { background:var(--primary-color); color:#fff; transform:translateY(-2px); }
    .tm-main-tabs .tm-tab-btn.active { background:var(--primary-color); color:#fff; box-shadow:0 4px 10px rgba(26,188,156,.35); }
    .tm-main-tabs .tm-tab-btn:focus { outline:none; box-shadow:0 0 0 3px rgba(26,188,156,.25); }
    body.dark-mode .tm-main-tabs .tm-tab-btn {
        background:#23243a; border-color:#1abc9c; color:#1abc9c; box-shadow:0 2px 6px rgba(0,0,0,.4);
    }
    body.dark-mode .tm-main-tabs .tm-tab-btn:hover,
    body.dark-mode .tm-main-tabs .tm-tab-btn.active { background:#1abc9c; color:#fff; }
    </style>

    <ul class="nav nav-pills tm-main-tabs mb-3" id="expensesTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link tm-tab-btn active"
                    id="expenses-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#expensesTab"
                    type="button"
                    role="tab"
                    aria-controls="expensesTab"
                    aria-selected="true">
                Expenses
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link tm-tab-btn"
                    id="total-expenses-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#totalExpensesTab"
                    type="button"
                    role="tab"
                    aria-controls="totalExpensesTab"
                    aria-selected="false">
                Total Expenses
            </button>
        </li>
    </ul>
    <div class="tab-content" id="expensesTabsContent">
        <!-- Expenses Tab -->
        <div class="tab-pane fade show active" id="expensesTab" role="tabpanel" aria-labelledby="expenses-tab" >
            <!-- Card wrapper for small devices -->
            <div class="d-block d-md-none mb-4">
                <div class="card transactions-card"  style="border-left: 4px solid teal;">
                    <div class="card-body" >
                        <!-- Report Button: icon for small, full for md+ -->
                        <button type="button" class="btn btn-success mb-3 d-inline-flex d-md-none" title="Generate Report" onclick="openReportGen('expenses')">
                            <i class="fa fa-file-pdf"></i>
                        </button>
                        <button type="button" class="btn btn-success mb-3 d-none d-md-inline-flex" onclick="openReportGen('expenses')">
                            <i class="fa fa-file-pdf"></i> Generate Report
                        </button>
                        <!-- Filter tools (smaller on small devices) -->
                        <form method="GET" class="expenses-filter-form d-flex align-items-center flex-wrap gap-2 mb-3">
                            <label class="fw-bold me-2">From:</label>
                            <input type="date" name="date_from" class="form-select me-2" value="<?= htmlspecialchars($date_from) ?>" style="width:110px;">
                            <label class="fw-bold me-2">To:</label>
                            <input type="date" name="date_to" class="form-select me-2" value="<?= htmlspecialchars($date_to) ?>" style="width:110px;">
                            <label class="fw-bold me-2">Branch:</label>
                            <select name="branch" class="form-select me-2" onchange="this.form.submit()" style="width:120px;">
                                <option value="">-- All Branches --</option>
                                <?php
                                $branches = $conn->query("SELECT id, name FROM branch");
                                while ($b = $branches->fetch_assoc()):
                                    $selected = ($branch_filter == $b['id']) ? 'selected' : '';
                                    echo "<option value='{$b['id']}' $selected>{$b['name']}</option>";
                                endwhile;
                                ?>
                            </select>
                            <button type="submit" class="btn btn-primary ms-2" style="padding: 4px 12px; font-size: 0.95rem;">Filter</button>
                        </form>
                        <div class="table-responsive-sm">
                            <div class="transactions-table">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Date & Time</th>
                                            <th>Supplier</th>
                                            <th>Branch</th>
                                            <th>Category</th>
                                            <th>Product</th>
                                            <th>Quantity</th>
                                            <th>Unit Price</th>
                                            <th>Amount Expected</th>
                                            <th>Spent By</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($expenses_arr) > 0): ?>
                                            <?php foreach ($expenses_arr as $row): ?>
                                                <tr>
                                                    <td><?= isset($row['id']) ? htmlspecialchars($row['id']) : '' ?></td>
                                                    <td><?= isset($row['date']) ? htmlspecialchars($row['date']) : '' ?></td>
                                                    <td>
                                                        <?php
                                                        $sup_name = '';
                                                        if (!empty($row['supplier'])) {
                                                            $sup_name = $row['supplier'];
                                                        } elseif (isset($row['supplier_id'])) {
                                                            foreach ($suppliers as $sup) {
                                                                if ($sup['id'] == $row['supplier_id']) {
                                                                    $sup_name = $sup['name'];
                                                                    break;
                                                                }
                                                            }
                                                        }
                                                        echo htmlspecialchars($sup_name);
                                                        ?>
                                                    </td>
                                                    <td><?= isset($row['branch_name']) ? htmlspecialchars($row['branch_name']) : '' ?></td>
                                                    <td><?= isset($row['category']) ? htmlspecialchars($row['category']) : '' ?></td>
                                                    <td>
                                                        <?php
                                                        $prod_name = '';
                                                        if ((empty($row['supplier_id']) || $row['supplier_id'] === null) && !empty($row['supplier'])) {
                                                            $prod_name = $row['product_name'] ?? '';
                                                        } elseif (!empty($row['product']) && isset($products_lookup[$row['product']])) {
                                                            $prod_name = $products_lookup[$row['product']];
                                                        }
                                                        echo htmlspecialchars($prod_name);
                                                        ?>
                                                    </td>
                                                    <td><?= isset($row['quantity']) ? htmlspecialchars($row['quantity']) : '' ?></td>
                                                    <td>UGX<?= isset($row['unit_price']) ? number_format($row['unit_price'], 2) : '0.00' ?></td>
                                                    <td>UGX<?= isset($row['amount']) ? number_format($row['amount'], 2) : '0.00' ?></td>
                                                    <td><?= isset($row['username']) ? htmlspecialchars($row['username']) : '' ?></td>
                                                    <td><?= isset($row['description']) ? htmlspecialchars($row['description']) : '' ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="11" class="text-center text-muted">No expenses recorded yet.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table for medium and large devices -->
            <div class="card mb-5 d-none d-md-block" style="border-left: 4px solid teal;">
                <div class="card-header bg-light text-black d-flex flex-wrap justify-content-between align-items-center" style="border-radius:12px 12px 0 0;">
                    <span class="fw-bold title-card"><i class="fa-solid fa-wallet"></i> All Expenses</span>
                    <button type="button" class="btn btn-success d-inline-flex d-md-none" title="Generate Report" onclick="openReportGen('expenses')">
                        <i class="fa fa-file-pdf"></i>
                    </button>
                    <button type="button" class="btn btn-success d-none d-md-inline-flex" onclick="openReportGen('expenses')">
                        <i class="fa fa-file-pdf"></i> Generate Report
                    </button>
                    <form method="GET" class="d-flex align-items-center flex-wrap gap-2" style="gap:1rem;">
                        <label class="fw-bold me-2">From:</label>
                        <input type="date" name="date_from" class="form-select me-2" value="<?= htmlspecialchars($date_from) ?>" style="width:150px;">
                        <label class="fw-bold me-2">To:</label>
                        <input type="date" name="date_to" class="form-select me-2" value="<?= htmlspecialchars($date_to) ?>" style="width:150px;">
                        <label class="fw-bold me-2">Branch:</label>
                        <select name="branch" class="form-select me-2" onchange="this.form.submit()" style="width:180px;">
                          <option value="">-- All Branches --</option>
                          <?php
                          $branches = $conn->query("SELECT id, name FROM branch");
                          while ($b = $branches->fetch_assoc()):
                            $selected = ($branch_filter == $b['id']) ? 'selected' : '';
                            echo "<option value='{$b['id']}' $selected>{$b['name']}</option>";
                          endwhile;
                          ?>
                        </select>
                        <button type="submit" class="btn btn-primary ms-2">Filter</button>
                    </form>
                </div>
                <div class="card-body p-0">
                  <div class="transactions-table">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date & Time</th>
                                <th>Supplier</th>
                                <th>Branch</th>
                                <th>Category</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Amount Expected</th>
                                <th>Spent By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($expenses_arr) > 0): ?>
                                <?php foreach ($expenses_arr as $row): ?>
                                    <tr>
                                        <td><?= isset($row['id']) ? htmlspecialchars($row['id']) : '' ?></td>
                                        <td><?= isset($row['date']) ? htmlspecialchars($row['date']) : '' ?></td>
                                        <td>
                                            <?php
                                            $sup_name = '';
                                            if (!empty($row['supplier'])) {
                                                $sup_name = $row['supplier'];
                                            } elseif (isset($row['supplier_id'])) {
                                                foreach ($suppliers as $sup) {
                                                    if ($sup['id'] == $row['supplier_id']) {
                                                        $sup_name = $sup['name'];
                                                        break;
                                                    }
                                                }
                                            }
                                            echo htmlspecialchars($sup_name);
                                            ?>
                                        </td>
                                        <td><?= isset($row['branch_name']) ? htmlspecialchars($row['branch_name']) : '' ?></td>
                                        <td><?= isset($row['category']) ? htmlspecialchars($row['category']) : '' ?></td>
                                        <td>
                                            <?php
                                            $prod_name = '';
                                            if ((empty($row['supplier_id']) || $row['supplier_id'] === null) && !empty($row['supplier'])) {
                                                $prod_name = $row['product_name'] ?? '';
                                            } elseif (!empty($row['product']) && isset($products_lookup[$row['product']])) {
                                                $prod_name = $products_lookup[$row['product']];
                                            }
                                            echo htmlspecialchars($prod_name);
                                            ?>
                                        </td>
                                        <td><?= isset($row['quantity']) ? htmlspecialchars($row['quantity']) : '' ?></td>
                                        <td>UGX<?= isset($row['unit_price']) ? number_format($row['unit_price'], 2) : '0.00' ?></td>
                                        <td>UGX<?= isset($row['amount']) ? number_format($row['amount'], 2) : '0.00' ?></td>
                                        <td><?= isset($row['username']) ? htmlspecialchars($row['username']) : '' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted">No expenses recorded yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                  </div>
                  <!-- Pagination -->
                  <?php if ($total_pages > 1): ?>
                  <nav aria-label="Page navigation">
                      <ul class="pagination justify-content-center mt-3">
                          <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                              <li class="page-item <?= ($p == $page) ? 'active' : '' ?>">
                                  <a class="page-link" href="?page=<?= $p ?><?= ($branch_filter ? '&branch=' . $branch_filter : '') ?><?= ($date_from ? '&date_from=' . $date_from : '') ?><?= ($date_to ? '&date_to=' . $date_to : '') ?>"><?= $p ?></a>
                              </li>
                          <?php endfor; ?>
                      </ul>
                  </nav>
                  <?php endif; ?>
                  <!-- Total Expenses Sum -->
                  <div class="mt-4 text-end">
                      <h5 class="fw-bold">Total Expenses: <span class="total-expenses-value">UGX <?= number_format($total_expenses, 2) ?></span></h5>
                  </div>
                </div>
            </div>
        </div>
        
        <!-- Total Expenses Tab -->
        <div class="tab-pane fade" id="totalExpensesTab" role="tabpanel" aria-labelledby="total-expenses-tab">
            <div class="card mb-5" style="border-left: 4px solid teal;">
                <div class="card-header bg-light text-black" style="border-radius:12px 12px 0 0;">
                    <span class="fw-bold title-card"><i class="fa-solid fa-calculator"></i> Total Expenses</span>
                    <button type="button" class="btn btn-success d-inline-flex d-md-none" title="Generate Report" onclick="openReportGen('total_expenses')">
                        <i class="fa fa-file-pdf"></i>
                    </button>
                    <button type="button" class="btn btn-success d-none d-md-inline-flex" onclick="openReportGen('total_expenses')">
                        <i class="fa fa-file-pdf"></i> Generate Report
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="transactions-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Branch</th>
                                    <th>Expenses</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Query: group by date and branch, sum(amount)
                                $totals_res = $conn->query("
                                    SELECT DATE(e.date) as expense_date, b.name as branch_name, COUNT(e.id) as expenses_count, SUM(e.amount) as total_expenses
                                    FROM expenses e
                                    LEFT JOIN branch b ON e.`branch-id` = b.id
                                    $whereClause
                                    GROUP BY expense_date, branch_name
                                    ORDER BY expense_date DESC, branch_name ASC
                                ");
                                $grand_total = 0;
                                if ($totals_res && $totals_res->num_rows > 0):
                                    while ($row = $totals_res->fetch_assoc()):
                                        $grand_total += $row['total_expenses'];
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['expense_date']) ?></td>
                                        <td><?= htmlspecialchars($row['branch_name']) ?></td>
                                        <td><?= htmlspecialchars($row['expenses_count']) ?></td>
                                        <td class="text-end">UGX <?= number_format($row['total_expenses'], 2) ?></td>
                                    </tr>
                                <?php endwhile; else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No expense totals found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 text-end">
                        <h5 class="fw-bold">Grand Total: <span class="total-expenses-value">UGX <?= number_format($grand_total, 2) ?></span></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for report generation -->
<div class="modal fade" id="reportGenModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" id="reportGenForm">
      <div class="modal-header">
        <h5 class="modal-title" id="reportGenModalTitle">Generate Expenses Report</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <div class="col-md-6">
          <label class="form-label">From</label>
          <input type="date" name="date_from" id="report_date_from" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">To</label>
          <input type="date" name="date_to" id="report_date_to" class="form-control" required>
        </div>
        <div class="col-md-12">
          <label class="form-label">Branch</label>
          <select name="branch" id="report_branch" class="form-select">
            <option value="">All Branches</option>
            <?php foreach($branches as $b): ?>
              <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Generate & Print</button>
      </div>
    </form>
  </div>
</div>

<!-- Link external JavaScript -->
<script src="assets/js/expense.js"></script>

<?php include '../includes/footer.php'; ?>

