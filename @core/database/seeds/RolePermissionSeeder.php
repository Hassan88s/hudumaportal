<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Seeds the admin permission catalogue from config/admin_permissions.php and
 * guarantees a Super Admin role that holds every permission.
 *
 * Idempotent: it only creates what is missing, so it can safely be re-run on
 * dev or production to backfill without wiping or duplicating existing rows,
 * and it never touches other roles' assignments.
 *
 * Run with:  php artisan db:seed --class=Database\\Seeders\\RolePermissionSeeder
 */
class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        $guard = 'admin';

        // 1. Ensure every catalogued permission exists (guard: admin).
        $names = collect(config('admin_permissions.groups', []))
            ->flatten()
            ->unique()
            ->values();

        foreach ($names as $name) {
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => $guard]
            );
        }

        // 2. Ensure the Super Admin role exists and holds every permission.
        $superAdmin = Role::firstOrCreate(
            ['name' => 'Super Admin', 'guard_name' => $guard]
        );
        $superAdmin->givePermissionTo(Permission::where('guard_name', $guard)->get());

        // 3. Refresh the cached permissions so changes take effect immediately.
        Artisan::call('permission:cache-reset');

        $this->command?->info('Seeded '.$names->count().' permissions and ensured the Super Admin role.');
    }
}
