<?php

namespace App\Services;

use App\StaticOption;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * HUDUMA CHAMPIONS — monthly gamification points engine.
 *
 * One ledger (champion_points) feeds two leagues:
 *   provider  → HUDUMA PRO LEAGUE
 *   client    → HUDUMA CLIENT LEAGUE
 *
 * Every earning/deduction goes through award() so that "once", "per month",
 * cap-group and provider-client collusion limits are enforced in one place,
 * and every row is idempotent on (rule, source).
 *
 * Only CONFIRMED points count toward the live ranking. Transaction points are
 * written PENDING and confirmed later by confirmMatured() (scheduler) once the
 * refund / fraud-review window has passed; reverseBySource() removes them if
 * the order is refunded or cancelled first.
 */
class ChampionsService
{
    public const TZ = 'Africa/Dar_es_Salaam';

    /**
     * Rule book. Keys:
     *   hp       points (negative = deduction)
     *   limit    'once' (lifetime) | 'month' (once per season) | int (max times per season)
     *   cap      cap group name (monthly HP ceiling shared by several rules)
     *   pending  true = written as pending (transaction points)
     *   pair     true = counts toward the same provider/client pair cap
     */
    public const RULES = [
        // ── PRO LEAGUE: account & profile (PDF §4) ──
        'p_verify_account'      => ['league' => 'provider', 'hp' => 50,  'limit' => 'once',  'label' => 'Verified phone/account'],
        'p_profile_80'          => ['league' => 'provider', 'hp' => 50,  'limit' => 'once',  'label' => 'Profile 80% complete'],
        'p_profile_100'         => ['league' => 'provider', 'hp' => 100, 'limit' => 'once',  'label' => 'Profile 100% complete'],
        'p_identity_verified'   => ['league' => 'provider', 'hp' => 100, 'limit' => 'once',  'label' => 'Identity/business verified'],
        'p_portfolio_item'      => ['league' => 'provider', 'hp' => 20,  'limit' => 5,       'label' => 'Added portfolio item'],
        'p_service_pricing'     => ['league' => 'provider', 'hp' => 25,  'limit' => 3,       'label' => 'Added complete service pricing'],
        'p_service_published'   => ['league' => 'provider', 'hp' => 75,  'limit' => 3,       'label' => 'Published approved service'],
        'p_onboarding_tutorial' => ['league' => 'provider', 'hp' => 30,  'limit' => 'once',  'label' => 'Completed onboarding tutorial'],

        // ── PRO LEAGUE: engagement (PDF §5) — response cap 250/month ──
        'p_enquiry_response'    => ['league' => 'provider', 'hp' => 5,   'cap' => 'response', 'label' => 'Responded to client enquiry'],
        'p_fast_response'       => ['league' => 'provider', 'hp' => 10,  'cap' => 'response', 'label' => 'Responded within 30 minutes'],
        'p_proposal_sent'       => ['league' => 'provider', 'hp' => 10,  'label' => 'Sent proposal to job post'],
        'p_proposal_accepted'   => ['league' => 'provider', 'hp' => 60,  'label' => 'Proposal accepted'],
        'p_first_booking_month' => ['league' => 'provider', 'hp' => 50,  'limit' => 'month', 'label' => 'First booking of the month'],
        'p_weekly_response_90'  => ['league' => 'provider', 'hp' => 50,  'cap' => 'response', 'label' => '90%+ response rate this week'],

        // ── PRO LEAGUE: transactions (PDF §6) ──
        'p_service_completed'   => ['league' => 'provider', 'hp' => 150, 'pending' => true, 'pair' => true, 'label' => 'Completed qualifying service'],
        'p_repeat_client'       => ['league' => 'provider', 'hp' => 50,  'pending' => true, 'pair' => true, 'label' => 'Customer became repeat client'],
        'p_milestone_3'         => ['league' => 'provider', 'hp' => 150, 'limit' => 'month', 'pending' => true, 'label' => 'Completed 3 services this month'],
        'p_milestone_5'         => ['league' => 'provider', 'hp' => 300, 'limit' => 'month', 'pending' => true, 'label' => 'Completed 5 services this month'],
        'p_milestone_10'        => ['league' => 'provider', 'hp' => 750, 'limit' => 'month', 'pending' => true, 'label' => 'Completed 10 services this month'],
        'p_review_received'     => ['league' => 'provider', 'hp' => 25,  'label' => 'Received verified client review'],
        'p_recurring_completed' => ['league' => 'provider', 'hp' => 75,  'pending' => true, 'label' => 'Completed recurring service'],

        // ── PRO LEAGUE: month-end quality bonuses (PDF §7) ──
        'p_q_response_90'       => ['league' => 'provider', 'hp' => 100, 'limit' => 'month', 'label' => 'Quality: 90%+ response rate'],
        'p_q_completion_95'     => ['league' => 'provider', 'hp' => 150, 'limit' => 'month', 'label' => 'Quality: 95%+ completion rate'],
        'p_q_rating'            => ['league' => 'provider', 'hp' => 200, 'limit' => 'month', 'label' => 'Quality: excellent verified rating'],
        'p_q_zero_cancel'       => ['league' => 'provider', 'hp' => 150, 'limit' => 'month', 'label' => 'Quality: zero cancellations after 5+ jobs'],
        'p_q_zero_complaints'   => ['league' => 'provider', 'hp' => 100, 'limit' => 'month', 'label' => 'Quality: zero upheld complaints'],
        'p_q_repeat_5'          => ['league' => 'provider', 'hp' => 250, 'limit' => 'month', 'label' => 'Quality: 5+ repeat-client transactions'],

        // ── PRO LEAGUE: referrals (PDF §8) — cap 500/month ──
        'p_ref_provider_qualified'     => ['league' => 'provider', 'hp' => 75,  'cap' => 'referral', 'label' => 'Referred provider became qualified'],
        'p_ref_client_first_txn'       => ['league' => 'provider', 'hp' => 100, 'cap' => 'referral', 'label' => 'Referred client completed first transaction'],
        'p_ref_provider_first_booking' => ['league' => 'provider', 'hp' => 100, 'cap' => 'referral', 'label' => 'Referred provider completed first booking'],

        // ── PRO LEAGUE: deductions (PDF §9) ──
        'p_cancel_no_reason'    => ['league' => 'provider', 'hp' => -100, 'label' => 'Provider-caused cancellation'],
        'p_slow_responses'      => ['league' => 'provider', 'hp' => -50,  'label' => 'Repeated slow/no responses'],
        'p_fake_listing'        => ['league' => 'provider', 'hp' => -500, 'label' => 'Confirmed fake listing'],
        'p_policy_violation'    => ['league' => 'provider', 'hp' => -500, 'label' => 'Serious policy violation'],

        // ── CLIENT LEAGUE: account (PDF §14) ──
        'c_verify_account'      => ['league' => 'client', 'hp' => 50, 'limit' => 'once', 'label' => 'Verified account'],
        'c_profile_complete'    => ['league' => 'client', 'hp' => 50, 'limit' => 'once', 'label' => 'Completed profile'],
        'c_preferences'         => ['league' => 'client', 'hp' => 25, 'limit' => 'once', 'label' => 'Added location/service preferences'],
        'c_onboarding'          => ['league' => 'client', 'hp' => 25, 'limit' => 'once', 'label' => 'Completed client onboarding'],

        // ── CLIENT LEAGUE: discovery (PDF §15) ──
        'c_save_provider'       => ['league' => 'client', 'hp' => 5,  'cap' => 'save',    'label' => 'Saved a provider'],
        'c_request_created'     => ['league' => 'client', 'hp' => 30, 'limit' => 5,       'label' => 'Created service request'],
        'c_request_response'    => ['league' => 'client', 'hp' => 10, 'limit' => 5,       'label' => 'Received provider response'],
        'c_compare_providers'   => ['league' => 'client', 'hp' => 5,  'cap' => 'compare', 'label' => 'Compared providers'],

        // ── CLIENT LEAGUE: bookings (PDF §16) ──
        'c_first_booking_month' => ['league' => 'client', 'hp' => 150, 'limit' => 'month', 'pending' => true, 'pair' => true, 'label' => 'First completed booking of the month'],
        'c_additional_booking'  => ['league' => 'client', 'hp' => 120, 'pending' => true, 'pair' => true, 'label' => 'Additional qualifying booking'],
        'c_second_booking'      => ['league' => 'client', 'hp' => 75,  'limit' => 'month', 'pending' => true, 'label' => 'Second booking this month'],
        'c_bookings_3'          => ['league' => 'client', 'hp' => 150, 'limit' => 'month', 'pending' => true, 'label' => 'Completed 3 bookings'],
        'c_bookings_5'          => ['league' => 'client', 'hp' => 300, 'limit' => 'month', 'pending' => true, 'label' => 'Completed 5 bookings'],
        'c_rebook_provider'     => ['league' => 'client', 'hp' => 50,  'pending' => true, 'label' => 'Booked a provider used before'],
        'c_recurring_booking'   => ['league' => 'client', 'hp' => 75,  'pending' => true, 'label' => 'Completed recurring booking'],
        'c_new_category'        => ['league' => 'client', 'hp' => 30,  'pending' => true, 'label' => 'Tried a new service category'],

        // ── CLIENT LEAGUE: community (PDF §17) ──
        'c_review'              => ['league' => 'client', 'hp' => 25, 'label' => 'Left verified review'],
        'c_review_written'      => ['league' => 'client', 'hp' => 10, 'label' => 'Added useful written feedback'],
        'c_confirm_prompt'      => ['league' => 'client', 'hp' => 10, 'label' => 'Confirmed job completion promptly'],
        'c_problem_report'      => ['league' => 'client', 'hp' => 25, 'label' => 'Verified marketplace problem report'],
        'c_safety_education'    => ['league' => 'client', 'hp' => 25, 'limit' => 'once', 'label' => 'Completed customer-safety education'],

        // ── CLIENT LEAGUE: referrals (PDF §18) — cap 500/month ──
        'c_ref_client_verified'        => ['league' => 'client', 'hp' => 25,  'cap' => 'referral', 'label' => 'Referred client verified account'],
        'c_ref_client_first_booking'   => ['league' => 'client', 'hp' => 100, 'cap' => 'referral', 'label' => 'Referred client completed first booking'],
        'c_ref_provider_qualified'     => ['league' => 'client', 'hp' => 75,  'cap' => 'referral', 'label' => 'Referred provider became qualified'],
        'c_ref_provider_first_service' => ['league' => 'client', 'hp' => 100, 'cap' => 'referral', 'label' => 'Referred provider completed first service'],

        // ── CLIENT LEAGUE: month-end loyalty (PDF §19) ──
        'c_l_two_categories'    => ['league' => 'client', 'hp' => 50,  'limit' => 'month', 'label' => 'Loyalty: bookings in 2 categories'],
        'c_l_three_categories'  => ['league' => 'client', 'hp' => 100, 'limit' => 'month', 'label' => 'Loyalty: bookings in 3 categories'],
        'c_l_three_no_cancel'   => ['league' => 'client', 'hp' => 100, 'limit' => 'month', 'label' => 'Loyalty: 3 bookings, no cancellations'],
        'c_l_five_bookings'     => ['league' => 'client', 'hp' => 250, 'limit' => 'month', 'label' => 'Loyalty: 5 completed bookings'],
        'c_l_same_provider'     => ['league' => 'client', 'hp' => 100, 'limit' => 'month', 'label' => 'Loyalty: booked same provider twice'],
        'c_l_recurring'         => ['league' => 'client', 'hp' => 150, 'limit' => 'month', 'label' => 'Loyalty: recurring schedule completed'],

        // ── CLIENT LEAGUE: deductions (PDF §20) ──
        'c_fake_request'        => ['league' => 'client', 'hp' => -100, 'label' => 'Fake service request'],
        'c_repeated_cancel'     => ['league' => 'client', 'hp' => -75,  'label' => 'Repeated client-caused cancellation'],
        'c_fake_review'         => ['league' => 'client', 'hp' => -500, 'label' => 'Fake review'],

        // ── Shared ──
        'mission_complete'      => ['league' => null, 'hp' => 0, 'label' => 'Mission completed'],
        'admin_adjustment'      => ['league' => null, 'hp' => 0, 'label' => 'Manual adjustment'],
    ];

