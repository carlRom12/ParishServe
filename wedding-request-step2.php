<?php
/**
 * wedding-request-step2.php
 * ---------------------------------------------------------------------
 * Step 2 of 3 ("Requirements") of the wedding request flow. wedding-
 * request.php's Step 1 form submits here for real. THIS page's own
 * "Save and Continue" now also submits for real, on to
 * wedding-request-step3.php ("Review & Send") -- the "not built yet"
 * JS intercept has moved forward again, now living on Step 3's own
 * submit instead, since there's nothing built after Review & Send.
 *
 * NO DATA CARRIES OVER FROM STEP 1: there's no session/database this
 * session, so this page doesn't read or care about whatever Step 1's
 * form POSTed. Clicking "Back" returns to a blank Step 1, not the
 * couple's info they just typed. That's an accepted limitation of
 * being frontend-only right now -- once a real multi-step save exists
 * (session-backed draft row, most likely), Back/Continue will actually
 * preserve entered data across steps.
 *
 * FILE UPLOADS: each requirement gets a real <input type="file">
 * (native browser file picker + native "No file chosen" text, styled
 * via ::file-selector-button rather than faked with JS) with an
 * `accept` attribute matching its allowed formats and a
 * data-max-size-mb attribute that main.js checks client-side on
 * change (see initFileUploadValidation()) -- a file that's too big
 * gets rejected and cleared immediately instead of only failing once
 * it reaches a server that doesn't exist yet.
 * ---------------------------------------------------------------------
 */

$steps = [
    ['title' => 'The Couple',    'sub' => 'Tell us about you'],
    ['title' => 'Requirements',  'sub' => 'Submit documents'],
    ['title' => 'Review & Send', 'sub' => 'Review and submit'],
];
$currentStep = 1; // "Requirements"

// Mirrors wedding-guidelines.php's Documents Needed list (section B),
// but with the field-level detail this step actually needs: a short
// description line, upload constraints, and whether it's required.
// Items 4/7/8 aren't marked required -- 4 is handled by the parish
// office directly (file input still shown per the reference image,
// just optional), 7/8 are conditional ("if applicable").
$requirements = [
    ['title' => 'Certificate of No Marriage (CENOMAR)', 'desc' => 'From the Statistics Office.', 'accept' => '.pdf,.jpg,.jpeg,.png', 'formats' => 'PDF, JPG, PNG', 'maxMb' => 5, 'required' => true],
    ['title' => 'Permit for non-parishioners and outsiders (BRIDE only).', 'desc' => 'Required for non-parishioners.', 'accept' => '.pdf,.jpg,.jpeg,.png', 'formats' => 'PDF, JPG, PNG', 'maxMb' => 5, 'required' => true],
    ['title' => 'Baptismal & Confirmation Certificates', 'desc' => 'Of the couples.', 'accept' => '.pdf,.jpg,.jpeg,.png', 'formats' => 'PDF, JPG, PNG', 'maxMb' => 5, 'required' => true],
    ['title' => 'Publication of Banns in the Parish and in the parishes where the parties reside.', 'desc' => 'We will handle this with the parish office.', 'accept' => '.pdf,.jpg,.jpeg,.png', 'formats' => 'PDF, JPG, PNG', 'maxMb' => 5, 'required' => false],
    ['title' => 'Marriage License', 'desc' => 'From the PSA.', 'accept' => '.pdf,.jpg,.jpeg,.png', 'formats' => 'PDF, JPG, PNG', 'maxMb' => 5, 'required' => true],
    ['title' => '2 x 2 Picture', 'desc' => 'Recent ID picture of both the couple.', 'accept' => '.jpg,.jpeg,.png', 'formats' => 'JPG, PNG', 'maxMb' => 2, 'required' => true],
    ['title' => 'Authorization number of the Guest Priest (If a guest priest is engaged)', 'desc' => 'If applicable.', 'accept' => '.pdf,.jpg,.jpeg,.png', 'formats' => 'PDF, JPG, PNG', 'maxMb' => 5, 'required' => false],
    ['title' => "Certification from his/her commanding officer (if one or both are military person/s)", 'desc' => 'If applicable.', 'accept' => '.pdf,.jpg,.jpeg,.png', 'formats' => 'PDF, JPG, PNG', 'maxMb' => 5, 'required' => false],
];

