<?php
/**
 * donation-proof.php?id=<donations.id>
 * ---------------------------------------------------------------------
 * Shows a signed-in donor the proof of payment of one of their own
 * donations (donation-request.php's Edit window). uploads/ is closed to
 * the web; staff use admin-file.php instead.
 * ---------------------------------------------------------------------
 */
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/uploads.php';
require_once __DIR__ . '/includes/donation-history.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$userId = ps_donation_account_id($conn);
$row = $id && $userId ? ps_own_donation($conn, $userId, $id) : null;

$path = ps_upload_abs($row['proof_of_payment'] ?? null);
$extension = $path ? strtolower(pathinfo($path, PATHINFO_EXTENSION)) : '';
if (!$path || !isset(PS_UPLOAD_MIME[$extension])) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'File not found.';
    exit;
}

header('Content-Type: ' . PS_UPLOAD_MIME[$extension]);
header('Content-Length: ' . filesize($path));
header('Content-Disposition: inline; filename="' . $row['donation_no'] . '-proof.' . $extension . '"');
header('Cache-Control: no-store, private');
header('X-Content-Type-Options: nosniff');
readfile($path);
