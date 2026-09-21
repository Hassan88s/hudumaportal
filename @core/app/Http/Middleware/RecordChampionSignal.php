<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

/**
 * Huduma Champions (PDF §29) — remembers which IP address and device each
 * logged-in user is seen on, at most once per user/IP/device per day.
 * Used only as a risk signal on the admin Top 20 review; never blocks anyone.
 */
class RecordChampionSignal
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        try {
            $user = Auth::guard('web')->user();
            if (!$user || !$this->tableReady()) return $response;

            // Long-lived random device id (reuse the Rafiki cookie if the browser has it)
            // Session fallback: requests that arrive before the cookie is saved reuse the same id
            $session = $request->hasSession() ? $request->session() : null;
            $fp = $request->cookie('rf_fp') ?: $request->cookie('hp_fp') ?: ($session ? $session->get('hp_fp') : null);
            if (!$fp) {
                $fp = bin2hex(random_bytes(16));
                Cookie::queue('hp_fp', $fp, 60 * 24 * 365);
            }
            if ($session && !$session->has('hp_fp')) $session->put('hp_fp', $fp);

            $ip  = $request->ip();
            $day = now()->toDateString();
            $key = 'champ_sig_' . $user->id . '_' . md5($ip . '|' . $fp . '|' . $day);
            if (!Cache::add($key, 1, now()->addDay())) return $response; // already stored today

            DB::table('champion_user_signals')->insertOrIgnore([
                'user_id'    => $user->id,
                'ip'         => $ip,
                'device_fp'  => substr((string) $fp, 0, 64),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
                'day'        => $day,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // never break a page because of a risk signal
        }

        return $response;
    }

    protected function tableReady(): bool
    {
        return Cache::remember('champ_signals_table', now()->addMinutes(10),
            fn () => \Schema::hasTable('champion_user_signals'));
    }
}
