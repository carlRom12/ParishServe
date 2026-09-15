<?php
/**
 * request-types.php
 * ---------------------------------------------------------------------
 * One place for everything the parish request tables share, so the
 * admin pages, the write endpoint, both dashboards, both calendars, the
 * reports and the public forms don't each keep their own copy:
 *   - the 7 request types (+ donations) and which table/columns hold them
 *   - the shared status pipeline and its labels
 *   - the required-document checklist per sacrament
 *   - booking windows and schedule conflicts
 *
 * Status pipeline (shared by every request table so they can be UNIONed), enforced strictly
 * by admin-update-request.php:
 *   submitted -> under_review -> approved -> scheduled -> completed
 * one step at a time, or -> rejected from any step before completed.
 * completed and rejected are final.
 *
 * Schedule conflicts: two booked requests (PS_BOOKED_STATUSES) clash when
 * their time windows overlap on the same date and they need the same
 * place -- the church (weddings, baptisms, confirmations, funerals), the
 * counseling office, or the same facility. A window runs from the
 * request's time for its type's usual length ('minutes'), or to its end
 * time where the table has one (facility reservations). Baptisms and
 * Confirmations are group ceremonies, so two of the same type share a
 * slot instead of clashing; Mass Intentions are offered at a regular Mass
 * and never clash. The public forms (includes/request-forms.php), their
 * booking hint (booking-availability.php), the admin Update window
 * (admin-schedule-check.php, admin-update-request.php) and the admin
 * calendar all use the functions at the bottom of this file.
 * ---------------------------------------------------------------------
 */

const PS_STATUS_PIPELINE = ['submitted', 'under_review', 'approved', 'scheduled', 'completed'];
const PS_STATUS_OPTIONS  = ['submitted', 'under_review', 'approved', 'scheduled', 'completed', 'rejected'];
const PS_STATUS_LABELS   = [
    'submitted' => 'Submitted', 'under_review' => 'Under Review', 'approved' => 'Approved',
    'scheduled' => 'Scheduled', 'completed' => 'Completed', 'rejected' => 'Rejected',
];

// Statuses that mean "this date is taken" on the calendars and in conflict checks.
const PS_BOOKED_STATUSES = ['approved', 'scheduled', 'completed'];

// A request can only be saved as one of these with a date and time set,
// and never over another booking (admin-update-request.php).
const PS_SCHEDULE_STATUSES = ['approved', 'scheduled'];

