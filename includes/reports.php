<?php
/**
 * reports.php
 * ---------------------------------------------------------------------
 * The admin report (admin-reports.php) for a date range, and its CSV and
 * PDF downloads. Read-only: it counts the same request and donation rows
 * the admin pages show (ps_fetch_requests() / ps_fetch_donations() in
 * includes/request-types.php).
 *   basis 'submitted'  requests go by the date they were submitted
 *   basis 'event'      requests go by their scheduled/preferred date
 * Donations have no event date, so they always go by the date submitted.
 * Pending = Submitted + Under Review; a donation counts as verified once
 * it is Approved, Scheduled or Completed.
 * ---------------------------------------------------------------------
 */
require_once __DIR__ . '/request-types.php';
require_once __DIR__ . '/pdf.php';

const PS_REPORT_BASES = ['submitted' => 'Date submitted', 'event' => 'Event date'];
const PS_REPORT_COLUMNS = [
    'total' => 'Total', 'pending' => 'Pending', 'approved' => 'Approved',
    'scheduled' => 'Scheduled', 'completed' => 'Completed', 'rejected' => 'Rejected',
];

function ps_build_report(mysqli $conn, $from, $to, $basis) {
    $empty = array_fill_keys(array_keys(PS_REPORT_COLUMNS), 0);
    $count = function (array &$counts, $status) {
        $counts['total']++;
        $counts[in_array($status, ['submitted', 'under_review'], true) ? 'pending' : $status]++;
    };

    $sortKey = $basis === 'event'
        ? fn($r) => [(string) $r['event_date'], (string) $r['event_time'], $r['reference_no']]
        : fn($r) => [$r['created_at'], $r['reference_no']];
    $requests = ps_fetch_requests($conn, ['from' => $from, 'to' => $to, 'basis' => $basis]);
    usort($requests, fn($a, $b) => $sortKey($a) <=> $sortKey($b));

    $byType = array_fill_keys(array_keys(PS_REQUEST_TYPES), $empty);
    $totals = $empty;
    $intentions = [];
    foreach ($requests as $request) {
        $count($byType[$request['type']], $request['status']);
        $count($totals, $request['status']);
        if ($request['type'] === 'massintention') {
            $kind = (string) $request['subtype'] !== '' ? $request['subtype'] : 'Unspecified';
            $intentions[$kind] = $intentions[$kind] ?? $empty;
            $count($intentions[$kind], $request['status']);
        }
    }
    ksort($intentions);

    $donations = ps_fetch_donations($conn, ['from' => $from, 'to' => $to]);
    usort($donations, fn($a, $b) => [$a['created_at'], $a['donation_no']] <=> [$b['created_at'], $b['donation_no']]);
    $emptyFund = ['count' => 0, 'received' => 0.0, 'verified' => 0.0, 'rejected' => 0];
    $funds = [];
    $donationTotals = $emptyFund;
    foreach ($donations as $donation) {
        $fund = (string) $donation['purpose'] !== '' ? $donation['purpose'] : 'Unspecified';
        $funds[$fund] = $funds[$fund] ?? $emptyFund;
        foreach ([&$funds[$fund], &$donationTotals] as &$sums) {
            $sums['count']++;
            if ($donation['status'] === 'rejected') {
                $sums['rejected']++;
                continue;
            }
            $sums['received'] += (float) $donation['amount'];
            if (in_array($donation['status'], PS_BOOKED_STATUSES, true)) {
                $sums['verified'] += (float) $donation['amount'];
            }
        }
        unset($sums);
    }
    ksort($funds);

    return [
        'from' => $from, 'to' => $to, 'basis' => $basis, 'generated' => date('M j, Y g:i A'),
        'requests' => $requests, 'byType' => $byType, 'totals' => $totals, 'intentions' => $intentions,
        'donations' => $donations, 'funds' => $funds, 'donationTotals' => $donationTotals,
    ];
}

