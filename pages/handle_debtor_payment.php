<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) session_start();

include_once __DIR__ . '/../includes/db.php';
include_once __DIR__ . '/../includes/receipt_helper.php'; // <-- NEW: Include helper

// Handle debtor payment (AJAX)
if (isset($_POST['pay_debtor']) && isset($_POST['id']) && isset($_POST['amount'])) {
    // Only send JSON header if possible (avoid "headers already sent" warnings)
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }

    $debtor_id = intval($_POST['id']);
    $amount = floatval($_POST['amount']);
    $now = date('Y-m-d H:i:s');
    $current_user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

    // Fetch debtor info safely
    $stmt = $conn->prepare("SELECT * FROM debtors WHERE id = ?");
    $stmt->bind_param("i", $debtor_id);
    $stmt->execute();
    $debtor = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$debtor) {
        echo json_encode(['message' => 'Debtor not found.', 'reload' => false]);
        exit;
    }

    // normalize fields (compat with different schemas)
    $debtor_amount_paid = floatval($debtor['amount_paid'] ?? 0);
    $debtor_balance = floatval($debtor['balance'] ?? 0);
    $debtor_item_taken = $debtor['item_taken'] ?? '';
    $debtor_quantity = intval($debtor['quantity_taken'] ?? 0);
    $debtor_branch_id = intval($debtor['branch_id'] ?? $debtor['branch-id'] ?? 0);
    $debtor_created_by = intval($debtor['created_by'] ?? $debtor['created-by'] ?? $current_user_id);
    $cust_id = intval($debtor['customer_id'] ?? 0); // <-- NEW: customer_id

    $new_paid = $debtor_amount_paid + $amount;
    $new_balance = $debtor_balance - $amount;

    // Start transaction for debtor repayment
    $conn->begin_transaction();
    try {
        // Generate sequential receipt number
        $receiptNo = generateReceiptNumber($conn, 'RP');
        
        $original_invoice = $debtor['invoice_no'] ?? '';
        $original_date_str = date('Y-m-d', strtotime($debtor['created_at']));
        
        // Build products list string
        $products_desc_parts = [];
        if (!empty($debtor['products_json'])) {
            $products_data = json_decode($debtor['products_json'], true);
            if (is_array($products_data)) {
                foreach ($products_data as $item) {
                    $products_desc_parts[] = $item['name'] . ' x' . $item['quantity'];
                }
            }
        }
        if (empty($products_desc_parts) && !empty($debtor_item_taken)) {
            $products_desc_parts[] = $debtor_item_taken;
        }
        $products_str = implode(', ', $products_desc_parts);
        
        $is_full_payment = ($new_balance <= 0);
        $prefix = $is_full_payment ? "receipt" : "partial payment receipt";
        $payment_desc = $prefix . " for invoice number " . $original_invoice . " on date " . $original_date_str . " for products " . $products_str;
        
        // Determine payment method and payments_json
        $pm_input = trim($_POST['pm'] ?? '');
        $payments_json = !empty($_POST['payments_json']) ? $_POST['payments_json'] : null;
        if ($payments_json) {
            $payment_method = 'Debtor Repayment (Split)';
        } elseif ($pm_input) {
            $payment_method = 'Debtor Repayment (' . $pm_input . ')';
        } else {
            $payment_method = 'Debtor Repayment';
        }
        
        // Insert sale record in sales table for today
        $sold_by = $current_user_id ?? $debtor_created_by;
        $sstmt = $conn->prepare("INSERT INTO sales (`product-id`,`branch-id`,quantity,amount,`sold-by`,`cost-price`,total_profits,date,payment_method,receipt_no,products_json,payments_json) VALUES (0, ?, 0, ?, ?, 0, ?, ?, ?, ?, ?, ?)");
        $sstmt->bind_param("ididsssss", $debtor_branch_id, $amount, $sold_by, $amount, $now, $payment_method, $receiptNo, $payment_desc, $payments_json);
        $sstmt->execute();
        $sstmt->close();
        
        // Update profits table for today
        $today = date('Y-m-d');
        $pf = $conn->prepare("SELECT total, expenses FROM profits WHERE date = ? AND `branch-id` = ?");
        $pf->bind_param("si", $today, $debtor_branch_id);
        $pf->execute();
        $pr = $pf->get_result()->fetch_assoc();
        $pf->close();
        
        if ($pr) {
            $new_total = ($pr['total'] ?? 0) + $amount;
            $expenses = $pr['expenses'] ?? 0;
            $net = $new_total - $expenses;
            $up = $conn->prepare("UPDATE profits SET total = ?, `net-profits` = ? WHERE date = ? AND `branch-id` = ?");
            $up->bind_param("ddsi", $new_total, $net, $today, $debtor_branch_id);
            $up->execute();
            $up->close();
        } else {
            $expenses = 0;
            $new_total = $amount;
            $net = $amount;
            $ins = $conn->prepare("INSERT INTO profits (`branch-id`, total, `net-profits`, expenses, date) VALUES (?, ?, ?, ?, ?)");
            $ins->bind_param("iddis", $debtor_branch_id, $new_total, $net, $expenses, $today);
            $ins->execute();
            $ins->close();
        }
        
        // Handle customer transaction link
        if ($cust_id > 0) {
            $now_ct = date('Y-m-d H:i:s');
            $sold_by_ct = $_SESSION['username'] ?? 'staff';
            $products_text = "Debtor payment for items: " . ($debtor_item_taken ?: 'Unknown');
            
            $ct_stmt = $conn->prepare("INSERT INTO customer_transactions (customer_id, date_time, products_bought, amount_paid, amount_credited, sold_by, status, invoice_receipt_no) VALUES (?, ?, ?, ?, 0, ?, 'paid', ?)");
            $ct_stmt->bind_param("issdss", $cust_id, $now_ct, $products_text, $amount, $sold_by_ct, $receiptNo);
            $ct_stmt->execute();
            $ct_stmt->close();
        }
        
        // Update or delete debtor record
        if ($is_full_payment) {
            $dstmt = $conn->prepare("DELETE FROM debtors WHERE id = ?");
            $dstmt->bind_param("i", $debtor_id);
            $dstmt->execute();
            $dstmt->close();
            
            $conn->commit();
            echo json_encode(['message' => 'Debt fully paid and sale recorded.', 'reload' => true]);
        } else {
            $ust = $conn->prepare("UPDATE debtors SET amount_paid = ?, balance = ? WHERE id = ?");
            $ust->bind_param("ddi", $new_paid, $new_balance, $debtor_id);
            $ust->execute();
            $ust->close();
            
            $conn->commit();
            echo json_encode(['message' => 'Partial payment recorded. Remaining balance: UGX ' . number_format($new_balance,2), 'reload' => true]);
        }
        exit;
    } catch (Throwable $e) {
        $conn->rollback();
        echo json_encode(['message' => 'Error processing payment: ' . $e->getMessage(), 'reload' => false]);
        exit;
    }
}
?>
