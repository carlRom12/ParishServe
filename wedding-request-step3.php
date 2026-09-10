<?php
/**
 * wedding-request-step3.php
 * ---------------------------------------------------------------------
 * Step 3 of 3 ("Review & Send"), the final screen of the wedding
 * request wizard. Two different kinds of content live on this page:
 *
 *   1. READ-ONLY REVIEW SUMMARY (The Couple, Uploaded Documents,
 *      Additional Information) -- this is what Steps 1 & 2 collected.
 *      Since there's still no session/database this session, Steps 1
 *      and 2 don't actually hand anything off to this page. The values
 *      below are hardcoded SAMPLE data standing in for what a real
 *      multi-step save would show -- same treatment as dashboard.php's
 *      hardcoded stats, not the same as a blank input field waiting
 *      for someone to type into it. TODO once wired up: read the
 *      couple's info + uploaded file list from wherever Steps 1/2
 *      persisted them (session-backed draft row, most likely).
 *
 *   2. A GENUINELY NEW INPUT SECTION (Seminar Schedule) -- the
 *      reference image asks for Pre-Marriage Seminar date/time/location
 *      preferences here for the first time. These fields are real and
 *      left BLANK (same "leave it blank" rule the group set for Step 1),
 *      not pre-filled with the mockup's sample values.
 *
 * SAMPLE DATA CORRECTIONS (flagging, same as Step 1):
 *   - Groom's display name uses "Juan Dela Cruz Santos Jr." (all four
 *     name parts from Step 1's placeholder example), not the image's
 *     "Juan Dela Cruz Jr." which drops the last name entirely.
 *   - Email uses "juan.delacruz@gmail.com" instead of the image's
 *     "caisdjoa@gmail.com", which reads like stray test input rather
 *     than a real address.
 *
 * "I confirm" TOGGLE: the reference image shows it already switched
 * on by default. Left it OFF here instead, with the Submit button
 * disabled until the user actually toggles it -- defaulting a legal-
 * ish "this is all true and correct" confirmation to already-agreed
 * felt like the same kind of fake-completed state the group already
 * asked to remove from Step 1's pre-filled fields.
 *
 * SUBMIT: there's no wedding_requests INSERT and no file storage this
 * session, so "Submit Wedding Request" uses the same inline "not built
 * yet" notice pattern as Step 2's own submit did before Step 3 existed
 * (see main.js's initWizardStepForm()).
 * ---------------------------------------------------------------------
 */

$steps = [
    ['title' => 'The Couple',    'sub' => 'Tell us about you'],
    ['title' => 'Requirements',  'sub' => 'Submit documents'],
    ['title' => 'Review & Send', 'sub' => 'Review and submit'],
];
$currentStep = 2; // "Review & Send"

$groom = [
    'Name' => 'Juan Dela Cruz Santos Jr.',
    'Preferred wedding date' => 'December 12, 2027',
    'Mobile number' => '09321898249',
    'Email address' => 'juan.delacruz@gmail.com',
];
$bride = [
    'Name' => 'Maria Clara Santos',
    'Mobile number' => '09321898249',
    'Email address' => 'juan.delacruz@gmail.com',
];

// Mirrors Step 2's 8 requirements, shown here as "already uploaded"
// filenames. "View" is intentionally non-interactive (see the span
// below) -- there's no real uploaded file behind any of these.
$uploadedDocs = [
    'Certificate of No Marriage (CENOMAR).pdf',
    'Permit (Non-Parishioners and Outsiders).pdf',
    'Baptismal & Confirmation Certificates.pdf',
    'Publication of Banns.pdf',
    'Marriage License.pdf',
    '2 x 2 Picture.jpg',
    'Guest Priest Authorization.pdf',
    'Military Certification.pdf',
];

$additionalInfo = 'We would prefer an afternoon ceremony.';

$importantReminders = [
    'Please make sure all documents are clear and complete.',
    'Accepted file types: PDF, JPG, PNG (Max 5MB per file)',
    'Each file must not exceed 5MB.',
    'In case any document is unclear or missing, the parish office will contact you.',
];

