<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Public leaderboard for the Rafiki Rewards program.
 *
 * Renders three views in one page (tabs):
 *   - Top Referrers    global by referral count + Rafiki level badges
 *   - Top Cities       grouped by service_city (Dar vs Arusha vs Mwanza...)
 *   - University League grouped by users.university (opt-in field)
 *
 * All three respect a period filter (This Month / This Year / All Time).
 */
class PublicLeaderboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->query('period', 'month'); // month | year | all
        $tab    = $request->query('tab', 'top');      // top | cities | universities

        $since = match ($period) {
            'month' => now()->startOfMonth(),
            'year'  => now()->startOfYear(),
            default => null,
        };

        // Only surface approved OR still-live referrals; hide rejected/blocked/flagged
        $baseFilter = fn ($q) => $q->whereIn('referrals.status', ['qualifying', 'approved']);

        // ── TOP REFERRERS (individuals) ──
        $topRef = DB::table('referrals')
            ->select(
                'referrals.referrer_id',
                'users.name',
                'users.username',
                'users.referral_code',
                DB::raw('COUNT(*) as ref_count'),
                DB::raw('SUM(CASE WHEN referrals.status = "approved" THEN 1 ELSE 0 END) as approved_count')
            )
            ->leftJoin('users', 'users.id', '=', 'referrals.referrer_id')
            ->where($baseFilter)
            ->when($since, fn ($q) => $q->where('referrals.created_at', '>=', $since))
            ->groupBy('referrals.referrer_id', 'users.name', 'users.username', 'users.referral_code')
            ->orderByDesc('ref_count')
            ->limit(20)
            ->get();

        // ── TOP CITIES ── (only computed when the cities tab is showing;
        //   the tab is hidden in the current UI so this is skipped in prod.
        //   ServiceCity's label column is named `service_city`, not `name`.)
        $topCities = collect();
        if ($tab === 'cities') {
            $topCities = DB::table('referrals')
                ->select(
                    'service_cities.id',
                    'service_cities.service_city as city_name',
                    DB::raw('COUNT(*) as ref_count')
                )
                ->join('users', 'users.id', '=', 'referrals.referrer_id')
                ->leftJoin('service_cities', 'service_cities.id', '=', 'users.service_city')
                ->where($baseFilter)
                ->when($since, fn ($q) => $q->where('referrals.created_at', '>=', $since))
                ->whereNotNull('users.service_city')
                ->groupBy('service_cities.id', 'service_cities.service_city')
                ->orderByDesc('ref_count')
                ->limit(15)
                ->get();
        }

        // ── TOP UNIVERSITIES ── (only when tab is active AND column exists)
        $topUnis = collect();
        if ($tab === 'universities' && Schema::hasColumn('users', 'university')) {
            $topUnis = DB::table('referrals')
                ->select(
                    'users.university',
                    DB::raw('COUNT(*) as ref_count'),
                    DB::raw('COUNT(DISTINCT referrals.referrer_id) as referrer_count')
                )
                ->join('users', 'users.id', '=', 'referrals.referrer_id')
                ->where($baseFilter)
                ->when($since, fn ($q) => $q->where('referrals.created_at', '>=', $since))
                ->whereNotNull('users.university')
                ->where('users.university', '!=', '')
                ->groupBy('users.university')
                ->orderByDesc('ref_count')
                ->limit(15)
                ->get();
        }

        // Program-wide totals for the hero strip
        $stats = [
            'total_referrers' => (int) DB::table('referrals')->distinct('referrer_id')->count('referrer_id'),
            'total_referrals' => (int) DB::table('referrals')->count(),
            'top_score'       => (int) ($topRef->first()->ref_count ?? 0),
        ];

        return view('frontend.public-leaderboard', compact(
            'topRef', 'topCities', 'topUnis', 'stats', 'period', 'tab'
        ));
    }
}
