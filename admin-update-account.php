<?php
/**
 * admin-update-account.php
 * ---------------------------------------------------------------------
 * POST endpoint behind admin-accounts.php's Manage modal. Super Admin
 * only, CSRF-checked. Sets a Parishioner/Admin account's role and
 * Active/Suspended status. Refuses to touch Super Admin accounts
 * (including the caller's own) and only promotes verified emails to
 * Admin, so every staff account can recover its password by email.
 * Same { ok, message, row } / { ok: false, error } shape as
 * admin-update-request.php.
 * ---------------------------------------------------------------------
 */
$psGuardJson = true;
$psGuardRoles = ['Super Admin'];
require __DIR__ . '/includes/auth-guard.php';

ps_require_post_csrf();

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$role = (string) ($_POST['role'] ?? '');
$status = (string) ($_POST['status'] ?? '');

if ($id === false) {
    ps_json(422, ['ok' => false, 'error' => 'Missing or invalid account id.']);
}
if (!in_array($role, ['Parishioner', 'Admin'], true)) {
    ps_json(422, ['ok' => false, 'error' => 'Role must be Parishioner or Admin.']);
}
if (!in_array($status, ['Active', 'Suspended'], true)) {
    ps_json(422, ['ok' => false, 'error' => 'Status must be Active or Suspended.']);
}
if ($id === ps_current_user_id()) {
    ps_json(403, ['ok' => false, 'error' => "You can't change your own account here."]);
}

$stmt = $conn->prepare('SELECT firstname, lastname, role, email_verified FROM users WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$account = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$account) {
    ps_json(404, ['ok' => false, 'error' => 'That account no longer exists. Please reload the page.']);
}
if ($account['role'] === 'Super Admin') {
    ps_json(403, ['ok' => false, 'error' => "Super Admin accounts can't be changed from this page."]);
}
if ($role === 'Admin' && !(int) $account['email_verified']) {
    ps_json(422, ['ok' => false, 'error' => "This account hasn't verified its email yet, so it can't be made an Admin."]);
}

$stmt = $conn->prepare('UPDATE users SET role = ?, status = ? WHERE id = ?');
$stmt->bind_param('ssi', $role, $status, $id);
if (!$stmt->execute()) {
    error_log('admin-update-account failed: ' . $stmt->error);
    ps_json(500, ['ok' => false, 'error' => 'Could not save the change. Please try again.']);
}
$stmt->close();

$name = trim($account['firstname'] . ' ' . $account['lastname']);
ps_json(200, [
    'ok'      => true,
    'message' => "{$name} saved as {$role} ({$status}).",
    'row'     => [
        'status'      => $status,
        'statusLabel' => $status,
        'statusClass' => $status === 'Active' ? 'is-approved' : 'is-rejected',
        'type'        => strtolower($role),
        'fields'      => ['role' => $role],
        'data'        => ['role' => $role, 'status' => $status],
    ],
]);
