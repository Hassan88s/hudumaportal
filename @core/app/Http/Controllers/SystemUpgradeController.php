<?php

namespace App\Http\Controllers;

use App\Services\SystemUpgradeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

/**
 * One URL that applies pending schema and permission changes, so deploying
 * does not mean pasting SQL into phpMyAdmin.
 *
 * Two ways in, and nothing else:
 *   - signed in as a Super Admin, or
 *   - the secret key in the URL matches SYSTEM_UPGRADE_KEY in .env
 *
 * Anything else gets a plain 404, so the endpoint does not announce that it
 * exists. Every attempt is logged with its IP.
 */
class SystemUpgradeController extends Controller
{
    public function run(Request $request, SystemUpgradeService $upgrader, ?string $key = null)
    {
        if (! $this->allowed($key)) {
            \Log::warning('[System upgrade] refused', [
                'ip'  => $request->ip(),
                'key' => $key ? 'provided but wrong' : 'absent',
            ]);
            abort(404);
        }

        $results = $upgrader->run();

        // Settings and permissions are cached, so a change nobody can see yet
        // is a change that has not really landed.
        $cleared = [];
        foreach (['cache:clear', 'config:clear', 'route:clear', 'view:clear'] as $cmd) {
            try { Artisan::call($cmd); $cleared[] = $cmd; } catch (\Throwable $e) {}
        }
        try {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            $cleared[] = 'permission cache';
        } catch (\Throwable $e) {}

        $failed = collect($results)->where('status', 'FAILED')->count();

        \Log::info('[System upgrade] ran', [
            'ip'      => $request->ip(),
            'by'      => Auth::guard('admin')->check() ? Auth::guard('admin')->user()->email : 'key',
            'applied' => collect($results)->where('status', 'applied')->pluck('key')->all(),
            'failed'  => $failed,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'ok'      => $failed === 0,
                'steps'   => $results,
                'cleared' => $cleared,
            ], $failed === 0 ? 200 : 500);
        }

        return response()
            ->view('system-upgrade', [
                'results' => $results,
                'cleared' => $cleared,
                'failed'  => $failed,
            ], $failed === 0 ? 200 : 500);
    }

    protected function allowed(?string $key): bool
    {
        if (Auth::guard('admin')->check() && Auth::guard('admin')->user()->hasRole('Super Admin')) {
            return true;
        }

        $expected = (string) config('app.system_upgrade_key', '');

        // No key configured means the URL route is simply closed. It must never
        // fall back to "allow", and an empty guess must never match an empty
        // setting.
        return $expected !== '' && is_string($key) && hash_equals($expected, $key);
    }
}
