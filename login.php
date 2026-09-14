<?php
/**
 * login.php
 * ---------------------------------------------------------------------
 * Handles login.html's form. Looks the account up by email, checks the
 * password with password_verify(), and requires an Active, verified
 * account. On success the session gets user_id / user_role / user_name
 * (read by includes/auth-guard.php); staff go to the admin portal,
 * parishioners to dashboard.html. Errors go back to login.html through
 * auth-state.php -- same flash pattern as registration.
 * ---------------------------------------------------------------------
 */
// HttpOnly + SameSite=Lax on the session cookie (sent again when the ID
// is regenerated below).
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();
require_once 'config.php';

function backToLogin($message, $email = '') {
    $_SESSION['login_error'] = $message;
    $_SESSION['login_email'] = $email;
    header('Location: login.html', true, 303);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.html');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    backToLogin('Please enter a valid email address.', $email);
}
if ($password === '') {
    backToLogin('Please enter your password.', $email);
}

$stmt = $conn->prepare('SELECT id, firstname, email, password_hash, role, email_verified, status FROM users WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user || !password_verify($password, $user['password_hash'])) {
    backToLogin('Incorrect email or password.', $email);
}
if ($user['status'] !== 'Active') {
    backToLogin('This account has been suspended. Please contact the parish office.', $email);
}
if (!(int) $user['email_verified']) {
    // The password was right, so let them finish verifying instead of
    // hitting a dead end -- verify-otp.php's Resend issues a fresh code.
    $_SESSION['pending_user_id'] = (int) $user['id'];
    $_SESSION['pending_email'] = $user['email'];
    $_SESSION['otp_error'] = 'Please verify your email before logging in. Use Resend Code if your code has expired.';
    header('Location: verify-otp.html', true, 303);
    exit;
}

session_regenerate_id(true);
unset($_SESSION['pending_user_id'], $_SESSION['pending_email'], $_SESSION['login_error'], $_SESSION['login_email']);
$_SESSION['user_id'] = (int) $user['id'];
$_SESSION['user_role'] = $user['role'];
$_SESSION['user_name'] = $user['firstname'];

$isStaff = in_array($user['role'], ['Admin', 'Super Admin'], true);
header('Location: ' . ($isStaff ? 'admin-dashboard.php' : 'dashboard.html'), true, 303);
exit;
