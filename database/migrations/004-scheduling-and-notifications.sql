-- =====================================================================
-- 004-scheduling-and-notifications.sql
-- Schema additions for status-change emails (includes/notifications.php)
-- and schedule conflict checks (includes/request-types.php). Additive and
-- safe to re-run:
--
--   C:\xampp\mysql\bin\mysql.exe -u root parish_serve < database\migrations\004-scheduling-and-notifications.sql
-- =====================================================================

-- Where status-change emails go. The public forms already collected an
-- email address, but only into the details JSON; new submissions fill
-- this column directly (includes/request-forms.php).
ALTER TABLE wedding_requests        ADD COLUMN IF NOT EXISTS contact_email VARCHAR(150) NULL AFTER contact_number;
ALTER TABLE baptism_requests        ADD COLUMN IF NOT EXISTS contact_email VARCHAR(150) NULL AFTER contact_number;
ALTER TABLE confirmation_requests   ADD COLUMN IF NOT EXISTS contact_email VARCHAR(150) NULL AFTER contact_number;
ALTER TABLE funeral_requests        ADD COLUMN IF NOT EXISTS contact_email VARCHAR(150) NULL AFTER contact_number;
ALTER TABLE counseling_appointments ADD COLUMN IF NOT EXISTS contact_email VARCHAR(150) NULL AFTER contact_number;
ALTER TABLE mass_intentions         ADD COLUMN IF NOT EXISTS contact_email VARCHAR(150) NULL AFTER contact_number;
ALTER TABLE facility_reservations   ADD COLUMN IF NOT EXISTS contact_email VARCHAR(150) NULL AFTER contact_number;
ALTER TABLE donations               ADD COLUMN IF NOT EXISTS contact_email VARCHAR(150) NULL AFTER contact_number;

-- Rows submitted before this migration: copy the address out of details.
UPDATE wedding_requests      SET contact_email = JSON_UNQUOTE(JSON_EXTRACT(details, '$."Email address"'))
 WHERE contact_email IS NULL AND JSON_VALID(details) AND JSON_EXTRACT(details, '$."Email address"') IS NOT NULL;
UPDATE baptism_requests      SET contact_email = JSON_UNQUOTE(JSON_EXTRACT(details, '$."Email address"'))
 WHERE contact_email IS NULL AND JSON_VALID(details) AND JSON_EXTRACT(details, '$."Email address"') IS NOT NULL;
UPDATE confirmation_requests SET contact_email = JSON_UNQUOTE(JSON_EXTRACT(details, '$."Email address"'))
 WHERE contact_email IS NULL AND JSON_VALID(details) AND JSON_EXTRACT(details, '$."Email address"') IS NOT NULL;
UPDATE funeral_requests      SET contact_email = JSON_UNQUOTE(JSON_EXTRACT(details, '$."Email address"'))
 WHERE contact_email IS NULL AND JSON_VALID(details) AND JSON_EXTRACT(details, '$."Email address"') IS NOT NULL;
UPDATE mass_intentions       SET contact_email = JSON_UNQUOTE(JSON_EXTRACT(details, '$."Email address"'))
 WHERE contact_email IS NULL AND JSON_VALID(details) AND JSON_EXTRACT(details, '$."Email address"') IS NOT NULL;
UPDATE donations             SET contact_email = JSON_UNQUOTE(JSON_EXTRACT(details, '$."Email address"'))
 WHERE contact_email IS NULL AND JSON_VALID(details) AND JSON_EXTRACT(details, '$."Email address"') IS NOT NULL;

-- Conflict checks look bookings up by date + status.
ALTER TABLE wedding_requests        ADD INDEX IF NOT EXISTS idx_schedule (preferred_date, status);
ALTER TABLE baptism_requests        ADD INDEX IF NOT EXISTS idx_schedule (preferred_date, status);
ALTER TABLE confirmation_requests   ADD INDEX IF NOT EXISTS idx_schedule (preferred_date, status);
ALTER TABLE funeral_requests        ADD INDEX IF NOT EXISTS idx_schedule (service_date, status);
ALTER TABLE counseling_appointments ADD INDEX IF NOT EXISTS idx_schedule (preferred_date, status);
ALTER TABLE mass_intentions         ADD INDEX IF NOT EXISTS idx_schedule (mass_date, status);
ALTER TABLE facility_reservations   ADD INDEX IF NOT EXISTS idx_schedule (reservation_date, status);
