-- =====================================================================
-- 003-announcements.sql
-- Parish announcements, managed by staff on admin-announcements.php and
-- shown on announcements.html / dashboard.html (announcements-data.php).
-- Additive and safe to re-run:
--
--   C:\xampp\mysql\bin\mysql.exe -u root parish_serve < database\migrations\003-announcements.sql
-- =====================================================================

-- category: the tabs on announcements.html (PS_ANNOUNCEMENT_CATEGORIES in
-- includes/announcements.php). posted_date in the future = scheduled:
-- staff see it, the public pages don't until that day.
CREATE TABLE IF NOT EXISTS announcements (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(150) NOT NULL,
    body         TEXT         NOT NULL,
    category     ENUM('Parish News', 'Events', 'Mass & Liturgical', 'Wedding Banns', 'Reminders', 'Notices')
                     NOT NULL DEFAULT 'Parish News',
    image_path   VARCHAR(255) NULL COMMENT 'uploads/announcements/..., served by announcement-image.php',
    is_featured  TINYINT(1)   NOT NULL DEFAULT 0,
    posted_date  DATE         NOT NULL,
    created_by   INT UNSIGNED NULL COMMENT 'users.id of the staff member who posted it',
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_announcements_posted (posted_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- No seed rows: announcements only ever come from staff posting them.
