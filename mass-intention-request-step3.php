<?php
/**
 * mass-intention-request-step3.php
 * ---------------------------------------------------------------------
 * Step 3 of 3 ("Review & Submit"), the final screen of the Mass
 * Intention request wizard.
 *
 * REAL REVIEW DATA, NOT HARDCODED SAMPLE VALUES -- explicit instruction
 * this round was to "connect it with our system" instead of repeating
 * wedding-request-step3.php's hardcoded-sample-data treatment (that
 * page's own header comment explains why it used fake values: no
 * session/database existed anywhere in the project at the time). This
 * page and Step 2 now start a PHP session and merge each step's $_POST
 * into $_SESSION['miDraft'] as the user moves through the wizard, so
 * everything shown below is genuinely what the user typed on Steps 1
 * and 2 -- the exact "session-backed draft row" wedding-request-
 * step3.php's own TODO already predicted. Still no MySQL INSERT
 * anywhere (see the bottom of this file), so nothing survives a
 * closed browser/session -- that part hasn't changed, only the review
 * page itself is now real instead of mocked.
 *
 * Visiting this page directly (skipping Steps 1/2, or after the
 * session/draft has expired) falls back to "Not provided" per field
 * rather than crashing on a missing array key or showing stale fake
 * data.
 *
 * NO RIGHT SIDEBAR: unlike wedding-request-step3.php's two-column
 * layout (review cards + a "What happens next?" card), this round's
 * reference image is a single full-width column, so this page skips
 * .ann-grid/.ann-main/.ann-side entirely.
 *
 * "3. Preferred Schedule" sits below the 2-column .wr3-review-grid as
 * its own full-width card (.wr3-review-card-full) rather than inside
 * that grid, matching the reference image -- CSS grid would otherwise
 * only give it half width as a third grid item.
 *
 * SUBMIT: no mass_intentions INSERT exists, so "Submit Mass Intention
 * Request" still shows the same honest "not built yet" notice pattern
 * as every other wizard's final step -- the REVIEW is real now, the
 * SAVE still isn't, and this page doesn't blur that distinction.
 * ---------------------------------------------------------------------
 */

session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['miDraft'] = array_merge($_SESSION['miDraft'] ?? [], $_POST);
}
$draft = $_SESSION['miDraft'] ?? [];

function mi_display($value, $fallback = 'Not provided') {
    $value = is_string($value) ? trim($value) : '';
    return $value !== '' ? $value : $fallback;
}

$massTypeLabels = [
    'regular' => 'Regular Parish Mass',
    'special' => 'Special / Subject to Parish Confirmation',
];
$massTypeDisplay = $massTypeLabels[$draft['massType'] ?? ''] ?? 'Not provided';

$dateDisplay = 'Not provided';
if (!empty($draft['preferredDate'])) {
    $parsedDate = DateTime::createFromFormat('m/d/Y', $draft['preferredDate']);
    // falls back to the raw stored string if it somehow doesn't parse
    // (e.g. a hand-crafted request bypassing the datepicker) rather
    // than silently showing "Not provided" for a value that DID exist
    $dateDisplay = $parsedDate ? $parsedDate->format('l, F j, Y') : $draft['preferredDate'];
}

$steps = [
    ['title' => 'Intent Details',  'sub' => 'Provide your intention information'],
    ['title' => 'Schedule',        'sub' => 'Choose preferred Mass schedule'],
    ['title' => 'Review & Submit', 'sub' => 'Review and submit'],
];
$currentStep = 2; // "Review & Submit"

