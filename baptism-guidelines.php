<?php
/**
 * baptism-guidelines.php
 * ---------------------------------------------------------------------
 * Same "one page, anchored sections" architecture the group and I
 * agreed on for wedding-guidelines.php: Guidelines / Required Document
 * / Types & Fees / Frequently Asked Questions are sections A-D of ONE
 * page (#guidelines, #documents, #fees, #faq), not four separate
 * files. This reference image adds two things wedding-guidelines.php
 * didn't have, both built as REAL functionality, not decoration:
 *
 *   1. The section tab bar up top -- real anchor links, and
 *      initSectionTabScrollSpy() in main.js keeps whichever tab
 *      matches the section actually in view marked .active as you
 *      scroll, using IntersectionObserver. Clicking a tab still jumps
 *      there instantly too (plain anchor behavior, unaffected).
 *
 *   2. The FAQ list -- built as native <details>/<summary> elements
 *      (.ps-faq-item), so open/close, keyboard operation, and
 *      accessibility are handled entirely by the browser. No JS
 *      accordion logic exists or is needed. They default CLOSED
 *      (the reference image shows them all expanded, but that reads
 *      as a mockup showing full content for documentation purposes --
 *      an accordion whose point is hiding answers until asked for
 *      doesn't do much starting fully open). Flag if the group
 *      actually wants them to start expanded.
 *
 * ONE INCONSISTENCY WITH THE WEDDING PAGES, kept because THIS image
 * shows it this way: FAQ lives on THIS page (#faq) instead of a
 * separate baptism-faq.php the way wedding-faq.php was planned as its
 * own file. Updated baptism.php's quick-link accordingly (was pointed
 * at baptism-faq.php, now points to baptism-guidelines.php#faq).
 *
 * Reuses baptism.php's baptism-shell.svg for the Welcome banner --
 * same asset-reuse call wedding-guidelines.php made with
 * wedding-rings.svg, so a sacrament's two pages share one visual.
 * ---------------------------------------------------------------------
 */

$tabs = [
    ['icon' => 'book',     'label' => 'Guidelines',                'href' => '#guidelines'],
    ['icon' => 'document', 'label' => 'Required Document',         'href' => '#documents'],
    ['icon' => 'tag',      'label' => 'Types & Fees',               'href' => '#fees'],
    ['icon' => 'question', 'label' => 'Frequently Asked Questions', 'href' => '#faq'],
];

$guidelines = [
    'Baptism is the first sacrament and the beginning of a new life in Christ.',
    'Parents and godparents must be active Catholics and willing to help raise the child in the Catholic faith.',
    'Parents and godparents must attend the pre-baptism orientation/seminar scheduled by the parish.',
    'Please provide accurate and complete information to avoid delays in processing.',
    'On the day of baptism, arrive on time and observe proper decorum inside the church.',
];

$feeTypes = [
    ['icon' => 'calendar', 'title' => 'Regular Baptism', 'desc' => 'Every Saturday',                                    'fee' => '₱500.00'],
    ['icon' => 'star',     'title' => 'Special Baptism', 'desc' => 'Preferred date or outside the regular schedule.',   'fee' => '₱3,000.00'],
];

$faqs = [
    ['q' => 'When is the regular baptism schedule?', 'a' => 'Regular baptism is every Saturday.'],
    ['q' => 'What document is needed?',               'a' => 'Birth Certificate (Original or PSA copy) only.'],
    ['q' => 'How much is the fee?',                    'a' => '₱500 for Regular Baptism and ₱3,000 for Special Baptism.'],
    ['q' => 'What is a Special Baptism?',              'a' => 'It is for those who prefer a date outside the regular Saturday schedule. The date is subject to parish approval.'],
];

