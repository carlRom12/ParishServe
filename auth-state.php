<?php
/**
 * auth-state.php
 * ---------------------------------------------------------------------
 * The auth pages are static .html, so they can't read the PHP session
 * themselves: auth.js fetches ?page=<page name> on load and shows what
 * comes back. Flash values are consumed (unset) on read. 'redirect'
 * sends the visitor away from a step they can't use right now (e.g.
 * reset-password.html without a verified code).
 * ---------------------------------------------------------------------
 */
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function takeFlash($key, $default = '') {
    $value = $_SESSION[$key] ?? $default;
    unset($_SESSION[$key]);
    return $value;
}

$page = $_GET['page'] ?? '';
$result = [];
if ($page === 'register') {
    $result['error'] = takeFlash('register_error');
    $result['oldInput'] = takeFlash('old_input', []);
} elseif ($page === 'verify-otp') {
    $result['email'] = $_SESSION['pending_email'] ?? '';
    $result['error'] = takeFlash('otp_error');
    $result['success'] = takeFlash('otp_success');
    if (takeFlash('otp_send_failed', false)) {
        $result['error'] = 'We could not send the email. Please use Resend Code.';
    }
} elseif ($page === 'login') {
    $result['error'] = takeFlash('login_error');
    $result['success'] = takeFlash('login_success');
    $email = takeFlash('login_email');
    if ($email !== '') {
        $result['oldInput'] = ['email' => $email];
    }
} elseif ($page === 'forgot-password') {
    $result['error'] = takeFlash('forgot_error');
    $email = takeFlash('forgot_email');
    if ($email !== '') {
        $result['oldInput'] = ['email' => $email];
    }
} elseif ($page === 'verify-reset-otp') {
    if (empty($_SESSION['pending_reset_user_id'])) {
        $result['redirect'] = 'forgot-password.html';
    } else {
        $result['email'] = $_SESSION['pending_reset_email'] ?? '';
        $result['error'] = takeFlash('reset_otp_error');
        $result['success'] = takeFlash('reset_otp_success');
    }
} elseif ($page === 'reset-password') {
    $verified = !empty($_SESSION['reset_verified_user_id'])
        && time() <= ($_SESSION['reset_verified_until'] ?? 0);
    if (!$verified) {
        if (!empty($_SESSION['reset_verified_user_id'])) {
            unset($_SESSION['reset_verified_user_id'], $_SESSION['reset_verified_until']);
            $_SESSION['forgot_error'] = 'Your password reset session expired. Please request a new code.';
        }
        $result['redirect'] = 'forgot-password.html';
    } else {
        $result['error'] = takeFlash('reset_error');
    }
}
echo json_encode($result);
