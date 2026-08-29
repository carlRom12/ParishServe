<?php
/**
 * mass-intention-request.php
 * ---------------------------------------------------------------------
 * Step 1 of 3 ("Intent Details") of the Mass Intention request wizard,
 * linked from mass-intention.php's "Request a Mass Intention" button.
 * Structurally closer to wedding-request.php than baptism-request.php:
 * ONE continuous form card (not baptism's split into several numbered
 * .ps-card sections), matching this round's reference image.
 *
 * TWO NEW PIECES NOT USED BY WEDDING/BAPTISM YET, both added to the
 * shared wedding-request.css rather than a page-specific file since
 * neither is Mass-Intention-specific in shape:
 *   - .wr-offering-badge: the small "Mass Intention offering: ₱100.00"
 *     pill in the card's top-right corner, wrapped together with the
 *     heading/subtitle in a new .wr-form-header flex row.
 *   - .wr-intent-options / .wr-intent-card: a 5-way selectable card
 *     row for Intention Type. Visually different from baptism's
 *     .wr-type-card (icon+label stacked and CENTERED, no fee line,
 *     five columns instead of two) so it's a new set of classes, not a
 *     reuse of .wr-type-card itself -- but the highlight-on-select
 *     behavior is identical, so it reuses the same generic
 *     initRadioCardGroups() helper in main.js (extracted from
 *     baptism's initTypeCardToggle() this round specifically so both
 *     pages could share it instead of duplicating the same loop).
 *     UNLIKE Regular/Special Baptism, no card starts pre-selected here
 *     -- the reference image shows every radio empty by default, so
 *     each radio carries `required` instead of the group defaulting to
 *     one choice.
 *
 * Intention details / prayer request is REQUIRED here (unlike wedding/
 * baptism's optional office-notes textarea at the end of their forms)
 * and sits in the middle of the form, matching the reference image --
 * still uses the same live character-counter convention
 * (main.js's initCharacterCounters(), id+"Count" pairing).
 *
 * mass-intention-request-step2.php exists now, so this form genuinely
 * submits there (native HTML5 validation still runs first) -- same as
 * wedding-request.php once ITS Step 2 was built. The "not built yet"
 * notice moved to Step 2's own form instead, since Step 3 doesn't
 * exist yet.
 * ---------------------------------------------------------------------
 */

$steps = [
    ['title' => 'Intent Details',  'sub' => 'Provide your intention information'],
    ['title' => 'Schedule',        'sub' => 'Choose preferred Mass schedule'],
    ['title' => 'Review & Submit', 'sub' => 'Review and submit'],
];
$currentStep = 0; // "Intent Details"

$intentionTypes = [
    ['icon' => 'cross',      'label' => 'For the Deceased'],
    ['icon' => 'people',     'label' => 'For the Living'],
    ['icon' => 'heart',      'label' => 'Thanksgiving'],
    ['icon' => 'gift',       'label' => 'Milestones & Celebrations'],
    ['icon' => 'star',       'label' => 'Special Intention'],
];

$pageTitle = 'Mass Intention Request';
$pageCss   = 'wedding-request.css';
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

    <!-- ============================ STEP 1 FORM =========================== -->
    <!-- Step 2 is a real page now, so this form genuinely submits (native
         HTML5 validation via required/pattern/etc. still runs first) --
         same pattern wedding-request.php followed once ITS Step 2 existed.
         The intercept-and-show-a-notice behavior moved to Step 2's own
         form instead, since Step 3 doesn't exist yet. -->
    <form class="ps-card wr-form" action="mass-intention-request-step2.php" method="post">

        <div class="wr-form-header">
            <div>
                <h2>Step 1 of 3: Intent Details</h2>
                <p class="wr-form-sub">Please provide the details of the Mass Intention you would like to request.</p>
            </div>
            <span class="wr-offering-badge"><?php ps_icon('chalice'); ?> Mass Intention offering: <strong>&#8369;100.00</strong></span>
        </div>

        <div class="wr-field-group">
            <span class="ps-field-label">Intention Type</span>
            <div class="wr-intent-options" data-radio-cards>
                <?php foreach ($intentionTypes as $t): ?>
                    <label class="wr-intent-card" data-radio-card>
                        <input type="radio" name="intentionType" value="<?php echo htmlspecialchars($t['label']); ?>" required>
                        <span class="wr-intent-card-icon"><?php ps_icon($t['icon']); ?></span>
                        <strong><?php echo htmlspecialchars($t['label']); ?></strong>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="ps-form-row-2">
            <div class="ps-field">
                <label for="intentionSubject">Name of person / family / intention subject <span class="wr-required">*</span></label>
                <input type="text" id="intentionSubject" name="intentionSubject" placeholder="Enter name or intention subject" required>
            </div>
            <div class="ps-field">
                <label for="occasion">Occasion or purpose <span class="wr-optional">(optional)</span></label>
                <input type="text" id="occasion" name="occasion" placeholder="e.g., Birthday, Anniversary, Get well soon">
            </div>
        </div>

        <div class="ps-field wr-notes-field">
            <label for="intentionDetails">Intention details / prayer request <span class="wr-required">*</span></label>
            <textarea id="intentionDetails" name="intentionDetails" rows="3" maxlength="500" placeholder="Please share the intention or prayer request you would like our parish to pray for." required></textarea>
            <small class="ps-form-hint" id="intentionDetailsCount">0 / 500</small>
        </div>

        <div class="ps-form-row-3">
            <div class="ps-field">
                <label for="requesterName">Requester's full name <span class="wr-required">*</span></label>
                <input type="text" id="requesterName" name="requesterName" placeholder="Enter your full name" required>
            </div>
            <div class="ps-field">
                <label for="mobileNumber">Mobile number <span class="wr-required">*</span></label>
                <input type="tel" id="mobileNumber" name="mobileNumber" placeholder="09XXXXXXXXX"
                       pattern="^09[0-9]{9}$" maxlength="11" inputmode="numeric"
                       title="Format: 09XXXXXXXXX (11 digits)" required>
                <small class="ps-form-hint">Format: 09XXXXXXXXX (11 digits)</small>
            </div>
            <div class="ps-field">
                <label for="emailAddress">Email address <span class="wr-required">*</span></label>
                <input type="email" id="emailAddress" name="emailAddress" placeholder="youremail@example.com" required>
                <small class="ps-form-hint">We'll send confirmation and updates here.</small>
            </div>
        </div>

        <div class="ps-info-banner">
            <?php ps_icon('info'); ?>
            <span>Please provide complete and respectful details to help the parish prepare your request.</span>
        </div>

        <div class="wr-actions">
            <a href="mass-intention.php" class="ps-btn wr-cancel">Cancel</a>
            <button type="submit" class="ps-btn ps-btn-primary wr-submit">Save and Continue <?php ps_icon('arrow-right'); ?></button>
        </div>

    </form>

</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
