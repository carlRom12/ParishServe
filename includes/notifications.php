<?php
/**
 * notifications.php
 * ---------------------------------------------------------------------
 * Emails a parishioner when staff change the status of their request or
 * donation, or the schedule of an Approved/Scheduled request.
 * admin-update-request.php calls ps_notify_request_update() only after
 * the change is committed, never before.
 *
 * Requests are tracked by reference number + contact details rather than
 * accounts, so the email goes to the contact_email the public form
 * collected; with none on file nothing is sent. It goes out through the
 * same parish SMTP account as the OTP emails (ps_smtp_mailer() in
 * includes/otp-mailer.php). Staff remarks are internal and never included.
 * Email only -- there is no SMS channel.
 * ---------------------------------------------------------------------
 */
require_once __DIR__ . '/otp-mailer.php';
require_once __DIR__ . '/request-types.php';

use PHPMailer\PHPMailer\Exception as MailException;

// What each new status means, in the parishioner's words.
const PS_STATUS_EMAIL_LINES = [
    'request' => [
        'under_review' => 'The parish office has started reviewing your request.',
        'approved'     => 'Good news: your request has been approved.',
        'scheduled'    => 'Your request has been scheduled.',
        'completed'    => 'Your request has been marked as completed. Thank you for letting the parish serve you.',
        'rejected'     => 'We are sorry, but your request could not be approved. Please contact the parish office for more information.',
    ],
    'donation' => [
        'under_review' => 'The parish office is checking your proof of payment.',
        'approved'     => 'Your donation has been verified. Thank you for your generosity.',
        'scheduled'    => 'Your donation has been recorded by the parish office.',
        'completed'    => 'Your donation has been fully processed. Thank you for supporting the parish.',
        'rejected'     => 'The parish office could not verify your donation. Please contact the parish office for more information.',
    ],
];

/**
 * Subject, HTML and plain-text body for one update. $update:
 *   type            a PS_REQUEST_TYPES key, or 'donation'
 *   reference, name, status
 *   status_changed  false when only the schedule changed
 *   date, time, end the saved schedule (requests only, each may be null)
 */
function ps_request_update_email(array $update) {
    $isDonation = $update['type'] === 'donation';
    $typeInfo = $isDonation ? null : PS_REQUEST_TYPES[$update['type']];
    $what = $isDonation ? 'donation' : $typeInfo['label'] . ' request';
    $status = ps_status_label($update['status']);

    if ($update['status_changed']) {
        $subject = "Your {$what} {$update['reference']} is now {$status}";
        $line = PS_STATUS_EMAIL_LINES[$isDonation ? 'donation' : 'request'][$update['status']] ?? "Your {$what} is now {$status}.";
    } else {
        $subject = "Schedule update for your {$what} {$update['reference']}";
        $line = 'The parish office has updated the schedule of your request.';
    }

    $rows = [['Reference number', $update['reference']]];
    if (!$isDonation) {
        $rows[] = ['Request', $typeInfo['label'] . ' for ' . $update['name']];
    }
    $rows[] = ['Status', $status];
    if (!$isDonation && !empty($update['date']) && in_array($update['status'], PS_SCHEDULE_STATUSES, true)) {
        $schedule = date('l, F j, Y', strtotime($update['date']));
        $window = ps_booking_window($update['type'], $update['time'] ?? null, $update['end'] ?? null);
        if ($window) {
            // Only a real end time is shown; other types' lengths are the parish's estimate.
            $schedule .= ' at ' . ps_minutes_label($window[0]) . ($typeInfo['end'] && !empty($update['end']) ? ' – ' . ps_minutes_label($window[1]) : '');
        }
        $rows[] = ['Schedule', $schedule];
    }
    $closing = 'If you have any questions, please contact the parish office and mention your reference number.';

    $html = '<p>Hello,</p><p>' . htmlspecialchars($line) . '</p><table cellpadding="6" style="border-collapse:collapse;">';
    foreach ($rows as [$label, $value]) {
        $html .= '<tr><td style="color:#6b5b56;">' . htmlspecialchars($label) . '</td><td><strong>' . htmlspecialchars($value) . '</strong></td></tr>';
    }
    $html .= '</table><p>' . htmlspecialchars($closing) . '</p><p>Our Lady of the Gate Parish &middot; ParishServe</p>';

    $text = "Hello,\n\n{$line}\n\n"
        . implode("\n", array_map(fn($row) => "{$row[0]}: {$row[1]}", $rows))
        . "\n\n{$closing}\n\nOur Lady of the Gate Parish - ParishServe";

    return ['subject' => $subject, 'html' => $html, 'text' => $text];
}

/** Emails ps_request_update_email($update) to $email: 'sent', 'no_email' (none on file) or 'failed' (logged). */
function ps_notify_request_update($email, array $update) {
    if (!is_string($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'no_email';
    }
    $message = ps_request_update_email($update);
    try {
        $mail = ps_smtp_mailer();
        $mail->Timeout = 20; // staff's Save waits on this
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = $message['subject'];
        $mail->Body    = $message['html'];
        $mail->AltBody = $message['text'];
        $mail->send();
        return 'sent';
    } catch (MailException $e) {
        error_log("Status email for {$update['reference']} failed: " . $e->getMessage());
        return 'failed';
    }
}
