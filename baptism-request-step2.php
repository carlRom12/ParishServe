<?php
require_once __DIR__ . '/includes/request-forms.php';
ps_handle_request_form('baptism');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Baptism Request | ParishServe</title>

<link rel="stylesheet" href="assets/css/style.css?v=confirmation-6">

<link rel="stylesheet" href="assets/css/confirmation-layout.css?v=12">
<link rel="stylesheet" href="assets/css/funeral-layout.css?v=1">
<link rel="stylesheet" href="assets/css/responsive.css?v=3">
<link rel="stylesheet" href="assets/css/counseling.css?v=2">
<link rel="stylesheet" href="assets/css/mass-intention.css?v=1">
<link rel="stylesheet" href="assets/css/service-cards.css?v=1">
<link rel="stylesheet" href="assets/css/baptism-home.css?v=1">
<link rel="stylesheet" href="assets/css/counseling-about.css?v=1">
<link rel="stylesheet" href="assets/css/baptism-about.css?v=1">
<link rel="stylesheet" href="assets/css/service-tabs.css?v=1">
<link rel="stylesheet" href="assets/css/service-back.css?v=2">
<link rel="stylesheet" href="assets/css/wedding-request.css">
<link rel="stylesheet" href="assets/css/baptism-request-step2.css">
<link rel="stylesheet" href="assets/css/baptism-request-layout.css?v=6">
<link rel="stylesheet" href="assets/css/service-review.css?v=2">
<link rel="stylesheet" href="assets/css/upload-preview.css?v=1">
<link rel="stylesheet" href="assets/css/service-payment.css?v=1">
<link rel="stylesheet" href="assets/css/sidebar-refined.css?v=2">
<link rel="stylesheet" href="assets/css/sidebar-hover.css?v=4">
</head>
<body class="funeral-page mass-intention-page baptism-home-page baptism-about-page ps-hover-sidebar">
<div class="ps-shell">
<aside class="ps-sidebar">

    <div class="ps-logo">
        <div class="ps-logo-crest"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2c1.6 1.6 3.5 2.2 5.5 2.2v6.3C17.5 15 15.3 19 12 21c-3.3-2-5.5-6-5.5-10.5V4.2C8.5 4.2 10.4 3.6 12 2z"/><path d="M12 8.5v6M9 11.5h6"/></svg></div>
        <div class="ps-logo-eyebrow">Our Lady<br>of the Gate</div>
        <div class="ps-logo-name">ParishServe</div>
        <div class="ps-logo-sub">Parish Community Portal</div>
    </div>

    <nav class="ps-nav">
                                <ul class="ps-nav-list">
                                    <li>
                        <a class="ps-nav-link"
                           href="dashboard.html">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 11.5 12 4l8 7.5"/><path d="M6 10v9a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1v-9"/><path d="M10 20v-6h4v6"/></svg>                            <span>Dashboard</span>
                        </a>
                    </li>
                                    <li>
                        <a class="ps-nav-link"
                           href="announcements.html">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 10v4h3l6 4V6L6 10H3z"/><path d="M14 9c1.2 1 1.2 5 0 6"/><path d="M17 7c2 2 2 8 0 10"/></svg>                            <span>Announcements</span>
                        </a>
                    </li>
                                    <li>
                        <a class="ps-nav-link"
                           href="calendar.html">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 9h18"/><path d="M8 3v4"/><path d="M16 3v4"/></svg>                            <span>Parish Calendar</span>
                        </a>
                    </li>
                            </ul>
                                    <span class="ps-nav-section">Sacraments</span>
                        <ul class="ps-nav-list">
                                    <li>
                        <a class="ps-nav-link"
                           href="wedding.html">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="8" cy="14" r="6"/><circle cx="16" cy="14" r="6"/><path d="m6 5 2-2 2 2-2 3-2-3Zm8 0 2-2 2 2-2 3-2-3Z"/></svg>                            <span>Wedding</span>
                        </a>
                    </li>
                                    <li>
                        <a class="ps-nav-link active" aria-current="page" href="baptism.html">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2S5 11 5 15a7 7 0 0 0 14 0c0-4-7-13-7-13Z"/><path d="M15 15a3 3 0 0 1-3 3"/></svg>                            <span>Baptism</span>
                        </a>
                    </li>
                                    <li>
                        <a class="ps-nav-link"
                           href="confirmation.html">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 5c3 1 4 4 7 5l3-4 3 1 2-2 3 2-3 3c-1 5-4 8-9 8l-5 3-2-3 4-3C3 12 2 9 3 5Z"/><path d="m7 11 5 3m4-6h.01"/></svg>                            <span>Confirmation</span>
                        </a>
                    </li>
                                    <li>
                        <a class="ps-nav-link" href="funeral.html">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v18"/><path d="M7 8h10"/></svg>                            <span>Burial / Funeral</span>
                        </a>
                    </li>
                            </ul>
                                    <span class="ps-nav-section">Parish Services</span>
                        <ul class="ps-nav-list">
                                    
                                    <li>
                        <a class="ps-nav-link" href="mass-intention.html">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 3h14v3a7 7 0 0 1-14 0V3Zm7 10v5m-5 3h10l-2-3H9l-2 3Z"/></svg>                            <span>Mass Intention</span>
                        </a>
                    </li>
                                    
                                    <li>
                        <a class="ps-nav-link"
                           href="donations.html">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z"/></svg>                            <span>Donate</span>
                        </a>
                    </li>
                            </ul>
                                    <span class="ps-nav-section">Other</span>
                        <ul class="ps-nav-list">
                                    <li>
                        <a class="ps-nav-link"
                           href="profile.html">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c0-3.9 3.1-7 7-7s7 3.1 7 7"/></svg>                            <span>My Profile</span>
                        </a>
                    </li>
                                    <li>
                        <a class="ps-nav-link"
                           href="settings.html">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M12 3v2.2M12 18.8V21M21 12h-2.2M5.2 12H3M18.4 5.6l-1.5 1.5M7.1 16.9l-1.5 1.5M18.4 18.4l-1.5-1.5M7.1 7.1 5.6 5.6"/></svg>                            <span>Settings</span>
                        </a>
                    </li>
                            </ul>
            </nav>

    <div class="ps-sidebar-art"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2v3M10.5 4h3M4 21V11l8-6 8 6v10"/><path d="M4 21h16"/><path d="M9 21v-6h6v6"/><path d="M9 12h.01M15 12h.01"/></svg></div>

    <div class="ps-logout-wrap">

        <a href="logout.php" class="ps-logout-btn" data-session-auth-link>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>            <span>Log out</span>
        </a>
    </div>

