<?php
/**
 * donations.php
 * ---------------------------------------------------------------------
 * A single-page donation form (no wizard/step bar -- unlike wedding/
 * baptism/mass intention, the reference image shows everything on one
 * screen: Donation Details, Upload Proof of Payment, Payment Method,
 * and Your Information, all as separate numbered .ps-cards followed
 * by one Cancel/Submit action row). Follows baptism-request.php's
 * "several stacked .ps-cards, actions row outside all of them" shape
 * rather than wedding-request.php's single continuous form card.
 *
 * NOT PART OF ANY WIZARD: this page loads style.css only (no wedding-
 * request.css). The numbered-section-heading/required-asterisk/action-
 * row/notice pieces it needs (.wr-section*, .wr-required, .wr-actions,
 * .wr-next-step-notice, .wr-notes-field/.wr-optional) were promoted
 * from wedding-request.css into style.css THIS round specifically
 * because this page needed them without being a wizard step -- loading
 * a stylesheet whose own header comment calls itself "the shared
 * request wizard stylesheet" for a page with no wizard felt wrong.
 *
 * DONATION PURPOSE is a custom dropdown (.ps-fund-picker, main.js's
 * initFundPickers()), not a plain <select> -- its closed state needs
 * to show an icon + title + description together, which a native
 * select can't render. Same open/close/outside-click/Escape mechanics
 * as the datepicker built earlier this session.
 *
 * PAYMENT METHOD: the QR code and GCash account details
 * (assets/images/gcash-qr-placeholder.svg, the phone number, and the
 * account name below it) are ALL placeholder/mock -- there's no real
 * parish GCash account wired up yet, and the "QR code" is a decorative
 * graphic that LOOKS like one (finder-pattern corners + a noise
 * texture) but doesn't decode to anything real. Swap all of this once
 * the parish provides real payment details.
 *
 * UPLOAD PROOF OF PAYMENT reuses the same real drag-and-drop .ps-
 * dropzone component baptism-request-step2.php's file upload uses
 * (main.js's initDropzones()/initFileUploadValidation()), just with a
 * ringed icon variant (.ps-dropzone-icon.is-ringed, donations.css)
 * instead of that page's plain bare icon.
 *
 * NOTHING IS ACTUALLY SAVED: this form intercepts its own submit (main
 * .js's initWizardStepForm(), reused here even though this isn't a
 * multi-step wizard -- the function itself is generic) and shows an
 * honest "not built yet" notice instead of pretending a donation
 * record and the uploaded screenshot get stored anywhere.
 * ---------------------------------------------------------------------
 */

$fundOptions = [
    ['value' => 'general',    'icon' => 'heart',      'title' => 'General Parish Fund',            'desc' => 'Used for the daily needs of the parish and its ministries.'],
    ['value' => 'building',   'icon' => 'building',   'title' => 'Building & Maintenance Fund',     'desc' => 'Supports church repairs, renovation, and facility upkeep.'],
    ['value' => 'outreach',   'icon' => 'heart-hand', 'title' => 'Outreach & Charity Programs',     'desc' => 'Helps fund outreach programs and assistance for those in need.'],
    ['value' => 'sacraments', 'icon' => 'chalice',    'title' => 'Sacramental Support',             'desc' => 'Supports sacramental celebrations and parish liturgical needs.'],
];

$howToSteps = [
    'Open your GCash app.',
    'Tap "Scan QR".',
    'Scan the QR code.',
    'Enter the amount.',
    'Tap "Send Money" and confirm.',
];

$importantReminders = [
    'Please send the exact amount you indicated.',
    'Do not include any message when sending.',
    'After sending, please upload your proof of payment.',
    'Your donation will be verified by our parish staff.',
];

