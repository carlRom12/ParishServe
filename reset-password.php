<?php
/**
 * reset-password.php
 * ---------------------------------------------------------------------
 * Step 3 of 3 of the password reset. Only works while the session holds
 * a reset_verified_user_id from verify-reset-otp.php (10-minute window).
 * Same password rules as registration -- keep in sync with main.js and
 * login_register.php. Clears all reset state so the flow can't be
 * replayed, then sends the user to log in with the new password.
 * ---------------------------------------------------------------------
 */
session_start();
require_once 'config.php';

function backToReset($message) {
    $_SESSION['reset_error'] = $message;
    header('Location: reset-password.html', true, 303);
    exit;
}

$user_id = $_SESSION['reset_verified_user_id'] ?? null;
if (!$user_id || time() > ($_SESSION['reset_verified_until'] ?? 0)) {
    unset($_SESSION['reset_verified_user_id'], $_SESSION['reset_verified_until']);
    if ($user_id) {
        $_SESSION['forgot_error'] = 'Your password reset session expired. Please request a new code.';
    }
    header('Location: forgot-password.html', true, 303);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: reset-password.html');
    exit;
}

$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';

if (strlen($password) < 8) {
    backToReset('Password must be at least 8 characters.');
}
if ($password !== $confirmPassword) {
    backToReset('Passwords do not match.');
}

// Receiving the reset code proves they can read mail at this address, so
// the email counts as verified from here on too.
$password_hash = password_hash($password, PASSWORD_DEFAULT);
$user_id = (int) $user_id;
$stmt = $conn->prepare("UPDATE users SET password_hash = ?, reset_otp_hash = NULL, reset_otp_expires_at = NULL,
                               reset_otp_attempts = 0, email_verified = 1
                         WHERE id = ? AND status = 'Active'");
$stmt->bind_param('si', $password_hash, $user_id);
$stmt->execute();
$updated = $stmt->affected_rows === 1;
$stmt->close();

if (!$updated) {
    unset($_SESSION['reset_verified_user_id'], $_SESSION['reset_verified_until']);
    $_SESSION['forgot_error'] = 'We could not reset the password for this account. Please contact the parish office.';
    header('Location: forgot-password.html', true, 303);
    exit;
}

session_regenerate_id(true);
unset($_SESSION['reset_verified_user_id'], $_SESSION['reset_verified_until'],
      $_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name']);
$_SESSION['login_success'] = 'Your password has been reset. You can now log in with your new password.';
header('Location: login.html', true, 303);
exit;
