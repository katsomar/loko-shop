<?php
include '../includes/db.php';
include '../includes/auth.php';
require_role(['manager']);
include '../pages/sidebar.php';
include '../includes/header.php';

// NEW: Handle AJAX request to mark notifications as shown
if (isset($_POST['mark_notifications_shown'])) {
    $_SESSION['shown_login_notifications'] = true;
    exit;
}

// NEW: Include notification popup (shows once per login)
include '../includes/notification_popup.php';

// Total Sales Today
$sql = "SELECT SUM(amount) AS total FROM sales WHERE DATE(`date`) = CURDATE()";
$result = mysqli_query($conn, $sql);
$sales_today = ($row = mysqli_fetch_assoc($result)) ? $row['total'] ?? 0 : 0;

// Total Expenses Today
$sql = "SELECT SUM(amount) AS total FROM expenses WHERE DATE(`date`) = CURDATE()";
$result = mysqli_query($conn, $sql);
$expenses_today = ($row = mysqli_fetch_assoc($result)) ? $row['total'] ?? 0 : 0;

// Total Products
$sql = "SELECT COUNT(*) AS total FROM products WHERE business_id = '{$_SESSION['business_id']}' AND `date` = CURRENT_DATE()";
$result = mysqli_query($conn, $sql);
$total_products = ($row = mysqli_fetch_assoc($result)) ? $row['total'] : 0;

// Total Staff
$sql = "SELECT COUNT(*) AS total FROM users WHERE role = 'staff' AND business_id = '{$_SESSION['business_id']}'";
$result = mysqli_query($conn, $sql);
$total_staff = ($row = mysqli_fetch_assoc($result)) ? $row['total'] : 0;

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Branch filter
$selected_branch = $_GET['branch'] ?? '';
$whereClause = $selected_branch ? "WHERE s.`branch-id` = ".intval($selected_branch) : "";

// Count total sales for pagination
$count_sql = "SELECT COUNT(*) AS total FROM sales s $whereClause";
$count_result = mysqli_query($conn, $count_sql);
$total_sales = ($row = mysqli_fetch_assoc($count_result)) ? $row['total'] : 0;
$total_pages = ceil($total_sales / $limit);

// Fetch recent sales with branch info
$sales_sql = "
    SELECT s.date, p.name AS product_name, s.quantity, s.amount, u.username, b.name AS branch_name
    FROM sales s
    JOIN products p ON s.`product-id` = p.id
    JOIN users u ON s.`sold-by` = u.id
    JOIN branch b ON s.`branch-id` = b.id
    $whereClause
    ORDER BY s.date DESC
    LIMIT $limit OFFSET $offset
";
$sales_result = mysqli_query($conn, $sales_sql);

// Fetch all branches for dropdown
$branches = $conn->query("SELECT id, name FROM branch");
?>

<!-- Link external CSS -->
<link rel="stylesheet" href="assets/css/manager_dashboard.css">

