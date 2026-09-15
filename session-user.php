<?php
/**
 * session-user.php
 * ---------------------------------------------------------------------
 * JSON for assets/js/session-user.js. The parishioner pages are static
 * .html, so their user chip and sidebar Log in / Log out link can't read
 * the PHP session themselves. Answers {loggedIn: false} for visitors (and
 * for accounts that have since been suspended), otherwise the signed-in
 * person's name and role -- nothing else.
 * ---------------------------------------------------------------------
 */
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (empty($_SESSION['user_id'])) {
    echo json_encode(['loggedIn' => false]);
    exit;
}

require_once __DIR__ . '/config.php';

$userId = (int) $_SESSION['user_id'];
$stmt = $conn->prepare('SELECT firstname, lastname, role, status, email_verified FROM users WHERE id = ?');
$stmt->bind_param('i', $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user || $user['status'] !== 'Active' || !(int) $user['email_verified']) {
    echo json_encode(['loggedIn' => false]);
    exit;
}

echo json_encode([
    'loggedIn'  => true,
    'firstName' => $user['firstname'],
    'fullName'  => trim($user['firstname'] . ' ' . $user['lastname']),
    'role'      => $user['role'],
]);
