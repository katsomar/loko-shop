<?php
if (!isset($_SESSION['role'])) {
    header("Location: ../index.php");
    exit();
}
$role = $_SESSION['role'];
?>

<!-- Link external CSS -->
<link rel="stylesheet" href="assets/css/sidebar_manager.css">

<div class="sidebar">
    <div class="sidebar-title">Manager Dashboard</div>
    <ul class="sidebar-nav">
        <li><a href="../pages/manager_dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
        
        <!-- Management Menu with Nested Items -->
        <li>
            <a href="#" class="has-submenu" data-toggle="submenu" aria-expanded="false">
                <span><i class="fa-solid fa-building-user"></i> Management</span>
                <i class="fa-solid fa-chevron-right arrow"></i>
            </a>
            <ul class="submenu">
                <li><a href="../pages/branch.php"><i class="fa-solid fa-building"></i> Branches</a></li>
                <li><a href="../pages/list_branches.php"><i class="fa-solid fa-building"></i> List Branches</a></li>
            </ul>
        </li>
        
        <!-- Inventory Menu with Nested Items -->
        <li>
            <a href="#" class="has-submenu" data-toggle="submenu" aria-expanded="false">
                <span><i class="fa-solid fa-boxes-stacked"></i> Inventory</span>
                <i class="fa-solid fa-chevron-right arrow"></i>
            </a>
            <ul class="submenu">
                <li><a href="../pages/product.php"><i class="fa-solid fa-cubes"></i> Products</a></li>
                <li><a href="../pages/product_images.php"><i class="fa-solid fa-image"></i> Product Images</a></li>
                <li><a href="../pages/edit_product.php"><i class="fa-solid fa-box"></i> Edit Product</a></li>
            </ul>
        </li>
        
        <!-- Finance Menu with Nested Items -->
        <li>
            <a href="#" class="has-submenu" data-toggle="submenu" aria-expanded="false">
                <span><i class="fa-solid fa-chart-line"></i> Finance</span>
                <i class="fa-solid fa-chevron-right arrow"></i>
            </a>
            <ul class="submenu">
                <li><a href="../pages/sales.php"><i class="fa-solid fa-cart-shopping"></i> Sales</a></li>
                <li><a href="../pages/expense.php"><i class="fa-solid fa-wallet"></i> Expenses</a></li>
                <li><a href="../pages/accounting.php"><i class="fa-solid fa-briefcase"></i> Accounting</a></li>
                <li><a href="petty_cash.php"><i class="fa fa-money-bill"></i> Petty Cash</a></li>
            </ul>
        </li>
        
        <!-- Staff Menu with Nested Items -->
        <li>
            <a href="#" class="has-submenu" data-toggle="submenu" aria-expanded="false">
                <span><i class="fa-solid fa-user-tie"></i> Staff</span>
                <i class="fa-solid fa-chevron-right arrow"></i>
            </a>
            <ul class="submenu">
                <li><a href="../pages/employees.php"><i class="fa-solid fa-users"></i> Employees</a></li>
                <li><a href="../pages/payroll.php"><i class="fa-solid fa-money-check-dollar"></i> Payroll</a></li>
            </ul>
        </li>
        
        <li><a href="../pages/suppliers.php"><i class="fa-solid fa-truck"></i> Suppliers</a></li>
        <li><a href="../pages/customer_management.php"><i class="fa-solid fa-users"></i> Customer Management</a></li>
        <li><a href="../pages/till_management.php"><i class="fa-solid fa-cash-register"></i> Till Management</a></li>
        
        
        <li style="margin-top:2rem;">
            <a href="../auth/logout.php" class="text-danger fw-bold"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </li>
    </ul>
</div>

<script>
// Sidebar submenu toggle
document.addEventListener('DOMContentLoaded', function() {
    const submenuToggles = document.querySelectorAll('.sidebar-nav a[data-toggle="submenu"]');
    
    submenuToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const submenu = this.nextElementSibling;
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            
            // Toggle aria-expanded
            this.setAttribute('aria-expanded', !isExpanded);
            
            // Toggle submenu visibility
            if (submenu && submenu.classList.contains('submenu')) {
                submenu.classList.toggle('show');
            }
        });
    });
    
    // NEW: Active link detection
    const currentPage = window.location.pathname.split('/').pop();
    const allLinks = document.querySelectorAll('.sidebar-nav a:not([data-toggle="submenu"])');
    
    allLinks.forEach(link => {
        const linkPage = link.getAttribute('href').split('/').pop().split('?')[0];
        if (linkPage === currentPage) {
            link.classList.add('active');
            
            // If link is in a submenu, expand parent and mark parent as active
            const parentSubmenu = link.closest('.submenu');
            if (parentSubmenu) {
                parentSubmenu.classList.add('show');
                const parentToggle = parentSubmenu.previousElementSibling;
                if (parentToggle && parentToggle.hasAttribute('data-toggle')) {
                    parentToggle.setAttribute('aria-expanded', 'true');
                    parentToggle.classList.add('active');
                }
            }
        }
    });
});
</script>

<div class="main-container">
