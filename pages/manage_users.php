<?php
include '../includes/db.php';
include '../includes/auth.php';
require_role(["admin"]);

$user_id     = $_SESSION['user_id'];
$user_role   = $_SESSION['role'];
$business_id = $_SESSION['business_id'] ?? 1;

$message = "";
$message_class = "";

// -----------------------------------------------------------------
// POST HANDLERS FOR ACTIONS
// -----------------------------------------------------------------

// 1. Approve User Request
if (isset($_POST['approve_user'])) {
    $target_user_id = intval($_POST['user_id']);
    $role = $_POST['role'] ?? 'staff';
    $branch_id = intval($_POST['branch_id']);
    $status = 'active';

    if ($target_user_id > 0) {
        $stmt = $conn->prepare("UPDATE users SET role = ?, `branch-id` = ?, business_id = ?, status = ? WHERE id = ?");
        $stmt->bind_param("siisi", $role, $branch_id, $business_id, $status, $target_user_id);
        if ($stmt->execute()) {
            $message = "✅ User approved and activated successfully!";
            $message_class = "alert-success";
        } else {
            $message = "❌ Error approving user: " . $conn->error;
            $message_class = "alert-danger";
        }
        $stmt->close();
    }
}

// 2. Toggle Status (Suspend / Reactivate)
if (isset($_POST['toggle_status'])) {
    $target_user_id = intval($_POST['user_id']);
    $new_status = $_POST['status'] ?? 'active';

    if ($target_user_id > 0 && $target_user_id !== $user_id) {
        $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ? AND business_id = ?");
        $stmt->bind_param("sii", $new_status, $target_user_id, $business_id);
        if ($stmt->execute()) {
            $message = "✅ User status updated successfully!";
            $message_class = "alert-success";
        } else {
            $message = "❌ Error updating status: " . $conn->error;
            $message_class = "alert-danger";
        }
        $stmt->close();
    }
}

// 3. Edit User Details
if (isset($_POST['edit_user'])) {
    $target_user_id = intval($_POST['user_id']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = intval($_POST['phone']);
    $role = $_POST['role'];
    $branch_id = intval($_POST['branch_id']);

    if ($target_user_id > 0 && !empty($username) && !empty($email)) {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, phone = ?, role = ?, `branch-id` = ? WHERE id = ? AND business_id = ?");
        $stmt->bind_param("ssisiii", $username, $email, $phone, $role, $branch_id, $target_user_id, $business_id);
        if ($stmt->execute()) {
            $message = "✅ User details updated successfully!";
            $message_class = "alert-success";
        } else {
            $message = "❌ Error updating user: " . $conn->error;
            $message_class = "alert-danger";
        }
        $stmt->close();
    }
}

// 4. Delete User Credentials
if (isset($_POST['delete_user'])) {
    $target_user_id = intval($_POST['user_id']);

    if ($target_user_id > 0 && $target_user_id !== $user_id) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND business_id = ?");
        $stmt->bind_param("ii", $target_user_id, $business_id);
        if ($stmt->execute()) {
            $message = "✅ User credentials deleted successfully!";
            $message_class = "alert-success";
        } else {
            $message = "❌ Error deleting user: " . $conn->error;
            $message_class = "alert-danger";
        }
        $stmt->close();
    }
}

// -----------------------------------------------------------------
// AJAX GET DETAILS FOR EDIT MODAL
// -----------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'get_user_details') {
    while (ob_get_level()) ob_end_clean();
    ob_start();
    header('Content-Type: application/json; charset=utf-8');
    
    $target_user_id = intval($_POST['user_id'] ?? 0);
    $stmt = $conn->prepare("SELECT id, username, email, phone, role, `branch-id` FROM users WHERE id = ? AND business_id = ?");
    $stmt->bind_param("ii", $target_user_id, $business_id);
    $stmt->execute();
    $u = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($u) {
        echo json_encode(['success' => true, 'user' => $u]);
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found']);
    }
    ob_end_flush();
    exit;
}

// -----------------------------------------------------------------
// FETCH LISTS FOR VIEWS
// -----------------------------------------------------------------

// Fetch branches for assign select lists
$branches_query = $conn->query("SELECT id, name FROM branch WHERE `business_id` = $business_id ORDER BY name ASC");
$branches = [];
while ($b = $branches_query->fetch_assoc()) {
    $branches[] = $b;
}

// Fetch pending approval users system-wide
$pending_res = $conn->query("SELECT * FROM users WHERE status = 'pending' ORDER BY id DESC");
$pending_users = [];
if ($pending_res) {
    while ($row = $pending_res->fetch_assoc()) {
        $pending_users[] = $row;
    }
}

