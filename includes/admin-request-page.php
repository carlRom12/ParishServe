<?php
/**
 * admin-request-page.php
 * ---------------------------------------------------------------------
 * The admin page for one request type. Each of the 7 pages named in
 * PS_REQUEST_TYPES[...]['page'] (admin-wedding-requests.php, ...) sets
 * $requestType and requires this, so they share one layout.
 *
 * Rows come from ps_fetch_requests() for that type and the document
 * checklist from request_documents (includes/request-types.php). The
 * status filter and search run client-side (initAdminTableFilters() in
 * main.js); ?ref=<reference> pre-fills the search, which is how the
 * dashboard and the admin calendar link to one request.
 *
 * The Update window's Save POSTs to admin-update-request.php: status
 * (next pipeline step or Rejected), remarks, received documents and the
 * schedule. While staff change the date/time, admin-schedule-check.php
 * lists that day's other bookings and flags any overlap; the save runs
 * the same check again and emails the parishioner once it's committed.
 * ---------------------------------------------------------------------
 */
require __DIR__ . '/auth-guard.php';
require_once __DIR__ . '/request-types.php';

if (!isset($requestType, PS_REQUEST_TYPES[$requestType])) {
    http_response_code(404);
    exit('Unknown request type.');
}

$typeInfo  = PS_REQUEST_TYPES[$requestType];
$requests  = ps_fetch_requests($conn, ['type' => $requestType]);
$hasDocs   = isset(PS_DOCUMENT_CHECKLISTS[$requestType]);
$documents = $hasDocs ? ps_fetch_documents($conn, $requestType) : [];
$search    = trim((string) ($_GET['ref'] ?? ''));
$noun      = strtolower($typeInfo['label']) . ' requests';

if ($typeInfo['resource'] === null) {
    $scheduleHint = 'Needed before approving. Mass intentions are offered at a regular Mass, so they never conflict with other bookings.';
} else {
    $minutes = $typeInfo['minutes'];
    $length = $minutes % 60 === 0 ? ($minutes / 60) . ' hour' . ($minutes > 60 ? 's' : '') : "{$minutes} minutes";
    $place = $typeInfo['resource'] === 'church' ? 'the church' : 'the counseling office';
    $scheduleHint = 'Needed before approving. A ' . strtolower($typeInfo['label']) . " takes about {$length} and can't overlap another booking in {$place}"
        . ($typeInfo['group'] ? ' (other ' . strtolower($typeInfo['plural']) . ' can share its time).' : '.');
}

