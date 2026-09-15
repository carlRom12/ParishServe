<?php

session_start();
require_once __DIR__ . '/includes/uploads.php';

function uploadReply($httpCode, array $body) {
    http_response_code($httpCode);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($body);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST' && !$_POST && !$_FILES && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    // Bigger than post_max_size: PHP dropped the whole body.
    uploadReply(413, ['ok' => false, 'error' => 'That file is too large.']);
}

$flow = (string) ($method === 'POST' ? ($_POST['flow'] ?? '') : ($_GET['flow'] ?? ''));
if (!ps_upload_rules($flow)) {
    uploadReply(404, ['ok' => false, 'error' => 'Unknown request form.']);
}

if ($method === 'GET') {
    uploadReply(200, ['ok' => true, 'documents' => ps_upload_status($flow)]);
}
if ($method !== 'POST') {
    uploadReply(405, ['ok' => false, 'error' => 'Use GET or POST.']);
}

$field = (string) ($_POST['field'] ?? '');
$rule = ps_upload_rule($flow, $field);
if (!$rule) {
    uploadReply(422, ['ok' => false, 'error' => "That document isn't part of this form."]);
}

if (($_POST['action'] ?? '') === 'remove') {
    ps_unstage($flow, $field);
    uploadReply(200, ['ok' => true, 'document' => ps_upload_status_item($rule, null)]);
}

$result = ps_stage_upload($flow, $field, $_FILES['file'] ?? null);
uploadReply($result['ok'] ? 200 : 422, $result);
