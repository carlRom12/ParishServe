<?php
/**
 * admin-donations.php
 * ---------------------------------------------------------------------
 * Donation verification: review the uploaded proof of payment, then move
 * a donation through the same status pipeline as every other request
 * table. Rows come from ps_fetch_donations(); the Update modal's Save
 * POSTs to admin-update-request.php with type=donation (same pipeline
 * rules as the request pages, and the donor is emailed about the change).
 * ---------------------------------------------------------------------
 */
require __DIR__ . '/includes/auth-guard.php';
require_once __DIR__ . '/includes/request-types.php';

$donations = ps_fetch_donations($conn);

$pageTitle = 'Donations';
$pageCss   = 'admin.css';
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
                    <?php foreach (PS_STATUS_OPTIONS as $s): ?>
                        <option value="<?php echo $s; ?>"><?php echo htmlspecialchars(ps_status_label($s)); ?></option>
                    <?php endforeach; ?>
                </select>
            </span>
        </div>

        <div class="admin-table" style="--admin-cols: 120px 1fr 110px 100px 120px 100px;">
            <div class="admin-table-head">
                <span>Reference</span><span>Donor / Purpose</span><span>Amount</span><span>Proof</span><span>Status</span><span></span>
            </div>
            <?php foreach ($donations as $d): ?>
                <?php
                // uploads/ is closed to the web; staff open the proof through admin-file.php.
                $proof = $d['proof_of_payment'] ? 'admin-file.php?donation=' . (int) $d['id'] : '';
                $amount = '₱' . number_format((float) $d['amount'], 2);
                ?>
                <div class="admin-row" data-admin-row data-status="<?php echo htmlspecialchars($d['status']); ?>" data-search="<?php echo htmlspecialchars(strtolower($d['reference_no'] . ' ' . $d['donor_name'])); ?>">
                    <span><?php echo htmlspecialchars($d['reference_no']); ?></span>
                    <span class="admin-cell-name">
                        <strong><?php echo htmlspecialchars($d['donor_name']); ?></strong>
                        <small><?php echo htmlspecialchars((string) $d['purpose']); ?></small>
                    </span>
                    <span><?php echo htmlspecialchars($amount); ?></span>
                    <span>
                        <?php if ($proof): ?>
                            <a class="admin-proof-link" href="<?php echo htmlspecialchars($proof); ?>" target="_blank" rel="noopener"><?php ps_icon('photo'); ?> View</a>
                        <?php else: ?>
                            <span class="admin-proof-none">No proof uploaded</span>
                        <?php endif; ?>
                    </span>
                    <span class="ps-status is-<?php echo htmlspecialchars($d['status']); ?>" data-row-status><?php echo htmlspecialchars(ps_status_label($d['status'])); ?></span>
                    <span class="admin-cell-actions">
                        <button type="button" class="ps-btn ps-btn-outline" data-modal-trigger="donationModal"
                            data-type="donation"
                            data-id="<?php echo (int) $d['id']; ?>"
                            data-reference="<?php echo htmlspecialchars($d['reference_no']); ?>"
                            data-name="<?php echo htmlspecialchars($d['donor_name'] . ' — ' . $amount); ?>"
                            data-status="<?php echo htmlspecialchars($d['status']); ?>"
                            data-allowed-statuses="<?php echo htmlspecialchars(implode(',', ps_next_statuses($d['status']))); ?>"
                            data-remarks="<?php echo htmlspecialchars((string) $d['remarks']); ?>"
                            data-proof="<?php echo htmlspecialchars($proof); ?>"
                            data-details='<?php echo htmlspecialchars(json_encode(ps_request_details($d['details'])), ENT_QUOTES); ?>'>
                            Update
                        </button>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="admin-empty" data-admin-empty<?php echo $donations ? ' hidden' : ''; ?>><?php ps_icon('heart'); ?><p><?php echo $donations ? 'No donations match these filters.' : 'No donations have been recorded yet.'; ?></p></div>
    </div>

</main>

<div class="ps-modal-overlay" id="donationModal" data-modal hidden>
    <div class="ps-modal-card" role="dialog" aria-modal="true" aria-labelledby="donationModalTitle">
        <button type="button" class="ps-modal-close" data-modal-close aria-label="Close"><?php ps_icon('close'); ?></button>
        <h2 class="ps-modal-title" id="donationModalTitle" data-modal-field="reference"></h2>
        <p class="ps-modal-sub" data-modal-field="name"></p>
        <form data-admin-form="admin-update-request.php">
            <input type="hidden" name="type" data-modal-field="type">
            <input type="hidden" name="id" data-modal-field="id">
            <div class="ps-modal-field" data-modal-details-wrap hidden>
                <label>Submitted Details</label>
                <dl class="admin-detail-list" data-modal-details></dl>
            </div>
            <div class="ps-modal-field">
                <label>Proof of Payment</label>
                <div class="admin-proof-preview" data-modal-proof-wrap hidden>
                    <a class="admin-doc-thumb-link" href="#" target="_blank" rel="noopener" data-modal-proof-link>
                        <img class="admin-thumb" src="data:," alt="Uploaded proof of payment" data-modal-proof-img>
                    </a>
                    <small class="admin-doc-hint">Open the full image to check the amount and reference before approving.</small>
                </div>
                <span class="admin-proof-none" data-modal-proof-empty>No proof uploaded</span>
            </div>
            <div class="ps-modal-field">
                <label for="donationModalStatus">Status</label>
                <span class="ps-select">
                    <select id="donationModalStatus" name="status" data-modal-field="status" aria-describedby="donationModalStatusHint">
                        <?php foreach (PS_STATUS_OPTIONS as $s): ?>
                            <option value="<?php echo $s; ?>"><?php echo htmlspecialchars(ps_status_label($s)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </span>
                <small class="admin-status-hint" id="donationModalStatusHint" data-modal-status-hint></small>
            </div>
            <div class="ps-modal-field">
                <label for="donationModalRemarks">Remarks</label>
                <textarea id="donationModalRemarks" name="remarks" rows="3" maxlength="2000" data-modal-field="remarks" placeholder="Optional note, visible internally only"></textarea>
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

<?php require __DIR__ . '/includes/footer.php'; ?>
