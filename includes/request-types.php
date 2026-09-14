<?php
/**
 * request-types.php
 * ---------------------------------------------------------------------
 * One place for everything the parish request tables share, so the
 * admin pages, the write endpoint, both dashboards and the calendar feed
 * don't each keep their own copy:
 *   - the 7 request types (+ donations) and which table/columns hold them
 *   - the shared status pipeline and its labels
 *   - the required-document checklist per sacrament
 *
 * Status pipeline (database/schema.sql group note #3), enforced strictly
 * by admin-update-request.php:
 *   submitted -> under_review -> approved -> scheduled -> completed
 * one step at a time, or -> rejected from any step before completed.
 * completed and rejected are final.
 * ---------------------------------------------------------------------
 */

const PS_STATUS_PIPELINE = ['submitted', 'under_review', 'approved', 'scheduled', 'completed'];
const PS_STATUS_OPTIONS  = ['submitted', 'under_review', 'approved', 'scheduled', 'completed', 'rejected'];
const PS_STATUS_LABELS   = [
    'submitted' => 'Submitted', 'under_review' => 'Under Review', 'approved' => 'Approved',
    'scheduled' => 'Scheduled', 'completed' => 'Completed', 'rejected' => 'Rejected',
];

// Statuses that mean "this date is taken" on the public calendar.
const PS_BOOKED_STATUSES = ['approved', 'scheduled', 'completed'];

// type key => where that request type lives. 'name', 'date', 'time' and
// 'calendar_label' are SQL expressions -- trusted constants, never built
// from user input. 'category' matches calendar.html's category filter;
// 'public' = shown (anonymously) on the calendar. Counseling stays private.
const PS_REQUEST_TYPES = [
    'wedding' => [
        'label' => 'Wedding', 'table' => 'wedding_requests', 'icon' => 'ring', 'category' => 'sacrament', 'public' => true,
        'name' => "CONCAT(bride_name, ' & ', groom_name)", 'date' => 'preferred_date', 'time' => 'preferred_time',
    ],
    'baptism' => [
        'label' => 'Baptism', 'table' => 'baptism_requests', 'icon' => 'droplet', 'category' => 'sacrament', 'public' => true,
        'name' => 'child_name', 'date' => 'preferred_date', 'time' => 'preferred_time',
    ],
    'confirmation' => [
        'label' => 'Confirmation', 'table' => 'confirmation_requests', 'icon' => 'crest', 'category' => 'sacrament', 'public' => true,
        'name' => 'applicant_name', 'date' => 'preferred_date', 'time' => 'preferred_time',
    ],
    'funeral' => [
        'label' => 'Funeral', 'table' => 'funeral_requests', 'icon' => 'cross', 'category' => 'sacrament', 'public' => true,
        'name' => 'deceased_name', 'date' => 'service_date', 'time' => 'service_time',
    ],
    'counseling' => [
        'label' => 'Counseling', 'table' => 'counseling_appointments', 'icon' => 'people', 'category' => 'other', 'public' => false,
        'name' => 'requester_name', 'date' => 'preferred_date', 'time' => 'preferred_time',
    ],
    'massintention' => [
        'label' => 'Mass Intention', 'table' => 'mass_intentions', 'icon' => 'chalice', 'category' => 'mass', 'public' => true,
        'name' => "COALESCE(NULLIF(intention_for, ''), requester_name)", 'date' => 'mass_date', 'time' => 'mass_time',
    ],
    'facility' => [
        'label' => 'Facility Reservation', 'table' => 'facility_reservations', 'icon' => 'building', 'category' => 'event', 'public' => true,
        'name' => 'requester_name', 'date' => 'reservation_date', 'time' => 'start_time',
        'calendar_label' => "CONCAT(facility_name, ' reserved')",
    ],
];

const PS_DONATION_TABLE = 'donations';

// Documents per sacrament -- exactly the upload inputs each public form
// has ('field' is the input name; see includes/uploads.php for types and
// size limits, default PDF/JPG/PNG up to 5 MB). Per the paper's Scope &
// Limitations, final verification still happens on-site: the admin
// checklist only tracks what has come in. Received flags and files are
// stored per request in request_documents, keyed by 'label'.
// Counseling/Mass Intention/Facility Reservation carry no documents.
const PS_DOCUMENT_CHECKLISTS = [
    'wedding' => [
        ['field' => 'doc1', 'label' => 'Certificate of No Marriage (CENOMAR)', 'required' => true],
        ['field' => 'doc2', 'label' => 'Permit for Non-Parishioners and Outsiders (Bride)', 'required' => true],
        ['field' => 'doc3', 'label' => 'Baptismal & Confirmation Certificates', 'required' => true],
        ['field' => 'doc4', 'label' => 'Publication of Banns', 'required' => false],
        ['field' => 'doc5', 'label' => 'Marriage License', 'required' => true],
        ['field' => 'doc6', 'label' => '2 x 2 Picture', 'required' => true, 'types' => ['jpg', 'jpeg', 'png'], 'max_mb' => 2],
        ['field' => 'doc7', 'label' => 'Guest Priest Authorization', 'required' => false],
        ['field' => 'doc8', 'label' => "Commanding Officer's Certification", 'required' => false],
    ],
    'baptism'      => [['field' => 'birthCertificate', 'label' => 'Birth Certificate', 'required' => true]],
    'confirmation' => [['field' => 'baptismalCertificate', 'label' => 'Baptismal Certificate', 'required' => true]],
    'funeral'      => [['field' => 'deathCertificate', 'label' => 'Death Certificate', 'required' => true]],
];

