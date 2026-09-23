-- =====================================================================
-- 008-donation-gcash-reference.sql
-- The GCash reference number a donor enters (digits from their GCash
-- receipt) gets its own column, so staff can see, search and match it on
-- admin-donations.php and in reports. It was only kept in the details
-- JSON before. Additive and safe to re-run:
--
--   C:\xampp\mysql\bin\mysql.exe -u root parish_serve < database\migrations\008-donation-gcash-reference.sql
-- =====================================================================

ALTER TABLE donations ADD COLUMN IF NOT EXISTS gcash_reference VARCHAR(20) NULL AFTER amount;
ALTER TABLE donations ADD INDEX IF NOT EXISTS idx_donation_gcash_reference (gcash_reference);

-- Rows submitted before this migration: move the number out of details.
UPDATE donations
   SET gcash_reference = JSON_UNQUOTE(JSON_EXTRACT(details, '$."GCash reference number"'))
 WHERE gcash_reference IS NULL AND JSON_VALID(details) AND JSON_EXTRACT(details, '$."GCash reference number"') IS NOT NULL;
UPDATE donations
   SET details = JSON_REMOVE(details, '$."GCash reference number"')
 WHERE gcash_reference IS NOT NULL AND JSON_VALID(details) AND JSON_EXTRACT(details, '$."GCash reference number"') IS NOT NULL;
