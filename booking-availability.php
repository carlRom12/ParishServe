<?php
/**
 * booking-availability.php
 * ---------------------------------------------------------------------
 * Public JSON for the booking hint under the request forms' date fields
 * (input[data-booking-type] in frontend.js): the times already taken on
 * ?date= in the place a ?type= request would use -- see the conflict
 * rules in includes/request-types.php. Anonymous, like
 * calendar-events.php (type and time only, never names or reference
 * numbers), and only for types shown on the public calendar.
 * Answers: { minutes, blocks: [{ type, label, start, end, time, shared }] }
 *   minutes  how long a request of this type takes, so the page can test
 *            a chosen time against the blocks (start/end: minutes after
 *            midnight, null while a booking has no time yet)
 *   shared   a same-type group ceremony, which doesn't block this request
 * The submitted form is checked again on the server (includes/request-forms.php).
 * ---------------------------------------------------------------------
 */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
require_once 'config.php';
require_once 'includes/request-types.php';

$type = (string) ($_GET['type'] ?? '');
$date = (string) ($_GET['date'] ?? '');
$parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
if (!isset(PS_REQUEST_TYPES[$type]) || !PS_REQUEST_TYPES[$type]['public'] || !$parsed || $parsed->format('Y-m-d') !== $date) {
    http_response_code(400);
    echo json_encode(['error' => 'A public request type and a valid date (YYYY-MM-DD) are required.']);
    exit;
}

$blocks = [];
$facility = isset($_GET['facility']) ? (string) $_GET['facility'] : null;
foreach (ps_bookings_on($conn, $type, $date, $facility) as $booking) {
    $blocks[] = [
        'type'   => $booking['type'],
        'label'  => PS_REQUEST_TYPES[$booking['type']]['label'],
        'start'  => $booking['window'][0] ?? null,
        'end'    => $booking['window'][1] ?? null,
        'time'   => ps_window_label($booking['window']),
        'shared' => $booking['type'] === $type && PS_REQUEST_TYPES[$type]['group'],
    ];
}

echo json_encode(['minutes' => PS_REQUEST_TYPES[$type]['minutes'], 'blocks' => $blocks]);
