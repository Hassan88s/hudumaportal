-- =====================================================================
--  Huduma Portal — Rafiki Rewards — Fraud Detection (Phase 1)
--  Version: 1.0
--  Date:    2026-09-10
--  Safe to run multiple times (idempotent).
-- =====================================================================
--
--  What this migration does:
--    (A) Adds `fraud_flags` JSON column to referrals (nullable)
--    (B) Expands referrals.status enum to include 'flagged'
--    (C) Adds new static_options for fraud thresholds
--
--  ROLLBACK: see bottom of file (commented) — run to undo.
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;

-- ---------------------------------------------------------------------
-- (A) Add fraud_flags column if missing
-- ---------------------------------------------------------------------
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'referrals'
             AND COLUMN_NAME = 'fraud_flags');
SET @sql := IF(@col = 0,
    'ALTER TABLE `referrals` ADD COLUMN `fraud_flags` JSON NULL AFTER `rejection_reason`',
    'SELECT "col fraud_flags already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Index on JSON extract for the highest severity (used by admin list filter)
SET @idx := (SELECT COUNT(*) FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'referrals'
             AND INDEX_NAME = 'referrals_fraud_flag_index');
SET @sql := IF(@idx = 0,
    'ALTER TABLE `referrals` ADD INDEX `referrals_fraud_flag_index` ((CAST(JSON_LENGTH(`fraud_flags`) AS UNSIGNED)))',
    'SELECT "idx fraud_flag exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------------
-- (B) Expand status enum to include 'flagged'
-- ---------------------------------------------------------------------
--    Old enum: pending, qualifying, approved, rejected, blocked
--    New enum: pending, qualifying, approved, rejected, blocked, flagged
ALTER TABLE `referrals`
    MODIFY COLUMN `status` ENUM('pending','qualifying','approved','rejected','blocked','flagged')
    NOT NULL DEFAULT 'pending';

-- ---------------------------------------------------------------------
-- (C) Fraud detection settings (admin-configurable thresholds)
-- ---------------------------------------------------------------------
INSERT INTO `static_options` (`option_name`, `option_value`, `created_at`, `updated_at`)
SELECT 'referral_fraud_ip_daily_limit' AS a, '3' AS b, NOW() AS c, NOW() AS d
WHERE NOT EXISTS (SELECT 1 FROM `static_options` WHERE `option_name` = 'referral_fraud_ip_daily_limit');

INSERT INTO `static_options` (`option_name`, `option_value`, `created_at`, `updated_at`)
SELECT 'referral_fraud_email_domain_limit' AS a, '5' AS b, NOW() AS c, NOW() AS d
WHERE NOT EXISTS (SELECT 1 FROM `static_options` WHERE `option_name` = 'referral_fraud_email_domain_limit');

INSERT INTO `static_options` (`option_name`, `option_value`, `created_at`, `updated_at`)
SELECT 'referral_fraud_phone_prefix_len' AS a, '9' AS b, NOW() AS c, NOW() AS d
WHERE NOT EXISTS (SELECT 1 FROM `static_options` WHERE `option_name` = 'referral_fraud_phone_prefix_len');

INSERT INTO `static_options` (`option_name`, `option_value`, `created_at`, `updated_at`)
SELECT 'referral_fraud_referrer_daily_limit' AS a, '10' AS b, NOW() AS c, NOW() AS d
WHERE NOT EXISTS (SELECT 1 FROM `static_options` WHERE `option_name` = 'referral_fraud_referrer_daily_limit');

INSERT INTO `static_options` (`option_name`, `option_value`, `created_at`, `updated_at`)
SELECT 'referral_fraud_auto_flag' AS a, '1' AS b, NOW() AS c, NOW() AS d
WHERE NOT EXISTS (SELECT 1 FROM `static_options` WHERE `option_name` = 'referral_fraud_auto_flag');

COMMIT;
SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- ROLLBACK (uncomment to undo)
-- =====================================================================
-- ALTER TABLE `referrals` DROP INDEX `referrals_fraud_flag_index`;
-- ALTER TABLE `referrals` DROP COLUMN `fraud_flags`;
-- ALTER TABLE `referrals` MODIFY COLUMN `status`
--     ENUM('pending','qualifying','approved','rejected','blocked') NOT NULL DEFAULT 'pending';
-- DELETE FROM `static_options` WHERE `option_name` LIKE 'referral_fraud_%';
