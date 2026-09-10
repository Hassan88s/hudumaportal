-- =====================================================================
--  Huduma Portal — Rafiki Rewards — Business Track (Phase 1 extension)
--  Version: 1.0
--  Date:    2026-09-10
--  Safe to run multiple times (idempotent).
-- =====================================================================
--
--  Adds the 5 admin-configurable settings for the Business referral track
--  (max TZS 10,000 per referral, per the strategy PDF):
--
--    Stage 1: Business gets verified (Enterprise admin-approved) → TZS 1,000
--    Stage 2: First completed booking by the business             → TZS 4,000
--    Stage 3: Cumulative spend >= TZS 250,000 within 90 days      → TZS 5,000
-- =====================================================================

START TRANSACTION;

INSERT INTO `static_options` (`option_name`, `option_value`, `created_at`, `updated_at`)
SELECT 'referral_stage1_business_amount' AS a, '1000' AS b, NOW() AS c, NOW() AS d
WHERE NOT EXISTS (SELECT 1 FROM `static_options` WHERE `option_name` = 'referral_stage1_business_amount');

INSERT INTO `static_options` (`option_name`, `option_value`, `created_at`, `updated_at`)
SELECT 'referral_stage2_business_amount' AS a, '4000' AS b, NOW() AS c, NOW() AS d
WHERE NOT EXISTS (SELECT 1 FROM `static_options` WHERE `option_name` = 'referral_stage2_business_amount');

INSERT INTO `static_options` (`option_name`, `option_value`, `created_at`, `updated_at`)
SELECT 'referral_stage3_business_amount' AS a, '5000' AS b, NOW() AS c, NOW() AS d
WHERE NOT EXISTS (SELECT 1 FROM `static_options` WHERE `option_name` = 'referral_stage3_business_amount');

INSERT INTO `static_options` (`option_name`, `option_value`, `created_at`, `updated_at`)
SELECT 'referral_business_spend_threshold' AS a, '250000' AS b, NOW() AS c, NOW() AS d
WHERE NOT EXISTS (SELECT 1 FROM `static_options` WHERE `option_name` = 'referral_business_spend_threshold');

INSERT INTO `static_options` (`option_name`, `option_value`, `created_at`, `updated_at`)
SELECT 'referral_business_spend_days' AS a, '90' AS b, NOW() AS c, NOW() AS d
WHERE NOT EXISTS (SELECT 1 FROM `static_options` WHERE `option_name` = 'referral_business_spend_days');

COMMIT;

-- =====================================================================
-- Verification
-- =====================================================================
-- SELECT option_name, option_value FROM static_options WHERE option_name LIKE 'referral_%business%' OR option_name LIKE 'referral_stage_business%' ORDER BY option_name;

-- =====================================================================
-- ROLLBACK
-- =====================================================================
-- DELETE FROM static_options WHERE option_name IN (
--     'referral_stage1_business_amount','referral_stage2_business_amount','referral_stage3_business_amount',
--     'referral_business_spend_threshold','referral_business_spend_days'
-- );
