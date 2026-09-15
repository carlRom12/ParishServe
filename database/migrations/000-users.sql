-- =====================================================================
-- 000-users.sql
-- The users table, as it exists in the live parish_serve database (with
-- 001's columns already in it). The table predates the migrations -- this
-- file records it so a fresh database can be built from database/migrations/
-- alone, in order 000, 001, 002, ... On an existing database it does
-- nothing, and 001's ALTERs stay harmless no-ops on a table created here.
--
--   C:\xampp\mysql\bin\mysql.exe -u root parish_serve < database\migrations\000-users.sql
-- =====================================================================

CREATE TABLE IF NOT EXISTS users (
    id                   INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    firstname            VARCHAR(255)     NOT NULL,
    middlename           VARCHAR(255)     NULL DEFAULT NULL,
    lastname             VARCHAR(255)     NOT NULL,
    suffix               VARCHAR(20)      NULL DEFAULT NULL,
    date_of_birth        DATE             NOT NULL,
    gender               ENUM('Female','Male','Prefer not to say') NOT NULL,
    mobile_number        VARCHAR(11)      NOT NULL,
    email                VARCHAR(255)     NOT NULL,
    password_hash        VARCHAR(255)     NOT NULL,
    role                 ENUM('Parishioner','Admin','Super Admin') NOT NULL DEFAULT 'Parishioner',
    email_verified       TINYINT(1)       NOT NULL DEFAULT 0,
    otp_hash             VARCHAR(255)     NULL DEFAULT NULL,
    otp_expires_at       DATETIME         NULL DEFAULT NULL,
    reset_otp_hash       VARCHAR(255)     NULL DEFAULT NULL,
    reset_otp_expires_at DATETIME         NULL DEFAULT NULL,
    reset_otp_attempts   TINYINT UNSIGNED NOT NULL DEFAULT 0,
    created_at           TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at           TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status               ENUM('Active','Suspended') NOT NULL DEFAULT 'Active',
    PRIMARY KEY (id),
    UNIQUE KEY mobile_number (mobile_number),
    UNIQUE KEY email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- No accounts are seeded. Create the first Super Admin directly in the
-- database; Admins are promoted from verified accounts on admin-accounts.php.
