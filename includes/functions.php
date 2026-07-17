<?php
require_once __DIR__ . '/../config/config.php';

// Database Connection
function getDBConnection() {
    global $conn;
    return $conn;
}

function closeDBConnection($connection) {
    mysqli_close($connection);
}

// Sanitize Input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Validate Phone Number
function validatePhone($phone) {
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    return (strlen($phone) >= 10 && strlen($phone) <= 15);
}


// Check if user is authenticated (POS)
function isAuthenticated() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Redirect if not authenticated
function requireAuth() {
    if (!isAuthenticated()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

// JSON Response
function jsonResponse($success, $message, $data = null, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Format Currency
function formatCurrency($amount) {
    return 'UGX ' . number_format($amount, 0);
}


?>
