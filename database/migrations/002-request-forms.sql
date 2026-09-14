-- =====================================================================
-- 002-request-forms.sql
-- Schema additions for the public request forms
-- (includes/request-forms.php). Additive and safe to re-run:
--
--   C:\xampp\mysql\bin\mysql.exe -u root parish_serve < database\migrations\002-request-forms.sql
-- =====================================================================

-- Everything a form collects that has no column of its own, stored as
-- {"Label": "value"} and shown to staff in the admin Update window.
ALTER TABLE wedding_requests        ADD COLUMN IF NOT EXISTS details JSON NULL AFTER remarks;
ALTER TABLE baptism_requests        ADD COLUMN IF NOT EXISTS details JSON NULL AFTER remarks;
ALTER TABLE confirmation_requests   ADD COLUMN IF NOT EXISTS details JSON NULL AFTER remarks;
ALTER TABLE funeral_requests        ADD COLUMN IF NOT EXISTS details JSON NULL AFTER remarks;
ALTER TABLE counseling_appointments ADD COLUMN IF NOT EXISTS details JSON NULL AFTER remarks;
ALTER TABLE mass_intentions         ADD COLUMN IF NOT EXISTS details JSON NULL AFTER remarks;
ALTER TABLE facility_reservations   ADD COLUMN IF NOT EXISTS details JSON NULL AFTER remarks;
ALTER TABLE donations               ADD COLUMN IF NOT EXISTS details JSON NULL AFTER remarks;

-- The Confirmation application has no date field (the parish sets the
-- Confirmation schedule), so a new application can't supply one.
ALTER TABLE confirmation_requests MODIFY preferred_date DATE NULL;

-- The five intention types the Mass Intention form actually offers.
ALTER TABLE mass_intentions MODIFY intention_type
    ENUM('For the Deceased', 'For the Living', 'Thanksgiving', 'Milestones & Celebrations', 'Special Intention') NOT NULL;

-- Keep the file name the parishioner uploaded, and when.
ALTER TABLE request_documents
    ADD COLUMN IF NOT EXISTS original_name VARCHAR(255) NULL AFTER file_path,
    ADD COLUMN IF NOT EXISTS uploaded_at   DATETIME     NULL AFTER original_name;
