<?php
$steps = [
    'Couples have to present themselves personally for interview by a priest, at least one month before the scheduled wedding. Earlier than one month is preferred.',
    "Attend the Pre-Marriage seminar in the parish or in a parish convenient for them. If they decide to attend the pre-marriage seminar in another parish they will have to present a Certificate of Attendance duly signed by a priest from that parish.",
    'Two days before the wedding or on the eve of the wedding couples should go to confession. The parish also recommends that all parties to the wedding go to confession (sponsors, parents, et al.).',
    'On the day of the wedding, arrive on time so that you do not disrupt the succession of activities in the church.',
];

$documents = [
    ['icon' => 'document', 'text' => 'Certificate of No Marriage (CENOMAR) from the Statistics Office.'],
    ['icon' => 'id-card',  'text' => 'Permit for non-parishioners and outsider (BRIDE only).'],
    ['icon' => 'shield',   'text' => 'Baptismal & Confirmation Certificates of the couples.'],
    ['icon' => 'megaphone','text' => 'Publication of Banns in the Parish and in the parishes where the parties reside.'],
    ['icon' => 'document', 'text' => 'Marriage License'],
    ['icon' => 'photo',    'text' => '2 x 2 Picture'],
    ['icon' => 'user',     'text' => 'Authorization number of the Guest Priest (If a guest priest is engaged)'],
    ['icon' => 'shield',   'text' => 'In case one or both of the couples is/are military person(s), a certification from his/her commanding officer to the effect that he/she is free to marry.'],
];

$feeTable = [
    ['label' => 'Ordinary Wedding (Thursdays)', 'amount' => '₱5,000.00'],
    ['label' => 'Special weddings for Parishioners', 'amount' => '₱12,000.00'],
    ['label' => 'Special weddings for non-parishioners belonging to the diocese', 'amount' => '₱15,000.00'],
    ['label' => 'Special weddings for Outsider', 'amount' => '₱20,000.00'],
    ['label' => 'Evening Wedding (6:00PM) – (Mon, Tue, Thu)', 'amount' => '₱20,000.00'],
];

$feeNotes = [
    '(Fee includes the wedding fee, Mass stipend for the priest and the choir).',
    "There is an in-house decorator which couples may avail depending on their taste. (See separate brochure and contact the Mother Butler's Guild at their office).",
    'Decorators outside of the parish contracted by the couple should be coordinated with the parish. Oftentimes outside decorators leave a lot of trash in the church so that effective immediately outside decorators are required to pay Two Thousand Five Hundred (₱2,500.00) Pesos for red carpet and janitorial fee.',
    'The couple is responsible for telling their decorators to remove immediately their decorations to give enough time for the succeeding wedding to set up.',
    'Date reservations are allowed. Couples will have to pay a 2,000 reservation fee which is non-refundable in case the wedding is cancelled or moved to another date.',
    'The parish requires that full payment be made two weeks before the scheduled wedding date, otherwise, the parish reserves the right to cancel the wedding and award the slot to another wedding.',
    'In excess of six pairs of sponsors, the couple will have to pay donation for every extra pair of sponsors.',
    'When couples decide to cancel their scheduled wedding for whatever reason payments will not be refunded because their slot has already been reserved for that wedding.',
];

$sponsorNotes = [
    'Couples may have as many sponsors as they want, the parish recommends only a maximum of six pairs due to space constraints. However, in excess of six pair, couples will have to pay the fee as stated above.',
    'There should be only three pairs of secondary sponsors i.e., candle, veil, cord.',
    'Children acting as ring bearers, flower girls and others should be old enough to avoid misbehavior from very young kids and preserve the solemnity of the wedding. The parish requires children to be seven years old and above.',
];

$quote = [
    'text'   => 'Marriage is the foundation of the family and the family is the foundation of society.',
    'author' => 'St. John Paul II',
];

