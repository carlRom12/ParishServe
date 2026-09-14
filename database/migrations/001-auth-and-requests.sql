-- =====================================================================
-- 001-auth-and-requests.sql
-- Additive migration for the LIVE parish_serve database. Do NOT import
-- database/schema.sql over it -- that file's users table is a stale demo
-- design. Safe to re-run (IF NOT EXISTS / INSERT IGNORE throughout):
--
--   C:\xampp\mysql\bin\mysql.exe -u root parish_serve < database\migrations\001-auth-and-requests.sql
-- =====================================================================

-- ---------------------------------------------------------------------
-- users
-- * otp_hash / otp_expires_at become nullable: verify-otp.php clears them
--   with NULL, which only "worked" before because sql_mode is non-strict.
-- * Password reset gets its OWN code columns so a pending registration
--   and a pending reset on the same account can't overwrite each other.
--   reset_otp_attempts caps wrong guesses per code (verify-reset-otp.php).
-- ---------------------------------------------------------------------
ALTER TABLE users
    MODIFY otp_hash       VARCHAR(255) NULL DEFAULT NULL,
    MODIFY otp_expires_at DATETIME     NULL DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS reset_otp_hash       VARCHAR(255)     NULL DEFAULT NULL AFTER otp_expires_at,
    ADD COLUMN IF NOT EXISTS reset_otp_expires_at DATETIME         NULL DEFAULT NULL AFTER reset_otp_hash,
    ADD COLUMN IF NOT EXISTS reset_otp_attempts   TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER reset_otp_expires_at;

UPDATE users SET otp_hash = NULL WHERE otp_hash = '';
UPDATE users SET otp_expires_at = NULL WHERE otp_expires_at = '0000-00-00 00:00:00';


-- ---------------------------------------------------------------------
-- Request tables -- column design copied from database/schema.sql (see
-- its group notes: requests link to parishioners by contact_number, and
-- every table shares one status pipeline so they can be UNIONed).
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS wedding_requests (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    reference_no    VARCHAR(20)  NOT NULL UNIQUE,
    contact_number  VARCHAR(11)  NOT NULL,
    bride_name      VARCHAR(150) NOT NULL,
    groom_name      VARCHAR(150) NOT NULL,
    preferred_date  DATE         NOT NULL,
    preferred_time  TIME         NULL,
    status          ENUM('submitted','under_review','approved','scheduled','completed','rejected')
                        NOT NULL DEFAULT 'submitted',
    remarks         TEXT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS baptism_requests (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    reference_no    VARCHAR(20)  NOT NULL UNIQUE,
    contact_number  VARCHAR(11)  NOT NULL,
    child_name      VARCHAR(150) NOT NULL,
    parent_names    VARCHAR(200) NULL,
    preferred_date  DATE         NOT NULL,
    preferred_time  TIME         NULL,
    status          ENUM('submitted','under_review','approved','scheduled','completed','rejected')
                        NOT NULL DEFAULT 'submitted',
    remarks         TEXT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS confirmation_requests (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    reference_no    VARCHAR(20)  NOT NULL UNIQUE,
    contact_number  VARCHAR(11)  NOT NULL,
    applicant_name  VARCHAR(150) NOT NULL,
    preferred_date  DATE         NOT NULL,
    preferred_time  TIME         NULL,
    status          ENUM('submitted','under_review','approved','scheduled','completed','rejected')
                        NOT NULL DEFAULT 'submitted',
    remarks         TEXT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS funeral_requests (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    reference_no    VARCHAR(20)  NOT NULL UNIQUE,
    contact_number  VARCHAR(11)  NOT NULL,
    deceased_name   VARCHAR(150) NOT NULL,
    service_date    DATE         NOT NULL,
    service_time    TIME         NULL,
    status          ENUM('submitted','under_review','approved','scheduled','completed','rejected')
                        NOT NULL DEFAULT 'submitted',
    remarks         TEXT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS counseling_appointments (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    reference_no    VARCHAR(20)  NOT NULL UNIQUE,
    contact_number  VARCHAR(11)  NOT NULL,
    requester_name  VARCHAR(150) NOT NULL,
    concern_type    VARCHAR(150) NULL,
    preferred_date  DATE         NOT NULL,
    preferred_time  TIME         NULL,
    status          ENUM('submitted','under_review','approved','scheduled','completed','rejected')
                        NOT NULL DEFAULT 'submitted',
    remarks         TEXT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS mass_intentions (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    reference_no    VARCHAR(20)  NOT NULL UNIQUE,
    contact_number  VARCHAR(11)  NOT NULL,
    requester_name  VARCHAR(150) NOT NULL,
    intention_type  ENUM('thanksgiving','petition','healing','departed') NOT NULL,
    intention_for   VARCHAR(150) NULL COMMENT 'name of person the mass is offered for',
    mass_date       DATE         NOT NULL,
    mass_time       TIME         NULL,
    status          ENUM('submitted','under_review','approved','scheduled','completed','rejected')
                        NOT NULL DEFAULT 'submitted',
    remarks         TEXT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS facility_reservations (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    reference_no    VARCHAR(20)  NOT NULL UNIQUE,
    contact_number  VARCHAR(11)  NOT NULL,
    requester_name  VARCHAR(150) NOT NULL,
    facility_name   VARCHAR(150) NOT NULL,
    reservation_date DATE        NOT NULL,
    start_time      TIME         NULL,
    end_time        TIME         NULL,
    purpose         VARCHAR(255) NULL,
    status          ENUM('submitted','under_review','approved','scheduled','completed','rejected')
                        NOT NULL DEFAULT 'submitted',
    remarks         TEXT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS donations (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    reference_no        VARCHAR(20)  NOT NULL UNIQUE,
    contact_number      VARCHAR(11)  NOT NULL,
    donor_name          VARCHAR(150) NOT NULL,
    amount              DECIMAL(10,2) NOT NULL,
    purpose             VARCHAR(150) NULL COMMENT 'e.g. general fund, church repair, charity drive',
    proof_of_payment    VARCHAR(255) NULL COMMENT 'uploaded receipt/screenshot file path, verified on-site',
    status              ENUM('submitted','under_review','approved','scheduled','completed','rejected')
                            NOT NULL DEFAULT 'submitted',
    remarks             TEXT NULL,
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- request_documents (new -- not in schema.sql)
-- What has come in for each sacrament request's required-document
-- checklist. The checklist LABELS live in includes/request-types.php
-- (PS_DOCUMENT_CHECKLISTS); a missing row just means "not received yet".
-- Keyed by type + id because each request table has its own id sequence.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS request_documents (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    request_type    VARCHAR(20)  NOT NULL COMMENT 'wedding | baptism | confirmation | funeral',
    request_id      INT          NOT NULL,
    document_label  VARCHAR(100) NOT NULL,
    received        TINYINT(1)   NOT NULL DEFAULT 0,
    file_path       VARCHAR(255) NULL COMMENT 'uploaded scan/photo, relative to the site root',
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_request_document (request_type, request_id, document_label)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- No seed rows: requests and donations only ever come from submitting
-- the real public forms (includes/request-forms.php).