// type key => where that request type lives.
//   label / plural  names; plural is the admin sidebar link
//   page            the type's admin page
//   name, date, time, end, subtype
//                   SQL expressions -- trusted constants, never built from
//                   user input. 'end' (end time) and 'subtype' (facility,
//                   intention type, concern) are null where a table has none.
//   category        calendar.html's category filter
//   public          shown (anonymously) on the public calendar; counseling stays private
//   resource        what a booking occupies: church | counseling | facility | null (never clashes)
//   minutes         usual length of a booking, for types without an end time
//   group           same-type bookings share a slot (group ceremonies)
//   date_optional   the table's date may be empty (the parish sets Confirmation dates)
const PS_REQUEST_TYPES = [
    'wedding' => [
        'label' => 'Wedding', 'plural' => 'Weddings', 'page' => 'admin-wedding-requests.php',
        'table' => 'wedding_requests', 'icon' => 'ring', 'category' => 'sacrament', 'public' => true,
        'name' => "CONCAT(bride_name, ' & ', groom_name)", 'date' => 'preferred_date', 'time' => 'preferred_time', 'end' => null, 'subtype' => null,
        'resource' => 'church', 'minutes' => 120, 'group' => false, 'date_optional' => false,
    ],
    'baptism' => [
        'label' => 'Baptism', 'plural' => 'Baptisms', 'page' => 'admin-baptism-requests.php',
        'table' => 'baptism_requests', 'icon' => 'droplet', 'category' => 'sacrament', 'public' => true,
        'name' => 'child_name', 'date' => 'preferred_date', 'time' => 'preferred_time', 'end' => null, 'subtype' => null,
        'resource' => 'church', 'minutes' => 60, 'group' => true, 'date_optional' => false,
    ],
    'confirmation' => [
        'label' => 'Confirmation', 'plural' => 'Confirmations', 'page' => 'admin-confirmation-requests.php',
        'table' => 'confirmation_requests', 'icon' => 'crest', 'category' => 'sacrament', 'public' => true,
        'name' => 'applicant_name', 'date' => 'preferred_date', 'time' => 'preferred_time', 'end' => null, 'subtype' => null,
        'resource' => 'church', 'minutes' => 120, 'group' => true, 'date_optional' => true,
    ],
    'funeral' => [
        'label' => 'Funeral', 'plural' => 'Funerals', 'page' => 'admin-funeral-requests.php',
        'table' => 'funeral_requests', 'icon' => 'cross', 'category' => 'sacrament', 'public' => true,
        'name' => 'deceased_name', 'date' => 'service_date', 'time' => 'service_time', 'end' => null, 'subtype' => null,
        'resource' => 'church', 'minutes' => 90, 'group' => false, 'date_optional' => false,
    ],
    'counseling' => [
        'label' => 'Counseling', 'plural' => 'Counseling', 'page' => 'admin-counseling-requests.php',
        'table' => 'counseling_appointments', 'icon' => 'people', 'category' => 'other', 'public' => false,
        'name' => 'requester_name', 'date' => 'preferred_date', 'time' => 'preferred_time', 'end' => null, 'subtype' => 'concern_type',
        'resource' => 'counseling', 'minutes' => 60, 'group' => false, 'date_optional' => false,
    ],
    'massintention' => [
        'label' => 'Mass Intention', 'plural' => 'Mass Intentions', 'page' => 'admin-mass-intentions.php',
        'table' => 'mass_intentions', 'icon' => 'chalice', 'category' => 'mass', 'public' => true,
        'name' => "COALESCE(NULLIF(intention_for, ''), requester_name)", 'date' => 'mass_date', 'time' => 'mass_time', 'end' => null, 'subtype' => 'intention_type',
        'resource' => null, 'minutes' => 60, 'group' => false, 'date_optional' => false,
    ],
    'facility' => [
        'label' => 'Facility Reservation', 'plural' => 'Facility Reservations', 'page' => 'admin-facility-reservations.php',
        'table' => 'facility_reservations', 'icon' => 'building', 'category' => 'event', 'public' => true,
        'name' => 'requester_name', 'date' => 'reservation_date', 'time' => 'start_time', 'end' => 'end_time', 'subtype' => 'facility_name',
        'calendar_label' => "CONCAT(facility_name, ' reserved')",
        'resource' => 'facility', 'minutes' => 60, 'group' => false, 'date_optional' => false,
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

/** Runs a prepared SELECT with string parameters and returns every row. */
function ps_query_all(mysqli $conn, $sql, array $params = []) {
    $stmt = $conn->prepare($sql);
    if ($params) {
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    }
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

/**
 * The request tables (all 7, or only $types) as one row shape: type, id,
 * reference_no, contact_number, contact_email, name, subtype, event_date,
 * event_time, event_end, status, remarks, details, created_at.
 */
function ps_request_union_sql(?array $types = null) {
    $selects = [];
    foreach (PS_REQUEST_TYPES as $key => $type) {
        if ($types !== null && !in_array($key, $types, true)) {
            continue;
        }
        $subtype = $type['subtype'] ?? 'NULL';
        $end = $type['end'] ?? 'NULL';
        $selects[] = "SELECT '{$key}' AS type, id, reference_no, contact_number, contact_email, {$type['name']} AS name, {$subtype} AS subtype,"
            . " {$type['date']} AS event_date, {$type['time']} AS event_time, {$end} AS event_end, status, remarks, details, created_at"
            . " FROM {$type['table']}";
    }
    return implode(' UNION ALL ', $selects);
}

/**
 * WHERE clause + parameters for request filters: contact (a parishioner's
 * contact number) and from/to (Y-m-d, inclusive) on the date submitted --
 * or on the event date with basis 'event'.
 */
function ps_request_where(array $filters) {
    $clauses = [];
    $params = [];
    if (isset($filters['contact'])) {
        $clauses[] = 'r.contact_number = ?';
        $params[] = $filters['contact'];
    }
    $column = ($filters['basis'] ?? 'submitted') === 'event' ? 'r.event_date' : 'DATE(r.created_at)';
    if (isset($filters['from'])) {
        $clauses[] = "{$column} >= ?";
        $params[] = $filters['from'];
    }
    if (isset($filters['to'])) {
        $clauses[] = "{$column} <= ?";
        $params[] = $filters['to'];
    }
    return [$clauses ? ' WHERE ' . implode(' AND ', $clauses) : '', $params];
}

/**
 * Requests newest first. $filters: type (one PS_REQUEST_TYPES key), contact,
 * from/to + basis (see ps_request_where()), limit.
 */
function ps_fetch_requests(mysqli $conn, array $filters = []) {
    if (isset($filters['type']) && !isset(PS_REQUEST_TYPES[$filters['type']])) {
        return [];
    }
    [$where, $params] = ps_request_where($filters);
    $types = isset($filters['type']) ? [$filters['type']] : null;
    $sql = 'SELECT * FROM (' . ps_request_union_sql($types) . ') AS r' . $where . ' ORDER BY r.created_at DESC, r.reference_no DESC';
    if (isset($filters['limit'])) {
        $sql .= ' LIMIT ' . max(1, (int) $filters['limit']);
    }
    return ps_query_all($conn, $sql, $params);
}

/** status => count across the 7 request tables (every status present, 0 if none). */
function ps_count_requests_by_status(mysqli $conn, $contactNumber = null) {
    [$where, $params] = ps_request_where($contactNumber === null ? [] : ['contact' => $contactNumber]);
    $counts = array_fill_keys(PS_STATUS_OPTIONS, 0);
    $sql = 'SELECT status, COUNT(*) AS total FROM (' . ps_request_union_sql() . ') AS r' . $where . ' GROUP BY status';
    foreach (ps_query_all($conn, $sql, $params) as $row) {
        $counts[$row['status']] = (int) $row['total'];
    }
    return $counts;
}

/** The four admin dashboard stat cards. Pending = submitted + under_review. */
function ps_dashboard_stats(array $counts) {
    return [
        ['key' => 'pending',   'icon' => 'clock',          'label' => 'Pending',   'sub' => 'awaiting review', 'count' => $counts['submitted'] + $counts['under_review'], 'tint' => 'amber'],
        ['key' => 'approved',  'icon' => 'check-circle',   'label' => 'Approved',  'sub' => 'requests',        'count' => $counts['approved'],  'tint' => 'green'],
        ['key' => 'scheduled', 'icon' => 'calendar-check', 'label' => 'Scheduled', 'sub' => 'upcoming',        'count' => $counts['scheduled'], 'tint' => 'maroon'],
        ['key' => 'completed', 'icon' => 'document',       'label' => 'Completed', 'sub' => 'requests',        'count' => $counts['completed'], 'tint' => 'blue'],
    ];
}

/**
 * What's waiting on staff: requests (per type in byType, and in total) +
 * donations still submitted/under review -- the topbar bell and the
 * dashboard's Waiting for Review card.
 */
function ps_pending_counts(mysqli $conn) {
    $byType = array_fill_keys(array_keys(PS_REQUEST_TYPES), 0);
    $sql = 'SELECT type, COUNT(*) AS total FROM (' . ps_request_union_sql() . ") AS r WHERE r.status IN ('submitted', 'under_review') GROUP BY type";
    foreach (ps_query_all($conn, $sql) as $row) {
        $byType[$row['type']] = (int) $row['total'];
    }
    $row = $conn->query("SELECT COUNT(*) AS total FROM " . PS_DONATION_TABLE . " WHERE status IN ('submitted', 'under_review')")->fetch_assoc();
    $requests = array_sum($byType);
    $donations = (int) $row['total'];
    return ['requests' => $requests, 'donations' => $donations, 'total' => $requests + $donations, 'byType' => $byType];
}

/** Donations newest first, optionally only those submitted from/to (Y-m-d, inclusive). */
function ps_fetch_donations(mysqli $conn, array $filters = []) {
    $sql = 'SELECT id, reference_no, contact_number, contact_email, donor_name, amount, purpose, proof_of_payment, status, remarks, details, created_at'
        . ' FROM ' . PS_DONATION_TABLE;
    $clauses = [];
    $params = [];
    if (isset($filters['from'])) {
        $clauses[] = 'DATE(created_at) >= ?';
        $params[] = $filters['from'];
    }
    if (isset($filters['to'])) {
        $clauses[] = 'DATE(created_at) <= ?';
        $params[] = $filters['to'];
    }
    if ($clauses) {
        $sql .= ' WHERE ' . implode(' AND ', $clauses);
    }
    return ps_query_all($conn, $sql . ' ORDER BY created_at DESC, id DESC', $params);
}

/** request_documents as [type][request id][label] => ['id', 'received', 'file', 'name']; optionally for one type, or one request. */
function ps_fetch_documents(mysqli $conn, $type = null, $id = null) {
    $sql = 'SELECT id, request_type, request_id, document_label, received, file_path, original_name FROM request_documents';
    if ($type !== null && $id !== null) {
        $rows = ps_query_all($conn, $sql . ' WHERE request_type = ? AND request_id = ?', [$type, (string) $id]);
    } elseif ($type !== null) {
        $rows = ps_query_all($conn, $sql . ' WHERE request_type = ?', [$type]);
    } else {
        $rows = ps_query_all($conn, $sql);
    }
    $documents = [];
    foreach ($rows as $doc) {
        $documents[$doc['request_type']][(int) $doc['request_id']][$doc['document_label']] = [
            'id'       => (int) $doc['id'],
            'received' => (bool) $doc['received'],
            'file'     => $doc['file_path'],
            'name'     => (string) $doc['original_name'],
        ];
    }
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

/** A request's row on its admin page, e.g. admin-wedding-requests.php?ref=WED-2026-0001. */
function ps_request_admin_url($type, $reference) {
    return PS_REQUEST_TYPES[$type]['page'] . '?ref=' . rawurlencode($reference);
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

    $events = [];
    foreach (ps_query_all($conn, implode(' UNION ALL ', $selects) . ' ORDER BY event_date, event_time', $params) as $row) {
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
    return $events;
}

// ---------------------------------------------------------------------
// Scheduling (see the header comment for what counts as a conflict)
// ---------------------------------------------------------------------

/** 'HH:MM' or 'HH:MM:SS' -> minutes after midnight, or null. */
function ps_time_minutes($time) {
    if (!is_string($time) || !preg_match('/^([01]\d|2[0-3]):([0-5]\d)(:[0-5]\d)?$/', $time, $m)) {
        return null;
    }
    return (int) $m[1] * 60 + (int) $m[2];
}

/** [start, end] minutes a booking occupies on its date, or null while it has no time. */
function ps_booking_window($type, $time, $end = null) {
    $from = ps_time_minutes($time);
    if ($from === null) {
        return null;
    }
    $to = ps_time_minutes($end);
    if ($to === null || $to <= $from) {
        $to = $from + PS_REQUEST_TYPES[$type]['minutes'];
    }
    return [$from, $to];
}

/** "9:00 AM" for minutes after midnight. */
function ps_minutes_label($minutes) {
    return date('g:i A', mktime(0, (int) $minutes, 0, 1, 1, 2000));
}

/** "9:00 AM – 11:00 AM", or "Time not set". */
function ps_window_label($window) {
    return $window ? ps_minutes_label($window[0]) . ' – ' . ps_minutes_label($window[1]) : 'Time not set';
}

/** A request's schedule as two cell labels: 'date' ("Oct 3, 2026") and 'time' (a window, or just the start for Mass Intentions). */
function ps_schedule_labels($type, $date, $time, $end = null) {
    if (!$date) {
        return ['date' => 'No date yet', 'time' => ''];
    }
    $window = ps_booking_window($type, $time, $end);
    return [
        'date' => date('M j, Y', strtotime($date)),
        'time' => PS_REQUEST_TYPES[$type]['resource'] === null
            ? ($window ? ps_minutes_label($window[0]) : 'Time not set')
            : ps_window_label($window),
    ];
}

/** Which schedule a booking takes a slot in: 'church', 'counseling', 'facility:<name>', or null (never clashes). */
function ps_booking_resource($type, $subtype = null) {
    $resource = PS_REQUEST_TYPES[$type]['resource'];
    return $resource === 'facility' ? 'facility:' . $subtype : $resource;
}

/** Whether two bookings in the same place on the same date clash. */
function ps_bookings_overlap($typeA, $windowA, $typeB, $windowB) {
    if (!$windowA || !$windowB) {
        return false;
    }
    if ($typeA === $typeB && PS_REQUEST_TYPES[$typeA]['group']) {
        return false;
    }
    return $windowA[0] < $windowB[1] && $windowB[0] < $windowA[1];
}

/**
 * Booked requests on $date in the same place as a $type request (for a
 * facility, the facility named $subtype), earliest first, each with its
 * 'type' and time 'window'. $exclude = [type, id] leaves out the request
 * being edited.
 */
function ps_bookings_on(mysqli $conn, $type, $date, $subtype = null, ?array $exclude = null) {
    $resource = PS_REQUEST_TYPES[$type]['resource'];
    if ($resource === null) {
        return [];
    }
    $booked = "'" . implode("', '", PS_BOOKED_STATUSES) . "'";
    $bookings = [];
    foreach (PS_REQUEST_TYPES as $key => $other) {
        if ($other['resource'] !== $resource) {
            continue;
        }
        $subtypeSql = $other['subtype'] ?? 'NULL';
        $end = $other['end'] ?? 'NULL';
        $sql = "SELECT id, reference_no, {$other['name']} AS name, {$subtypeSql} AS subtype, {$other['time']} AS event_time, {$end} AS event_end, status"
            . " FROM {$other['table']} WHERE {$other['date']} = ? AND status IN ({$booked})";
        $params = [$date];
        if ($resource === 'facility') {
            $sql .= " AND {$other['subtype']} = ?";
            $params[] = (string) $subtype;
        }
        foreach (ps_query_all($conn, $sql, $params) as $row) {
            if ($exclude !== null && $exclude[0] === $key && (int) $exclude[1] === (int) $row['id']) {
                continue;
            }
            $row['type'] = $key;
            $row['window'] = ps_booking_window($key, $row['event_time'], $row['event_end']);
            $bookings[] = $row;
        }
    }
    usort($bookings, fn($a, $b) => ($a['window'][0] ?? PHP_INT_MAX) <=> ($b['window'][0] ?? PHP_INT_MAX));
    return $bookings;
}

/** The bookings on $date that a $type request at $window would clash with. */
function ps_schedule_conflicts(mysqli $conn, $type, $date, array $window, $subtype = null, ?array $exclude = null) {
    return array_values(array_filter(
        ps_bookings_on($conn, $type, $date, $subtype, $exclude),
        fn($booking) => ps_bookings_overlap($type, $window, $booking['type'], $booking['window'])
    ));
}

/** A booking as staff see it in conflict lists, linked to its admin page. */
function ps_booking_summary(array $booking) {
    return [
        'reference' => $booking['reference_no'],
        'type'      => PS_REQUEST_TYPES[$booking['type']]['label'],
        'name'      => $booking['name'],
        'time'      => ps_window_label($booking['window']),
        'status'    => ps_status_label($booking['status']),
        'url'       => ps_request_admin_url($booking['type'], $booking['reference_no']),
    ];
}

/**
 * ps_fetch_requests() rows with 'window' set and 'conflicts' listing the
 * reference numbers of every other booked row in $rows each one clashes
 * with (the admin calendar).
 */
function ps_mark_conflicts(array $rows) {
    $rows = array_values($rows);
    foreach ($rows as &$row) {
        $row['window'] = ps_booking_window($row['type'], $row['event_time'], $row['event_end']);
        $row['conflicts'] = [];
    }
    unset($row);

    $count = count($rows);
    for ($i = 0; $i < $count; $i++) {
        $a = $rows[$i];
        $resource = ps_booking_resource($a['type'], $a['subtype']);
        if ($resource === null || !in_array($a['status'], PS_BOOKED_STATUSES, true)) {
            continue;
        }
        for ($j = $i + 1; $j < $count; $j++) {
            $b = $rows[$j];
            if ($b['event_date'] !== $a['event_date'] || !in_array($b['status'], PS_BOOKED_STATUSES, true)
                || ps_booking_resource($b['type'], $b['subtype']) !== $resource) {
                continue;
            }
            if (ps_bookings_overlap($a['type'], $a['window'], $b['type'], $b['window'])) {
                $rows[$i]['conflicts'][] = $b['reference_no'];
                $rows[$j]['conflicts'][] = $a['reference_no'];
            }
        }
    }
    return $rows;
}
