<?php
/**
 * header.php
 * ---------------------------------------------------------------------
 * Document <head> + the opening .ps-shell wrapper for the PHP (admin)
 * pages -- the same markup the .html pages carry inline. The calling
 * page sets, before requiring this:
 *   $pageTitle  shown as "<title> · ParishServe"
 *   $pageCss    one stylesheet name or an array of them (in assets/css/),
 *               loaded after style.css and before responsive.css
 * includes/footer.php closes the wrapper. The csrf-token meta is read by
 * initAdminModals() in main.js for its POSTs (see auth-guard.php).
 * ---------------------------------------------------------------------
 */
require_once __DIR__ . '/icons.php';

$pageCssFiles = isset($pageCss) ? (array) $pageCss : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars(isset($pageTitle) ? $pageTitle . ' · ParishServe' : 'ParishServe'); ?></title>
<?php if (function_exists('ps_csrf_token')): ?>
<meta name="csrf-token" content="<?php echo htmlspecialchars(ps_csrf_token()); ?>">
<?php endif; ?>

<link rel="stylesheet" href="assets/css/style.css">

<?php foreach ($pageCssFiles as $cssFile): ?>
<link rel="stylesheet" href="assets/css/<?php echo htmlspecialchars($cssFile); ?>">
<?php endforeach; ?>
<link rel="stylesheet" href="assets/css/responsive.css?v=3">
<link rel="stylesheet" href="assets/css/sidebar-refined.css?v=1">
<link rel="stylesheet" href="assets/css/sidebar-hover.css?v=4">
<link rel="stylesheet" href="assets/css/admin-sidebar.css?v=1">
</head>
<body class="ps-hover-sidebar admin-portal">
<div class="ps-shell">
