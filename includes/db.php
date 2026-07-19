<?php
// Set timezone globally to align with local Kampala time
date_default_timezone_set('Africa/Kampala');

// Determine if we are running locally or on the production server
$is_localhost = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) || (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'localhost');

if ($is_localhost) {
    // Localhost Development Settings
    $host = 'localhost';
    $rootname = 'root';
    $password = '';
    $database = 'shop_system';
} else {
    // InfinityFree Production Settings
    $host = 'sql300.infinityfree.com';
    $rootname = 'if0_42123248';
    $password = 'Sx0NIwEsXXDOj';
    $database = 'if0_42123248_shop_system';
}

$conn = mysqli_connect($host, $rootname, $password, $database);
if (!$conn) {
    if ($is_localhost) {
        die("Database connection failed: " . mysqli_connect_error());
    } else {
        die("Database connection failed. Please try again later.");
    }
}

// Helper function to handle daily product stock replication and migrations
if (!function_exists('ensure_daily_products')) {
function ensure_daily_products($conn) {
    $today = date('Y-m-d');
    
    // 1. Check if column 'date' exists in products table. If not, run migration
    $check_col = mysqli_query($conn, "SHOW COLUMNS FROM `products` LIKE 'date'");
    if ($check_col && mysqli_num_rows($check_col) == 0) {
        // Migration step 1: Add date column
        mysqli_query($conn, "ALTER TABLE `products` ADD COLUMN `date` DATE NULL");
        
        // Migration step 2: Update existing records to today's date
        mysqli_query($conn, "UPDATE `products` SET `date` = '$today' WHERE `date` IS NULL");
        
        // Migration step 3: Modify date to be NOT NULL
        mysqli_query($conn, "ALTER TABLE `products` MODIFY COLUMN `date` DATE NOT NULL");
        
        // Migration step 4: Drop old unique index if it exists
        $check_idx = mysqli_query($conn, "SHOW INDEX FROM `products` WHERE Key_name = 'unique_barcode_branch'");
        if ($check_idx && mysqli_num_rows($check_idx) > 0) {
            mysqli_query($conn, "ALTER TABLE `products` DROP INDEX `unique_barcode_branch`");
        }
        
        // Migration step 5: Add new unique index with date
        mysqli_query($conn, "ALTER TABLE `products` ADD UNIQUE KEY `unique_barcode_branch_date` (`barcode`, `branch-id`, `date`)");
    }
    
    // 2. Ensure product rows exist for today's date
    $check_today = mysqli_query($conn, "SELECT 1 FROM `products` WHERE `date` = '$today' LIMIT 1");
    if ($check_today && mysqli_num_rows($check_today) == 0) {
        // Find the most recent date before today that has product records
        $max_res = mysqli_query($conn, "SELECT MAX(`date`) AS max_date FROM `products` WHERE `date` < '$today'");
        if ($max_res) {
            $row = mysqli_fetch_assoc($max_res);
            $max_date = $row['max_date'];
            
            if ($max_date) {
                // Copy products sequentially for each missing day
                $start = new DateTime($max_date);
                $end = new DateTime($today);
                $interval = new DateInterval('P1D');
                $period = new DatePeriod($start->modify('+1 day'), $interval, $end->modify('+1 day'));
                
                $prev_date = $max_date;
                foreach ($period as $dt) {
                    $target_date = $dt->format('Y-m-d');
                    
                    $chk = mysqli_query($conn, "SELECT 1 FROM `products` WHERE `date` = '$target_date' LIMIT 1");
                    if ($chk && mysqli_num_rows($chk) == 0) {
                        $copy_query = "
                            INSERT INTO `products` (
                                `name`, `barcode`, `category`, `buying-price`, `selling-price`, 
                                `opening_stock`, `incoming_stock`, `outgoing`, `damages`, `stock`, 
                                `branch-id`, `business_id`, `expiry_date`, `sms_sent`, `location`, 
                                `image_path`, `visible`, `date`
                            )
                            SELECT 
                                `name`, `barcode`, `category`, `buying-price`, `selling-price`,
                                `stock` AS `opening_stock`, 0 AS `incoming_stock`, 0 AS `outgoing`, 0 AS `damages`, `stock`,
                                `branch-id`, `business_id`, `expiry_date`, 0 AS `sms_sent`, `location`,
                                `image_path`, `visible`, '$target_date' AS `date`
                            FROM `products`
                            WHERE `date` = '$prev_date'
                        ";
                        mysqli_query($conn, $copy_query);
                    }
                    $prev_date = $target_date;
                }
            }
        }
    }
}
}

