<?php
/**
 * dashboard.php
 * ---------------------------------------------------------------------
 * The parishioner landing page after login. This session is FRONTEND
 * ONLY per the group's call -- everything below is hardcoded sample
 * data shaped exactly like what a real MySQL query would return, so
 * wiring it up next session is just "replace this array with a query
 * result" rather than redesigning the page.
 *
 * WHAT WILL CHANGE WHEN WE WIRE THE BACKEND (left as TODOs inline):
 *   - $userFirstName    -> pulled from $_SESSION['full_name']
 *   - $stats             -> COUNT(*) queries per status, UNIONed across
 *                           all 8 service tables, filtered by the
 *                           logged-in user's contact_number
 *   - $todaySchedule      -> SELECT * FROM daily_schedule ORDER BY sort_order
 *   - $myRequests         -> UNION ALL across service tables, filtered
 *                           by contact_number, ORDER BY created_at DESC LIMIT 3
 *   - $featuredUpdate     -> SELECT * FROM announcements WHERE is_featured=1
 *   - $parishContacts     -> SELECT * FROM parish_contacts ORDER BY sort_order
 *   - $recentAnnouncements-> SELECT * FROM announcements ORDER BY posted_date DESC LIMIT 3
 *
 * The array shapes below match database/schema.sql's seed data on
 * purpose, so nothing about the markup/CSS needs to change later.
 * ---------------------------------------------------------------------
 */

// TODO: replace with $_SESSION['full_name'] once login.php exists.
$userFirstName = 'Juan';

// Time-based greeting -- this part genuinely works right now, no DB
// needed, just the server clock.
$hour = (int) date('H');
if ($hour < 12) {
    $greeting = 'Good morning';
} elseif ($hour < 18) {
    $greeting = 'Good afternoon';
} else {
    $greeting = 'Good evening';
}

// TODO: replace with COUNT(*) queries per status once auth + DB exist.
// Numbers below mirror the demo parishioner seed rows in schema.sql
// (Juan Dela Cruz, contact_number 09171234567) so this looks the same
// as it will once it's real. A brand-new registered account would
// correctly show all zeros here, same as the "qweqwe" account in the
// reference image.
$stats = [
    ['icon' => 'clock',          'label' => 'Pending',   'sub' => 'awaiting review', 'count' => 2, 'tint' => 'amber'],
    ['icon' => 'check-circle',   'label' => 'Approved',  'sub' => 'requests',        'count' => 2, 'tint' => 'green'],
    ['icon' => 'calendar-check', 'label' => 'Scheduled', 'sub' => 'upcoming',        'count' => 2, 'tint' => 'maroon'],
    ['icon' => 'document',       'label' => 'Completed', 'sub' => 'requests',        'count' => 2, 'tint' => 'blue'],
];

// TODO: SELECT * FROM daily_schedule WHERE is_active = 1 ORDER BY sort_order
$todaySchedule = [
    ['time' => '08:00 AM', 'title' => 'Morning Mass',    'location' => 'Main Church',      'dot' => 'blue'],
    ['time' => '10:00 AM', 'title' => 'Baptism Ceremony','location' => 'Main Church',      'dot' => 'red'],
    ['time' => '04:00 PM', 'title' => 'Confession',      'location' => 'Confession Room',  'dot' => 'blue'],
    ['time' => '06:00 PM', 'title' => 'Evening Mass',    'location' => 'Main Church',      'dot' => 'gold'],
];
// 24-hour "H:i" version of each slot's time, only for main.js to read
// off data-time and figure out which slot is happening right now --
// keeps that logic out of the display string above.
foreach ($todaySchedule as &$slot) {
    $slot['time24'] = date('H:i', strtotime($slot['time']));
}
unset($slot);

// TODO: UNION ALL SELECT ... FROM wedding_requests / baptism_requests /
// mass_intentions / etc. WHERE contact_number = $_SESSION['ps_contact']
// ORDER BY created_at DESC LIMIT 3
$myRequests = [
    [
        'icon' => 'ring', 'title' => 'Wedding Request',
        'meta' => 'Submitted · Aug 14, 2026',
        'status' => 'under_review', 'statusLabel' => 'Under Review',
    ],
    [
        'icon' => 'droplet', 'title' => 'Baptism Request',
        'meta' => 'Approved · Aug 10, 2026',
        'status' => 'approved', 'statusLabel' => 'Approved',
    ],
    [
        'icon' => 'chalice', 'title' => 'Mass Intention',
        'meta' => 'Scheduled · Aug 8, 2026',
        'extra' => 'Aug 18, 2026 · 8:00 AM',
        'status' => 'scheduled', 'statusLabel' => 'Scheduled',
    ],
];

