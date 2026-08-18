<?php
/**
 * announcements.php
 * ---------------------------------------------------------------------
 * Same deal as dashboard.php: FRONTEND ONLY this session, hardcoded
 * data shaped like what the real queries will return later. See the
 * TODO comments next to each array for what replaces it once
 * database/schema.sql's `announcements` table is actually wired up.
 *
 * Interactive bits that work RIGHT NOW with zero backend (all in
 * assets/js/main.js):
 *   - the Featured Announcement carousel (prev/next + dot nav)
 *   - category filter tabs + the search box (filters the row list
 *     that's already in the DOM -- no page reload)
 *   - "Load more" reveals 2 more hardcoded rows
 *   - bookmark icon toggles a saved/unsaved look (not persisted --
 *     there's no database yet to persist it TO)
 * ---------------------------------------------------------------------
 */

// TODO: SELECT * FROM announcements WHERE is_featured = 1 ORDER BY posted_date DESC
// (multiple featured slides -> a real carousel instead of the single
// featured card the dashboard shows). Reused the same placeholder SVGs
// as the dashboard's Recent Announcements strip for slides 2 and 3
// since we don't have real parish photos yet.
$featuredSlides = [
    [
        'title' => 'Feast Day Celebration this Sunday!',
        'body'  => "Join us this coming Sunday for the Feast of the Assumption of Mary. There will be a solemn Mass at 9:00 AM followed by a community gathering.",
        'image' => 'assets/images/announcements/feast-day.svg',
        'date'  => 'May 17, 2026',
    ],
    [
        'title' => 'Parish Fiesta Preparations Begin',
        'body'  => "Volunteers are needed to help prepare for this year's parish fiesta. Sign-up sheets are available at the parish office.",
        'image' => 'assets/images/announcements/cleaning-drive.svg',
        'date'  => 'May 20, 2026',
    ],
    [
        'title' => 'New Choir Members Wanted',
        'body'  => 'The parish choir is welcoming new members for the upcoming liturgical season. Rehearsals are every Saturday afternoon.',
        'image' => 'assets/images/announcements/adoration.svg',
        'date'  => 'May 22, 2026',
    ],
];

// TODO: SELECT * FROM announcements ORDER BY posted_date DESC
// `category` here isn't a real column on the announcements table in
// schema.sql yet -- that table only has is_featured right now. Adding
// a `category` VARCHAR/ENUM column is a small migration for whoever
// wires this page up to MySQL next.
$allAnnouncements = [
    ['title' => 'Adoration Every Friday', 'excerpt' => 'Eucharistic Adoration is held every Friday after the 6:00 PM Mass.', 'image' => 'assets/images/announcements/adoration.svg', 'category' => 'Mass & Liturgical', 'catClass' => 'cat-liturgical', 'date' => 'May 10, 2026'],
    ['title' => 'Mass Intentions Now Open', 'excerpt' => 'You can now submit your Mass intention requests for June.', 'image' => 'assets/images/announcements/mass-intentions.svg', 'category' => 'Reminders', 'catClass' => 'cat-reminders', 'date' => 'May 9, 2026'],
    ['title' => 'Church Cleaning Drive', 'excerpt' => "Let's keep our church clean and beautiful. See you there!", 'image' => 'assets/images/announcements/cleaning-drive.svg', 'category' => 'Events', 'catClass' => 'cat-events', 'date' => 'May 8, 2026'],
    ['title' => 'Marriage Preparation Seminar', 'excerpt' => 'All couples planning to get married this year are required to attend.', 'image' => 'assets/images/announcements/seminar.svg', 'category' => 'Notices', 'catClass' => 'cat-notices', 'date' => 'May 7, 2026'],
    ['title' => 'Confirmation Registration Ongoing', 'excerpt' => 'Registration for Confirmation this year is now open until May 31.', 'image' => 'assets/images/announcements/dove.svg', 'category' => 'Parish News', 'catClass' => 'cat-news', 'date' => 'May 5, 2026'],
    // revealed by "Load more" -- indices 5+ start hidden
    ['title' => 'Youth Fellowship Meetup', 'excerpt' => 'Join fellow young parishioners for fellowship, games, and reflection.', 'image' => 'assets/images/announcements/cleaning-drive.svg', 'category' => 'Events', 'catClass' => 'cat-events', 'date' => 'May 3, 2026'],
    ['title' => 'Parish Bulletin - June Edition', 'excerpt' => 'This month\'s bulletin is now available at the church entrance.', 'image' => 'assets/images/announcements/mass-intentions.svg', 'category' => 'Parish News', 'catClass' => 'cat-news', 'date' => 'May 1, 2026'],
];

// Tabs shown above the list. "All Announcements" always shows every
// row; the rest filter the list client-side by matching this label
// against each row's data-category attribute (see main.js).
$categoryTabs = ['All Announcements', 'Parish News', 'Events', 'Mass & Liturgical', 'Wedding Banns', 'Reminders', 'Notices'];