$pageTitle = $typeInfo['plural'];
$pageCss   = 'admin.css';
$activeNav = $requestType;
require __DIR__ . '/header.php';
require __DIR__ . '/admin-sidebar.php';
?>
<main class="ps-main">

    <section class="ps-plain-header" style="position:relative;">
        <div>
            <div class="ps-heading-ornament"><span></span><?php ps_icon($typeInfo['icon']); ?><span></span></div>
            <h1><?php echo htmlspecialchars($typeInfo['plural']); ?></h1>
            <p>Review, schedule and update <?php echo htmlspecialchars($noun); ?>.</p>
        </div>
        <?php require __DIR__ . '/topbar.php'; ?>
    </section>

    <div class="ps-card">
        <div class="admin-toolbar">
            <span class="ps-search"><?php ps_icon('search'); ?><input type="text" placeholder="Search reference or name" value="<?php echo htmlspecialchars($search); ?>" data-admin-search></span>
            <span class="ps-select">
                <select data-admin-status-select>
                    <option value="">All statuses</option>
                    <?php foreach (PS_STATUS_OPTIONS as $s): ?>
                        <option value="<?php echo $s; ?>"><?php echo htmlspecialchars(ps_status_label($s)); ?></option>
                    <?php endforeach; ?>
                </select>
            </span>
        </div>

        <div class="admin-table" style="--admin-cols: 130px 1fr 170px <?php echo $hasDocs ? '100px ' : ''; ?>120px 100px;">
            <div class="admin-table-head">
                <span>Reference</span><span>Name</span><span>Schedule</span><?php echo $hasDocs ? '<span>Documents</span>' : ''; ?><span>Status</span><span></span>
            </div>
            <?php foreach ($requests as $r): ?>
                <?php
                // 'file' opens whatever the parishioner uploaded for that
                // document (via admin-file.php), so admin isn't trusting a
                // bare checkbox. The badge counts required documents only.
                $docItems = $hasDocs ? ps_document_items($requestType, $documents[$requestType][(int) $r['id']] ?? []) : [];
                $docSummary = ps_document_summary($docItems);
                $schedule = ps_schedule_labels($requestType, $r['event_date'], $r['event_time'], $r['event_end']);
                $subline = (string) $r['subtype'] !== '' ? $r['subtype'] : 'Submitted ' . date('M j, Y', strtotime($r['created_at']));
                ?>
                <div class="admin-row" data-admin-row data-type="<?php echo htmlspecialchars($requestType); ?>" data-status="<?php echo htmlspecialchars($r['status']); ?>" data-search="<?php echo htmlspecialchars(strtolower($r['reference_no'] . ' ' . $r['name'])); ?>">
                    <span><?php echo htmlspecialchars($r['reference_no']); ?></span>
                    <span class="admin-cell-name">
                        <strong><?php echo htmlspecialchars($r['name']); ?></strong>
                        <small><?php echo htmlspecialchars($subline); ?></small>
                    </span>
                    <span class="admin-cell-name">
                        <strong data-row-field="scheduleDate"><?php echo htmlspecialchars($schedule['date']); ?></strong>
                        <small data-row-field="scheduleTime"><?php echo htmlspecialchars($schedule['time']); ?></small>
                    </span>
                    <?php if ($hasDocs): ?>
                        <span>
                            <span class="admin-doc-count<?php echo $docSummary['complete'] ? ' is-complete' : ''; ?>" data-row-docs>
                                <?php ps_icon('document', 'admin-doc-icon-pending'); ?><?php ps_icon('check-circle', 'admin-doc-icon-complete'); ?>
                                <span data-row-field="docs"><?php echo $docSummary['received']; ?>/<?php echo $docSummary['total']; ?></span>
                            </span>
                        </span>
                    <?php endif; ?>
                    <span class="ps-status is-<?php echo htmlspecialchars($r['status']); ?>" data-row-status><?php echo htmlspecialchars(ps_status_label($r['status'])); ?></span>
                    <span class="admin-cell-actions">
                        <button type="button" class="ps-btn ps-btn-outline" data-modal-trigger="requestModal"
                            data-type="<?php echo htmlspecialchars($requestType); ?>"
                            data-id="<?php echo (int) $r['id']; ?>"
                            data-reference="<?php echo htmlspecialchars($r['reference_no']); ?>"
                            data-name="<?php echo htmlspecialchars($r['name']); ?>"
                            data-status="<?php echo htmlspecialchars($r['status']); ?>"
                            data-allowed-statuses="<?php echo htmlspecialchars(implode(',', ps_next_statuses($r['status']))); ?>"
                            data-remarks="<?php echo htmlspecialchars((string) $r['remarks']); ?>"
                            data-date="<?php echo htmlspecialchars((string) $r['event_date']); ?>"
                            data-time="<?php echo htmlspecialchars(substr((string) $r['event_time'], 0, 5)); ?>"
                            data-end-time="<?php echo htmlspecialchars(substr((string) $r['event_end'], 0, 5)); ?>"
                            data-details='<?php echo htmlspecialchars(json_encode(ps_request_details($r['details'])), ENT_QUOTES); ?>'
                            <?php if ($docItems): ?>data-docs='<?php echo htmlspecialchars(json_encode($docItems), ENT_QUOTES); ?>'<?php endif; ?>>
                            Update
                        </button>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="admin-empty" data-admin-empty<?php echo $requests ? ' hidden' : ''; ?>><?php ps_icon($typeInfo['icon']); ?><p><?php echo htmlspecialchars($requests ? 'No requests match these filters.' : "No {$noun} have been submitted yet."); ?></p></div>
    </div>

