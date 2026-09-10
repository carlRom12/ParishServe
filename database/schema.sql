-- =====================================================================
-- ParishServe Database Schema
-- Our Lady of the Gate Parish Church
-- =====================================================================
-- Group notes (read this before touching the schema):
--
-- 1. We're using CREATE TABLE IF NOT EXISTS everywhere so this file is
--    safe to re-run in phpMyAdmin/mysql CLI without dropping anyone's
--    local data. If you change a column, edit the table by hand or drop
--    it first -- IF NOT EXISTS will NOT alter an existing table.
--
-- 2. Per the capstone paper, requests are tracked by REFERENCE NUMBER,
--    not by a strict user_id foreign key. That's why every service
--    table below stores `contact_number` (the parishioner's mobile
--    number, e.g. 09171234567) instead of a users.id FK. This matches
--    how the PDF describes lightweight tracking -- a parishioner could
--    theoretically submit a request and check on it later just by
--    knowing their contact number + reference number, without even
--    being logged in (useful later if we ever add a "track my request"
--    guest lookup page). The tradeoff: MySQL can't enforce referential
--    integrity for us here, so double-check contact_number spelling in
--    PHP before inserting.
--
-- 3. Every service/request table shares the SAME status pipeline so the
--    dashboard can UNION them together in one query instead of writing
--    8 different status-mapping rules:
--       submitted -> under_review -> approved -> scheduled -> completed
--       (or -> rejected at any point before completed)
--    This maps directly to the "Request Progress" stepper shown in the
--    reference image (Submitted / Under Review / Approved / Scheduled
--    / Completed). The dashboard's "Pending" stat card = submitted +
--    under_review combined (see dashboard.php for that query).
--
-- 4. Tables marked "INFERRED" below are not explicitly named as
--    features in the PDF's Objectives section, but the dashboard image
--    needs them to not be empty/fake. Flagging these so the group can
--    confirm they're okay to keep before the next defense.
-- =====================================================================

-- ---------------------------------------------------------------------
-- users
-- Core account table for both parishioners and admin/staff.
-- role decides which sidebar/dashboard view they get and gates access
-- to admin-only pages later (facility approval, reports, etc.)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    full_name       VARCHAR(150)        NOT NULL,
    email           VARCHAR(150)        NOT NULL UNIQUE,
    password_hash   VARCHAR(255)        NOT NULL,
    -- PH mobile format 09XXXXXXXXX, validated in PHP with preg_match
    -- before it ever reaches this table. UNIQUE because we use this
    -- number to link a parishioner to their service requests.
    contact_number  VARCHAR(11)         NOT NULL UNIQUE,
    role            ENUM('parishioner','admin') NOT NULL DEFAULT 'parishioner',
    created_at      TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ---------------------------------------------------------------------
-- wedding_requests  (Sacraments > Wedding)
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


-- ---------------------------------------------------------------------
-- baptism_requests  (Sacraments > Baptism)
-- ---------------------------------------------------------------------
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


-- ---------------------------------------------------------------------
-- confirmation_requests  (Sacraments > Confirmation)
-- INFERRED: not in the PDF's Objectives list of services, but it's a
-- sidebar item in the reference image under Sacraments, right next to
-- Wedding/Baptism/Funeral. NOTE: this is scheduling the sacrament
-- itself, not requesting a "Confirmation Certificate" document -- the
-- PDF explicitly excludes certificate requests from the system
-- (Data Privacy Act, see Scope and Limitations). Confirm with the
-- group whether this page is actually in scope before building it out
-- next session.
-- ---------------------------------------------------------------------
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


-- ---------------------------------------------------------------------
-- funeral_requests  (Sacraments > Funeral)
-- ---------------------------------------------------------------------
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


-- ---------------------------------------------------------------------
-- counseling_appointments  (Parish Services > Counseling)
-- ---------------------------------------------------------------------
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


-- ---------------------------------------------------------------------
-- mass_intentions  (Parish Services > Mass Intention)
-- intention_type options straight from the PDF's Objectives section.
-- ---------------------------------------------------------------------
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


-- ---------------------------------------------------------------------
-- facility_reservations  (Parish Services > Facility Reservation)
-- ---------------------------------------------------------------------
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


-- ---------------------------------------------------------------------
-- donations  (Parish Services > Donations)
-- PDF Scope/Limitations says NO payment gateway API -- parishioners pay
-- manually and upload a proof of payment for staff to verify on-site.
-- That's why there's a proof_of_payment file path column instead of
-- any transaction/gateway reference.
-- ---------------------------------------------------------------------
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
-- daily_schedule  (dashboard "Today at Our Lady of the Gate" timeline)
-- INFERRED from the image, not named in the PDF. We modeled this as a
-- RECURRING daily template (Morning Mass, Confession, Evening Mass,
-- etc.) rather than a date-specific table, because the PDF's actual
-- event scheduling already lives in the service request tables above
-- (weddings, baptisms...) and in the Parish Calendar module we're
-- building next session. This table is just the fixed daily agenda
-- (regular mass times + confession hours) that's always true "today".
-- If the group wants day-specific overrides later (e.g. no morning
-- mass on a feast day), we'd add a `event_date` column then.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS daily_schedule (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    event_time  TIME         NOT NULL,
    title       VARCHAR(150) NOT NULL,
    location    VARCHAR(150) NOT NULL,
    sort_order  INT          NOT NULL DEFAULT 0,
    is_active   TINYINT(1)   NOT NULL DEFAULT 1,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ---------------------------------------------------------------------
