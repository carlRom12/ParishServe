<?php
/**
 * baptism-request.php
 * ---------------------------------------------------------------------
 * Step 1 of 3 ("Baptism Details") of the baptism request wizard.
 * REBUILT this round per an updated reference image: the previous
 * version was a single-card clone of wedding-request.php's Step 1;
 * this image splits the same idea across three separate .ps-card
 * sections (Baptism Type, Child Information, Parent/Guardian +
 * Preferred Date) with a lot more fields, so the structure diverges
 * from wedding's Step 1 on purpose -- flagging here rather than
 * retrofitting wedding to match, since nobody asked for that yet.
 *
 * "4. Preferred Regular Baptism Date" IS THE FEATURE THIS ROUND WAS
 * ABOUT -- only future Saturdays may be chosen when Regular Baptism is
 * selected:
 *   - Frontend: main.js's initDatepickers() renders a real popup
 *     calendar (NOT a styled native <input type="date">, which can't
 *     have its own browser-drawn popup grey out specific weekdays --
 *     that popup lives in a closed shadow DOM the page can't touch).
 *     Past dates and, whenever "Regular Baptism" is selected, every
 *     non-Saturday render disabled/greyed and are real
 *     `disabled` buttons (not just dimmed text), so they can't be
 *     clicked OR focused. Typing a date by hand is validated the same
 *     way on blur/change, using a strict parser so something like
 *     "02/30/2026" is rejected instead of silently rolling over. The
 *     weekday-only-Saturday rule and the future-only rule are both
 *     computed from the browser's real current date every time -- no
 *     date is ever hard-coded.
 *   - Backend: this file re-validates the submitted date with PHP's
 *     DateTime below BEFORE trusting it, independent of whatever the
 *     JS already blocked -- a hand-crafted POST request skips the
 *     browser (and its JS) entirely, so the server can't lean on the
 *     calendar having done its job. Nothing else on this form gets
 *     that same full re-validation yet (same "HTML5 required/pattern
 *     is the real gate for now" scope as every other wizard step this
 *     session) -- just this one field, because that's what was asked
 *     for. On success this redirects to Step 2 (still no
 *     session/database, so nothing is actually saved) rather than
 *     faking a stored request; on failure it re-renders THIS page with
 *     every field's submitted value preserved and only the date error
 *     shown -- a real (if DB-less) post/redirect/get, not a fake one.
 *
 * DEFAULT UI STAYS QUIET ON PURPOSE: the reference image also shows a
 * permanent blue "Only future Saturdays are available..." banner next
 * to the date field. Per this round's explicit instruction, that's
 * dropped in favor of the plain hint text ("Regular Baptism is
 * scheduled every Saturday.") the disabled calendar itself already
 * communicates -- the red error small only appears when an invalid
 * date actually reaches validation (typed manually, or bounced back
 * from the PHP check above), not as a standing warning.
 *
 * FIELD SHAPE: Child Information and Parent/Guardian Information are
 * both new sections not in the prior version of this page (DOB, Place
 * of Birth, Gender for the child; Relationship/Full Name/Contact/Email
 * for the requestor) -- matched to this round's reference image, minus
 * Civil Status/Nationality/Address, which the image showed but were
 * dropped afterward as unnecessary for the child being baptized. The
 * red required-asterisk next to labels is new too; wedding's Step 1
 * never had one since its own reference image didn't show one either.
 *
 * database/schema.sql mismatch (same flag as before): baptism_requests
 * has nowhere close to enough columns for this shape yet. Whoever
 * wires this up to MySQL needs a schema migration first.
 * ---------------------------------------------------------------------
 */

$steps = [
    ['title' => 'Baptism Details', 'sub' => 'Tell us about the child'],
    ['title' => 'Requirement',     'sub' => 'Submit document'],
    ['title' => 'Review & Submit', 'sub' => 'Review and submit'],
];
$currentStep = 0; // "Baptism Details"

