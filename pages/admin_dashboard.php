<?php
include '../includes/db.php';
include '../includes/auth.php';
require_role(["admin"]);
include '../pages/sidebar.php';
include '../includes/header.php';      // Header


// NEW: Handle AJAX request to mark notifications as shown
if (isset($_POST['mark_notifications_shown'])) {
    $_SESSION['shown_login_notifications'] = true;
    exit;
}

// NEW: Include notification popup (shows once per login)
include '../includes/notification_popup.php';

$message = "";

// Dates & Filter
$date_from = $_GET['date_from'] ?? date('Y-m-d');
$date_to = $_GET['date_to'] ?? date('Y-m-d');

// Growth from month to month (remains for Monthly Revenue Growth card comparison)
$currentMonth =  date('m');
$lastMonth = date('m', strtotime('-1 month'));
$year =  date('Y');

$currentQuery = $conn->prepare("SELECT SUM(amount) as total FROM sales WHERE MONTH(date) = ? AND YEAR(date) = ?");
$currentQuery->bind_param("ss", $currentMonth, $year);
$currentQuery->execute();
$currentResult = $currentQuery->get_result()->fetch_assoc();
$currentSales = $currentResult['total'] ?? 0;

$lastQuery = $conn->prepare("SELECT SUM(amount) as total FROM sales WHERE MONTH(date) = ? AND YEAR(date) = ?");
$lastQuery->bind_param("is", $lastMonth, $year);
$lastQuery->execute();
$lastResult = $lastQuery->get_result()->fetch_assoc();
$lastSales = $lastResult['total'] ?? 0;

$growth = $lastSales > 0 ? (($currentSales - $lastSales) / $lastSales) * 100 : 0;

// Employees - ALL employees
$employee = $conn->query("SELECT COUNT(*) AS total_employees FROM users WHERE role='staff'")->fetch_assoc()['total_employees'];

// Total branches
$totalbranches = $conn->query("SELECT COUNT(*) AS total_branches FROM branch")->fetch_assoc()['total_branches'];

// Total stock on date_to (or fallback to latest)
$stock_q = $conn->prepare("SELECT SUM(stock) AS total_stock FROM products WHERE date = ?");
$stock_q->bind_param("s", $date_to);
$stock_q->execute();
$totalStock = $stock_q->get_result()->fetch_assoc()['total_stock'];
$stock_q->close();
if ($totalStock === null) {
    $totalStock = $conn->query("SELECT SUM(stock) AS total_stock FROM products WHERE date = (SELECT MAX(date) FROM products)")->fetch_assoc()['total_stock'] ?? 0;
}

// Total sales in period (actual money received, excluding debtors)
$sales_q = $conn->prepare("SELECT COALESCE(SUM(amount), 0) AS total_sales FROM sales WHERE DATE(date) >= ? AND DATE(date) <= ?");
$sales_q->bind_param("ss", $date_from, $date_to);
$sales_q->execute();
$period_sales = $sales_q->get_result()->fetch_assoc()['total_sales'] ?? 0.0;
$sales_q->close();

