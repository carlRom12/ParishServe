<?php
/**
 * calendar.php
 * ---------------------------------------------------------------------
 * FRONTEND ONLY, same rules as dashboard.php / announcements.php: real
 * PHP logic where it's genuinely just logic (the month-grid math lives
 * in includes/calendar-helpers.php and works for any month/year), but
 * the EVENT CONTENT is still a hardcoded array until there's a
 * database to query.
 *
 * MONTH NAVIGATION IS REAL, THOUGH:
 * Prev/Next/Today are plain links with ?month=&year= query params --
 * no JS, no AJAX, just PHP re-rendering the grid for whatever month
 * was asked for. That part will keep working unchanged once real
 * event data replaces $monthEvents below.
 *
 * WHY EVENTS DON'T ALWAYS LAND ON THE "RIGHT" WEEKDAY:
 * The reference image is a May 2025 mockup where e.g. "Sunday Mass"
 * entries happen to fall on Sundays. We generate the ACTUAL calendar
 * grid for whatever year is requested (default: our May 2026 demo
 * month, chosen to match the dates already used on dashboard.php /
 * announcements.php), and May 2026's weekdays don't line up with
 * May 2025's. So $monthEvents below is keyed by DATE NUMBER only
 * (matching the image's content) -- once this becomes a real query
 * against actual scheduled services, recurring-event logic (e.g. "every
 * Sunday") would replace this flat list entirely.
 * ---------------------------------------------------------------------
 */
require __DIR__ . '/includes/calendar-helpers.php';

// TODO: once real event data exists, default month/year to the actual
// current month (date('n'), date('Y')) instead of this hardcoded demo
// month. Defaulting to "today" right now would render a genuinely
// empty grid, since every hardcoded event below lives in May 2026.
$requestedMonth = isset($_GET['month']) ? (int) $_GET['month'] : 5;
$requestedYear  = isset($_GET['year'])  ? (int) $_GET['year']  : 2026;

$grid = ps_build_month_grid($requestedMonth, $requestedYear);

$prevLink = 'calendar.php?month=' . ($grid['month'] - 1) . '&year=' . $grid['year'];
$nextLink = 'calendar.php?month=' . ($grid['month'] + 1) . '&year=' . $grid['year'];
$todayLink = 'calendar.php?month=' . (int) date('n') . '&year=' . (int) date('Y');

// hardcoded "today" for highlighting purposes -- matches the demo
// month above, NOT the server's real clock (see TODO note up top)
$demoToday = '2026-05-17';

// TODO: SELECT * FROM <events table> WHERE event_date BETWEEN
// <first day of grid> AND <last day of grid>, grouped by date. Keys
// are 'Y-m-d'; each entry needs a 'cat' matching the legend below so
// filtering/coloring keeps working once this is real.
$monthEvents = [
    '2026-05-01' => [['time' => '6:00 PM', 'title' => 'Evening Mass', 'cat' => 'mass']],
    '2026-05-02' => [['time' => '6:00 PM', 'title' => 'First Friday Mass', 'cat' => 'mass']],
    '2026-05-03' => [['time' => '10:00 AM', 'title' => 'Baptism', 'cat' => 'sacrament']],
    '2026-05-04' => [['time' => '8:00 AM', 'title' => 'Sunday Mass', 'cat' => 'mass'], ['time' => '10:00 AM', 'title' => 'Sunday Mass', 'cat' => 'mass']],
    '2026-05-06' => [['time' => '6:00 PM', 'title' => 'Evening Mass', 'cat' => 'mass']],
    '2026-05-07' => [['time' => '6:00 PM', 'title' => 'Novena to Our Lady of the Gate', 'cat' => 'other']],
    '2026-05-08' => [['time' => '6:00 PM', 'title' => 'Evening Mass', 'cat' => 'mass']],
    '2026-05-10' => [['time' => '10:00 AM', 'title' => 'Marriage Seminar', 'cat' => 'reminder'], ['time' => '2:00 PM', 'title' => 'Youth Choir Practice', 'cat' => 'event']],
    '2026-05-11' => [['time' => '8:00 AM', 'title' => 'Sunday Mass', 'cat' => 'mass'], ['time' => '10:00 AM', 'title' => 'Sunday Mass', 'cat' => 'mass']],
    '2026-05-13' => [['time' => '6:00 PM', 'title' => 'Evening Mass', 'cat' => 'mass']],
    '2026-05-14' => [['time' => '6:00 PM', 'title' => 'Bible Study', 'cat' => 'other']],
    '2026-05-15' => [['time' => '6:00 PM', 'title' => 'Evening Mass', 'cat' => 'mass']],
    '2026-05-17' => [['time' => '9:00 AM', 'title' => 'Feast of the Assumption of Mary', 'cat' => 'mass'], ['time' => '5:00 PM', 'title' => 'Parish Fellowship Gathering', 'cat' => 'event']],
    '2026-05-18' => [['time' => '8:00 AM', 'title' => 'Sunday Mass', 'cat' => 'mass'], ['time' => '10:00 AM', 'title' => 'Sunday Mass', 'cat' => 'mass']],
    '2026-05-20' => [['time' => '6:00 PM', 'title' => 'Evening Mass', 'cat' => 'mass']],
    '2026-05-21' => [['time' => '6:00 PM', 'title' => 'Novena to Our Lady of the Gate', 'cat' => 'other']],
    '2026-05-22' => [['time' => '6:00 PM', 'title' => 'Evening Mass', 'cat' => 'mass']],
    '2026-05-24' => [['time' => '2:00 PM', 'title' => 'Youth Gathering', 'cat' => 'event']],
    '2026-05-25' => [['time' => '8:00 AM', 'title' => 'Sunday Mass', 'cat' => 'mass'], ['time' => '10:00 AM', 'title' => 'Sunday Mass', 'cat' => 'mass']],
    '2026-05-27' => [['time' => '6:00 PM', 'title' => 'Evening Mass', 'cat' => 'mass']],
    '2026-05-28' => [['time' => '6:00 PM', 'title' => 'Prayer Meeting', 'cat' => 'other']],
    '2026-05-29' => [['time' => '6:00 PM', 'title' => 'Evening Mass', 'cat' => 'mass']],
    '2026-05-31' => [['time' => '6:00 PM', 'title' => 'First Friday Mass & Holy Hour', 'cat' => 'mass']],
];