// Fetch active & suspended users belonging to this business
$active_res = $conn->query("
    SELECT u.*, b.name AS branch_name 
    FROM users u 
    LEFT JOIN branch b ON u.`branch-id` = b.id 
    WHERE u.business_id = $business_id AND u.id != $user_id AND u.status IN ('active', 'suspended')
    ORDER BY u.id DESC
");
$active_users = [];
if ($active_res) {
    while ($row = $active_res->fetch_assoc()) {
        $active_users[] = $row;
    }
}

// Fetch user counts for summary boxes
$count_active = count($active_users);
$count_pending = count($pending_users);
$count_suspended = 0;
foreach ($active_users as $u) {
    if ($u['status'] === 'suspended') $count_suspended++;
}

include '../pages/sidebar.php';
include '../includes/header.php';
?>

<link rel="stylesheet" href="assets/css/product.css">

<div class="container-fluid mt-4" style="max-width: 100vw; overflow-x: hidden; padding-left: 1rem; padding-right: 1rem;">
    <?php if ($message): ?>
        <div class="alert <?= $message_class ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Summary Widgets Grid -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card p-3" style="border-left: 4px solid #2ecc71; background:#fff;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1 text-uppercase small font-weight-bold">Active Staff</h6>
                        <h3 class="mb-0 fw-bold" style="color:#2ecc71;"><?= $count_active - $count_suspended ?></h3>
                    </div>
                    <i class="fa fa-users fa-2x text-muted opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3" style="border-left: 4px solid #f39c12; background:#fff;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1 text-uppercase small font-weight-bold">Pending Approvals</h6>
                        <h3 class="mb-0 fw-bold" style="color:#f39c12;"><?= $count_pending ?></h3>
                    </div>
                    <i class="fa fa-user-plus fa-2x text-muted opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3" style="border-left: 4px solid #e74c3c; background:#fff;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1 text-uppercase small font-weight-bold">Suspended Accounts</h6>
                        <h3 class="mb-0 fw-bold" style="color:#e74c3c;"><?= $count_suspended ?></h3>
                    </div>
                    <i class="fa fa-user-slash fa-2x text-muted opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Approvals Queue -->
    <?php if ($count_pending > 0): ?>
    <div class="card mb-4" style="border-left: 4px solid #f39c12;">
        <div class="card-header title-card" style="background:#f39c12; color:#fff;">
            <span>⏳ Pending User Sign-Up Requests</span>
        </div>
        <div class="card-body">
            <div class="transactions-table">
                <table>
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email Address</th>
                            <th>Phone Number</th>
                            <th>Requested Role</th>
                            <th>Request Date</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_users as $p): ?>
                            <tr>
                                <td><?= htmlspecialchars($p['username']) ?></td>
                                <td><?= htmlspecialchars($p['email']) ?></td>
                                <td><?= htmlspecialchars($p['phone']) ?></td>
                                <td><span class="badge bg-secondary"><?= ucfirst($p['role']) ?></span></td>
                                <td><?= htmlspecialchars($p['created_at']) ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-success approve-btn" 
                                            data-id="<?= $p['id'] ?>" 
                                            data-name="<?= htmlspecialchars($p['username']) ?>"
                                            data-role="<?= $p['role'] ?>">
                                        <i class="fa fa-check"></i> Approve
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Manage Existing Users -->
    <div class="card mb-4" style="border-left: 4px solid teal;">
        <div class="card-header title-card d-flex justify-content-between align-items-center">
            <span>👥 Registered User Credentials</span>
            <input type="text" id="userSearchInput" class="form-control form-control-sm" placeholder="Search users..." style="width:220px;">
        </div>
        <div class="card-body">
            <div class="transactions-table">
                <table id="usersTable">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email Address</th>
                            <th>Phone Number</th>
                            <th>User Role</th>
                            <th>Assigned Branch</th>
                            <th>Account Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($active_users) > 0): ?>
                            <?php foreach ($active_users as $u): 
                                $statusBadge = ($u['status'] === 'active') ? 'bg-success' : 'bg-danger';
                            ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($u['username']) ?></td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td><?= htmlspecialchars($u['phone']) ?></td>
                                    <td><span class="badge bg-info text-dark"><?= ucfirst($u['role']) ?></span></td>
                                    <td><?= htmlspecialchars($u['branch_name'] ?: 'None') ?></td>
                                    <td><span class="badge <?= $statusBadge ?>"><?= ucfirst($u['status']) ?></span></td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <!-- Edit Details -->
                                            <button class="btn btn-sm btn-warning edit-user-btn" data-id="<?= $u['id'] ?>" title="Edit Credentials">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            
                                            <!-- Suspend/Reactivate -->
                                            <form method="POST" action="" style="display:inline;">
                                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                <?php if ($u['status'] === 'active'): ?>
                                                    <input type="hidden" name="status" value="suspended">
                                                    <button type="submit" name="toggle_status" class="btn btn-sm btn-outline-danger" title="Suspend User">
                                                        <i class="fa fa-ban"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <input type="hidden" name="status" value="active">
                                                    <button type="submit" name="toggle_status" class="btn btn-sm btn-outline-success" title="Activate User">
                                                        <i class="fa fa-check-circle"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </form>

                                            <!-- Delete User -->
                                            <button class="btn btn-sm btn-danger delete-user-btn" data-id="<?= $u['id'] ?>" data-name="<?= htmlspecialchars($u['username']) ?>" title="Delete Permanent">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">No staff or managers registered under your business.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Approve Request -->