$old = [
    'baptismType'       => 'regular',
    'childFirstName'    => '',
    'childMiddleName'   => '',
    'childLastName'     => '',
    'childSuffix'       => '',
    'childDob'          => '',
    'childPlaceOfBirth' => '',
    'childGender'       => '',
    'relationship'      => '',
    'requestorName'     => '',
    'requestorContact'  => '',
    'requestorEmail'    => '',
    'baptismDate'       => '',
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $key => $default) {
        $old[$key] = trim($_POST[$key] ?? $default);
    }

    // ---- Preferred Baptism Date: the mandatory PHP-side re-check,
    // regardless of whatever the frontend calendar already blocked ----
    $isRegular   = $old['baptismType'] === 'regular';
    $rawDate     = $old['baptismDate'];
    $parsedDate  = DateTime::createFromFormat('m/d/Y', $rawDate);
    $today       = new DateTime('today'); // midnight today
    // createFromFormat() only got date fields ('m/d/Y'), so PHP fills
    // the unspecified time-of-day fields in with the CURRENT time
    // instead of midnight -- without zeroing it out, comparing against
    // $today (a real midnight) would wrongly let TODAY'S date through
    // as "future" any time after 00:00:00, since e.g. 3pm today is
    // technically later than midnight today.
    if ($parsedDate) {
        $parsedDate->setTime(0, 0, 0);
    }

    if ($rawDate === '' || !$parsedDate || $parsedDate->format('m/d/Y') !== $rawDate) {
        $errors['baptismDate'] = 'Please choose a valid date.';
    } elseif ($parsedDate <= $today) {
        $errors['baptismDate'] = 'Please choose a future date.';
    } elseif ($isRegular && (int) $parsedDate->format('N') !== 6) {
        // ISO-8601 day-of-week: 6 = Saturday
        $errors['baptismDate'] = 'Regular Baptism is only available on Saturdays. Please choose a future Saturday.';
    }

    if (empty($errors)) {
        header('Location: baptism-request-step2.php');
        exit;
    }
}