$pageTitle = 'Donate';
$pageCss   = 'donations.css';
$activeNav = 'donations';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>
<main class="ps-main">

    <!-- ============================ PLAIN HEADER ========================== -->
    <section class="ps-plain-header">
        <?php require __DIR__ . '/includes/topbar.php'; ?>
        <h1>Donate</h1>
        <p>Support our parish and help us continue our mission.</p>
    </section>

    <!-- ============================ INTRO BANNER ========================== -->
    <div class="ps-card wed-banner">
        <div class="wed-banner-art"><img src="assets/images/donate-heart.svg" alt=""></div>
        <div class="wed-banner-text">
            <h2>Your generosity makes a difference.</h2>
            <p>Your donation helps us in our ministries, church maintenance, community outreach, and other parish activities. Thank you for supporting Our Lady of the Gate Parish.</p>
        </div>
    </div>

    <form action="donations.php" method="post" data-wizard-step-form novalidate>

        <div class="don-top-grid">

            <!-- ============================ 1 & 2 (LEFT COLUMN) ============= -->
            <div>
                <!-- ---- 1. Donation Details ---- -->
                <div class="ps-card wr-section">
                    <h2 class="wr-section-heading">1. Donation Details</h2>

                    <div class="ps-form-row-2">
                        <div class="ps-field">
                            <label for="donationPurposeTrigger">Donation Purpose <span class="wr-optional">(Optional)</span></label>
                            <div class="ps-fund-picker" data-fund-picker>
                                <button type="button" class="ps-fund-trigger" id="donationPurposeTrigger" data-fund-trigger>
                                    <span class="ps-fund-icon" data-fund-icon><?php ps_icon($fundOptions[0]['icon']); ?></span>
                                    <span class="ps-fund-text">
                                        <strong data-fund-label><?php echo htmlspecialchars($fundOptions[0]['title']); ?></strong>
                                        <small data-fund-desc><?php echo htmlspecialchars($fundOptions[0]['desc']); ?></small>
                                    </span>
                                    <?php ps_icon('chevron-down', 'ps-fund-chevron'); ?>
                                </button>
                                <input type="hidden" name="donationPurpose" id="donationPurpose" value="<?php echo htmlspecialchars($fundOptions[0]['value']); ?>" data-fund-input>
                                <div class="ps-fund-panel" data-fund-panel hidden>
                                    <?php foreach ($fundOptions as $i => $f): ?>
                                        <button type="button" class="ps-fund-option<?php echo $i === 0 ? ' is-selected' : ''; ?>" data-fund-option
                                                data-value="<?php echo htmlspecialchars($f['value']); ?>" data-desc="<?php echo htmlspecialchars($f['desc']); ?>">
                                            <?php ps_icon($f['icon']); ?>
                                            <span class="ps-fund-option-text">
                                                <strong><?php echo htmlspecialchars($f['title']); ?></strong>
                                                <small><?php echo htmlspecialchars($f['desc']); ?></small>
                                            </span>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <div class="ps-field">
                            <label for="donationAmount">Donation Amount</label>
                            <span class="ps-currency-field">
                                <span class="ps-currency-symbol">&#8369;</span>
                                <input type="number" id="donationAmount" name="donationAmount" min="0" step="0.01" inputmode="decimal" placeholder="0.00">
                            </span>
                            <small class="ps-form-hint">Enter the amount you sent through GCash.</small>
                        </div>
                    </div>
                </div>

                <!-- ---- 2. Upload Proof of Payment ---- -->
                <div class="ps-card wr-section">
                    <h2 class="wr-section-heading">2. Upload Proof of Payment</h2>
                    <p class="wr-section-sub">Please upload a screenshot of your successful GCash transaction.</p>

                    <div class="ps-field">
                        <span class="ps-dropzone" data-dropzone>
                            <input type="file" id="proofOfPayment" name="proofOfPayment"
                                   accept=".png,.jpg,.jpeg" data-max-size-mb="5"
                                   data-dropzone-input>
                            <span class="ps-dropzone-icon is-ringed"><?php ps_icon('upload'); ?></span>
                            <span class="ps-dropzone-text">Click to upload or drag and drop</span>
                            <span class="ps-dropzone-or">PNG, JPG, JPEG (Max. 5MB)</span>
                            <span class="ps-dropzone-filename" data-dropzone-filename>No file chosen</span>
                        </span>
                        <small class="wr-file-error" id="proofOfPaymentError" data-file-error hidden></small>
                    </div>
                </div>
            </div>

            <!-- ============================ 3. PAYMENT METHOD (RIGHT COLUMN) ============= -->
            <div class="ps-card wr-section">
                <h2 class="wr-section-heading">3. Payment Method</h2>
                <p class="wr-section-sub">We currently accept donations through GCash.</p>

                <div class="don-payment-grid">
                    <div class="don-qr-block">
                        <img src="assets/images/gcash-qr-placeholder.svg" alt="GCash QR code for Our Lady of the Gate Parish (placeholder)">
                        <div class="don-qr-name">Our Lady of the Gate Parish</div>
                        <div class="don-qr-number">0915 123 4567</div>
                        <div class="don-qr-account">Juan Dela Cruz</div>
                    </div>

                    <div>
                        <div class="don-howto">
                            <h4><?php ps_icon('info'); ?> How to donate using GCash</h4>
                            <div class="don-howto-list">
                                <?php foreach ($howToSteps as $i => $step): ?>
                                    <div class="don-howto-item">
                                        <span class="don-howto-num"><?php echo (int) $i + 1; ?></span>
                                        <span><?php echo htmlspecialchars($step); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="don-reminders">
                            <h4><?php ps_icon('bell'); ?> Important Reminders</h4>
                            <ul class="wed-reminder-list">
                                <?php foreach ($importantReminders as $r): ?>
                                    <li><?php ps_icon('check'); ?><?php echo htmlspecialchars($r); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ============================ 4. YOUR INFORMATION (FULL WIDTH) ============= -->
        <div class="ps-card wr-section">
            <h2 class="wr-section-heading">4. Your Information</h2>
            <p class="wr-section-sub">Please provide your details so we can thank you.</p>

            <div class="ps-form-row-3">
                <div class="ps-field">
                    <label for="donorName">Full Name</label>
                    <input type="text" id="donorName" name="donorName" placeholder="Enter your full name">
                </div>
                <div class="ps-field">
                    <label for="donorEmail">Email <span class="wr-optional">(Optional)</span></label>
                    <input type="email" id="donorEmail" name="donorEmail" placeholder="Enter your email">
                </div>
                <div class="ps-field">
                    <label for="donorContact">Contact Number <span class="wr-optional">(Optional)</span></label>
                    <input type="tel" id="donorContact" name="donorContact" placeholder="09XXXXXXXXX"
                           pattern="^09[0-9]{9}$" maxlength="11" inputmode="numeric"
                           title="Format: 09XXXXXXXXX (11 digits)">
                </div>
            </div>

            <div class="don-anon-row">
                <input type="checkbox" id="isAnonymous" name="isAnonymous">
                <label for="isAnonymous">I would like to remain anonymous.
                    <small>We will not display your name in any public listing.</small>
                </label>
            </div>
        </div>

        <div class="wr-actions">
            <a href="dashboard.php" class="ps-btn wr-cancel">Cancel</a>
            <button type="submit" class="ps-btn ps-btn-primary wr-submit">Submit Donation <?php ps_icon('arrow-right'); ?></button>
        </div>

        <p class="don-trust-note">
            <?php ps_icon('lock'); ?> Your donation is secure and will be used for parish purposes only.<br>
            Thank you and God bless you!
        </p>

        <p class="wr-next-step-notice" data-wizard-notice hidden>
            <?php ps_icon('info'); ?>
            There's no backend yet to actually process this donation or store the uploaded proof of payment. Once the database is wired up, submitting here will create a real donation record for the parish office to verify.
        </p>

    </form>

</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
