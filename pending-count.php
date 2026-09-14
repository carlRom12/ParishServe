<?php
/**
 * pending-count.php
 * ---------------------------------------------------------------------
 * Lightweight JSON poll for the admin topbar bell (initNotificationPoll()
 * in main.js, every ~45s): how many requests and donations are still
 * submitted / under review. Admin + Super Admin only.
 * ---------------------------------------------------------------------
 */
$psGuardJson = true;
require __DIR__ . '/includes/auth-guard.php';
require_once __DIR__ . '/includes/request-types.php';

ps_json(200, ['ok' => true] + ps_pending_counts($conn));
