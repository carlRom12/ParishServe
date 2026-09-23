<?php
/**
 * admin-file.php
 * ---------------------------------------------------------------------
 * Opens an uploaded request document or proof of payment for staff.
 * uploads/ itself is closed to the web (uploads/.htaccess), so this
 * Admin/Super Admin-only script is the only way to view those files.
 *   ?doc=<request_documents.id>     ?donation=<donations.id>
 * ---------------------------------------------------------------------
 */
require __DIR__ . '/includes/auth-guard.php';
require_once __DIR__ . '/includes/uploads.php';

$docId = filter_input(INPUT_GET, 'doc', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$donationId = filter_input(INPUT_GET, 'donation', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

$row = null;
if ($docId) {
    $stmt = $conn->prepare('SELECT file_path, original_name FROM request_documents WHERE id = ?');
    $stmt->bind_param('i', $docId);
} elseif ($donationId) {
    $stmt = $conn->prepare('SELECT proof_of_payment AS file_path, donation_no AS original_name FROM donations WHERE id = ?');
    $stmt->bind_param('i', $donationId);
}
if (isset($stmt)) {
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$path = ps_upload_abs($row['file_path'] ?? null);
$extension = $path ? strtolower(pathinfo($path, PATHINFO_EXTENSION)) : '';
if (!$path || !isset(PS_UPLOAD_MIME[$extension])) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'File not found.';
    exit;
}

$downloadName = preg_replace('/[^A-Za-z0-9 ._()-]+/', '_', pathinfo((string) $row['original_name'], PATHINFO_FILENAME)) ?: 'document';
header('Content-Type: ' . PS_UPLOAD_MIME[$extension]);
header('Content-Length: ' . filesize($path));
header('Content-Disposition: inline; filename="' . $downloadName . '.' . $extension . '"');
header('X-Content-Type-Options: nosniff');
readfile($path);
