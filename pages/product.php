<?php
include '../includes/db.php';
include '../includes/auth.php';
require_role(["admin", "manager", "staff", "super"]);

// Get logged-in user info EARLY (before any output)
$user_role   = $_SESSION['role'];
$user_branch = $_SESSION['branch_id'] ?? null;

// -----------------------------------------------------------------
// AJAX HANDLERS
// -----------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    while (ob_get_level()) ob_end_clean();
    ob_start();
    header('Content-Type: application/json; charset=utf-8');
    
    $action = $_POST['action'];
    
    // Delete Product handler
    if ($action === 'delete_product') {
        $product_id = intval($_POST['product_id'] ?? 0);
        
        if ($product_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
            ob_end_flush();
            exit;
        }
        
        // Fetch product to check branch security
        $fetch = $conn->prepare("SELECT `branch-id` FROM products WHERE id = ?");
        $fetch->bind_param("i", $product_id);
        $fetch->execute();
        $prod = $fetch->get_result()->fetch_assoc();
        $fetch->close();
        
        if (!$prod) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            ob_end_flush();
            exit;
        }
        
        if ($user_role === 'staff' && $prod['branch-id'] != $user_branch) {
            echo json_encode(['success' => false, 'message' => 'Access denied: You can only delete products in your branch']);
            ob_end_flush();
            exit;
        }
        
        $delete_stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $delete_stmt->bind_param("i", $product_id);
        if ($delete_stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Product deleted successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $delete_stmt->error]);
        }
        $delete_stmt->close();
        ob_end_flush();
        exit;
    }
    
    // Get product details for Edit Modal populating
    if ($action === 'get_product_details') {
        $product_id = intval($_POST['product_id'] ?? 0);
        if ($product_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
            ob_end_flush();
            exit;
        }
        
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $prod = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($prod) {
            echo json_encode(['success' => true, 'product' => $prod]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
        }
        ob_end_flush();
        exit;
    }
}

// -----------------------------------------------------------------
// POST HANDLERS FOR FORMS
// -----------------------------------------------------------------