// The 5-stage pipeline every request moves through. This is a static
// legend of the whole flow (not tied to one specific request), which
// is why "Submitted" is drawn as the emphasized/first node -- it's
// showing parishioners how the process works in general.
$progressStages = ['Submitted', 'Under Review', 'Approved', 'Scheduled', 'Completed'];

// TODO: SELECT * FROM announcements WHERE is_featured = 1 LIMIT 1
$featuredUpdate = [
    'title' => 'Feast Day Celebration this Sunday!',
    'body'  => "Join us this coming Sunday for the Feast of the Assumption of Mary. There will be a solemn Mass at 9:00 AM followed by a community gathering.",
    'image' => 'assets/images/announcements/feast-day.svg',
];

// TODO: SELECT * FROM parish_contacts ORDER BY sort_order
// NOTE: the reference image's mockup uses "Fr. Juan Dela Cruz" as the
// placeholder Parish Priest name -- matched here for visual accuracy,
// but the capstone paper's actual Resource Persons list (Appendix A)
// names the real parish priest as Fr. Antonio S. Sial. Swap this
// before the real defense/demo so the contact info is accurate.
$parishContacts = [
    ['label' => 'Parish Office',     'name' => null,                 'phone' => '(052) 480-1234'],
    ['label' => 'Parish Priest',     'name' => 'Fr. Juan Dela Cruz',  'phone' => '(052) 480-1234'],
    ['label' => 'Counseling Office', 'name' => null,                 'phone' => '(052) 480-5678'],
];

// TODO: SELECT * FROM announcements ORDER BY posted_date DESC LIMIT 3
$recentAnnouncements = [
    [
        'title' => 'Adoration Every Friday',
        'body'  => 'Eucharistic Adoration is held every Friday after the 6:00 PM Mass.',
        'image' => 'assets/images/announcements/adoration.svg',
        'date'  => 'May 10, 2026',
    ],
    [
        'title' => 'Mass Intentions Now Open',
        'body'  => 'You can now submit your Mass intention requests for June.',
        'image' => 'assets/images/announcements/mass-intentions.svg',
        'date'  => 'May 9, 2026',
    ],
    [
        'title' => 'Church Cleaning Drive',
        'body'  => "Let's keep our church clean and beautiful. See you there!",
        'image' => 'assets/images/announcements/cleaning-drive.svg',
        'date'  => 'May 8, 2026',
    ],
];

