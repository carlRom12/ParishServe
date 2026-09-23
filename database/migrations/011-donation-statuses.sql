-- =====================================================================
-- 011-donation-statuses.sql
-- Donations have their own, shorter status list: a new donation is
-- Under Review until staff check its proof of payment, then Approved or
-- Rejected (PS_DONATION_STATUS_OPTIONS in includes/request-types.php).
-- Submitted rows become Under Review; Scheduled and Completed ones --
-- both already verified -- become Approved. Safe to re-run:
--
--   C:\xampp\mysql\bin\mysql.exe -u root parish_serve < database\migrations\011-donation-statuses.sql
-- =====================================================================

-- Widen first (a no-op on re-run) so the rows can be moved before the old values go.
ALTER TABLE donations MODIFY status ENUM('submitted','under_review','approved','scheduled','completed','rejected') NOT NULL DEFAULT 'under_review';
UPDATE donations SET status = 'under_review' WHERE status = 'submitted';
UPDATE donations SET status = 'approved' WHERE status IN ('scheduled', 'completed');
ALTER TABLE donations MODIFY status ENUM('under_review','approved','rejected') NOT NULL DEFAULT 'under_review';