// Most selling product in the period
$productRes = $conn->prepare("
    SELECT p.name, SUM(s.quantity) AS total_sold
    FROM sales s
    LEFT JOIN products p ON s.`product-id` = p.id
    WHERE s.`product-id` > 0 AND DATE(s.date) >= ? AND DATE(s.date) <= ?
    GROUP BY p.name
    ORDER BY total_sold DESC
    LIMIT 1
");
$productRes->bind_param("ss", $date_from, $date_to);
$productRes->execute();
$topProduct = $productRes->get_result()->fetch_assoc();
$productRes->close();

// Most active branch in the period
$branchSales = $conn->prepare("
    SELECT b.name, COUNT(s.id) AS sales_count
    FROM sales s
    JOIN branch b ON s.`branch-id` = b.id
    WHERE DATE(s.date) >= ? AND DATE(s.date) <= ?
    GROUP BY b.name
    ORDER BY sales_count DESC
    LIMIT 1
");
$branchSales->bind_param("ss", $date_from, $date_to);
$branchSales->execute();
$topBranch = $branchSales->get_result()->fetch_assoc();
$branchSales->close();

// --- SALES VS DEBTS CHART ---
$chart_date_from = $date_from;
$chart_date_to = $date_to;

$datetime1 = new DateTime($date_from);
$datetime2 = new DateTime($date_to);
$interval = $datetime1->diff($datetime2);
$days_diff = $interval->days;

if ($days_diff < 5) {
    // Show trailing 30 days ending at $date_to if period is very short
    $chart_date_from = date('Y-m-d', strtotime($date_to . ' - 30 days'));
}

$date_period = new DatePeriod(
    new DateTime($chart_date_from),
    new DateInterval('P1D'),
    (new DateTime($chart_date_to))->modify('+1 day')
);

$daily_labels = [];
$daily_sales = [];
$daily_debts = [];

foreach ($date_period as $dt) {
    $d_str = $dt->format('Y-m-d');
    $daily_labels[] = $dt->format('d M');
    $daily_sales[$d_str] = 0.0;
    $daily_debts[$d_str] = 0.0;
}

$sales_stmt = $conn->prepare("
    SELECT DATE(date) AS day, SUM(amount) AS total_paid
    FROM sales
    WHERE DATE(date) >= ? AND DATE(date) <= ?
    GROUP BY day
");
$sales_stmt->bind_param("ss", $chart_date_from, $chart_date_to);
$sales_stmt->execute();
$sales_res = $sales_stmt->get_result();
while ($r = $sales_res->fetch_assoc()) {
    if (isset($daily_sales[$r['day']])) {
        $daily_sales[$r['day']] = floatval($r['total_paid']);
    }
}
$sales_stmt->close();

$shop_debt_stmt = $conn->prepare("
    SELECT DATE(created_at) AS day, SUM(balance) AS total_debt
    FROM debtors
    WHERE DATE(created_at) >= ? AND DATE(created_at) <= ?
    GROUP BY day
");
$shop_debt_stmt->bind_param("ss", $chart_date_from, $chart_date_to);
$shop_debt_stmt->execute();
$shop_debt_res = $shop_debt_stmt->get_result();
while ($r = $shop_debt_res->fetch_assoc()) {
    if (isset($daily_debts[$r['day']])) {
        $daily_debts[$r['day']] += floatval($r['total_debt']);
    }
}
$shop_debt_stmt->close();

$cust_debt_stmt = $conn->prepare("
    SELECT DATE(date_time) AS day, SUM(amount_credited) AS total_debt
    FROM customer_transactions
    WHERE DATE(date_time) >= ? AND DATE(date_time) <= ?
    GROUP BY day
");
$cust_debt_stmt->bind_param("ss", $chart_date_from, $chart_date_to);
$cust_debt_stmt->execute();
$cust_debt_res = $cust_debt_stmt->get_result();
while ($r = $cust_debt_res->fetch_assoc()) {
    if (isset($daily_debts[$r['day']])) {
        $daily_debts[$r['day']] += floatval($r['total_debt']);
    }
}
$cust_debt_stmt->close();

$chart_labels_json = json_encode($daily_labels);
$chart_sales_json  = json_encode(array_values($daily_sales));
$chart_debts_json  = json_encode(array_values($daily_debts));

// Monthly sales (last 12 months) - ALL branches
$monthlySalesQuery = $conn->query("
    SELECT DATE_FORMAT(date, '%b %Y') as month_label, SUM(amount) AS total
    FROM sales
    WHERE date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY YEAR(date), MONTH(date)
    ORDER BY YEAR(date), MONTH(date)
");

$months = [];
$monthlyTotals = array_fill(0, 12, 0);
$currentDate = new DateTime();
for ($i = 11; $i >= 0; $i--) {
    $date = (clone $currentDate)->modify("-$i months");
    $months[] = $date->format('M Y'); 
}

while ($row = $monthlySalesQuery->fetch_assoc()) {
    $monthIndex = array_search($row['month_label'], $months);
    if ($monthIndex !== false) {
        $monthlyTotals[$monthIndex] = floatval($row['total']);
    }
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
?>

<div class="container-fluid mt-4 main-content-scroll">

  <div class="welcome-banner mb-4" style="position:relative;overflow:hidden;">
    <div class="welcome-balls"></div>
    <h3 class="welcome-text" style="position:relative;z-index:2;">Welcome, <?= htmlspecialchars($username); ?> 👋</h3>
  </div>

  <!-- Period Filter -->
  <div class="card mb-4" style="border-left: 4px solid #1abc9c;">
    <div class="card-body">
      <form method="GET" class="row g-3 align-items-end">
        <div class="col-12 col-md-4">
          <label class="form-label fw-bold small text-secondary">From Date:</label>
          <input type="date" name="date_from" class="form-control shadow-sm" value="<?= htmlspecialchars($date_from) ?>">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label fw-bold small text-secondary">To Date:</label>
          <input type="date" name="date_to" class="form-control shadow-sm" value="<?= htmlspecialchars($date_to) ?>">
        </div>
        <div class="col-12 col-md-4 d-flex">
          <button type="submit" class="btn btn-primary w-100 me-2"><i class="fa fa-filter"></i> Filter</button>
          <a href="admin_dashboard.php" class="btn btn-secondary w-100"><i class="fa fa-undo"></i> Reset</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Dashboard Overview Card: Only show on medium and larger devices -->
  <div class="card mb-4 d-none d-md-block" style="border-left: 4px solid teal;">
    <div class="card-body" >
      <h5 class="title-card">Dashboard Overview</h5>
      <div class="row">
        <div class="col-md-3 mb-3">
          <div class="card stat-card gradient-primary">
            <div class="card-body d-flex justify-content-between align-items-center">
              <div>
                <h6>Total Employees</h6>
                <h3><?= $employee ?></h3>
              </div>
              <i class="fa-solid fa-users stat-icon"></i>
            </div>
          </div>
        </div>
        <div class="col-md-3 mb-3">
          <div class="card stat-card gradient-success">
            <div class="card-body d-flex justify-content-between align-items-center">
              <div>
                <h6>Total Branches</h6>
                <h3><?= $totalbranches ?></h3>
              </div>
              <i class="fa-solid fa-building stat-icon"></i>
            </div>
          </div>
        </div>
        <div class="col-md-3 mb-3">
          <div class="card stat-card gradient-warning">
            <div class="card-body d-flex justify-content-between align-items-center">
              <div>
                <h6>Total Stock</h6>
                <h3><?= $totalStock ?? 0 ?></h3>
              </div>
              <i class="fa-solid fa-cubes stat-icon"></i>
            </div>
          </div>
        </div>
        <div class="col-md-3 mb-3">
          <div class="card stat-card gradient-danger">
            <div class="card-body d-flex justify-content-between align-items-center">
              <div>
                <h6>Sales</h6>
                <h3>UGX<?= number_format($period_sales, 2) ?></h3>
              </div>
              <i class="fa-solid fa-wallet stat-icon"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Responsive Summary Carousel for Small Devices -->
  <div class="d-block d-md-none mb-4">
    <div id="summaryCarousel" class="carousel slide" data-bs-ride="carousel">
      <div class="carousel-inner">
        <div class="carousel-item active">
          <!-- Total Employees Card -->
          <div class="card stat-card gradient-primary">
            <div class="card-body d-flex justify-content-between align-items-center">
              <div>
                <h6>Total Employees</h6>
                <h3><?= $employee ?></h3>
              </div>
              <i class="fa-solid fa-users stat-icon"></i>
            </div>
          </div>
        </div>
        <div class="carousel-item">
          <!-- Total Branches Card -->
          <div class="card stat-card gradient-success">
            <div class="card-body d-flex justify-content-between align-items-center">
              <div>
                <h6>Total Branches</h6>
                <h3><?= $totalbranches ?></h3>
              </div>
              <i class="fa-solid fa-building stat-icon"></i>
            </div>
          </div>
        </div>
        <div class="carousel-item">
          <!-- Total Stock Card -->
          <div class="card stat-card gradient-warning">
            <div class="card-body d-flex justify-content-between align-items-center">
              <div>
                <h6>Total Stock</h6>
                <h3><?= $totalStock ?></h3>
              </div>
              <i class="fa-solid fa-cubes stat-icon"></i>
            </div>
          </div>
        </div>
        <div class="carousel-item">
          <!-- Sales Card -->
          <div class="card stat-card gradient-danger">
            <div class="card-body d-flex justify-content-between align-items-center">
              <div>
                <h6>Sales</h6>
                <h3>UGX<?= number_format($period_sales, 2) ?></h3>
              </div>
              <i class="fa-solid fa-wallet stat-icon"></i>
            </div>
          </div>
        </div>
      </div>
      <!-- Move carousel indicators below the cards -->
      <div class="d-flex justify-content-center mt-3">
        <div class="carousel-indicators position-static mb-0">
          <button type="button" data-bs-target="#summaryCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Employees"></button>
          <button type="button" data-bs-target="#summaryCarousel" data-bs-slide-to="1" aria-label="Branches"></button>
          <button type="button" data-bs-target="#summaryCarousel" data-bs-slide-to="2" aria-label="Stock"></button>
          <button type="button" data-bs-target="#summaryCarousel" data-bs-slide-to="3" aria-label="Sales"></button>
        </div>
      </div>
      <!-- Remove carousel-control-prev and carousel-control-next buttons -->
    </div>
  </div>

  <!-- Responsive Charts Carousel for Small Devices -->
  <div class="d-block d-md-none mb-4">
    <div id="chartsCarousel" class="carousel slide charts-carousel" data-bs-ride="false" data-bs-touch="true">
      <div class="carousel-inner">
        <div class="carousel-item active">
          <div class="card">
            <div class="card-body">
              <h5 class="title-card">Sales vs Debts</h5>
              <div id="salesDebtChartMobileContainer">
                <canvas id="salesDebtChartMobile"></canvas>
              </div>
            </div>
          </div>
        </div>
        <div class="carousel-item">
          <div class="card" >
            <div class="card-body" style="border-left: 4px solid teal;">
              <h5 class="title-card">Sales Per Month</h5>
              <div id="lineChartMobileContainer">
                <canvas id="lineChartMobile"></canvas>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="d-flex justify-content-center mt-3">
        <div class="carousel-indicators position-static mb-0">
          <button type="button" data-bs-target="#chartsCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Sales vs Debts"></button>
          <button type="button" data-bs-target="#chartsCarousel" data-bs-slide-to="1" aria-label="Sales Per Month"></button>
        </div>
      </div>
    </div>
  </div>

  <!-- Existing charts for medium and large devices -->
  <div class="row mb-4 d-none d-md-flex">
    <div class="col-md-6" >
      <div class="card" style="border-left: 4px solid teal;">
        <div class="card-body">
          <h5 class="title-card">Sales vs Debts</h5>
          <div id="salesDebtChartContainer">
            <canvas id="salesDebtChart"></canvas>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card" style="border-left: 4px solid teal;">
        <div class="card-body">
          <h5 class="title-card">Sales Per Month</h5>
          <div id="lineChartContainer">
            <canvas id="lineChart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Extra Stats -->
  <div class="row mb-4 d-none d-md-flex">
    <div class="col-md-4">
      <div class="card stat-card gradient-info">
        <div class="card-body">
          <h6>Most Selling Product</h6>
          <p><?= $topProduct['name'] ?? 'None' ?> (<?= $topProduct['total_sold'] ?? '0' ?> sold)</p>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card stat-card gradient-secondary">
        <div class="card-body">
          <h6>Most Active Branch</h6>
          <p><?= $topBranch['name'] ?? 'None' ?> (<?= $topBranch['sales_count'] ?? '0' ?> sales)</p>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card stat-card gradient-success">
        <div class="card-body">
          <h6>Revenue Growth</h6>
          <p><?= number_format($growth, 2) ?>% <?= $growth >= 0 ? 'increase 📈' : 'decrease 📉' ?> from last month</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Responsive Extra Stats Carousel for Small Devices -->
  <div class="d-block d-md-none mb-4">
    <div id="statsCarousel" class="carousel slide stats-carousel" data-bs-ride="false" data-bs-touch="true">
      <div class="carousel-inner">
        <div class="carousel-item active">
          <div class="card stat-card gradient-info">
            <div class="card-body">
              <h6>Most Selling Product</h6>
              <p><?= $topProduct['name'] ?? 'N/A' ?> (<?= $topProduct['total_sold'] ?? '0' ?> sold)</p>
            </div>
          </div>
        </div>
        <div class="carousel-item">
          <div class="card stat-card gradient-secondary">
            <div class="card-body">
              <h6>Most Active Branch</h6>
              <p><?= $topBranch['name'] ?? 'N/A' ?> (<?= $topBranch['sales_count'] ?? '0' ?> sales)</p>
            </div>
          </div>
        </div>
        <div class="carousel-item">
          <div class="card stat-card gradient-success">
            <div class="card-body">
              <h6>Revenue Growth</h6>
              <p><?= number_format($growth, 2) ?>% <?= $growth >= 0 ? 'increase 📈' : 'decrease 📉' ?> from last month</p>
            </div>
          </div>
        </div>
      </div>
      <div class="d-flex justify-content-center mt-3">
        <div class="carousel-indicators position-static mb-0">
          <button type="button" data-bs-target="#statsCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Most Selling Product"></button>
          <button type="button" data-bs-target="#statsCarousel" data-bs-slide-to="1" aria-label="Most Active Branch"></button>
          <button type="button" data-bs-target="#statsCarousel" data-bs-slide-to="2" aria-label="Revenue Growth"></button>
        </div>
      </div>
    </div>
  </div>

 

  <!-- Recent Transactions -->
  <div class="card mb-4 transactions-card" style="border-left: 4px solid teal;">
    <div class="card-body">
      <h5 class="transactions-title">Recent Transactions</h5>
      <div class="table-responsive-sm">
        <div class="transactions-table">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>Branch</th>
                <th>Product</th>
                <th>Quantity</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Sold By</th>
              </tr>
            </thead>
            <tbody>
              <?php
              // FIXED: Show transactions from ALL branches + join users for sold-by name
              $salesData = $conn->query("
                SELECT 
                    sales.id, 
                    COALESCE(products.name, 'Multiple Products') AS product_name, 
                    sales.quantity, 
                    sales.amount, 
                    sales.`sold-by`, 
                    sales.date,
                    branch.name AS branch_name,
                    users.username AS sold_by_username
                FROM sales
                LEFT JOIN products ON sales.`product-id` = products.id
                JOIN branch ON sales.`branch-id` = branch.id
                LEFT JOIN users ON sales.`sold-by` = users.id
                ORDER BY sales.id DESC
                LIMIT 10
              ");
              
              if ($salesData && $salesData->num_rows > 0):
                  $i = 1;
                  while ($row = $salesData->fetch_assoc()):
              ?>
                <tr>
                  <td><?= $i++ ?></td>
                  <td><?= htmlspecialchars($row['branch_name']) ?></td>
                  <td><?= htmlspecialchars($row['product_name']) ?></td>
                  <td><?= $row['quantity'] ?></td>
                  <td>UGX<?= number_format($row['amount'], 2) ?></td>
                  <td><?= date('d-M-Y', strtotime($row['date'])) ?></td>
                  <td><?= htmlspecialchars($row['sold_by_username'] ?: 'Unknown') ?></td>
                </tr>
              <?php 
                  endwhile;
              else:
              ?>
                <tr>
                  <td colspan="7" class="text-center text-muted">No recent transactions found</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>


<?php include '../includes/footer.php'; ?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const dailyLabels   = <?= $chart_labels_json ?>;
const dailySales    = <?= $chart_sales_json ?>;
const dailyDebts    = <?= $chart_debts_json ?>;
const months        = <?= json_encode($months) ?>;
const monthlyTotals = <?= json_encode($monthlyTotals) ?>;

function isDarkMode() {
  return document.body.classList.contains('dark-mode');
}

function getChartColors() {
  if (isDarkMode()) {
    return {
      salesColor: 'rgba(54, 162, 235, 1)',
      salesFill: 'rgba(54, 162, 235, 0.1)',
      debtColor: 'rgba(255, 99, 132, 1)',
      debtFill: 'rgba(255, 99, 132, 0.1)',
      monthlyLine: 'rgba(231,76,60,0.9)',
      monthlyFill: 'rgba(231,76,60,0.2)',
      fontColor: '#f4f4f4',
      gridColor: 'rgba(255,255,255,0.2)'
    };
  } else {
    return {
      salesColor: 'rgba(54, 162, 235, 1)',
      salesFill: 'rgba(54, 162, 235, 0.1)',
      debtColor: 'rgba(255, 99, 132, 1)',
      debtFill: 'rgba(255, 99, 132, 0.1)',
      monthlyLine: 'rgba(231,76,60,0.9)',
      monthlyFill: 'rgba(231,76,60,0.2)',
      fontColor: '#2c3e50',
      gridColor: 'rgba(0,0,0,0.1)'
    };
  }
}

function createSalesDebtChart() {
  const colors = getChartColors();
  const el = document.getElementById('salesDebtChart');
  if (el) {
    new Chart(el, {
      type: 'line',
      data: {
        labels: dailyLabels,
        datasets: [
          {
            label: 'Sales (Paid)',
            data: dailySales,
            borderColor: colors.salesColor,
            backgroundColor: colors.salesFill,
            fill: true,
            tension: 0.4
          },
          {
            label: 'Debtors (Unpaid)',
            data: dailyDebts,
            borderColor: colors.debtColor,
            backgroundColor: colors.debtFill,
            fill: true,
            tension: 0.4
          }
        ]
      },
      options: {
        responsive: true,
        plugins: { legend: { labels: { color: colors.fontColor } } },
        scales: {
          x: { ticks: { color: colors.fontColor }, grid: { color: colors.gridColor } },
          y: { ticks: { color: colors.fontColor }, grid: { color: colors.gridColor }, beginAtZero: true }
        }
      }
    });
  }
}

function createLineChart() {
  const colors = getChartColors();
  const el = document.getElementById('lineChart');
  if (el) {
    new Chart(el, {
      type: 'line',
      data: {
        labels: months,
        datasets: [{
          label: 'Monthly Sales',
          data: monthlyTotals,
          borderColor: colors.monthlyLine,
          backgroundColor: colors.monthlyFill,
          fill: true,
          tension: 0.4
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { labels: { color: colors.fontColor } } },
        scales: {
          x: { ticks: { color: colors.fontColor }, grid: { color: colors.gridColor } },
          y: { ticks: { color: colors.fontColor }, grid: { color: colors.gridColor }, beginAtZero: true }
        }
      }
    });
  }
}

function createSalesDebtChartMobile() {
  const colors = getChartColors();
  const el = document.getElementById('salesDebtChartMobile');
  if (el) {
    new Chart(el, {
      type: 'line',
      data: {
        labels: dailyLabels,
        datasets: [
          {
            label: 'Sales (Paid)',
            data: dailySales,
            borderColor: colors.salesColor,
            backgroundColor: colors.salesFill,
            fill: true,
            tension: 0.4
          },
          {
            label: 'Debtors (Unpaid)',
            data: dailyDebts,
            borderColor: colors.debtColor,
            backgroundColor: colors.debtFill,
            fill: true,
            tension: 0.4
          }
        ]
      },
      options: {
        responsive: true,
        plugins: { legend: { labels: { color: colors.fontColor } } },
        scales: {
          x: { ticks: { color: colors.fontColor }, grid: { color: colors.gridColor } },
          y: { ticks: { color: colors.fontColor }, grid: { color: colors.gridColor }, beginAtZero: true }
        }
      }
    });
  }
}

function createLineChartMobile() {
  const colors = getChartColors();
  const el = document.getElementById('lineChartMobile');
  if (el) {
    new Chart(el, {
      type: 'line',
      data: {
        labels: months,
        datasets: [{
          label: 'Monthly Sales',
          data: monthlyTotals,
          borderColor: colors.monthlyLine,
          backgroundColor: colors.monthlyFill,
          fill: true,
          tension: 0.4
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { labels: { color: colors.fontColor } } },
        scales: {
          x: { ticks: { color: colors.fontColor }, grid: { color: colors.gridColor } },
          y: { ticks: { color: colors.fontColor }, grid: { color: colors.gridColor }, beginAtZero: true }
        }
      }
    });
  }
}

// Initialize charts
createSalesDebtChart();
createLineChart();
if (window.innerWidth < 992) {
  createSalesDebtChartMobile();
  createLineChartMobile();
}

// Re-render charts on dark mode toggle
const darkToggle = document.querySelector('.dark-toggle');
if (darkToggle) {
  darkToggle.addEventListener('click', () => {
    // Clear containers and recreate canvases
    const c1 = document.getElementById('salesDebtChartContainer');
    if (c1) c1.innerHTML = '<canvas id="salesDebtChart"></canvas>';
    
    const c2 = document.getElementById('lineChartContainer');
    if (c2) c2.innerHTML = '<canvas id="lineChart"></canvas>';

    const cm1 = document.getElementById('salesDebtChartMobileContainer');
    if (cm1) cm1.innerHTML = '<canvas id="salesDebtChartMobile"></canvas>';
    
    const cm2 = document.getElementById('lineChartMobileContainer');
    if (cm2) cm2.innerHTML = '<canvas id="lineChartMobile"></canvas>';

    createSalesDebtChart();
    createLineChart();
    if (window.innerWidth < 992) {
      createSalesDebtChartMobile();
      createLineChartMobile();
    }
  });
}
</script>

<!-- Animated Balls Script -->
<script>
(function() {
  const banner = document.querySelector('.welcome-banner');
  const ballsContainer = document.querySelector('.welcome-balls');
  if (!banner || !ballsContainer) return;

  // Ball colors for light and dark mode
  function getColors() {
    if (document.body.classList.contains('dark-mode')) {
      return ['#ffd200', '#1abc9c', '#56ccf2', '#23243a', '#fff'];
    } else {
      return ['#1abc9c', '#56ccf2', '#ffd200', '#3498db', '#fff'];
    }
  }

  // Remove old balls
  ballsContainer.innerHTML = '';
  ballsContainer.style.position = 'absolute';
  ballsContainer.style.top = 0;
  ballsContainer.style.left = 0;
  ballsContainer.style.width = '100%';
  ballsContainer.style.height = '100%';
  ballsContainer.style.zIndex = 1;
  ballsContainer.style.pointerEvents = 'none';

  // Create balls
  const balls = [];
  const colors = getColors();
  const numBalls = 7;
  for (let i = 0; i < numBalls; i++) {
    const ball = document.createElement('div');
    ball.className = 'welcome-ball';
    ball.style.position = 'absolute';
    ball.style.borderRadius = '50%';
    ball.style.opacity = '0.18';
    ball.style.background = colors[i % colors.length];
    ball.style.width = ball.style.height = (32 + Math.random() * 32) + 'px';
    ball.style.top = (10 + Math.random() * 60) + '%';
    ball.style.left = (5 + Math.random() * 85) + '%';
    ballsContainer.appendChild(ball);
    balls.push({
      el: ball,
      x: parseFloat(ball.style.left),
      y: parseFloat(ball.style.top),
      r: Math.random() * 0.5 + 0.2,
      dx: (Math.random() - 0.5) * 0.2,
      dy: (Math.random() - 0.5) * 0.2
    });
  }

  // Animate balls
  function animateBalls() {
    balls.forEach(ball => {
      ball.x += ball.dx;
      ball.y += ball.dy;
      if (ball.x < 0 || ball.x > 95) ball.dx *= -1;
      if (ball.y < 5 || ball.y > 80) ball.dy *= -1;
      ball.el.style.left = ball.x + '%';
      ball.el.style.top = ball.y + '%';
    });
    requestAnimationFrame(animateBalls);
  }
  animateBalls();

  // Recolor balls on theme change
  window.addEventListener('storage', () => {
    const newColors = getColors();
    balls.forEach((ball, i) => {
      ball.el.style.background = newColors[i % newColors.length];
    });
  });
  document.getElementById('themeToggle')?.addEventListener('change', () => {
    const newColors = getColors();
    balls.forEach((ball, i) => {
      ball.el.style.background = newColors[i % newColors.length];
    });
  });
})();
</script>