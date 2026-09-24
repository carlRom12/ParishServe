<?php
/** Private donation history. Never accepts a user ID from request parameters. */

/** A donor may still change a donation while staff haven't verified it (donation-request.php's Edit). */
const PS_DONATION_EDITABLE_STATUSES = ['under_review'];

function ps_donation_editable($status): bool {
    return in_array($status, PS_DONATION_EDITABLE_STATUSES, true);
}

function ps_donation_account_id(mysqli $conn): ?int {
    $id = (int) ($_SESSION['user_id'] ?? 0);
    if ($id < 1) return null;
    $stmt = $conn->prepare("SELECT id FROM users WHERE id = ? AND status = 'Active' AND email_verified = 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $user ? (int) $user['id'] : null;
}

/**
 * The account's donations, newest first, 20 per page ($before = the
 * previous page's nextCursor). $withDetails adds what the donor entered
 * (contact, email, details JSON) for donation-request.php's Edit window.
 * $status (a PS_DONATION_STATUS_OPTIONS value, or '' for all) and $search
 * (donation number, donor name, GCash reference number or purpose) narrow the list.
 */
function ps_donation_history(mysqli $conn, int $userId, int $before = 0, bool $withDetails = false, string $status = '', string $search = ''): array {
    $extra = $withDetails ? ', contact_number, contact_email, details' : '';
    $like = '%' . addcslashes($search, '%_\\') . '%';
    $stmt = $conn->prepare("SELECT id, donation_no, donor_name, amount, gcash_reference, purpose, proof_of_payment IS NOT NULL AS has_proof, status, created_at{$extra} FROM donations
                             WHERE user_id = ? AND (? = 0 OR id < ?) AND (? = '' OR status = ?)
                               AND (? = '' OR donation_no LIKE ? OR donor_name LIKE ? OR gcash_reference LIKE ? OR purpose LIKE ?)
                             ORDER BY id DESC LIMIT 21");
    $stmt->bind_param('iiisssssss', $userId, $before, $before, $status, $status, $search, $like, $like, $like, $like);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $more = count($rows) > 20;
    $rows = array_slice($rows, 0, 20);
    return ['donations' => $rows, 'nextCursor' => $more ? (int) end($rows)['id'] : null];
}

/** One of the account's own donations, or null. $lock = SELECT ... FOR UPDATE (inside a transaction). */
function ps_own_donation(mysqli $conn, int $userId, int $id, bool $lock = false): ?array {
    $stmt = $conn->prepare('SELECT id, donation_no, status, proof_of_payment FROM donations WHERE id = ? AND user_id = ?' . ($lock ? ' FOR UPDATE' : ''));
    $stmt->bind_param('ii', $id, $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}
