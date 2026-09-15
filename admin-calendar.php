<?php
/**
 * admin-calendar.php
 * ---------------------------------------------------------------------
 * Staff month view of every request with a date -- all 7 types, pending
 * ones included, rejected ones left out -- colored by type, so the office
 * sees the whole schedule at once. Booked requests whose times overlap in
 * the same place (ps_mark_conflicts(); rules in includes/request-types.php)
 * are flagged in the grid and listed under it. Each entry links to its row
 * on its type's page, where the Update window can move it.
 *
 * Server-rendered into calendar.css's month grid -- the same 6-week
 * layout calendar.js draws for the public calendar. ?month=&year= picks
 * the month (default: this one).
 * ---------------------------------------------------------------------
 */
require __DIR__ . '/includes/auth-guard.php';
require_once __DIR__ . '/includes/request-types.php';

$month = filter_var($_GET['month'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 12]]);
$year  = filter_var($_GET['year'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 2000, 'max_range' => 2100]]);
$first = ($month && $year)
    ? new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month))
    : new DateTimeImmutable(date('Y-m-01'));
$start = $first->modify('-' . $first->format('w') . ' days');
$end   = $start->modify('+41 days');
$today = date('Y-m-d');

$rows = array_filter(
    ps_fetch_requests($conn, ['from' => $start->format('Y-m-d'), 'to' => $end->format('Y-m-d'), 'basis' => 'event']),
    fn($row) => $row['status'] !== 'rejected'
);
$events = ps_mark_conflicts($rows);
usort($events, fn($a, $b) => [$a['event_date'], $a['window'][0] ?? PHP_INT_MAX, $a['reference_no']]
    <=> [$b['event_date'], $b['window'][0] ?? PHP_INT_MAX, $b['reference_no']]);

$byDate = [];
$byReference = [];
foreach ($events as $event) {
    $byDate[$event['event_date']][] = $event;
    $byReference[$event['reference_no']] = $event;
}
$clashes = []; // each clashing pair once
foreach ($events as $event) {
    foreach ($event['conflicts'] as $other) {
        if (strcmp($event['reference_no'], $other) < 0) {
            $clashes[] = [$event, $byReference[$other]];
        }
    }
}

$monthLink = fn(DateTimeImmutable $date) => 'admin-calendar.php?month=' . $date->format('n') . '&year=' . $date->format('Y');
$eventTitle = fn(array $event) => implode(' · ', [
    $event['reference_no'], PS_REQUEST_TYPES[$event['type']]['label'], $event['name'],
    ps_schedule_labels($event['type'], $event['event_date'], $event['event_time'], $event['event_end'])['time'],
    ps_status_label($event['status']),
]);

$pageTitle = 'Calendar';
$pageCss   = ['calendar.css', 'admin.css'];
$activeNav = 'calendar';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/admin-sidebar.php';
?>
<main class="ps-main">

    <section class="ps-plain-header" style="position:relative;">
        <div>
            <div class="ps-heading-ornament"><span></span><?php ps_icon('calendar'); ?><span></span></div>
            <h1>Calendar</h1>
            <p>Every dated request across the parish, with scheduling conflicts flagged.</p>
        </div>
        <?php require __DIR__ . '/includes/topbar.php'; ?>
    </section>

    <div class="ps-card admin-cal">
        <div class="cal-toolbar">
            <div class="cal-toolbar-left">
                <a class="ps-btn ps-btn-outline admin-cal-nav" href="<?php echo htmlspecialchars($monthLink($first->modify('-1 month'))); ?>" aria-label="Previous month">&#8249;</a>
                <h2 class="admin-cal-month"><?php echo htmlspecialchars($first->format('F Y')); ?></h2>
                <a class="ps-btn ps-btn-outline admin-cal-nav" href="<?php echo htmlspecialchars($monthLink($first->modify('+1 month'))); ?>" aria-label="Next month">&#8250;</a>
                <a class="ps-btn ps-btn-outline" href="admin-calendar.php">Today</a>
            </div>
            <div class="ps-legend">
                <?php foreach (PS_REQUEST_TYPES as $key => $typeInfo): ?>
                    <span class="ps-legend-item"><span class="admin-cal-swatch admin-type-<?php echo $key; ?>"></span><?php echo htmlspecialchars($typeInfo['plural']); ?></span>
                <?php endforeach; ?>
                <span class="ps-legend-item"><span class="admin-cal-swatch is-pending"></span>Not yet approved</span>
                <span class="ps-legend-item"><span class="admin-cal-swatch is-conflict"></span>Conflict</span>
            </div>
        </div>

        <div class="admin-cal-scroll" tabindex="0" role="region" aria-label="Month calendar of parish requests">
            <div class="cal-weekday-row">
                <span class="is-sunday">SUN</span><span>MON</span><span>TUE</span><span>WED</span><span>THU</span><span>FRI</span><span>SAT</span>
            </div>
            <div class="cal-grid">
                <?php for ($i = 0; $i < 42; $i++): ?>
                    <?php $day = $start->modify("+{$i} days"); $iso = $day->format('Y-m-d'); ?>
                    <div class="cal-cell<?php echo $day->format('n') !== $first->format('n') ? ' is-muted' : ''; ?>">
                        <span class="cal-cell-day<?php echo $iso === $today ? ' is-today' : ''; ?>"><?php echo $day->format('j'); ?></span>
                        <?php foreach ($byDate[$iso] ?? [] as $event): ?>
                            <?php
                            $classes = 'cal-event admin-cal-event admin-type-' . $event['type']
                                . (in_array($event['status'], PS_BOOKED_STATUSES, true) ? '' : ' is-pending')
                                . ($event['conflicts'] ? ' is-conflict' : '');
                            ?>
                            <a class="<?php echo $classes; ?>" href="<?php echo htmlspecialchars(ps_request_admin_url($event['type'], $event['reference_no'])); ?>" title="<?php echo htmlspecialchars($eventTitle($event)); ?>">
                                <?php if ($event['conflicts']) ps_icon('warning', 'admin-cal-warning'); ?>
                                <span class="cal-event-text"><b><?php echo htmlspecialchars($event['window'] ? ps_minutes_label($event['window'][0]) : 'No time'); ?></b> <?php echo htmlspecialchars(PS_REQUEST_TYPES[$event['type']]['label'] . ' · ' . $event['name']); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endfor; ?>
            </div>
        </div>

        <div class="admin-cal-clashes">
            <?php if ($clashes): ?>
                <h2 class="admin-report-heading"><?php ps_icon('warning'); ?> Scheduling conflicts</h2>
                <ul>
                    <?php foreach ($clashes as [$a, $b]): ?>
                        <li>
                            <strong><?php echo htmlspecialchars(date('D, M j', strtotime($a['event_date']))); ?>:</strong>
                            <?php foreach ([$a, $b] as $index => $event): ?>
                                <?php echo $index ? ' overlaps ' : ''; ?>
                                <a href="<?php echo htmlspecialchars(ps_request_admin_url($event['type'], $event['reference_no'])); ?>"><?php echo htmlspecialchars($event['reference_no']); ?></a>
                                (<?php echo htmlspecialchars(PS_REQUEST_TYPES[$event['type']]['label'] . ', ' . ps_window_label($event['window'])); ?>)
                            <?php endforeach; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="admin-cal-none"><?php ps_icon('check-circle'); ?> No scheduling conflicts in these weeks.</p>
            <?php endif; ?>
        </div>
    </div>

</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
