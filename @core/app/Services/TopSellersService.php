<?php

namespace App\Services;

use App\Order;
use App\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Top 100 Sellers ranking service.
 *
 * A completed order (orders.status = 2) is the ranking signal — real,
 * delivered work is what earns a badge, not signups or postings.
 *
 * Results are cached for 1 hour to keep landing pages fast; a manual
 * `flush()` on the singleton clears it if admin ever needs an override.
 */
class TopSellersService
{
    protected const CACHE_TTL_MIN = 60;
    protected const CACHE_KEY     = 'top_sellers_%s_%d';

    /**
     * Get the top N sellers by completed-order count. Period may be
     * "month" | "year" | "all" (default all).
     *
     * @return \Illuminate\Support\Collection each item: {seller_id, name,
     *   username, image, city_id, completed_orders, rank}
     */
    public function getTopSellers(int $limit = 100, string $period = 'all')
    {
        $key = sprintf(self::CACHE_KEY, $period, $limit);

        return Cache::remember($key, now()->addMinutes(self::CACHE_TTL_MIN),
            function () use ($limit, $period) {
                $since = match ($period) {
                    'month' => now()->startOfMonth(),
                    'year'  => now()->startOfYear(),
                    default => null,
                };

                $q = DB::table('orders')
                    ->select(
                        'orders.seller_id',
                        DB::raw('COUNT(*) as completed_orders'),
                        'users.name', 'users.username', 'users.image',
                        'users.service_city', 'users.service_area'
                    )
                    ->join('users', 'users.id', '=', 'orders.seller_id')
                    ->where('orders.status', 2)                  // completed
                    ->where('users.user_status', 1)              // active seller only
                    ->when($since, fn ($qq) => $qq->where('orders.created_at', '>=', $since))
                    ->groupBy(
                        'orders.seller_id',
                        'users.name', 'users.username', 'users.image',
                        'users.service_city', 'users.service_area'
                    )
                    ->orderByDesc('completed_orders')
                    ->limit($limit);

                $rows = $q->get();

                // Attach rank (1-based) so callers don't have to re-compute.
                return $rows->values()->map(function ($row, $i) {
                    $row->rank = $i + 1;
                    return $row;
                });
            });
    }

    /**
     * Get the rank of a specific seller in the all-time top 100.
     * Returns null if the seller is not in the top 100.
     */
    public function getRankForSeller(int $sellerId, string $period = 'all'): ?int
    {
        $top = $this->getTopSellers(100, $period);
        foreach ($top as $row) {
            if ((int) $row->seller_id === $sellerId) return (int) $row->rank;
        }
        return null;
    }

    /**
     * Founder Providers — sellers ranked by who got their FIRST completed order
     * earliest (not by registration date, not by order count). The date used is
     * created_at of the seller's earliest order that reached status 2, because
     * orders.updated_at keeps changing after completion.
     *
     * @return \Illuminate\Support\Collection each item: {seller_id, first_completed_at, rank}
     */
    public function getFounderProviders(int $limit = 100)
    {
        return Cache::remember('founder_providers_' . $limit, now()->addMinutes(self::CACHE_TTL_MIN),
            function () use ($limit) {
                return DB::table('orders')
                    ->join('users', 'users.id', '=', 'orders.seller_id')
                    ->where('orders.status', 2)
                    ->where('users.user_status', 1)
                    ->whereColumn('orders.seller_id', '!=', 'orders.buyer_id')
                    ->groupBy('orders.seller_id')
                    ->select('orders.seller_id', DB::raw('MIN(orders.created_at) as first_completed_at'))
                    ->orderBy('first_completed_at')->orderBy('orders.seller_id')
                    ->limit($limit)->get()
                    ->values()->map(function ($row, $i) {
                        $row->rank = $i + 1;
                        return $row;
                    });
            });
    }

    public function getFounderRank(int $sellerId): ?int
    {
        $row = $this->getFounderProviders(100)->firstWhere('seller_id', $sellerId);
        return $row ? (int) $row->rank : null;
    }

    /**
     * Force-clear all cached top-seller results. Call from admin action or
     * a nightly job if you want fresher data than the 60-minute default.
     */
    public function flush(): void
    {
        foreach (['all', 'year', 'month'] as $p) {
            foreach ([10, 20, 50, 100] as $lim) {
                Cache::forget(sprintf(self::CACHE_KEY, $p, $lim));
            }
        }
    }
}
