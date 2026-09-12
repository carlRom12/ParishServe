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
 * includes/footer.php closes the wrapper.
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

<link rel="stylesheet" href="assets/css/style.css">

<?php foreach ($pageCssFiles as $cssFile): ?>
<link rel="stylesheet" href="assets/css/<?php echo htmlspecialchars($cssFile); ?>">
<?php endforeach; ?>
<link rel="stylesheet" href="assets/css/responsive.css?v=3">
</head>
<body>
<div class="ps-shell">
