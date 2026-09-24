-- =====================================================================
-- 010-donation-no-without-year.sql
-- Donation numbers drop the year: DON-<NNNN>, one running sequence
-- (ps_insert_with_reference(..., false) in includes/request-forms.php).
-- Donations numbered DON-<YEAR>-<NNNN> before this are renumbered, in the
-- order they were submitted, after any DON-<NNNN> already there. Their
-- proof files stay where they are (proof_of_payment keeps the full path).
-- Safe to re-run:
--
--   C:\xampp\mysql\bin\mysql.exe -u root parish_serve < database\migrations\010-donation-no-without-year.sql
-- =====================================================================

SET @n := (SELECT COALESCE(MAX(CAST(SUBSTRING(donation_no, 5) AS UNSIGNED)), 0)
             FROM donations WHERE donation_no REGEXP '^DON-[0-9]+$');
UPDATE donations
   SET donation_no = CONCAT('DON-', IF(@n + 1 < 10000, LPAD(@n := @n + 1, 4, '0'), @n := @n + 1))
 WHERE donation_no REGEXP '^DON-[0-9]{4}-[0-9]+$'
 ORDER BY id;
