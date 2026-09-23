<?php
require_once __DIR__ . '/includes/request-forms.php';
require_once __DIR__ . '/includes/mass-intention-history.php';
ps_handle_request_form('massintention');

// Like donation-request.php, the page opens on the signed-in account's own
// history; "Request a Mass Intention" opens the 3-step form in #massIntentionModal
// (mass-intention-modal.js). Guests can still request, but have no history.
$historyContact = ps_mass_intention_account_contact($conn);
$historyBefore = filter_input(INPUT_GET, 'before', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
// The search bar and status filter: Under Review until another status (or "all") is chosen.
$historyStatus = is_string($_GET['status'] ?? null) ? $_GET['status'] : 'under_review';
if ($historyStatus !== 'all' && !in_array($historyStatus, PS_STATUS_OPTIONS, true)) {
    $historyStatus = 'under_review';
}
$historySearch = is_string($_GET['q'] ?? null) ? mb_substr(trim($_GET['q']), 0, 50) : '';
$historyFiltered = $historyStatus !== 'all' || $historySearch !== '';
$history = $historyContact
    ? ps_mass_intention_history($conn, $historyContact, $historyBefore, $historyStatus === 'all' ? '' : $historyStatus, $historySearch)
    : ['intentions' => [], 'nextCursor' => null];
/** A history page link that keeps the current search and filter. */
function ps_mi_history_url(array $params) {
    global $historyStatus, $historySearch;
    $query = http_build_query(array_filter(['status' => $historyStatus, 'q' => $historySearch] + $params, fn($value) => $value !== '' && $value !== null));
    return 'mass-intention-request.php?' . $query . '#mass-intention-history';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Request a Mass Intention | ParishServe</title>

<link rel="stylesheet" href="assets/css/style.css?v=confirmation-6">

<link rel="stylesheet" href="assets/css/wedding-request.css">
<link rel="stylesheet" href="assets/css/wedding-request-step3.css">
<link rel="stylesheet" href="assets/css/confirmation-layout.css?v=12">
<link rel="stylesheet" href="assets/css/funeral-layout.css?v=1">
<link rel="stylesheet" href="assets/css/responsive.css?v=3">
<link rel="stylesheet" href="assets/css/counseling.css?v=2">
<link rel="stylesheet" href="assets/css/counseling-about.css?v=1">
<link rel="stylesheet" href="assets/css/mass-intention.css?v=1">
<link rel="stylesheet" href="assets/css/mass-about.css?v=1">
<link rel="stylesheet" href="assets/css/service-tabs.css?v=1">
<link rel="stylesheet" href="assets/css/mass-intention-request.css?v=6">
<link rel="stylesheet" href="assets/css/service-back.css?v=2">
<link rel="stylesheet" href="assets/css/service-review.css?v=2">
<link rel="stylesheet" href="assets/css/sidebar-refined.css?v=2">
<link rel="stylesheet" href="assets/css/sidebar-hover.css?v=3">
<link rel="stylesheet" href="assets/css/donation-history.css?v=10">
</head>
<body class="funeral-page mass-intention-page mass-intention-request-page ps-hover-sidebar">
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
                        <a class="ps-nav-link active"
                           href="mass-intention.html">
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

    <div class="conf-topline service-topline">
<a class="ma-back service-back" href="mass-intention.html"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5m6-6-6 6 6 6"/></svg>Back to Mass Intention</a>
        
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

<section class="co-hero" aria-labelledby="mass-request-title"><img src="assets/images/mass-intention-hero.png" alt="A golden chalice, open missal, and lit candle on a church altar"><div class="co-hero-copy"><span class="co-eyebrow">Mass Intention</span><h1 id="mass-request-title">Request a Mass Intention</h1><p>Offer prayers of thanksgiving, remembrance, and special intentions through the Holy Mass.</p><blockquote>Join your prayers with the celebration of the Holy Mass.</blockquote></div></section>

<nav class="ca-tabs" aria-label="Mass Intention sections"><a href="mass-intention-about.html"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v16M12 5C9 3 5 3 2 5v16c3-2 7-2 10 0 3-2 7-2 10 0V5c-3-2-7-2-10 0Z"/></svg>About Mass Intentions</a><a href="mass-intention-guidelines.html"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H5v20h14V7l-5-5Zm0 0v6h5M8 12h8M8 16h8"/></svg>Guidelines</a><a href="mass-intention-types.html"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3h9l9 9-9 9-9-9V3Z"/><circle cx="8" cy="8" r="1"/></svg>Types of Intentions</a><a href="mass-intention-request.php" aria-current="page"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 3 6 6-12 12H3v-6L15 3Zm-2 2 6 6"/></svg>Request</a></nav>

<section class="ps-card dh-history dh-table-card" id="mass-intention-history" aria-labelledby="mass-intention-history-title">
    <div class="dh-head">
        <div>
            <h2 id="mass-intention-history-title">My Mass Intention History</h2>
            <p>Mass Intentions you requested while signed in, and where each one is in the parish review.</p>
        </div>
        <div class="dh-actions">
<?php if ($historyContact): ?>
            <form class="dh-filters" action="mass-intention-request.php#mass-intention-history" method="get" role="search" data-no-draft>
                <span class="ps-search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                    <input type="search" name="q" value="<?php echo htmlspecialchars($historySearch); ?>" maxlength="50" placeholder="Search your intentions" title="Search by reference number, intention type or who it is offered for" aria-label="Search your Mass Intentions">
                </span>
                <span class="ps-select">
                    <select name="status" aria-label="Filter by status" data-auto-submit>
                        <option value="all"<?php echo $historyStatus === 'all' ? ' selected' : ''; ?>>All statuses</option>
<?php foreach (PS_STATUS_OPTIONS as $s): ?>
                        <option value="<?php echo $s; ?>"<?php echo $historyStatus === $s ? ' selected' : ''; ?>><?php echo htmlspecialchars(ps_status_label($s)); ?></option>
<?php endforeach; ?>
                    </select>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
                </span>
            </form>
<?php endif; ?>
            <button type="button" class="ps-btn ps-btn-primary" data-mi-open>Request a Mass Intention</button>
        </div>
    </div>
<?php if (!$historyContact): ?>
    <div class="dh-empty">
        <p>Sign in to see the Mass Intentions requested through your account. You can still request one without signing in.</p>
        <a class="ps-btn ps-btn-outline" href="login.html">Sign in</a>
    </div>
<?php elseif (!$history['intentions']): ?>
    <div class="dh-empty">
        <p><?php
            if ($historyBefore) {
                echo 'There are no older Mass Intentions.';
            } elseif ($historySearch !== '') {
                echo 'No Mass Intentions match &ldquo;' . htmlspecialchars($historySearch) . '&rdquo;' . ($historyStatus !== 'all' ? ' among those ' . htmlspecialchars(strtolower(ps_status_label($historyStatus))) : '') . '.';
            } elseif ($historyStatus !== 'all') {
                echo 'You have no Mass Intentions that are ' . htmlspecialchars(strtolower(ps_status_label($historyStatus))) . '.';
            } else {
                echo 'No Mass Intentions are linked to your account yet. Choose Request a Mass Intention to send your first one.';
            }
        ?></p>
<?php if ($historyFiltered): ?>
        <a class="ps-btn ps-btn-outline" href="mass-intention-request.php?status=all#mass-intention-history">Show all Mass Intentions</a>
<?php endif; ?>
    </div>
<?php else: ?>
    <div class="dh-table-wrap">
        <table class="dh-table">
            <caption>Your Mass Intentions, newest first</caption>
            <thead>
                <tr>
                    <th scope="col">Reference Number</th>
                    <th scope="col">Date Requested</th>
                    <th scope="col">Intention Type</th>
                    <th scope="col">Offered For</th>
                    <th scope="col">Mass Schedule</th>
                    <th scope="col">Status</th>
                </tr>
            </thead>
            <tbody>
<?php foreach ($history['intentions'] as $row):
    $schedule = $row['mass_date']
        ? date('M j, Y', strtotime($row['mass_date'])) . ($row['mass_time'] ? ' &middot; ' . date('g:i A', strtotime($row['mass_time'])) : '')
        : '';
?>
                <tr>
                    <td class="dh-number"><?php echo htmlspecialchars($row['reference_no']); ?></td>
                    <td><?php echo htmlspecialchars(date('M j, Y', strtotime($row['created_at']))); ?></td>
                    <td><?php echo htmlspecialchars((string) $row['intention_type']); ?></td>
                    <td><?php echo (string) $row['intention_for'] !== '' ? htmlspecialchars($row['intention_for']) : '<span class="dh-muted">&mdash;</span>'; ?></td>
                    <td><?php echo $schedule !== '' ? $schedule : '<span class="dh-muted">Not scheduled yet</span>'; ?></td>
                    <td><span class="dh-status is-<?php echo htmlspecialchars($row['status']); ?>"><?php echo htmlspecialchars(ps_status_label($row['status'])); ?></span></td>
                </tr>
<?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php if ($historyBefore || $history['nextCursor']): ?>
    <nav class="dh-pager" aria-label="Mass Intention history pages">
<?php if ($historyBefore): ?>
        <a class="ps-btn ps-btn-outline" href="<?php echo htmlspecialchars(ps_mi_history_url([])); ?>">&larr; Newest intentions</a>
<?php endif; ?>
<?php if ($history['nextCursor']): ?>
        <a class="ps-btn ps-btn-outline" href="<?php echo htmlspecialchars(ps_mi_history_url(['before' => (int) $history['nextCursor']])); ?>">Older intentions &rarr;</a>
<?php endif; ?>
    </nav>
<?php endif; ?>
    <p class="dh-footnote">Mass Intentions are matched to your account by the mobile number on your profile. Contact the parish office if an intention is missing.</p>
</section>

<dialog class="dn-modal" id="massIntentionModal" aria-labelledby="massIntentionModalTitle">
<div class="dn-modal-head">
    <h2 id="massIntentionModalTitle">Request a Mass Intention</h2>
    <button type="button" class="dn-modal-close" aria-label="Close" data-mi-cancel>&times;</button>
</div>
<div class="dn-modal-body">

    <div class="ps-card wr-stepbar">
                    <div class="wr-step is-current" data-step-item="0">
            <span class="wr-step-num" data-step-num>1</span>
            <span class="wr-step-text">
                <strong>Intent Details</strong>
                <small>Provide your intention information</small>
            </span>
        </div>
                    <svg class="wr-step-sep" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>                            <div class="wr-step" data-step-item="1">
            <span class="wr-step-num" data-step-num>2</span>
            <span class="wr-step-text">
                <strong>Schedule</strong>
                <small>Choose preferred Mass schedule</small>
            </span>
        </div>
                    <svg class="wr-step-sep" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>                            <div class="wr-step" data-step-item="2">
            <span class="wr-step-num" data-step-num>3</span>
            <span class="wr-step-text">
                <strong>Review &amp; Submit</strong>
                <small>Review and submit</small>
            </span>
        </div>
            </div>

    <div class="ca-layout">

        <form id="massIntentionRequest" class="ps-card wr-form" action="mass-intention-request.php" method="post" data-multi-step novalidate><?php ps_request_form_fields(); ?>

        <div class="wr-form-header">
            <div>
                <h2 id="mrStepHeading" tabindex="-1">Step 1 of 3: Intent Details</h2>
                <p class="wr-form-sub" id="mrStepSub">Please provide the details of the Mass Intention you would like to request.</p>
            </div>
            <span class="wr-offering-badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 4h10"/><path d="M7 4c0 4.5 2 7 5 7s5-2.5 5-7"/><path d="M12 11v6"/><path d="M8 20h8"/><path d="M9.5 17h5l-.5 3h-4z"/></svg> Mass Intention offering: <strong data-offering-total aria-live="polite">&#8369;100.00</strong></span>
        </div>

        <section data-step="0">
        <div class="wr-field-group">
            <span class="ps-field-label">Intention Type</span>
            <div class="wr-intent-options" data-radio-cards>
                                    <label class="wr-intent-card" data-radio-card>
                        <input type="radio" name="intentionType" value="Thanksgiving Mass" required>
                        <span class="wr-intent-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20s-7-4.4-9.5-9C1 8 2.5 4.5 6 4.5c2 0 3.5 1.2 4 2.5.5-1.3 2-2.5 4-2.5 3.5 0 5 3.5 3.5 6.5C19 15.6 12 20 12 20z"/></svg></span>
                        <strong>Thanksgiving Mass</strong>
                    </label>
                                    <label class="wr-intent-card" data-radio-card>
                        <input type="radio" name="intentionType" value="Special Intention" required>
                        <span class="wr-intent-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3.5l2.6 5.6 6.1.7-4.5 4.2 1.2 6-5.4-3-5.4 3 1.2-6-4.5-4.2 6.1-.7z"/></svg></span>
                        <strong>Special Intention</strong>
                    </label>
                                    <label class="wr-intent-card" data-radio-card>
                        <input type="radio" name="intentionType" value="Petition Mass" required>
                        <span class="wr-intent-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="8.5" cy="9" r="3"/><circle cx="16" cy="10" r="2.5"/><path d="M3 20c0-3.3 2.5-6 5.5-6s5.5 2.7 5.5 6"/><path d="M14 15.2c2.4.3 4 2.3 4 4.8"/></svg></span>
                        <strong>Petition Mass</strong>
                    </label>
                                    <label class="wr-intent-card" data-radio-card>
                        <input type="radio" name="intentionType" value="All Souls" required>
                        <span class="wr-intent-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v18"/><path d="M7 8h10"/></svg></span>
                        <strong>All Souls</strong>
                    </label>
                                    <label class="wr-intent-card" data-radio-card>
                        <input type="radio" name="intentionType" value="For the Souls of" required>
                        <span class="wr-intent-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v18"/><path d="M7 8h10"/></svg></span>
                        <strong>For the Souls of</strong>
                    </label>
                            </div>
        </div>

        <div class="ps-form-row-2" id="mrSubjectFields"><div class="ps-field"><label for="intentionSubject">Name of person / family / intention subject <span class="wr-required">*</span></label>
                <input maxlength="150" type="text" id="intentionSubject" name="intentionSubject" placeholder="Enter name or intention subject" required>
            </div>
            <div class="ps-field">
                <label for="occasion">Occasion or purpose <span class="wr-optional">(optional)</span></label>
                <input maxlength="150" type="text" id="occasion" name="occasion" placeholder="e.g., Birthday, Anniversary, Get well soon">
            </div>
        </div>

        <fieldset id="mrSoulFields" hidden class="mr-soul-fields"><legend>For the souls of</legend><p>Enter one deceased person's name per field. Maximum: 2 souls per request. Offering: &#8369;100 per soul.</p><div class="ps-form-row-2"><div class="ps-field"><label for="soulName1">First deceased person's full name <span class="wr-required">*</span></label><input type="text" id="soulName1" name="soulName1" maxlength="70" placeholder="e.g., Juan Dela Cruz" disabled></div><div class="ps-field"><label for="soulName2">Second deceased person's full name (optional)</label><input type="text" id="soulName2" name="soulName2" maxlength="70" placeholder="Leave blank for one soul" disabled></div></div></fieldset><p id="mrTypeHint" class="ps-form-hint" aria-live="polite"></p>
<div class="ps-field wr-notes-field">
            <label for="intentionDetails">Intention details / prayer request <span class="wr-required">*</span></label>
            <textarea id="intentionDetails" name="intentionDetails" rows="3" maxlength="500" placeholder="Please share the intention or prayer request you would like our parish to pray for." required></textarea>
            <small class="ps-form-hint" id="intentionDetailsCount">0 / 500</small>
        </div>

        <div class="ps-form-row-3">
            <div class="ps-field">
                <label for="requesterName">Requester's full name <span class="wr-required">*</span></label>
                <input type="text" id="requesterName" name="requesterName" placeholder="Enter your full name" value="<?php echo ps_account_full_name_attr(); ?>" required>
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
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5.5"/><path d="M12 7.8h.01"/></svg>            <span>Please provide complete and respectful details to help the parish prepare your request.</span>
        </div>
        </section>

        <section data-step="1" hidden>
        <div class="ps-form-row-2 mr-schedule-fields">
            <div class="ps-field">
                <label for="preferredDate">Preferred Mass Date <span class="wr-required">*</span></label>
                <div class="ps-datepicker" data-datepicker>
                    <span class="ps-datepicker-field">
                        <input type="date" id="preferredDate" name="preferredDate" required>
                        <button type="button" class="ps-datepicker-toggle" data-datepicker-toggle aria-label="Open calendar">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 9h18"/><path d="M8 3v4"/><path d="M16 3v4"/></svg>
                        </button>
                    </span>
                    <div class="ps-datepicker-panel" data-datepicker-panel hidden></div>
                </div>
            </div>
            <div class="ps-field">
                <label for="preferredTime">Preferred Mass Time <span class="wr-required">*</span></label>
                
                    <select id="preferredTime" name="preferredTime" required>
                        <option value="" selected>Select preferred time</option>
                                                    <option value="6:00 AM">6:00 AM</option>
                                                    <option value="7:00 AM">7:00 AM</option>
                                                    <option value="8:30 AM">8:30 AM</option>
                                                    <option value="10:00 AM (Family Mass)">10:00 AM (Family Mass)</option>
                                                    <option value="12:00 PM (Noon Mass)">12:00 PM (Noon Mass)</option>
                                                    <option value="5:00 PM (Anticipated Mass — Saturday only)">5:00 PM (Anticipated Mass — Saturday only)</option>
                                                    <option value="6:00 PM">6:00 PM</option>
                                            </select>
                    
                <small class="ps-form-hint">Choose the time most convenient for you.</small>
            </div>
        </div>

        <div class="ps-info-banner">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5.5"/><path d="M12 7.8h.01"/></svg>            <span>Your preferred schedule will be reviewed by the parish office and is subject to availability.</span>
        </div>

        <div class="ps-field wr-notes-field">
            <label for="schedulingNotes">Additional scheduling notes <span class="wr-optional">(optional)</span></label>
            <textarea id="schedulingNotes" name="schedulingNotes" rows="3" maxlength="500" placeholder="Add any helpful scheduling details for the parish office."></textarea>
            <small class="ps-form-hint" id="schedulingNotesCount">0 / 500</small>
        </div>
        </section>

        <section data-step="2" hidden>
        <div class="wr3-review-grid">

            <div class="ps-card wr3-review-card">
                <div class="wr3-review-card-header">
                    <span class="wr3-review-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 4h10"/><path d="M7 4c0 4.5 2 7 5 7s5-2.5 5-7"/><path d="M12 11v6"/><path d="M8 20h8"/><path d="M9.5 17h5l-.5 3h-4z"/></svg></span>
                    <h3>1. Intent Details</h3>
                </div>
                <div class="wr3-review-rows">
                    <div class="wr3-review-row"><span>Intention Type</span><strong data-review="intentionType">Not provided</strong></div>
                    <div class="wr3-review-row"><span data-review-subject-label>Intention subject</span><strong data-review="intentionSubject">Not provided</strong></div>
                    <div class="wr3-review-row"><span>Offering</span><strong data-review="offeringTotal"></strong></div><div class="wr3-review-row"><span>Occasion or purpose</span><strong data-review="occasion">Not provided</strong></div>
                    <div class="wr3-review-row wr3-review-row-wrap"><span>Intention details / prayer request</span><strong data-review="intentionDetails">Not provided</strong></div>
                </div>
            </div>

            <div class="ps-card wr3-review-card">
                <div class="wr3-review-card-header">
                    <span class="wr3-review-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c0-3.9 3.1-7 7-7s7 3.1 7 7"/></svg></span>
                    <h3>2. Requester Information</h3>
                </div>
                <div class="wr3-review-rows">
                    <div class="wr3-review-row"><span>Requester's full name</span><strong data-review="requesterName">Not provided</strong></div>
                    <div class="wr3-review-row"><span>Mobile number</span><strong data-review="mobileNumber">Not provided</strong></div>
                    <div class="wr3-review-row"><span>Email address</span><strong data-review="emailAddress">Not provided</strong></div>
                </div>
            </div>

        </div>

        <div class="ps-card wr3-review-card wr3-review-card-full">
            <div class="wr3-review-card-header">
                <span class="wr3-review-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 9h18"/><path d="M8 3v4"/><path d="M16 3v4"/></svg></span>
                <h3>3. Preferred Schedule</h3>
            </div>
            <div class="wr3-review-rows">
                <div class="wr3-review-row"><span>Preferred Mass Date</span><strong data-review="preferredDate">Not provided</strong></div>
                <div class="wr3-review-row"><span>Preferred Mass Time</span><strong data-review="preferredTime">Not provided</strong></div>
                <div class="wr3-review-row wr3-review-row-wrap"><span>Additional scheduling notes</span><strong data-review="schedulingNotes">Not provided</strong></div>
            </div>
        </div>

        <div class="ps-info-banner is-tip">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5.5"/><path d="M12 7.8h.01"/></svg>            <span>Your requested Mass schedule is still subject to parish review and availability. You will receive a confirmation once your request has been approved.</span>
        </div>

        <div class="service-confirm"><label><input type="checkbox" id="confirmRespectful" name="confirmRespectful" required><span><strong>I confirm</strong> that the information provided is true and accurate.</span></label></div>

        
        </section>

        <div class="wr-actions">
            <button type="button" class="ps-btn wr-cancel" id="mrCancel" data-mi-cancel>Cancel</button>
            <button type="button" class="ps-btn wr-cancel" id="mrBack" hidden><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5"/><path d="M11 18l-6-6 6-6"/></svg> Back</button>
            <button type="submit" class="ps-btn ps-btn-primary wr-submit" id="mrNext">Save and Continue <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg></button>
        </div>

        </form>

        <aside class="ca-side">
            <section class="co-card">
                <div class="co-card-head">
                    <span class="co-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 4h10"/><path d="M7 4c0 4.5 2 7 5 7s5-2.5 5-7"/><path d="M12 11v6"/><path d="M8 20h8"/><path d="M9.5 17h5l-.5 3h-4z"/></svg></span>
                    <div>
                        <h2>We Walk With You in Prayer</h2>
                        <p>Your intention becomes part of the parish's offering during the Holy Mass.</p>
                    </div>
                </div>
            </section>

            <blockquote class="co-card ca-scripture">
                <p>&ldquo;Pray without ceasing.&rdquo;</p>
                <cite>&mdash; 1 Thessalonians 5:17</cite>
            </blockquote>

            <section class="co-card">
                <h2>Request Steps</h2>
                <ul class="conf-apply-steps-list">
                    <li class="is-current" data-step-item="0"><span class="conf-apply-steps-num">1</span>Provide your intention details.</li>
                    <li data-step-item="1"><span class="conf-apply-steps-num">2</span>Choose your preferred schedule.</li>
                    <li data-step-item="2"><span class="conf-apply-steps-num">3</span>Review and submit your request.</li>
                    <li><span class="conf-apply-steps-num">4</span>Wait for parish confirmation.</li>
                </ul>
            </section>

            <section class="co-card">
                <div class="co-card-head">
                    <span class="co-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 13a8 8 0 0 1 16 0"/><rect x="3" y="13" width="4" height="7" rx="1.5"/><rect x="17" y="13" width="4" height="7" rx="1.5"/><path d="M20 20a4 4 0 0 1-4 3h-2"/></svg></span>
                    <div>
                        <h2>Need Help?</h2>
                        <p>For more information, you may contact our parish office.</p>
                    </div>
                </div>
                <a class="ps-btn ps-btn-outline ca-contact" href="dashboard.html#parish-contacts">Contact Parish Office</a>
            </section>
        </aside>

    </div>
</div>
</dialog>

</main>
</div>
<script src="assets/js/frontend.js"></script>
<script src="assets/js/booking-calendar.js?v=7"></script>
<script src="assets/js/mass-intention-request.js?v=4"></script>
<script src="assets/js/mass-intention-modal.js?v=1"></script>
<script src="assets/js/session-user.js"></script>
<script src="assets/js/responsive.js?v=1"></script>
</body>
</html>
