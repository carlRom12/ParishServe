<?php
/**
 * admin-requests.php
 * ---------------------------------------------------------------------
 * Unified request management across all 7 sacrament/service tables --
 * one page instead of 7 near-identical ones, per schema.sql's own group
 * notes ("every service/request table shares the SAME status pipeline
 * so the dashboard can UNION them together in one query").
 *
 * FRONTEND ONLY this pass: $requests below is hardcoded to mirror
 * database/schema.sql's seed rows exactly. Filtering (type tabs, status
 * select, search) runs client-side against these rows via
 * initAdminTableFilters() in main.js. The "Update" modal's Save button
 * is a cosmetic preview only (updates the row's status pill on screen,
 * see initAdminModals() in main.js) -- nothing is persisted anywhere
 * until a real backend/UPDATE query replaces this array.
 * ---------------------------------------------------------------------
 */

$typeLabels = [
    'wedding'       => 'Wedding',
    'baptism'       => 'Baptism',
    'confirmation'  => 'Confirmation',
    'funeral'       => 'Funeral',
    'counseling'    => 'Counseling',
    'massintention' => 'Mass Intention',
    'facility'      => 'Facility Reservation',
];

$statusOptions = ['submitted', 'under_review', 'approved', 'scheduled', 'completed', 'rejected'];
$statusLabels = [
    'submitted' => 'Submitted', 'under_review' => 'Under Review', 'approved' => 'Approved',
    'scheduled' => 'Scheduled', 'completed' => 'Completed', 'rejected' => 'Rejected',
];

// Required documents per sacrament, per the paper's Objectives (church
// staff review/approve requests) and Scope & Limitations (final
// document verification happens on-site, not through the system --
// this checklist just tracks what's been received so far, it doesn't
// replace the on-site verification itself). Only sacrament requests
// get a checklist; Counseling/Mass Intention/Facility Reservation
// don't carry documents in the paper's scope.
$documentChecklists = [
    'wedding'      => ['Baptismal Certificate (Bride)', 'Baptismal Certificate (Groom)', 'Confirmation Certificate (Bride)', 'Confirmation Certificate (Groom)', 'CENOMAR', 'Marriage License'],
    'baptism'      => ["Child's Birth Certificate", "Parents' Marriage Certificate"],
    'confirmation' => ['Baptismal Certificate', 'Confirmation Class Certificate'],
    'funeral'      => ['Death Certificate', 'Burial Permit'],
];

// Mirrors database/schema.sql's seed rows across the 7 request tables.
// 'docs' (sacrament rows only) mirrors the order of $documentChecklists
// for that type -- true where that document has already come in.
$requests = [
    ['id' => 1, 'type' => 'wedding',       'ref' => 'WED-2026-0001', 'name' => 'Maria Santos & Juan Dela Cruz', 'date' => 'Oct 10, 2026', 'status' => 'under_review', 'docs' => [true, true, true, false, false, false]],
    ['id' => 2, 'type' => 'baptism',       'ref' => 'BAP-2026-0001', 'name' => 'Baby Sofia Dela Cruz',           'date' => 'Sep 5, 2026',  'status' => 'approved',     'docs' => [true, true]],
    ['id' => 3, 'type' => 'baptism',       'ref' => 'BAP-2026-0002', 'name' => 'Baby Gabriel Reyes',             'date' => 'Sep 20, 2026', 'status' => 'submitted',    'docs' => [true, false]],
    ['id' => 4, 'type' => 'confirmation',  'ref' => 'CNF-2026-0001', 'name' => 'Miguel Dela Cruz',                'date' => 'Nov 2, 2026',  'status' => 'scheduled',    'docs' => [true, true]],
    ['id' => 5, 'type' => 'funeral',       'ref' => 'FUN-2026-0001', 'name' => 'Pedro Dela Cruz Sr.',             'date' => 'Jul 15, 2026', 'status' => 'completed',    'docs' => [true, true]],
    ['id' => 6, 'type' => 'counseling',    'ref' => 'CNS-2026-0001', 'name' => 'Juan Dela Cruz',                  'date' => 'Aug 22, 2026', 'status' => 'submitted'],
    ['id' => 7, 'type' => 'massintention', 'ref' => 'MI-2026-0001',  'name' => 'Dela Cruz Family',                'date' => 'Aug 18, 2026', 'status' => 'scheduled'],
    ['id' => 8, 'type' => 'facility',      'ref' => 'FAC-2026-0001', 'name' => 'Juan Dela Cruz',                  'date' => 'Sep 12, 2026', 'status' => 'approved'],
    ['id' => 9, 'type' => 'facility',      'ref' => 'FAC-2026-0002', 'name' => 'Angela Reyes',                    'date' => 'Sep 1, 2026',  'status' => 'under_review'],
];

