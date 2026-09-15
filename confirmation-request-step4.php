<?php
require_once __DIR__ . '/includes/request-forms.php';
ps_handle_request_form('confirmation');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Apply for Confirmation · ParishServe</title>

<link rel="stylesheet" href="assets/css/style.css?v=confirmation-2">

<link rel="stylesheet" href="assets/css/wedding-request.css">
<link rel="stylesheet" href="assets/css/wedding-request-step3.css">
<link rel="stylesheet" href="assets/css/confirmation-layout.css?v=12">
<link rel="stylesheet" href="assets/css/responsive.css?v=3">
<link rel="stylesheet" href="assets/css/service-tabs.css?v=1">
<link rel="stylesheet" href="assets/css/service-back.css?v=2">
<link rel="stylesheet" href="assets/css/service-review.css?v=2">
<link rel="stylesheet" href="assets/css/sidebar-refined.css?v=2">
</head>
<body class="confirmation-page">
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
                        <a class="ps-nav-link"
                           href="baptism.html">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2S5 11 5 15a7 7 0 0 0 14 0c0-4-7-13-7-13Z"/><path d="M15 15a3 3 0 0 1-3 3"/></svg>                            <span>Baptism</span>
                        </a>
                    </li>
                                    <li>
                        <a class="ps-nav-link active"
                           href="confirmation.html">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 5c3 1 4 4 7 5l3-4 3 1 2-2 3 2-3 3c-1 5-4 8-9 8l-5 3-2-3 4-3C3 12 2 9 3 5Z"/><path d="m7 11 5 3m4-6h.01"/></svg>                            <span>Confirmation</span>
                        </a>
                    </li>
                                    <li>
                        <a class="ps-nav-link"
                           href="funeral.html">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v18"/><path d="M7 8h10"/></svg>                            <span>Burial / Funeral</span>
                        </a>
                    </li>
                            </ul>
                                    <span class="ps-nav-section">Parish Services</span>
                        <ul class="ps-nav-list">
                                    <li>
                        <a class="ps-nav-link"
                           href="counseling.html">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="7" r="3"/><path d="M5 21v-2a7 7 0 0 1 14 0v2M4 5a3 3 0 0 0 0 6m16-6a3 3 0 0 1 0 6M2 19v-2a5 5 0 0 1 3-4m17 6v-2a5 5 0 0 0-3-4"/></svg>                            <span>Counseling</span>
                        </a>
                    </li>
                                    <li>
                        <a class="ps-nav-link"
                           href="mass-intention.html">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 3h14v3a7 7 0 0 1-14 0V3Zm7 10v5m-5 3h10l-2-3H9l-2 3Z"/></svg>                            <span>Mass Intention</span>
                        </a>
                    </li>
                                    <li>
                        <a class="ps-nav-link"
                           href="facility-reservation.html">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="8" width="7" height="13"/><rect x="13" y="3" width="7" height="18"/><path d="M6.5 11h2M6.5 14h2M6.5 17h2M15.5 6h2M15.5 9h2M15.5 12h2M15.5 15h2"/></svg>                            <span>Facility Reservation</span>
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

    <div class="conf-topline service-topline">
