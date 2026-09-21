<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/mass-intention-history.php';
try {
    $contact = ps_mass_intention_account_contact($conn);
    if (!$contact) {
        http_response_code(401);
        echo json_encode(['error' => 'Please sign in to view your Mass Intention history.']);
        exit;
    }
    $before = filter_input(INPUT_GET, 'before', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
    echo json_encode(ps_mass_intention_history($conn, $contact, $before));
} catch (Throwable $e) {
    error_log('Mass Intention history failed: ' . $e->getMessage());
    http_response_code(503);
    echo json_encode(['error' => 'Mass Intention history is temporarily unavailable. Please try again.']);
}
