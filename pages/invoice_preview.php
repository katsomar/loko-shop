<?php
// Invoice preview page - generates invoice number and displays invoice
$cart = json_decode($_POST['cart'] ?? '[]', true);
$total = floatval($_POST['total'] ?? 0);
$payment_method = $_POST['payment_method'] ?? 'Customer File';
$customer_id = intval($_POST['customer_id'] ?? 0);
$customer_name = $_POST['customer_name'] ?? 'Unknown Customer';
$customer_email = $_POST['customer_email'] ?? '';
$customer_contact = $_POST['customer_contact'] ?? '';
$amount_paid = floatval($_POST['amount_paid'] ?? 0);
$balance = floatval($_POST['balance'] ?? 0);
$invoice_no = $_POST['invoice_no'] ?? 'N/A'; // USE EXISTING INVOICE NUMBER
$due_date = $_POST['due_date'] ?? ''; // GET DUE DATE FROM POST

$date = date('M d, Y');

// Calculate due date display
if ($due_date) {
    // Use the actual due date from database
    $due_date_display = date('M d, Y', strtotime($due_date));
} else {
    // Fallback: 30 days from now if no due date set
    $due_date_display = date('M d, Y', strtotime('+30 days'));
}

// Company details (customize as needed)
$company_name = "Zylisor Thread & Weave";
$company_tagline = "Life Redefining Sales";
$company_address = "Kinton Town Fabricae";
$company_city = "New York Sales 1207";
$company_tin = "1234";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice <?= htmlspecialchars($invoice_no) ?></title>
    <link rel="stylesheet" href="assets/css/invoice_preview.css">
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="company-info">
                <div class="company-logo">Z</div>
                <div class="company-name"><?= htmlspecialchars($company_name) ?></div>
                <div class="company-tagline"><?= htmlspecialchars($company_tagline) ?></div>
                <div class="company-address">
                    <?= htmlspecialchars($company_address) ?><br>
                    <?= htmlspecialchars($company_city) ?><br>
                    TIN: <?= htmlspecialchars($company_tin) ?>
                </div>
            </div>
            <div class="invoice-title-section">
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-number"><?= htmlspecialchars($invoice_no) ?></div>
            </div>
        </div>

        <!-- Body -->
        <div class="invoice-body">
            <!-- Meta Info -->
            <div class="invoice-meta">
                <div class="bill-to">
                    <strong>Bill To:</strong>
                    <p><?= htmlspecialchars($customer_name) ?></p>
                    <?php if ($customer_email): ?>
                        <p><?= htmlspecialchars($customer_email) ?></p>
                    <?php endif; ?>
                    <?php if ($customer_contact): ?>
                        <p><?= htmlspecialchars($customer_contact) ?></p>
                    <?php endif; ?>
                </div>
                <div class="invoice-details">
                    <strong>Invoice Date:</strong>
                    <p><?= $date ?></p>
                    <strong style="margin-top:1rem;">Due Date:</strong>
                    <p><?= $due_date_display ?></p>
                </div>
            </div>

            <!-- Items Table -->
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item & Description</th>
                        <th class="text-right">Qty</th>
                        <th class="text-right">Rate</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $i = 1;
                    foreach ($cart as $item): 
                        $subtotal = $item['price'] * $item['quantity'];
                    ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td>
                            <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
                            <small style="color:#999;">SKU: <?= $item['id'] ?></small>
                        </td>
                        <td class="text-right"><?= $item['quantity'] ?></td>
                        <td class="text-right">UGX <?= number_format($item['price'], 2) ?></td>
                        <td class="text-right">UGX <?= number_format($subtotal, 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Summary -->
            <table class="summary-table">
                <tr class="summary-row">
                    <td>Sub Total</td>
                    <td class="text-right">UGX <?= number_format($total, 2) ?></td>
                </tr>
                <tr class="summary-row">
                    <td>Tax Rate</td>
                    <td class="text-right">0.00%</td>
                </tr>
                <tr class="total-row">
                    <td>Total</td>
                    <td class="text-right">UGX <?= number_format($total, 2) ?></td>
                </tr>
                <?php if ($amount_paid > 0): ?>
                <tr class="summary-row">
                    <td>Amount Paid</td>
                    <td class="text-right">UGX <?= number_format($amount_paid, 2) ?></td>
                </tr>
                <?php endif; ?>
                <tr class="balance-due-row">
                    <td>Balance Due</td>
                    <td class="text-right">UGX <?= number_format($balance, 2) ?></td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="invoice-footer">
            <strong>Terms & Conditions</strong>
            <p>Payment is due by <?= $due_date_display ?>. Late payments may incur additional charges as per the applicable laws. Thank you for your business!</p>
        </div>
    </div>

    <button class="print-btn" onclick="window.print()">
        <i class="fa fa-print"></i> Print Invoice
    </button>
</body>
</html>
