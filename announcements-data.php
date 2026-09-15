<?php

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/announcements.php';

echo json_encode([
    'announcements' => array_map('ps_announcement_for_public', ps_fetch_announcements($conn)),
]);