function ps_report_range_label(array $report) {
    return date('M j, Y', strtotime($report['from'])) . ' – ' . date('M j, Y', strtotime($report['to']));
}

function ps_report_filename(array $report, $extension) {
    return "parishserve-report-{$report['from']}-to-{$report['to']}.{$extension}";
}

function ps_peso($amount, $symbol = '₱') {
    return $symbol . number_format((float) $amount, 2);
}

/** One request as report record cells: reference, type, name, event date, time, status, submitted. */
function ps_report_request_cells(array $request) {
    $schedule = ps_schedule_labels($request['type'], $request['event_date'], $request['event_time'], $request['event_end']);
    return [
        $request['reference_no'], PS_REQUEST_TYPES[$request['type']]['label'], $request['name'],
        $request['event_date'] ? $schedule['date'] : '—',
        $request['event_date'] && $request['event_time'] ? ps_minutes_label(ps_time_minutes($request['event_time'])) : '—',
        ps_status_label($request['status']), date('M j, Y', strtotime($request['created_at'])),
    ];
}

/** The report's summary figures, as [label, value] pairs. */
function ps_report_stats(array $report, $pesoSymbol = '₱') {
    return [
        ['Requests', (string) $report['totals']['total']],
        ['Pending', (string) $report['totals']['pending']],
        ['Completed', (string) $report['totals']['completed']],
        ['Rejected', (string) $report['totals']['rejected']],
        ['Donations verified', ps_peso($report['donationTotals']['verified'], $pesoSymbol)],
    ];
}

/** Keeps spreadsheet apps from running submitted text (a name, a note) as a formula. */
function ps_csv_cell($value) {
    $value = (string) $value;
    return preg_match('/^[=+\-@\t\r]/', $value) ? "'" . $value : $value;
}

/** Sends the report as a CSV download (UTF-8 with BOM so Excel keeps accents and ₱). */
function ps_report_csv(array $report) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . ps_report_filename($report, 'csv') . '"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    $put = function (array $cells = []) use ($out) {
        fputcsv($out, array_map('ps_csv_cell', $cells));
    };

    $put(['ParishServe Report', 'Our Lady of the Gate Parish']);
    $put(['Range', ps_report_range_label($report)]);
    $put(['Requests counted by', PS_REPORT_BASES[$report['basis']]]);
    $put(['Generated', $report['generated']]);
    $put();

    $put(['Summary']);
    foreach (ps_report_stats($report, 'PHP ') as $stat) {
        $put($stat);
    }
    $put();

    $put(['Requests by type']);
    $put(array_merge(['Type'], array_values(PS_REPORT_COLUMNS)));
    foreach ($report['byType'] as $type => $counts) {
        $put(array_merge([PS_REQUEST_TYPES[$type]['plural']], array_values($counts)));
    }
    $put(array_merge(['All requests'], array_values($report['totals'])));
    $put();

    $put(['Mass intentions by intention type']);
    $put(array_merge(['Intention type'], array_values(PS_REPORT_COLUMNS)));
    foreach ($report['intentions'] as $kind => $counts) {
        $put(array_merge([$kind], array_values($counts)));
    }
    $put();

    $put(['Donations by fund']);
    $put(['Fund', 'Donations', 'Received (PHP, not rejected)', 'Verified (PHP)', 'Rejected']);
    foreach ($report['funds'] as $fund => $sums) {
        $put([$fund, $sums['count'], number_format($sums['received'], 2, '.', ''), number_format($sums['verified'], 2, '.', ''), $sums['rejected']]);
    }
    $totals = $report['donationTotals'];
    $put(['All funds', $totals['count'], number_format($totals['received'], 2, '.', ''), number_format($totals['verified'], 2, '.', ''), $totals['rejected']]);
    $put();

    $put(['Request records']);
    $put(['Reference', 'Type', 'Name', 'Event date', 'Time', 'Status', 'Submitted']);
    foreach ($report['requests'] as $request) {
        $put(ps_report_request_cells($request));
    }
    $put();

    $put(['Donation records']);
    $put(['Donation number', 'GCash reference number', 'Donor', 'Fund', 'Amount (PHP)', 'Status', 'Submitted']);
    foreach ($report['donations'] as $donation) {
        $put([$donation['donation_no'], (string) $donation['gcash_reference'], $donation['donor_name'], (string) $donation['purpose'], number_format((float) $donation['amount'], 2, '.', ''),
            ps_status_label($donation['status']), date('M j, Y', strtotime($donation['created_at']))]);
    }
    fclose($out);
}