// TODO: SELECT * FROM <events/calendar table we don't have yet> WHERE
// event_date >= CURDATE() ORDER BY event_date LIMIT 4. This will
// likely end up being a UNION across wedding/baptism/funeral/etc.
// request tables (the ones with status = 'scheduled') once the Parish
// Calendar page's data model is figured out next session.
//
// 'dot' uses the same Mass/Sacrament/Event/Reminder/Other category
// system introduced on calendar.php (see its legend) -- kept in sync
// so the same event reads as the same color on both pages. This is
// literally the SAME array as calendar.php's $upcomingEvents; once
// there's a real query, both pages just call the same function
// instead of each keeping their own hardcoded copy.
$upcomingEvents = [
    ['month' => 'MAY', 'day' => '17', 'title' => 'Feast of the Assumption of Mary', 'time' => '9:00 AM', 'location' => 'Main Church', 'dot' => 'mass'],
    ['month' => 'MAY', 'day' => '24', 'title' => 'Youth Gathering',                 'time' => '2:00 PM', 'location' => 'Parish Hall', 'dot' => 'event'],
    ['month' => 'MAY', 'day' => '31', 'title' => 'First Friday Mass & Holy Hour',   'time' => '6:00 PM', 'location' => 'Main Church', 'dot' => 'mass'],
    ['month' => 'JUN', 'day' => '07', 'title' => 'Pentecost Sunday Celebration',    'time' => '9:00 AM', 'location' => 'Main Church', 'dot' => 'mass'],
];

// INFERRED from the image -- not a table in schema.sql. These read
// like manually curated admin call-outs rather than announcements, so
// they'd probably need their own small `notices` table later, distinct
// from `announcements` (shorter, no image, always shown until an admin
// dismisses them).
$importantNotices = [
    ['icon' => 'calendar', 'tint' => 'blue',  'title' => 'Office Schedule Update',           'body' => 'Parish office will be closed on May 17.'],
    ['icon' => 'bell',     'tint' => 'amber', 'title' => 'Marriage Preparation Requirements', 'body' => 'Please complete the seminar before proceeding.'],
    ['icon' => 'droplet',  'tint' => 'blue',  'title' => 'Baptism Requirements Reminder',      'body' => 'Prepare all required documents before appointment.'],
];