</aside>
<main class="ps-main">

    <div class="conf-topline service-topline"><a class="service-back" href="baptism.html"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M19 12H5m6-6-6 6 6 6"/></svg>Back to Baptism</a>
        <div class="ps-topbar">

    <button type="button" class="ps-notif-btn" aria-label="Notifications">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9a6 6 0 0 1 12 0c0 4 1.5 5.5 2 6.5H4c.5-1 2-2.5 2-6.5z"/><path d="M10 19a2 2 0 0 0 4 0"/></svg>                    
            </button>

    <div class="ps-user-chip">
        <span class="ps-user-avatar" data-session-initial>G</span>
        <span class="ps-user-info">
            <strong data-session-name>Guest</strong>
            <small data-session-role>Not signed in</small>
        </span>
        <svg class="ps-user-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>    </div>
</div>
    </div>

<section class="co-hero" aria-labelledby="baptism-title"><img src="assets/images/baptism-hero.png" alt="Baptismal font with water, a white cloth with a gold cross, and candles in a church"><div class="co-hero-copy"><span class="co-eyebrow">Sacraments</span><h1 id="baptism-title">Request Baptism</h1><p>Provide your child&rsquo;s details and prepare your baptism request.</p><blockquote>&ldquo;Let the little children come to me.&rdquo;<cite>&mdash; Mark 10:14</cite></blockquote></div></section><nav class="ca-tabs" id="baptism-tabs" aria-label="Baptism sections"><a href="baptism-about.html"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v16M12 5C9 3 5 3 2 5v16c3-2 7-2 10 0 3-2 7-2 10 0V5c-3-2-7-2-10 0Z"/></svg>About Baptism</a><a href="baptism-requirements.html"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H5v20h14V7l-5-5Zm0 0v6h5M8 12h8M8 16h8"/></svg>Requirements</a><a href="baptism-schedule.html"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 2v6m8-6v6"/></svg>Schedule</a><a href="baptism-request.html" aria-current="page"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 3 6 6-12 12H3v-6L15 3Zm-2 2 6 6"/></svg>Request</a></nav><div class="ps-card wr-stepbar">
                    <div class="wr-step is-done">
            <span class="wr-step-num">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>                            </span>
            <span class="wr-step-text">
                <strong>Baptism Details</strong>
                <small>Tell us about the child</small>
            </span>
        </div>
                    <svg class="wr-step-sep" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>                            <div class="wr-step is-current">
            <span class="wr-step-num">
                                    2                            </span>
            <span class="wr-step-text">
                <strong>Requirement</strong>
                <small>Submit document</small>
            </span>
        </div>
                    <svg class="wr-step-sep" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>                            <div class="wr-step">
            <span class="wr-step-num">
                                    3                            </span>
            <span class="wr-step-text">
                <strong>Review &amp; Submit</strong>
                <small>Review and submit</small>
            </span>
        </div>
            </div>

    <form class="ps-card wr-form" action="baptism-request-step2.php" method="post" enctype="multipart/form-data" data-baptism-upload data-upload-flow="baptism" data-draft-key="parishserve-draft-baptism" novalidate><?php ps_request_form_fields(); ?>

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
                    <span class="ps-dropzone-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17a4.5 4.5 0 0 1-1-8.9A5.5 5.5 0 0 1 16.5 8H17a4 4 0 0 1 1 7.9"/><path d="M12 12v7"/><path d="M9 15l3-3 3 3"/></svg></span>
                    <span class="ps-dropzone-text">Drag and drop your file here</span>
                    <span class="ps-dropzone-or">or</span>
                    <span class="ps-dropzone-btn">Choose File</span>
                    <span class="ps-dropzone-filename" data-dropzone-filename>No file chosen</span>
                </span>
                <small class="wr-file-error" id="birthCertificateError" data-file-error hidden></small>
            </div>
        </div>

        <div class="ps-info-banner bap2-note">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5.5"/><path d="M12 7.8h.01"/></svg>            <span>This is the only required document. Please make sure the birth certificate is readable and complete before uploading.</span>
        </div>

        <div class="ps-info-banner bap2-note">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 3h7l4 4v14a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z"/><path d="M14 3v4h4"/><path d="M9 13h6M9 16.5h6M9 9.5h3"/></svg>            <span>Allowed file types: PDF, JPG, PNG</span>
        </div>

        <section class="wr-payment" data-service-payment data-amount-from="parishserve-draft-baptism:baptismType" data-amount-map='{"regular":{"label":"Regular Baptism","amount":"₱500.00"},"special":{"label":"Special Baptism","amount":"₱3,000.00"}}' aria-labelledby="bapPaymentTitle">
            <h3 id="bapPaymentTitle">Baptism Fee &amp; Payment</h3>
            <p class="wr-form-sub">Pay the baptism fee upfront through GCash using the QR code, then attach a screenshot of your payment.</p>

            <div class="wr-pay-grid">
                <div>
                    <p class="wr-pay-due"><span>Amount to send <small data-pay-label></small></span> <strong data-pay-amount>Choose a type in Step 1</strong></p>
                    <small class="ps-form-hint">Regular Baptism: ₱500.00 &middot; Special Baptism: ₱3,000.00 &mdash; based on the type you chose in Step 1.</small>

                    <ol class="wr-pay-steps">
                        <li>Open your GCash app and tap "Scan QR".</li>
                        <li>Scan the parish QR code shown here.</li>
                        <li>Enter the exact amount for your baptism type.</li>
                        <li>Tap "Send" and take a screenshot of the confirmation.</li>
                    </ol>
                </div>

                <div class="wr-pay-qr">
                    <img src="assets/images/gcash-qr-placeholder.svg" alt="Placeholder only - not an official payment QR">
                    <p class="wr-pay-qr-label">Preview only &mdash; official QR pending</p>
                    <strong>Our Lady of the Gate Parish</strong>
                </div>
            </div>

            <div class="wr-pay-proof">
                <label for="paymentProof">Screenshot of your GCash payment <span class="wr-optional">(optional)</span></label>
                <span class="ps-dropzone" data-dropzone>
                    <input type="file" id="paymentProof" accept=".jpg,.jpeg,.png" data-max-size-mb="5">
                    <span class="ps-dropzone-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17a4.5 4.5 0 0 1-1-8.9A5.5 5.5 0 0 1 16.5 8H17a4 4 0 0 1 1 7.9"/><path d="M12 12v7"/><path d="M9 15l3-3 3 3"/></svg></span>
                    <span class="ps-dropzone-text">Drag and drop your screenshot here</span>
                    <span class="ps-dropzone-or">or</span>
                    <span class="ps-dropzone-btn">Choose File</span>
                    <span class="ps-dropzone-filename">No file chosen</span>
                </span>
                <small class="ps-form-hint">Accepted: JPG, PNG (Max 5MB)</small>
                <p class="wr-pay-notice">Online sending of payment screenshots isn't connected yet, so this file stays on your device. Please keep your screenshot and present it to the parish office to confirm your payment.</p>
            </div>
        </section>

        <div class="ps-field wr-notes-field">
            <label for="officeNotes">Additional note <span class="wr-optional">(optional)</span></label>
            <textarea id="officeNotes" name="officeNotes" rows="3" maxlength="500" placeholder="Add any details the parish office should know..."></textarea>
            <small class="ps-form-hint bap2-counter" id="officeNotesCount">0 / 500</small>
        </div>

        <div class="wr-actions">
            <a href="baptism-request.html" class="ps-btn wr-cancel"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5"/><path d="M11 18l-6-6 6-6"/></svg> Back</a>
            <button type="submit" class="ps-btn ps-btn-primary wr-submit">Save and Continue <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg></button>
        </div>

        <p id="baptismStatus" role="status" hidden></p>

    </form>

</main>
</div>
<script src="assets/js/frontend.js"></script>
<script src="assets/js/main.js"></script>
<script src="assets/js/session-user.js"></script>
<script src="assets/js/responsive.js?v=1"></script>
<script src="assets/js/baptism-request-tab.js?v=1"></script><script src="assets/js/request-uploads.js"></script>
<script src="assets/js/baptism-upload.js?v=21"></script><script src="assets/js/service-payment.js?v=1"></script><script src="assets/js/upload-preview.js?v=4"></script></body>
</html>
