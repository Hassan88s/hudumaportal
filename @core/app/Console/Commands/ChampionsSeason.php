<?php

namespace App\Console\Commands;

use App\Services\ChampionsService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * HUDUMA CHAMPIONS season jobs (PDF §2 season cycle, §7, §19, §34, §36).
 *
 *   champions:season confirm    hourly  — pending HP → confirmed once matured
 *   champions:season bonuses    day 1   — month-end quality (provider) + loyalty (client) bonuses for last season
 *   champions:season finalize   day 4   — rank last season, write provisional Top Five, move season to 'finalized'
 *
 * Winners stay 'provisional' until an admin approves them (manual Top 20
 * review, PDF §30). Approval, badges and the day-5 announcement happen in
 * the admin panel.
 */
class ChampionsSeason extends Command
{
    protected $signature = 'champions:season {action : confirm|sync|weekly|nudges|bonuses|finalize} {--season= : YYYY-MM (default: previous month)}';
    protected $description = 'Huduma Champions season jobs: confirm pending HP, month-end bonuses, finalize Top Five.';

    public function handle(ChampionsService $svc): int
    {
        if (!$svc->enabled()) { $this->line('Champions disabled.'); return self::SUCCESS; }

        $season = $this->option('season') ?: $svc->now()->subMonthNoOverflow()->format('Y-m');

        switch ($this->argument('action')) {
            case 'confirm':
                $n = $svc->confirmMatured();
                $this->info("Confirmed {$n} pending HP rows.");
                break;

            case 'sync':
                $n = $svc->syncRecent();
                $this->info('Synced: ' . json_encode($n));
                break;

            case 'weekly':
                $this->weeklyResponseBonus($svc);
                break;

            case 'nudges':
                $this->progressNudges($svc);
                break;

            case 'bonuses':
                $this->monthEndBonuses($svc, $season);
                break;

            case 'finalize':
                $this->finalize($svc, $season);
                break;

            default:
                $this->error('Unknown action.');
                return self::FAILURE;
        }
        return self::SUCCESS;
    }

    /* ------------------------------------------------------------------ */

    protected function monthEndBonuses(ChampionsService $svc, string $season): void
    {
        [$from, $to] = $svc->seasonRangeUtc($season);
        DB::table('champion_seasons')->where('season_key', $season)->update(['status' => 'auditing', 'updated_at' => now()]);

        $given = 0;

        // ── Providers (PDF §7) ──
        $providers = DB::table('orders')->whereBetween('updated_at', [$from, $to])
            ->whereIn('status', [2, 4])->distinct()->pluck('seller_id');

        foreach ($providers as $sid) {
            $done      = DB::table('orders')->where('seller_id', $sid)->where('status', 2)->whereBetween('updated_at', [$from, $to])->count();
            $cancelled = DB::table('orders')->where('seller_id', $sid)->where('status', 4)->whereBetween('updated_at', [$from, $to])->count();
            $rating    = (float) DB::table('reviews')->where('seller_id', $sid)->whereBetween('created_at', [$from, $to])->avg('rating');
            $repeat    = DB::table('orders')->where('seller_id', $sid)->where('status', 2)->whereBetween('updated_at', [$from, $to])
                ->select('buyer_id')->groupBy('buyer_id')->havingRaw('COUNT(*) > 1')->get()->count();

            $opt = ['season_key' => $season, 'source_type' => 'season', 'source_id' => null];
            $total = $done + $cancelled;

            if ($total > 0 && $done / $total >= 0.95)          $given += (int) (bool) $svc->award($sid, 'p_q_completion_95', $opt);
            if ($rating >= 4.5 && $done >= 3)                   $given += (int) (bool) $svc->award($sid, 'p_q_rating', $opt);
            if ($done >= 5 && $cancelled === 0)                 $given += (int) (bool) $svc->award($sid, 'p_q_zero_cancel', $opt);
            if ($repeat >= 5)                                   $given += (int) (bool) $svc->award($sid, 'p_q_repeat_5', $opt);
            if ($done >= 1 && !$this->hasUpheldComplaint($sid, $from, $to)) $given += (int) (bool) $svc->award($sid, 'p_q_zero_complaints', $opt);
        }

        // ── Clients (PDF §19) ──
        $clients = DB::table('orders')->whereBetween('updated_at', [$from, $to])
            ->whereIn('status', [2, 4])->distinct()->pluck('buyer_id');

        foreach ($clients as $bid) {
            $completed = DB::table('orders')->where('buyer_id', $bid)->where('status', 2)->whereBetween('updated_at', [$from, $to]);
            $done      = (clone $completed)->count();
            $cancelled = DB::table('orders')->where('buyer_id', $bid)->where('status', 4)->whereBetween('updated_at', [$from, $to])->count();
            $cats      = (clone $completed)->join('services', 'services.id', '=', 'orders.service_id')->distinct()->count('services.category_id');
            $sameProv  = (clone $completed)->select('seller_id')->groupBy('seller_id')->havingRaw('COUNT(*) > 1')->get()->count();

            $opt = ['season_key' => $season, 'source_type' => 'season', 'source_id' => null];

            if ($cats >= 2)                     $given += (int) (bool) $svc->award($bid, 'c_l_two_categories', $opt);
            if ($cats >= 3)                     $given += (int) (bool) $svc->award($bid, 'c_l_three_categories', $opt);
            if ($done >= 3 && $cancelled === 0) $given += (int) (bool) $svc->award($bid, 'c_l_three_no_cancel', $opt);
            if ($done >= 5)                     $given += (int) (bool) $svc->award($bid, 'c_l_five_bookings', $opt);
            if ($sameProv >= 1)                 $given += (int) (bool) $svc->award($bid, 'c_l_same_provider', $opt);
        }

        $msg = "[Champions] {$season} month-end bonuses awarded: {$given}";
        $this->info($msg);
        Log::info($msg);
    }