/** Sends the report as a PDF download (includes/pdf.php). */
function ps_report_pdf(array $report) {
    $countColumns = array_map(fn($label) => [$label, 1, 'R'], array_values(PS_REPORT_COLUMNS));
    $countRows = function (array $groups) {
        $rows = [];
        foreach ($groups as $label => $counts) {
            $rows[] = array_merge([$label], array_map('strval', array_values($counts)));
        }
        return $rows;
    };

    $pdf = new PsPdf('ParishServe Report · ' . ps_report_range_label($report));
    $pdf->write('ParishServe Report', 18, true, PsPdf::MAROON, 2);
    $pdf->write('Our Lady of the Gate Parish', 10, false, PsPdf::MUTED, 4);
    $pdf->write(ps_report_range_label($report) . '  ·  Requests counted by ' . strtolower(PS_REPORT_BASES[$report['basis']])
        . '  ·  Generated ' . $report['generated'], 9, false, PsPdf::MUTED, 14);
    $pdf->stats(ps_report_stats($report, 'PHP '));

    $types = [];
    foreach ($report['byType'] as $type => $counts) {
        $types[PS_REQUEST_TYPES[$type]['plural']] = $counts;
    }
    $pdf->heading('Requests by type');
    $pdf->table(array_merge([['Type', 2.2, 'L']], $countColumns), $countRows($types + ['All requests' => $report['totals']]));

    $pdf->heading('Mass intentions by intention type');
    $pdf->table(array_merge([['Intention type', 2.2, 'L']], $countColumns), $countRows($report['intentions']));

    $pdf->heading('Donations by fund');
    $fundRows = [];
    foreach ($report['funds'] + ($report['funds'] ? ['All funds' => $report['donationTotals']] : []) as $fund => $sums) {
        $fundRows[] = [$fund, (string) $sums['count'], ps_peso($sums['received'], 'PHP '), ps_peso($sums['verified'], 'PHP '), (string) $sums['rejected']];
    }
    $pdf->table([['Fund', 2.4, 'L'], ['Donations', 1, 'R'], ['Received', 1.4, 'R'], ['Verified', 1.4, 'R'], ['Rejected', 1, 'R']], $fundRows);

    $pdf->heading('Request records');
    $pdf->table(
        [['Reference', 1.45, 'L'], ['Type', 1.6, 'L'], ['Name', 2.15, 'L'], ['Event date', 1.1, 'L'], ['Time', 1.0, 'L'], ['Status', 1.0, 'L'], ['Submitted', 1.1, 'L']],
        array_map('ps_report_request_cells', $report['requests'])
    );

    $pdf->heading('Donation records');
    $pdf->table(
        [['Donation no.', 1.3, 'L'], ['GCash ref. no.', 1.6, 'L'], ['Donor', 1.8, 'L'], ['Fund', 1.8, 'L'], ['Amount', 1.2, 'R'], ['Status', 1.0, 'L'], ['Submitted', 1.1, 'L']],
        array_map(fn($donation) => [
            $donation['donation_no'], (string) $donation['gcash_reference'], $donation['donor_name'], (string) $donation['purpose'], ps_peso($donation['amount'], 'PHP '),
            ps_status_label($donation['status']), date('M j, Y', strtotime($donation['created_at'])),
        ], $report['donations'])
    );

    $body = $pdf->output();
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . ps_report_filename($report, 'pdf') . '"');
    header('Content-Length: ' . strlen($body));
    echo $body;
}