$pageTitle = 'Wedding Request';
$pageCss   = 'wedding-request-step3.css';
$activeNav = 'wedding';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>
<main class="ps-main">

    <!-- ============================ PLAIN HEADER ========================== -->
    <section class="ps-plain-header">
        <?php require __DIR__ . '/includes/topbar.php'; ?>
        <h1>Wedding Request</h1>
        <nav class="ps-breadcrumb">
            <a href="wedding.php">Wedding</a>
            <?php ps_icon('chevron-right'); ?>
            <span>Start Request</span>
        </nav>
    </section>

    <!-- ============================ STEP BAR ============================== -->
    <div class="ps-card wr-stepbar">
        <?php foreach ($steps as $i => $step): ?>
            <div class="wr-step<?php echo $i === $currentStep ? ' is-current' : ''; ?>">
                <span class="wr-step-num"><?php echo (int) $i + 1; ?></span>
                <span class="wr-step-text">
                    <strong><?php echo htmlspecialchars($step['title']); ?></strong>
                    <small><?php echo htmlspecialchars($step['sub']); ?></small>
                </span>
            </div>
            <?php if ($i < count($steps) - 1): ?>
                <?php ps_icon('chevron-right', 'wr-step-sep'); ?>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <form class="wr3-form" action="wedding-request-confirmation.php" method="post" data-wizard-step-form novalidate>

        <section class="ann-grid">
            <div class="ann-main">

                <div class="wr3-intro">
                    <h2>Step 3 of 3: Review &amp; Send</h2>
                    <p>Please review all the information below before submitting your request.</p>
                </div>

                <div class="wr3-review-grid">

                    <!-- ---- The Couple ---- -->
                    <div class="ps-card wr3-review-card">
                        <h3 class="ps-card-title"><?php ps_icon('people'); ?> The Couple</h3>

                        <span class="wr3-section-label">Groom</span>
                        <div class="wr3-review-rows">
                            <?php foreach ($groom as $label => $value): ?>
                                <div class="wr3-review-row"><span><?php echo htmlspecialchars($label); ?></span><strong><?php echo htmlspecialchars($value); ?></strong></div>
                            <?php endforeach; ?>
                        </div>

                        <span class="wr3-section-label">Bride</span>
                        <div class="wr3-review-rows">
                            <?php foreach ($bride as $label => $value): ?>
                                <div class="wr3-review-row"><span><?php echo htmlspecialchars($label); ?></span><strong><?php echo htmlspecialchars($value); ?></strong></div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- ---- Uploaded Documents ---- -->
                    <div class="ps-card wr3-review-card">
                        <h3 class="ps-card-title"><?php ps_icon('document'); ?> Uploaded Documents (<?php echo count($uploadedDocs); ?>)</h3>
                        <ul class="wr3-doc-list">
                            <?php foreach ($uploadedDocs as $i => $doc): ?>
                                <li>
                                    <span class="wr3-doc-check"><?php ps_icon('check'); ?></span>
                                    <span class="wr3-doc-name"><?php echo (int) $i + 1; ?>. <?php echo htmlspecialchars($doc); ?></span>
                                    <span class="wr3-doc-view" title="No file is actually attached to this review yet -- Steps 1/2 don't persist data across pages this session.">View</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- ---- Seminar Schedule (real, blank inputs) ---- -->
                    <div class="ps-card wr3-review-card">
                        <h3 class="ps-card-title"><?php ps_icon('calendar'); ?> Seminar Schedule</h3>
                        <p class="wr3-card-sub">Please choose your preferred schedule for the Pre-Marriage Seminar.</p>

                        <div class="ps-info-banner is-tip wr3-seminar-note">
                            <?php ps_icon('info'); ?>
                            <span>The parish will confirm the final schedule based on availability.</span>
                        </div>

                        <div class="wr3-seminar-fields">
                            <div class="ps-field">
                                <label for="seminarDate">Preferred seminar date</label>
                                <span class="ps-input-icon">
                                    <input type="date" id="seminarDate" name="seminarDate" required>
                                    <?php ps_icon('calendar'); ?>
                                </span>
                            </div>
                            <div class="ps-field">
                                <label for="seminarTime">Preferred time</label>
                                <span class="ps-select is-block">
                                    <select id="seminarTime" name="seminarTime" required>
                                        <option value="" selected>Select time</option>
                                        <option value="08:00 AM">08:00 AM</option>
                                        <option value="09:00 AM">09:00 AM</option>
                                        <option value="01:00 PM">01:00 PM</option>
                                        <option value="02:00 PM">02:00 PM</option>
                                    </select>
                                    <?php ps_icon('chevron-down'); ?>
                                </span>
                            </div>
                            <div class="ps-field">
                                <label for="seminarLocation">Preferred location</label>
                                <span class="ps-select is-block">
                                    <select id="seminarLocation" name="seminarLocation" required>
                                        <option value="" selected>Select location</option>
                                        <option value="Parish Hall">Parish Hall</option>
                                        <option value="Main Church">Main Church</option>
                                        <option value="Function Room">Function Room</option>
                                    </select>
                                    <?php ps_icon('chevron-down'); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- ---- Additional Information ---- -->
                    <div class="ps-card wr3-review-card">
                        <h3 class="ps-card-title"><?php ps_icon('message'); ?> Additional Information</h3>
                        <div class="wr3-review-row wr3-review-row-wrap">
                            <span>Anything the office should know?</span>
                            <strong><?php echo htmlspecialchars($additionalInfo); ?></strong>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ============================ SIDEBAR =========================== -->
            <div class="ann-side">
                <div class="ps-card wr3-next-card">
                    <div class="wr3-next-art"><img src="assets/images/wedding-rings.svg" alt=""></div>

                    <h3>What happens next?</h3>
                    <p>After you submit your request, our parish office will review your documents.</p>
                    <p>We'll contact you for the next steps: pre-Cana seminar, schedule reservation, and payment.</p>

                    <div class="wr3-divider"></div>

                    <h4>Need help?</h4>
                    <p>Contact our parish office during office hours.</p>
                    <a href="contacts.php" class="ps-btn ps-btn-outline wr3-help-btn"><?php ps_icon('phone'); ?> Contact Parish Office</a>

                    <div class="wr3-divider"></div>

                    <h4>Office Hours</h4>
                    <p>Monday to Saturday<br><strong>8:00 AM - 5:00 PM</strong></p>
                    <p class="wr3-muted">(Sunday Closed)</p>
                </div>
            </div>
        </section>

        <!-- ---- Important Reminders (full width, spans both columns) ---- -->
        <div class="ps-card wr3-reminders-card">
            <h3 class="ps-card-title"><?php ps_icon('bell'); ?> Important Reminders</h3>
            <ul class="wed-reminder-list wr3-reminders-list">
                <?php foreach ($importantReminders as $r): ?>
                    <li><?php ps_icon('check'); ?><?php echo htmlspecialchars($r); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- ---- Confirm bar ---- -->
        <div class="ps-card wr3-confirm-bar">
            <div class="wr3-confirm-text">
                <span class="wr3-confirm-icon"><?php ps_icon('check'); ?></span>
                <span>
                    <strong>Please confirm</strong>
                    <small>I confirm that all the information and documents provided are true and correct.</small>
                </span>
            </div>
            <label class="ps-toggle">
                <input type="checkbox" id="confirmTruthful" name="confirmTruthful" data-confirm-toggle required>
                <span class="ps-toggle-track"><span class="ps-toggle-thumb"></span></span>
                <span class="ps-toggle-label">I confirm</span>
            </label>
        </div>

        <div class="wr-actions">
            <a href="wedding-request-step2.php" class="ps-btn wr-cancel"><?php ps_icon('arrow-left'); ?> Back</a>
            <button type="submit" class="ps-btn ps-btn-primary wr-submit" data-confirm-submit disabled>
                <?php ps_icon('send'); ?> Submit Wedding Request
            </button>
        </div>

        <p class="wr-next-step-notice" data-wizard-notice hidden>
            <?php ps_icon('info'); ?>
            There's no backend yet to actually save this request or store the uploaded files. Once the database is wired up, submitting here will create a real row in <code>wedding_requests</code>, generate a reference number, and take you to a confirmation page.
        </p>

    </form>

</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