$categoryLegend = [
    'mass'      => 'Mass',
    'sacrament' => 'Sacrament',
    'event'     => 'Event',
    'reminder'  => 'Reminder',
    'other'     => 'Other',
];

$weekdayLabels = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];
$weekdayLettersMini = ['S', 'M', 'T', 'W', 'T', 'F', 'S'];

// Same data as announcements.php's $upcomingEvents -- intentionally
// duplicated for now (see that file's comment). Once there's a real
// query behind either page, this becomes one shared function both
// pages call instead of two hardcoded copies that could drift apart.
$upcomingEvents = [
    ['month' => 'MAY', 'day' => '17', 'title' => 'Feast of the Assumption of Mary', 'time' => '9:00 AM', 'location' => 'Main Church', 'dot' => 'mass'],
    ['month' => 'MAY', 'day' => '24', 'title' => 'Youth Gathering',                 'time' => '2:00 PM', 'location' => 'Parish Hall', 'dot' => 'event'],
    ['month' => 'MAY', 'day' => '31', 'title' => 'First Friday Mass & Holy Hour',   'time' => '6:00 PM', 'location' => 'Main Church', 'dot' => 'mass'],
    ['month' => 'JUN', 'day' => '07', 'title' => 'Pentecost Sunday Celebration',    'time' => '9:00 AM', 'location' => 'Main Church', 'dot' => 'mass'],
];