if (!function_exists('backfill_debtor_invoices')) {
function backfill_debtor_invoices($conn) {
    // 1. Backfill customer debtors from customer_transactions
    $ct_res = mysqli_query($conn, "
        SELECT ct.*, c.name 
        FROM customer_transactions ct 
        JOIN customers c ON ct.customer_id = c.id 
        WHERE ct.invoice_receipt_no LIKE 'INV-%'
    ");
    if ($ct_res) {
        while ($ct_row = mysqli_fetch_assoc($ct_res)) {
            $inv_no = $ct_row['invoice_receipt_no'];
            // Check if already in sales table
            $check = mysqli_query($conn, "SELECT 1 FROM sales WHERE invoice_no = '" . mysqli_real_escape_string($conn, $inv_no) . "' LIMIT 1");
            if ($check && mysqli_num_rows($check) == 0) {
                // Calculate quantity, cost and profit from products_bought json
                $qty_sum = 0;
                $cost_sum = 0;
                $expected_sum = 0;
                $products_json = $ct_row['products_bought'];
                $products_data = json_decode($products_json, true);
                if (is_array($products_data)) {
                    foreach ($products_data as $item) {
                        $p_qty = intval($item['quantity'] ?? $item['qty'] ?? 0);
                        $p_price = floatval($item['price'] ?? 0);
                        $qty_sum += $p_qty;
                        $expected_sum += $p_price * $p_qty;
                        
                        // Get cost price
                        $p_id = intval($item['id'] ?? 0);
                        $p_name = $item['name'] ?? $item['product'] ?? '';
                        if ($p_id > 0) {
                            $p_check = mysqli_query($conn, "SELECT `buying-price` FROM products WHERE id = $p_id LIMIT 1");
                            if ($p_check && $p_row = mysqli_fetch_assoc($p_check)) {
                                $cost_sum += floatval($p_row['buying-price']) * $p_qty;
                            }
                        } elseif ($p_name) {
                            $p_check = mysqli_query($conn, "SELECT `buying-price` FROM products WHERE name = '" . mysqli_real_escape_string($conn, $p_name) . "' LIMIT 1");
                            if ($p_check && $p_row = mysqli_fetch_assoc($p_check)) {
                                $cost_sum += floatval($p_row['buying-price']) * $p_qty;
                            }
                        }
                    }
                }
                
                // Get sold_by user id
                $sold_by_name = $ct_row['sold_by'];
                $sold_by_id = 1;
                $u_check = mysqli_query($conn, "SELECT id FROM users WHERE username = '" . mysqli_real_escape_string($conn, $sold_by_name) . "' LIMIT 1");
                if ($u_check && $u_row = mysqli_fetch_assoc($u_check)) {
                    $sold_by_id = intval($u_row['id']);
                }
                
                $amt_paid = floatval($ct_row['amount_paid']);
                $initial_profit = $amt_paid - $cost_sum;
                $branch_id = intval($ct_row['branch_id'] ?? $ct_row['branch-id'] ?? 1);
                $dt = $ct_row['date_time'];
                
                $ins_query = "
                    INSERT INTO sales (
                        `product-id`, `branch-id`, quantity, amount, `sold-by`, 
                        `cost-price`, total_profits, date, payment_method, invoice_no, products_json
                    ) VALUES (
                        0, $branch_id, $qty_sum, $amt_paid, $sold_by_id, 
                        $cost_sum, $initial_profit, '$dt', 'Customer File', '" . mysqli_real_escape_string($conn, $inv_no) . "', '" . mysqli_real_escape_string($conn, $products_json) . "'
                    )
                ";
                mysqli_query($conn, $ins_query);
            }
        }
    }
    
    // 2. Backfill shop debtors from debtors table
    $debtors_res = mysqli_query($conn, "SELECT * FROM debtors WHERE invoice_no LIKE 'INV-%'");
    if ($debtors_res) {
        while ($d_row = mysqli_fetch_assoc($debtors_res)) {
            $inv_no = $d_row['invoice_no'];
            // Check if already in sales table
            $check = mysqli_query($conn, "SELECT 1 FROM sales WHERE invoice_no = '" . mysqli_real_escape_string($conn, $inv_no) . "' LIMIT 1");
            if ($check && mysqli_num_rows($check) == 0) {
                $qty_sum = intval($d_row['quantity_taken']);
                $cost_sum = 0;
                $products_json = $d_row['products_json'];
                $products_data = json_decode($products_json, true);
                if (is_array($products_data)) {
                    foreach ($products_data as $item) {
                        $p_qty = intval($item['quantity'] ?? $item['qty'] ?? 0);
                        $p_id = intval($item['id'] ?? 0);
                        $p_name = $item['name'] ?? $item['product'] ?? '';
                        if ($p_id > 0) {
                            $p_check = mysqli_query($conn, "SELECT `buying-price` FROM products WHERE id = $p_id LIMIT 1");
                            if ($p_check && $p_row = mysqli_fetch_assoc($p_check)) {
                                $cost_sum += floatval($p_row['buying-price']) * $p_qty;
                            }
                        } elseif ($p_name) {
                            $p_check = mysqli_query($conn, "SELECT `buying-price` FROM products WHERE name = '" . mysqli_real_escape_string($conn, $p_name) . "' LIMIT 1");
                            if ($p_check && $p_row = mysqli_fetch_assoc($p_check)) {
                                $cost_sum += floatval($p_row['buying-price']) * $p_qty;
                            }
                        }
                    }
                }
                
                $sold_by_id = intval($d_row['created_by']);
                $amt_paid = floatval($d_row['amount_paid']);
                $initial_profit = $amt_paid - $cost_sum;
                $branch_id = intval($d_row['branch_id'] ?? $d_row['branch-id'] ?? 1);
                $dt = $d_row['created_at'];
                
                $ins_query = "
                    INSERT INTO sales (
                        `product-id`, `branch-id`, quantity, amount, `sold-by`, 
                        `cost-price`, total_profits, date, payment_method, invoice_no, products_json
                    ) VALUES (
                        0, $branch_id, $qty_sum, $amt_paid, $sold_by_id, 
                        $cost_sum, $initial_profit, '$dt', 'Debtor', '" . mysqli_real_escape_string($conn, $inv_no) . "', '" . mysqli_real_escape_string($conn, $products_json) . "'
                    )
                ";
                mysqli_query($conn, $ins_query);
            }
        }
    }

    // 3. Clean up any invalid dates (0000-00-00 00:00:00) in sales table
    $bad_sales = mysqli_query($conn, "SELECT id, invoice_no FROM sales WHERE date = '0000-00-00 00:00:00' OR date IS NULL");
    if ($bad_sales) {
        while ($s_row = mysqli_fetch_assoc($bad_sales)) {
            $sid = intval($s_row['id']);
            $inv = $s_row['invoice_no'];
            if ($inv) {
                // Try to find correct date from customer_transactions
                $ct_check = mysqli_query($conn, "SELECT date_time FROM customer_transactions WHERE invoice_receipt_no = '" . mysqli_real_escape_string($conn, $inv) . "' LIMIT 1");
                if ($ct_check && $ct_d = mysqli_fetch_assoc($ct_check)) {
                    $correct_date = $ct_d['date_time'];
                    mysqli_query($conn, "UPDATE sales SET date = '$correct_date' WHERE id = $sid");
                    continue;
                }
                // Try to find from debtors
                $d_check = mysqli_query($conn, "SELECT created_at FROM debtors WHERE invoice_no = '" . mysqli_real_escape_string($conn, $inv) . "' LIMIT 1");
                if ($d_check && $d_d = mysqli_fetch_assoc($d_check)) {
                    $correct_date = $d_d['created_at'];
                    mysqli_query($conn, "UPDATE sales SET date = '$correct_date' WHERE id = $sid");
                    continue;
                }
            }
        }
    }
}
}

// Ensure customer_transactions has payment_method column
$conn->query("ALTER TABLE customer_transactions ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) NULL");
if ($conn->errno) { @$conn->query("ALTER TABLE customer_transactions ADD COLUMN payment_method VARCHAR(50) NULL"); }

// Ensure products and sales tables columns support decimals
$conn->query("ALTER TABLE products MODIFY COLUMN damages DECIMAL(12,2) DEFAULT 0.00");
$conn->query("ALTER TABLE products MODIFY COLUMN stock DECIMAL(12,2) DEFAULT 0.00");
$conn->query("ALTER TABLE products MODIFY COLUMN opening_stock DECIMAL(12,2) DEFAULT 0.00");
$conn->query("ALTER TABLE products MODIFY COLUMN incoming_stock DECIMAL(12,2) DEFAULT 0.00");
$conn->query("ALTER TABLE products MODIFY COLUMN outgoing DECIMAL(12,2) DEFAULT 0.00");
$conn->query("ALTER TABLE sales MODIFY COLUMN quantity DECIMAL(12,2) DEFAULT 0.00");

// Auto-sync products stock column to opening_stock + incoming_stock - outgoing - damages
$conn->query("UPDATE products SET stock = (opening_stock + incoming_stock - outgoing - damages)");

// Run the migration/daily check
ensure_daily_products($conn);
backfill_debtor_invoices($conn);
?>