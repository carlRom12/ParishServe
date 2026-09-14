<?php
/**
 * admin-update-request.php
 * ---------------------------------------------------------------------
 * POST endpoint behind the Update modals on admin-requests.php and
 * admin-donations.php (initAdminModals() in main.js). Admin/Super Admin
 * only, CSRF-checked.
 *
 * Takes:  type     a PS_REQUEST_TYPES key, or 'donation'
 *         id       row id in that table
 *         status   new status -- must be the current one, the next step
 *                  of the pipeline, or 'rejected' (includes/request-types.php)
 *         remarks  optional internal note
 *         docs     sacrament types only: JSON list of booleans in
 *                  PS_DOCUMENT_CHECKLISTS order (what's been received)
 * Answers: { ok: true, message, row }  -- row is applied by applyRowUpdate() in main.js
 *          { ok: false, error }        -- 4xx/5xx, nothing written
 * ---------------------------------------------------------------------
 */
$psGuardJson = true;
require __DIR__ . '/includes/auth-guard.php';
require_once __DIR__ . '/includes/request-types.php';

ps_require_post_csrf();

$type = (string) ($_POST['type'] ?? '');
if ($type === 'donation') {
    $table = PS_DONATION_TABLE;
} elseif (isset(PS_REQUEST_TYPES[$type])) {
    $table = PS_REQUEST_TYPES[$type]['table'];
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

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn->begin_transaction();

    // $table comes from the constant map above, never from the request.
    $stmt = $conn->prepare("SELECT reference_no, status FROM {$table} WHERE id = ? FOR UPDATE");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $current = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$current) {
        $conn->rollback();
        ps_json(404, ['ok' => false, 'error' => 'That record no longer exists. Please reload the page.']);
    }
    if ($status !== $current['status'] && !in_array($status, ps_next_statuses($current['status']), true)) {
        $conn->rollback();
        ps_json(409, ['ok' => false, 'error' => ps_transition_error($current['status']), 'currentStatus' => $current['status']]);
    }

    $remarksValue = $remarks === '' ? null : $remarks;
    $stmt = $conn->prepare("UPDATE {$table} SET status = ?, remarks = ? WHERE id = ?");
    $stmt->bind_param('ssi', $status, $remarksValue, $id);
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

$row = [
    'status'      => $status,
    'statusLabel' => ps_status_label($status),
    'statusClass' => 'is-' . $status,
    'data'        => [
        'status'          => $status,
        'remarks'         => $remarks,
        'allowedStatuses' => implode(',', ps_next_statuses($status)),
    ],
];
if (isset(PS_DOCUMENT_CHECKLISTS[$type])) {
    $items = ps_document_items($type, ps_fetch_documents($conn, $type, $id)[$type][$id] ?? []);
    $summary = ps_document_summary($items);
    $row['data']['docs'] = json_encode($items);
    $row['fields'] = ['docs' => $summary['received'] . '/' . $summary['total']];
    $row['docsComplete'] = $summary['complete'];
}

ps_json(200, [
    'ok'      => true,
    'message' => $current['reference_no'] . ' saved as ' . ps_status_label($status) . '.',
    'row'     => $row,
]);
