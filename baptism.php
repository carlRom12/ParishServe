<?php
/**
 * baptism.php
 * ---------------------------------------------------------------------
 * Built as a structural clone of wedding.php, per the group's explicit
 * instruction ("make it just like wedding.php for the sake of
 * uniformity") -- same hero, same intro banner, same 4 quick-link
 * cards, same 3-column overview row, same about+quote card. Content
 * below comes from the group's baptism.php reference image; anywhere
 * that image showed something wedding.php's layout didn't have (the
 * info banner under Process Overview, the fee schedule rows), it was
 * added as a small extension INSIDE the existing shared components
 * rather than a structural departure -- e.g. the fee rows still live
 * inside the same .wed-schedule-card wedding.php uses for its
 * (simpler) Wedding Schedule card.
 *
 * One deliberate deviation from the reference image: it showed a
 * breadcrumb ("Home > Baptism") above the heading. wedding.php's own
 * top-level sacrament page has no breadcrumb (only its sub-pages like
 * wedding-guidelines.php do) -- dropped it here too, since the ask was
 * uniformity with wedding.php specifically, not literal image
 * reproduction.
 *
 * Almost all CSS here comes from style.css's "Sacrament landing page
 * kit" (promoted from wedding.css this session for exactly this
 * reuse) -- baptism.css is only the fee-schedule rows, which are new.
 * ---------------------------------------------------------------------
 */

$quickLinks = [
    ['icon' => 'book',     'title' => 'Baptism Guidelines',         'desc' => 'Learn about the meaning of baptism and our parish practices.', 'href' => 'baptism-guidelines.php'],
    ['icon' => 'document', 'title' => 'Required Document',          'desc' => 'Birth Certificate only, original or PSA copy.',                 'href' => 'baptism-guidelines.php#documents'],
    ['icon' => 'tag',      'title' => 'Baptism Types & Fees',       'desc' => 'Regular every Saturday or choose a special schedule.',          'href' => 'baptism-guidelines.php#fees'],
    ['icon' => 'question', 'title' => 'Frequently Asked Questions', 'desc' => 'Find answers to common questions about baptism.',               'href' => 'baptism-guidelines.php#faq'],
];

$processSteps = [
    ['title' => 'Baptism Details', 'sub' => 'Provide information about the child and parent/guardian.'],
    ['title' => 'Requirement',     'sub' => 'Upload the birth certificate of the child.'],
    ['title' => 'Review & Submit', 'sub' => 'Review your request and submit.'],
];

$reminders = [
    'Bring the original or PSA copy of the birth certificate.',
    'Godparents must be Catholic (baptized and in good standing).',
    'Attend the pre-baptism orientation.',
    'Arrive on time on the day of baptism.',
    'You will be notified once your request is approved.',
];

// INFERRED from the reference image -- wedding.php's equivalent card
// (Wedding Schedule) is just contact text + a button; baptism's shows
// an actual small fee schedule too. New to this page, not part of the
// promoted shared kit.
$feeSchedule = [
    ['icon' => 'calendar', 'title' => 'Regular Baptism', 'sub' => 'Every Saturday',                                   'fee' => '₱500.00'],
    ['icon' => 'star',     'title' => 'Special Baptism', 'sub' => 'Subject to parish approval / special arrangement', 'fee' => '₱3,000.00'],
];

$quote = [
    'text'   => 'Let the little children come to me, and do not hinder them, for the kingdom of God belongs to such as these.',
    'author' => 'Mark 10:14',
];

$pageTitle = 'Baptism';
$pageCss   = 'baptism.css';
$activeNav = 'baptism';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>
<main class="ps-main">

    <!-- ============================ PAGE HERO ============================ -->
    <section class="ann-hero">
        <div class="ann-hero-image">
            <img src="assets/images/hero-banner.svg" alt="Our Lady of the Gate altar">
        </div>
        <?php require __DIR__ . '/includes/topbar.php'; ?>
        <div class="ann-hero-text">
            <h1>Baptism</h1>
            <p>Begin your child's journey of faith through the Sacrament of Baptism.</p>
        </div>
    </section>

    <!-- ============================ INTRO BANNER ========================== -->
    <div class="ps-card wed-banner">
        <div class="wed-banner-art"><img src="assets/images/baptism-shell.svg" alt=""></div>
        <div class="wed-banner-text">
            <h2>A Sacred Beginning</h2>
            <p>Baptism is the first sacrament and the beginning of a new life in Christ. We are honored to walk with you and your family on this important step of faith.</p>
        </div>
        <div class="bap-start">
            <a href="baptism-request.php" class="ps-btn ps-btn-primary wed-start-btn">
                <?php ps_icon('droplet'); ?> Start Baptism Request <?php ps_icon('arrow-right'); ?>
            </a>
            <small>Begin your request in just a few steps.</small>
        </div>
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
            <p>Your baptism request will go through three simple steps:</p>

            <div class="wed-steps">
                <?php foreach ($processSteps as $i => $step): ?>
                    <div class="wed-step<?php echo $i === 0 ? ' is-current' : ''; ?>">
                        <span class="wed-step-num"><?php echo (int) $i + 1; ?></span>
                        <strong><?php echo htmlspecialchars($step['title']); ?></strong>
                        <small><?php echo htmlspecialchars($step['sub']); ?></small>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="ps-info-banner bap-process-note">
                <?php ps_icon('info'); ?>
                <span>You will be notified by email or text once your baptism schedule is confirmed.</span>
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
            <h3 class="ps-card-title"><?php ps_icon('calendar'); ?> Baptism Schedule &amp; Fees</h3>

            <div class="bap-fee-list">
                <?php foreach ($feeSchedule as $f): ?>
                    <div class="bap-fee-row">
                        <span class="bap-fee-icon"><?php ps_icon($f['icon']); ?></span>
                        <span class="bap-fee-text">
                            <strong><?php echo htmlspecialchars($f['title']); ?></strong>
                            <small><?php echo htmlspecialchars($f['sub']); ?></small>
                        </span>
                        <span class="bap-fee-amount">Fee: <?php echo htmlspecialchars($f['fee']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="ps-info-banner bap-fee-note">
                <?php ps_icon('info'); ?>
                <span>Special Baptism schedule is subject to parish confirmation.</span>
            </div>

            <a href="contacts.php" class="ps-btn ps-btn-outline"><?php ps_icon('phone'); ?> Contact Parish Office</a>
        </div>

    </div>

    <!-- ============================ ABOUT ================================= -->
    <div class="ps-card wed-about-card">
        <div class="wed-about-text">
            <h3 class="ps-card-title"><?php ps_icon('church'); ?> About Baptism</h3>
            <p>Through Baptism, we are cleansed from sin and welcomed into the family of God, the Church. It is the foundation of our faith and the start of our journey as disciples of Jesus.</p>
        </div>
        <blockquote class="wed-quote">
            <p>&ldquo;<?php echo htmlspecialchars($quote['text']); ?>&rdquo;</p>
            <cite>— <?php echo htmlspecialchars($quote['author']); ?></cite>
        </blockquote>
    </div>

</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
