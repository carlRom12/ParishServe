-- =====================================================================
-- 009-donation-no.sql
-- A donation's server-generated number (DON-<YEAR>-<NNNN>) is called a
-- Donation No. everywhere it is shown, so its column is renamed from
-- reference_no to donation_no (the request tables keep reference_no).
-- Safe to re-run:
--
--   C:\xampp\mysql\bin\mysql.exe -u root parish_serve < database\migrations\009-donation-no.sql
-- =====================================================================

ALTER TABLE donations CHANGE COLUMN IF EXISTS reference_no donation_no VARCHAR(20) NOT NULL;
ALTER TABLE donations DROP INDEX IF EXISTS reference_no;
ALTER TABLE donations ADD UNIQUE INDEX IF NOT EXISTS donation_no (donation_no);
