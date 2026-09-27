<?php

namespace App\Console\Commands;

use App\Services\ChampionsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Demo data for testing Huduma Champions end to end.
 *
 *   php artisan champions:demo-data          create demo activity and score it
 *   php artisan champions:demo-data --clean  remove everything it created
 *
 * Everything it writes is tagged so --clean can find it again:
 *   orders.invoice        starts with DEMO-
 *   reviews.message       starts with [demo]
 *   live_chat_messages    starts with [demo]
 *   portfolios.name       starts with [demo]
 *   buyer_jobs.title      starts with [demo]
 * Never run this on the live site.
 */
class ChampionsDemoData extends Command
{
    protected $signature = 'champions:demo-data {--clean : delete the demo data instead of creating it}';
    protected $description = 'Create (or remove) demo marketplace activity so Huduma Champions can be tested.';

    protected const TAG = '[demo]';

    public function handle(ChampionsService $svc): int
    {
        if ($this->option('clean')) return $this->clean();

        $seller = DB::table('services')->join('users', 'users.id', '=', 'services.seller_id')
            ->where('users.user_type', 0)->where('users.user_status', 1)
            ->orderBy('services.id')->select('users.id', 'users.username', 'services.id as service_id')->first();
        if (!$seller) { $this->error('No active seller with a service found.'); return self::FAILURE; }

        $buyers = DB::table('users')->where('user_type', 1)->where('user_status', 1)
            ->where('id', '!=', $seller->id)->orderBy('id')->limit(3)->get(['id', 'username']);
        if ($buyers->count() < 2) { $this->error('Need at least 2 buyer accounts.'); return self::FAILURE; }

        $this->info("Seller: {$seller->username} (#{$seller->id}) · service #{$seller->service_id}");
        $this->info('Buyers: ' . $buyers->map(fn ($b) => "{$b->username} (#{$b->id})")->implode(', '));

        $template = (array) DB::table('orders')->first();
        if (!$template) { $this->error('No existing order to copy the column layout from.'); return self::FAILURE; }
        unset($template['id']);

        // ── Completed orders: 2 for the first buyer (repeat client), 1 each for the others
        $orderIds = [];
        $plan = [[$buyers[0], 35000], [$buyers[0], 42000], [$buyers[1], 28000]];
        if (isset($buyers[2])) $plan[] = [$buyers[2], 60000];

        foreach ($plan as $i => [$buyer, $total]) {
            $orderIds[] = DB::table('orders')->insertGetId(array_merge($template, [
                'invoice' => 'DEMO-' . strtoupper(uniqid()),
                'service_id' => $seller->service_id, 'seller_id' => $seller->id, 'buyer_id' => $buyer->id,
                'status' => 2, 'payment_status' => 'complete', 'total' => $total, 'sub_total' => $total,
                'commission_amount' => round($total * 0.1, 2),
                'created_at' => now()->subDays(3 - $i)->subHours(2), 'updated_at' => now()->subHours($i + 1),
            ]));
        }
        $this->line('  orders created: ' . count($orderIds));

        // ── Verified reviews on two of them
        $reviewIds = [];
        foreach (array_slice($orderIds, 0, 2) as $k => $orderId) {
            $order = DB::table('orders')->where('id', $orderId)->first();
            $reviewIds[] = DB::table('reviews')->insertGetId([
                'order_id' => $orderId, 'service_id' => $seller->service_id, 'seller_id' => $seller->id,
                'buyer_id' => $order->buyer_id, 'type' => 1, 'rating' => 5,
                'name' => 'Demo client', 'email' => 'demo@example.com',
                'message' => self::TAG . ' Very professional, arrived on time and finished the work properly.',
                'created_at' => now()->subHours(2 - $k), 'updated_at' => now(),
            ]);
        }
        $this->line('  reviews created: ' . count($reviewIds));

        // ── Chat: a client question, answered 12 minutes later (fast), and one answered after 50 minutes
        DB::table('live_chat_messages')->insert([
            ['from_user' => $buyers[0]->id, 'to_user' => $seller->id, 'seller_id' => $seller->id, 'buyer_id' => $buyers[0]->id,
             'message' => self::TAG . ' Hi, are you available this week?', 'created_at' => now()->subMinutes(90), 'updated_at' => now()],
            ['from_user' => $seller->id, 'to_user' => $buyers[0]->id, 'seller_id' => $seller->id, 'buyer_id' => $buyers[0]->id,
             'message' => self::TAG . ' Yes, I can come on Thursday morning.', 'created_at' => now()->subMinutes(78), 'updated_at' => now()],
            ['from_user' => $buyers[1]->id, 'to_user' => $seller->id, 'seller_id' => $seller->id, 'buyer_id' => $buyers[1]->id,
             'message' => self::TAG . ' What do you charge for a full house rewire?', 'created_at' => now()->subMinutes(70), 'updated_at' => now()],
            ['from_user' => $seller->id, 'to_user' => $buyers[1]->id, 'seller_id' => $seller->id, 'buyer_id' => $buyers[1]->id,
             'message' => self::TAG . ' Around 250,000 TZS depending on the rooms.', 'created_at' => now()->subMinutes(20), 'updated_at' => now()],
        ]);
        $this->line('  chat messages created: 4 (one fast reply, one slow reply)');

        // ── A saved provider, a portfolio item, a job post and a proposal
        DB::table('bookmarks')->insertOrIgnore([
            'user_id' => $buyers[0]->id, 'service_id' => $seller->service_id, 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('portfolios')->insert([
            'freelancer_id' => $seller->id, 'name' => self::TAG . ' Office rewiring, Arusha',
            'description' => 'Demo portfolio item created for Champions testing.',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $jobId = null;
        if (\Schema::hasTable('buyer_jobs')) {
            $jobTemplate = (array) DB::table('buyer_jobs')->first();
            if ($jobTemplate) {
                unset($jobTemplate['id']);
                $jobId = DB::table('buyer_jobs')->insertGetId(array_merge($jobTemplate, [
                    'buyer_id' => $buyers[0]->id, 'title' => self::TAG . ' Need an electrician this week',
                    'slug' => 'demo-champions-' . uniqid(),
                    'created_at' => now()->subHours(6), 'updated_at' => now(),
                ]));
                DB::table('job_requests')->insert([
                    'seller_id' => $seller->id, 'buyer_id' => $buyers[0]->id, 'job_post_id' => $jobId,
                    'is_hired' => 0, 'expected_salary' => 50000, 'cover_letter' => self::TAG . ' I can start on Thursday.',
                    'created_at' => now()->subHours(5), 'updated_at' => now(),
                ]);
            }
        }
        $this->line('  bookmark, portfolio item' . ($jobId ? ', job post and proposal' : '') . ' created');

        // ── Score it
        $n = $svc->syncRecent(now()->subDays(5));
        $this->info('Synced: ' . json_encode($n));

        // Order points are held for the refund window; mature them so the boards fill now
        DB::table('champion_points')->where('status', 'pending')->update(['confirm_after' => now()->subMinute()]);
        $this->info('Confirmed pending rows: ' . $svc->confirmMatured());

        foreach (['provider', 'client'] as $league) {
            $svc->forgetBoard($league, $svc->currentSeasonKey());
            $this->info(strtoupper($league) . ' league:');
            foreach ($svc->leaderboard($league, null, 5) as $row) {
                $this->line("  #{$row->rank}  {$row->display_name}  {$row->hp} HP  ({$row->completed} completed)");
            }
        }

        $this->info('Done. Remove it again with: php artisan champions:demo-data --clean');
        return self::SUCCESS;
    }

    protected function clean(): int
    {
        $orderIds = DB::table('orders')->where('invoice', 'like', 'DEMO-%')->pluck('id');
        $reviewIds = DB::table('reviews')->where('message', 'like', self::TAG . '%')->pluck('id');
        $chatIds = DB::table('live_chat_messages')->where('message', 'like', self::TAG . '%')->pluck('id');
        $jobIds = \Schema::hasTable('buyer_jobs') ? DB::table('buyer_jobs')->where('title', 'like', self::TAG . '%')->pluck('id') : collect();
        $portfolioIds = DB::table('portfolios')->where('name', 'like', self::TAG . '%')->pluck('id');

        // Points first, so nothing is left pointing at deleted rows
        $points = DB::table('champion_points')
            ->where(fn ($q) => $q->where(fn ($w) => $w->where('source_type', 'order')->whereIn('source_id', $orderIds))
                ->orWhere(fn ($w) => $w->where('source_type', 'review')->whereIn('source_id', $reviewIds))
                ->orWhere(fn ($w) => $w->where('source_type', 'chat_message')->whereIn('source_id', $chatIds))
                ->orWhere(fn ($w) => $w->where('source_type', 'portfolio')->whereIn('source_id', $portfolioIds))
                ->orWhere(fn ($w) => $w->where('source_type', 'job')->whereIn('source_id', $jobIds))
                ->orWhere(fn ($w) => $w->where('source_type', 'referral_order')->whereIn('source_id', $orderIds)))
            ->delete();

        if ($jobIds->count()) DB::table('job_requests')->whereIn('job_post_id', $jobIds)->delete();
        $deleted = [
            'champion_points' => $points,
            'orders' => DB::table('orders')->whereIn('id', $orderIds)->delete(),
            'reviews' => DB::table('reviews')->whereIn('id', $reviewIds)->delete(),
            'live_chat_messages' => DB::table('live_chat_messages')->whereIn('id', $chatIds)->delete(),
            'portfolios' => DB::table('portfolios')->whereIn('id', $portfolioIds)->delete(),
            'buyer_jobs' => $jobIds->count() ? DB::table('buyer_jobs')->whereIn('id', $jobIds)->delete() : 0,
        ];

        $svc = app(ChampionsService::class);
        foreach (['provider', 'client'] as $league) $svc->forgetBoard($league, $svc->currentSeasonKey());

        $this->info('Removed: ' . json_encode($deleted));
        return self::SUCCESS;
    }
}
