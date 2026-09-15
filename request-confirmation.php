<?php

session_start();
require_once __DIR__ . '/includes/request-forms.php';

header('Cache-Control: no-store');
$reference = is_string($_GET['ref'] ?? null) ? $_GET['ref'] : '';
$receipt = $_SESSION['ps_confirmations'][$reference] ?? null;
$form = $receipt ? PS_REQUEST_FORMS[$receipt['flow']] : null;
if (!$receipt) {
    http_response_code(404);
}
$isDonation = $receipt && $receipt['flow'] === 'donation';
$hasDocuments = $receipt && !$isDonation && ps_upload_rules($receipt['flow']);
$contact = $receipt && $receipt['contact'] !== '' ? $receipt['contact'] : '';

$navGroups = [
    ['label' => null, 'items' => [['Dashboard', 'home', 'dashboard.html'], ['Announcements', 'megaphone', 'announcements.html'], ['Parish Calendar', 'calendar', 'calendar.html']]],
    ['label' => 'Sacraments', 'items' => [['Wedding', 'ring', 'wedding.html'], ['Baptism', 'droplet', 'baptism.html'], ['Confirmation', 'flame', 'confirmation.html'], ['Burial / Funeral', 'cross', 'funeral.html']]],
    ['label' => 'Parish Services', 'items' => [['Counseling', 'people', 'counseling.html'], ['Mass Intention', 'chalice', 'mass-intention.html'], ['Facility Reservation', 'building', 'facility-reservation.html'], ['Donate', 'heart', 'donations.html']]],
    ['label' => 'Other', 'items' => [['My Profile', 'user', 'profile.html'], ['Settings', 'gear', 'settings.html']]],
];
$activeHref = $form['nav'] ?? '';
$userName = $_SESSION['user_name'] ?? 'Guest';
$userRole = $_SESSION['user_role'] ?? 'Visitor';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $receipt ? 'Request Submitted' : 'Confirmation Not Found'; ?> · ParishServe</title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/request-confirmation.css">
<link rel="stylesheet" href="assets/css/responsive.css?v=3">
</head>
<body>
<div class="ps-shell">
<aside class="ps-sidebar">

    <div class="ps-logo">
        <div class="ps-logo-crest"><?php ps_icon('crest'); ?></div>
        <div class="ps-logo-eyebrow">Our Lady<br>of the Gate</div>
        <div class="ps-logo-name">ParishServe</div>
        <div class="ps-logo-sub">Parish Community Portal</div>
    </div>

    <nav class="ps-nav">
        <?php foreach ($navGroups as $group): ?>
            <?php if ($group['label']): ?>
                <span class="ps-nav-section"><?php echo htmlspecialchars($group['label']); ?></span>
            <?php endif; ?>
            <ul class="ps-nav-list">
                <?php foreach ($group['items'] as [$label, $icon, $href]): ?>
                    <li>
                        <a class="ps-nav-link<?php echo $href === $activeHref ? ' active' : ''; ?>" href="<?php echo htmlspecialchars($href); ?>">
                            <?php ps_icon($icon); ?> <span><?php echo htmlspecialchars($label); ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endforeach; ?>
    </nav>

    <div class="ps-sidebar-art"><?php ps_icon('church'); ?></div>

    <div class="ps-logout-wrap">
        <a href="logout.php" class="ps-logout-btn">
            <?php ps_icon('logout'); ?> <span>Log out</span>
        </a>
    </div>

