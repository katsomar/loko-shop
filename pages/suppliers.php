<?php
include '../includes/db.php';
// --- FIX: Handle AJAX actions before any output ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'delete_supplier') {
        header('Content-Type: application/json');
        $id = intval($_POST['id']);
        $conn->query("DELETE FROM suppliers WHERE id = $id");
        echo json_encode(['success'=>true]);
        exit;
    }
    if ($_POST['action'] === 'edit_supplier') {
        header('Content-Type: application/json');
        $id = intval($_POST['id']);
        $name = trim($_POST['name'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $contact = trim($_POST['contact'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $stmt = $conn->prepare("UPDATE suppliers SET name=?, location=?, contact=?, email=? WHERE id=?");
        $stmt->bind_param("ssssi", $name, $location, $contact, $email, $id);
        $ok = $stmt->execute();
        $stmt->close();
        echo json_encode(['success'=>$ok]);
        exit;
    }
    if ($_POST['action'] === 'fetch_supplier_transactions') {
        header('Content-Type: application/json');
        $supplier_id = intval($_POST['supplier_id'] ?? 0);
        $rows = [];
        if ($supplier_id > 0) {
            $sql = "SELECT * FROM supplier_transactions WHERE supplier_id = ? ORDER BY date_time DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $supplier_id);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($r = $res->fetch_assoc()) $rows[] = $r;
            $stmt->close();
        }
        echo json_encode(['success'=>true, 'rows'=>$rows]);
        exit;
    }

    /***** UPDATED: pay_supplier_balance handler (supports partial payments & history rows) *****/
    if ($_POST['action'] === 'pay_supplier_balance') {
        header('Content-Type: application/json');
        $trans_id = intval($_POST['trans_id'] ?? 0);
        $amount_paid = floatval($_POST['amount_paid'] ?? 0);

        // Validate
        if ($trans_id <= 0 || $amount_paid <= 0) {
            echo json_encode(['success' => false, 'msg' => 'Invalid transaction or amount.']);
            exit;
        }

        // Fetch original transaction
        $stmt = $conn->prepare("SELECT * FROM supplier_transactions WHERE id = ?");
        $stmt->bind_param("i", $trans_id);
        $stmt->execute();
        $orig = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$orig) {
            echo json_encode(['success' => false, 'msg' => 'Original transaction not found.']);
            exit;
        }

        // Calculate new remaining balance (never negative)
        $orig_balance = floatval($orig['balance'] ?? 0.0);
        $new_balance = $orig_balance - $amount_paid;
        if ($new_balance < 0) $new_balance = 0.0;
        $now = date('Y-m-d H:i:s');

        // Use DB transaction for consistency
        $conn->begin_transaction();
        try {
            // 1) Update original transaction's balance to remaining balance
            $u = $conn->prepare("UPDATE supplier_transactions SET balance = ? WHERE id = ?");
            $u->bind_param("di", $new_balance, $trans_id);
            $u_ok = $u->execute();
            $u->close();

            // 2) Insert a new payment-history row representing this payment
            // Insert all relevant fields and store balance remaining after this payment
            $ins = $conn->prepare("INSERT INTO supplier_transactions (supplier_id, date_time, products_supplied, quantity, unit_price, amount, payment_method, amount_paid, balance) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $supplier_id = intval($orig['supplier_id']);
            $products_supplied = $orig['products_supplied'];
            $quantity = $orig['quantity'];
            $unit_price = floatval($orig['unit_price']);
            $amount = floatval($orig['amount']);
            $payment_method = $orig['payment_method'];
            // types: i s s s d d s d d  => "isssddsdd"
            $ins->bind_param("isssddsdd", $supplier_id, $now, $products_supplied, $quantity, $unit_price, $amount, $payment_method, $amount_paid, $new_balance);
            $i_ok = $ins->execute();
            $insert_id = $ins->insert_id;
            $ins->close();

            if ($u_ok && $i_ok) {
                $conn->commit();
                echo json_encode([
                    'success' => true,
                    'new_balance' => round($new_balance, 2),
                    'cleared' => ($new_balance == 0.0),
                    'payment_id' => $insert_id,
                    'trans_id' => $trans_id
                ]);
                exit;
            } else {
                $conn->rollback();
                echo json_encode(['success' => false, 'msg' => 'Database error while recording payment.']);
                exit;
            }
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'msg' => 'Exception: ' . $e->getMessage()]);
            exit;
        }
    }
    /***** END UPDATED handler *****/

    // --- AJAX HANDLERS for supplier products ---
    if ($_POST['action'] === 'fetch_supplier_products') {
        header('Content-Type: application/json');
        $supplier_id = intval($_POST['supplier_id'] ?? 0);
        $rows = [];
        if ($supplier_id > 0) {
            $stmt = $conn->prepare("SELECT * FROM supplier_products WHERE supplier_id = ? ORDER BY product_name ASC");
            $stmt->bind_param("i", $supplier_id);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($r = $res->fetch_assoc()) $rows[] = $r;
            $stmt->close();
        }
        echo json_encode(['success'=>true, 'rows'=>$rows]);
        exit;
    }
    if ($_POST['action'] === 'add_supplier_product') {
        header('Content-Type: application/json');
        $supplier_id = intval($_POST['supplier_id'] ?? 0);
        $product_name = trim($_POST['product_name'] ?? '');
        $unit_price = floatval($_POST['unit_price'] ?? 0);
        if ($supplier_id > 0 && $product_name !== '' && $unit_price > 0) {
            $stmt = $conn->prepare("INSERT INTO supplier_products (supplier_id, product_name, unit_price) VALUES (?, ?, ?)");
            $stmt->bind_param("isd", $supplier_id, $product_name, $unit_price);
            $ok = $stmt->execute();
            $stmt->close();
            echo json_encode(['success'=>$ok]);
        } else {
            echo json_encode(['success'=>false]);
        }
        exit;
    }
    if ($_POST['action'] === 'edit_supplier_product') {
        header('Content-Type: application/json');
        $id = intval($_POST['id'] ?? 0);
        $product_name = trim($_POST['product_name'] ?? '');
        $unit_price = floatval($_POST['unit_price'] ?? 0);
        if ($id > 0 && $product_name !== '' && $unit_price > 0) {
            $stmt = $conn->prepare("UPDATE supplier_products SET product_name=?, unit_price=? WHERE id=?");
            $stmt->bind_param("sdi", $product_name, $unit_price, $id);
            $ok = $stmt->execute();
            $stmt->close();
            echo json_encode(['success'=>$ok]);
        } else {
            echo json_encode(['success'=>false]);
        }
        exit;
    }
    if ($_POST['action'] === 'delete_supplier_product') {
        header('Content-Type: application/json');
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM supplier_products WHERE id=?");
            $stmt->bind_param("i", $id);
            $ok = $stmt->execute();
            $stmt->close();
            echo json_encode(['success'=>$ok]);
        } else {
            echo json_encode(['success'=>false]);
        }
        exit;
    }
}

