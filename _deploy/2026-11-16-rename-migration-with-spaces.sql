-- NOT NEEDED IF YOU RUN THE UPGRADE URL.
-- The system upgrade page now carries this as a step
-- ("Migrations: rename the entry whose filename had spaces"),
-- so this file is only here for running by hand instead.
--
-- The migration file 2022_08_10_083550_add_ system_type_to_ admin_commissions_table.php
-- had literal spaces in its name. The file has been renamed to
-- 2022_08_10_083550_add_system_type_to_admin_commissions_table.php, so the
-- ledger has to be renamed with it — otherwise Laravel sees the renamed file
-- as a migration that has never run and tries to run it again.
--
-- Harmless either way (the migration guards with Schema::hasColumn), but this
-- keeps `php artisan migrate:status` honest.
UPDATE `migrations`
   SET `migration` = '2022_08_10_083550_add_system_type_to_admin_commissions_table'
 WHERE `migration` = '2022_08_10_083550_add_ system_type_to_ admin_commissions_table';
