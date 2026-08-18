<?php
/**
 * wedding.php
 * ---------------------------------------------------------------------
 * The Wedding sacrament landing page -- an informational/orientation
 * page (guidelines, process overview, reminders) rather than a data
 * page like dashboard.php. Almost everything here is static parish
 * copy, not per-user records, so there isn't really a "TODO: replace
 * with a query" for most of it -- it's the kind of content that would
 * eventually live in a small admin-editable CMS table (so staff can
 * update the wording without a developer), not something tied to a
 * logged-in user's data. Flagged where relevant below.
 *
 * FIVE CLICKABLE THINGS ON THIS PAGE THAT AREN'T BUILT YET:
 *   Start Wedding Request, Wedding Guidelines, Documents Needed,
 *   Steps to Be Taken, Frequently Asked Questions.
 * Per the group's instruction, THIS session only builds the page as
 * shown in the reference image -- those five all link to pages that
 * don't exist yet (same "links to future pages" pattern as the
 * sidebar), to be built one at a time in later sessions.
 * ---------------------------------------------------------------------
 */

// Static content -- see file header. Kept as PHP arrays (not just
// inline HTML) so this becomes "replace the array" instead of
// "rewrite the markup" whenever this content moves into an admin CMS.
// Documents Needed and Steps to Be Taken both jump to sections WITHIN
// wedding-guidelines.php (#documents / #steps) rather than their own
// pages -- that page already covers both, discussed with the group
// rather than duplicating the same requirements content in 3 places.
$quickLinks = [
    ['icon' => 'book',       'title' => 'Wedding Guidelines',        'desc' => 'Read the complete guidelines and requirements.', 'href' => 'wedding-guidelines.php'],
    ['icon' => 'document',   'title' => 'Documents Needed',          'desc' => 'See the list of required documents.',            'href' => 'wedding-guidelines.php#documents'],
    ['icon' => 'footprints', 'title' => 'Steps to Be Taken',         'desc' => 'Learn the process step by step.',                 'href' => 'wedding-guidelines.php#steps'],
    ['icon' => 'question',   'title' => 'Frequently Asked Questions','desc' => 'Get answers to common questions.',               'href' => 'wedding-faq.php'],
];

$processSteps = [
    ['title' => 'The Couple',   'sub' => 'Tell us about you'],
    ['title' => 'Requirements', 'sub' => 'Submit documents'],
    ['title' => 'Review & Send','sub' => 'Review and submit'],
];

$reminders = [
    'Apply at least 1 month before the scheduled wedding.',
    'Attend the Pre-Marriage Seminar.',
    'Submit complete and valid documents.',
    'Arrive on time on the wedding day.',
];

$pageTitle = 'Wedding';
$pageCss   = 'wedding.css';
$activeNav = 'wedding';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>
<main class="ps-main">

    <!-- ============================ PAGE HERO ============================ -->
    <!-- same .ann-hero shell as Announcements/Parish Calendar -- reused
         as-is rather than a bespoke hero, so every page's banner behaves
         identically (this one just skips the gold heading-ornament,
         matching the reference image) -->
    <section class="ann-hero">
        <div class="ann-hero-image">
            <img src="assets/images/hero-banner.svg" alt="Our Lady of the Gate altar">
        </div>
        <?php require __DIR__ . '/includes/topbar.php'; ?>
        <div class="ann-hero-text">
            <h1>Wedding</h1>
            <p>Start your journey to a blessed marriage with Our Lady of the Gate Parish.</p>
        </div>
    </section>

    <!-- ============================ INTRO BANNER ========================== -->
    <div class="ps-card wed-banner">
        <div class="wed-banner-art"><img src="assets/images/wedding-rings.svg" alt=""></div>
        <div class="wed-banner-text">
            <h2>A Sacred Covenant of Love</h2>
            <p>Marriage is a sacred covenant, and we are here to guide you as you prepare for this important step in your life. Follow the steps, submit the required documents, and we'll assist you throughout the process.</p>
        </div>
        <a href="wedding-request.php" class="ps-btn ps-btn-primary wed-start-btn">
            <?php ps_icon('ring'); ?> Start Wedding Request <?php ps_icon('arrow-right'); ?>
        </a>
    </div>

    <!-- ============================ QUICK LINKS =========================== -->
    <div class="wed-quicklinks">
        <?php foreach ($quickLinks as $q): ?>
            <a href="<?php echo htmlspecialchars($q['href']); ?>" class="wed-quicklink-card">
                <span class="wed-quicklink-icon"><?php ps_icon($q['icon']); ?></span>
                <span class="wed-quicklink-text">
                    <strong><?php echo htmlspecialchars($q['title']); ?></strong>
                    <small><?php echo htmlspecialchars($q['desc']); ?></small>
                </span>
                <?php ps_icon('chevron-right', 'wed-quicklink-chevron'); ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- ============================ OVERVIEW ROW ========================== -->
    <div class="wed-overview-grid">

        <div class="ps-card wed-process-card">
            <h3 class="ps-card-title"><?php ps_icon('cross'); ?> Process Overview</h3>
            <p>Your wedding request will go through three simple steps:</p>

            <div class="wed-steps">
                <?php foreach ($processSteps as $i => $step): ?>
                    <div class="wed-step<?php echo $i === 0 ? ' is-current' : ''; ?>">
                        <span class="wed-step-num"><?php echo (int) $i + 1; ?></span>
                        <strong><?php echo htmlspecialchars($step['title']); ?></strong>
                        <small><?php echo htmlspecialchars($step['sub']); ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="ps-card wed-reminders-card">
            <h3 class="ps-card-title"><?php ps_icon('bell'); ?> Reminders</h3>
            <ul class="wed-reminder-list">
                <?php foreach ($reminders as $r): ?>
                    <li><?php ps_icon('check'); ?><?php echo htmlspecialchars($r); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="ps-card wed-schedule-card">
            <h3 class="ps-card-title"><?php ps_icon('calendar'); ?> Wedding Schedule</h3>
            <p>To inquire about available dates for wedding reservations, please visit or contact the parish office during office hours.</p>
            <a href="contacts.php" class="ps-btn ps-btn-outline"><?php ps_icon('phone'); ?> Contact Parish Office</a>
        </div>

    </div>

    <!-- ============================ ABOUT ================================= -->
    <div class="ps-card wed-about-card">
        <div class="wed-about-text">
            <h3 class="ps-card-title"><?php ps_icon('church'); ?> About Church Wedding</h3>
            <p>A church wedding is more than a ceremony — it is the beginning of a lifelong commitment before God. The parish accompanies you in this journey through prayer, formation, and a meaningful celebration.</p>
        </div>
        <blockquote class="wed-quote">
            <p>&ldquo;Therefore what God has joined together, let no one separate.&rdquo;</p>
            <cite>— Mark 10:9</cite>
        </blockquote>
    </div>

</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
