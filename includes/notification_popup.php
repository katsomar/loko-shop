<?php
include 'db.php'; // Include your database connection

// Check if the popup has already been shown in this session
if (!isset($_SESSION['shown_login_notifications'])) {
    $_SESSION['shown_login_notifications'] = false;
}

// Only show popup if user just logged in (flag not set)
if (!isset($_SESSION['shown_login_notifications']) || $_SESSION['shown_login_notifications'] !== true) {
    
    // Fetch user's notifications
    $user_id = $_SESSION['user_id'] ?? 0;
    $user_branch = $_SESSION['branch_id'] ?? null;
    $user_role = $_SESSION['role'] ?? 'staff';
    $today = date('Y-m-d');
    
    $notifications = [];
    $total_count = 0;
    
    // Fetch shop debtors (overdue) - FIX: Use backticks for column names with hyphens
    $where_shop = ($user_role === 'staff' && $user_branch) 
        ? "WHERE d.`branch_id` = $user_branch AND d.due_date IS NOT NULL AND d.due_date <= '$today' AND d.is_paid = 0"
        : "WHERE d.due_date IS NOT NULL AND d.due_date <= '$today' AND d.is_paid = 0";
    
    $shop_query = $conn->query("
        SELECT d.id, d.debtor_name, d.balance, d.due_date, b.name as branch_name, 'shop_debtor' as type
        FROM debtors d 
        LEFT JOIN branch b ON d.`branch_id` = b.id 
        $where_shop 
        ORDER BY d.due_date ASC 
        LIMIT 5
    ");
    
    if ($shop_query) {
        while ($row = $shop_query->fetch_assoc()) {
            $days_overdue = floor((strtotime($today) - strtotime($row['due_date'])) / 86400);
            $notifications[] = [
                'type' => 'shop_debtor',
                'icon' => 'fa-store',
                'color' => $days_overdue > 7 ? 'danger' : 'warning',
                'title' => htmlspecialchars($row['debtor_name']),
                'message' => 'Shop debt: UGX ' . number_format($row['balance'], 2) . ' - ' . $days_overdue . ' days overdue',
                'branch' => $row['branch_name'] ?? 'Unknown',
                'time' => date('M d, Y', strtotime($row['due_date']))
            ];
            $total_count++;
        }
    }
    
    // Fetch customer debtors (overdue)
    $where_cust = "WHERE ct.status = 'debtor' AND ct.due_date IS NOT NULL AND ct.due_date <= '$today'";
    $cust_query = $conn->query("
        SELECT ct.id, c.name as customer_name, ct.amount_credited, ct.due_date, 'customer_debtor' as type
        FROM customer_transactions ct
        JOIN customers c ON ct.customer_id = c.id
        $where_cust
        ORDER BY ct.due_date ASC
        LIMIT 3
    ");
    
    if ($cust_query) {
        while ($row = $cust_query->fetch_assoc()) {
            $days_overdue = floor((strtotime($today) - strtotime($row['due_date'])) / 86400);
            $notifications[] = [
                'type' => 'customer_debtor',
                'icon' => 'fa-users',
                'color' => $days_overdue > 7 ? 'danger' : 'warning',
                'title' => htmlspecialchars($row['customer_name']),
                'message' => 'Customer debt: UGX ' . number_format($row['amount_credited'], 2) . ' - ' . $days_overdue . ' days overdue',
                'branch' => '',
                'time' => date('M d, Y', strtotime($row['due_date']))
            ];
            $total_count++;
        }
    }
    
    // Fetch low stock products
    $where_stock = ($user_role === 'staff' && $user_branch) 
        ? "WHERE p.`branch-id` = $user_branch AND p.stock < 10"
        : "WHERE p.stock < 10";
    
    $stock_query = $conn->query("
        SELECT p.id, p.name, p.stock, b.name as branch_name, 'low_stock' as type
        FROM products p
        LEFT JOIN branch b ON p.`branch-id` = b.id
        $where_stock
        ORDER BY p.stock ASC
        LIMIT 3
    ");
    
    if ($stock_query) {
        while ($row = $stock_query->fetch_assoc()) {
            $stock = intval($row['stock']);
            $notifications[] = [
                'type' => 'low_stock',
                'icon' => 'fa-box',
                'color' => $stock < 3 ? 'danger' : 'warning',
                'title' => htmlspecialchars($row['name']),
                'message' => 'Low stock: Only ' . $stock . ' items remaining',
                'branch' => $row['branch_name'] ?? 'Unknown',
                'time' => 'Now'
            ];
            $total_count++;
        }
    }
    
    // Get total count for all notifications - FIX: Use backticks for column names
    $count_shop = $conn->query("SELECT COUNT(*) as cnt FROM debtors d $where_shop");
    $count_cust = $conn->query("SELECT COUNT(*) as cnt FROM customer_transactions ct WHERE ct.status = 'debtor' AND ct.due_date IS NOT NULL AND ct.due_date <= '$today'");
    $count_stock = $conn->query("SELECT COUNT(*) as cnt FROM products p $where_stock");
    
    $total_count = 0;
    if ($count_shop) $total_count += intval($count_shop->fetch_assoc()['cnt'] ?? 0);
    if ($count_cust) $total_count += intval($count_cust->fetch_assoc()['cnt'] ?? 0);
    if ($count_stock) $total_count += intval($count_stock->fetch_assoc()['cnt'] ?? 0);
    
    // Only show popup if there are notifications
    if ($total_count > 0):
?>
<!-- External CSS -->
<link rel="stylesheet" href="../pages/assets/css/notification_popup.css">

<!-- Notification Popup Overlay -->
<div id="notificationPopupOverlay" class="notification-popup-overlay">
    <div id="notificationPopup" class="notification-popup">
        <!-- Close Button -->
        <button class="notification-popup-close" id="closeNotificationPopup" aria-label="Close notifications">
            <i class="fa fa-times"></i>
        </button>
        
        <!-- Header -->
        <div class="notification-popup-header">
            <div class="notification-popup-icon">
                <i class="fa fa-bell"></i>
            </div>
            <h2 class="notification-popup-title">
                You have <span class="notification-count-animate" data-count="<?= min($total_count, 99) ?>">0</span> new notifications
            </h2>
            <p class="notification-popup-subtitle">Stay updated with your business activities</p>
        </div>
        
        <!-- Notifications List -->
        <div class="notification-popup-list">
            <?php 
            $displayed = 0;
            foreach (array_slice($notifications, 0, 7) as $index => $notif): 
                $displayed++;
            ?>
                <div class="notification-popup-item" style="animation-delay: <?= ($index * 0.08) ?>s;" data-type="<?= $notif['type'] ?>">
                    <div class="notification-popup-item-icon notification-icon-<?= $notif['color'] ?>">
                        <i class="fa <?= $notif['icon'] ?>"></i>
                    </div>
                    <div class="notification-popup-item-content">
                        <h4 class="notification-popup-item-title"><?= $notif['title'] ?></h4>
                        <p class="notification-popup-item-message"><?= $notif['message'] ?></p>
                        <?php if (!empty($notif['branch'])): ?>
                            <span class="notification-popup-item-branch"><?= htmlspecialchars($notif['branch']) ?></span>
                        <?php endif; ?>
                        <span class="notification-popup-item-time"><?= $notif['time'] ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Footer -->
        <div class="notification-popup-footer">
            <?php if ($total_count > 7): ?>
                <a href="../pages/notification.php" class="notification-popup-view-more">
                    View All <?= $total_count ?> Notifications <i class="fa fa-arrow-right"></i>
                </a>
            <?php endif; ?>
            <button class="notification-popup-dismiss" id="dismissNotificationPopup">
                Got it, thanks!
            </button>
        </div>
    </div>
</div>

<!-- External JavaScript -->
<script src="../pages/assets/js/notification_popup.js" data-php-self="<?= $_SERVER['PHP_SELF'] ?>"></script>
<?php
    endif; // end if total_count > 0
} // end if not shown
?>