$pageTitle = 'Baptism Guidelines';
$pageCss   = 'baptism-guidelines.css';
$activeNav = 'baptism';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>
<main class="ps-main">

    <!-- ============================ PLAIN HEADER ========================== -->
    <section class="ps-plain-header">
        <?php require __DIR__ . '/includes/topbar.php'; ?>
        <h1>Baptism Guidelines</h1>
        <nav class="ps-breadcrumb">
            <a href="baptism.php">Baptism</a>
            <?php ps_icon('chevron-right'); ?>
            <span>Guidelines</span>
        </nav>
    </section>

    <!-- ============================ SECTION TABS ========================== -->
    <nav class="ps-card ps-anchor-tabs" aria-label="Baptism guideline sections" data-scroll-spy-tabs>
        <?php foreach ($tabs as $i => $tab): ?>
            <a href="<?php echo htmlspecialchars($tab['href']); ?>" class="ps-anchor-tab<?php echo $i === 0 ? ' active' : ''; ?>" data-scroll-spy-tab="<?php echo htmlspecialchars(ltrim($tab['href'], '#')); ?>">
                <?php ps_icon($tab['icon']); ?> <?php echo htmlspecialchars($tab['label']); ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <!-- ============================ WELCOME BANNER ======================== -->
    <div class="ps-card wed-banner">
        <div class="wed-banner-art"><img src="assets/images/baptism-shell.svg" alt=""></div>
        <div class="wed-banner-text">
            <h2>Welcome!</h2>
            <p>Baptism is the first sacrament and the beginning of a new life in Christ. Please read the following guidelines carefully to help you in your preparation for this important sacrament.</p>
        </div>
    </div>

    <!-- ============================ A: GUIDELINES ========================= -->
    <div class="ps-card wg-section-card" id="guidelines" data-scroll-spy-section="guidelines">
        <h3 class="ps-card-title">A. Baptism Guidelines</h3>
        <div class="ps-steps-list">
            <?php foreach ($guidelines as $i => $g): ?>
                <div class="ps-steps-list-item">
                    <span class="ps-steps-list-num"><?php echo (int) $i + 1; ?></span>
                    <p class="ps-steps-list-text"><?php echo htmlspecialchars($g); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ============================ B: REQUIRED DOCUMENT ================== -->
    <div class="ps-card wg-section-card" id="documents" data-scroll-spy-section="documents">
        <h3 class="ps-card-title">B. Required Document</h3>

        <div class="ps-doc-highlight">
            <span class="ps-doc-highlight-icon"><?php ps_icon('document'); ?></span>
            <span class="ps-doc-highlight-text">
                <strong>Birth Certificate</strong>
                <small>Original or PSA copy</small>
            </span>
        </div>
        <p class="bg-plain-note">This is the only document required.</p>

        <div class="ps-info-banner bg-doc-tip">
            <?php ps_icon('info'); ?>
            <span>Make sure the birth certificate is readable and complete before uploading.</span>
        </div>
    </div>

    <!-- ============================ C: TYPES & FEES ======================= -->
    <div class="ps-card wg-section-card" id="fees" data-scroll-spy-section="fees">
        <h3 class="ps-card-title">C. Baptism Types &amp; Fees</h3>

        <div class="bg-fee-grid">
            <?php foreach ($feeTypes as $f): ?>
                <div class="bg-fee-card">
                    <span class="bg-fee-card-icon"><?php ps_icon($f['icon']); ?></span>
                    <span class="bg-fee-card-text">
                        <strong><?php echo htmlspecialchars($f['title']); ?></strong>
                        <small><?php echo htmlspecialchars($f['desc']); ?></small>
                        <span class="bg-fee-card-amount">Fee: <?php echo htmlspecialchars($f['fee']); ?></span>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="ps-info-banner">
            <?php ps_icon('info'); ?>
            <span>Special Baptism schedule is subject to parish confirmation.</span>
        </div>
    </div>

    <!-- ============================ D: FAQ ================================ -->
    <div class="ps-card wg-section-card" id="faq" data-scroll-spy-section="faq">
        <h3 class="ps-card-title">D. Frequently Asked Questions</h3>

        <?php foreach ($faqs as $item): ?>
            <details class="ps-faq-item">
                <summary>
                    <strong><?php echo htmlspecialchars($item['q']); ?></strong>
                    <?php ps_icon('chevron-down', 'ps-faq-chevron'); ?>
                </summary>
                <p class="ps-faq-answer"><?php echo htmlspecialchars($item['a']); ?></p>
            </details>
        <?php endforeach; ?>
    </div>

    <!-- ============================ HELP FOOTER =========================== -->
    <div class="ps-card ps-help-footer">
        <div class="ps-help-footer-text">
            <span class="ps-help-footer-icon"><?php ps_icon('headset'); ?></span>
            <span>
                <strong>Need more help?</strong>
                <small>Please contact the Parish Office. We are happy to assist you.</small>
            </span>
        </div>
        <a href="contacts.php" class="ps-btn ps-btn-primary"><?php ps_icon('phone'); ?> Contact Parish Office</a>
    </div>

</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
