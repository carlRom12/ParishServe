<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/donation-history.php';
try {
    $userId = ps_donation_account_id($conn);
    if (!$userId) {
        http_response_code(401);
        echo json_encode(['error' => 'Please sign in to view your donation history.']);
        exit;
    }
    $before = filter_input(INPUT_GET, 'before', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
    echo json_encode(ps_donation_history($conn, $userId, $before));
} catch (Throwable $e) {
    error_log('Donation history failed: ' . $e->getMessage());
    http_response_code(503);
    echo json_encode(['error' => 'Donation history is temporarily unavailable. Please try again.']);
}
