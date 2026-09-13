<?php
/**
 * admin-dashboard.php
 * ---------------------------------------------------------------------
 * Admin landing page. FRONTEND ONLY, same as dashboard.php: everything
 * below is hardcoded sample data shaped like what a real query would
 * return (numbers mirror database/schema.sql's seed rows so this looks
 * the same once it's wired up), not a live DB read.
 *
 * WHAT WOULD CHANGE WHEN A BACKEND IS ADDED:
 *   - $adminFirstName -> $_SESSION['full_name'] once admin login exists
 *   - $stats           -> COUNT(*) grouped by status, UNIONed across all
 *                         8 request-ish tables (schema.sql's own notes
 *                         already call for this exact query shape)
 *   - $recentActivity   -> UNION ALL ... ORDER BY created_at DESC LIMIT 8
 * ---------------------------------------------------------------------
 */

$adminFirstName = 'Parish';

$hour = (int) date('H');
if ($hour < 12) {
    $greeting = 'Good morning';
} elseif ($hour < 18) {
    $greeting = 'Good afternoon';
} else {
    $greeting = 'Good evening';
}

// Mirrors the seed data in database/schema.sql exactly (4 pending, 2
// approved, 2 scheduled, 2 completed across the 8 request tables).
$stats = [
    ['icon' => 'clock',          'label' => 'Pending',   'sub' => 'awaiting review', 'count' => 4, 'tint' => 'amber'],
    ['icon' => 'check-circle',   'label' => 'Approved',  'sub' => 'requests',        'count' => 2, 'tint' => 'green'],
    ['icon' => 'calendar-check', 'label' => 'Scheduled', 'sub' => 'upcoming',        'count' => 2, 'tint' => 'maroon'],
    ['icon' => 'document',       'label' => 'Completed', 'sub' => 'requests',        'count' => 2, 'tint' => 'blue'],
];

$recentActivity = [
    ['icon' => 'droplet',  'type' => 'Baptism',              'name' => 'Baby Gabriel Reyes',            'ref' => 'BAP-2026-0002', 'status' => 'submitted',    'statusLabel' => 'Submitted',    'date' => 'Aug 16, 2026'],
    ['icon' => 'people',   'type' => 'Counseling',           'name' => 'Juan Dela Cruz',                 'ref' => 'CNS-2026-0001', 'status' => 'submitted',    'statusLabel' => 'Submitted',    'date' => 'Aug 15, 2026'],
    ['icon' => 'ring',     'type' => 'Wedding',              'name' => 'Maria Santos & Juan Dela Cruz',  'ref' => 'WED-2026-0001', 'status' => 'under_review', 'statusLabel' => 'Under Review', 'date' => 'Aug 14, 2026'],
    ['icon' => 'building', 'type' => 'Facility Reservation', 'name' => 'Angela Reyes',                   'ref' => 'FAC-2026-0002', 'status' => 'under_review', 'statusLabel' => 'Under Review', 'date' => 'Aug 13, 2026'],
    ['icon' => 'building', 'type' => 'Facility Reservation', 'name' => 'Juan Dela Cruz',                 'ref' => 'FAC-2026-0001', 'status' => 'approved',     'statusLabel' => 'Approved',     'date' => 'Aug 12, 2026'],
    ['icon' => 'droplet',  'type' => 'Baptism',              'name' => 'Baby Sofia Dela Cruz',           'ref' => 'BAP-2026-0001', 'status' => 'approved',     'statusLabel' => 'Approved',     'date' => 'Aug 10, 2026'],
];

$pageTitle = 'Admin Dashboard';
$pageCss   = ['dashboard.css', 'admin.css'];
$userFirstName = $adminFirstName;
$userRole = 'Admin';
$activeNav = 'dashboard';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/admin-sidebar.php';
?>
<main class="ps-main">

    <section class="db-hero admin-hero">
        <?php require __DIR__ . '/includes/topbar.php'; ?>
        <div class="db-hero-text">
            <h1 class="db-greeting"><?php echo htmlspecialchars($greeting); ?>, <?php echo htmlspecialchars($adminFirstName); ?> <span class="db-wave">👋</span></h1>
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
                    <span class="stat-count"><?php echo (int) $stat['count']; ?></span>
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
                <a href="admin-requests.php" class="ps-link-more">View all <?php ps_icon('arrow-right'); ?></a>
            </div>
            <ul class="db-request-list">
                <?php foreach ($recentActivity as $req): ?>
                    <li class="db-request-item">
                        <span class="db-request-icon"><?php ps_icon($req['icon']); ?></span>
                        <span class="db-request-body">
                            <strong><?php echo htmlspecialchars($req['type']); ?> · <?php echo htmlspecialchars($req['name']); ?></strong>
                            <small><?php echo htmlspecialchars($req['ref']); ?> · <?php echo htmlspecialchars($req['date']); ?></small>
                        </span>
                        <span class="ps-status is-<?php echo htmlspecialchars($req['status']); ?>"><?php echo htmlspecialchars($req['statusLabel']); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="db-side">
            <div class="ps-card db-update">
                <div class="ps-card-header">
                    <span class="ps-card-title"><?php ps_icon('gear'); ?> Quick Links</span>
                </div>
                <ul class="db-contact-list">
                    <li>
                        <span class="db-contact-text"><strong>Requests</strong><small>Review &amp; update sacrament/service requests</small></span>
                        <a href="admin-requests.php" class="ps-link-more"><?php ps_icon('arrow-right'); ?></a>
                    </li>
                    <li>
                        <span class="db-contact-text"><strong>Donations</strong><small>Verify proof of payment</small></span>
                        <a href="admin-donations.php" class="ps-link-more"><?php ps_icon('arrow-right'); ?></a>
                    </li>
                    <li>
                        <span class="db-contact-text"><strong>Announcements</strong><small>Publish parish updates</small></span>
                        <a href="admin-announcements.php" class="ps-link-more"><?php ps_icon('arrow-right'); ?></a>
                    </li>
                </ul>
            </div>
        </div>

    </section>

</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
