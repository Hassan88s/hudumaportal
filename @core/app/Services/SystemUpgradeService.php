<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Applies pending schema and permission changes so a deploy does not need
 * anyone to paste SQL into phpMyAdmin.
 *
 * Deliberately NOT Laravel migrations: this database was set up from a dump
 * and its migrations table does not reflect reality, so `artisan migrate`
 * would try to replay the project's whole history against a live schema.
 * Instead every step here is an explicit, named, idempotent operation that
 * checks the current schema before touching it, so running this twice — or
 * twenty times — changes nothing the second time.
 *
 * Steps only ever ADD things. Nothing here drops a table, a column or a row.
 */
class SystemUpgradeService
{
    /** @return array<int,array{key:string,title:string,status:string,detail:string}> */
    public function run(): array
    {
        $results = [];

        foreach ($this->steps() as $key => $step) {
            try {
                $detail = $step['run']();
                $results[] = [
                    'key'    => $key,
                    'title'  => $step['title'],
                    'status' => $detail === null ? 'already applied' : 'applied',
                    'detail' => $detail ?? '',
                ];
            } catch (\Throwable $e) {
                $results[] = [
                    'key'    => $key,
                    'title'  => $step['title'],
                    'status' => 'FAILED',
                    'detail' => $e->getMessage(),
                ];
                \Log::error('[System upgrade] step "' . $key . '" failed: ' . $e->getMessage());
            }
        }

        return $results;
    }