$pageTitle = 'Mass Intention Request';
$pageCss   = ['wedding-request.css', 'wedding-request-step3.css'];
$activeNav = 'massintention';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>
<main class="ps-main">

    <!-- ============================ PLAIN HEADER ========================== -->
    <section class="ps-plain-header">
        <?php require __DIR__ . '/includes/topbar.php'; ?>
        <h1>Mass Intention Request</h1>
        <nav class="ps-breadcrumb">
            <a href="mass-intention.php">Mass Intention</a>
            <?php ps_icon('chevron-right'); ?>
            <span>Start Request</span>
        </nav>
    </section>

    <!-- ============================ STEP BAR ============================== -->
    <?php require __DIR__ . '/includes/step-bar.php'; ?>

    <form class="wr-form" action="mass-intention-request-confirmation.php" method="post" data-wizard-step-form novalidate>

        <div class="wr-form-header">
            <div>
                <h2>Step 3 of 3: Review &amp; Submit</h2>
                <p class="wr-form-sub">Please review your Mass Intention details before submitting your request.</p>
            </div>
            <span class="wr-offering-badge"><?php ps_icon('chalice'); ?> Mass Intention offering: <strong>&#8369;100.00</strong></span>
        </div>

        <div class="wr3-review-grid">

            <!-- ---- 1. Intent Details ---- -->
            <div class="ps-card wr3-review-card">
                <div class="wr3-review-card-header">
                    <span class="wr3-review-card-icon"><?php ps_icon('chalice'); ?></span>
                    <h3>1. Intent Details</h3>
                </div>
                <div class="wr3-review-rows">
                    <div class="wr3-review-row"><span>Intention Type</span><strong><?php echo htmlspecialchars(mi_display($draft['intentionType'] ?? '')); ?></strong></div>
                    <div class="wr3-review-row"><span>Name of person / family / intention subject</span><strong><?php echo htmlspecialchars(mi_display($draft['intentionSubject'] ?? '')); ?></strong></div>
                    <div class="wr3-review-row"><span>Occasion or purpose</span><strong><?php echo htmlspecialchars(mi_display($draft['occasion'] ?? '')); ?></strong></div>
                    <div class="wr3-review-row wr3-review-row-wrap"><span>Intention details / prayer request</span><strong><?php echo htmlspecialchars(mi_display($draft['intentionDetails'] ?? '')); ?></strong></div>
                </div>
            </div>

            <!-- ---- 2. Requester Information ---- -->
            <div class="ps-card wr3-review-card">
                <div class="wr3-review-card-header">
                    <span class="wr3-review-card-icon"><?php ps_icon('user'); ?></span>
                    <h3>2. Requester Information</h3>
                </div>
                <div class="wr3-review-rows">
                    <div class="wr3-review-row"><span>Requester's full name</span><strong><?php echo htmlspecialchars(mi_display($draft['requesterName'] ?? '')); ?></strong></div>
                    <div class="wr3-review-row"><span>Mobile number</span><strong><?php echo htmlspecialchars(mi_display($draft['mobileNumber'] ?? '')); ?></strong></div>
                    <div class="wr3-review-row"><span>Email address</span><strong><?php echo htmlspecialchars(mi_display($draft['emailAddress'] ?? '')); ?></strong></div>
                </div>
            </div>

        </div>

        <!-- ---- 3. Preferred Schedule (full width) ---- -->
        <div class="ps-card wr3-review-card wr3-review-card-full">
            <div class="wr3-review-card-header">
                <span class="wr3-review-card-icon"><?php ps_icon('calendar'); ?></span>
                <h3>3. Preferred Schedule</h3>
            </div>
            <div class="wr3-review-rows">
                <div class="wr3-review-row"><span>Preferred Mass Date</span><strong><?php echo htmlspecialchars($dateDisplay); ?></strong></div>
                <div class="wr3-review-row"><span>Preferred Mass Time</span><strong><?php echo htmlspecialchars(mi_display($draft['preferredTime'] ?? '')); ?></strong></div>
                <div class="wr3-review-row"><span>Preferred Mass Type</span><strong><?php echo htmlspecialchars($massTypeDisplay); ?></strong></div>
                <div class="wr3-review-row wr3-review-row-wrap"><span>Additional scheduling notes</span><strong><?php echo htmlspecialchars(mi_display($draft['schedulingNotes'] ?? '')); ?></strong></div>
            </div>
        </div>

        <div class="ps-info-banner is-tip">
            <?php ps_icon('info'); ?>
            <span>Your requested Mass schedule is still subject to parish review and availability. You will receive a confirmation once your request has been approved.</span>
        </div>

        <!-- ---- Confirm bar ---- -->
        <div class="ps-card wr3-confirm-bar">
            <div class="wr3-confirm-text">
                <span class="wr3-confirm-icon"><?php ps_icon('check'); ?></span>
                <span>
                    <strong>Please confirm</strong>
                    <small>I confirm that the information provided is correct and respectful.</small>
                    <small>Submitting this request is a heartfelt offering to our parish community.</small>
                </span>
            </div>
            <label class="ps-toggle">
                <input type="checkbox" id="confirmRespectful" name="confirmRespectful" data-confirm-toggle required>
                <span class="ps-toggle-track"><span class="ps-toggle-thumb"></span></span>
                <span class="ps-toggle-label">I confirm</span>
            </label>
        </div>

        <div class="wr-actions">
            <a href="mass-intention-request-step2.php" class="ps-btn wr-cancel"><?php ps_icon('arrow-left'); ?> Back</a>
            <button type="submit" class="ps-btn ps-btn-primary wr-submit" data-confirm-submit disabled>
                <?php ps_icon('send'); ?> Submit Mass Intention Request
            </button>
        </div>

        <p class="wr-next-step-notice" data-wizard-notice hidden>
            <?php ps_icon('info'); ?>
            There's no backend yet to actually save this request -- the review above is genuinely what you typed on Steps 1 &amp; 2, but there's no <code>mass_intentions</code> table to insert it into. Once the database is wired up, submitting here will create a real request and generate a reference number.
        </p>

    </form>

</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
