-- =====================================================================
--  Huduma Portal — Rafiki Rewards — Provider Stage 1 fix (PDF §05)
--  Version: 1.0
--  Date:    2026-09-13
--  Safe to run multiple times (idempotent).
-- =====================================================================
--
--  Adds the switch that lets admin suppress the automatic signup-time
--  Stage 1 reward for providers so it fires only after profile is
--  complete + service published + service approved.
-- =====================================================================

START TRANSACTION;

-- When 1, the provider Stage 1 reward does NOT fire on signup; it fires
-- only when the provider completes profile + publishes an approved service
-- (via ReferralService::onProviderProfileComplete()).
INSERT INTO `static_options` (`option_name`, `option_value`, `created_at`, `updated_at`)
SELECT 'referral_provider_stage1_requires_verified' AS a, '1' AS b, NOW() AS c, NOW() AS d
WHERE NOT EXISTS (SELECT 1 FROM `static_options` WHERE `option_name` = 'referral_provider_stage1_requires_verified');

COMMIT;

-- Rollback:
-- DELETE FROM static_options WHERE option_name = 'referral_provider_stage1_requires_verified';