// Add / Restock Product Form
if (isset($_POST['add_product'])) {
    $barcode = trim($_POST['barcode']);
    $name = trim($_POST['name']);
    $selling_price = floatval($_POST['price']);
    $buying_price = floatval($_POST['cost']);
    $stock = floatval($_POST['stock']);
    $branch_id = intval($_POST['branch_id']);
    $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
    $business_id = $_SESSION['business_id'] ?? 1;

    // SECURITY: Staff can only add products to their own branch
    if ($user_role === 'staff' && $branch_id != $user_branch) {
        $_SESSION['product_message'] = "<div class='alert alert-danger shadow-sm'>❌ Access denied: You can only add products to your branch</div>";
        header("Location: product.php");
        exit;
    }

    // Check if product with same barcode already exists in this branch for today
    $check_stmt = $conn->prepare("SELECT id, stock, incoming_stock FROM products WHERE barcode = ? AND `branch-id` = ? AND `date` = CURRENT_DATE()");
    $check_stmt->bind_param("si", $barcode, $branch_id);
    $check_stmt->execute();
    $existing = $check_stmt->get_result()->fetch_assoc();
    $check_stmt->close();

    if ($existing) {
        // Product exists - update stock and add to incoming_stock
        $new_stock = $existing['stock'] + $stock;
        $new_incoming = $existing['incoming_stock'] + $stock;
        $update_stmt = $conn->prepare("UPDATE products SET stock = ?, incoming_stock = ?, `selling-price` = ?, `buying-price` = ?, expiry_date = ? WHERE id = ?");
        $update_stmt->bind_param("iiddsi", $new_stock, $new_incoming, $selling_price, $buying_price, $expiry_date, $existing['id']);
        
        if ($update_stmt->execute()) {
            $_SESSION['product_message'] = "<div class='alert alert-success shadow-sm'>✅ Product stock updated! Added {$stock} units as incoming stock. New total: {$new_stock}</div>";
        } else {
            $_SESSION['product_message'] = "<div class='alert alert-danger shadow-sm'>❌ Error updating stock: " . $update_stmt->error . "</div>";
        }
        $update_stmt->close();
    } else {
        // New product - insert with today's date
        $stmt = $conn->prepare("INSERT INTO products (name, barcode, `selling-price`, `buying-price`, stock, opening_stock, incoming_stock, outgoing, damages, `branch-id`, business_id, expiry_date, sms_sent, visible, location, `date`) VALUES (?, ?, ?, ?, ?, ?, 0, 0, 0, ?, ?, ?, 0, 1, 'shelf', CURRENT_DATE())");
        $stmt->bind_param("ssddiiiis", $name, $barcode, $selling_price, $buying_price, $stock, $stock, $branch_id, $business_id, $expiry_date);
        
        if ($stmt->execute()) {
            $_SESSION['product_message'] = "<div class='alert alert-success shadow-sm'>✅ Product added successfully!</div>";
        } else {
            $_SESSION['product_message'] = "<div class='alert alert-danger shadow-sm'>❌ Error: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
    header("Location: product.php");
    exit;
}

// Edit Product Form Handler
if (isset($_POST['edit_product'])) {
    $product_id = intval($_POST['product_id']);
    $name = trim($_POST['name']);
    $barcode = trim($_POST['barcode']);
    $selling_price = floatval($_POST['price']);
    $buying_price = floatval($_POST['cost']);
    $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
    $restock = floatval($_POST['restock_qty'] ?? 0);
    $damages = floatval($_POST['damages_qty'] ?? 0);

    // Fetch product to verify branch
    $fetch = $conn->prepare("SELECT `branch-id`, stock, incoming_stock, damages FROM products WHERE id = ?");
    $fetch->bind_param("i", $product_id);
    $fetch->execute();
    $product = $fetch->get_result()->fetch_assoc();
    $fetch->close();

    if (!$product) {
        $_SESSION['product_message'] = "<div class='alert alert-danger shadow-sm'>❌ Product not found.</div>";
        header("Location: product.php");
        exit;
    }

    // SECURITY: Staff can only edit products in their branch
    if ($user_role === 'staff' && $product['branch-id'] != $user_branch) {
        $_SESSION['product_message'] = "<div class='alert alert-danger shadow-sm'>❌ Access denied: You can only edit products in your branch</div>";
        header("Location: product.php");
        exit;
    }

    // Calculate new values
    $new_incoming = $product['incoming_stock'] + $restock;
    $new_damages = $product['damages'] + $damages;
    $new_stock = $product['stock'] + $restock - $damages;

    $update = $conn->prepare("UPDATE products SET name = ?, barcode = ?, `selling-price` = ?, `buying-price` = ?, expiry_date = ?, stock = ?, incoming_stock = ?, damages = ? WHERE id = ?");
    $update->bind_param("ssddsiiii", $name, $barcode, $selling_price, $buying_price, $expiry_date, $new_stock, $new_incoming, $new_damages, $product_id);

    if ($update->execute()) {
        $_SESSION['product_message'] = "<div class='alert alert-success shadow-sm'>✅ Product updated successfully!</div>";
    } else {
        $_SESSION['product_message'] = "<div class='alert alert-danger shadow-sm'>❌ Error updating product: " . $update->error . "</div>";
    }
    $update->close();

    header("Location: product.php");
    exit;
}

// Quick Restock Product Form Handler
if (isset($_POST['quick_restock_product'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = floatval($_POST['quantity'] ?? 0);
    $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;

    // Fetch product to check branch security
    $fetch = $conn->prepare("SELECT `branch-id`, stock, incoming_stock FROM products WHERE id = ?");
    $fetch->bind_param("i", $product_id);
    $fetch->execute();
    $product = $fetch->get_result()->fetch_assoc();
    $fetch->close();

    if (!$product) {
        $_SESSION['product_message'] = "<div class='alert alert-danger shadow-sm'>❌ Product not found.</div>";
        header("Location: product.php");
        exit;
    }

    // SECURITY: Staff can only edit products in their branch
    if ($user_role === 'staff' && $product['branch-id'] != $user_branch) {
        $_SESSION['product_message'] = "<div class='alert alert-danger shadow-sm'>❌ Access denied: You can only restock products in your branch</div>";
        header("Location: product.php");
        exit;
    }

    if ($quantity <= 0) {
        $_SESSION['product_message'] = "<div class='alert alert-warning shadow-sm'>⚠️ Please enter a valid restock quantity.</div>";
        header("Location: product.php");
        exit;
    }

    $new_incoming = $product['incoming_stock'] + $quantity;
    $new_stock = $product['stock'] + $quantity;

    $update = $conn->prepare("UPDATE products SET stock = ?, incoming_stock = ?, expiry_date = ? WHERE id = ?");
    $update->bind_param("iisi", $new_stock, $new_incoming, $expiry_date, $product_id);

    if ($update->execute()) {
        $_SESSION['product_message'] = "<div class='alert alert-success shadow-sm'>✅ Product restocked successfully! Added {$quantity} units.</div>";
    } else {
        $_SESSION['product_message'] = "<div class='alert alert-danger shadow-sm'>❌ Error restocking product: " . $update->error . "</div>";
    }
    $update->close();

    header("Location: product.php");
    exit;
}

// -----------------------------------------------------------------
// VIEWS LOADING
// -----------------------------------------------------------------
include 'sms.php';

// Correct sidebar for staff
if ($_SESSION['role'] === 'staff') {
    include '../pages/sidebar_staff.php';
} else {
    include '../pages/sidebar.php';
}
include '../includes/header.php';

$message = "";

// Pagination and filtering setup
$limit = 50; 
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Date filter
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Build WHERE clause
$where_clauses = ["products.`date` = '" . $conn->real_escape_string($selected_date) . "'"];
$selected_branch = null;
if ($user_role === 'staff' && $user_branch) {
    $selected_branch = $user_branch;
    $where_clauses[] = "products.`branch-id` = $user_branch";
} elseif (!empty($_GET['branch'])) {
    $selected_branch = (int)$_GET['branch'];
    $where_clauses[] = "products.`branch-id` = $selected_branch";
}
$where = "WHERE " . implode(" AND ", $where_clauses);

// Fetch products count
$count_res = $conn->query("SELECT COUNT(*) AS total FROM products $where");
$total_products = ($count_res->fetch_assoc())['total'] ?? 0;
$total_pages = max(1, ceil($total_products / $limit));

// Fetch products paginated
$products_res = $conn->query("
    SELECT products.*, branch.name AS branch_name 
    FROM products 
    JOIN branch ON products.`branch-id` = branch.id 
    $where 
    ORDER BY products.id DESC 
    LIMIT $offset, $limit
");

$products = [];
if ($products_res && $products_res->num_rows > 0) {
    while ($row = $products_res->fetch_assoc()) {
        $products[] = $row;
    }
}

// Display message from session (if redirected)
if (isset($_SESSION['product_message'])) {
    $message = $_SESSION['product_message'];
    unset($_SESSION['product_message']);
}
?>

<link rel="stylesheet" href="assets/css/product.css">

<div class="container-fluid mt-4" style="max-width: 100vw; overflow-x: hidden; padding-left: 1rem; padding-right: 1rem;">
    <?= $message ?>

    <!-- Add Product Form -->
    <div class="card mb-4" style="border-left: 4px solid teal;">
        <div class="card-header title-card d-flex justify-content-between align-items-center">
            <span>➕ Add New Product</span>
            <button type="button" id="scanProductBarcodeBtn" class="btn btn-outline-primary btn-scan-barcode" title="Scan Barcode">
                <i class="fa-solid fa-barcode"></i>
            </button>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="barcode" class="form-label fw-semibold">Barcode</label>
                        <input type="text" name="barcode" id="barcode" class="form-control" placeholder="Scan or enter barcode" required>
                    </div>
                    <div class="col-md-3">
                        <label for="name" class="form-label fw-semibold">Product Name</label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="e.g. Coca-Cola 500ml" required>
                    </div>
                    <div class="col-md-3">
                        <label for="price" class="form-label fw-semibold">Selling Price (UGX)</label>
                        <input type="number" step="0.01" name="price" id="price" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="col-md-3">
                        <label for="cost" class="form-label fw-semibold">Buying Price (UGX)</label>
                        <input type="number" step="0.01" name="cost" id="cost" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="col-md-3">
                        <label for="stock" class="form-label fw-semibold">Stock Quantity</label>
                        <input type="number" step="any" min="0" name="stock" id="stock" class="form-control" placeholder="0" required>
                    </div>
                    <div class="col-md-3">
                        <label for="branch" class="form-label fw-semibold">Branch</label>
                        <select name="branch_id" id="branch" class="form-select" required <?= ($user_role === 'staff') ? 'disabled' : ''; ?>>
                            <option value="">-- Select Branch --</option>
                            <?php
                            if ($user_role === 'staff' && $user_branch) {
                                $branch = $conn->prepare("SELECT id, name FROM branch WHERE id = ?");
                                $branch->bind_param("i", $user_branch);
                                $branch->execute();
                                $branch_res = $branch->get_result();
                                if ($b = $branch_res->fetch_assoc()) {
                                    echo "<option value='{$b['id']}' selected>" . htmlspecialchars($b['name']) . "</option>";
                                }
                                $branch->close();
                            } else {
                                $branches = $conn->query("SELECT id, name FROM branch");
                                while ($b = $branches->fetch_assoc()) {
                                    $sel = ($selected_branch == $b['id']) ? "selected" : "";
                                    echo "<option value='{$b['id']}' $sel>" . htmlspecialchars($b['name']) . "</option>";
                                }
                            }
                            ?>
                        </select>
                        <?php if ($user_role === 'staff' && $user_branch): ?>
                            <input type="hidden" name="branch_id" value="<?= $user_branch ?>">
                        <?php endif; ?>
                    </div>
                    <div class="col-md-3">
                        <label for="expiry_date" class="form-label fw-semibold">Expiry Date</label>
                        <input type="date" name="expiry_date" id="expiry_date" class="form-control">
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" name="add_product" class="btn btn-primary">➕ Add Product</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Products List Card -->
    <div class="card mb-4" style="border-left: 4px solid teal; max-width: 100%; overflow: hidden;">
        <div class="card-header d-flex justify-content-between align-items-center title-card">
            <span>📋 Products Inventory <?php if ($user_role === 'staff') echo '(My Branch)'; ?> (Date: <?= htmlspecialchars($selected_date) ?>)</span>
            <div class="d-flex align-items-center gap-2">
                <input type="text" id="productSearchInput" class="form-control" placeholder="Search products..." style="width:180px;">
                <form method="GET" class="d-flex align-items-center gap-2 mb-0">
                    <label class="me-1 fw-bold text-white mb-0">Date:</label>
                    <input type="date" name="date" class="form-control form-control-sm" style="width:auto;" value="<?= htmlspecialchars($selected_date) ?>" onchange="this.form.submit()">
                    <?php if ($user_role !== 'staff'): ?>
                        <label class="ms-2 me-1 fw-bold text-white mb-0">Branch:</label>
                        <select name="branch" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
                            <option value="">-- All Branches --</option>
                            <?php
                            $branches = $conn->query("SELECT id, name FROM branch");
                            while ($b = $branches->fetch_assoc()) {
                                $selected = ($selected_branch == $b['id']) ? "selected" : "";
                                echo "<option value='{$b['id']}' $selected>" . htmlspecialchars($b['name']) . "</option>";
                            }
                            ?>
                        </select>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        <div class="card-body" style="overflow: hidden; padding: 1rem;">
            <div class="transactions-table" style="width: 100%; overflow-x: auto;">
                <table id="productsTable">
                    <thead>
                        <tr>
                            <th class="sticky-col sticky-col-1">#</th>
                            <?php 
                            if (empty($selected_branch) && $user_role !== 'staff') {
                                echo '<th class="sticky-col sticky-col-2">Branch</th>';
                                echo '<th class="sticky-col sticky-col-3">Name</th>';
                                echo '<th>Barcode</th>';
                            } else {
                                echo '<th class="sticky-col sticky-col-2">Name</th>';
                                echo '<th class="sticky-col sticky-col-3">Barcode</th>';
                            }
                            ?>
                            <th>Buying Price</th>
                            <th>Selling Price</th>
                            <th>Opening Stock</th>
                            <th>Incoming Stock</th>
                            <th>Current Balance</th>
                            <th>Outgoing</th>
                            <th>Damages</th>
                            <th>Closing Stock</th>
                            <th>Expected Closing</th>
                            <th>Expected Outgoing</th>
                            <th>Expected Profits</th>
                            <th>Expiry Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (count($products) > 0) {
                            $i = $offset + 1;
                            foreach ($products as $row) {
                                $sellingPrice = floatval($row['selling-price']);
                                $buyingPrice = floatval($row['buying-price']);
                                
                                $openingStock = floatval($row['opening_stock']);
                                $incomingStock = floatval($row['incoming_stock']);
                                $currentBalance = $openingStock + $incomingStock;
                                $outgoing = floatval($row['outgoing']);
                                $damages = floatval($row['damages']);
                                $closingStock = floatval($row['stock']); // the 'stock' column stores the current closing stock count
                                
                                $expectedClosing = $closingStock * $sellingPrice;
                                $expectedOutgoing = $outgoing * $sellingPrice;
                                $expectedProfits = $outgoing * ($sellingPrice - $buyingPrice);

                                echo "<tr>
                                    <td class='sticky-col sticky-col-1'>{$i}</td>";
                                if (empty($selected_branch) && $user_role !== 'staff') {
                                    echo "<td class='sticky-col sticky-col-2'>" . htmlspecialchars($row['branch_name']) . "</td>";
                                    echo "<td class='sticky-col sticky-col-3'>" . htmlspecialchars($row['name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['barcode']) . "</td>";
                                } else {
                                    echo "<td class='sticky-col sticky-col-2'>" . htmlspecialchars($row['name']) . "</td>";
                                    echo "<td class='sticky-col sticky-col-3'>" . htmlspecialchars($row['barcode']) . "</td>";
                                }
                                echo "<td>UGX " . number_format($buyingPrice, 2) . "</td>
                                    <td>UGX " . number_format($sellingPrice, 2) . "</td>
                                    <td>{$openingStock}</td>
                                    <td>{$incomingStock}</td>
                                    <td><span class='fw-semibold'>{$currentBalance}</span></td>
                                    <td class='text-primary fw-semibold'>{$outgoing}</td>
                                    <td class='text-danger fw-semibold'>{$damages}</td>
                                    <td class='text-success fw-bold'>{$closingStock}</td>
                                    <td>UGX " . number_format($expectedClosing, 2) . "</td>
                                    <td>UGX " . number_format($expectedOutgoing, 2) . "</td>
                                    <td><span class='fw-bold text-success'>UGX " . number_format($expectedProfits, 2) . "</span></td>
                                    <td>{$row['expiry_date']}</td>
                                    <td>
                                        <div class='d-flex gap-1'>
                                            <button class='btn btn-sm btn-success quick-restock-btn' 
                                                    data-id='{$row['id']}' 
                                                    data-name='" . htmlspecialchars($row['name']) . "' 
                                                    data-expiry='{$row['expiry_date']}'
                                                    title='Restock Product'>
                                                <i class='fa fa-plus'></i>
                                            </button>
                                            <button class='btn btn-sm btn-warning edit-product-btn' 
                                                    data-id='{$row['id']}' 
                                                    title='Edit Product'>
                                                <i class='fa fa-edit'></i>
                                            </button>
                                            <button class='btn btn-sm btn-danger delete-product-btn' 
                                                    data-id='{$row['id']}' 
                                                    data-name='" . htmlspecialchars($row['name']) . "'
                                                    title='Delete Product'>
                                                <i class='fa fa-trash'></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>";
                                $i++;
                            }
                        } else {
                            $colspan = (empty($selected_branch) && $user_role !== 'staff') ? 17 : 16;
                            echo "<tr><td colspan='$colspan' class='text-center text-muted'>No products in inventory.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <nav aria-label="Products pagination" class="mt-2">
        <ul class="pagination justify-content-center">
            <?php for ($p = 1; $p <= $total_pages; $p++): 
                $qs = "?page={$p}";
                if ($selected_branch) $qs .= "&branch={$selected_branch}";
                if ($selected_date) $qs .= "&date=" . urlencode($selected_date);
            ?>
            <li class="page-item <?= ($p == $page) ? 'active' : '' ?>">
                <a class="page-link" href="product.php<?= $qs ?>"><?= $p ?></a>
            </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header" style="background:var(--primary-color);color:#fff;">
                    <h5 class="modal-title">✏️ Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="product_id" id="editProductId">
                    
                    <div class="mb-3">
                        <label for="editName" class="form-label fw-semibold">Product Name</label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editBarcode" class="form-label fw-semibold">Barcode</label>
                        <input type="text" name="barcode" id="editBarcode" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="editCost" class="form-label fw-semibold">Buying Price (UGX)</label>
                        <input type="number" step="0.01" name="cost" id="editCost" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editPrice" class="form-label fw-semibold">Selling Price (UGX)</label>
                        <input type="number" step="0.01" name="price" id="editPrice" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="editExpiryDate" class="form-label fw-semibold">Expiry Date</label>
                        <input type="date" name="expiry_date" id="editExpiryDate" class="form-control">
                    </div>

                    <div class="border p-3 rounded mb-3 bg-light">
                        <h6 class="fw-bold mb-2">Adjust Inventory (Optional)</h6>
                        
                        <div class="mb-3">
                            <label for="editRestockQty" class="form-label small fw-semibold">Add Incoming Stock Quantity</label>
                            <input type="number" step="any" min="0" name="restock_qty" id="editRestockQty" class="form-control form-control-sm" placeholder="0">
                        </div>

                        <div class="mb-0">
                            <label for="editDamagesQty" class="form-label small fw-semibold">Add Damages Quantity</label>
                            <input type="number" step="any" min="0" name="damages_qty" id="editDamagesQty" class="form-control form-control-sm" placeholder="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_product" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Product Modal -->
<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">⚠️ Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="deleteProductMessage" class="fw-semibold"></p>
                <input type="hidden" id="deleteProductId">
                <div id="deleteMessage"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirmDeleteProduct" class="btn btn-danger">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Quick Restock Modal -->
<div class="modal fade" id="quickRestockModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">➕ Restock Product</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="quickRestockProductName" class="fw-semibold mb-3"></p>
                    <input type="hidden" name="product_id" id="quickRestockProductId">
                    
                    <div class="mb-3">
                        <label for="quickRestockQty" class="form-label fw-semibold">Quantity to Add</label>
                        <input type="number" step="any" min="0" name="quantity" id="quickRestockQty" class="form-control" placeholder="Enter quantity" required>
                    </div>

                    <div class="mb-3">
                        <label for="quickRestockExpiry" class="form-label fw-semibold">New Expiry Date</label>
                        <input type="date" name="expiry_date" id="quickRestockExpiry" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="quick_restock_product" class="btn btn-success">Confirm Restock</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Barcode Scan Modal -->
<div id="productBarcodeScanModal" class="barcode-scan-modal" style="display:none;">
    <div class="barcode-scan-card" style="border-left: 4px solid teal;">
        <div class="barcode-scan-header d-flex justify-content-between align-items-center">
            <span><i class="fa-solid fa-barcode"></i> Scan Product Barcode</span>
            <button type="button" id="closeProductBarcodeScan" class="btn btn-close"></button>
        </div>
        <div class="barcode-scan-body">
            <div class="barcode-scan-view-area">
                <video id="productBarcodeScanVideo" autoplay muted playsinline></video>
                <canvas id="productBarcodeScanCanvas" style="display:none;"></canvas>
                <button type="button" id="rotateProductBarcodeCameraBtn" class="btn btn-secondary barcode-rotate-btn" title="Switch Camera">
                    <i class="fa-solid fa-camera-rotate"></i>
                </button>
            </div>
            <div class="barcode-scan-text mt-3 mb-2 text-center">
                <span>Scan product barcode to auto-fill.</span>
            </div>
            <div class="barcode-scan-mode mb-3 text-center">
                <label class="me-2">Scan Mode:</label>
                <select id="productBarcodeScanMode" class="form-select d-inline-block" style="width:auto;">
                    <option value="camera">Camera</option>
                    <option value="hardware">Barcode Hardware</option>
                </select>
            </div>
            <div id="productBarcodeScanStatus" class="barcode-scan-status text-center"></div>
        </div>
    </div>
</div>

<script>
    window.productNameColumnIndex = <?php echo (empty($selected_branch) && $user_role !== 'staff') ? '2' : '1'; ?>;
</script>

<script src="assets/js/product.js"></script>

<?php include '../includes/footer.php'; ?>
