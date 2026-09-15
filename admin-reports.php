<?php
/**
 * admin-reports.php
 * ---------------------------------------------------------------------
 * Read-only reporting for staff: pick a date range -- and whether
 * requests count by the date submitted or by their event date -- to see
 * requests per type and status, mass intentions per intention type,
 * donations per fund, and every record in the range; or download the same
 * report as CSV (?export=csv) or PDF (?export=pdf). Built by
 * includes/reports.php from the live request and donation tables; nothing
 * on this page writes.
 * ---------------------------------------------------------------------
 */
require __DIR__ . '/includes/auth-guard.php';
require_once __DIR__ . '/includes/reports.php';

$validDate = function ($value) {
    $value = is_string($value) ? $value : '';
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return $date && $date->format('Y-m-d') === $value ? $value : null;
};
$from = $validDate($_GET['from'] ?? null) ?? date('Y-m-01');
$to   = $validDate($_GET['to'] ?? null) ?? date('Y-m-t');
if ($from > $to) {
    [$from, $to] = [$to, $from];
}
$basis = is_string($_GET['basis'] ?? null) && isset(PS_REPORT_BASES[$_GET['basis']]) ? $_GET['basis'] : 'submitted';

$report = ps_build_report($conn, $from, $to, $basis);
$export = $_GET['export'] ?? '';
if ($export === 'csv') {
    ps_report_csv($report);
    exit;
}
if ($export === 'pdf') {
    ps_report_pdf($report);
    exit;
}

$reportLink = fn(array $params) => 'admin-reports.php?' . http_build_query($params + ['from' => $from, 'to' => $to, 'basis' => $basis]);
$presets = [
    'This month' => [date('Y-m-01'), date('Y-m-t')],
    'Last month' => [date('Y-m-01', strtotime('first day of last month')), date('Y-m-t', strtotime('last day of last month'))],
    'This year'  => [date('Y-01-01'), date('Y-12-31')],
];
$funds = $report['funds'];
$donationTotals = $report['donationTotals'];

