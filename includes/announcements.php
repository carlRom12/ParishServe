<?php
/**
 * announcements.php
 * ---------------------------------------------------------------------
 * The announcements table (database/migrations/003-announcements.sql).
 * Staff post them on admin-announcements.php (admin-save-announcement.php
 * writes); the public reads them through announcements-data.php
 * (announcements.html and dashboard.html's Parish Updates).
 *
 * A posted_date in the future means "scheduled": staff see it, the public
 * pages and its image stay hidden until that day.
 *
 * Images are stored in uploads/announcements/, which is closed to the web
 * like every other upload; announcement-image.php serves them. PNG/JPG
 * only -- an SVG can carry script.
 * ---------------------------------------------------------------------
 */
require_once __DIR__ . '/uploads.php';

const PS_ANNOUNCEMENT_CATEGORIES   = ['Parish News', 'Events', 'Mass & Liturgical', 'Wedding Banns', 'Reminders', 'Notices'];
const PS_ANNOUNCEMENT_IMAGE_TYPES  = ['jpg', 'jpeg', 'png'];
const PS_ANNOUNCEMENT_IMAGE_MAX_MB = 5;
const PS_ANNOUNCEMENT_TITLE_MAX    = 150;
const PS_ANNOUNCEMENT_BODY_MAX     = 5000;

/** Newest first. $publishedOnly leaves out scheduled (future-dated) announcements. */
function ps_fetch_announcements(mysqli $conn, $publishedOnly = true) {
    $sql = 'SELECT id, title, body, category, image_path, is_featured, posted_date, created_at FROM announcements';
    if ($publishedOnly) {
        $sql .= ' WHERE posted_date <= ?';
    }
    $sql .= ' ORDER BY posted_date DESC, id DESC';

    $stmt = $conn->prepare($sql);
    if ($publishedOnly) {
        $today = date('Y-m-d'); // PHP's clock (Asia/Manila, config.php), not the database server's
        $stmt->bind_param('s', $today);
    }
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function ps_announcement_image_url(array $row) {
    return $row['image_path'] ? 'announcement-image.php?id=' . (int) $row['id'] : null;
}

/** The shape announcements-data.php sends to the public pages. */
function ps_announcement_for_public(array $row) {
    return [
        'id'        => (int) $row['id'],
        'title'     => $row['title'],
        'body'      => $row['body'],
        'category'  => $row['category'],
        'date'      => $row['posted_date'],
        'dateLabel' => date('F j, Y', strtotime($row['posted_date'])),
        'featured'  => (bool) $row['is_featured'],
        'imageUrl'  => ps_announcement_image_url($row),
    ];
}

/**
 * Checks one uploaded image ($_FILES entry) and stores it under
 * uploads/announcements/. Returns ['ok' => true, 'path' => relative path]
 * or ['ok' => false, 'error' => message for staff].
 */
function ps_store_announcement_image(array $file) {
    $maxMb = PS_ANNOUNCEMENT_IMAGE_MAX_MB;
    $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
    if (is_array($error)) {
        return ['ok' => false, 'error' => 'Please choose one image.'];
    }
    if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
        return ['ok' => false, 'error' => "The image must be {$maxMb} MB or smaller."];
    }
    if ($error !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        return ['ok' => false, 'error' => "The image didn't upload completely. Please try again."];
    }
    if ($file['size'] <= 0 || $file['size'] > $maxMb * 1024 * 1024) {
        return ['ok' => false, 'error' => "The image must be a non-empty file of {$maxMb} MB or smaller."];
    }
    if (!in_array(strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION)), PS_ANNOUNCEMENT_IMAGE_TYPES, true)) {
        return ['ok' => false, 'error' => 'The image must be a PNG or JPG file.'];
    }

    // Judge the content, not the name.
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $extension = array_search($mime, PS_UPLOAD_MIME, true);
    if (!in_array($extension, PS_ANNOUNCEMENT_IMAGE_TYPES, true)) {
        return ['ok' => false, 'error' => "That file isn't a real PNG or JPG image."];
    }

    $folder = 'uploads/announcements';
    $directory = __DIR__ . '/../' . $folder;
    $relative = $folder . '/' . bin2hex(random_bytes(12)) . '.' . $extension;
    if ((!is_dir($directory) && !mkdir($directory, 0775, true)) || !move_uploaded_file($file['tmp_name'], __DIR__ . '/../' . $relative)) {
        error_log('ps_store_announcement_image: could not store the upload');
        return ['ok' => false, 'error' => "We couldn't save the image. Please try again."];
    }
    return ['ok' => true, 'path' => $relative];
}

function ps_delete_announcement_image($relative) {
    $path = ps_upload_abs($relative);
    if ($path) {
        unlink($path);
    }
}
