<?php
/**
 * calendar-events.php
 * ---------------------------------------------------------------------
 * Public JSON feed for calendar.js, service-schedule.js,
 * dashboard-revamp.js and announcements.js: parish bookings staff have approved,
 * scheduled or completed, for the 6-week grid shown for ?month=&year=.
 * Anonymous on purpose -- type, time and status only, never names or
 * contact numbers -- and counseling appointments are left out entirely.
 * ---------------------------------------------------------------------
 */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
require_once 'config.php';
require_once 'includes/request-types.php';
require_once 'includes/mass-schedule.php';

$month = filter_var($_GET['month'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 12]]);
$year  = filter_var($_GET['year'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1900, 'max_range' => 9999]]);
if ($month === false || $year === false) {
    http_response_code(400);
    echo json_encode(['error' => 'Valid month (1-12) and year are required.']);
    exit;
}

// Same 42-day window calendar.js draws: the Sunday on/before the 1st, +41.
$first = new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month));
$start = $first->modify('-' . $first->format('w') . ' days');
$end   = $start->modify('+41 days');

$events = array_merge(
    ps_fetch_public_bookings($conn, $start->format('Y-m-d'), $end->format('Y-m-d')),
    ps_regular_mass_events($start->format('Y-m-d'), $end->format('Y-m-d'))
);
usort($events, static fn($a, $b) => strcmp($a['date'], $b['date']) ?: (strtotime($a['time'] ?: '00:00') <=> strtotime($b['time'] ?: '00:00')));
echo json_encode(['events' => $events]);