<div class="modal fade" id="approveUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">✔️ Approve & Assign User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="user_id" id="approveUserId">
                    <p class="fw-semibold mb-3">Approve credentials for: <span id="approveUserName" class="text-success fw-bold"></span></p>

                    <div class="mb-3">
                        <label for="approveRole" class="form-label fw-semibold">Assign Role</label>
                        <select name="role" id="approveRole" class="form-select" required>
                            <option value="staff">Staff</option>
                            <option value="manager">Manager</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="approveBranch" class="form-label fw-semibold">Assign to Branch</label>
                        <select name="branch_id" id="approveBranch" class="form-select" required>
                            <option value="0">None / Head Office</option>
                            <?php foreach ($branches as $b): ?>
                                <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="approve_user" class="btn btn-success">Approve User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Credentials -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header" style="background:var(--primary-color);color:#fff;">
                    <h5 class="modal-title">✏️ Edit User Credentials</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="user_id" id="editUserId">

                    <div class="mb-3">
                        <label for="editUsername" class="form-label fw-semibold">Username</label>
                        <input type="text" name="username" id="editUsername" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="editEmail" class="form-label fw-semibold">Email Address</label>
                        <input type="email" name="email" id="editEmail" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="editPhone" class="form-label fw-semibold">Phone Number</label>
                        <input type="number" name="phone" id="editPhone" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="editRole" class="form-label fw-semibold">User Role</label>
                        <select name="role" id="editRole" class="form-select" required>
                            <option value="staff">Staff</option>
                            <option value="manager">Manager</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="editBranch" class="form-label fw-semibold">Assigned Branch</label>
                        <select name="branch_id" id="editBranch" class="form-select" required>
                            <option value="0">None / Head Office</option>
                            <?php foreach ($branches as $b): ?>
                                <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_user" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Delete User -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">⚠️ Confirm Delete User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="user_id" id="deleteUserId">
                    <p class="fw-semibold">Are you sure you want to permanently delete credentials for: <span id="deleteUserName" class="text-danger fw-bold"></span>?</p>
                    <p class="text-muted small">This action will revoke their login rights immediately. Transactions recorded by this user will remain in system logs.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_user" class="btn btn-danger">Delete Permanent</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Live Search
    const searchInput = document.getElementById('userSearchInput');
    const table = document.getElementById('usersTable');
    if (searchInput && table) {
        searchInput.addEventListener('input', function() {
            const filter = this.value.trim().toLowerCase();
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = (text.includes(filter) || filter === '') ? '' : 'none';
            });
        });
    }

    // 2. Approve Modal opener
    const approveModal = new bootstrap.Modal(document.getElementById('approveUserModal'));
    document.querySelectorAll('.approve-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const role = this.dataset.role;

            document.getElementById('approveUserId').value = id;
            document.getElementById('approveUserName').textContent = name;
            document.getElementById('approveRole').value = (role === 'staff' || role === 'manager') ? role : 'staff';
            
            approveModal.show();
        });
    });

    // 3. Edit Modal opener
    const editModal = new bootstrap.Modal(document.getElementById('editUserModal'));
    document.querySelectorAll('.edit-user-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const id = this.dataset.id;
            
            try {
                const formData = new FormData();
                formData.append('action', 'get_user_details');
                formData.append('user_id', id);

                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    const u = data.user;
                    document.getElementById('editUserId').value = u.id;
                    document.getElementById('editUsername').value = u.username;
                    document.getElementById('editEmail').value = u.email;
                    document.getElementById('editPhone').value = u.phone;
                    document.getElementById('editRole').value = u.role;
                    document.getElementById('editBranch').value = u['branch-id'];

                    editModal.show();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (err) {
                console.error(err);
                alert('Failed to load user details.');
            }
        });
    });

    // 4. Delete Modal opener
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
    document.querySelectorAll('.delete-user-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;

            document.getElementById('deleteUserId').value = id;
            document.getElementById('deleteUserName').textContent = name;

            deleteModal.show();
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>