// --- FIX: Only handle create supplier if NOT an AJAX POST ---
$is_ajax = ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] !== 'create_supplier');
if (!$is_ajax && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_supplier') {
    include '../includes/db.php'; // Ensure DB is available
    $name = trim($_POST['name'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $email = trim($_POST['email'] ?? '');
    if ($name !== '') {
        $stmt = $conn->prepare("INSERT INTO suppliers (name, location, contact, email) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $location, $contact, $email);
        $ok = $stmt->execute();
        $stmt->close();
        if ($ok) {
            // --- PRG pattern: redirect after successful creation ---
            header("Location: suppliers.php?created=1");
            exit;
        } else {
            $message = "<div class='alert alert-danger'>Error creating supplier.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Supplier name is required.</div>";
    }
}

include '../includes/db.php';
include '../includes/auth.php';
require_role(["admin", "manager", "staff", "super"]);
include '../pages/sidebar.php';
include '../includes/header.php';
?>

<!-- Link external CSS -->
<link rel="stylesheet" href="assets/css/accounting.css">
<link rel="stylesheet" href="assets/css/suppliers.css">

<?php
// --- Always fetch suppliers fresh after any changes ---
$suppliers_res = $conn->query("SELECT * FROM suppliers ORDER BY id DESC");
$suppliers_arr = $suppliers_res ? $suppliers_res->fetch_all(MYSQLI_ASSOC) : [];

// Show success message if redirected after creation
if (isset($_GET['created']) && $_GET['created'] == '1') {
    $message = "<div class='alert alert-success'>Supplier created successfully.</div>";
}
?>

<div class="container-fluid mt-4">
    <!-- Page Title -->
    <h2 class="mb-4" style="color: var(--primary-color); font-weight: 600;">
        <i class="fa-solid fa-truck"></i> Suppliers Management
    </h2>

    <!-- Updated tabs with pill style (matching till_management.php) -->
    <ul class="nav nav-pills tm-main-tabs mb-4" id="suppliersTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link tm-tab-btn active" 
                    id="create-tab"
                    data-bs-toggle="tab" 
                    data-bs-target="#tab-create"
                    type="button"
                    role="tab"
                    aria-controls="tab-create"
                    aria-selected="true">
                Create Supplier
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link tm-tab-btn" 
                    id="manage-tab"
                    data-bs-toggle="tab" 
                    data-bs-target="#tab-manage"
                    type="button"
                    role="tab"
                    aria-controls="tab-manage"
                    aria-selected="false">
                Manage Suppliers
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link tm-tab-btn" 
                    id="transactions-tab"
                    data-bs-toggle="tab" 
                    data-bs-target="#tab-transactions"
                    type="button"
                    role="tab"
                    aria-controls="tab-transactions"
                    aria-selected="false">
                Supplier Transactions
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link tm-tab-btn" 
                    id="products-tab"
                    data-bs-toggle="tab" 
                    data-bs-target="#tab-products"
                    type="button"
                    role="tab"
                    aria-controls="tab-products"
                    aria-selected="false">
                Supplier Products
            </button>
        </li>
    </ul>

    <div class="tab-content" id="suppliersTabsContent">
        <!-- CREATE SUPPLIER TAB -->
        <div class="tab-pane fade show active" id="tab-create">
            <div class="card add-supplier-card mb-4"  style="border-left: 4px solid teal;">
                <div class="card-header">Create Supplier</div>
                <div class="card-body">
                    <?= isset($message) ? $message : "" ?>
                    <form method="POST" class="row g-3">
                        <input type="hidden" name="action" value="create_supplier">
                        <div class="col-md-6">
                            <label class="form-label">Supplier Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact</label>
                            <input type="text" name="contact" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary">Create Supplier</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- MANAGE SUPPLIERS TAB -->
        <div class="tab-pane fade" id="tab-manage">
            <!-- Manage Suppliers Table for Small Devices -->
            <div class="d-block d-md-none mb-4" style="border-left: 4px solid teal;">
              <div class="card transactions-card">
                <div class="card-body">
                  <div class="table-responsive-sm">
                    <div class="transactions-table">
                      <table>
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>Supplier Name</th>
                            <th>Contact</th>
                            <th>Email</th>
                            <th>Address</th>
                            <th>Actions</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                          $i = 1;
                          foreach ($suppliers_arr as $row) {
                            echo "<tr>
                              <td>{$i}</td>
                              <td>" . htmlspecialchars($row['name']) . "</td>
                              <td>" . htmlspecialchars($row['contact']) . "</td>
                              <td>" . htmlspecialchars($row['email']) . "</td>
                              <td>" . htmlspecialchars($row['location']) . "</td>
                              <td>
                                <button class='btn btn-warning btn-sm edit-supplier-btn' 
                                    data-id='{$row['id']}'
                                    data-name='" . htmlspecialchars($row['name']) . "'
                                    data-location='" . htmlspecialchars($row['location']) . "'
                                    data-contact='" . htmlspecialchars($row['contact']) . "'
                                    data-email='" . htmlspecialchars($row['email']) . "' title='Edit'>
                                    <i class='fa fa-edit'></i>
                                </button>
                                <button class='btn btn-danger btn-sm delete-supplier-btn' data-id='{$row['id']}' title='Delete'>
                                    <i class='fa fa-trash'></i>
                                </button>
                              </td>
                            </tr>";
                            $i++;
                          }
                          ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Manage Suppliers Table for Medium and Large Devices -->
            <div class="card mb-4 d-none d-md-block" style="border-left: 4px solid teal;">
              <div class="card-header">Manage Suppliers</div>
              <div class="card-body">
                <div class="transactions-table">
                  <table>
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Supplier Name</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Address</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (count($suppliers_arr) > 0): ?>
                          <?php foreach ($suppliers_arr as $s): ?>
                              <tr data-id="<?= $s['id'] ?>">
                                  <td><?= $s['id'] ?></td>
                                  <td><?= htmlspecialchars($s['name']) ?></td>
                                  <td><?= htmlspecialchars($s['contact']) ?></td>
                                  <td><?= htmlspecialchars($s['email']) ?></td>
                                  <td><?= htmlspecialchars($s['location']) ?></td>
                                  <td>
                                      <button class="btn btn-warning btn-sm edit-supplier-btn" 
                                          data-id="<?= $s['id'] ?>"
                                          data-name="<?= htmlspecialchars($s['name']) ?>"
                                          data-location="<?= htmlspecialchars($s['location']) ?>"
                                          data-contact="<?= htmlspecialchars($s['contact']) ?>"
                                          data-email="<?= htmlspecialchars($s['email']) ?>">Edit</button>
                                      <button class="btn btn-danger btn-sm delete-supplier-btn" data-id="<?= $s['id'] ?>">Delete</button>
                                  </td>
                              </tr>
                          <?php endforeach; ?>
                      <?php else: ?>
                          <tr><td colspan="6" class="text-center text-muted">No suppliers found.</td></tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <!-- Edit Supplier Modal -->
            <div class="modal fade" id="editSupplierModal" tabindex="-1" aria-labelledby="editSupplierModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <form class="modal-content" id="editSupplierForm">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editSupplierModalLabel">Edit Supplier</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body row g-3">
                            <input type="hidden" name="id" id="editSupplierId">
                            <div class="col-md-6">
                                <label class="form-label">Supplier Name</label>
                                <input type="text" name="name" id="editSupplierName" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Location</label>
                                <input type="text" name="location" id="editSupplierLocation" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact</label>
                                <input type="text" name="contact" id="editSupplierContact" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" id="editSupplierEmail" class="form-control">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">OK</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- SUPPLIER TRANSACTIONS TAB -->
        <div class="tab-pane fade" id="tab-transactions">
            <!-- Supplier Transactions Table for Small Devices -->
            <div class="d-block d-md-none mb-4" style="border-left: 4px solid teal;">
              <?php if (count($suppliers_arr) > 0): ?>
                <div class="accordion" id="suppliersAccordionMobile">
                  <?php foreach($suppliers_arr as $s): ?>
                    <div class="accordion-item mb-2">
                      <h2 class="accordion-header" id="headingS<?= $s['id'] ?>m">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseS<?= $s['id'] ?>m" aria-expanded="false" aria-controls="collapseS<?= $s['id'] ?>m">
                          <?= htmlspecialchars($s['name']) ?> — Location: <?= htmlspecialchars($s['location']) ?>
                        </button>
                      </h2>
                      <div id="collapseS<?= $s['id'] ?>m" class="accordion-collapse collapse" aria-labelledby="headingS<?= $s['id'] ?>m" data-bs-parent="#suppliersAccordionMobile">
                        <div class="accordion-body">
                          <div class="card transactions-card">
                            <div class="card-body">
                              <div class="table-responsive-sm">
                                <div class="transactions-table" id="transContainerS<?= $s['id'] ?>m">Loading transactions...</div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
                <script>
                // Mobile Supplier Transactions Accordion
                document.querySelectorAll('#suppliersAccordionMobile .accordion-button').forEach(btn=>{
                  btn.addEventListener('click', async (e) => {
                    const target = e.target.closest('.accordion-button');
                    const collapseId = target.getAttribute('data-bs-target').substring(1);
                    const supplierId = collapseId.replace('collapseS','').replace('m','');
                    const container = document.getElementById('transContainerS'+supplierId+'m');
                    if (container.dataset.loaded) return;
                    container.innerHTML = '<div class="text-muted">Loading...</div>';
                    const form = new FormData();
                    form.append('action','fetch_supplier_transactions');
                    form.append('supplier_id', supplierId);
                    let data;
                    try {
                      const res = await fetch('suppliers.php', {method:'POST', body: form});
                      data = await res.json();
                    } catch (err) {
                      data = {success: false, rows: []};
                    }
                    let html = '<table><thead><tr><th>Date & Time</th><th>Branch</th><th>Products</th><th class="text-center">Quantity</th><th class="text-end">Unit Price</th><th class="text-end">Amount</th><th>Payment Method</th><th class="text-end">Amount Paid</th><th class="text-end">Balance</th><th>Actions</th></tr></thead><tbody>';
                    if (!data.success || !Array.isArray(data.rows) || !data.rows.length) {
                      html += '<tr><td colspan="10" class="text-center text-muted">No transactions found.</td></tr>';
                      html += '</tbody></table>';
                      container.innerHTML = html;
                      container.dataset.loaded = '1';
                      return;
                    }
                    data.rows.forEach(r=>{
                      const paid = parseFloat(r.amount_paid || 0).toFixed(2);
                      const balance = parseFloat(r.balance || 0).toFixed(2);
                      const unitPrice = parseFloat(r.unit_price || 0).toFixed(2);
                      const amount = parseFloat(r.amount || 0).toFixed(2);
                      const products = escapeHtml(r.products_supplied || '');
                      const qty = escapeHtml(r.quantity || '');
                      const method = escapeHtml(r.payment_method || '');
                      const date = escapeHtml(r.date_time || '');
                      const branch = escapeHtml(r.branch || '');
                      let actions = '';
                      if (parseFloat(balance) > 0) {
                        actions = `<button class="btn btn-success btn-sm pay-supplier-btn" data-id="${r.id}" data-balance="${balance}" title="Pay"><i class="fa fa-check"></i></button>`;
                      } else {
                        actions = `<span class="badge bg-success">Cleared</span>`;
                      }
                      html += `<tr>
                        <td>${date}</td>
                        <td>${branch}</td>
                        <td>${products}</td>
                        <td class="text-center">${qty}</td>
                        <td class="text-end">UGX ${unitPrice}</td>
                        <td class="text-end">UGX ${amount}</td>
                        <td>${method}</td>
                        <td class="text-end">UGX ${paid}</td>
                        <td class="text-end">UGX ${balance}</td>
                        <td>${actions}</td>
                      </tr>`;
                    });
                    html += '</tbody></table>';
                    container.innerHTML = html;
                    container.dataset.loaded = '1';

                    // Attach pay button events
                    container.querySelectorAll('.pay-supplier-btn').forEach(btn=>{
                      btn.addEventListener('click', () => {
                        document.getElementById('payTransId').value = btn.getAttribute('data-id');
                        document.getElementById('payAmount').value = btn.getAttribute('data-balance');
                        document.getElementById('payMsg').innerHTML = '';
                        new bootstrap.Modal(document.getElementById('paySupplierModal')).show();
                      });
                    });
                  });
                });
                </script>
              <?php else: ?>
                <div class="alert alert-info">No suppliers found.</div>
              <?php endif; ?>
            </div>
            <!-- Supplier Transactions Table for Medium and Large Devices -->
            <div class="card mb-4 d-none d-md-block" style="border-left: 4px solid teal;">
                <div class="card-header">Supplier Transactions</div>
                <div class="card-body">
                    <?php if (count($suppliers_arr) > 0): ?>
                        <div class="accordion" id="suppliersAccordion">
                            <?php foreach($suppliers_arr as $s): ?>
                                <div class="accordion-item mb-2">
                                    <h2 class="accordion-header" id="headingS<?= $s['id'] ?>">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseS<?= $s['id'] ?>" aria-expanded="false" aria-controls="collapseS<?= $s['id'] ?>">
                                            <?= htmlspecialchars($s['name']) ?> — Location: <?= htmlspecialchars($s['location']) ?>
                                        </button>
                                    </h2>
                                    <div id="collapseS<?= $s['id'] ?>" class="accordion-collapse collapse" aria-labelledby="headingS<?= $s['id'] ?>" data-bs-parent="#suppliersAccordion">
                                        <div class="accordion-body">
                                            <div class="transactions-table" id="transContainerS<?= $s['id'] ?>">Loading transactions...</div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">No suppliers found.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- SUPPLIER PRODUCTS TAB -->
        <div class="tab-pane fade" id="tab-products">
            <div class="card mb-4" style="border-left: 4px solid teal;">
                <div class="card-header">Supplier Products</div>
                <div class="card-body">
                    <?php if (count($suppliers_arr) > 0): ?>
                        <div class="accordion" id="supplierProductsAccordion">
                            <?php foreach($suppliers_arr as $s): ?>
                                <div class="accordion-item mb-2" style="border-left: 4px solid teal; border-top: 1px solid teal; border-right: 1px solid teal;">
                                    <h2 class="accordion-header" id="headingP<?= $s['id'] ?>">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseP<?= $s['id'] ?>" aria-expanded="false" aria-controls="collapseP<?= $s['id'] ?>">
                                            <?= htmlspecialchars($s['name']) ?> — Location: <?= htmlspecialchars($s['location']) ?>
                                        </button>
                                    </h2>
                                    <div id="collapseP<?= $s['id'] ?>" class="accordion-collapse collapse" aria-labelledby="headingP<?= $s['id'] ?>" data-bs-parent="#supplierProductsAccordion">
                                        <div class="accordion-body">
                                            <button class="btn btn-success btn-sm mb-2 add-supply-btn" data-supplier="<?= $s['id'] ?>">Add Supply</button>
                                            <!-- Supplier Products Table for Small Devices -->
                                            <div class="d-block d-md-none mb-2">
                                                <div class="card transactions-card">
                                                    <div class="card-body">
                                                        <div class="table-responsive-sm">
                                                            <div class="transactions-table" id="productsContainer<?= $s['id'] ?>Mobile">Loading products...</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Supplier Products Table for Medium/Large Devices -->
                                            <div class="d-none d-md-block">
                                                <div id="productsContainer<?= $s['id'] ?>">Loading products...</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <script>
                        // Mobile Supplier Products Accordion
                        document.querySelectorAll('#supplierProductsAccordion .accordion-button').forEach(btn=>{
                          btn.addEventListener('click', async (e) => {
                            const target = e.target.closest('.accordion-button');
                            const collapseId = target.getAttribute('data-bs-target').substring(1);
                            const supplierId = collapseId.replace('collapseP','');
                            // Mobile table
                            const containerMobile = document.getElementById('productsContainer'+supplierId+'Mobile');
                            if (containerMobile && !containerMobile.dataset.loaded) {
                              containerMobile.innerHTML = '<div class="text-muted">Loading...</div>';
                              const form = new FormData();
                              form.append('action','fetch_supplier_products');
                              form.append('supplier_id', supplierId);
                              let data;
                              try {
                                const res = await fetch('suppliers.php', {method:'POST', body: form});
                                data = await res.json();
                              } catch (err) {
                                data = {success: false, rows: []};
                              }
                              let html = '<table><thead><tr><th>Product</th><th class="text-end">Unit Price</th><th>Actions</th></tr></thead><tbody>';
                              if (!data.success || !Array.isArray(data.rows) || !data.rows.length) {
                                html += '<tr><td colspan="3" class="text-center text-muted">No products found.</td></tr>';
                                html += '</tbody></table>';
                                containerMobile.innerHTML = html;
                                containerMobile.dataset.loaded = '1';
                                return;
                              }
                              data.rows.forEach(r=>{
                                html += `<tr>
                                  <td>${escapeHtml(r.product_name)}</td>
                                  <td class="text-end">UGX ${parseFloat(r.unit_price).toFixed(2)}</td>
                                  <td>
                                    <button class="btn btn-warning btn-sm edit-supply-btn" data-id="${r.id}" data-supplier="${r.supplier_id}" data-name="${escapeHtml(r.product_name)}" data-price="${r.unit_price}" title="Edit">
                                      <i class="fa fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm delete-supply-btn" data-id="${r.id}" title="Delete">
                                      <i class="fa fa-trash"></i>
                                    </button>
                                  </td>
                                </tr>`;
                              });
                              html += '</tbody></table>';
                              containerMobile.innerHTML = html;
                              containerMobile.dataset.loaded = '1';

                              // Attach edit/delete events
                              containerMobile.querySelectorAll('.edit-supply-btn').forEach(btn=>{
                                btn.addEventListener('click', () => {
                                  document.getElementById('supplierProductModalTitle').textContent = 'Edit Supply';
                                  document.getElementById('spSupplierId').value = btn.getAttribute('data-supplier');
                                  document.getElementById('spProductId').value = btn.getAttribute('data-id');
                                  document.getElementById('spProductName').value = btn.getAttribute('data-name');
                                  document.getElementById('spUnitPrice').value = btn.getAttribute('data-price');
                                  document.getElementById('spMsg').innerHTML = '';
                                  document.getElementById('spSubmitBtn').textContent = 'Update';
                                  new bootstrap.Modal(document.getElementById('supplierProductModal')).show();
                                });
                              });
                              containerMobile.querySelectorAll('.delete-supply-btn').forEach(btn=>{
                                btn.addEventListener('click', async () => {
                                  if (!confirm('Delete this product?')) return;
                                  const form = new FormData();
                                  form.append('action','delete_supplier_product');
                                  form.append('id', btn.getAttribute('data-id'));
                                  const res = await fetch('suppliers.php', {method:'POST', body: form});
                                  const data = await res.json();
                                  if (data.success) location.reload();
                                  else alert('Delete failed');
                                });
                              });
                            }
                            // ...existing code for desktop table...
                          });
                        });
                        </script>
                    <?php else: ?>
                        <div class="alert alert-info">No suppliers found.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pay Supplier Modal -->
<div class="modal fade" id="paySupplierModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Pay Supplier Balance</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="payTransId">
        <div class="mb-3">
          <label class="form-label">Amount to Pay (UGX)</label>
          <input id="payAmount" class="form-control" type="number" step="0.01" min="0">
        </div>
        <div id="payMsg"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary" id="payConfirmBtn">Confirm</button>
      </div>
    </div>
  </div>
</div>

<!-- Add/Edit Supplier Product Modal -->
<div class="modal fade" id="supplierProductModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" id="supplierProductForm">
      <div class="modal-header">
        <h5 class="modal-title" id="supplierProductModalTitle">Add Supply</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="spSupplierId" name="supplier_id">
        <input type="hidden" id="spProductId" name="id">
        <div class="mb-3">
          <label class="form-label">Product Name</label>
          <input name="product_name" id="spProductName" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Unit Price</label>
          <input name="unit_price" id="spUnitPrice" class="form-control" type="number" step="0.01" min="0" required>
        </div>
        <div id="spMsg"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary" id="spSubmitBtn">Add</button>
      </div>
    </form>
  </div>
</div>

<!-- Link external JavaScript -->
<script src="assets/js/suppliers.js"></script>

<?php include '../includes/footer.php'; ?>
