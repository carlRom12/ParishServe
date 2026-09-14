<?php
/**
 * uploads.php
 * ---------------------------------------------------------------------
 * Document uploads for the public request forms.
 *
 * A file is "staged" the moment a parishioner picks it
 * (upload-document.php, driven by assets/js/request-uploads.js): it's
 * checked, saved under uploads/_staged/<random per-session folder>/ and
 * remembered in $_SESSION['ps_staged'][flow][field]. That's what lets a
 * document chosen on an earlier wizard step survive until the final step
 * is submitted, when ps_promote_upload() moves it to
 * uploads/<type>/<reference_no>/. Abandoned staged folders are swept
 * after a day.
 *
 * Nothing under uploads/ is reachable from the web (uploads/.htaccess);
 * staff open files through admin-file.php.
 *
 * The checks never trust the browser: PHP's upload error, size (the same
 * per-document limit the page's data-max-size-mb advertises), extension,
 * and the file's real content type via finfo.
 * ---------------------------------------------------------------------
 */
require_once __DIR__ . '/request-types.php';

const PS_UPLOAD_ROOT = __DIR__ . '/../uploads';
const PS_UPLOAD_MIME = ['pdf' => 'application/pdf', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png'];
const PS_UPLOAD_DEFAULTS = ['types' => ['pdf', 'jpg', 'jpeg', 'png'], 'max_mb' => 5];
const PS_STAGED_MAX_AGE = 86400;

/** The documents a flow accepts, in page order, with defaults filled in. */
function ps_upload_rules($flow) {
    $docs = $flow === 'donation' ? [PS_DONATION_PROOF] : (PS_DOCUMENT_CHECKLISTS[$flow] ?? []);
    return array_map(fn($doc) => $doc + PS_UPLOAD_DEFAULTS, $docs);
}

function ps_upload_rule($flow, $field) {
    foreach (ps_upload_rules($flow) as $rule) {
        if ($rule['field'] === $field) {
            return $rule;
        }
    }
    return null;
}

/** ['pdf','jpg','jpeg','png'] -> "PDF, JPG or PNG" */
function ps_upload_type_list(array $types) {
    $names = array_values(array_unique(array_map(fn($type) => $type === 'jpeg' ? 'JPG' : strtoupper($type), $types)));
    $last = array_pop($names);
    return $names ? implode(', ', $names) . ' or ' . $last : $last;
}

/** Absolute path for a stored "uploads/..." path -- only if it really is a file inside uploads/. */
function ps_upload_abs($relative) {
    if (!is_string($relative) || strpos($relative, '..') !== false || !preg_match('#^uploads/[A-Za-z0-9_./-]+$#', $relative)) {
        return null;
    }
    $root = realpath(PS_UPLOAD_ROOT);
    $path = realpath(__DIR__ . '/../' . $relative);
    if (!$root || !$path || strpos($path, $root . DIRECTORY_SEPARATOR) !== 0 || !is_file($path)) {
        return null;
    }
    return $path;
}

function ps_stage_folder() {
    if (empty($_SESSION['ps_stage_folder'])) {
        $_SESSION['ps_stage_folder'] = bin2hex(random_bytes(16));
    }
    return 'uploads/_staged/' . $_SESSION['ps_stage_folder'];
}

function ps_clean_filename($name, $extension) {
    $base = preg_replace('/[^A-Za-z0-9 ._()-]+/', '_', pathinfo(basename($name), PATHINFO_FILENAME));
    $base = trim(substr($base, 0, 100), ' ._');
    return ($base === '' ? 'document' : $base) . '.' . $extension;
}

/**
 * Checks one $_FILES entry and stages it for $flow/$field, replacing any
 * earlier pick. Returns ['ok' => true, 'document' => status item] or
 * ['ok' => false, 'error' => message for the parishioner].
 */
function ps_stage_upload($flow, $field, $file) {
    $rule = ps_upload_rule($flow, $field);
    if (!$rule) {
        return ['ok' => false, 'error' => "That document isn't part of this form."];
    }
    $label = $rule['label'];
    $types = ps_upload_type_list($rule['types']);

    if (!is_array($file) || !isset($file['error']) || is_array($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['ok' => false, 'error' => "Please choose a file for the {$label}."];
    }
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        return ['ok' => false, 'error' => "The {$label} must be {$rule['max_mb']} MB or smaller."];
    }
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        return ['ok' => false, 'error' => "The {$label} didn't upload completely. Please try again."];
    }
    if ($file['size'] <= 0) {
        return ['ok' => false, 'error' => "The file chosen for the {$label} is empty."];
    }
    if ($file['size'] > $rule['max_mb'] * 1024 * 1024) {
        return ['ok' => false, 'error' => "The {$label} must be {$rule['max_mb']} MB or smaller."];
    }
    if (!in_array(strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION)), $rule['types'], true)) {
        return ['ok' => false, 'error' => "The {$label} must be a {$types} file."];
    }

    // Judge the content, not the name: a renamed .php or .exe fails here.
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $allowedMimes = array_map(fn($type) => PS_UPLOAD_MIME[$type], $rule['types']);
    if (!in_array($mime, $allowedMimes, true)) {
        return ['ok' => false, 'error' => "The file chosen for the {$label} isn't a real {$types} file."];
    }
    $extension = array_search($mime, PS_UPLOAD_MIME, true); // stored under its true type

    $folder = ps_stage_folder();
    $directory = __DIR__ . '/../' . $folder;
    $relative = $folder . '/' . $flow . '-' . $field . '-' . bin2hex(random_bytes(6)) . '.' . $extension;
    if ((!is_dir($directory) && !mkdir($directory, 0775, true)) || !move_uploaded_file($file['tmp_name'], __DIR__ . '/../' . $relative)) {
        error_log("ps_stage_upload: could not store {$flow}/{$field}");
        return ['ok' => false, 'error' => "We couldn't save the {$label}. Please try again."];
    }

    ps_unstage($flow, $field);
    $_SESSION['ps_staged'][$flow][$field] = [
        'path' => $relative,
        'name' => ps_clean_filename((string) $file['name'], $extension),
        'size' => (int) $file['size'],
    ];
    ps_sweep_staged();
    return ['ok' => true, 'document' => ps_upload_status_item($rule, $_SESSION['ps_staged'][$flow][$field])];
}

