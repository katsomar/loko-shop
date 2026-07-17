<?php
include '../includes/db.php';
include '../includes/auth.php';
require_role(["admin", "manager"]);
include '../pages/sidebar.php';
include '../includes/header.php';

// Branch ID from GET (default 1 if not set)
$branch_id = isset($_GET['id']) ? intval($_GET['id']) : 1;

// Fetch all branches for dropdown
$all_branches = $conn->query("SELECT id, name FROM branch ORDER BY name ASC");

// Get Branch Info
$branch_stmt = $conn->prepare("SELECT * FROM branch WHERE id = ?");
$branch_stmt->bind_param("i", $branch_id);
$branch_stmt->execute();
$branch = $branch_stmt->get_result()->fetch_assoc();
?>

<!-- Link external CSS -->
<link rel="stylesheet" href="assets/css/branch.css">

<div class="container mt-5">

    <!-- Branch Selector -->
    <div class="d-flex justify-content-end mb-3">
        <div class="dropdown">
            <button class="btn btn-outline-primary dropdown-toggle" type="button" id="branchDropdown" data-bs-toggle="dropdown">
                <?= $branch ? "Viewing: " . htmlspecialchars($branch['name']) : "Select Branch" ?>
            </button>
            <ul class="dropdown-menu" aria-labelledby="branchDropdown">
                <?php while ($row = $all_branches->fetch_assoc()): ?>
                    <li>
                        <a class="dropdown-item <?= ($row['id'] == $branch_id) ? 'active' : '' ?>" 
                           href="branch.php?id=<?= $row['id'] ?>">
                           <?= htmlspecialchars($row['name']) ?>
                        </a>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>
    </div>

<?php if (!$branch): ?>
    <div class='alert alert-warning shadow-sm text-center p-4 rounded'>
        <h4 class="fw-bold mb-2">No Branch Selected or Found</h4>
        <p class="text-muted mb-3">To view the dashboard, please select an existing branch or create a new one.</p>
        <div class="d-flex justify-content-center gap-2">
            <a href="list_branches.php" class="btn btn-outline-primary"><i class="fa fa-list"></i> View Branch List</a>
            <a href="create_branch.php" class="btn btn-primary"><i class="fa fa-plus"></i> Create New Branch</a>
        </div>
    </div>