    /** Monthly HP ceilings per cap group (PDF §5, §8, §15, §18). */
    public const CAPS = [
        'response' => 250,
        'referral' => 500,
        'save'     => 25,
        'compare'  => 25,
    ];

    /** Level thresholds (PDF §10, §21) — calibrate after 2–3 months of data. */
    public const LEVELS = [
        'provider' => [0 => 'Starter', 500 => 'Bronze Provider', 1250 => 'Silver Provider', 2500 => 'Gold Provider', 5000 => 'Elite Provider', 8000 => 'Huduma Master'],
        'client'   => [0 => 'Explorer', 300 => 'Active Client', 750 => 'Silver Client', 1500 => 'Gold Client', 3000 => 'VIP Client', 5000 => 'Huduma Champion'],
    ];

    /** Top Five rewards (PDF §12, §23). */
    public const REWARDS = [
        'provider' => [
            1 => ['cash',   300000, '30-day featured profile; 1 month Premium Plus; Champion badge; social feature; priority placement'],
            2 => ['cash',   200000, '21-day featured profile; 1 month Premium; Runner-Up badge; social feature'],
            3 => ['cash',   100000, '14-day featured profile; 1 month Professional; Top Provider badge'],
            4 => ['credit',  75000, '10-day service boost; Top Five badge'],
            5 => ['credit',  50000, '7-day service boost; Top Five badge'],
        ],
        'client' => [
            1 => ['credit', 200000, 'VIP Client badge; 30-day priority support; optional community recognition'],
            2 => ['credit', 150000, 'Gold Client badge; 30-day VIP benefits'],
            3 => ['credit', 100000, 'Top Client badge'],
            4 => ['credit',  75000, 'Top Five recognition'],
            5 => ['credit',  50000, 'Top Five recognition'],
        ],
    ];

