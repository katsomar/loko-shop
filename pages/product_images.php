<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';
require_role(['admin', 'manager', 'staff']);

// Get user info
$user_role = $_SESSION['role'];
$user_branch = $_SESSION['branch_id'] ?? null;

// Ensure image_path column exists (defensive)
$checkCol = $conn->query("
    SELECT COLUMN_NAME 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'products'
      AND COLUMN_NAME = 'image_path'
");
if (!$checkCol || $checkCol->num_rows === 0) {
    $conn->query("ALTER TABLE products ADD COLUMN image_path VARCHAR(255) NULL");
    if ($conn->errno) {
        @$conn->query("ALTER TABLE products ADD COLUMN image_path VARCHAR(255) NULL");
    }
}

// --- AJAX HANDLERS (before any output) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    
    $action = $_POST['action'];
    $product_id = intval($_POST['product_id'] ?? 0);
    
    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product']);
        exit;
    }
    
    // Fetch product
    $stmt = $conn->prepare("SELECT id, name, `branch-id`, image_path FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }
    
    // SECURITY: Staff can only manage images for their branch
    if ($user_role === 'staff' && $product['branch-id'] != $user_branch) {
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit;
    }
    
    // Upload image
    if ($action === 'upload') {
        if (empty($_FILES['image']['name'])) {
            echo json_encode(['success' => false, 'message' => 'No image selected']);
            exit;
        }
        
        $file = $_FILES['image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($ext, $allowed, true)) {
            echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, WEBP allowed']);
            exit;
        }
        
        if ($file['size'] > $maxSize) {
            echo json_encode(['success' => false, 'message' => 'Image too large (max 2MB)']);
            exit;
        }
        
        // FIXED: Get correct uploads directory path
        // From /pages/product_images.php, go up to root: ../ = /business-system/
        $uploadDir = realpath(__DIR__ . '/..') . '/uploads';
        
        if (!is_dir($uploadDir)) {
            if (!@mkdir($uploadDir, 0775, true)) {
                echo json_encode(['success' => false, 'message' => 'Failed to create uploads directory']);
                exit;
            }
        }
        
        $productDir = $uploadDir . '/product_images';
        if (!is_dir($productDir)) {
            if (!@mkdir($productDir, 0775, true)) {
                echo json_encode(['success' => false, 'message' => 'Failed to create product_images directory']);
                exit;
            }
        }

        // Generate unique filename
        $filename   = 'prod_' . $product['id'] . '_' . time() . '.' . $ext;
        $targetPath = $productDir . '/' . $filename;

        // VERIFY directory exists and is writable
        if (!is_writable($productDir)) {
            echo json_encode(['success' => false, 'message' => 'Upload directory is not writable. Check permissions.']);
            exit;
        }

        // Upload file
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            echo json_encode(['success' => false, 'message' => 'Failed to save image. Check: ' . error_get_last()['message']]);
            exit;
        }

        // Verify file was actually created
        if (!file_exists($targetPath)) {
            echo json_encode(['success' => false, 'message' => 'File upload verified but file not found on disk']);
            exit;
        }

        // Delete old image file if exists
        if (!empty($product['image_path'])) {
            $oldPath = realpath(__DIR__ . '/..') . '/' . $product['image_path'];
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        // Store relative path from app root in DATABASE
        // Path should be: uploads/product_images/prod_123_timestamp.jpg
        $dbPath = 'uploads/product_images/' . $filename;
        
        $ustmt = $conn->prepare("UPDATE products SET image_path = ? WHERE id = ?");
        $ustmt->bind_param("si", $dbPath, $product['id']);
        if ($ustmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Image saved successfully', 'image_path' => $dbPath]);
        } else {
            // Delete the uploaded file if DB update fails
            @unlink($targetPath);
            echo json_encode(['success' => false, 'message' => 'DB error: ' . $ustmt->error]);
        }
        $ustmt->close();
        exit;
    }
    
    // Delete image
    if ($action === 'delete') {
        $stmt = $conn->prepare("SELECT image_path FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($row && !empty($row['image_path'])) {
            $oldPath = __DIR__ . '/../' . $row['image_path'];
            if (is_file($oldPath)) @unlink($oldPath);
        }
        
        $update = $conn->prepare("UPDATE products SET image_path = NULL WHERE id = ?");
        $update->bind_param("i", $product_id);
        
        if ($update->execute()) {
            echo json_encode(['success' => true, 'message' => '✅ Image removed']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Delete failed']);
        }
        $update->close();
        exit;
    }
    
    echo json_encode(['success' => false, 'message' => 'Unknown action']);
    exit;
}

// Include layout
if ($user_role === 'staff') {
    include '../pages/sidebar_staff.php';
} else {
    include '../pages/sidebar.php';
}
include '../includes/header.php';

// Get branch filter
$selected_branch = null;
if ($user_role === 'staff' && $user_branch) {
    $selected_branch = $user_branch;
} elseif (!empty($_GET['branch'])) {
    $selected_branch = intval($_GET['branch']);
}

// NEW: Get search term
$search = trim($_GET['search'] ?? '');

// Build query
$where = "";
if ($selected_branch) {
    $where = "WHERE `branch-id` = $selected_branch";
} elseif ($user_role === 'staff' && $user_branch) {
    $where = "WHERE `branch-id` = $user_branch";
}

// Add search filter if provided
if ($search !== '') {
    if ($where !== "") {
        $where .= " AND name LIKE '%" . $conn->real_escape_string($search) . "%'";
    } else {
        $where = "WHERE name LIKE '%" . $conn->real_escape_string($search) . "%'";
    }
}

// Fetch products
$sql = "
    SELECT p.id, p.name, p.image_path, p.`branch-id`, b.name AS branch_name
    FROM products p
    JOIN branch b ON p.`branch-id` = b.id
    $where
    ORDER BY b.name, p.name
";
$result = $conn->query($sql);
$products = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Fetch branches
$branches = $conn->query("SELECT id, name FROM branch ORDER BY name");
?>

<!-- Link external CSS -->
<link rel="stylesheet" href="assets/css/product_images.css">

<div class="container-fluid mt-4">
    <div class="card mb-4" style="border-left: 4px solid teal;">
        <div class="card-header d-flex justify-content-between align-items-center title-card">
            <span><i class="fa-solid fa-image me-2"></i> Product Images Manager</span>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" class="row g-3 align-items-end mb-3">
                <?php if ($user_role === 'staff'): ?>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Branch</label>
                        <input type="text" class="form-control" value="My Branch" disabled>
                    </div>
                <?php else: ?>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Branch</label>
                        <select name="branch" class="form-select" onchange="this.form.submit()">
                            <option value="">-- All Branches --</option>
                            <?php
                            if ($branches) {
                                while ($b = $branches->fetch_assoc()) {
                                    $sel = ($selected_branch == $b['id']) ? 'selected' : '';
                                    echo "<option value='{$b['id']}' {$sel}>" . htmlspecialchars($b['name']) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                <?php endif; ?>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Search Product</label>
                    <input type="text" name="search" class="form-control" placeholder="Search by name" value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>

            <!-- Products table -->
            <div class="transactions-table">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Branch</th>
                            <th>Product Name</th>
                            <th>Current Image</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($products): ?>
                        <?php $i = 1; foreach ($products as $row): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($row['branch_name']) ?></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td>
                                    <?php if (!empty($row['image_path'])): ?>
                                        <img src="../<?= htmlspecialchars($row['image_path']) ?>" alt="Product" class="product-image-thumb" title="<?= htmlspecialchars($row['name']) ?>">
                                    <?php else: ?>
                                        <div class="product-image-placeholder">📷 No Image</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button 
                                            type="button"
                                            class="btn btn-sm btn-primary btn-upload-image"
                                            data-id="<?= $row['id'] ?>"
                                            data-name="<?= htmlspecialchars($row['name']) ?>"
                                            title="Upload/Change Image"
                                        >
                                            <i class="fa fa-upload"></i> Upload
                                        </button>
                                        <?php if (!empty($row['image_path'])): ?>
                                        <button 
                                            type="button"
                                            class="btn btn-sm btn-danger btn-delete-image"
                                            data-id="<?= $row['id'] ?>"
                                            data-name="<?= htmlspecialchars($row['name']) ?>"
                                            title="Remove Image"
                                        >
                                            <i class="fa fa-trash"></i> Remove
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">No products found.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Upload Image Modal -->
<div class="modal fade" id="imageUploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:var(--primary-color);color:#fff;">
                <h5 class="modal-title">Upload Product Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="imageProductName" class="fw-semibold mb-3"></p>
                <form id="imageUploadForm">
                    <input type="hidden" name="product_id" id="imageProductId">
                    <input type="hidden" name="action" value="upload_image">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Image (JPG, PNG, WEBP - Max 2MB)</label>
                        <input type="file" name="product_image" id="productImageInput" class="form-control" accept="image/jpeg,image/png,image/webp" required>
                    </div>
                    <div class="mb-3" id="imagePreviewWrapper" style="display:none;">
                        <label class="form-label fw-semibold">Preview</label><br>
                        <img id="imagePreview" src="#" alt="Preview" class="product-image-thumb">
                    </div>
                    <div id="imageUploadMessage"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button id="confirmImageUpload" class="btn btn-primary">Save Image</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Image Modal -->
<div class="modal fade" id="imageDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Remove Product Image</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="deleteImageMessage" class="fw-semibold mb-3"></p>
                <input type="hidden" id="deleteImageProductId">
                <div id="deleteImageFeedback"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button id="confirmImageDelete" class="btn btn-danger">Remove</button>
            </div>
        </div>
    </div>
</div>

<!-- Link external JavaScript -->
<script src="assets/js/product_images.js"></script>

<?php include '../includes/footer.php'; ?>
