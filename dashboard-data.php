<?php
/**
 * dashboard-data.php
 * ---------------------------------------------------------------------
 * JSON for dashboard.html (a static page, so it can't query anything
 * itself -- assets/js/dashboard-revamp.js fetches this). For a signed-in
 * user: first name, role, stat counts and the 3 newest requests, linked
 * to them by users.mobile_number = <request table>.contact_number (the
 * request tables have no users.id column). Logged-out visitors get
 * {loggedIn: false} and the page keeps its "Log in to see your requests"
 * prompt.
 * ---------------------------------------------------------------------
 */
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
require_once 'config.php';
require_once 'includes/icons.php';
require_once 'includes/request-types.php';

$user = null;
if (!empty($_SESSION['user_id'])) {
    $userId = (int) $_SESSION['user_id'];
    $stmt = $conn->prepare('SELECT firstname, role, mobile_number, status FROM users WHERE id = ?');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
if (!$user || $user['status'] !== 'Active') {
    echo json_encode(['loggedIn' => false]);
    exit;
}

$counts = ps_count_requests_by_status($conn, $user['mobile_number']);
$stats = [];
foreach (ps_dashboard_stats($counts) as $stat) {
    $stats[$stat['key']] = $stat['count'];
}

$requests = [];
foreach (ps_fetch_requests($conn, ['contact' => $user['mobile_number'], 'limit' => 3]) as $r) {
    $type = PS_REQUEST_TYPES[$r['type']];
    ob_start();
    ps_icon($type['icon']);
    $icon = ob_get_clean();

    $schedule = '';
    if ($r['status'] === 'scheduled' && $r['event_date']) {
        $schedule = date('M j, Y', strtotime($r['event_date']))
            . ($r['event_time'] ? ' · ' . date('g:i A', strtotime($r['event_time'])) : '');
    }
    $requests[] = [
        'title'       => $r['type'] === 'massintention' ? $type['label'] : $type['label'] . ' Request',
        'iconSvg'     => $icon, // server-side constant markup from includes/icons.php
        'status'      => $r['status'],
        'statusLabel' => ps_status_label($r['status']),
        'submitted'   => date('M j, Y', strtotime($r['created_at'])),
        'schedule'    => $schedule,
    ];
}

echo json_encode([
    'loggedIn'  => true,
    'firstName' => $user['firstname'],
    'role'      => $user['role'],
    'isStaff'   => in_array($user['role'], ['Admin', 'Super Admin'], true),
    'stats'     => $stats,
    'requests'  => $requests,
]);
