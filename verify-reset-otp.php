<?php
/**
 * verify-reset-otp.php
 * ---------------------------------------------------------------------
 * Step 2 of 3 of the password reset. Same verify/resend + flash + 303
 * shape as verify-otp.php, but checks the reset_otp_* columns.
 * Against guessing and replay:
 *   - 5 wrong tries burn the code (a new one must be requested)
 *   - resends are limited to one a minute (otpSentRecently)
 *   - a correct code is cleared in the same UPDATE that accepts it, so
 *     it can never be used twice
 * A verified code opens reset-password.html for 10 minutes.
 * ---------------------------------------------------------------------
 */
session_start();
require_once 'config.php';
require_once 'includes/otp-mailer.php';

const RESET_MAX_ATTEMPTS = 5;
const RESET_WINDOW_SECONDS = 600;

if (!isset($_SESSION['pending_reset_user_id'])) {
    header('Location: forgot-password.html');
    exit;
}

$user_id = (int) $_SESSION['pending_reset_user_id'];
$email   = $_SESSION['pending_reset_email'] ?? '';
$error   = '';
$success = '';

if (isset($_POST['verify'])) {
    $enteredOtp = trim($_POST['otp'] ?? '');

    $stmt = $conn->prepare('SELECT reset_otp_hash, reset_otp_expires_at, reset_otp_attempts FROM users WHERE id = ?');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!preg_match('/^\d{6}$/', $enteredOtp)) {
        $error = 'Please enter the 6-digit code.';
    } elseif (!$user) {
        $error = 'Account not found. Please start over.';
    } elseif (empty($user['reset_otp_hash'])) {
        $error = 'This code is no longer valid. Please request a new one.';
    } elseif (strtotime($user['reset_otp_expires_at']) < time()) {
        $error = 'This code has expired. Please request a new one.';
    } elseif ((int) $user['reset_otp_attempts'] >= RESET_MAX_ATTEMPTS) {
        $error = 'Too many incorrect attempts. Please request a new code.';
    } elseif (!password_verify($enteredOtp, $user['reset_otp_hash'])) {
        // Count atomically, then burn the code once the limit is reached.
        $stmt = $conn->prepare('UPDATE users SET reset_otp_attempts = reset_otp_attempts + 1 WHERE id = ?');
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare('UPDATE users SET reset_otp_hash = NULL WHERE id = ? AND reset_otp_attempts >= ?');
        $maxAttempts = RESET_MAX_ATTEMPTS;
        $stmt->bind_param('ii', $user_id, $maxAttempts);
        $stmt->execute();
        $burned = $stmt->affected_rows > 0;
        $stmt->close();

        $remaining = RESET_MAX_ATTEMPTS - ((int) $user['reset_otp_attempts'] + 1);
        $error = $burned || $remaining < 1
            ? 'Too many incorrect attempts. Please request a new code.'
            : 'Incorrect code. You have ' . $remaining . ' ' . ($remaining === 1 ? 'try' : 'tries') . ' left.';
    } else {
        // Accept and consume in one step: only succeeds if this exact code
        // is still the stored one (a parallel request can't reuse it).
        $stmt = $conn->prepare('UPDATE users SET reset_otp_hash = NULL, reset_otp_expires_at = NULL, reset_otp_attempts = 0 WHERE id = ? AND reset_otp_hash = ?');
        $stmt->bind_param('is', $user_id, $user['reset_otp_hash']);
        $stmt->execute();
        $consumed = $stmt->affected_rows === 1;
        $stmt->close();

        if (!$consumed) {
            $error = 'This code is no longer valid. Please request a new one.';
        } else {
            session_regenerate_id(true);
            unset($_SESSION['pending_reset_user_id'], $_SESSION['pending_reset_email']);
            $_SESSION['reset_verified_user_id'] = $user_id;
            $_SESSION['reset_verified_until'] = time() + RESET_WINDOW_SECONDS;
            header('Location: reset-password.html', true, 303);
            exit;
        }
    }
}

if (isset($_POST['resend'])) {
    $stmt = $conn->prepare('SELECT firstname, email, status, reset_otp_expires_at FROM users WHERE id = ?');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user || $user['status'] !== 'Active') {
        $error = 'This account can no longer reset its password here. Please contact the parish office.';
    } elseif (otpSentRecently($user['reset_otp_expires_at'])) {
        $error = 'Please wait a minute before requesting another code.';
    } else {
        $new_otp        = random_int(100000, 999999);
        $new_otp_hash   = password_hash((string) $new_otp, PASSWORD_DEFAULT);
        $new_expires_at = date('Y-m-d H:i:s', time() + PS_OTP_TTL_SECONDS);

        $stmt = $conn->prepare('UPDATE users SET reset_otp_hash = ?, reset_otp_expires_at = ?, reset_otp_attempts = 0 WHERE id = ?');
        $stmt->bind_param('ssi', $new_otp_hash, $new_expires_at, $user_id);
        $stmt->execute();
        $stmt->close();

        if (sendPasswordResetEmail($user['email'], $user['firstname'], (string) $new_otp)) {
            $success = 'A new code has been sent to your email.';
        } else {
            $error = 'Could not resend the code. Please try again in a moment.';
        }
    }
}

$_SESSION['reset_otp_error'] = $error;
$_SESSION['reset_otp_success'] = $success;
header('Location: verify-reset-otp.html', true, 303);
exit;
