<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Remote Ordering Configuration
define('QR_EXPIRY_HOURS', 24);
define('ORDER_PREFIX', 'ORD');
define('API_VERSION', 'v1');
define('ENABLE_CORS', true);

// Environment Detection
$is_localhost = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) || (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'localhost');

// Base URLs - UPDATE THESE IF NEEDED
if ($is_localhost) {
    define('BASE_URL', 'http://localhost/shop-system');
} else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    define('BASE_URL', $protocol . $_SERVER['HTTP_HOST']);
}
define('API_BASE_URL', BASE_URL . '/api');
define('CUSTOMER_SITE_URL', BASE_URL . '/customer');
define('ASSETS_URL', BASE_URL . '/assets');

// QR Code Settings
define('QR_CODE_SIZE', 300);
define('QR_CODE_MARGIN', 10);

// File Paths
define('ROOT_PATH', dirname(__DIR__));
define('UPLOADS_PATH', ROOT_PATH . '/uploads');

// Security
define('API_SECRET_KEY', 'your-secret-key-here-change-in-production');

// Timezone
date_default_timezone_set('Africa/Kampala');

// Error Reporting (disable in production)
if ($is_localhost) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
?>