$pageTitle = 'Announcements';
$pageCss   = 'announcements.css';
$activeNav = 'announcements';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>
<main class="ps-main">

    <!-- ============================ PAGE HERO ============================ -->
    <section class="ann-hero">
        <div class="ann-hero-image">
            <img src="assets/images/hero-banner.svg" alt="Our Lady of the Gate interior">
        </div>
        <?php require __DIR__ . '/includes/topbar.php'; ?>
        <div class="ann-hero-text">
            <h1>Announcements</h1>
            <div class="ps-heading-ornament"><span></span><?php ps_icon('cross'); ?><span></span></div>
            <p>Stay updated with the latest news, notices, and activities from our parish.</p>
        </div>
    </section>

    <!-- ============================ TOOLBAR =============================== -->
    <div class="ann-toolbar">
        <div class="ps-tabs" data-filter-tabs>
            <?php foreach ($categoryTabs as $i => $tab): ?>
                <button type="button" class="ps-tab<?php echo $i === 0 ? ' active' : ''; ?>" data-filter-tab="<?php echo htmlspecialchars($tab); ?>">
                    <?php echo htmlspecialchars($tab); ?>
                </button>
            <?php endforeach; ?>
        </div>
        <div class="ann-toolbar-right">
            <label class="ps-search">
                <?php ps_icon('search'); ?>
                <input type="text" id="announcementSearch" name="announcementSearch" placeholder="Search announcements..." data-announcement-search>
            </label>
            <button type="button" class="ps-filter-btn">
                <?php ps_icon('filter'); ?> Filter <?php ps_icon('chevron-down', 'ps-chev-sm'); ?>
            </button>
        </div>
    </div>

    <section class="ann-grid">

        <div class="ann-main">

            <!-- ---- Featured announcement carousel ---- -->
            <div class="ps-card ann-featured">
                <div class="ps-card-header">
                    <span class="ps-card-title"><?php ps_icon('star'); ?> Featured Announcement</span>
                </div>

                <div class="ann-carousel" data-carousel>
                    <?php foreach ($featuredSlides as $i => $slide): ?>
                        <div class="ann-slide<?php echo $i === 0 ? ' is-active' : ''; ?>" data-slide>
                            <div class="ann-slide-image">
                                <img src="<?php echo htmlspecialchars($slide['image']); ?>" alt="">
                            </div>
                            <div class="ann-slide-body">
                                <span class="ann-featured-badge">FEATURED</span>
                                <h3><?php echo htmlspecialchars($slide['title']); ?></h3>
                                <p><?php echo htmlspecialchars($slide['body']); ?></p>
                                <div class="ann-slide-actions">
                                    <a href="announcements.php" class="ps-btn ps-btn-primary">Read full announcement <?php ps_icon('arrow-right'); ?></a>
                                    <span class="ann-slide-date"><?php ps_icon('calendar'); ?><?php echo htmlspecialchars($slide['date']); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="ann-carousel-nav">
                        <button type="button" class="ps-round-btn" data-carousel-prev aria-label="Previous announcement"><?php ps_icon('arrow-left'); ?></button>
                        <button type="button" class="ps-round-btn" data-carousel-next aria-label="Next announcement"><?php ps_icon('arrow-right'); ?></button>
                    </div>
                    <div class="ann-carousel-dots" data-carousel-dots>
                        <?php foreach ($featuredSlides as $i => $slide): ?>
                            <button type="button" class="ann-dot<?php echo $i === 0 ? ' is-active' : ''; ?>" data-carousel-dot="<?php echo (int) $i; ?>" aria-label="Go to slide <?php echo (int) $i + 1; ?>"></button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- ---- All announcements list ---- -->
            <div class="ps-card ann-list-card">
                <div class="ps-card-header">
                    <span class="ps-card-title">All Announcements</span>
                </div>

                <div class="ann-table">
                    <div class="ann-table-head">
                        <span>Announcement</span><span>Category</span><span>Date</span><span></span>
                    </div>

                    <?php foreach ($allAnnouncements as $i => $a): ?>
                        <div class="ann-row<?php echo $i >= 5 ? ' is-hidden' : ''; ?>" data-announcement-row data-category="<?php echo htmlspecialchars($a['category']); ?>">
                            <div class="ann-row-main">
                                <img src="<?php echo htmlspecialchars($a['image']); ?>" alt="">
                                <div class="ann-row-text">
                                    <strong><?php echo htmlspecialchars($a['title']); ?></strong>
                                    <small><?php echo htmlspecialchars($a['excerpt']); ?></small>
                                </div>
                            </div>
                            <span class="ps-tag <?php echo htmlspecialchars($a['catClass']); ?>"><?php echo htmlspecialchars($a['category']); ?></span>
                            <span class="ann-row-date"><?php echo htmlspecialchars($a['date']); ?></span>
                            <button type="button" class="ps-bookmark-btn" data-bookmark-btn aria-label="Save this announcement">
                                <?php ps_icon('bookmark'); ?>
                            </button>
                        </div>
                    <?php endforeach; ?>

                    <p class="ann-empty is-hidden" data-announcement-empty>No announcements in this category yet.</p>
                </div>

                <button type="button" class="ann-load-more" data-load-more>
                    Load more <?php ps_icon('chevron-down'); ?>
                </button>
            </div>

        </div>

        <!-- ---- Right column ---- -->
        <div class="ann-side">

            <div class="ps-card">
                <div class="ps-card-header">
                    <span class="ps-card-title"><?php ps_icon('calendar'); ?> Upcoming Parish Events</span>
                    <a href="calendar.php" class="ps-link-more">View full calendar <?php ps_icon('arrow-right'); ?></a>
                </div>
                <ul class="ann-event-list">
                    <?php foreach ($upcomingEvents as $ev): ?>
                        <li>
                            <span class="ann-event-date"><small><?php echo htmlspecialchars($ev['month']); ?></small><strong><?php echo htmlspecialchars($ev['day']); ?></strong></span>
                            <span class="ann-event-body">
                                <strong><?php echo htmlspecialchars($ev['title']); ?></strong>
                                <small><?php echo htmlspecialchars($ev['time']); ?> · <?php echo htmlspecialchars($ev['location']); ?></small>
                            </span>
                            <span class="ps-dot cat-<?php echo htmlspecialchars($ev['dot']); ?>"></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="ps-card">
                <div class="ps-card-header">
                    <span class="ps-card-title"><?php ps_icon('bell'); ?> Important Notices</span>
                </div>
                <ul class="ann-notice-list">
                    <?php foreach ($importantNotices as $n): ?>
                        <li>
                            <span class="ann-notice-icon tint-<?php echo htmlspecialchars($n['tint']); ?>"><?php ps_icon($n['icon']); ?></span>
                            <span class="ann-notice-body">
                                <strong><?php echo htmlspecialchars($n['title']); ?></strong>
                                <small><?php echo htmlspecialchars($n['body']); ?></small>
                            </span>
                            <?php ps_icon('chevron-right', 'ann-notice-chevron'); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="ps-card ann-contact-box">
                <div class="ann-contact-art"><?php ps_icon('heart'); ?></div>
                <h3>Have questions?</h3>
                <p>We're here to help. Contact the parish office during office hours.</p>
                <a href="contacts.php" class="ps-btn ps-btn-primary">Contact Us</a>
            </div>

        </div>

    </section>

</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
