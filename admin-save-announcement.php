<?php
/**
 * admin-save-announcement.php
 * ---------------------------------------------------------------------
 * POST endpoint behind admin-announcements.php (initAdminModals() and
 * initAdminDelete() in main.js). Admin/Super Admin only, CSRF-checked.
 *
 * Takes:  action=save    id (empty for a new announcement), title, body,
 *                        category, posted_date (Y-m-d), is_featured,
 *                        image (optional PNG/JPG; replaces the current one)
 *         action=delete  id
 * Answers: { ok: true, message, reload }  -- save: the page re-renders
 *          { ok: false, error }           -- 4xx/5xx, nothing written
 * ---------------------------------------------------------------------
 */
$psGuardJson = true;
require __DIR__ . '/includes/auth-guard.php';
require_once __DIR__ . '/includes/announcements.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$_POST && !$_FILES && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    // Bigger than post_max_size: PHP dropped the whole body, CSRF token included.
    ps_json(413, ['ok' => false, 'error' => 'That image is too large. Please choose one of ' . PS_ANNOUNCEMENT_IMAGE_MAX_MB . ' MB or less.']);
}
ps_require_post_csrf();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$action = (string) ($_POST['action'] ?? '');
$id = null;
if ((string) ($_POST['id'] ?? '') !== '') {
    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id === false) {
        ps_json(422, ['ok' => false, 'error' => 'Missing or invalid announcement id.']);
    }
}

$existing = null;
if ($id !== null) {
    $stmt = $conn->prepare('SELECT id, title, image_path FROM announcements WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$existing) {
        ps_json(404, ['ok' => false, 'error' => 'That announcement no longer exists. Please reload the page.']);
    }
}

if ($action === 'delete') {
    if (!$existing) {
        ps_json(422, ['ok' => false, 'error' => 'Missing or invalid announcement id.']);
    }
    try {
        $stmt = $conn->prepare('DELETE FROM announcements WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    } catch (mysqli_sql_exception $e) {
        error_log('admin-save-announcement delete failed: ' . $e->getMessage());
        ps_json(500, ['ok' => false, 'error' => 'Could not delete the announcement. Please try again.']);
    }
    ps_delete_announcement_image($existing['image_path']);
    ps_json(200, ['ok' => true, 'message' => '"' . $existing['title'] . '" was deleted.']);
}

if ($action !== 'save') {
    ps_json(422, ['ok' => false, 'error' => 'Unknown action.']);
}

$title      = trim((string) ($_POST['title'] ?? ''));
$body       = trim(str_replace("\r\n", "\n", (string) ($_POST['body'] ?? '')));
$category   = (string) ($_POST['category'] ?? '');
$postedDate = (string) ($_POST['posted_date'] ?? '');
$featured   = empty($_POST['is_featured']) ? 0 : 1;

$errors = [];
if ($title === '') {
    $errors[] = 'Please enter a title.';
} elseif (mb_strlen($title, 'UTF-8') > PS_ANNOUNCEMENT_TITLE_MAX) {
    $errors[] = 'The title must be ' . PS_ANNOUNCEMENT_TITLE_MAX . ' characters or fewer.';
}
if ($body === '') {
    $errors[] = 'Please enter the announcement text.';
} elseif (mb_strlen($body, 'UTF-8') > PS_ANNOUNCEMENT_BODY_MAX) {
    $errors[] = 'The announcement text must be ' . number_format(PS_ANNOUNCEMENT_BODY_MAX) . ' characters or fewer.';
}
if (!in_array($category, PS_ANNOUNCEMENT_CATEGORIES, true)) {
    $errors[] = 'Please choose a category.';
}
$date = DateTimeImmutable::createFromFormat('!Y-m-d', $postedDate);
if (!$date || $date->format('Y-m-d') !== $postedDate) {
    $errors[] = 'Please choose a valid posted date.';
}
if ($errors) {
    ps_json(422, ['ok' => false, 'error' => implode(' ', $errors)]);
}

$newImage = null;
$file = $_FILES['image'] ?? null;
if (is_array($file) && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
    $stored = ps_store_announcement_image($file);
    if (!$stored['ok']) {
        ps_json(422, ['ok' => false, 'error' => $stored['error']]);
    }
    $newImage = $stored['path'];
}

try {
    if ($existing) {
        $imagePath = $newImage ?? $existing['image_path'];
        $stmt = $conn->prepare('UPDATE announcements SET title = ?, body = ?, category = ?, image_path = ?, is_featured = ?, posted_date = ? WHERE id = ?');
        $stmt->bind_param('ssssisi', $title, $body, $category, $imagePath, $featured, $postedDate, $id);
    } else {
        $createdBy = ps_current_user_id();
        $stmt = $conn->prepare('INSERT INTO announcements (title, body, category, image_path, is_featured, posted_date, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('ssssisi', $title, $body, $category, $newImage, $featured, $postedDate, $createdBy);
    }
    $stmt->execute();
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    if ($newImage) {
        ps_delete_announcement_image($newImage);
    }
    error_log('admin-save-announcement save failed: ' . $e->getMessage());
    ps_json(500, ['ok' => false, 'error' => 'Could not save the announcement. Please try again.']);
}

if ($existing && $newImage && $existing['image_path']) {
    ps_delete_announcement_image($existing['image_path']);
}

$scheduled = $postedDate > date('Y-m-d');
ps_json(200, [
    'ok'      => true,
    'reload'  => true,
    'message' => '"' . $title . '" ' . ($existing ? 'updated' : 'posted')
        . ($scheduled ? ' -- it goes live on ' . date('M j, Y', strtotime($postedDate)) . '.' : '.'),
]);
