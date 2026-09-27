<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Run database migrations from the admin panel, so schema changes do not need
 * SSH or phpMyAdmin.
 *
 * Locked down three ways:
 *   1. admin session required (the admin route group)
 *   2. Super Admin role required
 *   3. a key must match DB_UPDATE_KEY in .env (when that value is set)
 * Every run is written to the Laravel log with the admin's id.
 */
class DatabaseUpdateController extends Controller
{
    public function index()
    {
        $this->guard();

        return view('backend.database.update', [
            'pending'    => $this->pending(),
            'keyNeeded'  => (string) env('DB_UPDATE_KEY', '') !== '',
            'lastOutput' => session('db_update_output'),
        ]);
    }

    public function run(Request $request)
    {
        $this->guard();

        $key = (string) env('DB_UPDATE_KEY', '');
        if ($key !== '' && !hash_equals($key, (string) $request->input('key'))) {
            return back()->with('warning', __('Wrong update key.'));
        }

        $pending = $this->pending();
        if (empty($pending)) {
            return back()->with('success', __('Database is already up to date.'));
        }

        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = trim(Artisan::output());
            Log::info('[DB update] migrations run from admin panel', [
                'admin_id' => Auth::guard('admin')->id(),
                'ip'       => $request->ip(),
                'ran'      => $pending,
            ]);
            return back()->with('success', __('Database updated.'))->with('db_update_output', $output);
        } catch (\Throwable $e) {
            Log::error('[DB update] failed: ' . $e->getMessage(), ['admin_id' => Auth::guard('admin')->id()]);
            return back()->with('warning', __('Update failed: ') . $e->getMessage());
        }
    }

    /* ------------------------------------------------------------------ */

    /** Migration files that have not run on this server yet. */
    protected function pending(): array
    {
        if (!Schema::hasTable('migrations')) return ['(migrations table missing — first run will create it)'];

        $done = DB::table('migrations')->pluck('migration')->all();
        $files = collect(glob(database_path('migrations/*.php')))
            ->map(fn ($p) => pathinfo($p, PATHINFO_FILENAME))->all();

        return array_values(array_diff($files, $done));
    }

    protected function guard(): void
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin, 403);
        if (method_exists($admin, 'hasRole')) abort_unless($admin->hasRole('Super Admin'), 403);
    }
}