    /* =================================================================
     |  Seasons
     | ================================================================= */

    public function enabled(): bool
    {
        // Stay off until _deploy/champions_schema.sql has been run on this server.
        if (!Cache::remember('champions_tables_ok', now()->addMinutes(10), fn () => \Schema::hasTable('champion_points'))) return false;
        return (int) (StaticOption::where('option_name', 'champions_enabled')->value('option_value') ?? 1) === 1;
    }

    public function now(): Carbon
    {
        return Carbon::now(self::TZ);
    }

    public function currentSeasonKey(): string
    {
        return $this->now()->format('Y-m');
    }

    /** Ensure the season row exists and return it. */
    public function season(?string $key = null): object
    {
        $key = $key ?: $this->currentSeasonKey();
        $row = DB::table('champion_seasons')->where('season_key', $key)->first();
        if ($row) return $row;

        $start = Carbon::createFromFormat('Y-m-d H:i:s', $key . '-01 00:00:00', self::TZ);
        DB::table('champion_seasons')->insertOrIgnore([
            'season_key' => $key,
            'starts_at'  => $start->toDateTimeString(),
            'ends_at'    => $start->copy()->endOfMonth()->toDateTimeString(),
            'status'     => 'open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return DB::table('champion_seasons')->where('season_key', $key)->first();
    }

    public function daysRemaining(): int
    {
        $n = $this->now();
        return (int) $n->diffInDays($n->copy()->endOfMonth());
    }

    /* =================================================================
     |  Awarding
     | ================================================================= */

    /**
     * Award (or deduct) HP for a rule. Safe to call blind: returns null when
     * disabled, disqualified, over a limit / cap, or already awarded for this
     * source.
     *
     * @param array $opt  source_type, source_id, counterparty_id, reason,
     *                    hp (override, used by missions/admin), league (override)
     */
    public function award(int $userId, string $ruleKey, array $opt = []): ?int
    {
        if (!$this->enabled() || !isset(self::RULES[$ruleKey])) return null;

        $rule    = self::RULES[$ruleKey];
        $league  = $opt['league'] ?? $rule['league'];
        $hp      = (int) ($opt['hp'] ?? $rule['hp']);
        $season  = $opt['season_key'] ?? $this->currentSeasonKey();
        $srcType = $opt['source_type'] ?? null;
        $srcId   = $opt['source_id'] ?? null;
        if (!$league || $hp === 0) return null;

        $this->season($season);

        if ($this->isDisqualified($userId, $season, $league) && $hp > 0) return null;

        // Idempotency key: rule + scope + source
        $limit = $rule['limit'] ?? null;
        $scope = $limit === 'once' ? 'life' : $season;
        $idem  = implode(':', [$ruleKey, $userId, $scope, $srcType ?? '-', $srcId ?? ($limit === 'once' || $limit === 'month' ? '-' : uniqid('', true))]);
        if (DB::table('champion_points')->where('idempotency_key', $idem)->exists()) return null;

        // One award per (user, rule, source) across all seasons — a sync sweep that
        // straddles month-end must not score the same order/review twice.
        if ($srcType && $srcId && DB::table('champion_points')
                ->where(['user_id' => $userId, 'rule_key' => $ruleKey, 'source_type' => $srcType, 'source_id' => $srcId])
                ->exists()) return null;

        $base = DB::table('champion_points')->where('user_id', $userId)->where('rule_key', $ruleKey)->where('status', '!=', 'reversed');

        // Limits
        if ($limit === 'once' && (clone $base)->exists()) return null;
        if ($limit === 'month' && (clone $base)->where('season_key', $season)->exists()) return null;
        if (is_int($limit) && (clone $base)->where('season_key', $season)->count() >= $limit) return null;

        // Cap groups — trim the award to what's left under the ceiling
        $capGroup = $rule['cap'] ?? null;
        if ($capGroup && $hp > 0) {
            $used = (int) DB::table('champion_points')
                ->where('user_id', $userId)->where('season_key', $season)
                ->where('cap_group', $capGroup)->where('status', '!=', 'reversed')->sum('points');
            $left = (self::CAPS[$capGroup] ?? PHP_INT_MAX) - $used;
            if ($left <= 0) return null;
            $hp = min($hp, $left);
        }

        // Collusion guard: same provider/client pair can only generate N
        // point-earning transactions per season (PDF §30)
        if (!empty($rule['pair']) && !empty($opt['counterparty_id']) && $hp > 0) {
            $pairCap = (int) (StaticOption::where('option_name', 'champions_pair_txn_cap')->value('option_value') ?? 2);
            $pairUsed = DB::table('champion_points')
                ->where('user_id', $userId)->where('season_key', $season)
                ->where('rule_key', $ruleKey)->where('counterparty_id', $opt['counterparty_id'])
                ->where('status', '!=', 'reversed')->count();
            if ($pairUsed >= $pairCap) return null;
        }

        // Demand-balancing bonus (PDF §25): +X% on eligible positive activity
        if ($hp > 0 && (!empty($opt['city_id']) || !empty($opt['category_id']))) {
            $hp += $this->demandBonus($league, $season, $opt['city_id'] ?? null, $opt['category_id'] ?? null, $hp);
        }

        $pending  = !empty($rule['pending']) && $hp > 0;
        $holdDays = (int) (StaticOption::where('option_name', 'champions_pending_hold_days')->value('option_value') ?? 14);

        // Pending points must mature before rankings are confirmed on day 4 of
        // the next month, so the hold is the shorter of hold_days and that date.
        $finalDate    = Carbon::createFromFormat('Y-m-d', $season . '-01', self::TZ)->addMonth()->startOfMonth()->addDays(2)->endOfDay();
        $confirmAfter = $this->now()->addDays($holdDays);
        if ($confirmAfter->greaterThan($finalDate)) $confirmAfter = $finalDate;

        $id = DB::table('champion_points')->insertGetId([
            'user_id'         => $userId,
            'league'          => $league,
            'season_key'      => $season,
            'rule_key'        => $ruleKey,
            'cap_group'       => $capGroup,
            'points'          => $hp,
            'status'          => $pending ? 'pending' : 'confirmed',
            'source_type'     => $srcType,
            'source_id'       => $srcId,
            'counterparty_id' => $opt['counterparty_id'] ?? null,
            'reason'          => $opt['reason'] ?? $rule['label'],
            'idempotency_key' => $idem,
            'confirm_after'   => $pending ? $confirmAfter->copy()->setTimezone(config('app.timezone'))->toDateTimeString() : null,
            'confirmed_at'    => $pending ? null : now(),
            'admin_id'        => $opt['admin_id'] ?? null,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        $this->forgetBoard($league, $season);
        $this->advanceMissions($userId, $league, $ruleKey, $season);

        return $id;
    }

    /** Reverse every non-reversed row tied to a source (refund / cancellation / fraud). */
    public function reverseBySource(string $sourceType, int $sourceId, string $reason): int
    {
        $rows = DB::table('champion_points')
            ->where('source_type', $sourceType)->where('source_id', $sourceId)
            ->where('status', '!=', 'reversed')->where('points', '>', 0)->get();

        foreach ($rows as $r) {
            DB::table('champion_points')->where('id', $r->id)->update([
                'status' => 'reversed', 'reversed_at' => now(), 'reversal_reason' => $reason, 'updated_at' => now(),
            ]);
            $this->forgetBoard($r->league, $r->season_key);
        }
        return $rows->count();
    }

    /** Scheduler: promote matured pending rows to confirmed. */
    public function confirmMatured(): int
    {
        $seasons = DB::table('champion_points')
            ->where('status', 'pending')->where('confirm_after', '<=', now())
            ->distinct()->pluck('season_key');

        $n = DB::table('champion_points')
            ->where('status', 'pending')->where('confirm_after', '<=', now())
            ->update(['status' => 'confirmed', 'confirmed_at' => now(), 'updated_at' => now()]);

        foreach ($seasons as $s) {
            $this->forgetBoard('provider', $s);
            $this->forgetBoard('client', $s);
        }
        return $n;
    }

    public function isDisqualified(int $userId, string $season, string $league): bool
    {
        return DB::table('champion_disqualifications')
            ->where(['user_id' => $userId, 'season_key' => $season, 'league' => $league])->exists();
    }

    /* =================================================================
     |  Event orchestration — controllers call these one-liners
     | ================================================================= */

    /** Sellers are user_type 0 on Huduma Portal. */
    public function leagueOfUser($user): string
    {
        return (int) ($user->user_type ?? 1) === 0 ? 'provider' : 'client';
    }

    /**
     * Order reached status 2 (completed). Awards Pro League + Client League
     * transaction points, milestones, repeat/rebook, new-category, and the
     * referral HP for first transactions (PDF §6, §8, §16, §18). Idempotent.
     */
    public function onOrderCompleted(int $orderId): void
    {
        try {
            $o = DB::table('orders')->where('id', $orderId)->first();
            if (!$o || (int) $o->status !== 2 || empty($o->seller_id) || empty($o->buyer_id)) return;
            if ((int) $o->seller_id === (int) $o->buyer_id) return; // self-booking never scores

            $season = $this->currentSeasonKey();
            [$from, $to] = $this->seasonRangeUtc($season);

            // Category + city for demand bonuses and "new category"
            $categoryId = null; $cityId = null;
            if (!empty($o->service_id)) {
                $svc = DB::table('services')->where('id', $o->service_id)->select('category_id', 'service_city_id')->first();
                $categoryId = $svc->category_id ?? null; $cityId = $svc->service_city_id ?? null;
            } elseif (!empty($o->job_post_id)) {
                $job = DB::table('buyer_jobs')->where('id', $o->job_post_id)->select('category_id', 'city_id')->first();
                $categoryId = $job->category_id ?? null; $cityId = $job->city_id ?? null;
            }

            $base = ['source_type' => 'order', 'source_id' => $o->id, 'category_id' => $categoryId, 'city_id' => $cityId];

            // Month counts come from the ledger (award time), not orders.updated_at,
            // which later invoice/payment edits keep changing.
            $ledgerCount = fn (int $uid, array $rules) => DB::table('champion_points')
                ->where('user_id', $uid)->where('season_key', $season)
                ->whereIn('rule_key', $rules)->where('status', '!=', 'reversed')->count();

            // ── Provider ──
            $sid = (int) $o->seller_id; $bid = (int) $o->buyer_id;
            $priorPair = DB::table('orders')->where('seller_id', $sid)->where('buyer_id', $bid)->where('status', 2)->where('id', '!=', $o->id)->exists();

            if ($this->award($sid, 'p_service_completed', $base + ['counterparty_id' => $bid])) {
                $sellerMonth = $ledgerCount($sid, ['p_service_completed']);
                if ($sellerMonth === 1) $this->award($sid, 'p_first_booking_month', $base);
                if ($priorPair)         $this->award($sid, 'p_repeat_client', $base + ['counterparty_id' => $bid]);
                if ($sellerMonth >= 3)  $this->award($sid, 'p_milestone_3', $base);
                if ($sellerMonth >= 5)  $this->award($sid, 'p_milestone_5', $base);
                if ($sellerMonth >= 10) $this->award($sid, 'p_milestone_10', $base);
            }

            // ── Client ── (scored once per order: first vs additional is picked by
            // count, so a per-rule dedupe alone would let a re-run take the other rule)
            $clientScored = DB::table('champion_points')->where(['user_id' => $bid, 'source_type' => 'order', 'source_id' => $o->id])
                ->whereIn('rule_key', ['c_first_booking_month', 'c_additional_booking'])->exists();
            $buyerMonth = $ledgerCount($bid, ['c_first_booking_month', 'c_additional_booking']) + 1;
            if (!$clientScored && $this->award($bid, $buyerMonth === 1 ? 'c_first_booking_month' : 'c_additional_booking', $base + ['counterparty_id' => $sid])) {
                if ($buyerMonth === 2) $this->award($bid, 'c_second_booking', $base);
                if ($buyerMonth >= 3)  $this->award($bid, 'c_bookings_3', $base);
                if ($buyerMonth >= 5)  $this->award($bid, 'c_bookings_5', $base);
                if ($priorPair)        $this->award($bid, 'c_rebook_provider', $base);

                if ($categoryId) {
                    $hadOrders   = DB::table('orders')->where('buyer_id', $bid)->where('status', 2)->where('id', '!=', $o->id)->exists();
                    $hadCategory = DB::table('orders')->join('services', 'services.id', '=', 'orders.service_id')
                        ->where('orders.buyer_id', $bid)->where('orders.status', 2)->where('orders.id', '!=', $o->id)
                        ->where('services.category_id', $categoryId)->exists();
                    if ($hadOrders && !$hadCategory) $this->award($bid, 'c_new_category', $base);
                }
            }

            // ── Referral HP on first lifetime transactions (PDF §8, §18) ──
            $buyer  = DB::table('users')->where('id', $bid)->select('id', 'referred_by')->first();
            $seller = DB::table('users')->where('id', $sid)->select('id', 'referred_by')->first();

            if ($buyer && $buyer->referred_by && DB::table('orders')->where('buyer_id', $bid)->where('status', 2)->count() === 1) {
                $ref = DB::table('users')->where('id', $buyer->referred_by)->select('id', 'user_type')->first();
                if ($ref) $this->award($ref->id, $this->leagueOfUser($ref) === 'provider' ? 'p_ref_client_first_txn' : 'c_ref_client_first_booking',
                    ['source_type' => 'referral_order', 'source_id' => $o->id]);
            }
            if ($seller && $seller->referred_by && DB::table('orders')->where('seller_id', $sid)->where('status', 2)->count() === 1) {
                $ref = DB::table('users')->where('id', $seller->referred_by)->select('id', 'user_type')->first();
                if ($ref) $this->award($ref->id, $this->leagueOfUser($ref) === 'provider' ? 'p_ref_provider_first_booking' : 'c_ref_provider_first_service',
                    ['source_type' => 'referral_order', 'source_id' => $o->id]);
            }
        } catch (\Throwable $e) {
            \Log::warning('[Champions] onOrderCompleted failed: ' . $e->getMessage(), ['order_id' => $orderId]);
        }
    }

    /** Refund / cancellation after completion: reverse every HP row tied to the order. */
    public function onOrderRefunded(int $orderId, string $reason = 'Order refunded or cancelled'): void
    {
        try {
            $this->reverseBySource('order', $orderId, $reason);
            $this->reverseBySource('referral_order', $orderId, $reason);
        } catch (\Throwable $e) {
            \Log::warning('[Champions] onOrderRefunded failed: ' . $e->getMessage(), ['order_id' => $orderId]);
        }
    }

    /** Buyer reviewed a seller for a completed order (PDF §6, §17). */
    public function onReviewCreated(int $reviewId): void
    {
        try {
            $r = DB::table('reviews')->where('id', $reviewId)->first();
            if (!$r || (int) ($r->type ?? 1) !== 1 || empty($r->buyer_id) || empty($r->seller_id)) return;
            if ((int) $r->buyer_id === (int) $r->seller_id) return;

            // "Verified" review = tied to a completed order between the two
            $verified = !empty($r->order_id) && DB::table('orders')->where('id', $r->order_id)
                ->where('buyer_id', $r->buyer_id)->where('seller_id', $r->seller_id)->where('status', 2)->exists();
            if (!$verified) return;

            $opt = ['source_type' => 'review', 'source_id' => $r->id];
            $this->award((int) $r->buyer_id, 'c_review', $opt);
            if (mb_strlen(trim((string) $r->message)) >= 30) $this->award((int) $r->buyer_id, 'c_review_written', $opt);
            $this->award((int) $r->seller_id, 'p_review_received', $opt + ['counterparty_id' => $r->buyer_id]);
        } catch (\Throwable $e) {
            \Log::warning('[Champions] onReviewCreated failed: ' . $e->getMessage(), ['review_id' => $reviewId]);
        }
    }

    /** Account verified (OTP or email) — league picked from user_type. */
    public function onAccountVerified(int $userId): void
    {
        try {
            $u = DB::table('users')->where('id', $userId)->select('id', 'user_type', 'referred_by')->first();
            if (!$u) return;
            $league = $this->leagueOfUser($u);
            $this->award($u->id, $league === 'provider' ? 'p_verify_account' : 'c_verify_account', ['source_type' => 'user', 'source_id' => $u->id]);

            // Referrer earns when a referred CLIENT verifies (PDF §18)
            if ($league === 'client' && $u->referred_by) {
                $ref = DB::table('users')->where('id', $u->referred_by)->select('id', 'user_type')->first();
                if ($ref && $this->leagueOfUser($ref) === 'client') {
                    $this->award($ref->id, 'c_ref_client_verified', ['source_type' => 'referral_user', 'source_id' => $u->id]);
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('[Champions] onAccountVerified failed: ' . $e->getMessage(), ['user_id' => $userId]);
        }
    }

    /**
     * Profile completeness (PDF §4 provider 80% / 100%, §14 client profile + preferences).
     */
    public function onProfileUpdated(int $userId): void
    {
        try {
            $u = DB::table('users')->where('id', $userId)->first();
            if (!$u) return;
            $filled = fn (array $cols) => collect($cols)->filter(fn ($c) => trim((string) ($u->$c ?? '')) !== '')->count();
            $opt = ['source_type' => 'user', 'source_id' => $u->id];

            if ($this->leagueOfUser($u) === 'provider') {
                $cols = ['name', 'email', 'phone', 'image', 'service_city', 'service_area', 'country_id', 'address', 'about', 'profile_background'];
                $pct  = (int) round($filled($cols) / count($cols) * 100);
                if ($pct >= 80)  $this->award($u->id, 'p_profile_80', $opt);
                if ($pct >= 100) $this->award($u->id, 'p_profile_100', $opt);
            } else {
                if ($filled(['name', 'email', 'phone', 'image', 'address']) === 5) $this->award($u->id, 'c_profile_complete', $opt);
                if ($filled(['service_city', 'service_area']) === 2 || $filled(['country_id', 'state']) === 2) $this->award($u->id, 'c_preferences', $opt);
            }
        } catch (\Throwable $e) {
            \Log::warning('[Champions] onProfileUpdated failed: ' . $e->getMessage(), ['user_id' => $userId]);
        }
    }

    /**
     * Catch-up sweep (every 10 min). Many writers update orders, reviews, services
     * and chats directly (API, admin panel, payment webhooks) — rather than patch
     * each one, scan recent rows. award() is idempotent per source, so re-scans are free.
     */
    public function syncRecent(?Carbon $since = null): array
    {
        $n = ['orders' => 0, 'reviews' => 0, 'users' => 0, 'identity' => 0, 'portfolio' => 0, 'services' => 0, 'proposals' => 0, 'chat' => 0];
        if (!$this->enabled()) return $n;

        $started = get_static_option('champions_started_at');
        $since   = $since ?: now()->subDays(2);
        if ($started && Carbon::parse($started)->gt($since)) $since = Carbon::parse($started);
        $awarded = fn (string $type, int $id) => DB::table('champion_points')->where(['source_type' => $type, 'source_id' => $id])->exists();

        // Completed orders
        foreach (DB::table('orders')->where('status', 2)->where('updated_at', '>=', $since)->pluck('id') as $id) {
            if ($awarded('order', $id)) continue;
            $this->onOrderCompleted((int) $id); $n['orders']++;
        }

        // Verified client reviews
        foreach (DB::table('reviews')->where('type', 1)->where('created_at', '>=', $since)->pluck('id') as $id) {
            if ($awarded('review', $id)) continue;
            $this->onReviewCreated((int) $id); $n['reviews']++;
        }

        // Account verification + profile completeness
        foreach (DB::table('users')->where('updated_at', '>=', $since)->select('id', 'otp_verified', 'email_verified')->get() as $u) {
            if ((int) $u->otp_verified === 1 || (string) $u->email_verified === '1') $this->onAccountVerified((int) $u->id);
            $this->onProfileUpdated((int) $u->id); $n['users']++;
        }

        // Identity verified by admin (PDF §4)
        foreach (DB::table('seller_verifies')->where('status', 1)->where('updated_at', '>=', $since)->pluck('seller_id') as $sid) {
            if ($this->award((int) $sid, 'p_identity_verified', ['source_type' => 'seller_verify', 'source_id' => (int) $sid])) $n['identity']++;
        }

        // Portfolio items (max 5)
        foreach (DB::table('portfolios')->where('created_at', '>=', $since)->select('id', 'freelancer_id')->get() as $p) {
            if ($this->award((int) $p->freelancer_id, 'p_portfolio_item', ['source_type' => 'portfolio', 'source_id' => $p->id])) $n['portfolio']++;
        }

        // Approved services (max 3) + complete pricing (max 3)
        foreach (DB::table('services')->where('status', 1)->where('updated_at', '>=', $since)
                     ->select('id', 'seller_id', 'price', 'delivery_days', 'category_id', 'service_city_id')->get() as $s) {
            if (!$s->seller_id) continue;
            $opt = ['source_type' => 'service', 'source_id' => $s->id];
            if ($this->award((int) $s->seller_id, 'p_service_published', $opt)) $n['services']++;
            if ((float) $s->price > 0 && (int) $s->delivery_days > 0) $this->award((int) $s->seller_id, 'p_service_pricing', $opt);
        }

        // Proposal accepted — only proposals that actually turned into an order
        // (one hire path flags every proposal on the job as hired).
        $hired = DB::table('job_requests as jr')
            ->join('orders as o', fn ($j) => $j->on('o.job_post_id', '=', 'jr.job_post_id')->on('o.seller_id', '=', 'jr.seller_id'))
            ->where('jr.is_hired', 1)->where('jr.updated_at', '>=', $since)
            ->select('jr.id', 'jr.seller_id', 'jr.buyer_id')->distinct()->get();
        foreach ($hired as $jr) {
            if ($this->award((int) $jr->seller_id, 'p_proposal_accepted', ['source_type' => 'job_request', 'source_id' => $jr->id, 'counterparty_id' => $jr->buyer_id])) $n['proposals']++;
        }

        // Provider replies to client enquiries (+5, +10 if within 30 min; shared 250 HP cap)
        $replies = DB::table('live_chat_messages')->whereColumn('from_user', 'seller_id')
            ->whereNotNull('buyer_id')->where('created_at', '>=', $since)
            ->select('id', 'seller_id', 'buyer_id', 'created_at')->orderBy('id')->get();
        foreach ($replies as $m) {
            if ((int) $m->seller_id === (int) $m->buyer_id) continue;
            $prev = DB::table('live_chat_messages')->where('seller_id', $m->seller_id)->where('buyer_id', $m->buyer_id)
                ->where('id', '<', $m->id)->orderByDesc('id')->select('from_user', 'created_at')->first();
            if (!$prev || (int) $prev->from_user !== (int) $m->buyer_id) continue; // only the first reply to a client message
            $opt = ['source_type' => 'chat_message', 'source_id' => $m->id, 'counterparty_id' => $m->buyer_id];
            if ($this->award((int) $m->seller_id, 'p_enquiry_response', $opt)) $n['chat']++;
            if (Carbon::parse($prev->created_at)->diffInMinutes(Carbon::parse($m->created_at)) <= 30) {
                $this->award((int) $m->seller_id, 'p_fast_response', $opt);
            }
        }

        return $n;
    }

    /** Share of client messages a provider answered in [from, to] (PDF §5 response-rate bonuses). */
    public function responseRate(int $sellerId, Carbon $from, Carbon $to): ?float
    {
        $msgs = DB::table('live_chat_messages')->where('seller_id', $sellerId)
            ->whereBetween('created_at', [$from, $to])->orderBy('buyer_id')->orderBy('id')
            ->select('buyer_id', 'from_user')->get()->groupBy('buyer_id');
        $threads = 0; $answered = 0;
        foreach ($msgs as $buyerId => $rows) {
            if (!$rows->contains(fn ($r) => (int) $r->from_user === (int) $buyerId)) continue;
            $threads++;
            if ($rows->contains(fn ($r) => (int) $r->from_user === $sellerId)) $answered++;
        }
        return $threads >= 3 ? $answered / $threads : null;
    }

    /* =================================================================
     |  Missions (PDF §24, §25)
     | ================================================================= */

    /**
     * Map a rule to the mission counter it advances. A mission with
     * mission_key = 'completed_services' progresses on every p_service_completed.
     */
    public const MISSION_COUNTERS = [
        'p_service_completed'   => 'completed_services',
        'p_repeat_client'       => 'repeat_bookings',
        'p_portfolio_item'      => 'portfolio_items',
        'p_fast_response'       => 'fast_responses',
        'p_proposal_sent'       => 'proposals_sent',
        'c_first_booking_month' => 'completed_bookings',
        'c_additional_booking'  => 'completed_bookings',
        'c_review'              => 'reviews',
        'c_new_category'        => 'new_categories',
        'c_rebook_provider'     => 'repeat_bookings',
        'c_request_created'     => 'requests_created',
    ];

    protected function advanceMissions(int $userId, string $league, string $ruleKey, string $season): void
    {
        $counter = self::MISSION_COUNTERS[$ruleKey] ?? null;
        if (!$counter) return;

        $missions = DB::table('champion_missions')
            ->where('league', $league)->where('type', 'mission')->where('is_active', 1)
            ->where('mission_key', $counter)
            ->where(fn ($q) => $q->whereNull('season_key')->orWhere('season_key', $season))
            ->get();

        foreach ($missions as $m) {
            $p = DB::table('champion_mission_progress')
                ->where(['mission_id' => $m->id, 'user_id' => $userId, 'season_key' => $season])->first();
            if ($p && $p->completed_at) continue;

            $progress = ($p->progress ?? 0) + 1;
            $done     = $progress >= $m->target;

            DB::table('champion_mission_progress')->updateOrInsert(
                ['mission_id' => $m->id, 'user_id' => $userId, 'season_key' => $season],
                ['progress' => $progress, 'completed_at' => $done ? now() : null, 'updated_at' => now(), 'created_at' => $p->created_at ?? now()]
            );

            if ($done && $m->reward_hp > 0) {
                $this->award($userId, 'mission_complete', [
                    'league' => $league, 'hp' => (int) $m->reward_hp,
                    'source_type' => 'mission', 'source_id' => $m->id,
                    'reason' => 'Mission: ' . $m->title, 'season_key' => $season,
                ]);
            }
        }
    }

    protected function demandBonus(string $league, string $season, $cityId, $categoryId, int $hp): int
    {
        $pct = (int) DB::table('champion_missions')
            ->where('league', $league)->where('type', 'demand_bonus')->where('is_active', 1)
            ->where(fn ($q) => $q->whereNull('season_key')->orWhere('season_key', $season))
            ->where(function ($q) use ($cityId, $categoryId) {
                $q->when($cityId, fn ($qq) => $qq->orWhere('city_id', $cityId))
                  ->when($categoryId, fn ($qq) => $qq->orWhere('category_id', $categoryId));
            })
            ->max('bonus_percent');
        return $pct > 0 ? (int) round($hp * $pct / 100) : 0;
    }

    /* =================================================================
     |  Reading: totals, levels, leaderboard, rank
     | ================================================================= */

    public function userTotals(int $userId, string $league, ?string $season = null): array
    {
        $season = $season ?: $this->currentSeasonKey();
        $q = DB::table('champion_points')->where(['user_id' => $userId, 'league' => $league, 'season_key' => $season]);
        return [
            'confirmed' => (int) (clone $q)->where('status', 'confirmed')->sum('points'),
            'pending'   => (int) (clone $q)->where('status', 'pending')->sum('points'),
            'reversed'  => (int) (clone $q)->where('status', 'reversed')->sum('points'),
            'lifetime'  => (int) DB::table('champion_points')->where(['user_id' => $userId, 'league' => $league, 'status' => 'confirmed'])->sum('points'),
        ];
    }

    public function levelFor(string $league, int $hp): array
    {
        $current = null; $next = null;
        foreach (self::LEVELS[$league] as $min => $name) {
            if ($hp >= $min) $current = ['name' => $name, 'min' => $min];
            elseif ($next === null) $next = ['name' => $name, 'min' => $min];
        }
        return [
            'current'   => $current,
            'next'      => $next,
            'remaining' => $next ? $next['min'] - $hp : 0,
        ];
    }

    /**
     * Ranked league table for a season (confirmed HP only), with the PDF §28
     * tie-breakers. Cached 15 minutes (PDF §27: updates every 15–60 min).
     */
    /**
     * @param string $period  month = official season board (confirmed HP only);
     *                        week  = HP earned this week (Mon–Sun EAT, pending + confirmed);
     *                        year  = confirmed HP across all seasons of the current year.
     */
    public function leaderboard(string $league, ?string $season = null, int $limit = 100, string $period = 'month')
    {
        $season = $season ?: $this->currentSeasonKey();
        $period = in_array($period, ['week', 'year'], true) ? $period : 'month';
        $now    = $this->now();
        $cacheKey = $period === 'month'
            ? "champ_board_{$league}_{$season}_{$limit}"
            : "champ_board_{$league}_{$period}_" . $now->format($period === 'week' ? 'o\WW' : 'Y') . "_{$limit}";

        return Cache::remember($cacheKey, now()->addMinutes(15),
            function () use ($league, $season, $limit, $period, $now) {
                $userCol = $league === 'provider' ? 'seller_id' : 'buyer_id';
                $tz = config('app.timezone');
                if ($period === 'week') {
                    $from = $now->copy()->startOfWeek()->setTimezone($tz)->toDateTimeString();
                    $to   = $now->copy()->endOfWeek()->setTimezone($tz)->toDateTimeString();
                } elseif ($period === 'year') {
                    $from = $now->copy()->startOfYear()->setTimezone($tz)->toDateTimeString();
                    $to   = $now->copy()->endOfYear()->setTimezone($tz)->toDateTimeString();
                } else {
                    [$from, $to] = $this->seasonRangeUtc($season);
                }

                $q = DB::table('champion_points as cp')
                    ->join('users as u', 'u.id', '=', 'cp.user_id')
                    ->leftJoin('champion_disqualifications as dq', function ($j) use ($league) {
                        $j->on('dq.user_id', '=', 'cp.user_id')->on('dq.season_key', '=', 'cp.season_key')->where('dq.league', $league);
                    })
                    ->whereNull('dq.id')->where('cp.league', $league);

                if ($period === 'week') {
                    $q->whereIn('cp.status', ['pending', 'confirmed'])->whereBetween('cp.created_at', [$from, $to]);
                } elseif ($period === 'year') {
                    $q->where('cp.status', 'confirmed')->where('cp.season_key', 'like', $now->format('Y') . '-%');
                } else {
                    $q->where('cp.status', 'confirmed')->where('cp.season_key', $season);
                }

                $rows = $q
                    ->groupBy('cp.user_id', 'u.name', 'u.username', 'u.image')
                    ->havingRaw('SUM(cp.points) > 0')
                    ->select('cp.user_id', 'u.name', 'u.username', 'u.image',
                        DB::raw('SUM(cp.points) as hp'),
                        DB::raw('MAX(cp.confirmed_at) as reached_at'),
                        DB::raw("(SELECT COUNT(*) FROM orders o WHERE o.{$userCol} = cp.user_id AND o.status = 2 AND o.updated_at BETWEEN '{$from}' AND '{$to}') as completed"),
                        DB::raw("(SELECT COUNT(*) FROM orders o WHERE o.{$userCol} = cp.user_id AND o.status = 4 AND o.updated_at BETWEEN '{$from}' AND '{$to}') as cancelled"),
                        DB::raw("(SELECT COALESCE(AVG(r.rating),0) FROM reviews r WHERE r.seller_id = cp.user_id) as rating"),
                        // Client tie-breakers (PDF §28): repeat bookings and verified reviews written
                        // providers this client booked more than once (no derived table: MariaDB 10.4 can't see cp there)
                        DB::raw("(SELECT COUNT(DISTINCT o2.seller_id) FROM orders o2 WHERE o2.buyer_id = cp.user_id AND o2.status = 2 AND o2.updated_at BETWEEN '{$from}' AND '{$to}'
                                  AND EXISTS (SELECT 1 FROM orders o3 WHERE o3.buyer_id = o2.buyer_id AND o3.seller_id = o2.seller_id AND o3.status = 2 AND o3.id <> o2.id AND o3.updated_at BETWEEN '{$from}' AND '{$to}')) as repeat_bookings"),
                        DB::raw("(SELECT COUNT(*) FROM reviews r2 WHERE r2.buyer_id = cp.user_id AND r2.type = 1 AND r2.created_at BETWEEN '{$from}' AND '{$to}') as reviews_written"))
                    ->get();

                // PDF §28 tie-breaks — different per league.
                //   Providers: completed bookings ↓, completion rate ↓, verified rating ↓, cancellations ↑
                //   Clients:   completed bookings ↓, repeat bookings ↓, verified reviews ↓, cancellations ↑
                // Both finish with whoever reached the total first.
                $rows = $rows->sort(function ($a, $b) use ($league) {
                    $rate = fn ($r) => ($r->completed + $r->cancelled) ? $r->completed / ($r->completed + $r->cancelled) : 0;
                    if ($league === 'client') {
                        return [$b->hp, $b->completed, $b->repeat_bookings, $b->reviews_written, $a->cancelled, $a->reached_at]
                           <=> [$a->hp, $a->completed, $a->repeat_bookings, $a->reviews_written, $b->cancelled, $b->reached_at];
                    }
                    return [$b->hp, $b->completed, $rate($b), $b->rating, $a->cancelled, $a->reached_at]
                       <=> [$a->hp, $a->completed, $rate($a), $a->rating, $b->cancelled, $b->reached_at];
                })->values()->take($limit);

                return $rows->map(function ($r, $i) use ($league) {
                    $r->rank = $i + 1;
                    $r->display_name = $league === 'client' ? $this->anonymize($r->name) : $r->name;
                    return $r;
                });
            });
    }

    /** Personal position: rank, HP, gap to Top 5 / Top 10. */
    public function position(int $userId, string $league, ?string $season = null, string $period = 'month'): array
    {
        $board = $this->leaderboard($league, $season, 100000, $period);
        $me    = $board->firstWhere('user_id', $userId);
        $hpAt  = fn ($r) => optional($board->get($r - 1))->hp;

        return [
            'rank'         => $me->rank ?? null,
            'hp'           => (int) ($me->hp ?? 0),
            'participants' => $board->count(),
            'to_top5'      => ($me && $me->rank > 5 && $hpAt(5) !== null) ? max(1, $hpAt(5) - $me->hp + 1) : 0,
            'to_top10'     => ($me && $me->rank > 10 && $hpAt(10) !== null) ? max(1, $hpAt(10) - $me->hp + 1) : 0,
        ];
    }

    /** "Sarah Mwakalinga" → "Sarah M." (PDF §22 privacy). */
    public function anonymize(?string $name): string
    {
        $parts = preg_split('/\s+/', trim((string) $name));
        if (!$parts || $parts[0] === '') return __('Client');
        return count($parts) > 1 ? $parts[0] . ' ' . mb_strtoupper(mb_substr(end($parts), 0, 1)) . '.' : $parts[0];
    }

    public function seasonRangeUtc(string $season): array
    {
        $start = Carbon::createFromFormat('Y-m-d H:i:s', $season . '-01 00:00:00', self::TZ);
        $tz    = config('app.timezone');
        return [$start->copy()->setTimezone($tz)->toDateTimeString(), $start->copy()->endOfMonth()->setTimezone($tz)->toDateTimeString()];
    }

    public function forgetBoard(string $league, string $season): void
    {
        foreach ([5, 10, 20, 30, 100, 100000] as $l) Cache::forget("champ_board_{$league}_{$season}_{$l}");
    }
}