<a href="confirmation.html" class="conf-back-link service-back"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5m6-6-6 6 6 6"/></svg>Back to Confirmation</a>
        
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

    <section class="conf-hero">
        <div class="conf-hero-image">
            <img src="assets/images/confirmation.png" alt="A priest laying hands on a young man's forehead during a Confirmation Mass">
        </div>
        <div class="conf-hero-text">
            <span class="conf-hero-eyebrow">Sacraments</span>
            <h1>Apply for Confirmation</h1>
            <p>Take the next step in your faith journey.</p>
            <div class="conf-hero-quote">
                <p>&ldquo;Be sealed with the gift of the Holy Spirit.&rdquo;</p>
                <cite>— Ephesians 1:13</cite>
            </div>
        </div>
    </section>

    <nav class="conf-tabs" aria-label="Confirmation sections">
        <a href="confirmation-guidelines.html" class="conf-tab">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5c2-1.2 5-1.2 7 0v14c-2-1.2-5-1.2-7 0z"/><path d="M20 5c-2-1.2-5-1.2-7 0v14c2-1.2 5-1.2 7 0z"/></svg>
            About Confirmation
        </a>
        <a href="confirmation-requirements.html" class="conf-tab">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 3h7l4 4v14a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z"/><path d="M14 3v4h4"/><path d="M9 13h6M9 16.5h6M9 9.5h3"/></svg>
            Requirements
        </a>
        <a href="confirmation-schedule.html" class="conf-tab">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 9h18"/><path d="M8 3v4"/><path d="M16 3v4"/></svg>
            Schedule
        </a>
        <a href="confirmation-request.html" class="conf-tab active">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 4l5 5-11 11H4v-5z"/><path d="M13 6l5 5"/></svg>
            Apply Now
        </a>
    </nav>

    <div class="ps-card wr-stepbar">
        <div class="wr-step is-done">
            <span class="wr-step-num"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg></span>
            <span class="wr-step-text">
                <strong>Personal Information</strong>
                <small>Tell us about the candidate</small>
            </span>
        </div>
        <svg class="wr-step-sep" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
        <div class="wr-step is-done">
            <span class="wr-step-num"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg></span>
            <span class="wr-step-text">
                <strong>Additional Information</strong>
                <small>Sponsor and schedule details</small>
            </span>
        </div>
        <svg class="wr-step-sep" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
        <div class="wr-step is-done">
            <span class="wr-step-num"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg></span>
            <span class="wr-step-text">
                <strong>Upload Document</strong>
                <small>Submit Baptismal Certificate</small>
            </span>
        </div>
        <svg class="wr-step-sep" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
        <div class="wr-step is-current">
            <span class="wr-step-num">4</span>
            <span class="wr-step-text">
                <strong>Review &amp; Submit</strong>
                <small>Review and submit</small>
            </span>
        </div>
    </div>

    <form class="wr-form" action="confirmation-request-step4.php" method="post" data-wizard-step-form data-draft-key="parishserve-draft-confirmation" novalidate><?php ps_request_form_fields(); ?>

        <div class="ps-card wr3-intro">
            <div>
                <h2>Review &amp; Submit</h2>
                <p>Please review your Confirmation application details before submitting.</p>
            </div>
            <span class="conf-review-progress" id="confReviewProgress">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7.5v5l3.5 2"/></svg>
                <span id="confReviewProgressText">Checking details…</span>
            </span>
        </div>

        <div class="wr3-review-grid">

            <div class="ps-card wr3-review-card">
                <div class="wr3-review-card-header">
                    <span class="wr3-review-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c0-3.9 3.1-7 7-7s7 3.1 7 7"/></svg></span>
                    <h3>1. Personal Information</h3>
                </div>
                <div class="wr3-review-rows">
                    <div class="wr3-review-row"><span>Full Name</span><strong>Not provided</strong></div>
                    <div class="wr3-review-row"><span>Date of Birth</span><strong>Not provided</strong></div>
                    <div class="wr3-review-row"><span>Complete Address</span><strong>Not provided</strong></div>
                    <div class="wr3-review-row"><span>Mobile Number</span><strong>Not provided</strong></div>
                    <div class="wr3-review-row"><span>Email Address</span><strong>Not provided</strong></div>
                </div>
            </div>

            <div class="ps-card wr3-review-card">
                <div class="wr3-review-card-header">
                    <span class="wr3-review-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 9h18"/><path d="M8 3v4"/><path d="M16 3v4"/></svg></span>
                    <h3>2. Additional Information</h3>
                </div>
                <div class="wr3-review-rows">
                    <div class="wr3-review-row"><span>Parish</span><strong>Not provided</strong></div>
                    <div class="wr3-review-row"><span>Name of Parent/Guardian</span><strong>Not provided</strong></div>
                    <div class="wr3-review-row"><span>Relationship to Applicant</span><strong>Not provided</strong></div>
                    <div class="wr3-review-row"><span>Name of School</span><strong>Not provided</strong></div>
                    <div class="wr3-review-row"><span>Grade / Year Level</span><strong>Not provided</strong></div>
                    <div class="wr3-review-row"><span>Emergency Contact Number</span><strong>Not provided</strong></div>
                    <div class="wr3-review-row"><span>Received First Communion?</span><strong>Not provided</strong></div>
                    <div class="wr3-review-row"><span>Name of Sponsor</span><strong>Not provided</strong></div>
                    <div class="wr3-review-row"><span>Sponsor Contact Number</span><strong>Not provided</strong></div>
                    <div class="wr3-review-row wr3-review-row-wrap"><span>Notes / Message to Parish</span><strong>Not provided</strong></div>
                </div>
            </div>

        </div>

        <div class="ps-card wr3-review-card wr3-review-card-full">
            <div class="wr3-review-card-header">
                <span class="wr3-review-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17a4.5 4.5 0 0 1-1-8.9A5.5 5.5 0 0 1 16.5 8H17a4 4 0 0 1 1 7.9"/><path d="M12 12v7"/><path d="M9 15l3-3 3 3"/></svg></span>
                <h3>3. Uploaded Document</h3>
            </div>
            <ul class="wr3-doc-list" data-uploaded-docs="confirmation"></ul>
        </div>

        <div class="ps-info-banner is-tip">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5.5"/><path d="M12 7.8h.01"/></svg>
            <span>Your application is still subject to parish review and availability. You will receive a confirmation once your request has been approved.</span>
        </div>

        <div class="service-confirm"><label><input type="checkbox" id="confirmAccurate" name="confirmAccurate" data-confirm-toggle required><span><strong>I confirm</strong> that the information provided is true and accurate.</span></label></div>

        <div class="wr-actions">
            <a href="confirmation-request-step3.html" class="ps-btn wr-cancel"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5"/><path d="M11 18l-6-6 6-6"/></svg> Back</a>
            <button type="submit" class="ps-btn ps-btn-primary wr-submit" data-confirm-submit disabled>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12 20 4l-6 16-3-7-7-3z"/></svg> Submit Confirmation Application
            </button>
        </div>

        

    </form>

</main>
</div>
<script src="assets/js/frontend.js"></script>
<script src="assets/js/main.js"></script>
<script src="assets/js/session-user.js"></script>
<script src="assets/js/responsive.js?v=1"></script>
<script src="assets/js/request-uploads.js"></script></body>
</html>