// The donation form's one upload, stored in donations.proof_of_payment.
const PS_DONATION_PROOF = ['field' => 'proofOfPayment', 'label' => 'Proof of Payment', 'required' => true, 'types' => ['jpg', 'jpeg', 'png']];

function ps_status_label($status) {
    return PS_STATUS_LABELS[$status] ?? ucwords(str_replace('_', ' ', (string) $status));
}

/** Statuses a record at $current may move to next (staying put is always allowed). */
function ps_next_statuses($current) {
    $step = array_search($current, PS_STATUS_PIPELINE, true);
    if ($step === false || !isset(PS_STATUS_PIPELINE[$step + 1])) {
        return []; // rejected or completed: final
    }
    return [PS_STATUS_PIPELINE[$step + 1], 'rejected'];
}

/** Why a status change was refused, in words staff can act on. */
function ps_transition_error($current) {
    $next = ps_next_statuses($current);
    if (!$next) {
        return '"' . ps_status_label($current) . '" is a final status and can no longer be changed.';
    }
    return '"' . ps_status_label($current) . '" can only move to "' . ps_status_label($next[0]) . '" next, or to "Rejected".';
}

/**
 * A row's `details` JSON ({"Label": "value"}, written by the public forms)
 * as [[label, value], ...] for the admin Update window.
 */
function ps_request_details($json) {
    $details = is_string($json) ? json_decode($json, true) : null;
    $pairs = [];
    foreach (is_array($details) ? $details : [] as $label => $value) {
        if (is_scalar($value) && (string) $value !== '') {
            $pairs[] = [(string) $label, (string) $value];
        }
    }
    return $pairs;
}

/** The 7 request tables as one row shape: type, id, reference_no, contact_number, name, event_date, event_time, status, remarks, details, created_at. */
function ps_request_union_sql() {
    $selects = [];
    foreach (PS_REQUEST_TYPES as $key => $type) {
        $selects[] = "SELECT '{$key}' AS type, id, reference_no, contact_number, {$type['name']} AS name,"
            . " {$type['date']} AS event_date, {$type['time']} AS event_time, status, remarks, details, created_at"
            . " FROM {$type['table']}";
    }
    return implode(' UNION ALL ', $selects);
}

/** All requests newest first, optionally only one parishioner's (by contact number) and/or the latest $limit. */
function ps_fetch_requests(mysqli $conn, $contactNumber = null, $limit = null) {
    $sql = 'SELECT * FROM (' . ps_request_union_sql() . ') AS r';
    if ($contactNumber !== null) {
        $sql .= ' WHERE r.contact_number = ?';
    }
    $sql .= ' ORDER BY r.created_at DESC, r.reference_no DESC';
    if ($limit !== null) {
        $sql .= ' LIMIT ' . max(1, (int) $limit);
    }

    $stmt = $conn->prepare($sql);
    if ($contactNumber !== null) {
        $stmt->bind_param('s', $contactNumber);
    }
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

/** status => count across the 7 request tables (every status present, 0 if none). */
function ps_count_requests_by_status(mysqli $conn, $contactNumber = null) {
    $sql = 'SELECT status, COUNT(*) AS total FROM (' . ps_request_union_sql() . ') AS r';
    if ($contactNumber !== null) {
        $sql .= ' WHERE r.contact_number = ?';
    }
    $sql .= ' GROUP BY status';

    $stmt = $conn->prepare($sql);
    if ($contactNumber !== null) {
        $stmt->bind_param('s', $contactNumber);
    }
    $stmt->execute();
    $counts = array_fill_keys(PS_STATUS_OPTIONS, 0);
    foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $row) {
        $counts[$row['status']] = (int) $row['total'];
    }
    $stmt->close();
    return $counts;
}

