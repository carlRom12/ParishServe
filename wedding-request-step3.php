<?php
require_once __DIR__ . '/includes/request-forms.php';
ps_handle_request_form('wedding');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Wedding Request | ParishServe</title>

<link rel="stylesheet" href="assets/css/style.css?v=confirmation-6">

<link rel="stylesheet" href="assets/css/confirmation-layout.css?v=12">
<link rel="stylesheet" href="assets/css/funeral-layout.css?v=1">
<link rel="stylesheet" href="assets/css/responsive.css?v=3">
<link rel="stylesheet" href="assets/css/counseling.css?v=2">
<link rel="stylesheet" href="assets/css/mass-intention.css?v=1">
<link rel="stylesheet" href="assets/css/service-cards.css?v=1">

<link rel="stylesheet" href="assets/css/counseling-about.css?v=1">
<link rel="stylesheet" href="assets/css/wedding-about.css?v=1">
<link rel="stylesheet" href="assets/css/service-tabs.css?v=1">
<link rel="stylesheet" href="assets/css/service-back.css?v=2">
<link rel="stylesheet" href="assets/css/wedding-request.css">
<link rel="stylesheet" href="assets/css/wedding-request-step3.css">
<link rel="stylesheet" href="assets/css/service-review.css?v=2"><link rel="stylesheet" href="assets/css/sidebar-refined.css?v=2">
<link rel="stylesheet" href="assets/css/sidebar-hover.css?v=3">
</head>
<body class="funeral-page mass-intention-page wedding-about-page ps-hover-sidebar">
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
                        <a class="ps-nav-link active" aria-current="page" href="wedding.html">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="8" cy="14" r="6"/><circle cx="16" cy="14" r="6"/><path d="m6 5 2-2 2 2-2 3-2-3Zm8 0 2-2 2 2-2 3-2-3Z"/></svg>                            <span>Wedding</span>
                        </a>
                    </li>
                                    <li>
                        <a class="ps-nav-link" href="baptism.html">
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

    <div class="conf-topline service-topline"><a class="service-back" href="wedding.html"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M19 12H5m6-6-6 6 6 6"/></svg>Back to Wedding</a>
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

<section class="co-hero" aria-labelledby="wedding-title">
<img src="assets/images/wedding-hero.png" alt="Gold wedding rings on ivory lace with flowers in a church">
<div class="co-hero-copy"><span class="co-eyebrow">Sacraments</span><h1 id="wedding-title">Wedding Request</h1><p>Prepare and review your request for the Sacrament of Marriage.</p><blockquote>&ldquo;What God has joined together, let no one separate.&rdquo;<cite>&mdash; Mark 10:9</cite></blockquote></div></section>
<nav class="ca-tabs" id="wedding-tabs" aria-label="Wedding sections"><a href="wedding-about.html"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v16M12 5C9 3 5 3 2 5v16c3-2 7-2 10 0 3-2 7-2 10 0V5c-3-2-7-2-10 0Z"/></svg>About Wedding</a><a href="wedding-guidelines.html"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H5v20h14V7l-5-5Zm0 0v6h5M8 12h8M8 16h8"/></svg>Requirements</a><a href="wedding-schedule.html"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 2v6m8-6v6"/></svg>Schedule</a><a href="wedding-request.html" aria-current="page"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 3 6 6-12 12H3v-6L15 3Zm-2 2 6 6"/></svg>Request</a></nav>
<div class="ps-card wr-stepbar">
                    <div class="wr-step is-done">
            <span class="wr-step-num">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>                            </span>
            <span class="wr-step-text">
                <strong>The Couple</strong>
                <small>Tell us about you</small>
            </span>
        </div>
                    <svg class="wr-step-sep" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>                            <div class="wr-step is-done">
            <span class="wr-step-num">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>                            </span>
            <span class="wr-step-text">
                <strong>Requirements</strong>
                <small>Submit documents</small>
            </span>
        </div>
                    <svg class="wr-step-sep" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>                            <div class="wr-step is-current">
            <span class="wr-step-num">
                                    3                            </span>
            <span class="wr-step-text">
                <strong>Review &amp; Send</strong>
                <small>Review and submit</small>
            </span>
        </div>
            </div>

    <section class="ps-card"><h2>Step 3 of 3: Review &amp; Submit</h2><p>Please review your wedding request before submitting.</p></section><div id="weddingReview" class="wr3-review-grid" style="margin-top:20px"></div><section class="ps-card wr3-review-card" style="margin-top:20px"><h3>Uploaded Documents</h3><ul class="wr3-doc-list" id="weddingReviewFiles" data-uploaded-docs="wedding"></ul></section><form id="weddingReviewForm" action="wedding-request-step3.php" method="post" data-wizard-step-form data-draft-key="parishserve-draft-wedding" novalidate><?php ps_request_form_fields(); ?><div class="service-confirm"><label><input type="checkbox" name="confirmTruthful" required> <strong>I confirm</strong> that the information provided is true and accurate.</label></div><p id="weddingSubmitStatus" role="status"></p><div class="wr-actions"><a class="ps-btn wr-cancel" href="wedding-request-step2.html">Back</a><button class="ps-btn ps-btn-primary wr-submit" type="submit">Submit Wedding Request</button></div></form></main></div><script src="assets/js/session-user.js"></script>
<script src="assets/js/responsive.js?v=1"></script><script src="assets/js/frontend.js"></script>
<script src="assets/js/main.js"></script>
<script src="assets/js/request-uploads.js"></script>
<script src="assets/js/wedding-review.js?v=2"></script></body></html>