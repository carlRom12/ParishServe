<?php
/**
 * admin-accounts.php
 * ---------------------------------------------------------------------
 * Super Admin only. Lists every account and lets the Super Admin promote
 * a verified Parishioner to Admin (or back), and suspend or reactivate
 * accounts. New staff register normally and verify their email first,
 * then get promoted here -- so every Admin has a working email for
 * password resets.
 *
 * Super Admin accounts (including your own) are read-only on this page;
 * add or change those directly in the database. Save POSTs to
 * admin-update-account.php through the same initAdminModals() flow as
 * the request pages; filters reuse initAdminTableFilters() (the role
 * tabs are its "type" tabs).
 * ---------------------------------------------------------------------
 */
$psGuardRoles = ['Super Admin'];
require __DIR__ . '/includes/auth-guard.php';

$roleTabs = ['parishioner' => 'Parishioners', 'admin' => 'Admins', 'super-admin' => 'Super Admins'];
$statusClasses = ['Active' => 'is-approved', 'Suspended' => 'is-rejected'];

$accounts = $conn->query(
    "SELECT id, firstname, middlename, lastname, suffix, email, mobile_number, role, status, email_verified
       FROM users
      ORDER BY FIELD(role, 'Super Admin', 'Admin', 'Parishioner'), lastname, firstname"
)->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Accounts';
$pageCss   = 'admin.css';
$activeNav = 'accounts';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/admin-sidebar.php';
?>
<main class="ps-main">

    <section class="ps-plain-header" style="position:relative;">
        <div>
            <div class="ps-heading-ornament"><span></span><?php ps_icon('cross'); ?><span></span></div>
            <h1>Accounts</h1>
            <p>Promote parish staff to Admin and suspend or reactivate accounts.</p>
        </div>
        <?php require __DIR__ . '/includes/topbar.php'; ?>
    </section>

    <div class="ps-card">
        <div class="ps-info-banner is-tip admin-note">
            <?php ps_icon('info'); ?>
            <span>To add a staff member, have them create an account and verify their email first, then set their role to Admin here.</span>
        </div>

        <div class="admin-toolbar" data-admin-type-tabs>
            <button type="button" class="ps-tab active" data-admin-type-tab="all">All</button>
            <?php foreach ($roleTabs as $key => $label): ?>
                <button type="button" class="ps-tab" data-admin-type-tab="<?php echo $key; ?>"><?php echo htmlspecialchars($label); ?></button>
            <?php endforeach; ?>
        </div>
        <div class="admin-toolbar">
            <span class="ps-search"><?php ps_icon('search'); ?><input type="text" placeholder="Search name, email or mobile" data-admin-search></span>
            <span class="ps-select">
                <select data-admin-status-select>
                    <option value="">All statuses</option>
                    <option value="Active">Active</option>
                    <option value="Suspended">Suspended</option>
                </select>
            </span>
        </div>

        <div class="admin-table" style="--admin-cols: 1fr 120px 100px 90px 100px 110px;">
            <div class="admin-table-head">
                <span>Name / Email</span><span>Mobile</span><span>Role</span><span>Email</span><span>Status</span><span></span>
            </div>
            <?php foreach ($accounts as $a): ?>
                <?php
                $fullName = implode(' ', array_filter([$a['firstname'], $a['middlename'], $a['lastname'], $a['suffix']]));
                $isSelf = (int) $a['id'] === ps_current_user_id();
                $isLocked = $isSelf || $a['role'] === 'Super Admin';
                ?>
                <div class="admin-row" data-admin-row data-type="<?php echo htmlspecialchars(strtolower(str_replace(' ', '-', $a['role']))); ?>" data-status="<?php echo htmlspecialchars($a['status']); ?>" data-search="<?php echo htmlspecialchars(strtolower($fullName . ' ' . $a['email'] . ' ' . $a['mobile_number'])); ?>">
                    <span class="admin-cell-name">
                        <strong><?php echo htmlspecialchars($fullName . ($isSelf ? ' (you)' : '')); ?></strong>
                        <small><?php echo htmlspecialchars($a['email']); ?></small>
                    </span>
                    <span><?php echo htmlspecialchars($a['mobile_number']); ?></span>
                    <span data-row-field="role"><?php echo htmlspecialchars($a['role']); ?></span>
                    <span><?php echo (int) $a['email_verified'] ? 'Verified' : '<span class="admin-proof-none">Unverified</span>'; ?></span>
                    <span class="ps-status <?php echo $statusClasses[$a['status']] ?? ''; ?>" data-row-status><?php echo htmlspecialchars($a['status']); ?></span>
                    <span class="admin-cell-actions">
                        <?php if ($isLocked): ?>
                            <span class="admin-proof-none"><?php echo $isSelf ? 'Your account' : 'Protected'; ?></span>
                        <?php else: ?>
                            <button type="button" class="ps-btn ps-btn-outline" data-modal-trigger="accountModal"
                                data-id="<?php echo (int) $a['id']; ?>"
                                data-fullname="<?php echo htmlspecialchars($fullName); ?>"
                                data-email="<?php echo htmlspecialchars($a['email'] . ((int) $a['email_verified'] ? '' : ' (email not verified)')); ?>"
                                data-role="<?php echo htmlspecialchars($a['role']); ?>"
                                data-status="<?php echo htmlspecialchars($a['status']); ?>">
                                Manage
                            </button>
                        <?php endif; ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="admin-empty" data-admin-empty hidden><?php ps_icon('people'); ?><p>No accounts match these filters.</p></div>
    </div>

</main>

<div class="ps-modal-overlay" id="accountModal" data-modal hidden>
    <div class="ps-modal-card" role="dialog" aria-modal="true" aria-labelledby="accountModalTitle">
        <button type="button" class="ps-modal-close" data-modal-close aria-label="Close"><?php ps_icon('close'); ?></button>
        <h2 class="ps-modal-title" id="accountModalTitle" data-modal-field="fullname"></h2>
        <p class="ps-modal-sub" data-modal-field="email"></p>
        <form data-admin-form="admin-update-account.php">
            <input type="hidden" name="id" data-modal-field="id">
            <div class="ps-modal-field">
                <label for="accountRole">Role</label>
                <span class="ps-select">
                    <select id="accountRole" name="role" data-modal-field="role" aria-describedby="accountRoleHint">
                        <option value="Parishioner">Parishioner</option>
                        <option value="Admin">Admin</option>
                    </select>
                </span>
                <small class="admin-status-hint" id="accountRoleHint">Admins can review requests, donations and announcements. Only verified emails can be made Admin.</small>
            </div>
            <div class="ps-modal-field">
                <label for="accountStatus">Status</label>
                <span class="ps-select">
                    <select id="accountStatus" name="status" data-modal-field="status" aria-describedby="accountStatusHint">
                        <option value="Active">Active</option>
                        <option value="Suspended">Suspended</option>
                    </select>
                </span>
                <small class="admin-status-hint" id="accountStatusHint">A suspended account is signed out on its next page load and can't log in.</small>
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