$pageTitle = 'Wedding Guidelines';
$pageCss   = 'wedding-guidelines.css';
$activeNav = 'wedding';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>
<main class="ps-main">

    <!-- ============================ PLAIN HEADER ========================== -->
    <section class="ps-plain-header">
        <?php require __DIR__ . '/includes/topbar.php'; ?>
        <h1>Wedding Guidelines</h1>
        <nav class="ps-breadcrumb">
            <a href="wedding.php">Wedding</a>
            <?php ps_icon('chevron-right'); ?>
            <span>Guidelines</span>
        </nav>
    </section>

    <!-- ============================ WELCOME BANNER ======================== -->
    <div class="ps-card wed-banner">
        <div class="wed-banner-art"><img src="assets/images/wedding-rings.svg" alt=""></div>
        <div class="wed-banner-text">
            <h2>Welcome!</h2>
            <p>We are honored that you are preparing for the Sacrament of Marriage. Please read the following guidelines carefully to help you in your preparation for this important step in your life.</p>
        </div>
    </div>

    <!-- ============================ A + B ROW ============================= -->
    <div class="wg-row-2">

        <div class="ps-card" id="steps">
            <h3 class="ps-card-title">A. Steps to be Taken</h3>
            <div class="ps-steps-list">
                <?php foreach ($steps as $i => $step): ?>
                    <div class="ps-steps-list-item">
                        <span class="ps-steps-list-num"><?php echo (int) $i + 1; ?></span>
                        <p class="ps-steps-list-text"><?php echo htmlspecialchars($step); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="ps-card" id="documents">
            <h3 class="ps-card-title">B. Documents Needed</h3>
            <div class="ps-icon-list">
                <?php foreach ($documents as $i => $doc): ?>
                    <div class="ps-icon-list-item">
                        <span class="ps-icon-list-icon"><?php ps_icon($doc['icon']); ?></span>
                        <p class="ps-icon-list-text"><span class="ps-icon-list-num"><?php echo (int) $i + 1; ?>.</span><?php echo htmlspecialchars($doc['text']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

    <!-- ============================ C: FEES =============================== -->
    <div class="ps-card wg-section-card">
        <h3 class="ps-card-title">C. Fees / Decorations / Cancellations</h3>
        <div class="wg-fees-grid">

            <div class="ps-fee-table">
                <div class="ps-fee-table-head">Wedding Fees / Decorations</div>
                <?php foreach ($feeTable as $i => $fee): ?>
                    <div class="ps-fee-row">
                        <span class="ps-fee-num"><?php echo (int) $i + 1; ?></span>
                        <span class="ps-fee-label"><?php echo htmlspecialchars($fee['label']); ?></span>
                        <span class="ps-fee-amount"><?php echo htmlspecialchars($fee['amount']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="ps-note-list">
                <?php foreach ($feeNotes as $i => $note): ?>
                    <div class="ps-note-list-item">
                        <span class="ps-note-list-num"><?php echo (int) $i + 1; ?>.</span>
                        <span><?php echo htmlspecialchars($note); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>
    </div>

    <!-- ============================ D: SPONSORS =========================== -->
    <div class="ps-card wg-section-card">
        <h3 class="ps-card-title">D. Primary / Secondary Sponsors, Ring Bearers, Flower Girls, Guest Priests</h3>
        <div class="wg-sponsors-grid">

            <div class="ps-note-list">
                <?php foreach ($sponsorNotes as $i => $note): ?>
                    <div class="ps-note-list-item">
                        <span class="ps-note-list-num"><?php echo (int) $i + 1; ?>.</span>
                        <span><?php echo htmlspecialchars($note); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="ps-quote-card">
                <span class="ps-quote-mark">&ldquo;</span>
                <p><?php echo htmlspecialchars($quote['text']); ?></p>
                <cite>— <?php echo htmlspecialchars($quote['author']); ?></cite>
                <div class="ps-quote-art"><?php ps_icon('church'); ?></div>
            </div>

        </div>
    </div>

    <!-- ============================ HELP FOOTER =========================== -->
    <div class="ps-card ps-help-footer">
        <div class="ps-help-footer-text">
            <span class="ps-help-footer-icon"><?php ps_icon('headset'); ?></span>
            <span>
                <strong>Need help?</strong>
                <small>Contact our parish office during office hours.</small>
            </span>
        </div>
        <a href="contacts.php" class="ps-btn ps-btn-outline"><?php ps_icon('phone'); ?> Contact Parish Office</a>
    </div>

</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