$pageTitle = 'Baptism Request';
$pageCss   = 'wedding-request.css';
$activeNav = 'baptism';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>
<main class="ps-main">

    <!-- ============================ PLAIN HEADER ========================== -->
    <section class="ps-plain-header">
        <?php require __DIR__ . '/includes/topbar.php'; ?>
        <h1>Baptism Request</h1>
        <nav class="ps-breadcrumb">
            <a href="baptism.php">Baptism</a>
            <?php ps_icon('chevron-right'); ?>
            <span>Start Request</span>
        </nav>
    </section>

    <!-- ============================ STEP BAR ============================== -->
    <?php require __DIR__ . '/includes/step-bar.php'; ?>

    <form action="baptism-request.php" method="post" novalidate>

        <!-- ============================ 1. BAPTISM TYPE ==================== -->
        <div class="ps-card wr-section">
            <h2 class="wr-section-heading">1. Baptism Type <span class="wr-required">*</span></h2>
            <p class="wr-section-sub">Please select the type of baptism you are requesting.</p>

            <div class="wr-type-options" data-type-toggle data-radio-cards>
                <label class="wr-type-card<?php echo $old['baptismType'] === 'regular' ? ' is-checked' : ''; ?>" data-radio-card>
                    <input type="radio" name="baptismType" value="regular" <?php echo $old['baptismType'] === 'regular' ? 'checked' : ''; ?>>
                    <span class="wr-type-card-icon"><?php ps_icon('calendar'); ?></span>
                    <span class="wr-type-card-body">
                        <strong>Regular Baptism</strong>
                        <span>Every Saturday</span>
                        <span class="wr-type-fee">Fee: &#8369;500.00</span>
                    </span>
                </label>
                <label class="wr-type-card<?php echo $old['baptismType'] === 'special' ? ' is-checked' : ''; ?>" data-radio-card>
                    <input type="radio" name="baptismType" value="special" <?php echo $old['baptismType'] === 'special' ? 'checked' : ''; ?>>
                    <span class="wr-type-card-icon"><?php ps_icon('star'); ?></span>
                    <span class="wr-type-card-body">
                        <strong>Special Baptism</strong>
                        <span>Preferred / special schedule</span>
                        <span class="wr-type-fee">Fee: &#8369;3,000.00</span>
                    </span>
                </label>
            </div>

            <div class="ps-info-banner" id="baptismTypeBanner"<?php echo $old['baptismType'] === 'regular' ? '' : ' hidden'; ?>>
                <?php ps_icon('info'); ?>
                <span>Regular Baptism is scheduled every Saturday.</span>
            </div>
        </div>

        <!-- ============================ 2. CHILD INFORMATION ================ -->
        <div class="ps-card wr-section">
            <h2 class="wr-section-heading">2. Child Information</h2>

            <div class="ps-form-row-4">
                <div class="ps-field">
                    <label for="childFirstName">First Name <span class="wr-required">*</span></label>
                    <input type="text" id="childFirstName" name="childFirstName" placeholder="Enter first name" value="<?php echo htmlspecialchars($old['childFirstName']); ?>" required>
                </div>
                <div class="ps-field">
                    <label for="childMiddleName">Middle Name</label>
                    <input type="text" id="childMiddleName" name="childMiddleName" placeholder="Enter middle name" value="<?php echo htmlspecialchars($old['childMiddleName']); ?>">
                </div>
                <div class="ps-field">
                    <label for="childLastName">Last Name <span class="wr-required">*</span></label>
                    <input type="text" id="childLastName" name="childLastName" placeholder="Enter last name" value="<?php echo htmlspecialchars($old['childLastName']); ?>" required>
                </div>
                <div class="ps-field">
                    <label for="childSuffix">Suffix (Optional)</label>
                    <span class="ps-select is-block">
                        <select id="childSuffix" name="childSuffix">
                            <option value="" <?php echo $old['childSuffix'] === '' ? 'selected' : ''; ?>>Select suffix</option>
                            <option value="Jr." <?php echo $old['childSuffix'] === 'Jr.' ? 'selected' : ''; ?>>Jr.</option>
                            <option value="Sr." <?php echo $old['childSuffix'] === 'Sr.' ? 'selected' : ''; ?>>Sr.</option>
                            <option value="II" <?php echo $old['childSuffix'] === 'II' ? 'selected' : ''; ?>>II</option>
                            <option value="III" <?php echo $old['childSuffix'] === 'III' ? 'selected' : ''; ?>>III</option>
                        </select>
                        <?php ps_icon('chevron-down'); ?>
                    </span>
                </div>
            </div>

            <div class="ps-form-row-3">
                <div class="ps-field">
                    <label for="childDob">Date of Birth <span class="wr-required">*</span></label>
                    <span class="ps-input-icon">
                        <input type="date" id="childDob" name="childDob" max="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($old['childDob']); ?>" required>
                        <?php ps_icon('calendar'); ?>
                    </span>
                </div>
                <div class="ps-field">
                    <label for="childPlaceOfBirth">Place of Birth <span class="wr-required">*</span></label>
                    <input type="text" id="childPlaceOfBirth" name="childPlaceOfBirth" placeholder="Enter place of birth" value="<?php echo htmlspecialchars($old['childPlaceOfBirth']); ?>" required>
                </div>
                <div class="ps-field">
                    <label for="childGender">Gender <span class="wr-required">*</span></label>
                    <span class="ps-select is-block">
                        <select id="childGender" name="childGender" required>
                            <option value="" <?php echo $old['childGender'] === '' ? 'selected' : ''; ?>>Select gender</option>
                            <option value="Male" <?php echo $old['childGender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo $old['childGender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                        </select>
                        <?php ps_icon('chevron-down'); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- ============================ 3. PARENT/GUARDIAN + DATE =========== -->
        <div class="ps-card wr-section">
            <h2 class="wr-section-heading">3. Parent / Guardian Information (Requestor)</h2>

            <div class="ps-form-row-4">
                <div class="ps-field">
                    <label for="relationship">Relationship to Child <span class="wr-required">*</span></label>
                    <span class="ps-select is-block">
                        <select id="relationship" name="relationship" required>
                            <option value="" <?php echo $old['relationship'] === '' ? 'selected' : ''; ?>>Select relationship</option>
                            <option value="Father" <?php echo $old['relationship'] === 'Father' ? 'selected' : ''; ?>>Father</option>
                            <option value="Mother" <?php echo $old['relationship'] === 'Mother' ? 'selected' : ''; ?>>Mother</option>
                            <option value="Legal Guardian" <?php echo $old['relationship'] === 'Legal Guardian' ? 'selected' : ''; ?>>Legal Guardian</option>
                            <option value="Other" <?php echo $old['relationship'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                        <?php ps_icon('chevron-down'); ?>
                    </span>
                </div>
                <div class="ps-field">
                    <label for="requestorName">Full Name <span class="wr-required">*</span></label>
                    <input type="text" id="requestorName" name="requestorName" placeholder="Enter full name" value="<?php echo htmlspecialchars($old['requestorName']); ?>" required>
                </div>
                <div class="ps-field">
                    <label for="requestorContact">Contact Number <span class="wr-required">*</span></label>
                    <input type="tel" id="requestorContact" name="requestorContact" placeholder="09XXXXXXXXX"
                           pattern="^09[0-9]{9}$" maxlength="11" inputmode="numeric"
                           title="Format: 09XXXXXXXXX (11 digits)" value="<?php echo htmlspecialchars($old['requestorContact']); ?>" required>
                </div>
                <div class="ps-field">
                    <label for="requestorEmail">Email (Optional)</label>
                    <input type="email" id="requestorEmail" name="requestorEmail" placeholder="example@email.com" value="<?php echo htmlspecialchars($old['requestorEmail']); ?>">
                </div>
            </div>

            <h2 class="wr-section-heading">4. Preferred <span id="baptismDateTypeWord"><?php echo $old['baptismType'] === 'regular' ? 'Regular' : 'Special'; ?></span> Baptism Date <span class="wr-required">*</span></h2>
            <p class="wr-section-sub" id="baptismDateHint"><?php echo $old['baptismType'] === 'regular'
                ? 'Regular Baptism is scheduled every Saturday.'
                : 'Choose your preferred date. Final confirmation is subject to parish approval and availability.'; ?></p>

            <div class="ps-field">
                <label for="baptismDate"><span id="baptismDateLabel"><?php echo $old['baptismType'] === 'regular' ? 'Select a future Saturday' : 'Select a preferred date'; ?></span> <span class="wr-required">*</span></label>
                <div class="ps-datepicker" data-datepicker <?php echo $old['baptismType'] === 'regular' ? 'data-weekday="6" ' : ''; ?>data-regular-weekday="6">
                    <span class="ps-datepicker-field">
                        <input type="text" id="baptismDate" name="baptismDate" placeholder="mm/dd/yyyy"
                               inputmode="numeric" autocomplete="off" data-datepicker-input
                               pattern="^(0[1-9]|1[0-2])/(0[1-9]|[12][0-9]|3[01])/[0-9]{4}$"
                               value="<?php echo htmlspecialchars($old['baptismDate']); ?>" required>
                        <button type="button" class="ps-datepicker-toggle" data-datepicker-toggle aria-label="Open calendar">
                            <?php ps_icon('calendar'); ?>
                        </button>
                    </span>
                    <div class="ps-datepicker-panel" data-datepicker-panel hidden></div>
                </div>
                <small class="wr-file-error" id="baptismDateError"<?php echo empty($errors['baptismDate']) ? ' hidden' : ''; ?>><?php echo htmlspecialchars($errors['baptismDate'] ?? ''); ?></small>
                <!-- Special Baptism has only ONE simple rule (must be a
                     future date), so a short standing note here is
                     proportionate, unlike Regular's Saturday-only rule
                     which the greyed-out calendar already communicates
                     on its own -- shown only in Special mode. -->
                <div class="ps-info-banner is-tip" id="baptismDateTipBanner"<?php echo $old['baptismType'] === 'regular' ? ' hidden' : ''; ?>>
                    <?php ps_icon('info'); ?>
                    <span>Past dates are not allowed. Please choose a date today or later.</span>
                </div>
            </div>
        </div>

        <div class="wr-actions">
            <a href="baptism.php" class="ps-btn wr-cancel">Cancel</a>
            <button type="submit" class="ps-btn ps-btn-primary wr-submit">Save and Continue <?php ps_icon('arrow-right'); ?></button>
        </div>

    </form>

</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
