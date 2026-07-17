<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <!-- Change the favicon path below to your business logo file -->
  <link rel="icon" type="image/png" href="../uploads/19.png">
  <title>Bluecrest POS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../pages/assets/css/style.css" />
  <link rel="stylesheet" href="../assets/css/responsive.css" />
  <link rel="stylesheet" href="../pages/assets/css/header.css" />
  <!-- Add Google Fonts for tech logo style -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Nunito+Sans:wght@700&display=swap" rel="stylesheet">
</head>
<body>
<?php
// Get pending orders count (filtered by branch for staff)
$pending_orders_count = 0;
if (isset($_SESSION['user_id']) && isset($conn)) {
    $user_branch = $_SESSION['branch_id'] ?? null;
    $user_role = $_SESSION['role'] ?? null;
    
    // FIXED: Staff see only their branch orders, Admin/Manager see all
    if ($user_role === 'staff' && $user_branch) {
        $orders_stmt = $conn->prepare("SELECT COUNT(*) as count FROM remote_orders WHERE status = 'pending' AND branch_id = ?");
        $orders_stmt->bind_param("i", $user_branch);
    } else {
        $orders_stmt = $conn->prepare("SELECT COUNT(*) as count FROM remote_orders WHERE status = 'pending'");
    }
    $orders_stmt->execute();
    $orders_result = $orders_stmt->get_result()->fetch_assoc();
    $pending_orders_count = $orders_result['count'] ?? 0;
    $orders_stmt->close();
}
?>
<header class="main-header">
  <div class="logo-area">
    <img src="../uploads/beg1.png" alt="Logo" class="logo-img" />
    <span class="logo-text">Skyrix Technologies</span>
  </div>
  <div class="header-actions">
    <div class="theme-switch">
      <input type="checkbox" id="themeToggle" />
      <label for="themeToggle">
        <i class="fa-solid fa-moon"></i>
      </label>
    </div>
    <!-- NEW: Orders Icon (before notifications) -->
    <a href="../pages/order_notifications.php" class="position-relative me-3" style="text-decoration: none;">
      <i class="fas fa-shopping-bag text-white" style="font-size: 1.3rem;"></i>
      <?php if ($pending_orders_count > 0): ?>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
          <?= $pending_orders_count > 99 ? '99+' : $pending_orders_count ?>
        </span>
      <?php endif; ?>
    </a>
    <div class="notification-icon position-relative">
      <a href="../pages/notification.php" class="text-white text-decoration-none">
        <i class="fa-solid fa-bell"></i>
        <span id="notification-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">
          0
        </span>
      </a>
    </div>
    <!-- Hamburger icon should be after theme toggle -->
    <button id="sidebarToggle" class="hamburger d-lg-none ms-auto" aria-label="Open sidebar">
      <span class="bar bar1"></span>
      <span class="bar bar2"></span>
      <span class="bar bar3"></span>
    </button>
  </div>
</header>
<script src="../pages/assets/js/header.js"></script>
</body>
</html>
