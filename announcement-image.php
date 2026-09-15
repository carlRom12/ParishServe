<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/announcements.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$isStaff = in_array($_SESSION['user_role'] ?? '', ['Admin', 'Super Admin'], true);
session_write_close(); 

$row = null;
if ($id) {
    if ($isStaff) {
        $stmt = $conn->prepare('SELECT image_path FROM announcements WHERE id = ?');
        $stmt->bind_param('i', $id);
    } else {
        $today = date('Y-m-d');
        $stmt = $conn->prepare('SELECT image_path FROM announcements WHERE id = ? AND posted_date <= ?');
        $stmt->bind_param('is', $id, $today);
    }
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$path = ps_upload_abs($row['image_path'] ?? null);
$extension = $path ? strtolower(pathinfo($path, PATHINFO_EXTENSION)) : '';
if (!$path || !in_array($extension, PS_ANNOUNCEMENT_IMAGE_TYPES, true)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Image not found.';
    exit;
}

header('Content-Type: ' . PS_UPLOAD_MIME[$extension]);
header('Content-Length: ' . filesize($path));
header('Cache-Control: private, max-age=300');
header('X-Content-Type-Options: nosniff');
readfile($path);
