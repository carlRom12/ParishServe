<?php
/**
 * admin-donations.php
 * ---------------------------------------------------------------------
 * Donation verification: review uploaded proof of payment and move a
 * donation through the same status pipeline as every other request
 * table. FRONTEND ONLY this pass -- see admin-requests.php's docblock
 * for the same note; $donations below is hardcoded (the one seed row
 * from database/schema.sql plus a few extra sample rows so the table
 * doesn't look empty for a design preview).
 * ---------------------------------------------------------------------
 */

$statusOptions = ['submitted', 'under_review', 'approved', 'scheduled', 'completed', 'rejected'];
$statusLabels = [
    'submitted' => 'Submitted', 'under_review' => 'Under Review', 'approved' => 'Approved',
    'scheduled' => 'Scheduled', 'completed' => 'Completed', 'rejected' => 'Rejected',
];

$donations = [
    ['id' => 1, 'ref' => 'DON-2026-0001', 'donor' => 'Juan Dela Cruz', 'amount' => 500.00,  'purpose' => 'General fund',   'proof' => null, 'status' => 'completed',    'date' => 'Aug 1, 2026'],
    ['id' => 2, 'ref' => 'DON-2026-0002', 'donor' => 'Maria Santos',   'amount' => 1500.00, 'purpose' => 'Church repair',  'proof' => 'assets/images/gcash-qr-placeholder.svg', 'status' => 'under_review', 'date' => 'Aug 18, 2026'],
    ['id' => 3, 'ref' => 'DON-2026-0003', 'donor' => 'Angela Reyes',   'amount' => 250.00,  'purpose' => 'Outreach program', 'proof' => null, 'status' => 'submitted',  'date' => 'Aug 20, 2026'],
    ['id' => 4, 'ref' => 'DON-2026-0004', 'donor' => 'Pedro Ramos',    'amount' => 1000.00, 'purpose' => 'General fund',   'proof' => 'assets/images/gcash-qr-placeholder.svg', 'status' => 'approved',    'date' => 'Aug 21, 2026'],
];

$pageTitle = 'Donations';
$pageCss   = 'admin.css';
$userFirstName = 'Parish';
$userRole = 'Admin';
$activeNav = 'donations';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/admin-sidebar.php';
?>
<main class="ps-main">

    <section class="ps-plain-header" style="position:relative;">
        <div>
            <div class="ps-heading-ornament"><span></span><?php ps_icon('cross'); ?><span></span></div>
            <h1>Donations</h1>
            <p>Verify uploaded proof of payment and update donation status.</p>
        </div>
        <?php require __DIR__ . '/includes/topbar.php'; ?>
    </section>

    <div class="ps-card">
        <div class="admin-toolbar">
            <span class="ps-search"><?php ps_icon('search'); ?><input type="text" placeholder="Search reference or donor" data-admin-search></span>
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
                <span>Reference</span><span>Donor / Purpose</span><span>Amount</span><span>Proof</span><span>Status</span><span></span>
            </div>
            <?php foreach ($donations as $d): ?>
                <div class="admin-row" data-admin-row data-status="<?php echo htmlspecialchars($d['status']); ?>" data-search="<?php echo htmlspecialchars(strtolower($d['ref'] . ' ' . $d['donor'])); ?>">
                    <span><?php echo htmlspecialchars($d['ref']); ?></span>
                    <span class="admin-cell-name">
                        <strong><?php echo htmlspecialchars($d['donor']); ?></strong>
                        <small><?php echo htmlspecialchars($d['purpose']); ?></small>
                    </span>
                    <span>&#8369;<?php echo number_format($d['amount'], 2); ?></span>
                    <span>
                        <?php if ($d['proof']): ?>
                            <a class="admin-proof-link" href="<?php echo htmlspecialchars($d['proof']); ?>" target="_blank" rel="noopener"><?php ps_icon('photo'); ?> View</a>
                        <?php else: ?>
                            <span class="admin-proof-none">No proof uploaded</span>
                        <?php endif; ?>
                    </span>
                    <span class="ps-status is-<?php echo htmlspecialchars($d['status']); ?>" data-row-status><?php echo htmlspecialchars($statusLabels[$d['status']]); ?></span>
                    <span class="admin-cell-actions">
                        <button type="button" class="ps-btn ps-btn-outline" data-modal-trigger="donationModal"
                            data-reference="<?php echo htmlspecialchars($d['ref']); ?>"
                            data-name="<?php echo htmlspecialchars($d['donor'] . ' — ₱' . number_format($d['amount'], 2)); ?>"
                            data-status="<?php echo htmlspecialchars($d['status']); ?>">
                            Update
                        </button>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="admin-empty" data-admin-empty hidden><?php ps_icon('heart'); ?><p>No donations match these filters.</p></div>
    </div>

</main>

<div class="ps-modal-overlay" id="donationModal" data-modal hidden>
    <div class="ps-modal-card">
        <button type="button" class="ps-modal-close" data-modal-close><?php ps_icon('close'); ?></button>
        <h2 class="ps-modal-title" data-modal-field="reference"></h2>
        <p class="ps-modal-sub" data-modal-field="name"></p>
        <form data-mock-form="Donation updated. (Design preview only -- not connected to a database.)">
            <div class="ps-modal-field">
                <label for="donationModalStatus">Status</label>
                <span class="ps-select">
                    <select id="donationModalStatus" name="status" data-modal-field="status">
                        <?php foreach ($statusOptions as $s): ?>
                            <option value="<?php echo $s; ?>"><?php echo htmlspecialchars($statusLabels[$s]); ?></option>
                        <?php endforeach; ?>
                    </select>
                </span>
            </div>
            <div class="ps-modal-field">
                <label for="donationModalRemarks">Remarks</label>
                <textarea id="donationModalRemarks" name="remarks" rows="3" placeholder="Optional note, visible internally only"></textarea>
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
