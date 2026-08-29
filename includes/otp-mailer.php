<?php
require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';
require_once __DIR__ . '/mail-config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
function sendOtpEmail($toEmail, $toName, $otpCode) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;

        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = 'Your ParishServe Verification Code';
        $mail->Body    = "Hi " . htmlspecialchars($toName) . ",<br><br>"
            . "Your verification code is:<br>"
            . "<strong style=\"font-size:24px;letter-spacing:4px;\">{$otpCode}</strong><br><br>"
            . "This code expires in 5 minutes. If you didn't request this, you can ignore this email.";
        $mail->AltBody = "Your verification code is: {$otpCode}. It expires in 5 minutes.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('OTP mail failed: ' . $mail->ErrorInfo);
        return false;
    }
}

?>