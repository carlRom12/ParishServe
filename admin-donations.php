<?php
/**
 * admin-donations.php
 * ---------------------------------------------------------------------
 * Donation verification: review the uploaded proof of payment, then move
 * a donation between Under Review, Approved and Rejected -- any of the
 * three, at any time (ps_donation_next_statuses(); nothing is final here).
 * Oldest first, so the list reads as the queue; it opens filtered to Under Review.
 * Rows come from ps_fetch_donations(); the Update modal's Save
 * POSTs to admin-update-request.php with type=donation (which enforces
 * that pipeline, and the donor is emailed about the change).
 * ---------------------------------------------------------------------
 */
require __DIR__ . '/includes/auth-guard.php';
require_once __DIR__ . '/includes/request-types.php';

$donations = ps_fetch_donations($conn, ['order' => 'queue']);

$pageTitle = 'Donations';
$pageCss   = 'admin.css?v=3';
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
            <span class="ps-search"><?php ps_icon('search'); ?><input type="text" placeholder="Search donations" aria-label="Search by donation no., donor, purpose or GCash no." data-admin-search></span>
            <span class="ps-select admin-dn-status">
                <select data-admin-status-select aria-label="Filter by status">
                    <option value="">All statuses</option>
                    <?php foreach (PS_DONATION_STATUS_OPTIONS as $s): ?>
                        <option value="<?php echo $s; ?>"<?php echo $s === 'under_review' ? ' selected' : ''; ?>><?php echo htmlspecialchars(ps_status_label($s)); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php ps_icon('chevron-down'); ?>
            </span>
        </div>

        <div class="admin-dn-table-wrap">
        <table class="admin-dn-table">
            <caption>Donations in queue order, oldest first</caption>
            <thead>
                <tr>
                    <th scope="col">Donation Number</th>
                    <th scope="col">Date Submitted</th>
                    <th scope="col">Donor</th>
                    <th scope="col">Donation Purpose</th>
                    <th scope="col">Amount</th>
                    <th scope="col">GCash Reference Number</th>
                    <th scope="col">Status</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($donations as $d): ?>
                <?php
                // uploads/ is closed to the web; staff open the proof through admin-file.php.
                $proof = $d['proof_of_payment'] ? 'admin-file.php?donation=' . (int) $d['id'] : '';
                $amount = '₱' . number_format((float) $d['amount'], 2);
                $search = implode(' ', [$d['donation_no'], $d['donor_name'], $d['purpose'], $d['gcash_reference']]);
                ?>
                <tr data-admin-row data-status="<?php echo htmlspecialchars($d['status']); ?>" data-search="<?php echo htmlspecialchars(mb_strtolower($search)); ?>">
                    <td class="admin-dn-number"><?php echo htmlspecialchars($d['donation_no']); ?></td>
                    <td class="admin-dn-nowrap"><?php echo htmlspecialchars(date('M j, Y', strtotime($d['created_at']))); ?></td>
                    <td><?php echo htmlspecialchars($d['donor_name']); ?></td>
                    <td><?php echo htmlspecialchars((string) $d['purpose']); ?></td>
                    <td class="admin-dn-amount"><?php echo htmlspecialchars($amount); ?></td>
                    <td class="admin-gcash-ref"><?php echo $d['gcash_reference'] ? htmlspecialchars($d['gcash_reference']) : '<span class="admin-dn-muted">&mdash;</span>'; ?></td>
                    <td><span class="ps-status is-<?php echo htmlspecialchars($d['status']); ?>" data-row-status><?php echo htmlspecialchars(ps_status_label($d['status'])); ?></span></td>
                    <td>
                        <button type="button" class="ps-btn ps-btn-outline admin-dn-update" data-modal-trigger="donationModal"
                            data-type="donation"
                            data-id="<?php echo (int) $d['id']; ?>"
                            data-reference="Donation No. <?php echo htmlspecialchars($d['donation_no']); ?>"
                            data-gcash="<?php echo htmlspecialchars($d['gcash_reference'] ?: 'Not given'); ?>"
                            data-name="<?php echo htmlspecialchars($d['donor_name'] . ' — ' . $amount); ?>"
                            data-status="<?php echo htmlspecialchars($d['status']); ?>"
                            data-allowed-statuses="<?php echo htmlspecialchars(implode(',', ps_donation_next_statuses($d['status']))); ?>"
                            data-remarks="<?php echo htmlspecialchars((string) $d['remarks']); ?>"
                            data-proof="<?php echo htmlspecialchars($proof); ?>"
                            data-details='<?php echo htmlspecialchars(json_encode(ps_request_details($d['details'])), ENT_QUOTES); ?>'>
                            Update
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
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
                <label>GCash Reference Number</label>
                <p class="admin-gcash-ref" data-modal-field="gcash"></p>
            </div>
            <div class="ps-modal-field">
                <label>Proof of Payment</label>
                <div class="admin-proof-preview" data-modal-proof-wrap hidden>
                    <a class="admin-doc-thumb-link" href="#" target="_blank" rel="noopener" data-modal-proof-link>
                        <img class="admin-thumb" src="data:," alt="Uploaded proof of payment" data-modal-proof-img>
                    </a>
                    <small class="admin-doc-hint">Open the full image to check the amount and that its reference number matches the one above before approving.</small>
                </div>
                <span class="admin-proof-none" data-modal-proof-empty>No proof uploaded</span>
            </div>
            <div class="ps-modal-field">
                <label for="donationModalStatus">Status</label>
                <span class="ps-select">
                    <select id="donationModalStatus" name="status" data-modal-field="status" aria-describedby="donationModalStatusHint">
                        <?php foreach (PS_DONATION_STATUS_OPTIONS as $s): ?>
                            <option value="<?php echo $s; ?>"><?php echo htmlspecialchars(ps_status_label($s)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </span>
                <small class="admin-status-hint" id="donationModalStatusHint" data-modal-status-hint="free"></small>
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
