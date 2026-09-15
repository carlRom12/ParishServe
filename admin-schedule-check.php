<?php
/**
 * admin-schedule-check.php
 * ---------------------------------------------------------------------
 * GET JSON for the Schedule fields in the request Update window
 * (initAdminModals() in main.js): what else is booked on ?date= in the
 * same place as request ?type=&id=, and which of those bookings the time
 * ?time= (and ?end= for facility reservations) would overlap. Staff only.
 * The same check runs again when the change is saved
 * (admin-update-request.php), so this is the early warning, not the rule.
 * Answers: { ok, bookings: [...], conflicts: [...] } -- ps_booking_summary() items
 * ---------------------------------------------------------------------
 */
$psGuardJson = true;
require __DIR__ . '/includes/auth-guard.php';
require_once __DIR__ . '/includes/request-types.php';

$type = (string) ($_GET['type'] ?? '');
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$date = (string) ($_GET['date'] ?? '');
$parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
if (!isset(PS_REQUEST_TYPES[$type]) || $id === false || !$parsed || $parsed->format('Y-m-d') !== $date) {
    ps_json(422, ['ok' => false, 'error' => 'A request type, id and a valid date are required.']);
}
$typeInfo = PS_REQUEST_TYPES[$type];

// A facility reservation only competes with bookings of the same facility.
$subtype = null;
if ($typeInfo['resource'] === 'facility') {
    $rows = ps_query_all($conn, "SELECT {$typeInfo['subtype']} AS subtype FROM {$typeInfo['table']} WHERE id = ?", [(string) $id]);
    $subtype = $rows[0]['subtype'] ?? null;
}

$bookings = ps_bookings_on($conn, $type, $date, $subtype, [$type, $id]);
$window = ps_booking_window($type, (string) ($_GET['time'] ?? ''), (string) ($_GET['end'] ?? ''));
$conflicts = $window
    ? array_filter($bookings, fn($booking) => ps_bookings_overlap($type, $window, $booking['type'], $booking['window']))
    : [];

ps_json(200, [
    'ok'        => true,
    'bookings'  => array_map('ps_booking_summary', $bookings),
    'conflicts' => array_map('ps_booking_summary', array_values($conflicts)),
]);
