<?php
/**
 * Private Mass Intention history. Mirrors includes/donation-history.php,
 * but mass_intentions (like the other 6 request tables) has no user_id
 * FK -- it links to a parishioner by contact_number = users.mobile_number
 * (see includes/request-forms.php's ps_build_massintention()), so the
 * lookup goes through the signed-in account's own mobile number instead
 * of an id. Never accepts a contact number from request parameters.
 */
function ps_mass_intention_account_contact(mysqli $conn): ?string {
    $id = (int) ($_SESSION['user_id'] ?? 0);
    if ($id < 1) return null;
    $stmt = $conn->prepare("SELECT mobile_number FROM users WHERE id = ? AND status = 'Active' AND email_verified = 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $user && $user['mobile_number'] !== '' ? (string) $user['mobile_number'] : null;
}

/**
 * The account's Mass Intentions, newest first, 20 per page ($before = the
 * previous page's nextCursor). $status (a PS_STATUS_OPTIONS value, or ''
 * for all) and $search (reference number, intention type or who it is
 * offered for) narrow the list, as on mass-intention-request.php.
 */
function ps_mass_intention_history(mysqli $conn, string $contact, int $before = 0, string $status = '', string $search = ''): array {
    $like = '%' . addcslashes($search, '%_\\') . '%';
    $stmt = $conn->prepare(
        "SELECT id, reference_no, intention_type, intention_for, mass_date, mass_time, status, created_at
           FROM mass_intentions
          WHERE contact_number = ? AND (? = 0 OR id < ?) AND (? = '' OR status = ?)
            AND (? = '' OR reference_no LIKE ? OR intention_type LIKE ? OR intention_for LIKE ?)
          ORDER BY id DESC LIMIT 21"
    );
    $stmt->bind_param('siissssss', $contact, $before, $before, $status, $status, $search, $like, $like, $like);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $more = count($rows) > 20;
    $rows = array_slice($rows, 0, 20);
    return ['intentions' => $rows, 'nextCursor' => $more ? (int) end($rows)['id'] : null];
}