$pageTitle = 'Wedding Request';
$pageCss   = 'wedding-request-step2.css';
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
    <?php require __DIR__ . '/includes/step-bar.php'; ?>

    <section class="ann-grid">
        <div class="ann-main">

            <!-- ============================ STEP 2 FORM ======================= -->
            <!-- Step 3 is a real page now, so this submits for real (native
                 HTML5 validation via `required`/`accept` still runs first).
                 enctype is required here since this form actually contains
                 file inputs -- without it the browser would silently send
                 filenames instead of file contents on a real submit. -->
            <form class="ps-card wr-form" action="wedding-request-step3.php" method="post" enctype="multipart/form-data">

                <h2>Step 2 of 3: Requirements</h2>
                <p class="wr-form-sub">Please upload clear and readable copies of the required documents.</p>

                <div class="wr-req-list">
                    <?php foreach ($requirements as $i => $req): ?>
                        <div class="wr-req-row">
                            <span class="wr-req-num"><?php echo (int) $i + 1; ?></span>
                            <div class="wr-req-text">
                                <strong><?php echo htmlspecialchars($req['title']); ?></strong>
                                <small><?php echo htmlspecialchars($req['desc']); ?></small>
                            </div>
                            <div class="wr-req-upload">
                                <input type="file"
                                       id="doc<?php echo (int) $i + 1; ?>"
                                       name="doc<?php echo (int) $i + 1; ?>"
                                       accept="<?php echo htmlspecialchars($req['accept']); ?>"
                                       data-max-size-mb="<?php echo (int) $req['maxMb']; ?>"
                                       <?php echo $req['required'] ? 'required' : ''; ?>>
                                <small class="ps-form-hint">Accepted: <?php echo htmlspecialchars($req['formats']); ?> (Max <?php echo (int) $req['maxMb']; ?>MB)</small>
                                <small class="wr-file-error" data-file-error hidden></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="ps-field wr-notes-field">
                    <label for="officeNotes">Anything the office should know? <span class="wr-optional">(optional)</span></label>
                    <textarea id="officeNotes" name="officeNotes" rows="3" maxlength="500" placeholder="e.g. We would prefer an afternoon ceremony."></textarea>
                    <small class="ps-form-hint" id="officeNotesCount">0 / 500</small>
                </div>

                <div class="wr-actions">
                    <a href="wedding-request.php" class="ps-btn wr-cancel"><?php ps_icon('arrow-left'); ?> Back</a>
                    <button type="submit" class="ps-btn ps-btn-primary wr-submit">Save and Continue <?php ps_icon('arrow-right'); ?></button>
                </div>

            </form>

        </div>

        <!-- ============================ NEED HELP SIDEBAR ==================== -->
        <div class="ann-side">
            <div class="ps-card wr-help-card">
                <h3>Need help?</h3>
                <p>Not sure what to upload? Check our resources.</p>

                <div class="wr-help-links">
                    <a href="wedding-guidelines.php" class="ps-btn ps-btn-outline wr-help-link">View Wedding Guidelines <?php ps_icon('arrow-right'); ?></a>
                    <a href="wedding-guidelines.php#steps" class="ps-btn ps-btn-outline wr-help-link">View Steps to Be Taken <?php ps_icon('arrow-right'); ?></a>
                </div>

                <div class="wr-help-divider"></div>

                <h4>File reminders</h4>
                <ul class="wr-file-reminders">
                    <li>Accepted file types: PDF, JPG, PNG</li>
                    <li>Each file must not exceed 5MB.</li>
                    <li>Please make sure all documents are clear and complete.</li>
                </ul>

                <div class="wr-help-art"><img src="assets/images/document-tray.svg" alt=""></div>
            </div>
        </div>
    </section>

</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
