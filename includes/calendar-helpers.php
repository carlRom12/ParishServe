<?php
/**
 * calendar-helpers.php
 * ---------------------------------------------------------------------
 * Real date math for building a month calendar grid, shared by the
 * main "Month" view AND the small "Mini Calendar" widget on
 * calendar.php (both need the exact same weeks/cells layout, just
 * rendered at different sizes -- writing this once avoids two
 * slightly-different copies of leap-year/month-overflow logic).
 *
 * This is genuinely computed (uses PHP's date()/mktime(), not a
 * lookup table), so it works correctly for ANY month/year you pass
 * it -- unlike the event CONTENT in calendar.php, which is still
 * hardcoded sample data. See calendar.php's own comments for why the
 * hardcoded events won't line up on the "expected" weekday once you
 * navigate to a month other than the demo one.
 * ---------------------------------------------------------------------
 */

/**
 * Builds a 6-week (42-cell) grid for the given month/year.
 *
 * @return array{label:string, month:int, year:int, cells:array}
 *   Each cell: ['day' => int, 'inMonth' => bool, 'date' => 'Y-m-d']
 *   inMonth=false cells are the previous/next month's days used to
 *   pad the first/last week, shown muted in the UI.
 */
function ps_build_month_grid(int $month, int $year): array
{
    // normalize e.g. month=13 -> January of year+1, month=0 -> December of year-1
    while ($month < 1) { $month += 12; $year--; }
    while ($month > 12) { $month -= 12; $year++; }

    $firstTimestamp = mktime(0, 0, 0, $month, 1, $year);
    $daysInMonth    = (int) date('t', $firstTimestamp);
    $startWeekday   = (int) date('w', $firstTimestamp); // 0 = Sunday ... 6 = Saturday

    $prevMonth = $month - 1;
    $prevYear  = $year;
    if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
    $daysInPrevMonth = (int) date('t', mktime(0, 0, 0, $prevMonth, 1, $prevYear));

    $nextMonth = $month + 1;
    $nextYear  = $year;
    if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }

    $cells = [];

    // leading days borrowed from the previous month
    for ($i = 0; $i < $startWeekday; $i++) {
        $dayNum = $daysInPrevMonth - $startWeekday + 1 + $i;
        $cells[] = [
            'day'     => $dayNum,
            'inMonth' => false,
            'date'    => sprintf('%04d-%02d-%02d', $prevYear, $prevMonth, $dayNum),
        ];
    }

    // the real days of this month
    for ($d = 1; $d <= $daysInMonth; $d++) {
        $cells[] = [
            'day'     => $d,
            'inMonth' => true,
            'date'    => sprintf('%04d-%02d-%02d', $year, $month, $d),
        ];
    }

    // trailing days from next month, always padded out to exactly 42
    // cells (6 full weeks) so the grid is the same height every month
    // -- matches the reference image and avoids the page jumping
    // taller/shorter depending on how a given month falls
    $nextDay = 1;
    while (count($cells) < 42) {
        $cells[] = [
            'day'     => $nextDay,
            'inMonth' => false,
            'date'    => sprintf('%04d-%02d-%02d', $nextYear, $nextMonth, $nextDay),
        ];
        $nextDay++;
    }

    return [
        'label' => date('F Y', $firstTimestamp),
        'month' => $month,
        'year'  => $year,
        'cells' => $cells,
    ];
}