<?php else:

    // Data queries
    $inventory = $conn->query("SELECT COUNT(*) AS total_products, COALESCE(SUM(stock),0) AS stock FROM products WHERE `branch-id`=$branch_id")->fetch_assoc();
    $sales = $conn->query("SELECT COUNT(*) AS total_sales, SUM(amount) AS revenue FROM sales WHERE `branch-id`=$branch_id")->fetch_assoc();
    $expenses = $conn->query("SELECT SUM(amount) AS total_expense FROM expenses WHERE `branch-id`=$branch_id")->fetch_assoc();
    $profit = ($sales['revenue'] ?? 0) - ($expenses['total_expense'] ?? 0);

    // Top products
    $top_products = $conn->query("
        SELECT p.name, SUM(s.quantity) AS total_sold 
        FROM sales s 
        JOIN products p ON s.`product-id`=p.id 
        WHERE s.`branch-id`=$branch_id 
        GROUP BY s.`product-id` 
        ORDER BY total_sold DESC
    ");
    $top_products_array = [];
    while ($row = $top_products->fetch_assoc()) {
        $top_products_array[] = $row;
    }

    // Chart Data
    $chart_labels = array_slice(array_column($top_products_array, 'name'), 0, 5);
    $chart_data   = array_slice(array_column($top_products_array, 'total_sold'), 0, 5);

    // Staff
    $staff_result = $conn->query("SELECT username, role FROM users WHERE `branch-id`=$branch_id AND role='staff'");
?>

    <!-- Branch Info -->
    <div class="card mb-4 branch-info-card" style="border-left: 4px solid teal;">
        <div class="card-header">Branch Information</div>
        <div class="card-body">
            <p><strong>Name:</strong> <?= htmlspecialchars($branch['name']) ?></p>
            <p><strong>Location:</strong> <?= htmlspecialchars($branch['location']) ?></p>
            <p><strong>Contact:</strong> <?= htmlspecialchars($branch['contact']) ?></p>
        </div>
    </div>

    <!-- Responsive Summary Cards Carousel for Small Devices -->
    <div class="d-block d-md-none mb-4">
      <div id="branchSummaryCarousel" class="carousel slide stats-carousel" data-bs-ride="false" data-bs-touch="true">
        <div class="carousel-inner">
          <div class="carousel-item active">
            <div class="card stat-card gradient-primary h-100">
              <div class="card-body d-flex align-items-center">
                <i class="fa-solid fa-box stat-icon me-3"></i>
                <div>
                  <h6>Inventory</h6>
                  <h3><?= $inventory['total_products'] ?></h3>
                  <div>Stock: <?= $inventory['stock'] ?></div>
                </div>
              </div>
            </div>
          </div>
          <div class="carousel-item">
            <div class="card stat-card gradient-success h-100">
              <div class="card-body d-flex align-items-center">
                <i class="fa-solid fa-coins stat-icon me-3"></i>
                <div>
                  <h6>Sales</h6>
                  <h3><?= $sales['total_sales'] ?></h3>
                  <div>Revenue: UGX <?= number_format($sales['revenue'] ?? 0,2) ?></div>
                </div>
              </div>
            </div>
          </div>
          <div class="carousel-item">
            <div class="card stat-card gradient-danger h-100">
              <div class="card-body d-flex align-items-center">
                <i class="fa-solid fa-chart-line stat-icon me-3"></i>
                <div>
                  <h6>Profit</h6>
                  <div>Expenses: UGX <?= number_format($expenses['total_expense'] ?? 0,2) ?></div>
                  <h3>Net: UGX <?= number_format($profit,2) ?></h3>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="d-flex justify-content-center mt-3">
          <div class="carousel-indicators position-static mb-0">
            <button type="button" data-bs-target="#branchSummaryCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Inventory"></button>
            <button type="button" data-bs-target="#branchSummaryCarousel" data-bs-slide-to="1" aria-label="Sales"></button>
            <button type="button" data-bs-target="#branchSummaryCarousel" data-bs-slide-to="2" aria-label="Profit"></button>
          </div>
        </div>
      </div>
    </div>

    <!-- Summary Cards for medium and large devices -->
    <div class="row g-4 mb-4 d-none d-md-flex">
      <div class="col-md-4">
        <div class="card stat-card gradient-primary h-100">
          <div class="card-body d-flex align-items-center" >
            <i class="fa-solid fa-box stat-icon me-3"></i>
            <div>
              <h6>Inventory</h6>
              <h3><?= $inventory['total_products'] ?></h3>
              <div>Stock: <?= $inventory['stock'] ?></div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card stat-card gradient-success h-100">
          <div class="card-body d-flex align-items-center">
            <i class="fa-solid fa-coins stat-icon me-3"></i>
            <div>
              <h6>Sales</h6>
              <h3><?= $sales['total_sales'] ?></h3>
              <div>Revenue: UGX <?= number_format($sales['revenue'] ?? 0,2) ?></div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card stat-card gradient-danger h-100">
          <div class="card-body d-flex align-items-center">
            <i class="fa-solid fa-chart-line stat-icon me-3"></i>
            <div>
              <h6>Profit</h6>
              <div>Expenses: UGX <?= number_format($expenses['total_expense'] ?? 0,2) ?></div>
              <h3>Net: UGX <?= number_format($profit,2) ?></h3>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Responsive Charts Carousel for Small Devices -->
    <div class="d-block d-md-none mb-4">
      <div id="branchChartsCarousel" class="carousel slide charts-carousel" data-bs-ride="false" data-bs-touch="true">
        <div class="carousel-inner">
          <div class="carousel-item active">
            <div class="card h-100" style="border-left: 4px solid teal;">
              <div class="card-header">Top Products (Bar)</div>
              <div class="card-body p-3">
                <canvas id="barChartMobile"></canvas>
              </div>
            </div>
          </div>
          <div class="carousel-item">
            <div class="card h-100" style="border-left: 4px solid teal;">
              <div class="card-header">Top Products (Donut)</div>
              <div class="card-body p-3">
                <div class="donut-wrapper">
                  <canvas id="donutChartMobile"></canvas>
                  <div class="donut-legend">
                    <ul id="donutLegendListMobile"></ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="d-flex justify-content-center mt-3">
          <div class="carousel-indicators position-static mb-0">
            <button type="button" data-bs-target="#branchChartsCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Bar Chart"></button>
            <button type="button" data-bs-target="#branchChartsCarousel" data-bs-slide-to="1" aria-label="Donut Chart"></button>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts for medium and large devices -->
    <div class="row mb-4 d-none d-md-flex">
      <!-- Bar Chart -->
      <div class="col-md-6">
        <div class="card h-100" style="border-left: 4px solid teal;">
          <div class="card-header">Top Products (Bar)</div>
          <div class="card-body p-4">
            <canvas id="barChart"></canvas>
          </div>
        </div>
      </div>
      <!-- Donut Chart -->
      <div class="col-md-6">
        <div class="card h-100" style="border-left: 4px solid teal;">
          <div class="card-header">Top Products (Donut)</div>
          <div class="card-body p-4">
            <div class="donut-wrapper">
              <canvas id="donutChart"></canvas>
              <div class="donut-legend">
                <ul id="donutLegendList"></ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Top Products Table -->
    <div class="card mb-4" style="border-left: 4px solid teal;">
        <div class="card-header">Top Selling Products</div>
        <div class="card-body transactions-table">
            <table>
                <thead><tr><th>Product</th><th>Quantity Sold</th></tr></thead>
                <tbody>
                <?php foreach ($top_products_array as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= $row['total_sold'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Staff -->
    <div class="card mb-4" style="border-left: 4px solid teal;">
        <div class="card-header">Branch Staff</div>
        <div class="card-body transactions-table">
            <table>
                <thead><tr><th>Username</th><th>Role</th></tr></thead>
                <tbody>
                <?php if ($staff_result->num_rows > 0): ?>
                    <?php while($staff = $staff_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($staff['username']) ?></td>
                            <td><?= ucfirst($staff['role']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="2" class="text-center">No staff found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php endif; ?>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<!-- Pass chart data to JavaScript -->
<script>
    window.branchChartLabels = <?= json_encode($chart_labels) ?>;
    window.branchChartData = <?= json_encode($chart_data) ?>;
</script>

<!-- Link external JavaScript -->
<script src="assets/js/branch.js"></script>

<?php include '../includes/footer.php'; ?>