<div class="container-fluid mt-4">
    <!-- Welcome Banner -->
    <div class="welcome-banner mb-4" style="position:relative;overflow:hidden;">
        <div class="welcome-balls"></div>
        <h3 class="welcome-text" style="position:relative;z-index:2;">
            Welcome, <?= htmlspecialchars($_SESSION['username']); ?> 👋
        </h3>
    </div>

    <div class="container my-5">
        <!-- Summary Cards (styled like admin_dashboard, but manager content/icons) -->
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="summary-card gradient-success h-100">
                    <div>
                        <div class="summary-label">Sales Today</div>
                        <div class="summary-value">UGX <?= number_format($sales_today); ?></div>
                    </div>
                    <i class="fa-solid fa-coins summary-icon"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-card gradient-danger h-100">
                    <div>
                        <div class="summary-label">Expenses Today</div>
                        <div class="summary-value">UGX <?= number_format($expenses_today); ?></div>
                    </div>
                    <i class="fa-solid fa-wallet summary-icon"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-card gradient-info h-100">
                    <div>
                        <div class="summary-label">Products in Stock</div>
                        <div class="summary-value"><?= $total_products; ?> items</div>
                    </div>
                    <i class="fa-solid fa-boxes-stacked summary-icon"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-card gradient-primary h-100">
                    <div>
                        <div class="summary-label">Branch Staff</div>
                        <div class="summary-value"><?= $total_staff; ?> staff</div>
                    </div>
                    <i class="fa-solid fa-users summary-icon"></i>
                </div>
            </div>
        </div>

        <!-- Sales Table -->
        <!-- Card for medium and large devices -->
        <div class="card mb-4 shadow-sm rounded border-0 d-none d-md-block">
            <div class="card-header bg-gradient-primary text-white fw-bold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bar-chart-line me-2"></i> Recent Sales</span>
                <form method="GET" class="d-flex align-items-center">
                    <label class="me-2 fw-bold mb-0">Branch:</label>
                    <select name="branch" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- All Branches --</option>
                        <?php
                        $branches->data_seek(0);
                        while($b = $branches->fetch_assoc()): ?>
                            <option value="<?= $b['id'] ?>" <?= ($selected_branch == $b['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($b['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </form>
            </div>
            <div class="card-body">
                <div class="transactions-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Total</th>
                                <th>Sold By</th>
                                <th>Branch</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($sales_result->num_rows > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($sales_result)): ?>
                                <tr>
                                    <td><?= $row['date'] ?></td>
                                    <td><?= htmlspecialchars($row['product_name']) ?></td>
                                    <td><?= $row['quantity'] ?></td>
                                    <td>UGX <?= number_format($row['amount']) ?></td>
                                    <td><?= htmlspecialchars($row['username']) ?></td>
                                    <td><?= htmlspecialchars($row['branch_name']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">No sales found.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Sales pagination">
                        <ul class="pagination justify-content-center mt-3">
                            <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                                <li class="page-item <?= ($p == $page) ? 'active' : '' ?>">
                                    <a class="page-link" href="?branch=<?= urlencode($selected_branch) ?>&page=<?= $p ?>"><?= $p ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>

        <!-- Card for small devices: scrollable recent sales table -->
        <div class="d-block d-md-none mb-4">
            <div class="card transactions-card">
                <div class="card-header bg-gradient-primary text-white fw-bold d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-bar-chart-line me-2"></i> Recent Sales</span>
                    <form method="GET" class="d-flex align-items-center">
                        <label class="me-2 fw-bold mb-0">Branch:</label>
                        <select name="branch" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">-- All Branches --</option>
                            <?php
                            $branches->data_seek(0);
                            while($b = $branches->fetch_assoc()): ?>
                                <option value="<?= $b['id'] ?>" <?= ($selected_branch == $b['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($b['name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </form>
                </div>
                <div class="card-body">
                    <div class="table-responsive-sm">
                        <div class="transactions-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Product</th>
                                        <th>Qty</th>
                                        <th>Total</th>
                                        <th>Sold By</th>
                                        <th>Branch</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($sales_result->num_rows > 0): ?>
                                    <?php
                                    mysqli_data_seek($sales_result, 0);
                                    while($row = mysqli_fetch_assoc($sales_result)): ?>
                                        <tr>
                                            <td><?= $row['date'] ?></td>
                                            <td><?= htmlspecialchars($row['product_name']) ?></td>
                                            <td><?= $row['quantity'] ?></td>
                                            <td>UGX <?= number_format($row['amount']) ?></td>
                                            <td><?= htmlspecialchars($row['username']) ?></td>
                                            <td><?= htmlspecialchars($row['branch_name']) ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No sales found.</td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination-scroll-wrapper" style="overflow-x:auto; -webkit-overflow-scrolling:touch;">
                            <nav aria-label="Sales pagination">
                                <ul class="pagination justify-content-center mt-3 mb-0 flex-nowrap" style="white-space:nowrap;">
                                    <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                                        <li class="page-item <?= ($p == $page) ? 'active' : '' ?>">
                                            <a class="page-link" href="?branch=<?= urlencode($selected_branch) ?>&page=<?= $p ?>"><?= $p ?></a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Expenses Table -->
        <div class="card mb-4 shadow-sm rounded border-0">
            <div class="card-header bg-gradient-danger text-white fw-bold">
                <i class="bi bi-cash-coin me-2"></i> Recent Expenses
            </div>
            <div class="card-body">
                <div class="transactions-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Spent By</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $sql = "
                            SELECT e.date, e.category, e.amount, u.username 
                            FROM expenses e 
                            JOIN users u ON e.`spent-by` = u.id 
                            ORDER BY e.date DESC 
                            LIMIT 5
                        ";
                        $result = mysqli_query($conn, $sql);
                        while($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>
                                <td>{$row['date']}</td>
                                <td>{$row['category']}</td>
                                <td>UGX ".number_format($row['amount'])."</td>
                                <td>{$row['username']}</td>
                            </tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="d-flex gap-3 justify-content-center">
            <a href="sales.php" class="btn btn-success px-4"><i class="bi bi-plus-circle me-1"></i> Add Sale</a>
            <a href="expense.php" class="btn btn-danger px-4"><i class="bi bi-wallet2 me-1"></i> Add Expense</a>
            <a href="report.php" class="btn btn-secondary px-4"><i class="bi bi-file-earmark-bar-graph me-1"></i> Generate Report</a>
        </div>
    </div>

</div>

<!-- Link external JavaScript -->
<script src="assets/js/manager_dashboard.js"></script>

<?php include '../includes/footer.php'; ?>
