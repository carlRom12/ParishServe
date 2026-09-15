<?php
/**
 * admin-update-request.php
 * ---------------------------------------------------------------------
 * POST endpoint behind the Update windows on the per-type request pages
 * (includes/admin-request-page.php) and admin-donations.php
 * (initAdminModals() in main.js). Admin/Super Admin only, CSRF-checked.
 *
 * Takes:  type        a PS_REQUEST_TYPES key, or 'donation'
 *         id          row id in that table
 *         status      new status -- must be the current one, the next step
 *                     of the pipeline, or 'rejected' (includes/request-types.php)
 *         remarks     optional internal note
 *         docs        sacrament types only: JSON list of booleans in
 *                     PS_DOCUMENT_CHECKLISTS order (what's been received)
 *         event_date, event_time, event_end
 *                     requests only, optional: the schedule (Y-m-d, HH:MM;
 *                     event_end only for facility reservations). Approved
 *                     and Scheduled need a date and time and may not
 *                     overlap another booking (ps_schedule_conflicts()), a
 *                     changed date can't be in the past, and a final
 *                     record's schedule can't change.
 * Once the change is committed, the parishioner is emailed if the status
 * changed, or the schedule of an Approved/Scheduled request did
 * (includes/notifications.php); the reply's message says whether it went.
 * Answers: { ok: true, message, row }        -- row is applied by applyRowUpdate() in main.js
 *          { ok: false, error, conflicts? }   -- 4xx/5xx, nothing written
 * ---------------------------------------------------------------------
 */
$psGuardJson = true;
require __DIR__ . '/includes/auth-guard.php';
require_once __DIR__ . '/includes/request-types.php';
require_once __DIR__ . '/includes/notifications.php';

ps_require_post_csrf();

$type = (string) ($_POST['type'] ?? '');
$typeInfo = PS_REQUEST_TYPES[$type] ?? null;
if ($type === 'donation') {
    $table = PS_DONATION_TABLE;
} elseif ($typeInfo) {
    $table = $typeInfo['table'];
} else {
    ps_json(422, ['ok' => false, 'error' => 'Unknown request type.']);
}

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id === false) {
    ps_json(422, ['ok' => false, 'error' => 'Missing or invalid record id.']);
}

$status = (string) ($_POST['status'] ?? '');
if (!in_array($status, PS_STATUS_OPTIONS, true)) {
    ps_json(422, ['ok' => false, 'error' => 'Please choose a valid status.']);
}

$remarks = trim((string) ($_POST['remarks'] ?? ''));
if (strlen($remarks) > 2000) {
    ps_json(422, ['ok' => false, 'error' => 'Remarks are too long (2,000 characters max).']);
}

$docFlags = null;
if (isset(PS_DOCUMENT_CHECKLISTS[$type], $_POST['docs'])) {
    $decoded = json_decode((string) $_POST['docs'], true);
    if (!is_array($decoded) || count($decoded) !== count(PS_DOCUMENT_CHECKLISTS[$type])) {
        ps_json(422, ['ok' => false, 'error' => "The document checklist doesn't match this request type. Please reload the page."]);
    }
    $docFlags = array_map(fn($flag) => $flag === true, array_values($decoded));
}