$pageTitle = 'Reports';
$pageCss   = 'admin.css';
$activeNav = 'reports';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/admin-sidebar.php';
?>
<main class="ps-main">

    <section class="ps-plain-header" style="position:relative;">
        <div>
            <div class="ps-heading-ornament"><span></span><?php ps_icon('chart'); ?><span></span></div>
            <h1>Reports</h1>
            <p>Summaries of parish requests, mass intentions and donations for any date range.</p>
        </div>
        <?php require __DIR__ . '/includes/topbar.php'; ?>
    </section>

    <div class="ps-card admin-report-controls">
        <form class="admin-report-filters" method="get" action="admin-reports.php">
            <label>From <input type="date" name="from" value="<?php echo htmlspecialchars($from); ?>" required></label>
            <label>To <input type="date" name="to" value="<?php echo htmlspecialchars($to); ?>" required></label>
            <label>Count requests by
                <span class="ps-select">
                    <select name="basis">
                        <?php foreach (PS_REPORT_BASES as $key => $label): ?>
                            <option value="<?php echo $key; ?>"<?php echo $key === $basis ? ' selected' : ''; ?>><?php echo htmlspecialchars($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </span>
            </label>
            <button type="submit" class="ps-btn ps-btn-primary">Show report</button>
        </form>
        <div class="admin-report-bar">
            <div class="admin-report-presets">
                <?php foreach ($presets as $label => [$presetFrom, $presetTo]): ?>
                    <a class="ps-tab<?php echo [$presetFrom, $presetTo] === [$from, $to] ? ' active' : ''; ?>" href="<?php echo htmlspecialchars($reportLink(['from' => $presetFrom, 'to' => $presetTo])); ?>"><?php echo htmlspecialchars($label); ?></a>
                <?php endforeach; ?>
            </div>
            <div class="admin-report-exports">
                <a class="ps-btn ps-btn-outline" href="<?php echo htmlspecialchars($reportLink(['export' => 'csv'])); ?>"><?php ps_icon('download'); ?> CSV</a>
                <a class="ps-btn ps-btn-outline" href="<?php echo htmlspecialchars($reportLink(['export' => 'pdf'])); ?>"><?php ps_icon('download'); ?> PDF</a>
            </div>
        </div>
        <p class="admin-report-range">
            <?php echo htmlspecialchars(ps_report_range_label($report)); ?> · requests counted by <?php echo htmlspecialchars(strtolower(PS_REPORT_BASES[$basis])); ?> · generated <?php echo htmlspecialchars($report['generated']); ?>
        </p>
    </div>

    <section class="admin-report-stats">
        <?php foreach (ps_report_stats($report) as [$label, $value]): ?>
            <div class="ps-card admin-report-stat"><strong><?php echo htmlspecialchars($value); ?></strong><span><?php echo htmlspecialchars($label); ?></span></div>
        <?php endforeach; ?>
    </section>

    <div class="ps-card">
        <h2 class="admin-report-heading">Requests by type</h2>
        <table class="admin-report-table">
            <thead>
                <tr><th>Type</th><?php foreach (PS_REPORT_COLUMNS as $label): ?><th class="is-num"><?php echo $label; ?></th><?php endforeach; ?></tr>
            </thead>
            <tbody>
                <?php foreach ($report['byType'] as $type => $counts): ?>
                    <tr>
                        <td><a href="<?php echo htmlspecialchars(PS_REQUEST_TYPES[$type]['page']); ?>"><?php echo htmlspecialchars(PS_REQUEST_TYPES[$type]['plural']); ?></a></td>
                        <?php foreach ($counts as $value): ?><td class="is-num"><?php echo (int) $value; ?></td><?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr><td>All requests</td><?php foreach ($report['totals'] as $value): ?><td class="is-num"><?php echo (int) $value; ?></td><?php endforeach; ?></tr>
            </tfoot>
        </table>
    </div>

    <div class="admin-report-grid">
        <div class="ps-card">
            <h2 class="admin-report-heading">Mass intentions by intention type</h2>
            <?php if ($report['intentions']): ?>
                <table class="admin-report-table">
                    <thead><tr><th>Intention type</th><?php foreach (PS_REPORT_COLUMNS as $label): ?><th class="is-num"><?php echo $label; ?></th><?php endforeach; ?></tr></thead>
                    <tbody>
                        <?php foreach ($report['intentions'] as $kind => $counts): ?>
                            <tr><td><?php echo htmlspecialchars($kind); ?></td><?php foreach ($counts as $value): ?><td class="is-num"><?php echo (int) $value; ?></td><?php endforeach; ?></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="admin-report-empty">No mass intentions in this range.</p>
            <?php endif; ?>
        </div>

        <div class="ps-card">
            <h2 class="admin-report-heading">Donations by fund</h2>
            <?php if ($funds): ?>
                <table class="admin-report-table">
                    <thead><tr><th>Fund</th><th class="is-num">Donations</th><th class="is-num">Received</th><th class="is-num">Verified</th></tr></thead>
                    <tbody>
                        <?php foreach ($funds as $fund => $sums): ?>
                            <tr><td><?php echo htmlspecialchars($fund); ?></td><td class="is-num"><?php echo $sums['count']; ?></td><td class="is-num"><?php echo htmlspecialchars(ps_peso($sums['received'])); ?></td><td class="is-num"><?php echo htmlspecialchars(ps_peso($sums['verified'])); ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr><td>All funds</td><td class="is-num"><?php echo $donationTotals['count']; ?></td><td class="is-num"><?php echo htmlspecialchars(ps_peso($donationTotals['received'])); ?></td><td class="is-num"><?php echo htmlspecialchars(ps_peso($donationTotals['verified'])); ?></td></tr>
                    </tfoot>
                </table>
                <small class="admin-doc-hint">Received excludes rejected donations; verified counts Approved, Scheduled and Completed ones.</small>
            <?php else: ?>
                <p class="admin-report-empty">No donations in this range.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="ps-card">
        <h2 class="admin-report-heading">Request records <small><?php echo count($report['requests']); ?></small></h2>
        <?php if ($report['requests']): ?>
            <table class="admin-report-table">
                <thead><tr><th>Reference</th><th>Type</th><th>Name</th><th>Event date</th><th>Time</th><th>Status</th><th>Submitted</th></tr></thead>
                <tbody>
                    <?php foreach ($report['requests'] as $request): ?>
                        <?php $cells = ps_report_request_cells($request); ?>
                        <tr>
                            <td><a href="<?php echo htmlspecialchars(ps_request_admin_url($request['type'], $request['reference_no'])); ?>"><?php echo htmlspecialchars($cells[0]); ?></a></td>
                            <?php foreach (array_slice($cells, 1, 4) as $cell): ?><td><?php echo htmlspecialchars($cell); ?></td><?php endforeach; ?>
                            <td><span class="ps-status is-<?php echo htmlspecialchars($request['status']); ?>"><?php echo htmlspecialchars($cells[5]); ?></span></td>
                            <td><?php echo htmlspecialchars($cells[6]); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="admin-report-empty">No requests in this range.</p>
        <?php endif; ?>
    </div>

    <div class="ps-card">
        <h2 class="admin-report-heading">Donation records <small><?php echo count($report['donations']); ?></small></h2>
        <?php if ($report['donations']): ?>
            <table class="admin-report-table">
                <thead><tr><th>Reference</th><th>Donor</th><th>Fund</th><th class="is-num">Amount</th><th>Status</th><th>Submitted</th></tr></thead>
                <tbody>
                    <?php foreach ($report['donations'] as $donation): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($donation['reference_no']); ?></td>
                            <td><?php echo htmlspecialchars($donation['donor_name']); ?></td>
                            <td><?php echo htmlspecialchars((string) $donation['purpose']); ?></td>
                            <td class="is-num"><?php echo htmlspecialchars(ps_peso($donation['amount'])); ?></td>
                            <td><span class="ps-status is-<?php echo htmlspecialchars($donation['status']); ?>"><?php echo htmlspecialchars(ps_status_label($donation['status'])); ?></span></td>
                            <td><?php echo htmlspecialchars(date('M j, Y', strtotime($donation['created_at']))); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="admin-report-empty">No donations in this range.</p>
        <?php endif; ?>
    </div>

</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
