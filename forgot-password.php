<?php
/**
 * forgot-password.php
 * ---------------------------------------------------------------------
 * Step 1 of 3 of the password reset:
 *   forgot-password.html -> verify-reset-otp.html -> reset-password.html
 * Emails a 6-digit code kept in the separate reset_otp_* columns, so it
 * never touches a pending registration's otp_hash / otp_expires_at.
 * An unknown email is reported directly ("No account found..."), the
 * same style registration already uses for a duplicate email.
 * ---------------------------------------------------------------------
 */
session_start();
require_once 'config.php';
require_once 'includes/otp-mailer.php';

function backToForgot($message, $email = '') {
    $_SESSION['forgot_error'] = $message;
    $_SESSION['forgot_email'] = $email;
    header('Location: forgot-password.html', true, 303);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: forgot-password.html');
    exit;
}

$email = trim($_POST['email'] ?? '');
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    backToForgot('Please enter a valid email address.', $email);
}

$stmt = $conn->prepare('SELECT id, firstname, email, status, reset_otp_expires_at FROM users WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    backToForgot('No account found with that email address.', $email);
}
if ($user['status'] !== 'Active') {
    backToForgot('This account has been suspended. Please contact the parish office.', $email);
}

unset($_SESSION['reset_verified_user_id'], $_SESSION['reset_verified_until']);
$_SESSION['pending_reset_user_id'] = (int) $user['id'];
$_SESSION['pending_reset_email'] = $user['email'];

// Asked again within a minute (double click, back button): don't send
// another email, just continue to the code screen.
if (otpSentRecently($user['reset_otp_expires_at'])) {
    $_SESSION['reset_otp_success'] = 'We sent a code to this email a moment ago. Please check your inbox (and spam folder).';
    header('Location: verify-reset-otp.html', true, 303);
    exit;
}

$code = random_int(100000, 999999);
$codeHash = password_hash((string) $code, PASSWORD_DEFAULT);
$expiresAt = date('Y-m-d H:i:s', time() + PS_OTP_TTL_SECONDS);

$stmt = $conn->prepare('UPDATE users SET reset_otp_hash = ?, reset_otp_expires_at = ?, reset_otp_attempts = 0 WHERE id = ?');
$stmt->bind_param('ssi', $codeHash, $expiresAt, $user['id']);
$stmt->execute();
$stmt->close();

if (!sendPasswordResetEmail($user['email'], $user['firstname'], (string) $code)) {
    $_SESSION['reset_otp_error'] = "We couldn't send the email. Please use Resend Code below.";
}
header('Location: verify-reset-otp.html', true, 303);
exit;