$pageTitle = 'Parish Calendar';
$pageCss   = 'calendar.css';
$activeNav = 'calendar';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>
<main class="ps-main">

    <!-- ============================ PAGE HERO ============================ -->
    <section class="ann-hero">
        <div class="ann-hero-image">
            <img src="assets/images/hero-banner.svg" alt="Our Lady of the Gate interior">
        </div>
        <?php require __DIR__ . '/includes/topbar.php'; ?>
        <div class="ann-hero-text">
            <h1>Parish Calendar</h1>
            <div class="ps-heading-ornament"><span></span><?php ps_icon('cross'); ?><span></span></div>
            <p>Stay informed about Mass schedules, sacraments, events, and activities happening in our parish.</p>
        </div>
    </section>

    <!-- ============================ TOOLBAR =============================== -->
    <div class="cal-toolbar">
        <div class="cal-toolbar-left">
            <!-- Week/Day views aren't built yet -- this row toggles which
                 button LOOKS active, but the grid below only ever renders
                 Month view for now. See main.js initCalendarViewSwitch(). -->
            <div class="ps-segmented" data-view-switch>
                <button type="button" class="active" data-view="month">Month</button>
                <button type="button" data-view="week">Week</button>
                <button type="button" data-view="day">Day</button>
            </div>

            <label class="ps-select">
                <select id="categoryFilter" name="categoryFilter" data-category-filter>
                    <option value="">All Categories</option>
                    <?php foreach ($categoryLegend as $key => $label): ?>
                        <option value="<?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars($label); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php ps_icon('chevron-down'); ?>
            </label>

            <a href="<?php echo htmlspecialchars($todayLink); ?>" class="ps-filter-btn">Today</a>

            <a href="<?php echo htmlspecialchars($prevLink); ?>" class="ps-round-btn" aria-label="Previous month"><?php ps_icon('arrow-left'); ?></a>
            <a href="<?php echo htmlspecialchars($nextLink); ?>" class="ps-round-btn" aria-label="Next month"><?php ps_icon('arrow-right'); ?></a>
        </div>

        <div class="ps-legend">
            <?php foreach ($categoryLegend as $key => $label): ?>
                <span class="ps-legend-item"><span class="ps-dot cat-<?php echo htmlspecialchars($key); ?>"></span><?php echo htmlspecialchars($label); ?></span>
            <?php endforeach; ?>
        </div>
    </div>

    <section class="ann-grid cal-page-grid">

        <div class="ann-main">

            <!-- ---- Main month grid ---- -->
            <div class="ps-card cal-grid-card">
                <h2 class="cal-month-label"><?php echo htmlspecialchars($grid['label']); ?></h2>

                <div class="cal-weekday-row">
                    <?php foreach ($weekdayLabels as $i => $wd): ?>
                        <span class="<?php echo $i === 0 ? 'is-sunday' : ''; ?>"><?php echo $wd; ?></span>
                    <?php endforeach; ?>
                </div>

                <div class="cal-grid" data-category-scope>
                    <?php foreach ($grid['cells'] as $cell): ?>
                        <?php
                        $dayEvents = $monthEvents[$cell['date']] ?? [];
                        $isToday = $cell['date'] === $demoToday;
                        $visibleEvents = array_slice($dayEvents, 0, 2);
                        $overflowCount = count($dayEvents) - count($visibleEvents);
                        ?>
                        <div class="cal-cell<?php echo $cell['inMonth'] ? '' : ' is-muted'; ?>">
                            <span class="cal-cell-day<?php echo $isToday ? ' is-today' : ''; ?>"><?php echo (int) $cell['day']; ?></span>
                            <?php foreach ($visibleEvents as $ev): ?>
                                <div class="cal-event" data-category="<?php echo htmlspecialchars($ev['cat']); ?>">
                                    <span class="ps-dot cat-<?php echo htmlspecialchars($ev['cat']); ?>"></span>
                                    <span class="cal-event-text"><?php echo htmlspecialchars($ev['time']); ?> <?php echo htmlspecialchars($ev['title']); ?></span>
                                </div>
                            <?php endforeach; ?>
                            <?php if ($overflowCount > 0): ?>
                                <span class="cal-event-more">+<?php echo (int) $overflowCount; ?> more</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="ps-info-banner">
                    <?php ps_icon('info'); ?>
                    <span>Events are subject to change. Please check back regularly for updates.</span>
                </div>
            </div>

        </div>

        <!-- ---- Right column ---- -->
        <div class="ann-side">

            <div class="ps-card cal-mini">
                <div class="ps-card-header">
                    <span class="ps-card-title"><?php ps_icon('calendar'); ?> Mini Calendar</span>
                </div>

                <div class="cal-mini-head">
                    <a href="<?php echo htmlspecialchars($prevLink); ?>" class="ps-round-btn ps-round-btn-sm" aria-label="Previous month"><?php ps_icon('arrow-left'); ?></a>
                    <strong><?php echo htmlspecialchars($grid['label']); ?></strong>
                    <a href="<?php echo htmlspecialchars($nextLink); ?>" class="ps-round-btn ps-round-btn-sm" aria-label="Next month"><?php ps_icon('arrow-right'); ?></a>
                </div>

                <div class="cal-mini-weekday-row">
                    <?php foreach ($weekdayLettersMini as $wl): ?><span><?php echo $wl; ?></span><?php endforeach; ?>
                </div>
                <div class="cal-mini-grid">
                    <?php foreach ($grid['cells'] as $cell): ?>
                        <span class="cal-mini-cell<?php echo $cell['inMonth'] ? '' : ' is-muted'; ?><?php echo $cell['date'] === $demoToday ? ' is-today' : ''; ?>">
                            <?php echo (int) $cell['day']; ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="ps-card">
                <div class="ps-card-header">
                    <span class="ps-card-title">Upcoming Events</span>
                    <a href="calendar.php" class="ps-link-more">View all events <?php ps_icon('arrow-right'); ?></a>
                </div>
                <ul class="ann-event-list">
                    <?php foreach ($upcomingEvents as $ev): ?>
                        <li>
                            <span class="ann-event-date"><small><?php echo htmlspecialchars($ev['month']); ?></small><strong><?php echo htmlspecialchars($ev['day']); ?></strong></span>
                            <span class="ann-event-body">
                                <strong><?php echo htmlspecialchars($ev['title']); ?></strong>
                                <small><?php echo htmlspecialchars($ev['time']); ?> · <?php echo htmlspecialchars($ev['location']); ?></small>
                            </span>
                            <span class="ps-dot cat-<?php echo htmlspecialchars($ev['dot']); ?>"></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <a href="calendar.php" class="ps-link-more cal-view-full">View full calendar <?php ps_icon('arrow-right'); ?></a>
            </div>

            <div class="ps-card cal-sync-box">
                <div class="cal-sync-icon"><?php ps_icon('bell'); ?></div>
                <h3>Add to your calendar</h3>
                <p>Sync parish events with your personal calendar so you never miss an important event.</p>
                <!-- real calendar-sync (Google/Outlook/.ics export) is a
                     future feature -- no backend to generate a feed from
                     yet, so this button is intentionally inert for now -->
                <button type="button" class="ps-btn ps-btn-primary"><?php ps_icon('calendar'); ?> Sync Calendar</button>
            </div>

        </div>

    </section>

</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