</main>

<div class="ps-modal-overlay" id="requestModal" data-modal hidden>
    <div class="ps-modal-card" role="dialog" aria-modal="true" aria-labelledby="requestModalTitle">
        <button type="button" class="ps-modal-close" data-modal-close aria-label="Close"><?php ps_icon('close'); ?></button>
        <h2 class="ps-modal-title" id="requestModalTitle" data-modal-field="reference"></h2>
        <p class="ps-modal-sub" data-modal-field="name"></p>
        <form data-admin-form="admin-update-request.php">
            <input type="hidden" name="type" data-modal-field="type">
            <input type="hidden" name="id" data-modal-field="id">
            <div class="ps-modal-field" data-modal-details-wrap hidden>
                <label>Submitted Details</label>
                <dl class="admin-detail-list" data-modal-details></dl>
            </div>
            <?php if ($hasDocs): ?>
                <div class="ps-modal-field" data-modal-docs-wrap hidden>
                    <label>Required Documents</label>
                    <div class="admin-doc-list" data-modal-docs></div>
                    <small class="admin-doc-hint">Tracks what's been received -- final verification still happens on-site.</small>
                </div>
            <?php endif; ?>
            <?php if ($requestType === 'wedding'): ?>
                <div class="ps-modal-field" data-modal-seminar-wrap hidden>
                    <div class="ps-info-banner is-tip">
                        <?php ps_icon('info'); ?>
                        <span>All documents are in. Once you approve this request, the bride and groom will be notified to choose their own Pre-Cana Seminar date (1st or 3rd Saturday) -- that choice is theirs, not made here.</span>
                    </div>
                </div>
            <?php endif; ?>
            <fieldset class="ps-modal-field admin-schedule" data-modal-schedule="<?php echo $typeInfo['resource'] === null ? '' : 'admin-schedule-check.php'; ?>">
                <legend>Schedule</legend>
                <div class="admin-schedule-fields<?php echo $typeInfo['end'] ? ' has-end' : ''; ?>">
                    <label>Date <input type="date" name="event_date" data-modal-field="date"></label>
                    <label>Start time <input type="time" name="event_time" data-modal-field="time"></label>
                    <?php if ($typeInfo['end']): ?>
                        <label>End time <input type="time" name="event_end" data-modal-field="endTime"></label>
                    <?php endif; ?>
                </div>
                <small class="admin-status-hint"><?php echo htmlspecialchars($scheduleHint); ?></small>
                <div class="admin-schedule-result" data-schedule-result role="status" aria-live="polite" hidden></div>
            </fieldset>
            <div class="ps-modal-field">
                <label for="modalStatus">Status</label>
                <span class="ps-select">
                    <select id="modalStatus" name="status" data-modal-field="status" aria-describedby="modalStatusHint">
                        <?php foreach (PS_STATUS_OPTIONS as $s): ?>
                            <option value="<?php echo $s; ?>"><?php echo htmlspecialchars(ps_status_label($s)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </span>
                <small class="admin-status-hint" id="modalStatusHint" data-modal-status-hint></small>
            </div>
            <div class="ps-modal-field">
                <label for="modalRemarks">Remarks</label>
                <textarea id="modalRemarks" name="remarks" rows="3" maxlength="2000" data-modal-field="remarks" placeholder="Optional note, visible internally only"></textarea>
            </div>
            <div class="admin-modal-error" data-modal-error role="alert" hidden></div>
            <div class="ps-modal-actions">
                <button type="button" class="ps-btn ps-btn-outline" data-modal-close>Cancel</button>
                <button type="submit" class="ps-btn ps-btn-primary"><span data-submit-label>Save</span></button>
            </div>
        </form>
    </div>
</div>

<div class="ps-toast" data-toast role="status" aria-live="polite" hidden><?php ps_icon('check-circle'); ?> <span data-toast-text></span></div>

<?php require __DIR__ . '/footer.php'; ?>
