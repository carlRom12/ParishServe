<?php
/**
 * mass-intention-request-step2.php
 * ---------------------------------------------------------------------
 * Step 2 of 3 ("Schedule") of the Mass Intention request wizard.
 *
 * PLACEHOLDER SCHEDULE DATA: the group doesn't have the parish's real
 * Mass calendar/time slots yet, so $massTimes below is a realistic
 * MOCK of a typical parish weekday/weekend Mass schedule (early
 * morning, a mid-morning "Family Mass", noon, and evening/anticipated
 * slots) -- NOT sourced from any real schedule. Swap this array for a
 * real query once the parish's actual Mass schedule exists somewhere.
 *
 * "Preferred Mass Date" REBUILT this round: started as a plain native
 * <input type="date">, but that relies on the browser's own calendar
 * popup, which wasn't reliably opening for the group. Switched to the
 * SAME custom .ps-datepicker component baptism-request.php uses
 * (main.js's initDatepickers()) with no data-weekday attribute set --
 * that attribute is what makes baptism's calendar Saturday-only, and
 * simply omitting it here means only the "must be strictly future"
 * rule applies, computed fresh from the real current date every time,
 * never hard-coded. Past dates render disabled/greyed in the popup
 * itself; per this round's instruction, there's no separate "please
 * choose a future date" warning text anymore -- the disabled calendar
 * already communicates that, matching baptism's Regular Baptism date
 * field's own quiet-by-default design. The input also carries a real
 * HTML `pattern` (mm/dd/yyyy) as an extra format guard, on top of the
 * datepicker's own stricter JS parsing/validation.
 *
 * "Preferred Mass Type" reuses the SAME .wr-intent-card component
 * Step 1's Intention Type row uses, just in the 2-column
 * .wr-mass-type-options variant (added this round alongside a new
 * .wr-intent-card-sub class for the second line of text under each
 * label, since Step 1's cards never needed one). Regular Parish Mass
 * starts pre-selected here, unlike Step 1's Intention Type cards which
 * intentionally start with nothing chosen.
 *
 * Step 3 (Review & Submit) exists now and genuinely needs to show what
 * the user typed on Steps 1 & 2 -- so this page starts a session and
 * merges whatever it's just been POSTed (Step 1's fields, the first
 * time through) into a session-backed draft, same mechanism wedding-
 * request-step3.php's own header comment already anticipated
 * ("session-backed draft row, most likely") back when no page in this
 * project actually used a session yet. This form then genuinely
 * submits to Step 3 (native HTML5 validation still runs first),
 * instead of the "not built yet" notice it used before Step 3 existed.
 * ---------------------------------------------------------------------
 */

session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // whatever page just submitted here (only Step 1 does) -- merge
    // its fields into the running draft rather than overwrite it
    $_SESSION['miDraft'] = array_merge($_SESSION['miDraft'] ?? [], $_POST);
}

$steps = [
    ['title' => 'Intent Details',  'sub' => 'Provide your intention information'],
    ['title' => 'Schedule',        'sub' => 'Choose preferred Mass schedule'],
    ['title' => 'Review & Submit', 'sub' => 'Review and submit'],
];
$currentStep = 1; // "Schedule"

// mock data -- see file header comment
$massTimes = [
    '6:00 AM',
    '7:00 AM',
    '8:30 AM',
    '10:00 AM (Family Mass)',
    '12:00 PM (Noon Mass)',
    '5:00 PM (Anticipated Mass — Saturday only)',
    '6:00 PM',
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

    <!-- ============================ STEP 2 FORM =========================== -->
    <!-- Step 3 is a real page now, so this form genuinely submits (native
         HTML5 validation still runs first) -- same pattern every other
         wizard's step followed once ITS next step existed. -->
    <form class="ps-card wr-form" action="mass-intention-request-step3.php" method="post">

        <div class="wr-form-header">
            <div>
                <h2>Step 2 of 3: Schedule</h2>
                <p class="wr-form-sub">Choose your preferred Mass schedule for this intention.</p>
            </div>
            <span class="wr-offering-badge"><?php ps_icon('chalice'); ?> Mass Intention offering: <strong>&#8369;100.00</strong></span>
        </div>

        <div class="ps-form-row-3">
            <div class="ps-field">
                <label for="preferredDate">Preferred Mass Date <span class="wr-required">*</span></label>
                <div class="ps-datepicker" data-datepicker>
                    <span class="ps-datepicker-field">
                        <input type="text" id="preferredDate" name="preferredDate" placeholder="mm/dd/yyyy"
                               inputmode="numeric" autocomplete="off" data-datepicker-input
                               pattern="^(0[1-9]|1[0-2])/(0[1-9]|[12][0-9]|3[01])/[0-9]{4}$" required>
                        <button type="button" class="ps-datepicker-toggle" data-datepicker-toggle aria-label="Open calendar">
                            <?php ps_icon('calendar'); ?>
                        </button>
                    </span>
                    <div class="ps-datepicker-panel" data-datepicker-panel hidden></div>
                </div>
                <small class="wr-file-error" id="preferredDateError" hidden></small>
            </div>
            <div class="ps-field">
                <label for="preferredTime">Preferred Mass Time <span class="wr-required">*</span></label>
                <span class="ps-select is-block">
                    <select id="preferredTime" name="preferredTime" required>
                        <option value="" selected>Select preferred time</option>
                        <?php foreach ($massTimes as $time): ?>
                            <option value="<?php echo htmlspecialchars($time); ?>"><?php echo htmlspecialchars($time); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php ps_icon('chevron-down'); ?>
                </span>
                <small class="ps-form-hint">Choose the time most convenient for you.</small>
            </div>
            <div class="ps-field">
                <label>Preferred Mass Type <span class="wr-required">*</span></label>
                <div class="wr-mass-type-options" data-radio-cards>
                    <label class="wr-intent-card is-checked" data-radio-card>
                        <input type="radio" name="massType" value="regular" checked required>
                        <span class="wr-intent-card-icon"><?php ps_icon('church'); ?></span>
                        <strong>Regular Parish Mass</strong>
                        <span class="wr-intent-card-sub">Offered during regular parish schedule</span>
                    </label>
                    <label class="wr-intent-card" data-radio-card>
                        <input type="radio" name="massType" value="special" required>
                        <span class="wr-intent-card-icon"><?php ps_icon('star'); ?></span>
                        <strong>Special / Subject to Parish Confirmation</strong>
                        <span class="wr-intent-card-sub">Requires parish confirmation and availability</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="ps-info-banner">
            <?php ps_icon('info'); ?>
            <span>Your preferred schedule will be reviewed by the parish office and is subject to availability.</span>
        </div>

        <div class="ps-field wr-notes-field">
            <label for="schedulingNotes">Additional scheduling notes <span class="wr-optional">(optional)</span></label>
            <textarea id="schedulingNotes" name="schedulingNotes" rows="3" maxlength="500" placeholder="Add any helpful scheduling details for the parish office."></textarea>
            <small class="ps-form-hint" id="schedulingNotesCount">0 / 500</small>
        </div>

        <div class="wr-actions">
            <a href="mass-intention-request.php" class="ps-btn wr-cancel"><?php ps_icon('arrow-left'); ?> Back</a>
            <button type="submit" class="ps-btn ps-btn-primary wr-submit">Save and Continue <?php ps_icon('arrow-right'); ?></button>
        </div>

    </form>

</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
