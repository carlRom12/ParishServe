<?php
/**
 * admin-dashboard.php
 * ---------------------------------------------------------------------
 * Staff landing page after login. The stat cards count the 7 request
 * tables by status (Pending = submitted + under_review), Recent Activity
 * lists the newest requests (each linked to its row on its type's page)
 * and Waiting for Review splits the topbar bell's count by type -- all
 * straight from the database, so they reflect every change saved on the
 * request and donation pages.
 * ---------------------------------------------------------------------
 */
require __DIR__ . '/includes/auth-guard.php';
require_once __DIR__ . '/includes/request-types.php';

$hour = (int) date('H');
if ($hour < 12) {
    $greeting = 'Good morning';
} elseif ($hour < 18) {
    $greeting = 'Good afternoon';
} else {
    $greeting = 'Good evening';
}

$stats = ps_dashboard_stats(ps_count_requests_by_status($conn));
$recentActivity = ps_fetch_requests($conn, ['limit' => 6]);
$pending = ps_pending_counts($conn);

$waiting = [];
foreach (PS_REQUEST_TYPES as $key => $typeInfo) {
    $waiting[] = ['label' => $typeInfo['plural'], 'href' => $typeInfo['page'], 'count' => $pending['byType'][$key]];
}
$waiting[] = ['label' => 'Donations', 'href' => 'admin-donations.php', 'count' => $pending['donations']];

$pageTitle = 'Admin Dashboard';
$pageCss   = ['dashboard.css', 'admin.css'];
$activeNav = 'dashboard';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/admin-sidebar.php';
?>
<main class="ps-main">

    <section class="db-hero admin-hero">
        <?php require __DIR__ . '/includes/topbar.php'; ?>
        <div class="db-hero-text">
            <h1 class="db-greeting"><?php echo htmlspecialchars($greeting); ?>, <?php echo htmlspecialchars($userFirstName); ?> <span class="db-wave">👋</span></h1>
            <p class="db-subtitle">Here's what's happening across the parish right now.</p>
        </div>
    </section>

    <section class="db-stats">
        <?php foreach ($stats as $stat): ?>
            <div class="stat-card">
                <div class="stat-icon tint-<?php echo htmlspecialchars($stat['tint']); ?>">
                    <?php ps_icon($stat['icon']); ?>
                </div>
                <div class="stat-body">
                    <span class="stat-count" data-count-up><?php echo (int) $stat['count']; ?></span>
                    <span class="stat-label"><?php echo htmlspecialchars($stat['label']); ?></span>
                    <span class="stat-sub"><?php echo htmlspecialchars($stat['sub']); ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </section>

    <section class="admin-dash-grid">

        <div class="ps-card db-requests">
            <div class="ps-card-header">
                <span class="ps-card-title"><?php ps_icon('document'); ?> Recent Activity</span>
                <a href="admin-calendar.php" class="ps-link-more">Calendar <?php ps_icon('arrow-right'); ?></a>
            </div>
            <ul class="db-request-list">
                <?php foreach ($recentActivity as $req): ?>
                    <?php $typeInfo = PS_REQUEST_TYPES[$req['type']]; ?>
                    <li class="db-request-item">
                        <span class="db-request-icon"><?php ps_icon($typeInfo['icon']); ?></span>
                        <span class="db-request-body">
                            <strong><a class="admin-activity-link" href="<?php echo htmlspecialchars(ps_request_admin_url($req['type'], $req['reference_no'])); ?>"><?php echo htmlspecialchars($typeInfo['label']); ?> · <?php echo htmlspecialchars($req['name']); ?></a></strong>
                            <small><?php echo htmlspecialchars($req['reference_no']); ?> · <?php echo htmlspecialchars(date('M j, Y', strtotime($req['created_at']))); ?></small>
                        </span>
                        <span class="ps-status is-<?php echo htmlspecialchars($req['status']); ?>"><?php echo htmlspecialchars(ps_status_label($req['status'])); ?></span>
                    </li>
                <?php endforeach; ?>
                <?php if (!$recentActivity): ?>
                    <li class="db-request-item">
                        <span class="db-request-body"><small>No requests have been submitted yet.</small></span>
                    </li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="db-side">
            <div class="ps-card db-update">
                <div class="ps-card-header">
                    <span class="ps-card-title"><?php ps_icon('clock'); ?> Waiting for Review</span>
                </div>
                <ul class="db-contact-list">
                    <?php foreach ($waiting as $item): ?>
                        <li>
                            <span class="db-contact-text">
                                <strong><?php echo htmlspecialchars($item['label']); ?></strong>
                                <small><?php echo $item['count'] ? $item['count'] . ' awaiting review' : 'Nothing waiting'; ?></small>
                            </span>
                            <a href="<?php echo htmlspecialchars($item['href']); ?>" class="ps-link-more" aria-label="<?php echo htmlspecialchars($item['label']); ?>">
                                <?php if ($item['count']): ?><span class="admin-pending-badge"><?php echo (int) $item['count']; ?></span><?php endif; ?>
                                <?php ps_icon('arrow-right'); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="ps-card db-update">
                <div class="ps-card-header">
                    <span class="ps-card-title"><?php ps_icon('gear'); ?> Quick Links</span>
                </div>
                <ul class="db-contact-list">
                    <li>
                        <span class="db-contact-text"><strong>Reports</strong><small>Summaries by date range, CSV &amp; PDF export</small></span>
                        <a href="admin-reports.php" class="ps-link-more" aria-label="Reports"><?php ps_icon('arrow-right'); ?></a>
                    </li>
                    <li>
                        <span class="db-contact-text"><strong>Announcements</strong><small>Publish parish updates</small></span>
                        <a href="admin-announcements.php" class="ps-link-more" aria-label="Announcements"><?php ps_icon('arrow-right'); ?></a>
                    </li>
                    <?php if (ps_is_super_admin()): ?>
                        <li>
                            <span class="db-contact-text"><strong>Accounts</strong><small>Manage staff roles &amp; account status</small></span>
                            <a href="admin-accounts.php" class="ps-link-more" aria-label="Accounts"><?php ps_icon('arrow-right'); ?></a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

    </section>

</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
