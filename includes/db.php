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
?>