/** The four dashboard stat cards. Pending = submitted + under_review (schema.sql group note #3). */
function ps_dashboard_stats(array $counts) {
    return [
        ['key' => 'pending',   'icon' => 'clock',          'label' => 'Pending',   'sub' => 'awaiting review', 'count' => $counts['submitted'] + $counts['under_review'], 'tint' => 'amber'],
        ['key' => 'approved',  'icon' => 'check-circle',   'label' => 'Approved',  'sub' => 'requests',        'count' => $counts['approved'],  'tint' => 'green'],
        ['key' => 'scheduled', 'icon' => 'calendar-check', 'label' => 'Scheduled', 'sub' => 'upcoming',        'count' => $counts['scheduled'], 'tint' => 'maroon'],
        ['key' => 'completed', 'icon' => 'document',       'label' => 'Completed', 'sub' => 'requests',        'count' => $counts['completed'], 'tint' => 'blue'],
    ];
}

/** What's waiting on staff: requests + donations still submitted/under review (the topbar bell). */
function ps_pending_counts(mysqli $conn) {
    $counts = ps_count_requests_by_status($conn);
    $requests = $counts['submitted'] + $counts['under_review'];
    $row = $conn->query("SELECT COUNT(*) AS total FROM " . PS_DONATION_TABLE . " WHERE status IN ('submitted', 'under_review')")->fetch_assoc();
    $donations = (int) $row['total'];
    return ['requests' => $requests, 'donations' => $donations, 'total' => $requests + $donations];
}

/** request_documents as [type][request id][label] => ['id', 'received', 'file', 'name']; optionally for one request. */
function ps_fetch_documents(mysqli $conn, $type = null, $id = null) {
    $sql = 'SELECT id, request_type, request_id, document_label, received, file_path, original_name FROM request_documents';
    if ($type !== null) {
        $stmt = $conn->prepare($sql . ' WHERE request_type = ? AND request_id = ?');
        $stmt->bind_param('si', $type, $id);
    } else {
        $stmt = $conn->prepare($sql);
    }
    $stmt->execute();
    $documents = [];
    foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $doc) {
        $documents[$doc['request_type']][(int) $doc['request_id']][$doc['document_label']] = [
            'id'       => (int) $doc['id'],
            'received' => (bool) $doc['received'],
            'file'     => $doc['file_path'],
            'name'     => (string) $doc['original_name'],
        ];
    }
    $stmt->close();
    return $documents;
}

/**
 * One request's checklist in the shape initAdminModals() renders from
 * data-docs. Uploaded files open through admin-file.php (uploads/ is
 * closed to the web).
 */
function ps_document_items($type, array $stored) {
    $items = [];
    foreach (PS_DOCUMENT_CHECKLISTS[$type] ?? [] as $doc) {
        $saved = $stored[$doc['label']] ?? [];
        $hasFile = !empty($saved['id']) && !empty($saved['file']);
        $items[] = [
            'label'    => $doc['label'] . ($doc['required'] ? '' : ' (if applicable)'),
            'required' => $doc['required'],
            'checked'  => !empty($saved['received']),
            'file'     => $hasFile ? 'admin-file.php?doc=' . $saved['id'] : null,
            'fileName' => $hasFile ? $saved['name'] : '',
            'isImage'  => $hasFile && preg_match('/\.(jpe?g|png)$/i', $saved['file']) === 1,
        ];
    }
    return $items;
}

/** The documents badge: required documents received out of all required ones. */
function ps_document_summary(array $items) {
    $required = array_filter($items, fn($doc) => $doc['required']);
    $received = count(array_filter($required, fn($doc) => $doc['checked']));
    return ['received' => $received, 'total' => count($required), 'complete' => $received === count($required)];
}

/**
 * Booked dates for the public calendar between $from and $to (Y-m-d,
 * inclusive). Anonymous on purpose: type, label, date, time and status
 * only -- no names or contact numbers ever leave this function.
 */
function ps_fetch_public_bookings(mysqli $conn, $from, $to) {
    $booked = "'" . implode("', '", PS_BOOKED_STATUSES) . "'";
    $selects = [];
    $params = [];
    foreach (PS_REQUEST_TYPES as $key => $type) {
        if (!$type['public']) {
            continue;
        }
        $label = $type['calendar_label'] ?? 'NULL';
        $selects[] = "SELECT '{$key}' AS type, {$label} AS label, {$type['date']} AS event_date, {$type['time']} AS event_time, status"
            . " FROM {$type['table']} WHERE status IN ({$booked}) AND {$type['date']} BETWEEN ? AND ?";
        array_push($params, $from, $to);
    }

    $stmt = $conn->prepare(implode(' UNION ALL ', $selects) . ' ORDER BY event_date, event_time');
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    $stmt->execute();
    $events = [];
    foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $row) {
        $type = PS_REQUEST_TYPES[$row['type']];
        $events[] = [
            'date'        => $row['event_date'],
            'time'        => $row['event_time'] ? date('g:i A', strtotime($row['event_time'])) : '',
            'type'        => $row['type'],
            'label'       => $row['label'] ?? $type['label'],
            'category'    => $type['category'],
            'status'      => $row['status'],
            'statusLabel' => ps_status_label($row['status']),
        ];
    }
    $stmt->close();
    return $events;
}
