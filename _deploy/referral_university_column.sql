-- =====================================================================
--  Huduma Portal — Rafiki Rewards — Add university column for University League
--  Version: 1.0
--  Date:    2026-09-11
--  Safe to run multiple times (idempotent).
-- =====================================================================
--
--  Adds a nullable `university` varchar column to users so we can group
--  referrals by university for the "University League" leaderboard.
--
--  Populated by users themselves via a small opt-in profile field.
--  Only users who set it appear in the university league — no privacy
--  concerns for those who leave it blank.
-- =====================================================================

START TRANSACTION;

SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users'
             AND COLUMN_NAME = 'university');
SET @sql := IF(@col = 0,
    'ALTER TABLE `users` ADD COLUMN `university` VARCHAR(120) NULL AFTER `service_area`',
    'SELECT "col university already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Index on it so grouping is fast
SET @idx := (SELECT COUNT(*) FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users'
             AND INDEX_NAME = 'users_university_index');
SET @sql := IF(@idx = 0,
    'ALTER TABLE `users` ADD INDEX `users_university_index` (`university`)',
    'SELECT "idx university exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

COMMIT;

-- Rollback:
-- ALTER TABLE users DROP INDEX users_university_index;
-- ALTER TABLE users DROP COLUMN university;
