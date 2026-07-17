<?php
include '../includes/db.php';
include '../includes/auth.php';
require_role(["admin", "staff", "manager"]);

// Get user info
$user_role = $_SESSION['role'];
$user_branch = $_SESSION['branch_id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;
$username = $_SESSION['username'] ?? 'Staff';

// Handle AJAX request to record sale
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'record_sale') {
    // PREVENT ANY HTML OUTPUT
    while (ob_get_level()) ob_end_clean();
    ob_start();
    
    header('Content-Type: application/json');
    
    $order_id = intval($_POST['order_id'] ?? 0);
    $payment_method = trim($_POST['payment_method'] ?? 'Cash');
    
    // DETAILED ERROR LOGGING
    error_log("QR Scanner: Recording sale for order_id=$order_id, payment_method=$payment_method, user_id=$user_id, branch=$user_branch");
    
    if ($order_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
        ob_end_flush();
        exit;
    }
    
    try {
        // Check if receipt_counter table exists and create if needed
        $conn->query("CREATE TABLE IF NOT EXISTS `receipt_counter` (
            `prefix` VARCHAR(10) NOT NULL PRIMARY KEY,
            `last_number` INT NOT NULL DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        $conn->begin_transaction();
        
        // Get order details
        $stmt = $conn->prepare("SELECT * FROM remote_orders WHERE id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$order) {
            throw new Exception('Order not found');
        }
        
        error_log("QR Scanner: Order found - ref=" . $order['order_reference'] . ", amount=" . $order['expected_amount']);
        
        // Get order items
        $stmt = $conn->prepare("SELECT * FROM remote_order_items WHERE order_id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $items_result = $stmt->get_result();
        $items = [];
        while ($item = $items_result->fetch_assoc()) {
            $items[] = $item;
        }
        $stmt->close();
        
        if (empty($items)) {
            throw new Exception('No items found in order');
        }
        
        error_log("QR Scanner: Found " . count($items) . " items");
        
        // FIXED: Use RP prefix (same as staff dashboard sales)
        $prefix = 'RP';
        $lock_stmt = $conn->prepare("SELECT last_number FROM receipt_counter WHERE prefix = ? FOR UPDATE");
        $lock_stmt->bind_param("s", $prefix);
        $lock_stmt->execute();
        $counter_result = $lock_stmt->get_result()->fetch_assoc();
        $lock_stmt->close();
        
        $next_number = ($counter_result['last_number'] ?? 0) + 1;
        
        if (!$counter_result) {
            $ins_stmt = $conn->prepare("INSERT INTO receipt_counter (prefix, last_number) VALUES (?, ?)");
            $ins_stmt->bind_param("si", $prefix, $next_number);
            $ins_stmt->execute();
            $ins_stmt->close();
        } else {
            $upd_stmt = $conn->prepare("UPDATE receipt_counter SET last_number = ? WHERE prefix = ?");
            $upd_stmt->bind_param("is", $next_number, $prefix);
            $upd_stmt->execute();
            $upd_stmt->close();
        }
        
        $receipt_no = $prefix . '-' . str_pad($next_number, 5, '0', STR_PAD_LEFT);
        error_log("QR Scanner: Generated receipt number: $receipt_no");
        
        $now = date('Y-m-d H:i:s');
        $branch_id = intval($order['branch_id']);
        
        // Calculate totals
        $total_quantity = 0;
        $total_amount = floatval($order['expected_amount']);
        $total_cost = 0;
        $total_profit = 0;
        
        // Build products_json array AND REDUCE STOCK
        $products_json_array = [];
        
        foreach ($items as $item) {
            $product_id = intval($item['product_id']);
            $quantity = intval($item['quantity']);
            $unit_price = floatval($item['unit_price']);
            
            // Get product details
            $prod_stmt = $conn->prepare("SELECT `buying-price`, stock FROM products WHERE id = ? AND `branch-id` = ?");
            $prod_stmt->bind_param("ii", $product_id, $branch_id);
            $prod_stmt->execute();
            $prod = $prod_stmt->get_result()->fetch_assoc();
            $prod_stmt->close();
            
            $buying_price = $prod ? floatval($prod['buying-price']) : 0;
            $current_stock = $prod ? intval($prod['stock']) : 0;
            
            // IMPORTANT: Check if enough stock available
            if ($current_stock < $quantity) {
                throw new Exception("Insufficient stock for product: {$item['product_name']}. Available: {$current_stock}, Required: {$quantity}");
            }
            
            // REDUCE STOCK NOW (when sale is recorded)
            $update_stock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND `branch-id` = ?");
            $update_stock->bind_param("iii", $quantity, $product_id, $branch_id);
            if (!$update_stock->execute()) {
                throw new Exception('Failed to update stock for product: ' . $item['product_name']);
            }
            $update_stock->close();
            
            $item_cost = $buying_price * $quantity;
            $item_amount = $unit_price * $quantity;
            
            $total_quantity += $quantity;
            $total_cost += $item_cost;
            $total_profit += ($item_amount - $item_cost);
            
            // Add to products JSON
            $products_json_array[] = [
                'id' => $product_id,
                'name' => $item['product_name'],
                'quantity' => $quantity,
                'price' => $unit_price
            ];
        }
        
        $products_json = json_encode($products_json_array);
        
        error_log("QR Scanner: Totals - qty=$total_quantity, amount=$total_amount, cost=$total_cost, profit=$total_profit");
        
        // Insert sale record - SIMPLE VERSION
        $sale_stmt = $conn->prepare("INSERT INTO sales 
            (`product-id`, `branch-id`, quantity, amount, `sold-by`, `cost-price`, total_profits, date, payment_method, receipt_no, products_json) 
            VALUES (0, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $sale_stmt->bind_param("iididdssss", 
            $branch_id, $total_quantity, $total_amount, $user_id, $total_cost, $total_profit, $now, $payment_method, $receipt_no, $products_json);
        
        if (!$sale_stmt->execute()) {
            $error_msg = 'Failed to insert sale: ' . $sale_stmt->error;
            error_log("QR Scanner ERROR: $error_msg");
            throw new Exception($error_msg);
        }
        $sale_stmt->close();
        
        error_log("QR Scanner: Sale inserted successfully");
        
        // Update profits table
        $today = date('Y-m-d');
        $pf = $conn->prepare("SELECT total, expenses FROM profits WHERE date = ? AND `branch-id` = ?");
        $pf->bind_param("si", $today, $branch_id);
        $pf->execute();
        $pr = $pf->get_result()->fetch_assoc();
        $pf->close();
        
        if ($pr) {
            $new_total = ($pr['total'] ?? 0) + $total_profit;
            $expenses = $pr['expenses'] ?? 0;
            $net = $new_total - $expenses;
            $up = $conn->prepare("UPDATE profits SET total = ?, `net-profits` = ? WHERE date = ? AND `branch-id` = ?");
            $up->bind_param("ddsi", $new_total, $net, $today, $branch_id);
            $up->execute();
            $up->close();
        } else {
            $expenses = 0;
            $new_total = $total_profit;
            $net = $total_profit;
            $ins = $conn->prepare("INSERT INTO profits (`branch-id`, total, `net-profits`, expenses, date) VALUES (?, ?, ?, ?, ?)");
            $ins->bind_param("iddis", $branch_id, $new_total, $net, $expenses, $today);
            $ins->execute();
            $ins->close();
        }
        
        // Update order status to finished
        $update_order = $conn->prepare("UPDATE remote_orders SET status = 'finished', completed_at = ? WHERE id = ?");
        $update_order->bind_param("si", $now, $order_id);
        $update_order->execute();
        $update_order->close();
        
        // Log audit
        require_once '../includes/functions.php';
        logAuditAction($conn, $order_id, 'order_completed', $username, $user_id, 'pending', 'finished', "Sale recorded with receipt: $receipt_no");
        
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Sale recorded successfully!',
            'receipt_no' => $receipt_no
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    
    exit;
}

include '../pages/sidebar.php';
include '../includes/header.php';
require_once '../includes/functions.php';
?>

<!-- Link external CSS -->
<link rel="stylesheet" href="assets/css/qr_scanner.css">

<div class="container-fluid mt-5">
    <div class="card" style="border-left: 4px solid teal;">
        <div class="card-header title-card">
            <i class="fas fa-qrcode me-2"></i>QR Code Scanner
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="scanner-container">
                        <video id="qr-video" autoplay playsinline></video>
                        <div id="scan-status" class="alert alert-info mt-3">
                            <i class="fas fa-info-circle me-2"></i>Point camera at QR code
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div id="order-details" class="d-none">
                        <h5 class="mb-3">Order Details</h5>
                        <div class="order-info">
                            <p><strong>Order Reference:</strong> <span id="order-ref"></span></p>
                            <p><strong>Customer Name:</strong> <span id="customer-name"></span></p>
                            <p><strong>Customer Phone:</strong> <span id="customer-phone"></span></p>
                            <p><strong>Branch:</strong> <span id="branch-name"></span></p>
                            <p><strong>Expected Amount:</strong> <span id="expected-amount"></span></p>
                            <p><strong>Order Date:</strong> <span id="order-date"></span></p>
                        </div>
                        
                        <h5 class="mt-4 mb-3">Order Items</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="order-items"></tbody>
                            </table>
                        </div>
                        
                        <div class="mt-4">
                            <button class="btn btn-success btn-lg w-100" onclick="showRecordSaleModal()">
                                <i class="fas fa-check me-2"></i>Complete Order
                            </button>
                            <button class="btn btn-secondary mt-2 w-100" onclick="resetScanner()">
                                <i class="fas fa-redo me-2"></i>Scan Another Code
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Record Sale Modal -->
<div class="modal fade" id="recordSaleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:var(--primary-color);color:#fff;">
                <h5 class="modal-title">Record Sale</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Confirm sale details and record transaction:</p>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Customer Name</label>
                    <input type="text" id="sale-customer-name" class="form-control" readonly>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Total Amount</label>
                    <input type="text" id="sale-total-amount" class="form-control" readonly>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Payment Method</label>
                    <select id="sale-payment-method" class="form-select">
                        <option value="Cash" selected>Cash</option>
                        <option value="MTN MoMo">MTN MoMo</option>
                        <option value="Airtel Money">Airtel Money</option>
                        <option value="Bank">Bank</option>
                    </select>
                </div>
                
                <div id="sale-message"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirm-record-sale" class="btn btn-success">
                    <i class="fas fa-check me-2"></i>Record Sale
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Link external JavaScript -->
<script src="assets/js/qr_scanner.js"></script>

<?php include '../includes/footer.php'; ?>