/** Staged documents for a flow whose files still exist: field => ['path', 'name', 'size']. */
function ps_staged_uploads($flow) {
    $staged = [];
    foreach ($_SESSION['ps_staged'][$flow] ?? [] as $field => $entry) {
        if (ps_upload_abs($entry['path'] ?? null)) {
            $staged[$field] = $entry;
        } else {
            unset($_SESSION['ps_staged'][$flow][$field]);
        }
    }
    return $staged;
}

function ps_unstage($flow, $field) {
    $path = ps_upload_abs($_SESSION['ps_staged'][$flow][$field]['path'] ?? null);
    if ($path) {
        unlink($path);
    }
    unset($_SESSION['ps_staged'][$flow][$field]);
}

function ps_upload_status_item(array $rule, $entry) {
    return [
        'field'    => $rule['field'],
        'label'    => $rule['label'],
        'required' => $rule['required'],
        'uploaded' => (bool) $entry,
        'name'     => $entry['name'] ?? '',
        'size'     => $entry['size'] ?? 0,
    ];
}

/** Every document of a flow with whether it's been uploaded yet (for request-uploads.js). */
function ps_upload_status($flow) {
    $staged = ps_staged_uploads($flow);
    return array_map(fn($rule) => ps_upload_status_item($rule, $staged[$rule['field']] ?? null), ps_upload_rules($flow));
}

/** Moves a staged file into uploads/<type>/<reference>/; returns the new relative path, or null. */
function ps_promote_upload(array $entry, $type, $reference, $field) {
    $source = ps_upload_abs($entry['path']);
    if (!$source) {
        return null;
    }
    $folder = 'uploads/' . $type . '/' . $reference;
    $directory = __DIR__ . '/../' . $folder;
    if (!is_dir($directory) && !mkdir($directory, 0775, true)) {
        return null;
    }
    $relative = $folder . '/' . $field . '-' . bin2hex(random_bytes(6)) . '.' . strtolower(pathinfo($source, PATHINFO_EXTENSION));
    return rename($source, __DIR__ . '/../' . $relative) ? $relative : null;
}

/** Removes staged folders untouched for a day (drafts nobody submitted). */
function ps_sweep_staged() {
    foreach (glob(PS_UPLOAD_ROOT . '/_staged/*', GLOB_ONLYDIR) ?: [] as $directory) {
        if (filemtime($directory) > time() - PS_STAGED_MAX_AGE) {
            continue;
        }
        foreach (glob($directory . '/*') ?: [] as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        rmdir($directory);
    }
}