$pageTitle = 'Dashboard';
$pageCss   = 'dashboard.css';
$activeNav = 'dashboard';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>
<main class="ps-main">

    <!-- ============================ GREETING ============================ -->
    <section class="db-hero">
        <?php require __DIR__ . '/includes/topbar.php'; ?>
        <div class="db-hero-text">
            <h1 class="db-greeting"><?php echo htmlspecialchars($greeting); ?>, <?php echo htmlspecialchars($userFirstName); ?> <span class="db-wave">👋</span></h1>
            <p class="db-subtitle">Welcome back! Here's what's happening in your parish community.</p>
        </div>
        <div class="db-hero-image">
            <img src="assets/images/hero-banner.svg" alt="Our Lady of the Gate altar">
        </div>
    </section>

    <!-- ============================ STAT CARDS =========================== -->
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

    <!-- ============================ MAIN GRID ============================ -->
    <section class="db-grid">

        <!-- ---- Today's schedule ---- -->
        <div class="ps-card db-today">
            <div class="ps-card-header">
                <span class="ps-card-title"><?php ps_icon('calendar'); ?> Today at Our Lady of the Gate</span>
            </div>
            <ul class="db-timeline">
                <?php foreach ($todaySchedule as $slot): ?>
                    <li class="db-timeline-item" data-time="<?php echo htmlspecialchars($slot['time24']); ?>">
                        <span class="db-timeline-time"><?php echo htmlspecialchars($slot['time']); ?></span>
                        <span class="db-timeline-dot dot-<?php echo htmlspecialchars($slot['dot']); ?>"></span>
                        <span class="db-timeline-body">
                            <strong><?php echo htmlspecialchars($slot['title']); ?></strong>
                            <small><?php ps_icon('building'); ?><?php echo htmlspecialchars($slot['location']); ?></small>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <a href="calendar.php" class="ps-link-more">View full calendar <?php ps_icon('arrow-right'); ?></a>
        </div>

        <!-- ---- My requests ---- -->
        <div class="ps-card db-requests">
            <div class="ps-card-header">
                <span class="ps-card-title"><?php ps_icon('document'); ?> My Requests</span>
                <a href="my-requests.php" class="ps-link-more">View all <?php ps_icon('arrow-right'); ?></a>
            </div>
            <ul class="db-request-list">
                <?php foreach ($myRequests as $req): ?>
                    <li class="db-request-item">
                        <span class="db-request-icon"><?php ps_icon($req['icon']); ?></span>
                        <span class="db-request-body">
                            <strong><?php echo htmlspecialchars($req['title']); ?></strong>
                            <small><?php echo htmlspecialchars($req['meta']); ?></small>
                            <?php if (!empty($req['extra'])): ?>
                                <small class="db-request-extra"><?php ps_icon('calendar-check'); ?><?php echo htmlspecialchars($req['extra']); ?></small>
                            <?php endif; ?>
                        </span>
                        <span class="ps-status is-<?php echo htmlspecialchars($req['status']); ?>"><?php echo htmlspecialchars($req['statusLabel']); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="db-progress">
                <span class="db-progress-label">Request Progress</span>
                <div class="db-progress-track">
                    <?php foreach ($progressStages as $i => $stage): ?>
                        <div class="db-progress-step<?php echo $i === 0 ? ' is-current' : ''; ?>">
                            <span class="db-progress-node"><?php ps_icon($i === 0 ? 'document' : ($i === 1 ? 'clock' : ($i === 2 ? 'check-circle' : ($i === 3 ? 'calendar-check' : 'check-circle')))); ?></span>
                            <span class="db-progress-name"><?php echo htmlspecialchars($stage); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- ---- Right column: parish updates + contacts ---- -->
        <div class="db-side">
            <div class="ps-card db-update">
                <div class="ps-card-header">
                    <span class="ps-card-title"><?php ps_icon('megaphone'); ?> Parish Updates</span>
                </div>
                <div class="db-update-feature">
                    <img src="<?php echo htmlspecialchars($featuredUpdate['image']); ?>" alt="">
                    <div class="db-update-feature-text">
                        <strong><?php echo htmlspecialchars($featuredUpdate['title']); ?></strong>
                        <p><?php echo htmlspecialchars($featuredUpdate['body']); ?></p>
                        <a href="announcements.php" class="ps-link-more">Read more <?php ps_icon('arrow-right'); ?></a>
                    </div>
                </div>
            </div>

            <div class="ps-card db-contacts">
                <div class="ps-card-header">
                    <span class="ps-card-title"><?php ps_icon('people'); ?> Parish Contacts</span>
                </div>
                <ul class="db-contact-list">
                    <?php foreach ($parishContacts as $c): ?>
                        <li>
                            <span class="db-contact-text">
                                <strong><?php echo htmlspecialchars($c['label']); ?></strong>
                                <?php if ($c['name']): ?><small><?php echo htmlspecialchars($c['name']); ?></small><?php endif; ?>
                            </span>
                            <a href="tel:<?php echo htmlspecialchars(preg_replace('/[^0-9+]/', '', $c['phone'])); ?>" class="db-contact-phone">
                                <?php echo htmlspecialchars($c['phone']); ?> <?php ps_icon('phone'); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <a href="contacts.php" class="ps-link-more">View all contacts <?php ps_icon('arrow-right'); ?></a>
            </div>
        </div>

    </section>

    <!-- ============================ RECENT ANNOUNCEMENTS ================= -->
    <section class="ps-card db-announcements">
        <div class="ps-card-header">
            <span class="ps-card-title"><?php ps_icon('megaphone'); ?> Recent Announcements</span>
            <a href="announcements.php" class="ps-link-more">View all <?php ps_icon('arrow-right'); ?></a>
        </div>
        <div class="db-announcement-grid">
            <?php foreach ($recentAnnouncements as $a): ?>
                <a href="announcements.php" class="db-announcement-card">
                    <img src="<?php echo htmlspecialchars($a['image']); ?>" alt="">
                    <div class="db-announcement-text">
                        <strong><?php echo htmlspecialchars($a['title']); ?></strong>
                        <p><?php echo htmlspecialchars($a['body']); ?></p>
                        <small><?php echo htmlspecialchars($a['date']); ?></small>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