</aside>
<main class="ps-main">

    <section class="ps-plain-header">
        <div class="ps-topbar">
            <button type="button" class="ps-notif-btn" aria-label="Notifications"><?php ps_icon('bell'); ?></button>
            <div class="ps-user-chip">
                <span class="ps-user-avatar"><?php echo htmlspecialchars(strtoupper(substr($userName, 0, 1))); ?></span>
                <span class="ps-user-info">
                    <strong><?php echo htmlspecialchars($userName); ?></strong>
                    <small><?php echo htmlspecialchars($userRole); ?></small>
                </span>
                <?php ps_icon('chevron-down', 'ps-user-chevron'); ?>
            </div>
        </div>
        <h1><?php echo $receipt ? 'Request Submitted' : 'Confirmation'; ?></h1>
        <?php if ($form): ?>
            <nav class="ps-breadcrumb">
                <a href="<?php echo htmlspecialchars($form['nav']); ?>"><?php echo htmlspecialchars($form['label']); ?></a>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
                <span>Confirmation</span>
            </nav>
        <?php endif; ?>
    </section>

    <?php if ($receipt): ?>
        <div class="ps-card rc-card">
            <div class="rc-icon"><?php ps_icon('check-circle'); ?></div>
            <h2><?php echo $isDonation ? 'Thank you for your donation!' : 'Thank you! Your request has been received.'; ?></h2>
            <p class="rc-lead">
                <?php if ($isDonation): ?>
                    Please keep your reference number. Our parish staff will use it to match your proof of payment with your GCash transaction.
                <?php else: ?>
                    Please keep your reference number. The parish office uses it, together with your contact number, to find your request when you follow up.
                <?php endif; ?>
            </p>

            <div class="rc-reference">
                <span class="rc-reference-label">Reference number</span>
                <strong class="rc-reference-value"><?php echo htmlspecialchars($reference); ?></strong>
                <button type="button" class="ps-btn ps-btn-outline" data-copy-text="<?php echo htmlspecialchars($reference); ?>">Copy</button>
            </div>

            <dl class="rc-summary">
                <div><dt><?php echo $isDonation ? 'Type' : 'Request'; ?></dt><dd><?php echo htmlspecialchars($form['label']); ?></dd></div>
                <div><dt>Submitted</dt><dd><?php echo htmlspecialchars($receipt['submitted_at']); ?></dd></div>
                <div><dt>Contact number</dt><dd><?php echo $contact !== '' ? htmlspecialchars($contact) : 'Not provided'; ?></dd></div>
                <?php if ($hasDocuments): ?>
                    <div><dt>Documents uploaded</dt><dd><?php echo (int) $receipt['documents']; ?></dd></div>
                <?php endif; ?>
                <div><dt>Status</dt><dd><span class="ps-status is-submitted"><?php echo htmlspecialchars(ps_status_label('submitted')); ?></span></dd></div>
            </dl>

            <div class="rc-next">
                <h3>What happens next?</h3>
                <ol>
                    <?php if ($isDonation): ?>
                        <li>Our parish staff verify your proof of payment against the GCash transaction.</li>
                        <li>Once verified, your donation is recorded for the fund you chose.</li>
                        <?php if ($contact !== ''): ?><li>If anything doesn't match, we'll contact you at <?php echo htmlspecialchars($contact); ?>.</li><?php endif; ?>
                    <?php else: ?>
                        <li>The parish office reviews your details<?php echo $hasDocuments ? ' and uploaded documents' : ''; ?>.</li>
                        <li>If anything is missing or unclear, we'll contact you at <?php echo htmlspecialchars($contact); ?>.</li>
                        <li>You'll be told once your request is approved and scheduled. Final document checks happen at the parish office.</li>
                    <?php endif; ?>
                </ol>
            </div>

            <div class="rc-actions">
                <a href="dashboard.html" class="ps-btn ps-btn-outline">Back to Dashboard</a>
                <a href="<?php echo htmlspecialchars($form['steps'][0]); ?>" class="ps-btn ps-btn-primary"><?php echo $isDonation ? 'Make Another Donation' : 'Submit Another Request'; ?></a>
            </div>
        </div>
        <span data-clear-draft="<?php echo htmlspecialchars($form['draft_key']); ?>" hidden></span>
    <?php else: ?>
        <div class="ps-card rc-card">
            <div class="rc-icon is-missing"><?php ps_icon('info'); ?></div>
            <h2>We can't show this confirmation</h2>
            <p class="rc-lead">Confirmations can only be viewed in the browser that submitted the request, for a short while afterwards. If you submitted a request, the parish office can look it up with your reference number and contact number.</p>
            <div class="rc-actions">
                <a href="dashboard.html" class="ps-btn ps-btn-primary">Back to Dashboard</a>
            </div>
        </div>
    <?php endif; ?>

</main>
</div>
<script src="assets/js/frontend.js"></script>
<script src="assets/js/main.js"></script>
<script src="assets/js/responsive.js?v=1"></script>
</body>
</html>
