<?php
/**
 * topbar.php
 * ---------------------------------------------------------------------
 * Notification bell + signed-in user chip, placed inside the page's
 * hero/plain header. Uses what includes/auth-guard.php already set:
 *   $userFirstName, $userRole, $conn
 * The bell counts requests + donations still awaiting review and links
 * to admin-dashboard.php, whose Waiting for Review card splits that count
 * by type; initNotificationPoll() in main.js refreshes the badge from
 * pending-count.php while the page is open.
 * ---------------------------------------------------------------------
 */
require_once __DIR__ . '/request-types.php';

$topbarName = $userFirstName ?? 'Guest';
$topbarRole = $userRole ?? '';
$topbarNotifCount = isset($conn) ? ps_pending_counts($conn)['total'] : 0;
$topbarNotifLabel = 'Notifications: ' . $topbarNotifCount . ($topbarNotifCount === 1 ? ' item' : ' items') . ' awaiting review';
?>
<div class="ps-topbar">
    <a href="admin-dashboard.php" class="ps-notif-btn" aria-label="<?php echo htmlspecialchars($topbarNotifLabel); ?>" title="<?php echo htmlspecialchars($topbarNotifLabel); ?>"
       data-notif-poll="pending-count.php" data-notif-interval="45">
        <?php ps_icon('bell'); ?>
        <span class="ps-notif-badge" data-notif-badge<?php echo $topbarNotifCount > 0 ? '' : ' hidden'; ?>><?php echo $topbarNotifCount; ?></span>
    </a>

    <div class="ps-user-chip">
        <span class="ps-user-avatar"><?php echo htmlspecialchars(strtoupper(substr($topbarName, 0, 1))); ?></span>
        <span class="ps-user-info">
            <strong><?php echo htmlspecialchars($topbarName); ?></strong>
            <small><?php echo htmlspecialchars($topbarRole); ?></small>
        </span>
        <?php ps_icon('chevron-down', 'ps-user-chevron'); ?>
    </div>
</div>
