<?php
/** Private donation history. Never accepts a user ID from request parameters. */
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
function ps_donation_history(mysqli $conn, int $userId, int $before = 0): array {
    $stmt = $conn->prepare('SELECT id, reference_no, amount, purpose, status, created_at FROM donations WHERE user_id = ? AND (? = 0 OR id < ?) ORDER BY id DESC LIMIT 21');
    $stmt->bind_param('iii', $userId, $before, $before);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $more = count($rows) > 20;
    $rows = array_slice($rows, 0, 20);
    return ['donations' => $rows, 'nextCursor' => $more ? (int) end($rows)['id'] : null];
}