    /** PDF §5: +50 HP for 90%+ response rate over the last 7 days (runs Monday 00:30 EAT). */
    protected function weeklyResponseBonus(ChampionsService $svc): void
    {
        $to = $svc->now()->startOfDay()->utc(); $from = (clone $to)->subDays(7);
        $week = $svc->now()->subDay()->format('o-\WW');
        $given = 0;
        foreach (DB::table('live_chat_messages')->whereBetween('created_at', [$from, $to])->whereNotNull('seller_id')->distinct()->pluck('seller_id') as $sid) {
            $rate = $svc->responseRate((int) $sid, $from, $to);
            if ($rate !== null && $rate >= 0.9) {
                $given += (int) (bool) $svc->award((int) $sid, 'p_weekly_response_90', ['source_type' => 'week', 'source_id' => (int) str_replace(['-', 'W'], '', $week)]);
            }
        }
        $this->info("Weekly response bonuses: {$given}");
    }

    /**
     * PDF §27, §32, §33: progress nudges — gap to Top 10 / Top 5, 48 hours left,
     * provisional leaderboard notice. Runs daily 18:00 EAT; one message per user per day.
     */
    protected function progressNudges(ChampionsService $svc): void
    {
        $season = $svc->currentSeasonKey();
        $days   = $svc->daysRemaining();
        $sent   = 0;

        foreach (['provider', 'client'] as $league) {
            $board = $svc->leaderboard($league, $season, 30);
            $name  = $league === 'provider' ? 'Huduma Pro League' : 'Huduma Client League';

            foreach ($board as $i => $row) {
                $rank = $i + 1;
                $key  = "champ_nudge_{$row->user_id}_" . $svc->now()->format('Ymd');
                if (\Cache::has($key)) continue;

                $msg = null;
                if ($days <= 2) {
                    $msg = $rank <= 5
                        ? "Only 48 hours left in {$name}! You are #{$rank} — protect your Top Five place."
                        : "Final 48 hours in {$name}! You are #{$rank}. Complete one more job to climb.";
                } elseif ($rank > 10 && $rank <= 30 && isset($board[9])) {
                    $gap = (int) $board[9]->hp - (int) $row->hp + 1;
                    $msg = "You are #{$rank} in {$name}. You need {$gap} HP to enter the Top 10.";
                } elseif ($rank > 5 && $rank <= 10 && isset($board[4])) {
                    $gap = (int) $board[4]->hp - (int) $row->hp + 1;
                    $msg = "You are #{$rank} in {$name}. {$gap} HP more puts you in the Top Five.";
                }
                if (!$msg || !function_exists('notifySeller')) continue;

                notifySeller((int) $row->user_id, $msg, $msg, [
                    'type' => 'gernalnotifications', 'id' => uniqid('notif_'), 'details' => $msg, 'event' => 'champions_progress',
                ]);
                \Cache::put($key, 1, now()->addDay());
                $sent++;
            }
        }
        $this->info("Progress nudges sent: {$sent}");
    }

    protected function hasUpheldComplaint(int $sellerId, string $from, string $to): bool
    {
        if (!\Schema::hasTable('reports')) return false;
        return DB::table('reports')->where('seller_id', $sellerId)
            ->whereBetween('created_at', [$from, $to])->exists();
    }

    protected function finalize(ChampionsService $svc, string $season): void
    {
        $svc->confirmMatured();
        $blockRepeat = (int) (\App\StaticOption::where('option_name', 'champions_block_repeat_winner')->value('option_value') ?? 1) === 1;
        $prevSeason  = Carbon::createFromFormat('Y-m', $season)->subMonthNoOverflow()->format('Y-m');

        foreach (['provider', 'client'] as $league) {
            $svc->forgetBoard($league, $season);
            $board = $svc->leaderboard($league, $season, 100);

            // PDF §36: last month's #1 cannot take #1 again this month
            if ($blockRepeat && $board->count() > 1) {
                $lastChamp = DB::table('champion_winners')
                    ->where(['season_key' => $prevSeason, 'league' => $league, 'rank' => 1])
                    ->whereIn('status', ['approved', 'paid'])->value('user_id');
                if ($lastChamp && (int) $board[0]->user_id === (int) $lastChamp) {
                    $first = $board->shift();
                    $board->splice(1, 0, [$first]);
                    $board = $board->values();
                }
            }

            foreach ($board->take(5)->values() as $i => $row) {
                $rank = $i + 1;
                [$type, $amount, $benefits] = ChampionsService::REWARDS[$league][$rank];
                DB::table('champion_winners')->updateOrInsert(
                    ['season_key' => $season, 'league' => $league, 'rank' => $rank],
                    ['user_id' => $row->user_id, 'final_hp' => (int) $row->hp, 'reward_type' => $type,
                     'reward_amount' => $amount, 'benefits' => $benefits, 'status' => 'provisional',
                     'updated_at' => now(), 'created_at' => now()]
                );
            }
            $this->info(ucfirst($league) . " league: " . min(5, $board->count()) . " provisional winners written.");
        }

        DB::table('champion_seasons')->where('season_key', $season)
            ->update(['status' => 'finalized', 'finalized_at' => now(), 'updated_at' => now()]);
        Log::info("[Champions] {$season} finalized — provisional Top Five awaiting admin approval.");
    }
}
