<?php
require_once '../includes/db.php';

// Ensure products.image_path exists (defensive)
$checkImage = $conn->query("
    SELECT COLUMN_NAME 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'products'
      AND COLUMN_NAME = 'image_path'
");
if (!$checkImage || $checkImage->num_rows === 0) {
    $conn->query("ALTER TABLE products ADD COLUMN image_path VARCHAR(255) NULL");
    if ($conn->errno) {
        @$conn->query("ALTER TABLE products ADD COLUMN image_path VARCHAR(255) NULL");
    }
}

// Ensure products.visible exists (defensive)
$checkVisible = $conn->query("
    SELECT COLUMN_NAME 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'products'
      AND COLUMN_NAME = 'visible'
");
if (!$checkVisible || $checkVisible->num_rows === 0) {
    $conn->query("ALTER TABLE products ADD COLUMN visible TINYINT(1) NOT NULL DEFAULT 1");
    if ($conn->errno) {
        @$conn->query("ALTER TABLE products ADD COLUMN visible TINYINT(1) NOT NULL DEFAULT 1");
    }
}

// Get company settings
$settings = mysqli_query($conn, "SELECT * FROM company_settings LIMIT 1")->fetch_assoc();
$companyName = $settings['company_name'] ?? 'Our Business';

// Determine selected branch (if your site supports branch selection on the customer side)
$selectedBranch = isset($_GET['branch']) ? (int)$_GET['branch'] : 0;

// FETCH PRODUCTS WITH image_path SO JS CAN USE IT
$products = [];
$sql = "
    SELECT 
        p.id,
        p.name,
        p.`selling-price` AS selling_price,
        p.stock,
        p.image_path,              -- ✅ comes from product_images.php updates
        p.`branch-id` AS branch_id,
        b.name AS branch_name
    FROM products p
    JOIN branch b ON p.`branch-id` = b.id
    WHERE 1
      -- add any conditions you already use, e.g. p.visible = 1
";
$res = $conn->query($sql);
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $products[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($companyName) ?> - Place Your Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-overlay"></div>
        <div class="container hero-content">
            <h1 class="hero-title animate-fade-in"><?= htmlspecialchars($companyName) ?></h1>
            <p class="hero-subtitle animate-slide-up">Order your favorite products online</p>
            <button class="btn btn-primary btn-lg start-order-btn animate-bounce" onclick="showBranchSelection()">
                <i class="fas fa-shopping-cart me-2"></i> Start Ordering
            </button>
        </div>
    </section>

    <!-- Branch Selection Modal -->
    <div class="modal fade" id="branchModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-map-marker-alt me-2"></i>Select Your Branch</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="branchesContainer" class="row g-3"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Section -->
    <section class="products-section d-none" id="productsSection">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-box me-2"></i>Available Products</h2>
                <button class="btn btn-outline-primary" onclick="showBranchSelection()">
                    <i class="fas fa-exchange-alt me-2"></i>Change Branch
                </button>
            </div>

            <div id="productsContainer" class="row g-4">
                <!-- Products will be rendered here by JavaScript -->
            </div>
        </div>
    </section>

    <!-- Cart Drawer -->
    <div class="cart-drawer" id="cartDrawer">
        <div class="cart-header">
            <h5><i class="fas fa-shopping-cart me-2"></i>Your Cart</h5>
            <button class="btn-close-cart" onclick="toggleCart()"><i class="fas fa-times"></i></button>
        </div>
        <div class="cart-body" id="cartItems"></div>
        <div class="cart-footer">
            <div class="cart-total">
                <span>Total:</span>
                <span id="cartTotal">UGX 0</span>
            </div>
            <button class="btn btn-primary w-100" onclick="proceedToCheckout()">
                Proceed to Checkout <i class="fas fa-arrow-right ms-2"></i>
            </button>
        </div>
    </div>

    <!-- Floating Cart Button -->
    <button class="floating-cart-btn" onclick="toggleCart()">
        <i class="fas fa-shopping-cart"></i>
        <span class="cart-count" id="cartCount">0</span>
    </button>

    <!-- Checkout Modal -->
    <div class="modal fade" id="checkoutModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: var(--primary-color); color: #fff;">
                    <h5 class="modal-title"><i class="fas fa-shopping-cart"></i> Checkout</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6 class="mb-3">Customer Information</h6>
                    <div class="mb-3">
                        <label for="customerName" class="form-label">
                            <i class="fas fa-user"></i> Full Name
                        </label>
                        <input type="text" id="customerName" class="form-control" placeholder="Enter your full name" required>
                    </div>
                    <div class="mb-3">
                        <label for="customerPhone" class="form-label">
                            <i class="fas fa-phone"></i> Phone Number
                        </label>
                        <input type="tel" id="customerPhone" class="form-control" placeholder="e.g., 0700000000" required>
                    </div>
                    <div class="mb-3">
                        <label for="paymentMethod" class="form-label">
                            <i class="fas fa-credit-card"></i> Payment Method
                        </label>
                        <select id="paymentMethod" class="form-select">
                            <option value="cash">Cash on Pickup</option>
                            <option value="MTN Merchant">MTN Mobile Money</option>
                            <option value="Airtel Merchant">Airtel Money</option>
                        </select>
                        <small class="text-muted">
                            For Cash: Pay when you collect<br>
                            For Mobile Money: Pay now and we deliver
                        </small>
                    </div>
                    
                    <!-- Mobile money section will be inserted here dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitOrder()">
                        <i class="fas fa-check"></i> Place Order
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i>Order Placed Successfully!</h5>
                </div>
                <div class="modal-body text-center">
                    <div class="success-animation mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 80px;"></i>
                    </div>
                    <h5>Your Order Reference</h5>
                    <h3 class="text-primary mb-4" id="orderReference"></h3>
                    <div id="qrCodeContainer" class="mb-4"></div>
                    <p class="text-muted">Please present this QR code when picking up your order</p>
                    <div class="alert alert-info">
                        <small><i class="fas fa-clock me-2"></i>Valid for 24 hours</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Print QR Code
                    </button>
                    <button class="btn btn-secondary" onclick="location.reload()">
                        <i class="fas fa-home me-2"></i>Place New Order
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- make products (with image_path) available to JS -->
    <script>
        window.productsData = <?= json_encode($products, JSON_UNESCAPED_SLASHES) ?>;
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="assets/js/app.js"></script>
    <script>
    // Pass products to JavaScript
    window.productsData = <?php echo json_encode($products); ?>;

    document.addEventListener('DOMContentLoaded', () => {
        const products = window.productsData || [];
        const container = document.getElementById('productsContainer');
        if (!container) return;

        container.innerHTML = '';

        products.forEach(p => {
            let imageHtml = '';
            if (p.image_path && p.image_path.trim() !== '') {
                // product_images.php stores "uploads/product_images/xxx.jpg"
                // from /customer we must go one level up
                const imageSrc = '../' + p.image_path.replace(/^\/+/, '');
                imageHtml = `
                    <img 
                        src="${imageSrc}" 
                        alt="${escapeHtml(p.name)}" 
                        class="product-card-image"
                    >
                `;
            } else {
                imageHtml = `
                    <div class="product-card-no-image">
                        No Image
                    </div>
                `;
            }

            const cardHtml = `
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="product-card">
                        <div class="product-card-image-wrap">
                            ${imageHtml}
                        </div>
                        <div class="product-card-body">
                            <h5 class="product-name">${escapeHtml(p.name)}</h5>
                            <p class="product-branch small text-muted">${escapeHtml(p.branch_name)}</p>
                            <p class="product-price fw-bold text-primary">
                                UGX ${formatPrice(p.selling_price)}
                            </p>
                            <p class="product-stock small">
                                ${p.stock > 0
                                    ? `<span class="badge bg-success">In Stock (${p.stock})</span>`
                                    : '<span class="badge bg-danger">Out of Stock</span>'}
                            </p>
                            <button class="btn btn-primary btn-sm w-100 mt-2"
                                    onclick="addToCart(${p.id}, '${escapeHtml(p.name)}', ${p.selling_price})">
                                <i class="fas fa-cart-plus me-1"></i>Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', cardHtml);
        });
    });

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    function formatPrice(price) {
        return parseFloat(price || 0).toLocaleString('en-UG', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    }
    </script>

    <style>
/* === GLOBAL THEME ======================================================= */

:root {
    --cust-bg: #f8fafc;
    --cust-surface: #ffffff;
    --cust-surface-soft: #eef2ff;
    --cust-border-soft: #e2e8f0;
    --cust-text-main: #0f172a;
    --cust-text-muted: #64748b;
    --cust-primary: #0f766e;    /* deep teal */
    --cust-primary-soft: #14b8a6;
    --cust-accent: #22c55e;     /* add-to-cart accent */
    --cust-danger: #ef4444;
    --cust-radius-card: 16px;
    --cust-radius-pill: 999px;
    --cust-shadow-soft: 0 10px 30px rgba(15, 23, 42, 0.10);
    --cust-shadow-strong: 0 18px 45px rgba(15, 23, 42, 0.20);
    --cust-transition-fast: 160ms ease-out;
    --cust-transition-med: 220ms ease-out;
}

body {
    font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    background: var(--cust-bg);
    color: var(--cust-text-main);
}

/* === AVAILABLE PRODUCTS SECTION ========================================= */

#productsSection {
    padding-top: 2.5rem;
    padding-bottom: 3rem;
}

#productsSection .container {
    max-width: 1200px;
}

#productsSection h2 {
    font-size: 1.6rem;
    font-weight: 700;
    color: var(--cust-text-main);
    letter-spacing: 0.02em;
    display: flex;
    align-items: center;
    gap: 0.6rem;
}

#productsSection h2 i,
#productsSection h2 .fa,
#productsSection h2 .fas {
    color: var(--cust-primary);
}

/* “Change Branch” button in header */
#productsSection .btn-outline-primary {
    border-radius: var(--cust-radius-pill);
    border-color: var(--cust-primary);
    color: var(--cust-primary);
    font-weight: 500;
    padding-inline: 1.2rem;
    transition: background var(--cust-transition-fast),
                color var(--cust-transition-fast),
                box-shadow var(--cust-transition-fast);
}
#productsSection .btn-outline-primary:hover {
    background: var(--cust-primary);
    color: #f9fafb;
    box-shadow: 0 6px 18px rgba(15, 118, 110, 0.35);
}

/* === PRODUCT GRID & CARDS =============================================== */

#productsContainer {
    row-gap: 1.5rem;
}

/* Column wrapper */
#productsContainer > [class*="col-"] {
    display: flex;
}

/* Card shell */
.product-card {
    background: var(--cust-surface);
    border-radius: var(--cust-radius-card);
    box-shadow: var(--cust-shadow-soft);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    width: 100%;
    transition:
        transform var(--cust-transition-med),
        box-shadow var(--cust-transition-med),
        background var(--cust-transition-med);
    border: 1px solid rgba(148, 163, 184, 0.22);
}

.product-card:hover {
    transform: translateY(-6px);
    box-shadow: var(--cust-shadow-strong);
    background: #ffffff;
}

/* Image */
.product-card img.product-image {
    width: 100%;
    height: 190px;
    object-fit: cover;
    display: block;
    transition: transform 260ms ease-out, filter 260ms ease-out;
}

.product-card:hover img.product-image {
    transform: scale(1.05);
    filter: saturate(1.08);
}

/* If any card uses a no-image div instead of img */
.product-card .product-card-no-image {
    width: 100%;
    height: 190px;
    border-radius: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: repeating-linear-gradient(
        135deg,
        #e5e7eb,
        #e5e7eb 8px,
        #f9fafb 8px,
        #f9fafb 16px
    );
    color: var(--cust-text-muted);
    font-size: 0.85rem;
}

/* Info area */
.product-info {
    padding: 0.85rem 0.95rem 0.95rem;
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
    flex: 1;
}

.product-info h6,
.product-info .product-name {
    font-size: 0.98rem;
    font-weight: 600;
    margin: 0 0 0.1rem;
    color: var(--cust-text-main);
    text-transform: capitalize;
}

/* Price */
.product-info .product-price {
    margin: 0.15rem 0 0.15rem;
    font-size: 1.02rem;
    font-weight: 700;
    color: var(--cust-primary-soft);
}

/* Stock text */
.product-info small,
.product-info .text-muted,
.product-info .product-stock {
    font-size: 0.82rem;
    color: var(--cust-text-muted);
}

/* Quantity controls */
.quantity-controls {
    margin-top: 0.5rem;
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.25rem 0.5rem;
    border-radius: var(--cust-radius-pill);
    background: rgba(226, 232, 240, 0.7);
}

.quantity-btn {
    width: 30px;
    height: 30px;
    border-radius: var(--cust-radius-pill);
    border: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #ffffff;
    color: var(--cust-primary);
    font-weight: 600;
    font-size: 1rem;
    box-shadow: 0 2px 6px rgba(148, 163, 184, 0.55);
    cursor: pointer;
    transition:
        background var(--cust-transition-fast),
        color var(--cust-transition-fast),
        transform var(--cust-transition-fast),
        box-shadow var(--cust-transition-fast);
}

.quantity-btn:hover {
    background: var(--cust-primary);
    color: #f9fafb;
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(15, 118, 110, 0.35);
}

.quantity-btn:active {
    transform: translateY(0) scale(0.96);
    box-shadow: 0 2px 5px rgba(148, 163, 184, 0.6);
}

/* Quantity input */
.quantity-input {
    width: 48px;
    border: none;
    background: transparent;
    text-align: center;
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--cust-text-main);
    outline: none;
}

/* Add to Cart button inside card only */
.product-card .btn.btn-primary {
    margin-top: 0.7rem;
    width: 100%;
    border-radius: var(--cust-radius-pill);
    border: none;
    background: linear-gradient(135deg, var(--cust-accent), #16a34a);
    font-size: 0.9rem;
    font-weight: 600;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    box-shadow: 0 8px 20px rgba(34, 197, 94, 0.35);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.3rem;
    transition:
        transform var(--cust-transition-med),
        box-shadow var(--cust-transition-med),
        filter var(--cust-transition-med),
        background var(--cust-transition-med);
}

.product-card .btn.btn-primary:hover {
    transform: translateY(-1px) scale(1.01);
    box-shadow: 0 12px 26px rgba(22, 163, 74, 0.45);
    filter: brightness(1.03);
}

.product-card .btn.btn-primary:active {
    transform: translateY(0) scale(0.97);
    box-shadow: 0 6px 14px rgba(22, 163, 74, 0.4);
}

/* === CART DRAWER ======================================================== */

#cartDrawer {
    position: fixed;
    top: 0;
    right: 0;
    width: min(360px, 100%);
    max-width: 100%;
    height: 100vh;
    background: rgba(15, 23, 42, 0.96);
    color: #e5e7eb;
    box-shadow: -18px 0 40px rgba(15, 23, 42, 0.65);
    transform: translateX(100%);
    opacity: 0;
    visibility: hidden;
    transition:
        transform 260ms cubic-bezier(0.22, 0.8, 0.26, 0.95),
        opacity 220ms ease-out,
        visibility 220ms ease-out;
    z-index: 1050;
    display: flex;
    flex-direction: column;
    backdrop-filter: blur(18px);
}

#cartDrawer.active {
    transform: translateX(0);
    opacity: 1;
    visibility: visible;
}

/* Cart header */
#cartDrawer .cart-header,
#cartDrawer .offcanvas-header {
    padding: 1rem 1.3rem 0.75rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid rgba(148, 163, 184, 0.35);
}

#cartDrawer .cart-title {
    font-size: 1rem;
    font-weight: 600;
}

/* Close icon button */
#cartDrawer .btn-close {
    filter: invert(1);
    opacity: 0.8;
}

/* Cart items area */
#cartItems {
    padding: 0.75rem 1.1rem 1rem;
    overflow-y: auto;
    flex: 1;
}

.cart-item {
    padding: 0.5rem 0;
    border-bottom: 1px dashed rgba(148, 163, 184, 0.35);
}

.cart-item:last-child {
    border-bottom: none;
}

.cart-item-info h6 {
    font-size: 0.9rem;
    margin: 0 0 0.15rem;
    color: #f9fafb;
}

.cart-item-info p {
    font-size: 0.8rem;
    color: #cbd5f5;
}

/* Cart item delete button */
.cart-item .btn.btn-danger.btn-sm {
    padding: 0.25rem 0.45rem;
    border-radius: 999px;
}

/* Cart footer (sticky) */
#cartDrawer .cart-footer {
    padding: 0.75rem 1.1rem 1.1rem;
    border-top: 1px solid rgba(148, 163, 184, 0.35);
    background: linear-gradient(to top, rgba(15, 23, 42, 0.98), rgba(15, 23, 42, 0.92));
}

#cartDrawer .cart-total-label {
    font-size: 0.9rem;
    color: #d1d5db;
}

#cartDrawer #cartTotal {
    font-size: 1.1rem;
    font-weight: 700;
}

/* Checkout button in drawer */
#cartDrawer .btn.btn-primary {
    width: 100%;
    border-radius: var(--cust-radius-pill);
    margin-top: 0.7rem;
    background: linear-gradient(135deg, var(--cust-primary), #1d4ed8);
    border: none;
    box-shadow: 0 10px 26px rgba(37, 99, 235, 0.45);
    text-transform: uppercase;
    font-size: 0.9rem;
    letter-spacing: 0.06em;
    font-weight: 600;
}

#cartDrawer .btn.btn-primary:hover {
    filter: brightness(1.04);
    transform: translateY(-1px);
}

/* === CART BADGE (wherever #cartCount is used) =========================== */

#cartCount {
    min-width: 18px;
    height: 18px;
    border-radius: 999px;
    background: var(--cust-danger);
    color: #fef2f2;
    font-size: 0.7rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 4px;
}

/* If badge is inside any circular floating button, keep it tight */
button .cart-badge,
button #cartCount {
    position: absolute;
    top: 4px;
    right: 4px;
}

/* === RESPONSIVE TWEAKS ================================================== */

@media (max-width: 991px) {
    #productsSection .container {
        padding-inline: 0.75rem;
    }
}

@media (max-width: 575px) {
    .product-card img.product-image,
    .product-card .product-card-no-image {
        height: 170px;
    }
}
</style>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
