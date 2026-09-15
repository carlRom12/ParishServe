<?php
require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';
require_once __DIR__ . '/mail-config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// How long an emailed code stays valid, and the minimum gap between two
// sends. The email copy below is built from PS_OTP_TTL_SECONDS, so the
// text and the real expiry can't drift apart again.
const PS_OTP_TTL_SECONDS = 180;
const PS_OTP_RESEND_COOLDOWN_SECONDS = 60;

/** True if the code expiring at $expiresAt was sent less than the cooldown ago. */
function otpSentRecently($expiresAt) {
    if (empty($expiresAt)) {
        return false;
    }
    $sentAt = strtotime($expiresAt) - PS_OTP_TTL_SECONDS;
    return time() - $sentAt < PS_OTP_RESEND_COOLDOWN_SECONDS;
}

/**
 * A PHPMailer set up to send from the parish account (includes/mail-config.php),
 * throwing on failure. Also used by includes/notifications.php.
 */
function ps_smtp_mailer() {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = MAIL_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = MAIL_USERNAME;
    $mail->Password   = MAIL_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = MAIL_PORT;
    $mail->CharSet    = PHPMailer::CHARSET_UTF8;
    $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
    return $mail;
}

function sendCodeEmail($toEmail, $toName, $otpCode, $subject, $intro, $outro) {
    $mail = ps_smtp_mailer();
    $minutes = intdiv(PS_OTP_TTL_SECONDS, 60);

    try {
        $mail->addAddress($toEmail, (string) $toName);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = ($toName ? 'Hi ' . htmlspecialchars($toName) . ',' : 'Hi,') . '<br><br>'
            . htmlspecialchars($intro) . '<br>'
            . "<strong style=\"font-size:24px;letter-spacing:4px;\">{$otpCode}</strong><br><br>"
            . "This code expires in {$minutes} minutes. " . htmlspecialchars($outro);
        $mail->AltBody = "{$intro} {$otpCode}. It expires in {$minutes} minutes. {$outro}";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('OTP mail failed: ' . $mail->ErrorInfo);
        return false;
    }
}

/** Registration: verify the email address (login_register.php, verify-otp.php). */
function sendOtpEmail($toEmail, $toName, $otpCode) {
    return sendCodeEmail($toEmail, $toName, $otpCode,
        'Your ParishServe Verification Code',
        'Your verification code is:',
        "If you didn't request this, you can ignore this email.");
}

/** Forgot password (forgot-password.php, verify-reset-otp.php). */
function sendPasswordResetEmail($toEmail, $toName, $otpCode) {
    return sendCodeEmail($toEmail, $toName, $otpCode,
        'Your ParishServe Password Reset Code',
        'We received a request to reset your ParishServe password. Your password reset code is:',
        "If you didn't ask to reset your password, you can ignore this email and your password won't change.");
}
