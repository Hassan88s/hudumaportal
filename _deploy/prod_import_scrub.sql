-- Scrub a dev database copy before it becomes production.
--
-- Run this ONCE, on the imported copy, in phpMyAdmin's SQL tab, after
-- importing the dev dump into the production database and BEFORE letting
-- anyone at the site.
--
-- It is safe to run twice: everything matches on a pattern and deletes only
-- what matches, so a second run finds nothing left to do.
--
-- Nothing here touches real accounts. It matches test addresses only:
--   @test.local, @example.com, usernames qa_* and test_signup_*
--
-- Each section ends with a SELECT so you can see what happened.

/* ------------------------------------------------------------------ 1. look */
-- Run this section FIRST, on its own, and read the output before going on.
-- If anything listed here is an account you recognise, stop and say so.

SELECT 'admins to remove' AS what, id, name, username, email FROM admins
 WHERE email LIKE '%@test.local';

SELECT 'users to remove' AS what, id, name, username, email FROM users
 WHERE email LIKE '%@test.local'
    OR email LIKE '%@example.com'
    OR username LIKE 'qa\_%'
    OR username LIKE 'test\_signup\_%';


/* --------------------------------------------------- 2. remove test admins */
-- A test admin account is the one item on this page that is a real security
-- problem: it is a working login to the production panel, with a password
-- that was chosen for a test.

DELETE FROM model_has_roles
 WHERE model_type = 'App\\Admin'
   AND model_id IN (SELECT id FROM admins WHERE email LIKE '%@test.local');

DELETE FROM admins WHERE email LIKE '%@test.local';


/* ---------------------------------------------------- 3. remove test users */
-- Their dependent rows go first, so nothing is left pointing at a user that
-- no longer exists.

CREATE TEMPORARY TABLE _scrub_users AS
SELECT id FROM users
 WHERE email LIKE '%@test.local'
    OR email LIKE '%@example.com'
    OR username LIKE 'qa\_%'
    OR username LIKE 'test\_signup\_%';

DELETE FROM wallet_histories  WHERE buyer_id IN (SELECT id FROM _scrub_users);
DELETE FROM wallets           WHERE buyer_id IN (SELECT id FROM _scrub_users);
DELETE FROM referral_rewards  WHERE user_id  IN (SELECT id FROM _scrub_users);

-- Ride rows, in case the feature was ever exercised on dev.
DELETE FROM rides  WHERE passenger_id IN (SELECT id FROM _scrub_users)
                      OR rider_id IN (SELECT id FROM riders
                                       WHERE user_id IN (SELECT id FROM _scrub_users));
DELETE FROM riders WHERE user_id IN (SELECT id FROM _scrub_users);

DELETE FROM users WHERE id IN (SELECT id FROM _scrub_users);

DROP TEMPORARY TABLE _scrub_users;


/* ------------------------------------------------- 4. settings that matter */
-- These four decide whether the site opens in a state somebody intended.

-- The admin one-time-code bypass is a local development escape hatch and must
-- never be on anywhere else.
UPDATE static_options SET option_value = '0' WHERE option_name = 'admin_otp_disabled';

-- Ride booking ships dormant. Turning it on is a separate decision, made after
-- the fares and commission on that settings page are real numbers rather than
-- the placeholders they ship as.
UPDATE static_options SET option_value = '0' WHERE option_name = 'ride_enabled';
UPDATE static_options SET option_value = '0' WHERE option_name = 'ride_show_rider_link';

-- Maintenance mode off, so the site is actually reachable once you are done.
UPDATE static_options SET option_value = '' WHERE option_name = 'site_maintenance_mode';


/* --------------------------------------------------------------- 5. check */
-- Read all four of these before opening the site.

SELECT 'settings' AS what, option_name, option_value
  FROM static_options
 WHERE option_name IN ('admin_otp_disabled', 'ride_enabled',
                       'ride_show_rider_link', 'site_maintenance_mode');

-- Mail goes out as whatever this says. Confirm it is the mailbox production
-- should be sending from, not one a developer pointed at themselves.
SELECT 'outgoing mail' AS what, option_name, option_value
  FROM static_options
 WHERE option_name IN ('site_smtp_mail_host', 'site_smtp_mail_username',
                       'site_global_email');

-- Who can sign in to the admin panel. Every row here should be a person you
-- can name.
SELECT 'admins remaining' AS what, id, name, username, email FROM admins ORDER BY id;

-- Anything still carrying a dev or local address in its content.
SELECT 'live chat rows mentioning localhost' AS what, COUNT(*) AS n
  FROM live_chat_messages WHERE message LIKE '%localhost%';
