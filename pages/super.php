<?php
include '../includes/db.php';
include '../includes/auth.php';
require_role(["super"]);
include '../pages/super_sidebar.php';
include '../includes/header.php';

// 🔹 Count system updates from log
$logFile = '../logs/system.log';
$pendingUpdates = 0;
if (file_exists($logFile)) {
    $logEntries = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $pendingUpdates = count($logEntries);
}

// 🔹 Example stats
$totalBranches = $conn->query("SELECT COUNT(*) AS count FROM branch")->fetch_assoc()['count'] ?? 0;
$totalManagers = $conn->query("SELECT COUNT(*) AS count FROM users WHERE role = 'manager'")->fetch_assoc()['count'] ?? 0;
$totalProducts = $conn->query("SELECT COUNT(*) AS count FROM products WHERE `date` = CURRENT_DATE()")->fetch_assoc()['count'] ?? 0;
$totalSales = $conn->query("SELECT COUNT(*) AS count FROM sales")->fetch_assoc()['count'] ?? 0;
$totalSubscriptions = $conn->query("SELECT COUNT(*) AS count FROM businesses WHERE subscription_status = 'active'")->fetch_assoc()['count'] ?? 0;
?>

<!-- Link external CSS -->
<link rel="stylesheet" href="assets/css/super.css">

<div class="dashboard-container">
    <div class="dashboard-header">
        <h2>Super Admin Dashboard</h2>
        <a href="../auth/logout.php" class="btn btn-dashboard">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </div>

    <div class="stats-grid">
        <div class="card">
            <div class="card-body">
                <i class="fa-solid fa-building"></i>
                <h5 class="card-title">Total Branches</h5>
                <h3><?php echo $totalBranches; ?></h3>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <i class="fa-solid fa-users"></i>
                <h5 class="card-title">Total Managers</h5>
                <h3><?php echo $totalManagers; ?></h3>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <i class="fa-solid fa-cubes"></i>
                <h5 class="card-title">Total Products</h5>
                <h3><?php echo $totalProducts; ?></h3>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <i class="fa-solid fa-cart-shopping"></i>
                <h5 class="card-title">Total Sales</h5>
                <h3><?php echo $totalSales; ?></h3>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <i class="fa-solid fa-star"></i>
                <h5 class="card-title">Active Subscriptions</h5>
                <h3><?php echo $totalSubscriptions; ?></h3>
                <a href="../pages/subscriptions.php" class="btn btn-sm btn-dashboard mt-2">
                    Manage
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <i class="fa-solid fa-gear"></i>
                <h5 class="card-title">System Updates Logged</h5>
                <h3><?php echo $pendingUpdates; ?></h3>
                <a href="../pages/system_updates.php" class="btn btn-sm btn-dashboard mt-2">
                    View Logs
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
