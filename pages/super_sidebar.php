<?php
if (!isset($_SESSION['role'])) {
    header("Location: ../index.php");
    exit();
}
$role = $_SESSION['role'];
?>

<!-- Link external CSS -->
<link rel="stylesheet" href="assets/css/super_sidebar.css">

<div class="sidebar">
    <div class="sidebar-title">Super Dashboard</div>
    <ul class="sidebar-nav">
        <li><a href="admin_dashboard.php"><i class="fa-solid fa-crown"></i> Admin</a></li>
        <li><a href="manager_dashboard.php"><i class="fa-solid fa-user-tie"></i> Manager</a></li>
        <li><a href="staff_dashboard.php"><i class="fa-solid fa-user"></i> Staff</a></li>
        <li><a href="manage_business.php"><i class="fa-solid fa-briefcase"></i> Manage Businesses</a></li>
        <li><a href="manage_admin.php"><i class="fa-solid fa-users-gear"></i> Manage Admins</a></li>
        <li><a href="add_admin.php"><i class="fa-solid fa-user-plus"></i> Add Admins</a></li>
        <li><a href="subscription.php"><i class="fa-solid fa-credit-card"></i> Subscription</a></li>
        <li><a href="super_report.php"><i class="fa-solid fa-chart-line"></i> Reports & Analytics</a></li>
        <li><a href="system_updates.php"><i class="fa-solid fa-wrench"></i> System Updates</a></li>
        <li style="margin-top:2rem;">
            <a href="../auth/logout.php" class="text-danger fw-bold"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </li>
    </ul>
</div>

<div class="main-container">
