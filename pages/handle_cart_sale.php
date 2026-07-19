<?php
 
include '../includes/db.php';
include_once '../includes/receipt_helper.php'; // <-- Include helper

// --- NEW: Ensure products_json column exists in sales table ---
$check_col = $conn->query("
    SELECT COLUMN_NAME 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'sales' 
      AND COLUMN_NAME = 'products_json'
");
if (!$check_col || $check_col->num_rows === 0) {
    // Add products_json column
    $conn->query("ALTER TABLE sales ADD COLUMN products_json TEXT NULL");
    if ($conn->errno) {
        // Fallback for older MySQL versions
        @$conn->query("ALTER TABLE sales ADD COLUMN products_json TEXT NULL");
    }
}

$user_id   = $_SESSION['user_id'];
$username  = $_SESSION['username'];
$branch_id = $_SESSION['branch_id'];

// Handle cart sale submission
if (isset($_POST['submit_cart']) && !empty($_POST['cart_data'])) {
    $cart = json_decode($_POST['cart_data'], true);
    $amount_paid = floatval($_POST['amount_paid'] ?? 0);
    $payment_method = $_POST['payment_method'] ?? 'Cash';
    $customer_id = isset($_POST['customer_id']) ? intval($_POST['customer_id']) : 0;
    $currentDate = date("Y-m-d");
    $conn->begin_transaction();
    $success = true;
    $messages = [];
    $total = 0;

    // Track products for customer_transactions
    $products_json = json_encode($cart);

    // Calculate total first
    foreach ($cart as $item) {
        $total += floatval($item['price']) * floatval($item['quantity']);
    }

    // --- Check if Customer File payment ---
    if ($payment_method === 'Customer File' && $customer_id > 0) {
        $cust_stmt = $conn->prepare("SELECT account_balance FROM customers WHERE id = ?");
        $cust_stmt->bind_param("i", $customer_id);
        $cust_stmt->execute();
        $cust_res = $cust_stmt->get_result()->fetch_assoc();
        $cust_stmt->close();
        
        $customer_balance = floatval($cust_res['account_balance'] ?? 0);
        
        // Read immediate payment details
        $immediate_paid = floatval($_POST['amount_paid'] ?? 0);
        $immediate_pm = trim($_POST['customer_file_pay_method'] ?? '');
        if ($immediate_paid <= 0) {
            $immediate_paid = 0;
            $immediate_pm = '';
        }

        // Calculate distribution
        $remaining_to_cover = max(0.0, $total - $immediate_paid);
        $from_prepaid_balance = min($remaining_to_cover, $customer_balance);
        $amount_credited = $remaining_to_cover - $from_prepaid_balance;
        
        $amount_paid_val = $immediate_paid + $from_prepaid_balance;
        $status = ($amount_credited > 0) ? 'debtor' : 'paid';

        // Check stock
        $total_quantity = 0;
        $total_cost = 0;
        $total_profit = 0;
        $stock_ok = true;
        $stock_errors = [];

        $chk = $conn->prepare("SELECT `selling-price`,`buying-price`,stock FROM products WHERE id = ? AND `branch-id` = ?");
        foreach ($cart as $item) {
            $pid = (int)($item['id'] ?? 0);
            $qty = floatval($item['quantity'] ?? 0);
            if ($pid <= 0 || $qty <= 0) continue;

            $chk->bind_param("ii", $pid, $branch_id);
            $chk->execute();
            $prod = $chk->get_result()->fetch_assoc();
            if (!$prod) { $stock_ok = false; $stock_errors[] = "Product ID {$pid} not found."; break; }
            if (floatval($prod['stock']) < $qty) { $stock_ok = false; $stock_errors[] = "Not enough stock for {$item['name']}."; break; }

            $total_quantity += $qty;
            $item_total = floatval($prod['selling-price']) * $qty;
            $item_cost = floatval($prod['buying-price']) * $qty;
            $total_cost += $item_cost;
            $total_profit += ($item_total - $item_cost);
        }
        $chk->close();

        if (!$stock_ok) {
            $conn->rollback();
            $_SESSION['cart_sale_message'] = '❌ Cannot record Customer File sale: ' . implode(' ', $stock_errors);
            echo "<script>window.location='staff_dashboard.php';</script>";
            exit;
        }

        // Generate invoice or receipt number
        $receipt_invoice_no = generateReceiptNumber($conn, ($amount_credited > 0) ? 'INV' : 'RP');

        $now = date('Y-m-d H:i:s');
        $sold_by = $_SESSION['username'] ?? 'staff';

        // BEGIN TRANSACTION
        try {
            $conn->begin_transaction();

            // 1. Decrement stock
            $upd = $conn->prepare("UPDATE products SET stock = stock - ?, outgoing = outgoing + ? WHERE id = ? AND `branch-id` = ?");
            foreach ($cart as $item) {
                $pid = (int)($item['id'] ?? 0);
                $qty = floatval($item['quantity'] ?? 0);
                if ($pid <= 0 || $qty <= 0) continue;
                $upd->bind_param("ddii", $qty, $qty, $pid, $branch_id);
                $upd->execute();
            }
            $upd->close();

            // 2. Update customer balances
            $stmt = $conn->prepare("UPDATE customers SET account_balance = account_balance - ?, amount_credited = amount_credited + ? WHERE id = ?");
            $stmt->bind_param("ddi", $from_prepaid_balance, $amount_credited, $customer_id);
            $stmt->execute();
            $stmt->close();

            // 3. Insert customer_transactions
            $ct_pm = ($amount_credited > 0) ? 'Invoice' : ($immediate_pm ?: 'Customer File');
            $ct = $conn->prepare("INSERT INTO customer_transactions (customer_id, branch_id, date_time, products_bought, amount_paid, amount_credited, sold_by, status, invoice_receipt_no, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $ct->bind_param("iissddssss", $customer_id, $branch_id, $now, $products_json, $amount_paid_val, $amount_credited, $sold_by, $status, $receipt_invoice_no, $ct_pm);
            $ct->execute();
            $ct->close();

            // 4. Record immediate sale today in sales & profits tables if they paid money today
            if ($immediate_paid > 0) {
                $immediate_cost = ($immediate_paid / $total) * $total_cost;
                $immediate_profit = ($immediate_paid / $total) * $total_profit;

                $sstmt = $conn->prepare("INSERT INTO sales (`product-id`, `branch-id`, quantity, amount, `sold-by`, `cost-price`, total_profits, date, payment_method, customer_id, receipt_no, products_json) VALUES (0, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $sstmt->bind_param("idddddssiss", $branch_id, $total_quantity, $immediate_paid, $user_id, $immediate_cost, $immediate_profit, $now, $immediate_pm, $customer_id, $receipt_invoice_no, $products_json);
                $sstmt->execute();
                $sstmt->close();

                // Update profits
                $stmt = $conn->prepare("SELECT * FROM profits WHERE date = ? AND `branch-id` = ?");
                $stmt->bind_param("si", $currentDate, $branch_id);
                $stmt->execute();
                $profit_result = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if ($profit_result) {
                    $new_total = $profit_result['total'] + $immediate_profit;
                    $expenses  = $profit_result['expenses'] ?? 0;
                    $net_profit = $new_total - $expenses;
                    $up = $conn->prepare("UPDATE profits SET total = ?, `net-profits` = ? WHERE date = ? AND `branch-id` = ?");
                    $up->bind_param("ddsi", $new_total, $net_profit, $currentDate, $branch_id);
                    $up->execute();
                    $up->close();
                } else {
                    $expenses = 0;
                    $up = $conn->prepare("INSERT INTO profits (`branch-id`, total, `net-profits`, expenses, date) VALUES (?, ?, ?, ?, ?)");
                    $up->bind_param("iddis", $branch_id, $total_profit, $total_profit, $expenses, $currentDate);
                    $up->execute();
                    $up->close();
                }
            }

            $conn->commit();
            if ($amount_credited > 0) {
                $_SESSION['cart_sale_message'] = "✅ Customer debtor recorded successfully! Invoice: $receipt_invoice_no";
            } else {
                $_SESSION['cart_sale_message'] = "✅ Sale recorded successfully! Receipt: $receipt_invoice_no";
            }
            echo "<script>window.location='staff_dashboard.php';</script>";
            exit;

        } catch (Throwable $e) {
            $conn->rollback();
            $_SESSION['cart_sale_message'] = '❌ Failed to record Customer File sale: ' . $e->getMessage();
            echo "<script>window.location='staff_dashboard.php';</script>";
            exit;
        }
    }

    // --- Generate receipt number for ALL sales (not just Customer File) ---
    $receipt_invoice_no = generateReceiptNumber($conn, 'RP'); // <-- SEQUENTIAL RECEIPT NUMBER

    // --- NEW: Validate stock and update stock for all items FIRST ---
    $total_quantity = 0;
    $total_cost = 0;
    $total_profit = 0;
    
    foreach ($cart as $item) {
        $product_id = (int)$item['id'];
        $quantity = floatval($item['quantity']);
        
        // Get product info
        $stmt = $conn->prepare("SELECT name, `selling-price`, `buying-price`, stock FROM products WHERE id = ? AND `branch-id` = ?");
        $stmt->bind_param("ii", $product_id, $branch_id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$product || floatval($product['stock']) < $quantity) {
            $success = false;
            $messages[] = "Product not found or not enough stock for " . htmlspecialchars($item['name']);
            break;
        }
        
        // Calculate totals for grouped sale record
        $item_total = floatval($product['selling-price']) * $quantity;
        $item_cost = floatval($product['buying-price']) * $quantity;
        $total_quantity += $quantity;
        $total_cost += $item_cost;
        $total_profit += ($item_total - $item_cost);

        // Update stock
        $new_stock = floatval($product['stock']) - $quantity;
        $update = $conn->prepare("UPDATE products SET stock = ?, outgoing = outgoing + ? WHERE id = ?");
        $update->bind_param("ddi", $new_stock, $quantity, $product_id);
        $update->execute();
        $update->close();
    }

    // --- NEW: Insert SINGLE grouped sales record with receipt number ---
    if ($success) {
        $date = date('Y-m-d H:i:s');
        
        // Insert ONE sales record for the entire cart (WITH RECEIPT NUMBER)
        if ($payment_method === 'Customer File' && $customer_id > 0) {
            // FIX: Correct type string - 11 parameters
            $stmt = $conn->prepare("INSERT INTO sales (`product-id`, `branch-id`, quantity, amount, `sold-by`, `cost-price`, total_profits, date, payment_method, customer_id, receipt_no, products_json) VALUES (0, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("idddddssiss", $branch_id, $total_quantity, $total, $user_id, $total_cost, $total_profit, $date, $payment_method, $customer_id, $receipt_invoice_no, $products_json);
        } else {
            // FIX: Correct type string - 10 parameters
            $stmt = $conn->prepare("INSERT INTO sales (`product-id`, `branch-id`, quantity, amount, `sold-by`, `cost-price`, total_profits, date, payment_method, receipt_no, products_json) VALUES (0, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("idddddssss", $branch_id, $total_quantity, $total, $user_id, $total_cost, $total_profit, $date, $payment_method, $receipt_invoice_no, $products_json);
        }
        
        if (!$stmt->execute()) {
            $success = false;
            $messages[] = "Failed to record sale";
        }
        $stmt->close();

        // Update profits
        if ($success) {
            $stmt = $conn->prepare("SELECT * FROM profits WHERE date = ? AND `branch-id` = ?");
            $stmt->bind_param("si", $currentDate, $branch_id);
            $stmt->execute();
            $profit_result = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if ($profit_result) {
                $total_amount = $profit_result['total'] + $total_profit;
                $expenses     = $profit_result['expenses'] ?? 0;
                $net_profit   = $total_amount - $expenses;
                $stmt2 = $conn->prepare("UPDATE profits SET total=?, `net-profits`=? WHERE date=? AND `branch-id`=?");
                $stmt2->bind_param("ddsi", $total_amount, $net_profit, $currentDate, $branch_id);
                $stmt2->execute();
                $stmt2->close();
            } else {
                $total_amount = $total_profit;
                $net_profit   = $total_profit;
                $expenses     = 0;
                $stmt2 = $conn->prepare("INSERT INTO profits (`branch-id`, total, `net-profits`, expenses, date) VALUES (?, ?, ?, ?, ?)");
                $stmt2->bind_param("iddis", $branch_id, $total_amount, $net_profit, $expenses, $currentDate);
                $stmt2->execute();
                $stmt2->close();
            }
        }
    }

    // Record customer transaction if payment method is Customer File (sufficient balance case)
    if ($success && $payment_method === 'Customer File' && $customer_id > 0) {
        $now = date('Y-m-d H:i:s');
        $sold_by = $_SESSION['username'];
        $amount_paid_val = $total;
        $amount_credited = 0;
        $status = 'paid';
        
        $stmt = $conn->prepare("UPDATE customers SET account_balance = account_balance - ? WHERE id = ?");
        $stmt->bind_param("di", $total, $customer_id);
        $stmt->execute();
        $stmt->close();

        // FIX: Store the SAME receipt number from sales table WITH branch_id
        $ct = $conn->prepare("INSERT INTO customer_transactions (customer_id, branch_id, date_time, products_bought, amount_paid, amount_credited, sold_by, status, invoice_receipt_no, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Customer File')");
        $ct->bind_param("iissddsss", $customer_id, $branch_id, $now, $products_json, $amount_paid_val, $amount_credited, $sold_by, $status, $receipt_invoice_no);
        $ct->execute();
        $ct->close();
    }

    if ($success) {
        $conn->commit();
        $_SESSION['cart_sale_message'] = '✅ Sale recorded successfully!';
    } else {
        $conn->rollback();
        $_SESSION['cart_sale_message'] = '❌ ' . implode(' ', $messages);
    }
    
    echo "<script>window.location='staff_dashboard.php';</script>";
    exit;
}
?>