-- announcements  (sidebar "Announcements" page + dashboard's "Parish
-- Updates" featured card + "Recent Announcements" grid)
-- is_featured=1 means it shows in the big "Parish Updates" spot on the
-- dashboard (image only shows ONE featured item at a time -- Feast Day
-- Celebration). Everything else (featured or not) can also show up in
-- the "Recent Announcements" strip at the bottom, newest first.
-- image_path files referenced by seed data below are simple SVG
-- placeholders we drew ourselves (see /assets/images) so nothing shows
-- up as a broken image icon during the demo -- swap for real photos
-- whenever the parish sends them.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS announcements (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(200) NOT NULL,
    body        TEXT         NOT NULL,
    image_path  VARCHAR(255) NULL,
    is_featured TINYINT(1)   NOT NULL DEFAULT 0,
    posted_date DATE         NOT NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ---------------------------------------------------------------------
-- parish_contacts  (dashboard "Parish Contacts" card)
-- INFERRED from the image. Kept deliberately tiny/simple since this is
-- basically static directory info the admin rarely edits.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS parish_contacts (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    role_label     VARCHAR(100) NOT NULL COMMENT 'e.g. Parish Office, Parish Priest',
    contact_name   VARCHAR(150) NULL COMMENT 'left NULL for office lines that are not a specific person',
    phone_number   VARCHAR(30)  NOT NULL,
    sort_order     INT          NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =====================================================================
-- SEED DATA
-- Enough rows so the dashboard isn't empty when we demo it. All the
-- "My Requests" seed rows below are attached to the DEMO PARISHIONER
-- account (contact_number 09171234567 / juan@example.com) so logging
-- in as that account reproduces something close to the reference
-- image. A freshly REGISTERED account will correctly show all zeros,
-- same as the "qweqwe" test account in the image.
-- =====================================================================

-- Admin account -- password is: Admin@123
-- Parishioner demo account -- password is: Parish@123
-- (Both hashes below were generated with PHP's password_hash() using
-- the default bcrypt algorithm -- see register.php for how new ones
-- get created. Do NOT hardcode plaintext passwords anywhere else.)
INSERT INTO users (full_name, email, password_hash, contact_number, role) VALUES
('Parish Admin', 'admin@parishserve.local', '$2y$10$QP4WrXaAlW1u3vjxltIKYu80IXMxUqwDyzZM9nYsIL7D7alwVu0ky', '09991234567', 'admin'),
('Juan Dela Cruz', 'juan@example.com', '$2y$10$kxMrLhjbszs3./6ayyGYL.g.bu4yQodw8jweAuVTOtWaEqAX4QhE.', '09171234567', 'parishioner')
ON DUPLICATE KEY UPDATE email = email;

-- Wedding request -- Under Review (matches reference image)
INSERT INTO wedding_requests (reference_no, contact_number, bride_name, groom_name, preferred_date, preferred_time, status, created_at) VALUES
('WED-2026-0001', '09171234567', 'Maria Santos', 'Juan Dela Cruz', '2026-10-10', '09:00:00', 'under_review', '2026-08-14 09:00:00')
ON DUPLICATE KEY UPDATE status = status;

-- Baptism request -- Approved (matches reference image)
INSERT INTO baptism_requests (reference_no, contact_number, child_name, parent_names, preferred_date, preferred_time, status, created_at) VALUES
('BAP-2026-0001', '09171234567', 'Baby Sofia Dela Cruz', 'Juan Dela Cruz & Maria Santos', '2026-09-05', '10:00:00', 'approved', '2026-08-10 10:00:00')
ON DUPLICATE KEY UPDATE status = status;

-- Mass intention -- Scheduled (matches reference image)
INSERT INTO mass_intentions (reference_no, contact_number, requester_name, intention_type, intention_for, mass_date, mass_time, status, created_at) VALUES
('MI-2026-0001', '09171234567', 'Juan Dela Cruz', 'thanksgiving', 'Dela Cruz Family', '2026-08-18', '08:00:00', 'scheduled', '2026-08-08 14:00:00')
ON DUPLICATE KEY UPDATE status = status;

-- A few more, spread across the other service tables, so all four stat
-- cards (Pending / Approved / Scheduled / Completed) have something to
-- count for the demo account instead of just the 3 shown in the image.
INSERT INTO funeral_requests (reference_no, contact_number, deceased_name, service_date, service_time, status, created_at) VALUES
('FUN-2026-0001', '09171234567', 'Pedro Dela Cruz Sr.', '2026-07-15', '09:00:00', 'completed', '2026-07-10 08:00:00')
ON DUPLICATE KEY UPDATE status = status;

INSERT INTO counseling_appointments (reference_no, contact_number, requester_name, concern_type, preferred_date, preferred_time, status, created_at) VALUES
('CNS-2026-0001', '09171234567', 'Juan Dela Cruz', 'Marriage counseling', '2026-08-22', '15:00:00', 'submitted', '2026-08-15 11:00:00')
ON DUPLICATE KEY UPDATE status = status;

INSERT INTO facility_reservations (reference_no, contact_number, requester_name, facility_name, reservation_date, start_time, end_time, purpose, status, created_at) VALUES
('FAC-2026-0001', '09171234567', 'Juan Dela Cruz', 'Parish Multipurpose Hall', '2026-09-12', '13:00:00', '17:00:00', 'Baptism reception', 'approved', '2026-08-12 09:30:00')
ON DUPLICATE KEY UPDATE status = status;

INSERT INTO confirmation_requests (reference_no, contact_number, applicant_name, preferred_date, preferred_time, status, created_at) VALUES
('CNF-2026-0001', '09171234567', 'Miguel Dela Cruz', '2026-11-02', '10:00:00', 'scheduled', '2026-08-05 10:00:00')
ON DUPLICATE KEY UPDATE status = status;

INSERT INTO donations (reference_no, contact_number, donor_name, amount, purpose, status, created_at) VALUES
('DON-2026-0001', '09171234567', 'Juan Dela Cruz', 500.00, 'General fund', 'completed', '2026-08-01 16:00:00')
ON DUPLICATE KEY UPDATE status = status;

-- A couple of rows from a DIFFERENT parishioner (not seeded as a user
-- account, just simulating other real parish activity) so admin-side
-- totals aren't laughably tiny once we build the admin dashboard.
INSERT INTO baptism_requests (reference_no, contact_number, child_name, parent_names, preferred_date, preferred_time, status, created_at) VALUES
('BAP-2026-0002', '09209876543', 'Baby Gabriel Reyes', 'Mark Reyes & Angela Reyes', '2026-09-20', '10:00:00', 'submitted', '2026-08-16 08:45:00')
ON DUPLICATE KEY UPDATE status = status;

INSERT INTO facility_reservations (reference_no, contact_number, requester_name, facility_name, reservation_date, start_time, end_time, purpose, status, created_at) VALUES
('FAC-2026-0002', '09151112233', 'Angela Reyes', 'Parish Covered Court', '2026-09-01', '14:00:00', '18:00:00', 'Youth fellowship event', 'under_review', '2026-08-13 10:00:00')
ON DUPLICATE KEY UPDATE status = status;

-- Today's recurring mass/confession schedule (dashboard timeline)
INSERT INTO daily_schedule (event_time, title, location, sort_order) VALUES
('08:00:00', 'Morning Mass', 'Main Church', 1),
('10:00:00', 'Baptism Ceremony', 'Main Church', 2),
('16:00:00', 'Confession', 'Confession Room', 3),
('18:00:00', 'Evening Mass', 'Main Church', 4)
ON DUPLICATE KEY UPDATE title = title;

-- Announcements: one featured (shows in "Parish Updates"), rest show in
-- the "Recent Announcements" grid at the bottom of the dashboard.
INSERT INTO announcements (title, body, image_path, is_featured, posted_date) VALUES
('Feast Day Celebration this Sunday!', 'Join us this coming Sunday for the Feast of the Assumption of Mary. There will be a solemn Mass at 9:00 AM followed by a community gathering.', 'assets/images/announcements/feast-day.svg', 1, '2026-08-16'),
('Adoration Every Friday', 'Eucharistic Adoration is held every Friday after the 6:00 PM Mass.', 'assets/images/announcements/adoration.svg', 0, '2026-05-10'),
('Mass Intentions Now Open', 'You can now submit your Mass intention requests for June.', 'assets/images/announcements/mass-intentions.svg', 0, '2026-05-09'),
('Church Cleaning Drive', 'Let''s keep our church clean and beautiful. See you there!', 'assets/images/announcements/cleaning-drive.svg', 0, '2026-05-08')
ON DUPLICATE KEY UPDATE title = title;

-- Parish contact directory. Fr. Antonio S. Sial is the actual Parish
-- Priest named as a Resource Person in the capstone paper (Appendix A)
-- -- used the real name here instead of a placeholder since we have it.
INSERT INTO parish_contacts (role_label, contact_name, phone_number, sort_order) VALUES
('Parish Office', NULL, '(052) 480-1234', 1),
('Parish Priest', 'Fr. Antonio S. Sial', '(052) 480-1234', 2),
('Counseling Office', NULL, '(052) 480-5678', 3)
ON DUPLICATE KEY UPDATE phone_number = phone_number;