$pageTitle = 'Requests';
$pageCss   = 'admin.css';
$userFirstName = 'Parish';
$userRole = 'Admin';
$activeNav = 'requests';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/admin-sidebar.php';
?>
<main class="ps-main">

    <section class="ps-plain-header" style="position:relative;">
        <div>
            <div class="ps-heading-ornament"><span></span><?php ps_icon('cross'); ?><span></span></div>
            <h1>Requests</h1>
            <p>Review and update sacrament and parish service requests.</p>
        </div>
        <?php require __DIR__ . '/includes/topbar.php'; ?>
    </section>

    <div class="ps-card">
        <div class="admin-toolbar" data-admin-type-tabs>
            <button type="button" class="ps-tab active" data-admin-type-tab="all">All</button>
            <?php foreach ($typeLabels as $key => $label): ?>
                <button type="button" class="ps-tab" data-admin-type-tab="<?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars($label); ?></button>
            <?php endforeach; ?>
        </div>
        <div class="admin-toolbar">
            <span class="ps-search"><?php ps_icon('search'); ?><input type="text" placeholder="Search reference or name" data-admin-search></span>
            <span class="ps-select">
                <select data-admin-status-select>
                    <option value="">All statuses</option>
                    <?php foreach ($statusOptions as $s): ?>
                        <option value="<?php echo $s; ?>"><?php echo htmlspecialchars($statusLabels[$s]); ?></option>
                    <?php endforeach; ?>
                </select>
            </span>
        </div>

        <div class="admin-table" style="--admin-cols: 120px 1fr 110px 100px 120px 100px;">
            <div class="admin-table-head">
                <span>Reference</span><span>Requester / Type</span><span>Date</span><span>Documents</span><span>Status</span><span></span>
            </div>
            <?php foreach ($requests as $r): ?>
                <?php
                // 'file' is a mock uploaded-photo path (parishioners already upload
                // per-category on wedding-request-step2.php's Requirements step,
                // one <input type="file"> per document -- this just previews
                // whatever came in through that upload, per document, instead of
                // admin having to trust a bare checkbox).
                $docItems = [];
                if (isset($documentChecklists[$r['type']])) {
                    foreach ($documentChecklists[$r['type']] as $i => $label) {
                        $checked = $r['docs'][$i] ?? false;
                        $docItems[] = ['label' => $label, 'checked' => $checked, 'file' => $checked ? 'assets/images/document-tray.svg' : null];
                    }
                }
                $docsReceived = count(array_filter($docItems, fn($d) => $d['checked']));
                $docsTotal = count($docItems);
                ?>
                <div class="admin-row" data-admin-row data-type="<?php echo htmlspecialchars($r['type']); ?>" data-status="<?php echo htmlspecialchars($r['status']); ?>" data-search="<?php echo htmlspecialchars(strtolower($r['ref'] . ' ' . $r['name'])); ?>">
                    <span><?php echo htmlspecialchars($r['ref']); ?></span>
                    <span class="admin-cell-name">
                        <strong><?php echo htmlspecialchars($r['name']); ?></strong>
                        <small><?php echo htmlspecialchars($typeLabels[$r['type']]); ?></small>
                    </span>
                    <span><?php echo htmlspecialchars($r['date']); ?></span>
                    <span>
                        <?php if ($docsTotal > 0): ?>
                            <span class="admin-doc-count<?php echo $docsReceived === $docsTotal ? ' is-complete' : ''; ?>">
                                <?php ps_icon($docsReceived === $docsTotal ? 'check-circle' : 'document'); ?>
                                <?php echo $docsReceived; ?>/<?php echo $docsTotal; ?>
                            </span>
                        <?php else: ?>
                            <span class="admin-proof-none">—</span>
                        <?php endif; ?>
                    </span>
                    <span class="ps-status is-<?php echo htmlspecialchars($r['status']); ?>" data-row-status><?php echo htmlspecialchars($statusLabels[$r['status']]); ?></span>
                    <span class="admin-cell-actions">
                        <button type="button" class="ps-btn ps-btn-outline" data-modal-trigger="requestModal"
                            data-reference="<?php echo htmlspecialchars($r['ref']); ?>"
                            data-name="<?php echo htmlspecialchars($r['name']); ?>"
                            data-status="<?php echo htmlspecialchars($r['status']); ?>"
                            <?php if ($docItems): ?>data-docs='<?php echo htmlspecialchars(json_encode($docItems), ENT_QUOTES); ?>'<?php endif; ?>>
                            Update
                        </button>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="admin-empty" data-admin-empty hidden><?php ps_icon('document'); ?><p>No requests match these filters.</p></div>
    </div>

</main>

<div class="ps-modal-overlay" id="requestModal" data-modal hidden>
    <div class="ps-modal-card">
        <button type="button" class="ps-modal-close" data-modal-close><?php ps_icon('close'); ?></button>
        <h2 class="ps-modal-title" data-modal-field="reference"></h2>
        <p class="ps-modal-sub" data-modal-field="name"></p>
        <form data-mock-form="Request updated. (Design preview only -- not connected to a database.)">
            <div class="ps-modal-field" data-modal-docs-wrap hidden>
                <label>Required Documents</label>
                <div class="admin-doc-list" data-modal-docs></div>
                <small class="admin-doc-hint">Tracks what's been received -- final verification still happens on-site.</small>
            </div>
            <div class="ps-modal-field" data-modal-seminar-wrap hidden>
                <div class="ps-info-banner is-tip">
                    <?php ps_icon('info'); ?>
                    <span>All documents are in. Once you approve this request, the bride and groom will be notified to choose their own Pre-Cana Seminar date (1st or 3rd Saturday) -- that choice is theirs, not made here.</span>
                </div>
            </div>
            <div class="ps-modal-field">
                <label for="modalStatus">Status</label>
                <span class="ps-select">
                    <select id="modalStatus" name="status" data-modal-field="status">
                        <?php foreach ($statusOptions as $s): ?>
                            <option value="<?php echo $s; ?>"><?php echo htmlspecialchars($statusLabels[$s]); ?></option>
                        <?php endforeach; ?>
                    </select>
                </span>
            </div>
            <div class="ps-modal-actions">
                <button type="button" class="ps-btn ps-btn-outline" data-modal-close>Cancel</button>
                <button type="submit" class="ps-btn ps-btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<div class="ps-toast" data-toast hidden><?php ps_icon('check-circle'); ?> <span data-toast-text></span></div>

<?php require __DIR__ . '/includes/footer.php'; ?>
