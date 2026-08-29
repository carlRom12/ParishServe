<?php
/**
 * baptism-request-step2.php
 * ---------------------------------------------------------------------
 * Step 2 of 3 ("Requirement") of the baptism request wizard. Baptism
 * only needs ONE document (Birth Certificate), so instead of reusing
 * wedding-request-step2.php's 8-row native-file-input list, this
 * reference image shows a single, more prominent drag-and-drop zone --
 * built as a real one (assets/css/style.css's .ps-dropzone +
 * main.js's initDropzones()), not just a styled-up native input:
 *   - the native <input type="file"> covers the ENTIRE dashed box
 *     (absolutely positioned, invisible), so clicking anywhere in it
 *     opens the file picker, not just the "Choose File" pill
 *   - real dragenter/dragover/drop handling, with the dropped file
 *     assigned to that same real input so form submission and the
 *     existing max-size validator both still see it
 *   - the "No file chosen" text updates whether the file arrived via
 *     picker or drag-and-drop
 *
 * Also introduces a live "0 / 500" character counter on the notes
 * textarea (main.js's initCharacterCounters()) -- wedding-request-
 * step2.php's equivalent field only had a static "Up to 500
 * characters." hint before; retrofitted a matching counter there too
 * for consistency rather than leaving two different conventions.
 *
 * Step bar now comes from includes/step-bar.php (shared across every
 * wizard page as of this session) -- Step 1 renders as done with a
 * checkmark automatically, matching this reference image.
 * ---------------------------------------------------------------------
 */

$steps = [
    ['title' => 'Baptism Details', 'sub' => 'Tell us about the child'],
    ['title' => 'Requirement',     'sub' => 'Submit document'],
    ['title' => 'Review & Submit', 'sub' => 'Review and submit'],
];
$currentStep = 1; // "Requirement"

$pageTitle = 'Baptism Request';
// $pageCss can be an array -- the shared wizard step bar/layout styles
// plus this page's own small set of classes (.bap2-*)
$pageCss   = ['wedding-request.css', 'baptism-request-step2.css'];
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

    <!-- ============================ STEP 2 FORM =========================== -->
    <form class="ps-card wr-form" action="baptism-request-step3.php" method="post" enctype="multipart/form-data" data-wizard-step-form novalidate>

        <h2>Step 2 of 3: Requirement</h2>
        <p class="wr-form-sub">Please upload the required document for the baptism request.</p>

        <div class="bap2-upload-row">
            <div class="bap2-upload-label">
                <strong>Birth Certificate</strong>
                <small>Original or PSA copy</small>
            </div>

            <div class="ps-field">
                <span class="ps-dropzone" data-dropzone>
                    <input type="file" id="birthCertificate" name="birthCertificate"
                           accept=".pdf,.jpg,.jpeg,.png" data-max-size-mb="5"
                           data-dropzone-input required>
                    <span class="ps-dropzone-icon"><?php ps_icon('upload'); ?></span>
                    <span class="ps-dropzone-text">Drag and drop your file here</span>
                    <span class="ps-dropzone-or">or</span>
                    <span class="ps-dropzone-btn">Choose File</span>
                    <span class="ps-dropzone-filename" data-dropzone-filename>No file chosen</span>
                </span>
                <small class="wr-file-error" id="birthCertificateError" data-file-error hidden></small>
            </div>
        </div>

        <div class="ps-info-banner bap2-note">
            <?php ps_icon('info'); ?>
            <span>This is the only required document. Please make sure the birth certificate is readable and complete before uploading.</span>
        </div>

        <div class="ps-info-banner bap2-note">
            <?php ps_icon('document'); ?>
            <span>Allowed file types: PDF, JPG, PNG</span>
        </div>

        <div class="ps-field wr-notes-field">
            <label for="officeNotes">Additional note <span class="wr-optional">(optional)</span></label>
            <textarea id="officeNotes" name="officeNotes" rows="3" maxlength="500" placeholder="Add any details the parish office should know..."></textarea>
            <small class="ps-form-hint bap2-counter" id="officeNotesCount">0 / 500</small>
        </div>

        <div class="wr-actions">
            <a href="baptism-request.php" class="ps-btn wr-cancel"><?php ps_icon('arrow-left'); ?> Back</a>
            <button type="submit" class="ps-btn ps-btn-primary wr-submit">Save and Continue <?php ps_icon('arrow-right'); ?></button>
        </div>

        <p class="wr-next-step-notice" data-wizard-notice hidden>
            <?php ps_icon('info'); ?>
            Step 3 (Review & Submit) isn't built yet — this is where you'd review everything and submit the final request. Your uploaded file above isn't sent anywhere yet since there's no database or storage wired up.
        </p>

    </form>

</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
