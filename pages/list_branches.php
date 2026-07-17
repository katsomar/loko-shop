<?php
include '../includes/auth.php';
require_role(['admin', 'manager']);
include '../pages/sidebar.php';
include '../includes/header.php';
include '../includes/db.php';

// FIXED: Remove business_id check - show all branches for admin/manager
$user_role = $_SESSION['role'];

// Fetch all branches (admin/manager can see all)
$sql = "SELECT id, name, location, contact 
        FROM branch 
        ORDER BY id DESC";

$result = mysqli_query($conn, $sql);
?>

<!-- Link external CSS -->
<link rel="stylesheet" href="assets/css/list_branches.css">

<div class="container-fluid mt-5">
    <div class="d-flex justify-content-end align-items-center mb-4">
        <a href="create_branch.php" class="btn add-branch-btn">+ Add Branch</a>
    </div>

    <!-- Success message after deletion -->
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success text-center">Branch deleted successfully.</div>
    <?php endif; ?>

    <!-- Responsive Table Card for Small Devices -->
    <div class="d-block d-md-none mb-4">
      <div class="card transactions-card" style="border-left: 4px solid teal;">
        <div class="card-body">
          <div class="table-responsive-sm">
            <div class="transactions-table">
              <table>
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Branch Name</th>
                    <th>Location</th>
                    <th>Contact</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                  <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                      <td><?= $row['id']; ?></td>
                      <td><?= htmlspecialchars($row['name']); ?></td>
                      <td><?= htmlspecialchars($row['location']); ?></td>
                      <td><?= htmlspecialchars($row['contact'] ?? 'N/A'); ?></td>
                      <td>
                        <a href="branch.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-info" title="View">
                          <i class="fa fa-eye"></i>
                        </a>
                        <a href="branch_edit.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                          <i class="fa fa-edit"></i>
                        </a>
                        <a href="branch_delete.php?id=<?= $row['id']; ?>" 
                           class="btn btn-sm btn-danger"
                           title="Delete"
                           onclick="return confirm('Are you sure you want to delete this branch?');">
                           <i class="fa fa-trash"></i>
                        </a>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="5" class="text-center text-muted">No branches found</td>
                  </tr>
                <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Table for medium and large devices -->
    <div class="transactions-table d-none d-md-block">
      <div class="card transactions-card" style="border-left: 4px solid teal;">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Branch Name</th>
                    <th>Location</th>
                    <th>Contact</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            // Reset result pointer for large devices table
            mysqli_data_seek($result, 0);
            if (mysqli_num_rows($result) > 0):
                while ($row = mysqli_fetch_assoc($result)):
            ?>
                <tr>
                    <td><?= $row['id']; ?></td>
                    <td><?= htmlspecialchars($row['name']); ?></td>
                    <td><?= htmlspecialchars($row['location']); ?></td>
                    <td><?= htmlspecialchars($row['contact'] ?? 'N/A'); ?></td>
                    <td>
                        <a href="branch.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-info">View</a>
                        <a href="branch_edit.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="branch_delete.php?id=<?= $row['id']; ?>" 
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Are you sure you want to delete this branch?');">
                           Delete
                        </a>
                    </td>
                </tr>
            <?php endwhile; else: ?>
                <tr>
                    <td colspan="5" class="text-center text-muted">No branches found</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
      </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
