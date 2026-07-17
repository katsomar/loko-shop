<?php
session_start();
include "includes/db.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Fetch user from database
    $query = "SELECT id, username, password, role, `branch-id`, business_id, status FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($user && password_verify($password, $user["password"])) {
        $status = $user['status'] ?? 'active';
        if ($status === 'pending') {
            $error = '⚠️ Your account is pending approval by the Admin.';
        } elseif ($status === 'suspended') {
            $error = '⚠️ Your account is suspended.';
        } else {
            // Store session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = strtolower(trim($user['role']));
            $_SESSION['branch_id'] = $user['branch-id'];
            $_SESSION['business_id'] = $user['business_id'];
            
            // NEW: Reset notification popup flag on login
            unset($_SESSION['shown_login_notifications']);
          
            // Redirect based on role
            if ($_SESSION['role'] === 'super') {
                header('Location: pages/super.php');
            } elseif ($_SESSION['role'] === 'admin') {
                header('Location: pages/admin_dashboard.php');
            } elseif ($_SESSION['role'] === 'manager') {
                header('Location: pages/manager_dashboard.php');
            } elseif ($_SESSION['role'] === 'staff') {
                header('Location: pages/staff_dashboard.php');
            } else {
                $error = 'Unknown role';
            }
            exit;
        }
    } else {
        $error = 'Invalid username or password';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Business System - Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/responsive.css">
  <link rel="stylesheet" href="pages/assets/css/login.css">
</head>
<body>

  <div class="login-card">
    <div class="login-header">
      <img src="uploads/beg1.png" alt="Logo" width="100" height="100">
      <p>Secure Login Portal</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger text-center py-2">
        <?= htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>

    <form action="index.php" method="POST">
      <div class="mb-3">
        <label for="username" class="form-label fw-semibold">Username</label>
        <input type="text" class="form-control" id="username" name="username" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label fw-semibold">Password</label>
        <input type="password" class="form-control" id="password" name="password" required>
      </div>
      <button type="submit" name="login" class="btn btn-corporate w-100">Login</button>
    </form>

    <div class="text-center mt-3">
      <p>Don't have an account? <a href="auth/signup.php">Sign up here</a></p>
    </div>

    <div class="footer-text">
      © <?= date("Y"); ?> Bluecrest Technologies. All Rights Reserved.
    </div>
  </div>

</body>
</html>
