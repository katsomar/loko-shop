<?php
include '../includes/db.php';

$error = "";
$success = "";
$message = "";
$message_class = "";
$isSuperSignup = isset($_GET['super']); // true only if ?super in URL

// Fetch active businesses for selection
$active_businesses = [];
$biz_res = $conn->query("SELECT id, name FROM businesses WHERE status = 'active' ORDER BY name ASC");
if ($biz_res) {
    while ($row = $biz_res->fetch_assoc()) {
        $active_businesses[] = $row;
    }
}

function getInitials($string) {
    $words = explode(" ", trim($string));
    $initials = "";

    foreach ($words as $w) {
        if ($w !== "") {
            $initials .= strtoupper($w[0]);
        }
    }

    return $initials;
}

function generateUniqueBusinessCode($prefix, $conn) {
    while (true) {
        // random 4-digit number
        $number = rand(1000, 9999);

        // final code
        $business_code = $prefix . $number;

        // check database if it exists
        $stmt = $conn->prepare("SELECT id FROM businesses WHERE business_code = ?");
        $stmt->bind_param("s", $business_code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return $business_code; // Unique → OK
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    // Collect POST data safely
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $new_business_name = trim($_POST['new_business_name'] ?? '');
    $new_business_address = trim($_POST['new_business_address'] ?? '');
    $new_business_phone = trim($_POST['new_business_phone'] ?? '');
    $business_code = "";
    $business_id = "";

    // Basic validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($role) || empty($phone)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Email uniqueness check
        if (empty($error)) {
            $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check->bind_param("s", $email);
            $check->execute();
            $check->store_result();
            if ($check->num_rows > 0) {
                $error = "An account with this email already exists.";
            }
            $check->close();
        }

        // Username uniqueness check
        if (empty($error)) {
            $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $check->bind_param("s", $username);
            $check->execute();
            $check->store_result();
            if ($check->num_rows > 0) {
                $error = "This username is already taken. Please choose another one.";
            }
            $check->close();
        }

        // ✅ Admin: handle business registration
        if (empty($error) && $role === 'admin') {
            $business_option = $_POST['business_option'] ?? 'new';
            if ($business_option === 'new') {
                if (!empty($new_business_name) && !empty($new_business_address)) {
                    $name_initials = getInitials($new_business_name);
                    $address_initials = getInitials($new_business_address);
                    $prefix = $name_initials . $address_initials;
                    $business_code = generateUniqueBusinessCode($prefix, $conn);

                    $stmt = $conn->prepare("INSERT INTO businesses (business_code, name, address, phone, admin_name, email) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssssss", $business_code, $new_business_name, $new_business_address, $new_business_phone, $username, $email);
                    $stmt->execute();
                    $stmt->close();                
                } else {
                    $error = "Business Name and Address are required for registering a new business.";
                }
                
                if (empty($error)) {
                    $stmt = $conn->prepare("SELECT id FROM businesses WHERE business_code = ?");
                    $stmt->bind_param("s", $business_code);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    $business_id = $row['id'] ?? null;
                    $stmt->close();
                }
            } else {
                $business_id = isset($_POST['existing_business_id']) ? intval($_POST['existing_business_id']) : 0;
                if (empty($business_id)) {
                    $error = "Please select an existing business.";
                }
            }
        }

        // Insert user if still no errors
        if (empty($error)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            if ($role === 'admin') {
                $status = 'pending';
                $stmt2 = $conn->prepare(
                    "INSERT INTO users (username, email, password, role, phone, `branch-id`, business_id, status)
                     VALUES (?, ?, ?, ?, ?, 0, ?, ?)"
                );
                $stmt2->bind_param("sssssis", $username, $email, $hash, $role, $phone, $business_id, $status);
            } else {
                // staff or manager: status pending, no branch, no business code
                $status = 'pending';
                $stmt2 = $conn->prepare(
                    "INSERT INTO users (username, email, password, role, phone, `branch-id`, business_id, status)
                     VALUES (?, ?, ?, ?, ?, 0, NULL, ?)"
                );
                $stmt2->bind_param("ssssss", $username, $email, $hash, $role, $phone, $status);
            }

            if ($stmt2->execute()) {
                $success = "Registration request submitted successfully! Your account is pending admin approval.";
            } else {
                $error = "Database error: " . $stmt2->error;
            }
            $stmt2->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Business System - Sign Up</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/responsive.css">
  <link rel="stylesheet" href="../pages/assets/css/signup.css">
</head>
<body>
<div class="background-shapes"><div class="shape"></div><div class="shape"></div><div class="shape"></div></div>

<div class="signup-card">
    <div class="signup-header text-center mb-3">
        <img src="../uploads/2.png" alt="Logo" width="60">
        <h3>Create Account</h3>
        <p>Register to access the Business System</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger py-2 text-center mb-2"><?= htmlspecialchars($error) ?></div>
    <?php elseif (!empty($success)): ?>
        <div class="alert alert-success py-2 text-center mb-2"><?= htmlspecialchars($success) ?></div>
        <div class="text-center mb-2"><a href="../index.php" class="btn btn-sm btn-success rounded-pill px-3">Go to Login</a></div>
    <?php endif; ?>

    <form action="signup.php" method="POST" class="needs-validation" novalidate>
        <div class="mb-2">
            <label class="form-label">Username</label>
            <input name="username" type="text" class="form-control form-control-sm" required>
        </div>

        <div class="mb-2">
            <label class="form-label">Email Address</label>
            <input name="email" type="email" class="form-control form-control-sm" required>
        </div>

        <div class="row g-2">
            <div class="col-md-6">
                <label class="form-label">Password</label>
                <input name="password" type="password" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Confirm Password</label>
                <input name="confirm_password" type="password" class="form-control form-control-sm" required>
            </div>
        </div>

        <div class="row g-2 mt-1">
            <div class="col-md-6">
                <label class="form-label">Phone Number</label>
                <input name="phone" type="tel" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Role</label>
                <select id="role" name="role" class="form-select form-select-sm" required>
                    <option value="" disabled selected>Select role</option>
                    <option value="admin">Admin</option>
                    <option value="manager">Manager</option>
                    <option value="staff">Staff</option>
                    <?php if ($isSuperSignup): ?><option value="super">Super</option><?php endif; ?>
                </select>
            </div>
        </div>



        <!-- Admin Business Fields -->
        <div class="mt-2 admin-business-fields" style="display:none;">
            <div class="mb-2">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="business_option" id="biz_opt_new" value="new" checked>
                    <label class="form-check-label fw-semibold" for="biz_opt_new">New Business</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="business_option" id="biz_opt_existing" value="existing">
                    <label class="form-check-label fw-semibold" for="biz_opt_existing">Select Existing Business</label>
                </div>
            </div>

            <!-- New Business Inputs -->
            <div id="new-business-section" class="new-business-fields">
                <label class="form-label">New Business Name</label>
                <input name="new_business_name" type="text" class="form-control form-control-sm mb-2">
                <label class="form-label">Business Address</label>
                <input name="new_business_address" type="text" class="form-control form-control-sm mb-2">
                <label class="form-label">Business Phone</label>
                <input name="new_business_phone" type="text" class="form-control form-control-sm mb-2">
            </div>

            <!-- Existing Business Selection -->
            <div id="existing-business-section" class="existing-business-fields" style="display:none;">
                <label class="form-label">Select Existing Business</label>
                <select name="existing_business_id" class="form-select form-select-sm mb-2">
                    <option value="" disabled selected>-- Choose Business --</option>
                    <?php foreach ($active_businesses as $biz): ?>
                        <option value="<?= htmlspecialchars($biz['id']) ?>"><?= htmlspecialchars($biz['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="divider"></div>
        <button type="submit" name="signup" class="btn btn-corporate w-100 mb-2">Create Account</button>
        <div class="text-center mt-2"><p>Already have an account? <a href="../index.php">Login here</a></p></div>
    </form>
</div>

<script src="../pages/assets/js/signup.js"></script>
</body>
</html>
