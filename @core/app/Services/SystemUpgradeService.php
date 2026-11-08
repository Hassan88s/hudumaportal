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
                    ]);
                },
            ],

        ];
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

        return $added ? 'added ' . implode(', ', $added) : null;
    }

    protected function hasIndex(string $table, string $index): bool
    {
        return ! empty(DB::select(
            'SHOW INDEX FROM `' . str_replace('`', '', $table) . '` WHERE Key_name = ?',
            [$index]
        ));
    }
}
