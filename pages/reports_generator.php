<?php
include '../includes/db.php';
include '../includes/auth.php';
require_role(["admin", "manager", "staff", "super"]);

$type = $_GET['type'] ?? 'expenses'; // expenses, total_expenses, debtors, payment_analysis, product_summary
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$branch = $_GET['branch'] ?? '';

function getBranchName($conn, $branchId) {
    if (!$branchId) return 'All Branches';
    $res = $conn->query("SELECT name FROM branch WHERE id=" . intval($branchId));
    $row = $res ? $res->fetch_assoc() : null;
    return $row ? $row['name'] : 'Branch ' . $branchId;
}

// Build WHERE clause
$where = [];
if ($branch) $where[] = "e.`branch-id` = " . intval($branch);
if ($date_from) $where[] = "DATE(e.date) >= '" . $conn->real_escape_string($date_from) . "'";
if ($date_to) $where[] = "DATE(e.date) <= '" . $conn->real_escape_string($date_to) . "'";
$whereClause = count($where) ? "WHERE " . implode(' AND ', $where) : "";

// --- Query data based on type ---
$report_title = '';
$thead = '';
$rows = [];
if ($type === 'expenses') {
    $report_title = 'Expenses Report';
    $thead = '<tr>
        <th>ID</th><th>Date & Time</th><th>Supplier</th><th>Branch</th><th>Category</th>
        <th>Product</th><th>Quantity</th><th>Unit Price</th><th>Amount</th><th>Spent By</th>
    </tr>';
    $sql = "
        SELECT e.*, u.username, b.name AS branch_name, s.name AS supplier_name
        FROM expenses e
        LEFT JOIN users u ON e.`spent-by` = u.id
        LEFT JOIN branch b ON e.`branch-id` = b.id
        LEFT JOIN suppliers s ON e.supplier_id = s.id
        $whereClause
        ORDER BY e.date DESC
        LIMIT 500
    ";
    $res = $conn->query($sql);
    while ($row = $res->fetch_assoc()) $rows[] = $row;
} elseif ($type === 'total_expenses') {
    $report_title = 'Total Expenses Report';
    $thead = '<tr><th>Date</th><th>Branch</th><th>Expenses</th><th>Total</th></tr>';
    $sql = "
        SELECT DATE(e.date) as expense_date, b.name as branch_name, COUNT(e.id) as expenses_count, SUM(e.amount) as total_expenses
        FROM expenses e
        LEFT JOIN branch b ON e.`branch-id` = b.id
        $whereClause
        GROUP BY expense_date, branch_name
        ORDER BY expense_date DESC, branch_name ASC
        LIMIT 500
    ";
    $res = $conn->query($sql);
    while ($row = $res->fetch_assoc()) $rows[] = $row;
} elseif ($type === 'debtors') {
    $debtor_category = $_GET['debtor_type'] ?? 'all'; // all, shop, customer
    
    $rows = [];
    $total_shop_balance = 0.0;
    $total_customer_balance = 0.0;
    
    // 1. Fetch Shop Debtors if category is 'all' or 'shop'
    if ($debtor_category === 'all' || $debtor_category === 'shop') {
        $where_shop = [];
        if ($branch) $where_shop[] = "d.branch_id = " . intval($branch);
        if ($date_from) $where_shop[] = "DATE(d.created_at) >= '" . $conn->real_escape_string($date_from) . "'";
        if ($date_to) $where_shop[] = "DATE(d.created_at) <= '" . $conn->real_escape_string($date_to) . "'";
        $whereShopClause = count($where_shop) ? "WHERE " . implode(' AND ', $where_shop) : "";

        $shop_sql = "
            SELECT 
                d.created_at AS date_time,
                d.invoice_no AS invoice_no,
                d.debtor_name AS debtor_name,
                COALESCE(NULLIF(d.debtor_contact, ''), d.debtor_email, 'N/A') AS contact_info,
                d.item_taken AS items_taken,
                d.quantity_taken AS qty_taken,
                (d.amount_paid + d.balance) AS total_amount,
                d.amount_paid AS amount_paid,
                d.balance AS balance_due,
                d.is_paid,
                d.due_date,
                b.name AS branch_name
            FROM debtors d
            LEFT JOIN branch b ON d.branch_id = b.id
            $whereShopClause
            ORDER BY d.created_at DESC
        ";
        $shop_res = $conn->query($shop_sql);
        if ($shop_res) {
            while ($s_row = $shop_res->fetch_assoc()) {
                $bal = floatval($s_row['balance_due']);
                $total_shop_balance += $bal;
                $rows[] = [
                    'source_type' => 'Shop Debtor',
                    'date_time' => $s_row['date_time'],
                    'invoice_no' => $s_row['invoice_no'] ?: '-',
                    'debtor_name' => $s_row['debtor_name'] ?: 'Unknown',
                    'contact_info' => $s_row['contact_info'] ?: 'N/A',
                    'items_taken' => $s_row['items_taken'] ?: '-',
                    'total_amount' => floatval($s_row['total_amount']),
                    'amount_paid' => floatval($s_row['amount_paid']),
                    'balance_due' => $bal,
                    'status' => (!empty($s_row['is_paid']) || $bal <= 0) ? 'Paid' : 'Unpaid',
                    'due_date' => $s_row['due_date'] ?: '',
                    'branch_name' => $s_row['branch_name'] ?: 'N/A'
                ];
            }
            $shop_res->close();
        }
    }

    // 2. Fetch Customer File Debtors if category is 'all' or 'customer'
    if ($debtor_category === 'all' || $debtor_category === 'customer') {
        $where_cust = [];
        if ($branch) $where_cust[] = "ct.branch_id = " . intval($branch);
        if ($date_from) $where_cust[] = "DATE(ct.date_time) >= '" . $conn->real_escape_string($date_from) . "'";
        if ($date_to) $where_cust[] = "DATE(ct.date_time) <= '" . $conn->real_escape_string($date_to) . "'";
        $where_cust[] = "(ct.amount_credited > 0 OR LOWER(ct.status) = 'debtor')";
        $whereCustClause = "WHERE " . implode(' AND ', $where_cust);

        $cust_sql = "
            SELECT 
                ct.date_time,
                ct.invoice_receipt_no AS invoice_no,
                c.name AS debtor_name,
                COALESCE(NULLIF(c.contact, ''), c.email, 'N/A') AS contact_info,
                ct.products_bought AS items_json,
                ct.amount_paid,
                ct.amount_credited AS balance_due,
                ct.status,
                b.name AS branch_name
            FROM customer_transactions ct
            JOIN customers c ON ct.customer_id = c.id
            LEFT JOIN branch b ON ct.branch_id = b.id
            $whereCustClause
            ORDER BY ct.date_time DESC
        ";
        $cust_res = $conn->query($cust_sql);
        if ($cust_res) {
            while ($c_row = $cust_res->fetch_assoc()) {
                $bal = floatval($c_row['balance_due']);
                $paid = floatval($c_row['amount_paid']);
                $tot = $paid + $bal;
                $total_customer_balance += $bal;

                // Format products_bought description
                $items_display = '';
                if (!empty($c_row['items_json'])) {
                    $p_data = json_decode($c_row['items_json'], true);
                    if (is_array($p_data)) {
                        $items_display = implode(', ', array_map(function($p) {
                            $name = $p['name'] ?? ($p['product'] ?? 'Item');
                            $q = $p['quantity'] ?? ($p['qty'] ?? 1);
                            return $name . ' x' . $q;
                        }, $p_data));
                    } else {
                        $items_display = $c_row['items_json'];
                    }
                } else {
                    $items_display = 'Customer File Invoice';
                }

                $status_str = 'Unpaid';
                if ($bal <= 0 || strtolower($c_row['status']) === 'paid') {
                    $status_str = 'Paid';
                } elseif ($paid > 0) {
                    $status_str = 'Partial';
                }

                $rows[] = [
                    'source_type' => 'Customer File',
                    'date_time' => $c_row['date_time'],
                    'invoice_no' => $c_row['invoice_no'] ?: '-',
                    'debtor_name' => $c_row['debtor_name'] ?: 'Unknown',
                    'contact_info' => $c_row['contact_info'] ?: 'N/A',
                    'items_taken' => $items_display,
                    'total_amount' => $tot,
                    'amount_paid' => $paid,
                    'balance_due' => $bal,
                    'status' => $status_str,
                    'branch_name' => $c_row['branch_name'] ?: 'N/A'
                ];
            }
            $cust_res->close();
        }
    }

    // Sort combined rows by date_time DESC
    usort($rows, function($a, $b) {
        return strcmp($b['date_time'], $a['date_time']);
    });

    // Build Report Title and Table Headers
    $report_title = 'Consolidated Debtors Report';
    if ($debtor_category === 'shop') $report_title = 'Shop Debtors Report';
    elseif ($debtor_category === 'customer') $report_title = 'Customer File Debtors Report';

    $thead = '<tr>
        <th>Date & Time</th>
        <th>Source</th>
        <th>Invoice No.</th>
        <th>Debtor Name</th>
        <th>Contact</th>
        <th>Branch</th>
        <th>Items Taken</th>
        <th>Total Amount</th>
        <th>Amount Paid</th>
        <th>Balance Due</th>
        <th>Status</th>
    </tr>';
} elseif ($type === 'payment_analysis') {
    $report_title = 'Payment Method Analysis Report';
    $thead = '<tr>
        <th>Date</th><th>Payment Method</th><th>Amount</th>
    </tr>';
    $where = [];
    if ($branch) $where[] = "sales.`branch-id` = " . intval($branch);
    if ($date_from) $where[] = "DATE(sales.date) >= '" . $conn->real_escape_string($date_from) . "'";
    if ($date_to) $where[] = "DATE(sales.date) <= '" . $conn->real_escape_string($date_to) . "'";
    $whereClause = count($where) ? "WHERE " . implode(' AND ', $where) : "";
    $sql = "
        SELECT
            DATE(sales.date) AS day,
            COALESCE(
                NULLIF(sales.payment_method, ''),
                CASE
                    WHEN sales.customer_id IS NOT NULL
                      OR sales.receipt_no LIKE 'RP-%'
                      OR sales.invoice_no LIKE 'INV-%'
                    THEN 'Customer File'
                    ELSE 'Cash'
                END
            ) AS pm,
            sales.amount,
            sales.payments_json
        FROM sales
        $whereClause
        ORDER BY day DESC
    ";
    $res = $conn->query($sql);
    $pm_map = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $day = $row['day'];
            $amt = floatval($row['amount']);
            $p_json = $row['payments_json'];
            
            if (!empty($p_json) && ($p_arr = json_decode($p_json, true)) && is_array($p_arr)) {
                foreach ($p_arr as $p_item) {
                    $m_name = trim($p_item['method'] ?? 'Cash');
                    $m_amt = floatval($p_item['amount'] ?? 0);
                    if ($m_amt > 0) {
                        $pm_map[$day][$m_name] = ($pm_map[$day][$m_name] ?? 0) + $m_amt;
                    }
                }
            } else {
                $m_name = $row['pm'];
                $pm_map[$day][$m_name] = ($pm_map[$day][$m_name] ?? 0) + $amt;
            }
        }
        $res->close();
    }
    $rows = [];
    foreach ($pm_map as $day => $methods) {
        ksort($methods);
        foreach ($methods as $m_name => $total_amt) {
            $rows[] = [
                'day' => $day,
                'pm' => $m_name,
                'total' => $total_amt
            ];
        }
    }
} elseif ($type === 'product_summary') {
    $report_title = 'Product Summary Report';
    $thead = '<tr>
        <th>Date</th>
        <th>Branch</th>
        <th>Product</th>
        <th>Items Sold Full Pay</th>
        <th>Items Sold Debtors</th>
        <th>Total Items Sold</th>
        <th>Unit Price</th>
        <th>Expected Amount</th>
        <th>Amount Received</th>
    </tr>';
    $where = [];
    if ($branch) $where[] = "s.`branch-id` = " . intval($branch);
    if ($date_from) $where[] = "DATE(s.date) >= '" . $conn->real_escape_string($date_from) . "'";
    if ($date_to) $where[] = "DATE(s.date) <= '" . $conn->real_escape_string($date_to) . "'";
    $whereClause = count($where) ? "WHERE " . implode(' AND ', $where) : "";
    
    // Preload products map (id -> selling-price, name)
    $products_map_by_id = [];
    $products_map_by_name = [];
    $prodRes = $conn->query("SELECT id, name, `selling-price` FROM products WHERE `date` = CURRENT_DATE()");
    if ($prodRes) {
        while ($p = $prodRes->fetch_assoc()) {
            $products_map_by_id[intval($p['id'])] = $p;
            $products_map_by_name[strtolower(trim($p['name']))] = $p;
        }
        $prodRes->close();
    }
    
    $sql = "
        SELECT s.id, s.date, s.original_debt_date, s.`branch-id` AS branch_id, b.name AS branch_name,
               s.`product-id` AS product_id, p.name AS product_name, p.`selling-price` AS product_selling_price,
               s.quantity, s.amount, s.products_json, s.payment_method, s.invoice_no
        FROM sales s
        LEFT JOIN products p ON s.`product-id` = p.id
        LEFT JOIN branch b ON s.`branch-id` = b.id
        $whereClause
        ORDER BY s.date DESC
        LIMIT 5000
    ";
    $res = $conn->query($sql);
    
    $product_summary_map = [];
    $invoice_cache = [];
    $invoices_lookup = [];
    $srows = [];
    
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $srows[] = $row;
            if (!empty($row['invoice_no']) && stripos($row['invoice_no'], 'INV-') === 0) {
                $invoices_lookup[$row['invoice_no']] = $row;
            }
        }
        $res->close();
    }
    
    $get_invoice_data = function($invoice_no) use ($conn, &$invoice_cache, $invoices_lookup, $products_map_by_id, $products_map_by_name) {
        if (isset($invoice_cache[$invoice_no])) {
            return $invoice_cache[$invoice_no];
        }
        if (isset($invoices_lookup[$invoice_no])) {
            $invoice_cache[$invoice_no] = $invoices_lookup[$invoice_no];
            return $invoices_lookup[$invoice_no];
        }
        // Query database for invoice details
        $stmt = $conn->prepare("SELECT products_json, date, `branch-id` AS branch_id, (SELECT name FROM branch WHERE id = sales.`branch-id`) AS branch_name FROM sales WHERE invoice_no = ? LIMIT 1");
        $stmt->bind_param("s", $invoice_no);
        $stmt->execute();
        $r_data = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $invoice_cache[$invoice_no] = $r_data;
        return $r_data;
    };
    
    // Pass 1: Process normal sales and debtor invoices (exclude debtor repayments)
    foreach ($srows as $srow) {
        $payment_method = $srow['payment_method'] ?? '';
        $is_repayment = (stripos($payment_method, 'Repayment') !== false || stripos($srow['products_json'] ?? '', 'invoice number') !== false);
        
        if ($is_repayment) {
            continue; // Will handle in Pass 2
        }
        
        $sale_date = $srow['original_debt_date'] 
            ? $srow['original_debt_date'] 
            : date('Y-m-d', strtotime($srow['date']));
            
        $branch_id_key = intval($srow['branch_id']);
        $branch_name = $srow['branch_name'] ?? 'Unknown';
        $sale_amount = floatval($srow['amount']);
        $sale_qty = floatval($srow['quantity'] ?? 0);
        $product_id = intval($srow['product_id'] ?? 0);
        
        $is_debtor = (!empty($srow['invoice_no']) && stripos($srow['invoice_no'], 'INV-') === 0);
        
        if ($product_id > 0) {
            // Simple sale tied to a product row
            $prod = $products_map_by_id[$product_id] ?? null;
            $prod_name = $prod['name'] ?? ($srow['product_name'] ?? 'Unknown');
            $unit_price = ($prod && $prod['selling-price'] !== null && $prod['selling-price'] !== '') ? floatval($prod['selling-price']) : (($sale_qty > 0) ? ($sale_amount / $sale_qty) : 0);
            
            $key = implode('|', [$sale_date, $branch_id_key, 'id_'.$product_id]);
            if (!isset($product_summary_map[$key])) {
                $product_summary_map[$key] = [
                    'sale_date' => $sale_date,
                    'branch_name' => $branch_name,
                    'product_name' => $prod_name,
                    'items_sold_full_pay' => 0,
                    'items_sold_debtors' => 0,
                    'total_items_sold' => 0,
                    'unit_price' => $unit_price,
                    'expected_amount' => 0.0,
                    'amount_received' => 0.0
                ];
            }
            
            if ($is_debtor) {
                $product_summary_map[$key]['items_sold_debtors'] += $sale_qty;
            } else {
                $product_summary_map[$key]['items_sold_full_pay'] += $sale_qty;
            }
            $product_summary_map[$key]['total_items_sold'] += $sale_qty;
            $product_summary_map[$key]['expected_amount'] += ($unit_price * $sale_qty);
            $product_summary_map[$key]['amount_received'] += $sale_amount;
        } else {
            // Grouped sale: expand products_json
            $pj = $srow['products_json'] ?? null;
            $items = json_decode($pj, true);
            if (!is_array($items) || count($items) === 0) {
                $key = implode('|', [$sale_date, $branch_id_key, 'multiple_products']);
                if (!isset($product_summary_map[$key])) {
                    $product_summary_map[$key] = [
                        'sale_date' => $sale_date,
                        'branch_name' => $branch_name,
                        'product_name' => 'Multiple Products',
                        'items_sold_full_pay' => 0,
                        'items_sold_debtors' => 0,
                        'total_items_sold' => 0,
                        'unit_price' => 0,
                        'expected_amount' => 0.0,
                        'amount_received' => 0.0
                    ];
                }
                if ($is_debtor) {
                    $product_summary_map[$key]['items_sold_debtors'] += $sale_qty;
                } else {
                    $product_summary_map[$key]['items_sold_full_pay'] += $sale_qty;
                }
                $product_summary_map[$key]['total_items_sold'] += $sale_qty;
                $product_summary_map[$key]['amount_received'] += $sale_amount;
                continue;
            }
            
            $group_expected_total = 0.0;
            $expanded = [];
            foreach ($items as $it) {
                $it_qty = floatval($it['quantity'] ?? ($it['qty'] ?? 0));
                $it_id = intval($it['id'] ?? 0);
                $it_name = trim($it['name'] ?? ($it['product'] ?? 'Unknown'));
                $prod_info = null;
                if ($it_id && isset($products_map_by_id[$it_id])) {
                    $prod_info = $products_map_by_id[$it_id];
                } elseif ($it_name && isset($products_map_by_name[strtolower($it_name)])) {
                    $prod_info = $products_map_by_name[strtolower($it_name)];
                }
                $unit_price = ($prod_info && $prod_info['selling-price'] !== null && $prod_info['selling-price'] !== '') ? floatval($prod_info['selling-price']) : floatval($it['price'] ?? 0);
                $item_expected = $unit_price * $it_qty;
                $group_expected_total += $item_expected;
                
                $expanded[] = [
                    'id' => $it_id,
                    'name' => $it_name,
                    'quantity' => $it_qty,
                    'unit_price' => $unit_price,
                    'item_expected' => $item_expected
                ];
            }
            
            $group_received_total = $sale_amount;
            if ($group_expected_total <= 0) {
                $sum_qty = 0;
                foreach ($expanded as $ex) $sum_qty += $ex['quantity'];
                foreach ($expanded as $ex) {
                    $prop = ($sum_qty > 0) ? ($ex['quantity'] / $sum_qty) : 0;
                    $item_received = $group_received_total * $prop;
                    $prod_key = $ex['id'] ? 'id_'.$ex['id'] : 'name_'.strtolower($ex['name']);
                    $key = implode('|', [$sale_date, $branch_id_key, $prod_key]);
                    if (!isset($product_summary_map[$key])) {
                        $product_summary_map[$key] = [
                            'sale_date' => $sale_date,
                            'branch_name' => $branch_name,
                            'product_name' => $ex['name'],
                            'items_sold_full_pay' => 0,
                            'items_sold_debtors' => 0,
                            'total_items_sold' => 0,
                            'unit_price' => $ex['unit_price'],
                            'expected_amount' => 0.0,
                            'amount_received' => 0.0
                        ];
                    }
                    if ($is_debtor) {
                        $product_summary_map[$key]['items_sold_debtors'] += $ex['quantity'];
                    } else {
                        $product_summary_map[$key]['items_sold_full_pay'] += $ex['quantity'];
                    }
                    $product_summary_map[$key]['total_items_sold'] += $ex['quantity'];
                    $product_summary_map[$key]['expected_amount'] += $ex['unit_price'] * $ex['quantity'];
                    $product_summary_map[$key]['amount_received'] += $item_received;
                }
            } else {
                foreach ($expanded as $ex) {
                    $prop = ($group_expected_total > 0) ? ($ex['item_expected'] / $group_expected_total) : 0;
                    $item_received = $group_received_total * $prop;
                    $prod_key = $ex['id'] ? 'id_'.$ex['id'] : 'name_'.strtolower($ex['name']);
                    $key = implode('|', [$sale_date, $branch_id_key, $prod_key]);
                    if (!isset($product_summary_map[$key])) {
                        $product_summary_map[$key] = [
                            'sale_date' => $sale_date,
                            'branch_name' => $branch_name,
                            'product_name' => $ex['name'],
                            'items_sold_full_pay' => 0,
                            'items_sold_debtors' => 0,
                            'total_items_sold' => 0,
                            'unit_price' => $ex['unit_price'],
                            'expected_amount' => 0.0,
                            'amount_received' => 0.0
                        ];
                    }
                    if ($is_debtor) {
                        $product_summary_map[$key]['items_sold_debtors'] += $ex['quantity'];
                    } else {
                        $product_summary_map[$key]['items_sold_full_pay'] += $ex['quantity'];
                    }
                    $product_summary_map[$key]['total_items_sold'] += $ex['quantity'];
                    $product_summary_map[$key]['expected_amount'] += $ex['item_expected'];
                    $product_summary_map[$key]['amount_received'] += $item_received;
                }
            }
        }
    }
    
    // Pass 2: Process debtor repayments (distribute cash received back to original invoice date & product rows)
    foreach ($srows as $srow) {
        $payment_method = $srow['payment_method'] ?? '';
        $is_repayment = (stripos($payment_method, 'Repayment') !== false || stripos($srow['products_json'] ?? '', 'invoice number') !== false);
        
        if (!$is_repayment) {
            continue;
        }
        
        $invoice_no = '';
        if (preg_match('/invoice number (INV-\d+)/i', $srow['products_json'] ?? '', $matches)) {
            $invoice_no = $matches[1];
        }
        
        if ($invoice_no) {
            $orig_inv = $get_invoice_data($invoice_no);
            if ($orig_inv) {
                $orig_invoice_date = date('Y-m-d', strtotime($orig_inv['date']));
                $orig_branch_id = intval($srow['branch_id']);
                $orig_branch_name = $srow['branch_name'] ?? 'Unknown';
                $repay_amount = floatval($srow['amount']);
                
                $orig_items = json_decode($orig_inv['products_json'] ?? '', true);
                $orig_total_expected = 0.0;
                $orig_expanded = [];
                
                if (is_array($orig_items)) {
                    foreach ($orig_items as $it) {
                        $it_qty = floatval($it['quantity'] ?? ($it['qty'] ?? 0));
                        $it_id = intval($it['id'] ?? 0);
                        $it_name = trim($it['name'] ?? ($it['product'] ?? 'Unknown'));
                        $prod_info = null;
                        if ($it_id && isset($products_map_by_id[$it_id])) {
                            $prod_info = $products_map_by_id[$it_id];
                        } elseif ($it_name && isset($products_map_by_name[strtolower($it_name)])) {
                            $prod_info = $products_map_by_name[strtolower($it_name)];
                        }
                        $unit_price = ($prod_info && $prod_info['selling-price'] !== null && $prod_info['selling-price'] !== '') ? floatval($prod_info['selling-price']) : floatval($it['price'] ?? 0);
                        $item_expected = $unit_price * $it_qty;
                        $orig_total_expected += $item_expected;
                        
                        $orig_expanded[] = [
                            'id' => $it_id,
                            'name' => $it_name,
                            'quantity' => $it_qty,
                            'unit_price' => $unit_price,
                            'item_expected' => $item_expected
                        ];
                    }
                }
                
                foreach ($orig_expanded as $ex) {
                    $prop = ($orig_total_expected > 0) ? ($ex['item_expected'] / $orig_total_expected) : 0;
                    $allocated_repay = $repay_amount * $prop;
                    
                    $prod_key = $ex['id'] ? 'id_'.$ex['id'] : 'name_'.strtolower($ex['name']);
                    $orig_key = implode('|', [$orig_invoice_date, $orig_branch_id, $prod_key]);
                    
                    if (!isset($product_summary_map[$orig_key])) {
                        $product_summary_map[$orig_key] = [
                            'sale_date' => $orig_invoice_date,
                            'branch_name' => $orig_branch_name,
                            'product_name' => $ex['name'],
                            'items_sold_full_pay' => 0,
                            'items_sold_debtors' => $ex['quantity'],
                            'total_items_sold' => $ex['quantity'],
                            'unit_price' => $ex['unit_price'],
                            'expected_amount' => $ex['item_expected'],
                            'amount_received' => 0.0
                        ];
                    }
                    $product_summary_map[$orig_key]['amount_received'] += $allocated_repay;
                }
            }
        }
    }
    
    $rows = array_values($product_summary_map);
    usort($rows, function($a, $b) {
        if ($a['sale_date'] === $b['sale_date']) {
            if ($a['branch_name'] === $b['branch_name']) return strcmp($a['product_name'], $b['product_name']);
            return strcmp($a['branch_name'], $b['branch_name']);
        }
        return strcmp($b['sale_date'], $a['sale_date']);
    });

    // --- Query Payment Method Summary for the selected period & branch ---
    $where_pm = [];
    if ($branch) $where_pm[] = "sales.`branch-id` = " . intval($branch);
    if ($date_from) $where_pm[] = "DATE(sales.date) >= '" . $conn->real_escape_string($date_from) . "'";
    if ($date_to) $where_pm[] = "DATE(sales.date) <= '" . $conn->real_escape_string($date_to) . "'";
    $whereClausePM = count($where_pm) ? "WHERE " . implode(' AND ', $where_pm) : "";
    
    $pm_sql = "
        SELECT DATE(sales.date) AS day, sales.payment_method AS pm, sales.amount, sales.payments_json
        FROM sales
        $whereClausePM
        ORDER BY day DESC
    ";
    $pm_res = $conn->query($pm_sql);
    $pm_summary_map = [];
    $grand_total_received = 0.0;
    if ($pm_res) {
        while ($row = $pm_res->fetch_assoc()) {
            $day = $row['day'];
            $amt = floatval($row['amount']);
            $p_json = $row['payments_json'];
            
            if (!empty($p_json) && ($p_arr = json_decode($p_json, true)) && is_array($p_arr)) {
                foreach ($p_arr as $p_item) {
                    $m_name = trim($p_item['method'] ?? 'Cash');
                    $m_amt = floatval($p_item['amount'] ?? 0);
                    if ($m_amt > 0) {
                        $pm_summary_map[$day][$m_name] = ($pm_summary_map[$day][$m_name] ?? 0) + $m_amt;
                        $grand_total_received += $m_amt;
                    }
                }
            } else {
                $m_name = trim($row['pm'] ?: 'Cash');
                $pm_summary_map[$day][$m_name] = ($pm_summary_map[$day][$m_name] ?? 0) + $amt;
                $grand_total_received += $amt;
            }
        }
        $pm_res->close();
    }
    
    $payment_summary_rows = [];
    foreach ($pm_summary_map as $day => $methods) {
        ksort($methods);
        foreach ($methods as $m_name => $tot) {
            $payment_summary_rows[] = [
                'day' => $day,
                'pm' => $m_name,
                'total' => $tot
            ];
        }
    }
} elseif ($type === 'sales') {
    $report_title = 'Sales Report';
    $thead = '<tr>
        <th>Date</th><th>Branch</th><th>Product</th><th>Quantity</th><th>Unit Price</th><th>Total</th><th>Sold By</th>
    </tr>';
    $where = [];
    if ($branch) $where[] = "sales.`branch-id` = " . intval($branch);
    if ($date_from) $where[] = "DATE(sales.date) >= '" . $conn->real_escape_string($date_from) . "'";
    if ($date_to) $where[] = "DATE(sales.date) <= '" . $conn->real_escape_string($date_to) . "'";
    $whereClause = count($where) ? "WHERE " . implode(' AND ', $where) : "";
    $sql = "
        SELECT sales.date, branch.name AS branch_name, products.name AS product_name, sales.quantity, sales.amount, sales.`sold-by`
        FROM sales
        JOIN products ON sales.`product-id` = products.id
        JOIN branch ON sales.`branch-id` = branch.id
        $whereClause
        ORDER BY sales.date DESC
        LIMIT 500
    ";
    $res = $conn->query($sql);
    while ($row = $res->fetch_assoc()) $rows[] = $row;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($report_title) ?></title>
    <link rel="stylesheet" href="assets/css/reports_generator.css?v=<?= time() ?>">
</head>
<body>
    <div class="report-container">
        <div class="report-header">
            <div class="report-title"><?= htmlspecialchars($report_title) ?></div>
            <div class="report-meta">
                Period: <?= htmlspecialchars($date_from ?: '...') ?> to <?= htmlspecialchars($date_to ?: '...') ?> <br>
                Branch: <?= htmlspecialchars(getBranchName($conn, $branch)) ?>
            </div>
        </div>
        <div class="table-responsive-wrapper">
            <table class="report-table">
                <thead><?= $thead ?></thead>
                <tbody>
                <?php if ($type === 'expenses'): ?>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['date']) ?></td>
                            <td><?= htmlspecialchars($row['supplier_name']) ?></td>
                            <td><?= htmlspecialchars($row['branch_name']) ?></td>
                            <td><?= htmlspecialchars($row['category']) ?></td>
                            <td><?= htmlspecialchars($row['product']) ?></td>
                            <td><?= htmlspecialchars($row['quantity']) ?></td>
                            <td>UGX <?= number_format($row['unit_price'],2) ?></td>
                            <td>UGX <?= number_format($row['amount'],2) ?></td>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td><?= htmlspecialchars($row['description']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php elseif ($type === 'total_expenses'): ?>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['expense_date']) ?></td>
                            <td><?= htmlspecialchars($row['branch_name']) ?></td>
                            <td><?= htmlspecialchars($row['expenses_count']) ?></td>
                            <td>UGX <?= number_format($row['total_expenses'],2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php elseif ($type === 'debtors'): ?>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td style="white-space: nowrap;"><?= date("Y-m-d H:i", strtotime($row['date_time'])) ?></td>
                            <td>
                                <?php if ($row['source_type'] === 'Customer File'): ?>
                                    <span class="badge-source badge-customer">Customer File</span>
                                <?php else: ?>
                                    <span class="badge-source badge-shop">Shop Debtor</span>
                                <?php endif; ?>
                            </td>
                            <td style="white-space: nowrap;"><?= htmlspecialchars($row['invoice_no']) ?></td>
                            <td><strong><?= htmlspecialchars($row['debtor_name']) ?></strong></td>
                            <td style="white-space: nowrap;"><?= htmlspecialchars($row['contact_info']) ?></td>
                            <td><?= htmlspecialchars($row['branch_name']) ?></td>
                            <td><?= htmlspecialchars($row['items_taken']) ?></td>
                            <td style="white-space: nowrap;">UGX <?= number_format($row['total_amount'], 2) ?></td>
                            <td style="white-space: nowrap;">UGX <?= number_format($row['amount_paid'], 2) ?></td>
                            <td style="white-space: nowrap;"><strong style="color: #dc2626;">UGX <?= number_format($row['balance_due'], 2) ?></strong></td>
                            <td style="white-space: nowrap;">
                                <?php if ($row['status'] === 'Paid'): ?>
                                    <span style="color: #059669; font-weight: 700;">Paid</span>
                                <?php elseif ($row['status'] === 'Partial'): ?>
                                    <span style="color: #d97706; font-weight: 700;">Partial</span>
                                <?php else: ?>
                                    <span style="color: #dc2626; font-weight: 700;">Unpaid</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php elseif ($type === 'payment_analysis'): ?>
                    <?php
                    $prev_day = null;
                    foreach ($rows as $row):
                        $show_day = ($prev_day !== $row['day']);
                    ?>
                        <tr>
                            <td><?= $show_day ? htmlspecialchars($row['day']) : '' ?></td>
                            <td><?= htmlspecialchars($row['pm']) ?></td>
                            <td>UGX <?= number_format($row['total'],2) ?></td>
                        </tr>
                    <?php
                        $prev_day = $row['day'];
                    endforeach; ?>
                <?php elseif ($type === 'product_summary'): ?>
                    <?php
                    $prev_date = null;
                    $prev_branch = null;
                    foreach ($rows as $row):
                        $show_date = ($prev_date !== $row['sale_date']);
                        $show_branch = ($prev_branch !== $row['branch_name']) || $show_date;
                    ?>
                        <tr>
                            <td><?= $show_date ? htmlspecialchars($row['sale_date']) : '' ?></td>
                            <td><?= $show_branch ? htmlspecialchars($row['branch_name']) : '' ?></td>
                            <td><?= htmlspecialchars($row['product_name']) ?></td>
                            <td><?= htmlspecialchars((int)$row['items_sold_full_pay']) ?></td>
                            <td><?= htmlspecialchars((int)$row['items_sold_debtors']) ?></td>
                            <td><strong><?= htmlspecialchars((int)$row['total_items_sold']) ?></strong></td>
                            <td>UGX <?= number_format((float)($row['unit_price'] ?? 0), 2) ?></td>
                            <td>UGX <?= number_format((float)($row['expected_amount'] ?? 0), 2) ?></td>
                            <td>
                                <strong>UGX <?= number_format((float)($row['amount_received'] ?? 0), 2) ?></strong>
                                <?php
                                if ($row['items_sold_debtors'] > 0) {
                                    if ($row['amount_received'] >= $row['expected_amount']) {
                                        echo ' <span style="background-color: #d1e7dd; color: #0f5132; padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; font-weight: bold; margin-left: 4px;">Cleared</span>';
                                    } else {
                                        echo ' <span style="background-color: #fff3cd; color: #664d03; padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; font-weight: bold; margin-left: 4px;">Pending</span>';
                                    }
                                }
                                ?>
                            </td>
                        </tr>
                    <?php
                        $prev_date = $row['sale_date'];
                        $prev_branch = $row['branch_name'];
                    endforeach; ?>
                <?php elseif ($type === 'sales'): ?>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['date']) ?></td>
                            <td><?= htmlspecialchars($row['branch_name']) ?></td>
                            <td><?= htmlspecialchars($row['product_name']) ?></td>
                            <td><?= htmlspecialchars($row['quantity']) ?></td>
                            <td>UGX <?= number_format($row['amount'],2) ?></td>
                            <td>UGX <?= number_format($row['quantity']*$row['amount'],2) ?></td>
                            <td><?= htmlspecialchars($row['sold-by']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="20" style="text-align:center;color:#888;">No data found.</td></tr>
                <?php endif; ?>
                <?php if (count($rows) === 0): ?>
                    <tr><td colspan="20" style="text-align:center;color:#888;">No data found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
        
        <?php if ($type === 'product_summary' && isset($payment_summary_rows)): ?>
            <div style="margin-top: 30px; page-break-inside: avoid;">
                <h3 style="border-bottom: 2px solid #20b2aa; padding-bottom: 5px; color: #20b2aa; font-family: sans-serif; font-size: 1.2rem;">Payment Method Summary</h3>
                <table class="report-table" style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                    <thead>
                        <tr style="background-color: #20b2aa; color: white;">
                            <th style="padding: 8px; text-align: left; border: 1px solid #ddd;">Date</th>
                            <th style="padding: 8px; text-align: left; border: 1px solid #ddd;">Payment Method</th>
                            <th style="padding: 8px; text-align: right; border: 1px solid #ddd;">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($payment_summary_rows)): ?>
                            <tr>
                                <td colspan="3" style="text-align: center; color: #888; padding: 10px; border: 1px solid #ddd;">No payment summary data found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($payment_summary_rows as $pm_row): ?>
                                <tr>
                                    <td style="padding: 8px; border: 1px solid #ddd;"><?= htmlspecialchars($pm_row['day']) ?></td>
                                    <td style="padding: 8px; border: 1px solid #ddd;"><?= htmlspecialchars($pm_row['pm']) ?></td>
                                    <td style="padding: 8px; text-align: right; border: 1px solid #ddd; font-weight: bold;">UGX <?= number_format($pm_row['total'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr style="background-color: #f2f2f2; font-weight: bold;">
                                <td colspan="2" style="padding: 8px; border: 1px solid #ddd; text-align: right;">Total Amount Received:</td>
                                <td style="padding: 8px; border: 1px solid #ddd; text-align: right; color: #008080;">UGX <?= number_format($grand_total_received, 2) ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <?php if ($type === 'debtors' && isset($total_shop_balance)): ?>
            <div class="summary-card-section">
                <div class="summary-title">Debtors Summary Breakdown</div>
                <div class="table-responsive-wrapper">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Debtor Category</th>
                                <th style="text-align: right;">Total Outstanding Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Shop Debtors Total Balance</strong></td>
                                <td style="text-align: right; font-weight: 700; color: #d97706;">UGX <?= number_format($total_shop_balance, 2) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Customer File Debtors Total Balance</strong></td>
                                <td style="text-align: right; font-weight: 700; color: #0284c7;">UGX <?= number_format($total_customer_balance, 2) ?></td>
                            </tr>
                            <tr style="background-color: #f8fafc; font-weight: bold; font-size: 0.95rem;">
                                <td style="text-align: right; font-weight: 700;">Total Consolidated Debtors Balance:</td>
                                <td style="text-align: right; font-weight: 800; color: #dc2626; font-size: 1rem;">UGX <?= number_format($total_shop_balance + $total_customer_balance, 2) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <button class="print-btn" onclick="window.print()">Print Report</button>
    </div>
</body>
</html>
