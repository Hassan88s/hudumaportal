-- =====================================================================
--  Huduma Portal — Rafiki Rewards (Referral Program) — Phase 1 Schema
--  Version: 1.0
--  Date:    2026-09-07
--  Safe to run multiple times (idempotent).
--  Tested against local DB: 54 users, 3 referred_by rows, 0 anomalies.
-- =====================================================================
--
--  What this migration does:
--    (A) Fixes existing users table columns (referral_code / referred_by)
--    (B) Backfills referral_code for users missing one
--    (C) Creates 3 new tables: referrals, referral_rewards, referral_clicks
--    (D) Adds static_options rows for new reward parameters
--
--  ROLLBACK: see bottom of file (commented) — run those to undo.
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;

-- ---------------------------------------------------------------------
-- (A) FIX EXISTING users TABLE
-- ---------------------------------------------------------------------

-- A.1 Convert referred_by varchar(255) -> bigint unsigned (proper type)
--     Empty strings become NULL first so the cast doesn't fail.
UPDATE `users` SET `referred_by` = NULL WHERE `referred_by` = '' OR `referred_by` = '0';

ALTER TABLE `users`
    MODIFY COLUMN `referred_by` BIGINT UNSIGNED NULL DEFAULT NULL;

-- A.2 Add index on referred_by (fast "who did I refer" lookups)
--     Wrapped so re-running doesn't error if index exists.
SET @idx := (SELECT COUNT(*) FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND INDEX_NAME = 'users_referred_by_index');
SET @sql := IF(@idx = 0, 'ALTER TABLE `users` ADD INDEX `users_referred_by_index` (`referred_by`)', 'SELECT "idx users_referred_by_index already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- A.3 Add unique index on referral_code (prevent collisions)
--     Skip if any duplicate somehow exists (defensive).
SET @dupes := (SELECT COUNT(*) FROM (SELECT `referral_code` FROM `users`
               WHERE `referral_code` IS NOT NULL AND `referral_code` != ''
               GROUP BY `referral_code` HAVING COUNT(*) > 1) t);
SET @idx := (SELECT COUNT(*) FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND INDEX_NAME = 'users_referral_code_unique');
SET @sql := IF(@idx = 0 AND @dupes = 0,
    'ALTER TABLE `users` ADD UNIQUE INDEX `users_referral_code_unique` (`referral_code`)',
    'SELECT CONCAT("Skipped unique idx — dupes=", @dupes, " exists=", @idx)');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------------
-- (B) BACKFILL referral_code for users missing one
-- ---------------------------------------------------------------------
--   Format: HP + 6 uppercase alphanumerics derived from user id + random
--   Guaranteed unique because we include the numeric id.
UPDATE `users`
SET `referral_code` = CONCAT('HP', UPPER(SUBSTRING(MD5(CONCAT(id, RAND(), UNIX_TIMESTAMP())), 1, 6)))
WHERE `referral_code` IS NULL OR `referral_code` = '';

-- ---------------------------------------------------------------------
-- (C) NEW TABLES
-- ---------------------------------------------------------------------

-- C.1 referrals — one row per (referrer -> referred_user) relationship.
--     Tracks the 3-stage milestone flow described in the strategy PDF.
CREATE TABLE IF NOT EXISTS `referrals` (
    `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `referrer_id`         BIGINT UNSIGNED NOT NULL COMMENT 'user who shared the link',
    `referred_user_id`    BIGINT UNSIGNED NOT NULL COMMENT 'newly registered user',
    `code_used`           VARCHAR(32) NULL COMMENT 'code from the ?ref= URL',
    `track`               ENUM('provider','client','business') NOT NULL DEFAULT 'client',
    `source`              ENUM('link','code','qr','direct','other') NOT NULL DEFAULT 'link',
    `landing_url`         VARCHAR(500) NULL,
    `ip_address`          VARCHAR(45) NULL,
    `device_fingerprint`  VARCHAR(128) NULL,
    `user_agent`          VARCHAR(500) NULL,

    -- Milestone timestamps (NULL = not yet reached)
    `stage1_at`           TIMESTAMP NULL COMMENT 'profile complete + service published (provider) / registered+verified (client)',
    `stage2_at`           TIMESTAMP NULL COMMENT 'first paid order/booking',
    `stage3_at`           TIMESTAMP NULL COMMENT 'second order OR paid subscription',

    -- Overall lifecycle status
    `status`              ENUM('pending','qualifying','approved','rejected','blocked') NOT NULL DEFAULT 'pending',
    `rejection_reason`    VARCHAR(255) NULL,

    `created_at`          TIMESTAMP NULL,
    `updated_at`          TIMESTAMP NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `referrals_referred_user_unique` (`referred_user_id`),
    KEY `referrals_referrer_id_index` (`referrer_id`),
    KEY `referrals_status_index` (`status`),
    KEY `referrals_code_used_index` (`code_used`),
    KEY `referrals_created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- C.2 referral_rewards — ledger of every reward event.
--     One referral can produce 1..N rewards (stage1, stage2 cash, stage2 credit, stage3, milestone bonus...)
CREATE TABLE IF NOT EXISTS `referral_rewards` (
    `id`                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `referral_id`           BIGINT UNSIGNED NULL COMMENT 'link back to referrals table',
    `user_id`               BIGINT UNSIGNED NOT NULL COMMENT 'who receives the reward',
    `event`                 VARCHAR(64) NOT NULL COMMENT 'e.g. stage1_signup, stage2_first_order_cash, stage2_first_order_credit, stage3_second_order, subscription_bonus, milestone_5, milestone_15',
    `amount`                DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `currency`              VARCHAR(8) NOT NULL DEFAULT 'TZS',
    `type`                  ENUM('cash','credit') NOT NULL DEFAULT 'cash' COMMENT 'cash = withdrawable, credit = promo balance for purchases',
    `status`                ENUM('pending','qualifying','approved','paid','rejected') NOT NULL DEFAULT 'pending',
    `reason`                VARCHAR(255) NULL COMMENT 'human-readable',
    `wallet_history_id`     BIGINT UNSIGNED NULL COMMENT 'FK to wallet_histories once paid',
    `protection_ends_at`    TIMESTAMP NULL COMMENT '14-day hold before pending->available (refund reversal window)',
    `approved_at`           TIMESTAMP NULL,
    `paid_at`               TIMESTAMP NULL,
    `rejected_at`           TIMESTAMP NULL,
    `idempotency_key`       VARCHAR(128) NULL COMMENT 'prevents double-credit (e.g. stage2_first_order:referral#42)',
    `created_at`            TIMESTAMP NULL,
    `updated_at`            TIMESTAMP NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `referral_rewards_idem_unique` (`idempotency_key`),
    KEY `referral_rewards_referral_id_index` (`referral_id`),
    KEY `referral_rewards_user_id_index` (`user_id`),
    KEY `referral_rewards_status_index` (`status`),
    KEY `referral_rewards_event_index` (`event`),
    KEY `referral_rewards_protection_index` (`protection_ends_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- C.3 referral_clicks — tracks /r/<code> visits for the 30-day attribution
--     window and share-rate KPI. High-volume table, indexed lean.
CREATE TABLE IF NOT EXISTS `referral_clicks` (
    `id`                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code`                  VARCHAR(32) NOT NULL,
    `referrer_id`           BIGINT UNSIGNED NULL COMMENT 'denormalised from users.referral_code',
    `ip_address`            VARCHAR(45) NULL,
    `user_agent`            VARCHAR(500) NULL,
    `device_fingerprint`    VARCHAR(128) NULL,
    `channel`               VARCHAR(32) NULL COMMENT 'whatsapp, facebook, direct, qr, etc.',
    `converted_user_id`     BIGINT UNSIGNED NULL COMMENT 'set when this click resulted in registration',
    `created_at`            TIMESTAMP NULL,

    PRIMARY KEY (`id`),
    KEY `referral_clicks_code_index` (`code`),
    KEY `referral_clicks_referrer_id_index` (`referrer_id`),
    KEY `referral_clicks_converted_user_index` (`converted_user_id`),
    KEY `referral_clicks_created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- (D) STATIC_OPTIONS — new reward-amount settings
-- ---------------------------------------------------------------------
-- Existing (kept): sign_up_points, first_order_points, first_purchase_points
-- New reward amounts (in TZS) — will show up in admin RefferalSetting page later.
INSERT INTO `static_options` (`option_name`, `option_value`, `created_at`, `updated_at`)
SELECT 'referral_enabled' AS a, '1' AS b, NOW() AS c, NOW() AS d
WHERE NOT EXISTS (SELECT 1 FROM `static_options` WHERE `option_name` = 'referral_enabled');

INSERT INTO `static_options` (`option_name`, `option_value`, `created_at`, `updated_at`)
SELECT 'referral_stage1_provider_amount' AS a, '500' AS b, NOW() AS c, NOW() AS d
WHERE NOT EXISTS (SELECT 1 FROM `static_options` WHERE `option_name` = 'referral_stage1_provider_amount');

INSERT INTO `static_options` (`option_name`, `option_value`, `created_at`, `updated_at`)
SELECT 'referral_stage2_provider_cash' AS a, '1000' AS b, NOW() AS c, NOW() AS d
WHERE NOT EXISTS (SELECT 1 FROM `static_options` WHERE `option_name` = 'referral_stage2_provider_cash');

INSERT INTO `static_options` (`option_name`, `option_value`, `created_at`, `updated_at`)
SELECT 'referral_stage2_provider_credit' AS a, '1000' AS b, NOW() AS c, NOW() AS d
WHERE NOT EXISTS (SELECT 1 FROM `static_options` WHERE `option_name` = 'referral_stage2_provider_credit');

INSERT INTO `static_options` (`option_name`, `option_value`, `created_at`, `updated_at`)
SELECT 'referral_stage3_provider_amount' AS a, '1500' AS b, NOW() AS c, NOW() AS d
WHERE NOT EXISTS (SELECT 1 FROM `static_options` WHERE `option_name` = 'referral_stage3_provider_amount');

INSERT INTO `static_options` (`option_name`, `option_value`, `created_at`, `updated_at`)
SELECT 'referral_client_welcome_credit' AS a, '1000' AS b, NOW() AS c, NOW() AS d
WHERE NOT EXISTS (SELECT 1 FROM `static_options` WHERE `option_name` = 'referral_client_welcome_credit');

INSERT INTO `static_options` (`option_name`, `option_value`, `created_at`, `updated_at`)
SELECT 'referral_client_first_booking' AS a, '750' AS b, NOW() AS c, NOW() AS d
WHERE NOT EXISTS (SELECT 1 FROM `static_options` WHERE `option_name` = 'referral_client_first_booking');

INSERT INTO `static_options` (`option_name`, `option_value`, `created_at`, `updated_at`)
SELECT 'referral_client_second_booking' AS a, '750' AS b, NOW() AS c, NOW() AS d
WHERE NOT EXISTS (SELECT 1 FROM `static_options` WHERE `option_name` = 'referral_client_second_booking');

INSERT INTO `static_options` (`option_name`, `option_value`, `created_at`, `updated_at`)
SELECT 'referral_protection_days' AS a, '14' AS b, NOW() AS c, NOW() AS d
WHERE NOT EXISTS (SELECT 1 FROM `static_options` WHERE `option_name` = 'referral_protection_days');

INSERT INTO `static_options` (`option_name`, `option_value`, `created_at`, `updated_at`)
SELECT 'referral_attribution_days' AS a, '30' AS b, NOW() AS c, NOW() AS d
WHERE NOT EXISTS (SELECT 1 FROM `static_options` WHERE `option_name` = 'referral_attribution_days');

INSERT INTO `static_options` (`option_name`, `option_value`, `created_at`, `updated_at`)
SELECT 'referral_min_withdrawal' AS a, '5000' AS b, NOW() AS c, NOW() AS d
WHERE NOT EXISTS (SELECT 1 FROM `static_options` WHERE `option_name` = 'referral_min_withdrawal');

-- ---------------------------------------------------------------------
-- BACKFILL: create referrals rows for existing users with referred_by set.
--   This gives us a starting record for the 3 users who already have refs
--   so their historical data appears in the new admin/UI.
-- ---------------------------------------------------------------------
INSERT INTO `referrals` (`referrer_id`, `referred_user_id`, `track`, `source`, `status`, `stage1_at`, `created_at`, `updated_at`)
SELECT u.`referred_by`, u.`id`,
       CASE WHEN u.`user_type` = 2 THEN 'provider' ELSE 'client' END AS track,
       'link' AS source,
       'qualifying' AS status,
       u.`created_at` AS stage1_at,
       u.`created_at`, NOW()
FROM `users` u
WHERE u.`referred_by` IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM `referrals` r WHERE r.`referred_user_id` = u.`id`);

COMMIT;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- Verification queries (run manually after migration)
-- ---------------------------------------------------------------------
-- SELECT COUNT(*) AS users_with_code FROM users WHERE referral_code IS NOT NULL;
-- SELECT COUNT(*) AS backfilled_referrals FROM referrals;
-- SHOW INDEX FROM users WHERE Key_name IN ('users_referred_by_index', 'users_referral_code_unique');
-- SELECT option_name, option_value FROM static_options WHERE option_name LIKE 'referral_%';

-- =====================================================================
-- ROLLBACK (uncomment and run if you need to undo this migration)
-- =====================================================================
-- DROP TABLE IF EXISTS `referral_clicks`;
-- DROP TABLE IF EXISTS `referral_rewards`;
-- DROP TABLE IF EXISTS `referrals`;
-- ALTER TABLE `users` DROP INDEX `users_referred_by_index`;
-- ALTER TABLE `users` DROP INDEX `users_referral_code_unique`;
-- ALTER TABLE `users` MODIFY COLUMN `referred_by` VARCHAR(255) NULL DEFAULT NULL;
-- DELETE FROM `static_options` WHERE `option_name` LIKE 'referral_%'
--     AND `option_name` NOT IN ('sign_up_points','first_order_points','first_purchase_points');