    /**
     * Each step returns a description when it changed something, or null when
     * there was nothing to do.
     */
    protected function steps(): array
    {
        return [

            'spin_wheel_spins_table' => [
                'title' => 'Spin & Win: spin log table',
                'run'   => function () {
                    if (Schema::hasTable('spin_wheel_spins')) {
                        return null;
                    }
                    DB::statement("
                        CREATE TABLE `spin_wheel_spins` (
                          `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                          `user_id`       BIGINT UNSIGNED NOT NULL,
                          `try_number`    INT NOT NULL DEFAULT 1,
                          `segment_label` VARCHAR(191) NULL,
                          `result_type`   VARCHAR(50)  NULL,
                          `result_value`  DECIMAL(10,2) NOT NULL DEFAULT 0,
                          `is_win`        TINYINT(1)   NOT NULL DEFAULT 0,
                          `code`          VARCHAR(50)  NULL,
                          `created_at`    TIMESTAMP NULL DEFAULT NULL,
                          `updated_at`    TIMESTAMP NULL DEFAULT NULL,
                          PRIMARY KEY (`id`),
                          KEY `spin_wheel_spins_user_id_index` (`user_id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                    ");
                    return 'created spin_wheel_spins';
                },
            ],

            'spin_wheel_redeem_columns' => [
                'title' => 'Spin & Win: redemption tracking',
                'run'   => function () {
                    $done = [];

                    if (! Schema::hasColumn('spin_wheel_spins', 'used_at')) {
                        DB::statement("ALTER TABLE `spin_wheel_spins` ADD COLUMN `used_at` TIMESTAMP NULL DEFAULT NULL AFTER `code`");
                        $done[] = 'used_at';
                    }
                    if (! Schema::hasColumn('spin_wheel_spins', 'used_order_id')) {
                        DB::statement("ALTER TABLE `spin_wheel_spins` ADD COLUMN `used_order_id` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `used_at`");
                        $done[] = 'used_order_id';
                    }
                    if (! Schema::hasColumn('spin_wheel_spins', 'used_context')) {
                        DB::statement("ALTER TABLE `spin_wheel_spins` ADD COLUMN `used_context` VARCHAR(30) NOT NULL DEFAULT 'service_order' AFTER `used_order_id`");
                        $done[] = 'used_context';
                    }
                    if (! Schema::hasColumn('spin_wheel_spins', 'expires_at')) {
                        DB::statement("ALTER TABLE `spin_wheel_spins` ADD COLUMN `expires_at` TIMESTAMP NULL DEFAULT NULL AFTER `used_context`");
                        $done[] = 'expires_at';
                    }
                    if (! $this->hasIndex('spin_wheel_spins', 'spin_wheel_spins_code_unique')) {
                        DB::statement("ALTER TABLE `spin_wheel_spins` ADD UNIQUE KEY `spin_wheel_spins_code_unique` (`code`)");
                        $done[] = 'unique code index';
                    }
                    if (! $this->hasIndex('spin_wheel_spins', 'spin_wheel_spins_used_at_index')) {
                        DB::statement("ALTER TABLE `spin_wheel_spins` ADD KEY `spin_wheel_spins_used_at_index` (`used_at`)");
                        $done[] = 'used_at index';
                    }

                    return $done ? 'added ' . implode(', ', $done) : null;
                },
            ],

            'spin_wheel_bonus_table' => [
                'title' => 'Spin & Win: earned (referral) spins',
                'run'   => function () {
                    if (Schema::hasTable('spin_wheel_bonus_spins')) {
                        return null;
                    }
                    DB::statement("
                        CREATE TABLE `spin_wheel_bonus_spins` (
                          `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                          `user_id`        BIGINT UNSIGNED NOT NULL,
                          `reason`         VARCHAR(50) NOT NULL,
                          `source_user_id` BIGINT UNSIGNED NOT NULL DEFAULT 0,
                          `spins`          INT NOT NULL DEFAULT 1,
                          `created_at`     TIMESTAMP NULL DEFAULT NULL,
                          `updated_at`     TIMESTAMP NULL DEFAULT NULL,
                          PRIMARY KEY (`id`),
                          UNIQUE KEY `spin_bonus_unique` (`user_id`,`reason`,`source_user_id`),
                          KEY `spin_bonus_user_index` (`user_id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                    ");
                    return 'created spin_wheel_bonus_spins';
                },
            ],

            'spin_wheel_settings' => [
                'title' => 'Spin & Win: default settings',
                'run'   => function () {
                    return $this->ensureOptions([
                        'spin_wheel_enabled'                 => '0',
                        'spin_wheel_max_tries'               => '2',
                        'spin_wheel_force_first_lose'        => '1',
                        'spin_wheel_code_expiry_days'        => '7',
                        'spin_wheel_referral_enabled'        => '1',
                        'spin_wheel_referral_spins_referrer' => '1',
                        'spin_wheel_referral_spins_invitee'  => '1',
                    ]);
                },
            ],

            'order_money_defaults' => [
                'title' => 'Orders: default money columns to 0',
                'run'   => function () {
                    // These are NOT NULL with no default, so any one of them
                    // arriving null makes the whole order write fail with a
                    // 500 at checkout. package_fee is the usual culprit: for
                    // an online service it comes straight from a form field.
                    $columns = [
                        'package_fee'       => 'DOUBLE NOT NULL DEFAULT 0',
                        'extra_service'     => 'DOUBLE NOT NULL DEFAULT 0',
                        'sub_total'         => 'DOUBLE NOT NULL DEFAULT 0',
                        'tax'               => 'DOUBLE NOT NULL DEFAULT 0',
                        'total'             => 'DOUBLE NOT NULL DEFAULT 0',
                        'commission_amount' => 'DOUBLE NULL DEFAULT 0',
                    ];

                    $current = collect(DB::select('SHOW COLUMNS FROM `orders`'))->keyBy('Field');
                    $done = [];

                    foreach ($columns as $name => $definition) {
                        $col = $current->get($name);
                        if (! $col || $col->Default !== null) {
                            continue;   // missing, or already has a default
                        }
                        DB::statement("ALTER TABLE `orders` MODIFY `{$name}` {$definition}");
                        $done[] = $name;
                    }

                    return $done ? 'defaulted ' . implode(', ', $done) . ' to 0' : null;
                },
            ],

            'admin_permissions' => [
                'title' => 'Admin permissions for the newer modules',
                'run'   => function () {
                    return $this->ensurePermissions([
                        'spin-wheel-manage',
                        'champions-manage',
                        'referral-manage',
                        'dashboard-finance',
                        'dashboard-stats',
                        'registration-settings',
                        'tax-manage',
                        'database-maintenance',
                        'notice-manage',
                    ]);
                },
            ],

            'admin_permissions_phase2' => [
                'title' => 'Admin permissions for the rest of the panel',
                'run'   => function () {
                    /*
                     * Most of these are names the panel already used — in a
                     * sidebar @can or a controller's permission: middleware —
                     * that had never been created as permissions. The effect
                     * was the worst of both: the menu item was invisible to
                     * every role but Super Admin, nobody could grant it on
                     * the role screen, and the URL itself stayed wide open.
                     */
                    $created = $this->ensurePermissions([
                        // Orders
                        'order-status', 'cancel-order-list', 'order-request-complete',
                        'extra-orders', 'order-success-setting', 'order-create-setting',
                        'seller-buyer-report',
                        // Services
                        'admin-service-list', 'admin-service-add', 'coupon-list',
                        'service-create-setting', 'service-review-manage', 'service-promote-manage',
                        // Blog
                        'blog-single-settings', 'blog-trashed-edit',
                        // Pages
                        'page-settings-404-page-manage', 'page-settings-maintain-page-manage',
                        'page-settings-contact-page-manage', 'page-settings-blog-page-manage',
                        // Subscriptions
                        'subscription-edit', 'subscription-delete', 'subscription-settings',
                        // Everything else
                        'brand-status', 'appearance-home-variant',
                        'advertisement-list', 'advertisement-create',
                        'advertisement-edit', 'advertisement-delete',
                        'enterprise-manage', 'public-chat', 'all-seller',
                        'general-settings-payment-gateway-settings', 'general-settings-pusher',
                        'general-settings-otp-settings', 'general-settings-adspace-settings',
                        'general-settings-page-settings', 'gdpr-settings', 'company-settings',

                        /*
                         * The same problem one level down: these are checked
                         * by @can on buttons inside pages rather than in the
                         * sidebar, so the Edit, Delete, Status and Send Email
                         * controls on those screens were invisible to every
                         * role but Super Admin.
                         */
                        'admin-service-edit', 'admin-service-status', 'admin-service-delete',
                        'admin-service-attributes-add', 'admin-service-attributes-edit',
                        'admin-service-attributes-show', 'admin-coupon-edit',
                        'child-category-list', 'child-category-create',
                        'child-category-status', 'child-category-delete',
                        'complete-status', 'report-delete', 'coupon-delete',
                        'custom-font-status', 'custom-font-delete',
                        'email-verify-code', 'newsletter-send-mail',
                        'subscription-status', 'send-email', 'notification-delete',
                    ]);

                    /*
                     * These permissions split up areas that used to need no
                     * permission at all, so a role that already works in an
                     * area keeps working in it. Done once only, recorded in
                     * static_options: if an admin later unticks one of these
                     * boxes deliberately, re-running the upgrade must not put
                     * it back.
                     */
                    $inherited = $this->inheritOnce('hp_perm_phase2_inherited', [
                            'order-list' => [
                                'order-status', 'cancel-order-list', 'order-request-complete',
                                'extra-orders', 'order-success-setting', 'seller-buyer-report',
                            ],
                            'service-list' => [
                                'admin-service-list', 'admin-service-add', 'coupon-list',
                                'service-create-setting', 'order-create-setting',
                                'service-review-manage', 'service-promote-manage',
                            ],
                            'blog-list'         => [
                                'blog-single-settings', 'page-settings-blog-page-manage',
                            ],
                            'blog-trashed-list' => ['blog-trashed-edit'],
                            'brand-list'        => ['brand-status'],
                            'pages-list'        => [
                                'page-settings-404-page-manage',
                                'page-settings-maintain-page-manage',
                                'page-settings-contact-page-manage',
                            ],
                            'subscription-list'      => ['subscription-edit', 'subscription-delete', 'subscription-settings'],
                            'appearance-menu-list'   => ['appearance-home-variant'],
                            'user-list' => [
                                'enterprise-manage', 'public-chat', 'all-seller',
                            ],
                            'general-settings-basic-settings' => [
                                'general-settings-payment-gateway-settings', 'general-settings-pusher',
                                'general-settings-otp-settings', 'general-settings-adspace-settings',
                                'general-settings-page-settings', 'gdpr-settings', 'company-settings',
                            ],
                    ]);

                    /*
                     * A second wave, under its own flag. The first flag is
                     * already set everywhere this has run, so adding names to
                     * that map would have been a no-op on every live site.
                     */
                    $buttons = $this->inheritOnce('hp_perm_buttons_inherited', [
                        'order-list' => ['complete-status', 'report-delete'],
                        'service-list' => [
                            'admin-service-edit', 'admin-service-status', 'admin-service-delete',
                            'admin-service-attributes-add', 'admin-service-attributes-edit',
                            'admin-service-attributes-show', 'admin-coupon-edit',
                        ],
                        'category-list' => [
                            'child-category-list', 'child-category-create',
                            'child-category-status', 'child-category-delete',
                        ],
                        'user-list'                => ['email-verify-code', 'newsletter-send-mail'],
                        'subscription-coupon-list' => ['coupon-delete'],
                        'seller-subscription-list' => ['subscription-status', 'send-email'],
                        'general-settings-typography-settings' => ['custom-font-status', 'custom-font-delete'],
                    ]);

                    return trim(implode('; ', array_filter([$created, $inherited, $buttons]))) ?: null;
                },
            ],

            'referral_backfill_missed_stages' => [
                'title' => 'Referrals: settle up stages that were missed',
                'run'   => function () {
                    /*
                     * Several referral stages could never pay. Provider stage 1
                     * was unreachable, provider stage 3 had nothing calling it,
                     * bookings made as job hires were invisible to the client
                     * track, and the stage hooks insisted on an exact order
                     * count, so a reward was lost for good once anything else
                     * had landed first. People earned those rewards under the
                     * published terms and were never given them.
                     *
                     * This replays the real hooks over every referral rather
                     * than working the amounts out again here. They check the
                     * track, the counts and the dates themselves, and they are
                     * keyed so nothing already paid can be paid twice — which
                     * is the whole reason for going through them.
                     *
                     * Notifications are muted: this is months-old activity, and
                     * nobody should get a burst of congratulation texts for it.
                     *
                     * Runs once and is remembered.
                     */
                    if (DB::table('static_options')->where('option_name', 'hp_referral_backfill_done')->exists()) {
                        return null;
                    }

                    $rs = app(\App\Services\ReferralService::class);
                    $rs->muteNotifications = true;

                    $before = DB::table('referral_rewards')->count();
                    $seen   = 0;
                    $failed = 0;

                    foreach (DB::table('referrals')->orderBy('id')->cursor() as $row) {
                        $user = \App\User::find($row->referred_user_id);
                        if (! $user) { continue; }
                        $seen++;

                        try {
                            // Client track.
                            $rs->onBuyerFirstOrder($user);
                            $rs->onBuyerSecondOrderWithin60Days($user);

                            // Provider track. Each one self-checks user_type,
                            // so calling them for a buyer costs nothing.
                            $rs->onSellerFirstService($user);
                            $rs->onProviderFirstPaidOrder($user);
                            $rs->onProviderStage3($user, 'second_order');
                        } catch (\Throwable $e) {
                            $failed++;
                            \Log::warning('[Rafiki Rewards] backfill skipped referral ' . $row->id . ': ' . $e->getMessage());
                        }
                    }

                    $rs->muteNotifications = false;

                    $created = DB::table('referral_rewards')->count() - $before;

                    DB::table('static_options')->insert([
                        'option_name'  => 'hp_referral_backfill_done',
                        'option_value' => '1',
                    ]);

                    return 'checked ' . $seen . ' referral(s), created ' . $created . ' reward(s)'
                        . ($failed ? ', ' . $failed . ' skipped on error' : '');
                },
            ],

            'referral_signup_reward_off' => [
                'title' => 'Referrals: stop paying for a sign-up on its own',
                'run'   => function () {
                    /*
                     * The published programme copy says plainly that "signing
                     * up alone is not enough" and lists no reward for it, but
                     * sign_up_points was paying the referrer 100 the moment
                     * someone registered. Setting it to 0 brings the behaviour
                     * back in line with what was advertised.
                     *
                     * Done once and remembered, so an admin who decides later
                     * that they do want a sign-up reward can set one in the
                     * referral settings without the next upgrade undoing them.
                     */
                    if (DB::table('static_options')->where('option_name', 'hp_referral_signup_reward_off')->exists()) {
                        return null;
                    }

                    $current = DB::table('static_options')
                        ->where('option_name', 'sign_up_points')
                        ->value('option_value');

                    DB::table('static_options')->updateOrInsert(
                        ['option_name' => 'sign_up_points'],
                        ['option_value' => '0']
                    );

                    DB::table('static_options')->insert([
                        'option_name'  => 'hp_referral_signup_reward_off',
                        'option_value' => '1',
                    ]);

                    return 'sign_up_points ' . var_export($current, true) . ' -> 0';
                },
            ],

            'drop_blog_category_permissions' => [
                'title' => 'Admin permissions: drop the blog category ones',
                'run'   => function () {
                    /*
                     * Blog categories were removed: there is no model class,
                     * no view and no blog_categories table behind them, and no
                     * menu ever linked to the screen. The four permissions are
                     * gone from the catalogue, so they no longer appear on the
                     * role screen, but the rows and their role assignments are
                     * still here and would sit in the database for ever.
                     *
                     * Deleting the permission takes its role_has_permissions
                     * rows with it, which is what we want — nothing reads these
                     * names any more.
                     */
                    $names = [
                        'blog-category-list', 'blog-category-create',
                        'blog-category-edit', 'blog-category-delete',
                    ];

                    $found = DB::table('permissions')
                        ->whereIn('name', $names)
                        ->where('guard_name', 'admin')
                        ->pluck('id');

                    if ($found->isEmpty()) {
                        return null;
                    }

                    DB::table('role_has_permissions')->whereIn('permission_id', $found)->delete();
                    DB::table('model_has_permissions')->whereIn('permission_id', $found)->delete();
                    DB::table('permissions')->whereIn('id', $found)->delete();

                    app()->forgetInstance(\Spatie\Permission\PermissionRegistrar::class);

                    return 'removed ' . $found->count() . ' blog category permission(s)';
                },
            ],

            'migration_filename_with_spaces' => [
                'title' => 'Migrations: rename the entry whose filename had spaces',
                'run'   => function () {
                    /*
                     * 2022_08_10_083550_add_ system_type_to_ admin_commissions_table.php
                     * was committed with literal spaces in its name. The file is
                     * renamed now, so the ledger has to follow it: Laravel matches
                     * a migration by the stored name, and left alone it would read
                     * the renamed file as one that has never run.
                     *
                     * Re-running it would do no harm — the migration guards itself
                     * with Schema::hasColumn — but migrate:status would be lying,
                     * and the table would collect a duplicate row.
                     */
                    $old = '2022_08_10_083550_add_ system_type_to_ admin_commissions_table';
                    $new = '2022_08_10_083550_add_system_type_to_admin_commissions_table';

                    if (! DB::table('migrations')->where('migration', $old)->exists()) {
                        return null;
                    }

                    // If a row under the new name somehow exists too, drop the old
                    // one rather than create a duplicate key problem.
                    if (DB::table('migrations')->where('migration', $new)->exists()) {
                        DB::table('migrations')->where('migration', $old)->delete();
                        return 'removed the duplicate old ledger row';
                    }

                    DB::table('migrations')->where('migration', $old)->update(['migration' => $new]);

                    return 'renamed the migrations row to ' . $new;
                },
            ],

            'ride_riders_table' => [
                'title' => 'Rides: rider accounts and their documents',
                'run'   => function () {
                    if (Schema::hasTable('riders')) {
                        return null;
                    }
                    DB::statement("
                        CREATE TABLE `riders` (
                          `id`                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                          `user_id`            BIGINT UNSIGNED NOT NULL,
                          `status`             TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0 pending, 1 approved, 2 rejected, 3 suspended',
                          `full_name`          VARCHAR(191) NULL,
                          `phone`              VARCHAR(50)  NULL,
                          `national_id`        VARCHAR(100) NULL,
                          `national_id_photo`  VARCHAR(255) NULL,
                          `licence_number`     VARCHAR(100) NULL,
                          `licence_expiry`     DATE NULL,
                          `licence_photo`      VARCHAR(255) NULL,
                          `bike_plate`         VARCHAR(50)  NULL,
                          `bike_model`         VARCHAR(100) NULL,
                          `bike_photo`         VARCHAR(255) NULL,
                          `bike_reg_photo`     VARCHAR(255) NULL,
                          `insurance_photo`    VARCHAR(255) NULL,
                          `rejection_reason`   TEXT NULL,
                          `submitted_at`       TIMESTAMP NULL DEFAULT NULL,
                          `approved_at`        TIMESTAMP NULL DEFAULT NULL,
                          `approved_by`        BIGINT UNSIGNED NULL DEFAULT NULL,
                          `is_online`          TINYINT(1) NOT NULL DEFAULT 0,
                          `last_lat`           DECIMAL(10,7) NULL DEFAULT NULL,
                          `last_lng`           DECIMAL(10,7) NULL DEFAULT NULL,
                          `last_location_at`   TIMESTAMP NULL DEFAULT NULL,
                          `total_trips`        INT NOT NULL DEFAULT 0,
                          `rating_sum`         INT NOT NULL DEFAULT 0,
                          `rating_count`       INT NOT NULL DEFAULT 0,
                          `created_at`         TIMESTAMP NULL DEFAULT NULL,
                          `updated_at`         TIMESTAMP NULL DEFAULT NULL,
                          PRIMARY KEY (`id`),
                          UNIQUE KEY `riders_user_id_unique` (`user_id`),
                          KEY `riders_status_index` (`status`),
                          KEY `riders_online_index` (`is_online`, `status`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                    ");
                    return 'created riders';
                },
            ],

            'ride_settings_defaults' => [
                'title' => 'Rides: fare and dispatch settings',
                'run'   => function () {
                    /*
                     * Fares live in static_options like the rest of the site's
                     * settings, so get_static_option() and the existing settings
                     * screens work unchanged. Each default is written only when
                     * the option is absent, so a fare an admin edits is never
                     * reset by a later upgrade.
                     */
                    $defaults = [
                        'ride_enabled'            => '0',
                        'ride_base_fare'          => '1000',
                        'ride_price_per_km'       => '400',
                        'ride_price_per_minute'   => '50',
                        'ride_minimum_fare'       => '1500',
                        'ride_commission_percent' => '15',
                        'ride_search_radius_km'   => '5',
                        'ride_request_timeout'    => '30',
                        'ride_cancellation_fee'   => '0',
                    ];

                    $added = [];
                    foreach ($defaults as $name => $value) {
                        if (DB::table('static_options')->where('option_name', $name)->exists()) {
                            continue;
                        }
                        DB::table('static_options')->insert([
                            'option_name'  => $name,
                            'option_value' => $value,
                            'created_at'   => now(),
                            'updated_at'   => now(),
                        ]);
                        $added[] = $name;
                    }

                    return $added ? 'added ' . count($added) . ' ride setting(s)' : null;
                },
            ],

            'ride_admin_permissions' => [
                'title' => 'Rides: admin permissions',
                'run'   => function () {
                    $names = ['ride-rider-list', 'ride-rider-approve', 'ride-settings'];

                    $created = [];
                    foreach ($names as $name) {
                        $exists = DB::table('permissions')
                            ->where('name', $name)->where('guard_name', 'admin')->exists();
                        if ($exists) {
                            continue;
                        }
                        DB::table('permissions')->insert([
                            'name'       => $name,
                            'guard_name' => 'admin',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $created[] = $name;
                    }

                    // Super Admin must be able to reach anything new at once.
                    $superId = DB::table('roles')
                        ->where('name', 'Super Admin')->where('guard_name', 'admin')->value('id');

                    $granted = 0;
                    if ($superId) {
                        $ids = DB::table('permissions')
                            ->whereIn('name', $names)->where('guard_name', 'admin')->pluck('id');
                        foreach ($ids as $pid) {
                            $has = DB::table('role_has_permissions')
                                ->where('role_id', $superId)->where('permission_id', $pid)->exists();
                            if (! $has) {
                                DB::table('role_has_permissions')->insert([
                                    'role_id' => $superId, 'permission_id' => $pid,
                                ]);
                                $granted++;
                            }
                        }
                    }

                    app()->forgetInstance(\Spatie\Permission\PermissionRegistrar::class);

                    if (! $created && ! $granted) {
                        return null;
                    }

                    return trim(($created ? 'created ' . implode(', ', $created) : '')
                        . ($granted ? ' granted ' . $granted . ' to Super Admin' : ''));
                },
            ],

            'ride_rides_table' => [
                'title' => 'Rides: trips',
                'run'   => function () {
                    if (Schema::hasTable('rides')) {
                        return null;
                    }
                    DB::statement("
                        CREATE TABLE `rides` (
                          `id`                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                          `passenger_id`       BIGINT UNSIGNED NOT NULL,
                          `rider_id`           BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'riders.id, null while nobody has accepted',
                          `status`             TINYINT(2) NOT NULL DEFAULT 0 COMMENT '0 searching, 1 accepted, 2 rider arrived, 3 on the way, 4 completed, 5 passenger cancelled, 6 rider cancelled, 7 nobody accepted',
                          `pickup_address`     VARCHAR(255) NULL,
                          `pickup_lat`         DECIMAL(10,7) NOT NULL,
                          `pickup_lng`         DECIMAL(10,7) NOT NULL,
                          `dropoff_address`    VARCHAR(255) NULL,
                          `dropoff_lat`        DECIMAL(10,7) NOT NULL,
                          `dropoff_lng`        DECIMAL(10,7) NOT NULL,
                          `distance_km`        DECIMAL(8,2) NOT NULL DEFAULT 0,
                          `duration_min`       INT NOT NULL DEFAULT 0,
                          `fare`               DECIMAL(12,2) NOT NULL DEFAULT 0 COMMENT 'quoted at booking and charged unchanged',
                          `commission`         DECIMAL(12,2) NOT NULL DEFAULT 0,
                          `rider_earning`      DECIMAL(12,2) NOT NULL DEFAULT 0,
                          `cancellation_fee`   DECIMAL(12,2) NOT NULL DEFAULT 0,
                          `refunded_amount`    DECIMAL(12,2) NOT NULL DEFAULT 0,
                          `payment_state`      TINYINT(2) NOT NULL DEFAULT 0 COMMENT '0 nothing taken, 1 fare held, 2 settled, 3 refunded',
                          `cancel_reason`      VARCHAR(255) NULL,
                          `rating`             TINYINT(1) NULL DEFAULT NULL,
                          `rating_comment`     VARCHAR(500) NULL,
                          `requested_at`       TIMESTAMP NULL DEFAULT NULL,
                          `accepted_at`        TIMESTAMP NULL DEFAULT NULL,
                          `arrived_at`         TIMESTAMP NULL DEFAULT NULL,
                          `started_at`         TIMESTAMP NULL DEFAULT NULL,
                          `completed_at`       TIMESTAMP NULL DEFAULT NULL,
                          `cancelled_at`       TIMESTAMP NULL DEFAULT NULL,
                          `created_at`         TIMESTAMP NULL DEFAULT NULL,
                          `updated_at`         TIMESTAMP NULL DEFAULT NULL,
                          PRIMARY KEY (`id`),
                          KEY `rides_passenger_index` (`passenger_id`, `status`),
                          KEY `rides_rider_index` (`rider_id`, `status`),
                          KEY `rides_open_index` (`status`, `requested_at`),
                          KEY `rides_completed_index` (`completed_at`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                    ");
                    return 'created rides';
                },
            ],

            'ride_trip_settings' => [
                'title' => 'Rides: distance and speed assumptions',
                'run'   => function () {
                    /*
                     * A straight line between two points is shorter than the
                     * road, and the fare has a per-minute part, so both the
                     * detour and the expected speed have to be numbers an
                     * admin can tune rather than constants buried in code.
                     */
                    return $this->ensureOptions([
                        'ride_road_factor'   => '1.35',
                        'ride_avg_speed_kmh' => '20',
                        'ride_fare_rounding' => '100',
                    ]);
                },
            ],

            'ride_request_timeout_sane' => [
                'title' => 'Rides: give a request long enough to be seen',
                'run'   => function () {
                    /*
                     * The first default was 30 seconds, which was shorter than
                     * the interval at which a rider's screen asks for work —
                     * a request could expire before anybody was shown it. Only
                     * the untouched original value is corrected; a timeout an
                     * admin has chosen is left exactly as they set it.
                     */
                    $current = DB::table('static_options')
                        ->where('option_name', 'ride_request_timeout')->value('option_value');

                    if ((string) $current !== '30') {
                        return null;
                    }

                    DB::table('static_options')
                        ->where('option_name', 'ride_request_timeout')
                        ->update(['option_value' => '180', 'updated_at' => now()]);

                    \Illuminate\Support\Facades\Cache::forget('ride_request_timeout');

                    return 'raised request timeout to 180s';
                },
            ],

            'ride_passenger_rating_columns' => [
                'title' => 'Rides: the rating a rider gives the passenger',
                'run'   => function () {
                    /*
                     * `rating` on a trip is the rating the passenger gave the
                     * rider, which came first. These two are the other
                     * direction, named for who is being judged rather than
                     * who is doing the judging.
                     */
                    if (Schema::hasColumn('rides', 'passenger_rating')) {
                        return null;
                    }

                    DB::statement("
                        ALTER TABLE `rides`
                          ADD COLUMN `passenger_rating` TINYINT(1) NULL DEFAULT NULL AFTER `rating_comment`,
                          ADD COLUMN `passenger_rating_comment` VARCHAR(500) NULL DEFAULT NULL AFTER `passenger_rating`
                    ");

                    if (! $this->hasIndex('rides', 'rides_passenger_rating_index')) {
                        DB::statement("
                            ALTER TABLE `rides`
                              ADD INDEX `rides_passenger_rating_index` (`passenger_id`, `passenger_rating`)
                        ");
                    }

                    return 'added passenger_rating';
                },
            ],

            'ride_rating_settings' => [
                'title' => 'Rides: when a rating is poor enough to flag',
                'run'   => function () {
                    return $this->ensureOptions([
                        'ride_low_rating_threshold' => '3.5',
                    ]);
                },
            ],

            'ride_trip_permissions' => [
                'title' => 'Rides: trip permissions',
                'run'   => function () {
                    /*
                     * Reading the trip list and ending somebody's trip are
                     * very different powers, so they are separate boxes on the
                     * role screen rather than one.
                     */
                    return $this->ensurePermissions(['ride-trip-list', 'ride-trip-resolve']);
                },
            ],

            'ride_rider_link_setting' => [
                'title' => 'Rides: whether to invite sellers to ride',
                'run'   => function () {
                    /*
                     * Off to begin with. Riders are approved one at a time, so
                     * the invitation goes out when somebody decides it should,
                     * not the moment the feature ships.
                     */
                    return $this->ensureOptions([
                        'ride_show_rider_link' => '0',
                    ]);
                },
            ],

            'ride_dashboard_permission' => [
                'title' => 'Rides: the overview screen',
                'run'   => function () {
                    $added = $this->ensurePermissions(['ride-dashboard']);

                    /*
                     * Anybody already trusted with the trip list is trusted
                     * with the summary of it, so they keep a way in without an
                     * admin having to go round the roles again. Applied once:
                     * unticking the box later must stay unticked.
                     */
                    $inherited = $this->inheritOnce('ride_dashboard_inherited', [
                        'ride-trip-list' => ['ride-dashboard'],
                    ]);

                    return trim(($added ?: '') . ' ' . ($inherited ?: '')) ?: null;
                },
            ],

        ];
    }

    /**
     * Apply an inheritance map once, remembered under $flag.
     *
     * Each wave needs its own flag: once a flag is set, its map never runs
     * again, which is the point — an admin who later unticks one of these
     * boxes must not have it put back by the next upgrade.
     */
    protected function inheritOnce(string $flag, array $map): ?string
    {
        if (DB::table('static_options')->where('option_name', $flag)->exists()) {
            return null;
        }

        $result = $this->inheritPermissions($map);

        DB::table('static_options')->insert([
            'option_name'  => $flag,
            'option_value' => '1',
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return $result;
    }

    /** Create any missing static_options without touching existing values. */
    protected function ensureOptions(array $defaults): ?string
    {
        $added = [];

        foreach ($defaults as $name => $value) {
            $exists = DB::table('static_options')->where('option_name', $name)->exists();
            if (! $exists) {
                DB::table('static_options')->insert([
                    'option_name'  => $name,
                    'option_value' => $value,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
                $added[] = $name;
            }
            \Illuminate\Support\Facades\Cache::forget($name);
        }

        return $added ? 'added ' . implode(', ', $added) : null;
    }

    /**
     * Create any missing admin permissions and give them to Super Admin only.
     * Other roles must be granted them deliberately on the role screen — this
     * never widens an existing role's access.
     */
    protected function ensurePermissions(array $names): ?string
    {
        $added = [];

        $superRoleId = DB::table('roles')
            ->where('name', 'Super Admin')->where('guard_name', 'admin')->value('id');

        foreach ($names as $name) {
            $id = DB::table('permissions')
                ->where('name', $name)->where('guard_name', 'admin')->value('id');

            if (! $id) {
                $id = DB::table('permissions')->insertGetId([
                    'name'       => $name,
                    'guard_name' => 'admin',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $added[] = $name;
            }

            if ($superRoleId && $id) {
                DB::table('role_has_permissions')->insertOrIgnore([
                    'permission_id' => $id,
                    'role_id'       => $superRoleId,
                ]);
            }
        }

        app()->forgetInstance(\Spatie\Permission\PermissionRegistrar::class);

        return $added ? 'added ' . implode(', ', $added) : null;
    }

    /**
     * Give each role that already holds $existing the permissions listed
     * against it. Used once, when an area that needed no permission is split
     * into named ones, so nobody loses access they were already using.
     * Roles that never had $existing are left alone — that is the point.
     */
    protected function inheritPermissions(array $map): ?string
    {
        $granted = 0;
        $roles   = [];

        foreach ($map as $existing => $newOnes) {
            $existingId = DB::table('permissions')
                ->where('name', $existing)->where('guard_name', 'admin')->value('id');
            if (! $existingId) {
                continue;
            }

            $roleIds = DB::table('role_has_permissions')
                ->where('permission_id', $existingId)->pluck('role_id')->all();
            if (! $roleIds) {
                continue;
            }

            $newIds = DB::table('permissions')
                ->whereIn('name', $newOnes)->where('guard_name', 'admin')->pluck('id')->all();

            foreach ($roleIds as $roleId) {
                foreach ($newIds as $newId) {
                    $granted += DB::table('role_has_permissions')->insertOrIgnore([
                        'permission_id' => $newId,
                        'role_id'       => $roleId,
                    ]);
                }
                $roles[$roleId] = true;
            }
        }

        return $granted
            ? 'carried ' . $granted . ' permission(s) over to ' . count($roles) . ' existing role(s)'
            : null;
    }

    protected function hasIndex(string $table, string $index): bool
    {
        return ! empty(DB::select(
            'SHOW INDEX FROM `' . str_replace('`', '', $table) . '` WHERE Key_name = ?',
            [$index]
        ));
    }
}
