<?php
/**
 * topbar.php
 * ---------------------------------------------------------------------
 * The notification-bell + user-identity chip that sits in the top
 * right of every page's header banner. This showed up in the
 * Announcements reference image (bell with a badge count, avatar
 * initial, name + role, dropdown chevron) but logically belongs on
 * EVERY logged-in page, not just this one -- so it's its own include
 * rather than something copy-pasted into announcements.php. We also
 * added it to dashboard.php's hero for consistency (see that file).
 *
 * WHERE IT GETS PLACED:
 * This only outputs the .ps-topbar markup itself -- positioning is
 * left to whatever hero/banner container the calling page wraps it
 * in, since every page's hero looks a little different (dashboard's
 * is a small rounded photo, announcements' is a full-width banner).
 * Each page's CSS just needs `position: relative` on that wrapper and
 * `.ps-topbar { position: absolute; top: ...; right: ...; }` -- that
 * part already lives in style.css since the topbar itself is shared.
 *
 * HARDCODED FOR NOW (no backend this session):
 *   $userFirstName / $userRole / $notifCount can be set by the
 *   calling page before requiring this file; sensible defaults below
 *   otherwise. NOTE: the reference mockups literally show "qweqwe" as
 *   the test username -- we intentionally used the same demo identity
 *   as the dashboard (Juan Dela Cruz / Parishioner) instead, so the
 *   "logged in user" looks consistent across pages instead of jumping
 *   between two different placeholder names. Once login.php exists,
 *   all of this comes from $_SESSION instead.
 * ---------------------------------------------------------------------
 */
require_once __DIR__ . '/icons.php';

if (!isset($userFirstName)) { $userFirstName = 'Juan'; }
if (!isset($userRole))      { $userRole = 'Parishioner'; }
if (!isset($notifCount))    { $notifCount = 3; }

$userInitial = strtoupper(substr($userFirstName, 0, 1));
?>
<div class="ps-topbar">
    <!-- notifications.php doesn't exist yet -- bell is a static badge
         for now, wired up once we build a real notification feed -->
    <button type="button" class="ps-notif-btn" aria-label="Notifications">
        <?php ps_icon('bell'); ?>
        <?php if ($notifCount > 0): ?>
            <span class="ps-notif-badge"><?php echo (int) $notifCount; ?></span>
        <?php endif; ?>
    </button>

    <div class="ps-user-chip">
        <span class="ps-user-avatar"><?php echo htmlspecialchars($userInitial); ?></span>
        <span class="ps-user-info">
            <strong><?php echo htmlspecialchars($userFirstName); ?></strong>
            <small><?php echo htmlspecialchars($userRole); ?></small>
        </span>
        <?php ps_icon('chevron-down', 'ps-user-chevron'); ?>
    </div>
</div>
