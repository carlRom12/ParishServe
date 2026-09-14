<?php
/**
 * auth-guard.php
 * ---------------------------------------------------------------------
 * Required at the very top of every admin-*.php page and admin endpoint,
 * before any output. On every request it:
 *   1. looks the signed-in user up again in `users`, so a role change or
 *      suspension made on admin-accounts.php applies on that person's
 *      very next click rather than their next login;
 *   2. turns away anyone without an allowed role -- pages redirect to
 *      login.html, endpoints answer 401/403 JSON.
 *
 * The calling file can set, before requiring this:
 *   $psGuardRoles  roles let in (default: Admin + Super Admin)
 *   $psGuardJson   true for fetch() endpoints (JSON errors, no redirect)
 *
 * Afterwards $conn is connected and $userFirstName / $userRole are set
 * for includes/topbar.php. Gate any future Super-Admin-only feature on
 * ps_current_role() / ps_is_super_admin() below.
 * ---------------------------------------------------------------------
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../config.php';

const PS_ADMIN_ROLES = ['Admin', 'Super Admin'];

function ps_current_user_id() {
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function ps_current_role() {
    return $_SESSION['user_role'] ?? null;
}

function ps_is_super_admin() {
    return ps_current_role() === 'Super Admin';
}

function ps_json($httpCode, array $body) {
    http_response_code($httpCode);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($body);
    exit;
}

/** Per-session token for admin POSTs; header.php puts it in <meta name="csrf-token">. */
function ps_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function ps_require_post_csrf() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        ps_json(405, ['ok' => false, 'error' => 'This action must be sent as a POST request.']);
    }
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        ps_json(403, ['ok' => false, 'error' => 'Your session has changed. Please reload the page and try again.']);
    }
}

function ps_guard_deny($httpCode, $message, $redirect = 'login.html') {
    global $psGuardJson;
    if (!empty($psGuardJson)) {
        ps_json($httpCode, ['ok' => false, 'error' => $message]);
    }
    if ($redirect === 'login.html') {
        $_SESSION['login_error'] = $message;
    }
    header('Location: ' . $redirect, true, 303);
    exit;
}

function ps_require_role(array $roles) {
    global $conn;

    $userId = ps_current_user_id();
    if (!$userId) {
        ps_guard_deny(401, 'Please log in to continue.');
    }

    $stmt = $conn->prepare('SELECT firstname, role, status, email_verified FROM users WHERE id = ?');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user || $user['status'] !== 'Active' || !(int) $user['email_verified']) {
        unset($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name']);
        ps_guard_deny(401, $user && $user['status'] !== 'Active'
            ? 'This account has been suspended. Please contact the parish office.'
            : 'Your session has ended. Please log in again.');
    }

    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_name'] = $user['firstname'];

    if (!in_array($user['role'], $roles, true)) {
        if (in_array($user['role'], PS_ADMIN_ROLES, true)) {
            // Staff, just not allowed on this particular page/action.
            ps_guard_deny(403, 'Only a Super Admin can do that.', 'admin-dashboard.php');
        }
        ps_guard_deny(403, 'That page is only available to parish staff.');
    }
}

header('Cache-Control: no-store');
ps_require_role($psGuardRoles ?? PS_ADMIN_ROLES);

$userFirstName = $_SESSION['user_name'];
$userRole = $_SESSION['user_role'];
