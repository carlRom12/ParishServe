<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
$page = $_GET['page'] ?? '';
$result = [];
if ($page === 'register') {
    $result['error'] = $_SESSION['register_error'] ?? '';
    $result['oldInput'] = $_SESSION['old_input'] ?? [];
    unset($_SESSION['register_error'], $_SESSION['old_input']);
} elseif ($page === 'verify-otp') {
    $result['email'] = $_SESSION['pending_email'] ?? '';
    $result['error'] = $_SESSION['otp_error'] ?? '';
    $result['success'] = $_SESSION['otp_success'] ?? '';
    if (!empty($_SESSION['otp_send_failed'])) {
        $result['error'] = 'We could not send the email. Please use Resend Code.';
    }
    unset($_SESSION['otp_error'], $_SESSION['otp_success'], $_SESSION['otp_send_failed']);
} elseif ($page === 'login') {
    $result['success'] = $_SESSION['login_success'] ?? '';
    unset($_SESSION['login_success']);
}
echo json_encode($result);
