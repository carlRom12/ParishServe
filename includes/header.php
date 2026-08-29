<?php
/**
 * header.php
 * ---------------------------------------------------------------------
 * Shared top of every logged-in page. Opens <html>, sets up <head>
 * (fonts, base CSS, plus whatever CSS the page itself needs), then
 * opens <body> and the .ps-shell wrapper that the sidebar + main
 * content both live inside.
 *
 * HOW A PAGE USES THIS FILE:
 *   Before requiring this file, a page can optionally set:
 *     $pageTitle  -> shows in the browser tab, e.g. "Dashboard"
 *     $pageCss    -> extra stylesheet filename(s) inside assets/css/,
 *                    e.g. "dashboard.css" (loaded AFTER style.css so
 *                    it can override shared rules if needed). Can be a
 *                    single string, OR an array of filenames when a
 *                    page needs more than one (e.g. baptism-request-
 *                    step2.php loads the shared wedding-request.css
 *                    AND its own small baptism-request-step2.css) --
 *                    each loads in array order, after style.css.
 *     $activeNav  -> which sidebar link should get the "active" gold
 *                    highlight (sidebar.php reads this). Matches the
 *                    'key' values used in the $navItems list below,
 *                    e.g. "dashboard", "announcements", "wedding"...
 *
 * IMPORTANT - NO LOGIN CHECK YET:
 * A real header.php is supposed to bounce anyone without a session
 * back to login.php. We haven't built login.php / config.php / the
 * MySQL connection yet (that's next session's job per the group's
 * plan), so this file is pure presentation for now. When we wire the
 * backend, the auth guard goes right at the top of this file, BEFORE
 * anything is echoed out, like this:
 *
 *   require_once __DIR__ . '/../config.php'; // starts the session
 *   if (empty($_SESSION['user_id'])) {
 *       header('Location: /login.php');
 *       exit;
 *   }
 *
 * Until then, dashboard.php just hardcodes a sample $userName so the
 * page has something to greet.
 * ---------------------------------------------------------------------
 */

if (!isset($pageTitle)) {
    $pageTitle = 'ParishServe';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($pageTitle); ?> · ParishServe</title>

<!-- shared base styles: colors, fonts, sidebar shell, cards, buttons -->
<link rel="stylesheet" href="assets/css/style.css">

<?php if (!empty($pageCss)): ?>
<!-- page-specific styles, loaded after style.css so it can extend it --
     $pageCss may be a single filename or an array of them -->
<?php foreach ((array) $pageCss as $cssFile): ?>
<link rel="stylesheet" href="assets/css/<?php echo htmlspecialchars($cssFile); ?>">
<?php endforeach; ?>
<?php endif; ?>
</head>
<body>
<div class="ps-shell">
