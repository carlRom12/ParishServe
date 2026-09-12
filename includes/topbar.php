<?php
/**
 * topbar.php
 * ---------------------------------------------------------------------
 * Notification bell + signed-in user chip, placed inside the page's
 * hero/plain header. Uses what the calling page already set:
 *   $userFirstName, $userRole
 *   $notifCount  optional; the badge is hidden when unset or 0
 * No real session yet -- these are still hardcoded by each page.
 * ---------------------------------------------------------------------
 */
$topbarName = $userFirstName ?? 'Guest';
$topbarRole = $userRole ?? '';
$topbarNotifCount = (int) ($notifCount ?? 0);
?>
<div class="ps-topbar">
    <button type="button" class="ps-notif-btn" aria-label="Notifications">
        <?php ps_icon('bell'); ?>
        <?php if ($topbarNotifCount > 0): ?>
            <span class="ps-notif-badge"><?php echo $topbarNotifCount; ?></span>
        <?php endif; ?>
    </button>

    <div class="ps-user-chip">
        <span class="ps-user-avatar"><?php echo htmlspecialchars(strtoupper(substr($topbarName, 0, 1))); ?></span>
        <span class="ps-user-info">
            <strong><?php echo htmlspecialchars($topbarName); ?></strong>
            <small><?php echo htmlspecialchars($topbarRole); ?></small>
        </span>
        <?php ps_icon('chevron-down', 'ps-user-chevron'); ?>
    </div>
</div>
