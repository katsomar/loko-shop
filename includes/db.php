<?php
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

// Run the migration/daily check
ensure_daily_products($conn);
?>