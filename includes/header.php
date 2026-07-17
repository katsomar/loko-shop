<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <!-- Change the favicon path below to your business logo file -->
  <link rel="icon" type="image/png" href="../uploads/19.png">
  <title>Skyrix Technologies POS</title>
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
