<?php
/**
 * logout.php
 * ---------------------------------------------------------------------
 * Target of every sidebar "Log out" link. Wipes the session data and
 * moves to a brand-new session id (the old id's data is deleted, so it's
 * useless even if it leaked), then returns to login.html -- with a
 * confirmation only if someone was actually signed in.
 * ---------------------------------------------------------------------
 */
session_start();
$wasLoggedIn = !empty($_SESSION['user_id']);

$_SESSION = [];
session_regenerate_id(true);

if ($wasLoggedIn) {
    $_SESSION['login_success'] = 'You have been logged out.';
}
header('Location: login.html', true, 303);
exit;