// The schedule, when the form sent one (disabled fields -- a final
// record's -- aren't sent, so the stored schedule stays as it is).
$schedule = null;
if ($typeInfo && array_key_exists('event_date', $_POST)) {
    $date = trim((string) $_POST['event_date']);
    $time = trim((string) ($_POST['event_time'] ?? ''));
    $end  = $typeInfo['end'] ? trim((string) ($_POST['event_end'] ?? '')) : '';

    $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    if ($date !== '' && (!$parsed || $parsed->format('Y-m-d') !== $date)) {
        ps_json(422, ['ok' => false, 'error' => 'Please enter a valid date.']);
    }
    if (($time !== '' && ps_time_minutes($time) === null) || ($end !== '' && ps_time_minutes($end) === null)) {
        ps_json(422, ['ok' => false, 'error' => 'Please enter a valid time.']);
    }
    $schedule = [
        'date' => $date === '' ? null : $date,
        'time' => $time === '' ? null : substr($time, 0, 5) . ':00',
        'end'  => $end === '' ? null : substr($end, 0, 5) . ':00',
    ];
    if ($schedule['time'] !== null && $schedule['end'] !== null && ps_time_minutes($schedule['end']) <= ps_time_minutes($schedule['time'])) {
        ps_json(422, ['ok' => false, 'error' => 'The end time must be after the start time.']);
    }
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn->begin_transaction();
    $fail = function ($httpCode, array $body) use ($conn) {
        $conn->rollback();
        ps_json($httpCode, ['ok' => false] + $body);
    };

    // $table and the column expressions come from the constant maps, never from the request.
    $columns = $typeInfo
        ? "reference_no, status, contact_email, {$typeInfo['name']} AS name, " . ($typeInfo['subtype'] ?? 'NULL') . ' AS subtype,'
            . " {$typeInfo['date']} AS event_date, {$typeInfo['time']} AS event_time, " . ($typeInfo['end'] ?? 'NULL') . ' AS event_end'
        : 'reference_no, status, contact_email, donor_name AS name';
    $stmt = $conn->prepare("SELECT {$columns} FROM {$table} WHERE id = ? FOR UPDATE");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $current = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$current) {
        $fail(404, ['error' => 'That record no longer exists. Please reload the page.']);
    }
    if ($status !== $current['status'] && !in_array($status, ps_next_statuses($current['status']), true)) {
        $fail(409, ['error' => ps_transition_error($current['status']), 'currentStatus' => $current['status']]);
    }

    $statusChanged = $status !== $current['status'];
    $stored = ['date' => $current['event_date'] ?? null, 'time' => $current['event_time'] ?? null, 'end' => $current['event_end'] ?? null];
    $scheduleChanged = $schedule !== null && $schedule !== $stored;
    $saved = $schedule ?? $stored;

    if ($scheduleChanged) {
        if (!ps_next_statuses($current['status'])) {
            $fail(409, ['error' => '"' . ps_status_label($current['status']) . '" is a final status, so its schedule can no longer be changed.']);
        }
        if ($saved['date'] === null && !$typeInfo['date_optional']) {
            $fail(422, ['error' => 'Please set a date.']);
        }
        if ($saved['date'] !== null && $saved['date'] !== $stored['date'] && $saved['date'] < date('Y-m-d')) {
            $fail(422, ['error' => "The date can't be in the past."]);
        }
    }

    if ($typeInfo && in_array($status, PS_SCHEDULE_STATUSES, true) && ($statusChanged || $scheduleChanged)) {
        if ($saved['date'] === null || $saved['time'] === null) {
            $fail(422, ['error' => 'Set the date and time before saving this request as "' . ps_status_label($status) . '".']);
        }
        $conflicts = array_map('ps_booking_summary', ps_schedule_conflicts(
            $conn, $type, $saved['date'], ps_booking_window($type, $saved['time'], $saved['end']), $current['subtype'], [$type, $id]
        ));
        if ($conflicts) {
            $list = implode('; ', array_map(fn($booking) => "{$booking['reference']} ({$booking['type']}, {$booking['time']})", $conflicts));
            $fail(409, [
                'error'     => 'This time overlaps ' . (count($conflicts) === 1 ? 'another booking' : count($conflicts) . ' other bookings')
                    . ' on ' . date('M j, Y', strtotime($saved['date'])) . ": {$list}. Please choose a different date or time.",
                'conflicts' => $conflicts,
            ]);
        }
    }

    $remarksValue = $remarks === '' ? null : $remarks;
    if ($schedule !== null) {
        $sets = "status = ?, remarks = ?, {$typeInfo['date']} = ?, {$typeInfo['time']} = ?";
        $params = [$status, $remarksValue, $saved['date'], $saved['time']];
        if ($typeInfo['end']) {
            $sets .= ", {$typeInfo['end']} = ?";
            $params[] = $saved['end'];
        }
        $params[] = $id;
        $stmt = $conn->prepare("UPDATE {$table} SET {$sets} WHERE id = ?");
        $stmt->bind_param(str_repeat('s', count($params) - 1) . 'i', ...$params);
    } else {
        $stmt = $conn->prepare("UPDATE {$table} SET status = ?, remarks = ? WHERE id = ?");
        $stmt->bind_param('ssi', $status, $remarksValue, $id);
    }
    $stmt->execute();
    $stmt->close();

    if ($docFlags !== null) {
        $stmt = $conn->prepare('INSERT INTO request_documents (request_type, request_id, document_label, received)
                                VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE received = VALUES(received)');
        foreach (PS_DOCUMENT_CHECKLISTS[$type] as $index => $doc) {
            $label = $doc['label'];
            $received = $docFlags[$index] ? 1 : 0;
            $stmt->bind_param('sisi', $type, $id, $label, $received);
            $stmt->execute();
        }
        $stmt->close();
    }

    $conn->commit();
} catch (mysqli_sql_exception $e) {
    $conn->rollback();
    error_log('admin-update-request failed: ' . $e->getMessage());
    ps_json(500, ['ok' => false, 'error' => 'Could not save the change. Please try again.']);
}

// Committed -- now tell the parishioner.
$notice = '';
if ($statusChanged || ($scheduleChanged && in_array($status, PS_SCHEDULE_STATUSES, true))) {
    $emailed = ps_notify_request_update($current['contact_email'], [
        'type'           => $type,
        'reference'      => $current['reference_no'],
        'name'           => $current['name'],
        'status'         => $status,
        'status_changed' => $statusChanged,
        'date'           => $saved['date'],
        'time'           => $saved['time'],
        'end'            => $saved['end'],
    ]);
    $notice = [
        'sent'     => ' The parishioner was emailed.',
        'no_email' => ' No email address is on file, so no email was sent.',
        'failed'   => ' The email to the parishioner could not be sent.',
    ][$emailed];
}

$row = [
    'status'      => $status,
    'statusLabel' => ps_status_label($status),
    'statusClass' => 'is-' . $status,
    'fields'      => [],
    'data'        => [
        'status'          => $status,
        'remarks'         => $remarks,
        'allowedStatuses' => implode(',', ps_next_statuses($status)),
    ],
];
if ($typeInfo) {
    $labels = ps_schedule_labels($type, $saved['date'], $saved['time'], $saved['end']);
    $row['fields'] += ['scheduleDate' => $labels['date'], 'scheduleTime' => $labels['time']];
    $row['data'] += [
        'date'    => (string) $saved['date'],
        'time'    => $saved['time'] ? substr($saved['time'], 0, 5) : '',
        'endTime' => $saved['end'] ? substr($saved['end'], 0, 5) : '',
    ];
}
if (isset(PS_DOCUMENT_CHECKLISTS[$type])) {
    $items = ps_document_items($type, ps_fetch_documents($conn, $type, $id)[$type][$id] ?? []);
    $summary = ps_document_summary($items);
    $row['data']['docs'] = json_encode($items);
    $row['fields']['docs'] = $summary['received'] . '/' . $summary['total'];
    $row['docsComplete'] = $summary['complete'];
}

ps_json(200, [
    'ok'      => true,
    'message' => $current['reference_no'] . ' saved as ' . ps_status_label($status) . '.' . $notice,
    'row'     => $row,
]);
