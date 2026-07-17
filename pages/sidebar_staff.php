<?php
if (!isset($_SESSION['role'])) {
    header("Location: ../index.php");
    exit();
}
$role = $_SESSION['role'];
?>

<!-- Link external CSS -->
<link rel="stylesheet" href="assets/css/sidebar_staff.css">

<div class="sidebar">
    <div class="sidebar-title">Staff Dashboard</div>
    <ul class="sidebar-nav">
        <li><a href="../pages/staff_dashboard.php"><i class="fa-solid fa-crown"></i> Dashboard</a></li>
        
        <!-- Management Menu (hidden for staff role, uncomment if needed) -->
        <!--
        <li>
            <a href="#" class="has-submenu" data-toggle="submenu" aria-expanded="false">
                <span><i class="fa-solid fa-building-user"></i> Management</span>
                <i class="fa-solid fa-chevron-right arrow"></i>
            </a>
            <ul class="submenu">
                <li><a href="../pages/branch.php"><i class="fa-solid fa-building"></i> Branches</a></li>
                <li><a href="../pages/list_branches.php"><i class="fa-solid fa-list"></i> List Branches</a></li>
            </ul>
        </li>
        -->
        
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
                <!-- <li><a href="../pages/accounting.php"><i class="fa-solid fa-briefcase"></i> Accounting</a></li> -->
                <li><a href="petty_cash.php?tab=transactions"><i class="fa-solid fa-coins"></i> Petty Cash</a></li>
            </ul>
        </li>
        
        <li>
            <a href="customer_management.php" class="<?= basename($_SERVER['PHP_SELF']) === 'customer_management.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-users"></i> Customer Management
            </a>
        </li>
        
        <!-- Orders Menu with Nested Items -->
        <li>
            <a href="#" class="has-submenu" data-toggle="submenu" aria-expanded="false">
                <span><i class="fa-solid fa-shopping-cart"></i> Orders</span>
                <i class="fa-solid fa-chevron-right arrow"></i>
            </a>
            <ul class="submenu">
                <li><a href="../pages/remote_orders.php"><i class="fa-solid fa-shopping-bag"></i> Remote Orders</a></li>
                <li><a href="../pages/qr_scanner.php"><i class="fa-solid fa-qrcode"></i> QR Scanner</a></li>
                <li><a href="../pages/payment_proofs.php"><i class="fa-solid fa-receipt"></i> Payment Proofs</a></li>
            </ul>
        </li>
        
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
    
    // Active link detection
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
