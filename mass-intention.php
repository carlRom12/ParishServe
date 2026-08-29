<?php
/**
 * mass-intention.php
 * ---------------------------------------------------------------------
 * Built as a structural clone of wedding.php/baptism.php, same as the
 * group's earlier "make it just like wedding.php for the sake of
 * uniformity" instruction for baptism.php -- same intro banner, same
 * 4 quick-link cards, same 3-column overview row, same about+quote
 * card. Content below comes from this round's reference image.
 *
 * ONE STRUCTURAL DIFFERENCE FROM WEDDING/BAPTISM: this page's header
 * is the plain .ps-plain-header (heading + subtitle paragraph, no
 * photo), not the .ann-hero photo-banner pattern wedding.php/
 * baptism.php use -- that's what the reference image showed, no photo
 * behind "Mass Intention" at all.
 *
 * NO PAGE-SPECIFIC CSS FILE: everything this page needed (.wed-banner,
 * .wed-quicklinks, .wed-overview-grid, .wed-steps, .wed-about-card,
 * .wed-quote, .bap-start, .bap-fee-*) already lives in style.css's
 * "Sacrament landing page kit" -- the .bap-start/.bap-fee-* pieces
 * were promoted there THIS round from baptism.css specifically because
 * this page needed them too (see style.css's comment on those rules).
 * The single Mass Offering row has no "sub" line under its title (the
 * reference image doesn't show one, unlike baptism's two-line fee
 * rows), so that <small> is just omitted rather than left empty.
 *
 * mass-intention-request.php and mass-intention-guidelines.php don't
 * exist yet -- linked to below the same way wedding.php originally
 * linked to wedding-request.php/wedding-guidelines.php before either
 * existed. Building those is the natural next step.
 * ---------------------------------------------------------------------
 */

$quickLinks = [
    ['icon' => 'book',     'title' => 'About Mass Intentions',      'desc' => 'Learn about the meaning and importance of Mass Intentions.', 'href' => 'mass-intention-guidelines.php#about'],
    ['icon' => 'document', 'title' => 'Guidelines',                 'desc' => 'Read our guidelines and parish practices.',                   'href' => 'mass-intention-guidelines.php'],
    ['icon' => 'tag',      'title' => 'Types of Intentions',        'desc' => 'Common types of intentions you can request.',                 'href' => 'mass-intention-guidelines.php#types'],
    ['icon' => 'question', 'title' => 'FAQs',                       'desc' => 'Find answers to common questions about Mass Intentions.',     'href' => 'mass-intention-guidelines.php#faq'],
];

$processSteps = [
    ['title' => 'Intent Details',   'sub' => 'Provide the details of your intention and your information.'],
    ['title' => 'Schedule',         'sub' => 'Choose your preferred date and time.'],
    ['title' => 'Review & Submit',  'sub' => 'Review your request and submit.'],
];

$reminders = [
    'Mass Intentions are offered in accordance with Catholic Church teachings.',
    'The stipend you offer helps support the needs of our priests and the parish.',
    'Please provide correct information to avoid delays.',
    'The offering for each Mass Intention is ₱100.00.',
    'You will be notified once your request is accepted.',
];

$feeSchedule = [
    ['icon' => 'chalice', 'title' => 'Mass Intention Offering', 'fee' => '₱100.00'],
];

$quote = [
    'text'   => 'The Mass is the greatest prayer we can offer. Through it, we unite our sacrifices with Christ for the good of all.',
    'author' => 'St. John Paul II',
];

$pageTitle = 'Mass Intention';
$activeNav = 'massintention';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>
<main class="ps-main">

    <!-- ============================ PLAIN HEADER ========================== -->
    <section class="ps-plain-header">
        <?php require __DIR__ . '/includes/topbar.php'; ?>
        <h1>Mass Intention</h1>
        <p>Offer a Mass for your loved ones and special intentions.</p>
    </section>

    <!-- ============================ INTRO BANNER ========================== -->
    <div class="ps-card wed-banner">
        <div class="wed-banner-art"><img src="assets/images/mass-chalice.svg" alt=""></div>
        <div class="wed-banner-text">
            <h2>A Prayer That Makes a Difference</h2>
            <p>The Holy Mass is the greatest prayer we can offer. Through a Mass intention, we unite our prayers with the sacrifice of the Eucharist for our loved ones and for our special intentions.</p>
        </div>
        <div class="bap-start">
            <a href="mass-intention-request.php" class="ps-btn ps-btn-primary wed-start-btn">
                <?php ps_icon('chalice'); ?> Request a Mass Intention <?php ps_icon('arrow-right'); ?>
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
            <h3 class="ps-card-title"><?php ps_icon('cross'); ?> How It Works</h3>
            <p>Your Mass Intention request will go through three simple steps:</p>

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
                <span>You will be notified once your request is confirmed.</span>
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
            <h3 class="ps-card-title"><?php ps_icon('chalice'); ?> Mass Offering &amp; Information</h3>

            <div class="bap-fee-list">
                <?php foreach ($feeSchedule as $f): ?>
                    <div class="bap-fee-row">
                        <span class="bap-fee-icon"><?php ps_icon($f['icon']); ?></span>
                        <span class="bap-fee-text">
                            <strong><?php echo htmlspecialchars($f['title']); ?></strong>
                            <?php if (!empty($f['sub'])): ?><small><?php echo htmlspecialchars($f['sub']); ?></small><?php endif; ?>
                        </span>
                        <span class="bap-fee-amount">Fee: <?php echo htmlspecialchars($f['fee']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="ps-info-banner bap-fee-note">
                <?php ps_icon('info'); ?>
                <span>The offering is the same for every Mass Intention.</span>
            </div>

            <a href="contacts.php" class="ps-btn ps-btn-outline"><?php ps_icon('phone'); ?> Contact Parish Office</a>
        </div>

    </div>

    <!-- ============================ ABOUT ================================= -->
    <div class="ps-card wed-about-card">
        <div class="wed-about-text">
            <h3 class="ps-card-title"><?php ps_icon('church'); ?> About Mass Intentions</h3>
            <p>A Mass Intention is a special way to remember your loved ones or to pray for a particular need. The priest offers the Mass for that intention, joining your prayer with the sacrifice of Christ on the altar.</p>
        </div>
        <blockquote class="wed-quote">
            <p>&ldquo;<?php echo htmlspecialchars($quote['text']); ?>&rdquo;</p>
            <cite>— <?php echo htmlspecialchars($quote['author']); ?></cite>
        </blockquote>
    </div>

</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
