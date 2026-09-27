<?php

namespace App\Http\Controllers;

use App\Services\ChampionsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Admin tools for HUDUMA CHAMPIONS (PDF implementation checklist):
 *   - season overview with Top 20 per league for manual review (§30)
 *   - approve / disqualify provisional winners, mark rewards paid (§34)
 *   - manual HP adjustment with audit note
 *   - disqualify a user from a season (§29)
 *   - rotating missions + demand-balancing bonuses (§24, §25)
 */
class ChampionsAdminController extends Controller
{
    public function __construct(protected ChampionsService $svc) {}

    public function index(Request $request)
    {
        $season = preg_match('/^\d{4}-\d{2}$/', (string) $request->query('season')) ? $request->query('season') : $this->svc->currentSeasonKey();
        $this->svc->season($season);

        $winners = DB::table('champion_winners as w')->join('users as u', 'u.id', '=', 'w.user_id')
            ->where('w.season_key', $season)->select('w.*', 'u.name', 'u.email')
            ->orderBy('w.league')->orderBy('w.rank')->get();

        $stats = [
            'confirmed' => (int) DB::table('champion_points')->where('season_key', $season)->where('status', 'confirmed')->sum('points'),
            'pending'   => (int) DB::table('champion_points')->where('season_key', $season)->where('status', 'pending')->sum('points'),
            'reversed'  => (int) DB::table('champion_points')->where('season_key', $season)->where('status', 'reversed')->count(),
            'players'   => (int) DB::table('champion_points')->where('season_key', $season)->distinct()->count('user_id'),
            'dq'        => (int) DB::table('champion_disqualifications')->where('season_key', $season)->count(),
        ];

        // Whole boards, shown 20 at a time (the PDF's manual review is the first 20)
        $providerBoard = $this->svc->leaderboard('provider', $season, 1000);
        $clientBoard   = $this->svc->leaderboard('client', $season, 1000);
        $provider = $this->paginate($providerBoard, 20, 'provider_page', $request);
        $client   = $this->paginate($clientBoard, 20, 'client_page', $request);

        // PDF §29/§30 — risk signals, for the rows on screen only
        $risk = [];
        foreach (['provider' => $provider, 'client' => $client] as $lg => $rows) {
            foreach ($rows as $r) $risk[$lg][$r->user_id] = $this->svc->riskFlags((int) $r->user_id, $lg, $season);
        }

        // PDF §7 / §19 — month-end bonus preview, calculated for the visible page
        $quality = $this->paginate($providerBoard, 10, 'quality_page', $request);
        $quality->setCollection($quality->getCollection()->map(fn ($r) => (object) ([
            'user_id' => $r->user_id, 'name' => $r->name,
        ] + $this->svc->providerQuality((int) $r->user_id, $season))));

        $loyalty = $this->paginate($clientBoard, 10, 'loyalty_page', $request);
        $loyalty->setCollection($loyalty->getCollection()->map(fn ($r) => (object) ([
            'user_id' => $r->user_id, 'name' => $r->display_name,
        ] + $this->svc->clientLoyalty((int) $r->user_id, $season))));

        return view('backend.champions.index', [
            'season'    => $season,
            'seasonRow' => DB::table('champion_seasons')->where('season_key', $season)->first(),
            'seasons'   => DB::table('champion_seasons')->orderByDesc('season_key')->pluck('season_key'),
            'provider'  => $provider,
            'client'    => $client,
            'risk'      => $risk,
            'quality'   => $quality,
            'loyalty'   => $loyalty,
            'winners'   => $winners,
            'stats'     => $stats,
        ]);
    }

    /** Page a collection that was built in memory (the cached leaderboards). */
    protected function paginate($items, int $perPage, string $pageName, Request $request): \Illuminate\Pagination\LengthAwarePaginator
    {
        $items = collect($items)->values();
        $page  = max(1, (int) $request->query($pageName, 1));

        return (new \Illuminate\Pagination\LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(), $items->count(), $perPage, $page,
            ['path' => $request->url(), 'pageName' => $pageName]
        ))->withQueryString();
    }

    public function userLedger(Request $request, int $userId)
    {
        $season = $request->query('season', $this->svc->currentSeasonKey());
        $user   = DB::table('users')->where('id', $userId)->first();
        return view('backend.champions.ledger', [
            'user'   => $user,
            'season' => $season,
            'flags'  => $user ? $this->svc->riskFlags($userId, (int) $user->user_type === 0 ? 'provider' : 'client', $season) : [],
            'rows'   => DB::table('champion_points')->where('user_id', $userId)->where('season_key', $season)->orderByDesc('id')->paginate(50),
        ]);
    }

    public function adjust(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'league'  => 'required|in:provider,client',
            'points'  => 'required|integer|not_in:0|between:-100000,100000',
            'reason'  => 'required|string|max:255',
        ]);
        $this->svc->award((int) $data['user_id'], 'admin_adjustment', [
            'league' => $data['league'], 'hp' => (int) $data['points'],
            'source_type' => 'admin', 'source_id' => Auth::guard('admin')->id(),
            'admin_id' => Auth::guard('admin')->id(), 'reason' => 'Admin: ' . $data['reason'],
        ]);
        return back()->with('success', __('Points adjusted.'));
    }

    /** The deductions from PDF §9 (providers) and §20 (clients), applied by an admin. */
    public const PENALTIES = [
        'p_cancel_no_reason' => 'Provider-caused cancellation without valid reason (-100)',
        'p_slow_responses'   => 'Repeated slow or missing responses (-50)',
        'p_fake_listing'     => 'Confirmed fake listing (-500)',
        'p_fake_review'      => 'Confirmed fake review by a provider (-500)',
        'p_policy_violation' => 'Serious policy violation (-500)',
        'c_fake_request'     => 'Fake service request (-100)',
        'c_repeated_cancel'  => 'Repeated client-caused cancellation (-75)',
        'c_fake_review'      => 'Fake review by a client (-500)',
    ];

    /** Apply one of those penalties with its set amount, so admins never type the number. */
    public function penalty(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'rule'    => 'required|in:' . implode(',', array_keys(self::PENALTIES)),
            'reason'  => 'required|string|max:255',
        ]);

        $id = $this->svc->award((int) $data['user_id'], $data['rule'], [
            'source_type' => 'admin', 'source_id' => Auth::guard('admin')->id(),
            'admin_id'    => Auth::guard('admin')->id(),
            'reason'      => ChampionsService::RULES[$data['rule']]['label'] . ' — ' . $data['reason'],
        ]);

        return back()->with($id ? 'success' : 'warning', $id
            ? __('Penalty applied: :hp HP.', ['hp' => ChampionsService::RULES[$data['rule']]['hp']])
            : __('Nothing applied — that penalty is already recorded for this user.'));
    }

    public function reversePoint(Request $request, int $id)
    {
        $row = DB::table('champion_points')->where('id', $id)->first();
        abort_unless($row, 404);
        DB::table('champion_points')->where('id', $id)->update([
            'status' => 'reversed', 'reversed_at' => now(), 'updated_at' => now(),
            'reversal_reason' => 'Admin: ' . $request->input('reason', 'invalid activity'),
        ]);
        $this->svc->forgetBoard($row->league, $row->season_key);
        return back()->with('success', __('Point entry reversed.'));
    }

    public function disqualify(Request $request)
    {
        $data = $request->validate([
            'user_id'    => 'required|integer|exists:users,id',
            'league'     => 'required|in:provider,client',
            'season_key' => 'required|regex:/^\d{4}-\d{2}$/',
            'reason'     => 'required|string|max:255',
        ]);
        DB::table('champion_disqualifications')->updateOrInsert(
            ['user_id' => $data['user_id'], 'season_key' => $data['season_key'], 'league' => $data['league']],
            ['reason' => $data['reason'], 'admin_id' => Auth::guard('admin')->id(), 'updated_at' => now(), 'created_at' => now()]
        );
        DB::table('champion_winners')->where(['user_id' => $data['user_id'], 'season_key' => $data['season_key'], 'league' => $data['league']])
            ->update(['status' => 'disqualified', 'audit_notes' => $data['reason'], 'updated_at' => now()]);
        $this->svc->forgetBoard($data['league'], $data['season_key']);
        return back()->with('success', __('User disqualified from this season.'));
    }

    public function winnerStatus(Request $request, int $id)
    {
        $data = $request->validate([
            'status'      => 'required|in:approved,disqualified,paid',
            'audit_notes' => 'nullable|string|max:2000',
        ]);
        $w = DB::table('champion_winners')->where('id', $id)->first();
        abort_unless($w, 404);

        $update = ['status' => $data['status'], 'audit_notes' => $data['audit_notes'] ?? $w->audit_notes, 'updated_at' => now()];
        if ($data['status'] === 'approved') { $update['approved_by'] = Auth::guard('admin')->id(); $update['approved_at'] = now(); }
        if ($data['status'] === 'paid')     { $update['paid_at'] = now(); }
        DB::table('champion_winners')->where('id', $id)->update($update);

        if (in_array($data['status'], ['approved', 'paid'])) {
            $this->grantWinnerBadge($w);
            if ($data['status'] === 'paid' && $w->reward_type === 'credit') $this->creditWallet($w);
        }
        return back()->with('success', __('Winner updated.'));
    }

    /** Mark season announced (PDF §34, day 5) and notify winners. */
    public function announce(string $season)
    {
        $winners = DB::table('champion_winners')->where('season_key', $season)->whereIn('status', ['approved', 'paid'])->get();
        foreach ($winners as $w) {
            $label = \Carbon\Carbon::createFromFormat('Y-m', $season)->format('F Y');
            $league = $w->league === 'provider' ? __('Pro League') : __('Client League');
            if (function_exists('notifySeller')) {
                notifySeller($w->user_id,
                    __('Hongera! You finished #:rank in the :league — :season.', ['rank' => $w->rank, 'league' => $league, 'season' => $label]),
                    __('Hongera! Umeshika nafasi ya :rank kwenye :league — :season. / Congratulations, you are Top Five in Huduma Champions.', ['rank' => $w->rank, 'league' => $league, 'season' => $label]),
                    ['type' => 'gernalnotifications', 'details' => 'Huduma Champions Top Five', 'event' => 'champions_winner']);
            }
        }
        DB::table('champion_seasons')->where('season_key', $season)->update(['status' => 'announced', 'announced_at' => now(), 'updated_at' => now()]);
        return back()->with('success', __('Season announced — :n winners notified.', ['n' => $winners->count()]));
    }

    /**
     * Undo a finalized/announced season so it can be judged again — for testing,
     * or when a season was finalized too early. Points, penalties and
     * disqualifications are untouched; only the Top Five result is cleared.
     */
    public function resetSeason(Request $request)
    {
        $data = $request->validate([
            'season_key' => 'required|regex:/^\d{4}-\d{2}$/',
            'confirm'    => 'required|in:RESET',
        ]);
        $season  = $data['season_key'];
        $winners = DB::table('champion_winners')->where('season_key', $season)->get();

        // Credits already on a wallet are real money to the user — say so, do not claw back
        $paidCredit = $winners->where('status', 'paid')->where('reward_type', 'credit')->sum('reward_amount');

        DB::table('champion_badges')->where('season_key', $season)
            ->whereIn('badge_key', ['champion', 'runner_up', 'top_five'])->delete();
        DB::table('champion_winners')->where('season_key', $season)->delete();
        DB::table('champion_seasons')->where('season_key', $season)
            ->update(['status' => 'open', 'finalized_at' => null, 'announced_at' => null, 'updated_at' => now()]);

        foreach (['provider', 'client'] as $league) $this->svc->forgetBoard($league, $season);
        Log::info("[Champions] {$season} reset by admin " . (Auth::guard('admin')->id() ?: '?') . " — {$winners->count()} winner rows removed.");

        $msg = __('Season :s reset — :n winner rows and their badges removed. Run Finalize again to judge it.', ['s' => $season, 'n' => $winners->count()]);
        if ($paidCredit > 0) {
            $msg .= ' ' . __('Note: TZS :amt of service credit was already on wallets and has been left there.', ['amt' => number_format($paidCredit)]);
        }
        return back()->with('success', $msg);
    }

    public function runJob(Request $request)
    {
        $data = $request->validate(['action' => 'required|in:confirm,sync,bonuses,finalize', 'season_key' => 'nullable|regex:/^\d{4}-\d{2}$/']);
        $args = ['action' => $data['action']];
        if (!empty($data['season_key'])) $args['--season'] = $data['season_key'];
        Artisan::call('champions:season', $args);
        return back()->with('success', trim(Artisan::output()) ?: __('Job ran.'));
    }

    public function missionStore(Request $request)
    {
        $data = $request->validate([
            'league'        => 'required|in:provider,client',
            'type'          => 'required|in:mission,demand_bonus',
            'mission_key'   => 'required|string|max:64',
            'title'         => 'required|string|max:160',
            'description'   => 'nullable|string|max:255',
            'target'        => 'nullable|integer|min:1|max:1000',
            'reward_hp'     => 'nullable|integer|min:0|max:10000',
            'bonus_percent' => 'nullable|integer|min:1|max:200',
            'city_id'       => 'nullable|integer',
            'category_id'   => 'nullable|integer',
            'season_key'    => 'nullable|regex:/^\d{4}-\d{2}$/',
        ]);
        DB::table('champion_missions')->insert($data + ['target' => $data['target'] ?? 1, 'reward_hp' => $data['reward_hp'] ?? 0, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]);
        return back()->with('success', __('Mission created.'));
    }

    /**
     * PDF §38 — judge the program by marketplace outcomes, not HP issued.
     * Shows each KPI for the chosen season next to the previous season.
     */
    public function analytics(Request $request)
    {
        $season = preg_match('/^\d{4}-\d{2}$/', (string) $request->query('season')) ? $request->query('season') : $this->svc->currentSeasonKey();
        $prev   = \Carbon\Carbon::createFromFormat('Y-m', $season)->subMonthNoOverflow()->format('Y-m');

        $cur  = \Cache::remember("champ_kpi_{$season}", now()->addMinutes(30), fn () => $this->kpis($season));
        $last = \Cache::remember("champ_kpi_{$prev}", now()->addMinutes(30), fn () => $this->kpis($prev));

        return view('backend.champions.analytics', [
            'season'  => $season,
            'prev'    => $prev,
            'cur'     => $cur,
            'last'    => $last,
            'seasons' => DB::table('champion_seasons')->orderByDesc('season_key')->pluck('season_key'),
        ]);
    }

    protected function kpis(string $season): array
    {
        [$from, $to] = $this->svc->seasonRangeUtc($season);
        [$pFrom]     = $this->svc->seasonRangeUtc(\Carbon\Carbon::createFromFormat('Y-m', $season)->subMonthNoOverflow()->format('Y-m'));
        $inRange = fn ($q, $col = 'updated_at') => $q->whereBetween($col, [$from, $to]);

        $completed = $inRange(DB::table('orders')->where('status', 2)->whereColumn('seller_id', '!=', 'buyer_id'));
        $done      = (clone $completed)->count();
        $cancelled = $inRange(DB::table('orders')->where('status', 4))->count();

        // Providers who published their first service this month
        $activation = DB::table('services')->select('seller_id')->groupBy('seller_id')
            ->havingRaw('MIN(created_at) BETWEEN ? AND ?', [$from, $to])->get()->count();

        // Average response rate of providers with 3+ client conversations
        $rates = DB::table('live_chat_messages')->whereBetween('created_at', [$from, $to])->whereNotNull('seller_id')
            ->distinct()->limit(300)->pluck('seller_id')
            ->map(fn ($sid) => $this->svc->responseRate((int) $sid, \Carbon\Carbon::parse($from), \Carbon\Carbon::parse($to)))
            ->filter(fn ($r) => $r !== null);

        // Repeat bookings: completed orders where the client already used this provider before
        $repeat = (clone $completed)->whereExists(function ($q) {
            $q->from('orders as p')->whereColumn('p.buyer_id', 'orders.buyer_id')->whereColumn('p.seller_id', 'orders.seller_id')
              ->where('p.status', 2)->whereColumn('p.id', '<', 'orders.id');
        })->count();

        // 30-day retention: users active last month who are active again this month
        $activeIds = fn ($a, $b) => DB::table('orders')->whereBetween('created_at', [$a, $b])->pluck('buyer_id')
            ->merge(DB::table('orders')->whereBetween('created_at', [$a, $b])->pluck('seller_id'))->filter()->unique();
        $lastActive = $activeIds($pFrom, $from);
        $retained   = $lastActive->count() ? round($lastActive->intersect($activeIds($from, $to))->count() / $lastActive->count() * 100, 1) : null;

        $players = DB::table('champion_points')->where('season_key', $season)->distinct()->count('user_id');
        $dq      = DB::table('champion_disqualifications')->where('season_key', $season)->count();

        return [
            'provider_activation' => $activation,
            'response_rate'       => $rates->count() ? round($rates->avg() * 100, 1) : null,
            'completed'           => $done,
            'bookings'            => $inRange(DB::table('orders')->whereColumn('seller_id', '!=', 'buyer_id'), 'created_at')->count(),
            'repeat'              => $repeat,
            'reviews'             => $inRange(DB::table('reviews')->where('type', 1), 'created_at')->count(),
            'referrals'           => \Schema::hasTable('referrals') ? $inRange(DB::table('referrals')->where('status', 'approved'))->count() : null,
            'retention'           => $retained,
            'cancel_rate'         => ($done + $cancelled) ? round($cancelled / ($done + $cancelled) * 100, 1) : null,
            'fraud_rate'          => $players ? round($dq / $players * 100, 1) : null,
            'gmv'                 => (float) (clone $completed)->sum('total'),
            'revenue'             => (float) (clone $completed)->sum('commission_amount'),
            'players'             => $players,
            'hp_confirmed'        => (int) DB::table('champion_points')->where('season_key', $season)->where('status', 'confirmed')->sum('points'),
        ];
    }

    /** Program settings: minimum qualifying order, pair cap, pending hold, repeat-winner rule. */
    public function settings(Request $request)
    {
        $data = $request->validate([
            'champions_enabled'             => 'nullable|in:0,1',
            'champions_min_order_tzs'       => 'required|numeric|min:0|max:100000000',
            'champions_pair_txn_cap'        => 'required|integer|min:1|max:50',
            'champions_pending_hold_days'   => 'required|integer|min:0|max:60',
            'champions_block_repeat_winner' => 'required|in:0,1',
        ]);
        $data['champions_enabled'] = $data['champions_enabled'] ?? 1;
        foreach ($data as $name => $value) update_static_option($name, $value);
        \Cache::forget('champions_tables_ok');
        return back()->with('success', __('Champions settings saved.'));
    }

    /** The season list every sub-page offers, newest first. */
    protected function seasonList(string $season)
    {
        return DB::table('champion_seasons')->orderByDesc('season_key')->pluck('season_key')->push($season)->unique()->sortDesc()->values();
    }

    protected function seasonFrom(Request $request): string
    {
        return preg_match('/^\d{4}-\d{2}$/', (string) $request->query('season'))
            ? $request->query('season') : $this->svc->currentSeasonKey();
    }

    /** Manual HP adjustments, with what has been adjusted this season. */
    public function adjustPage(Request $request)
    {
        $season = $this->seasonFrom($request);

        return view('backend.champions.adjust', [
            'season'  => $season,
            'seasons' => $this->seasonList($season),
            'rows'    => DB::table('champion_points as p')
                ->leftJoin('users as u', 'u.id', '=', 'p.user_id')
                ->leftJoin('admins as a', 'a.id', '=', 'p.admin_id')
                ->where('p.season_key', $season)->where('p.rule_key', 'admin_adjustment')
                ->select('p.*', 'u.name', 'a.name as admin_name')
                ->orderByDesc('p.id')->paginate(25)->withQueryString(),
        ]);
    }

    /** Set penalties from the rules, with the ones already applied. */
    public function penaltiesPage(Request $request)
    {
        $season = $this->seasonFrom($request);
        $rules  = ChampionsService::RULES;

        return view('backend.champions.penalties', [
            'season'    => $season,
            'seasons'   => $this->seasonList($season),
            'penalties' => self::PENALTIES,
            'amounts'   => collect(self::PENALTIES)->map(fn ($l, $k) => (int) ($rules[$k]['hp'] ?? 0))->all(),
            'rows'      => DB::table('champion_points as p')
                ->leftJoin('users as u', 'u.id', '=', 'p.user_id')
                ->leftJoin('admins as a', 'a.id', '=', 'p.admin_id')
                ->where('p.season_key', $season)->whereIn('p.rule_key', array_keys(self::PENALTIES))
                ->select('p.*', 'u.name', 'a.name as admin_name')
                ->orderByDesc('p.id')->paginate(25)->withQueryString(),
        ]);
    }

    /** Season disqualifications (PDF §29). */
    public function disqualificationsPage(Request $request)
    {
        $season = $this->seasonFrom($request);

        return view('backend.champions.disqualifications', [
            'season'  => $season,
            'seasons' => $this->seasonList($season),
            'rows'    => DB::table('champion_disqualifications as d')
                ->leftJoin('users as u', 'u.id', '=', 'd.user_id')
                ->where('d.season_key', $season)
                ->select('d.*', 'u.name', 'u.email')
                ->orderByDesc('d.id')->paginate(25)->withQueryString(),
        ]);
    }

    /** Rotating missions and demand-balancing bonuses (PDF §24, §25). */
    public function missionsPage(Request $request)
    {
        $season = $this->seasonFrom($request);

        return view('backend.champions.missions', [
            'season'   => $season,
            'missions' => DB::table('champion_missions')->orderByDesc('id')
                ->paginate(15, ['*'], 'missions_page', max(1, (int) $request->query('missions_page', 1)))
                ->withQueryString(),
        ]);
    }

    /** Programme settings and the manual job runner. */
    public function settingsPage(Request $request)
    {
        $season = $this->seasonFrom($request);

        return view('backend.champions.settings', [
            'season'   => $season,
            'settings' => [
                'champions_enabled'             => get_static_option('champions_enabled') ?? 1,
                'champions_min_order_tzs'       => get_static_option('champions_min_order_tzs') ?: 0,
                'champions_pair_txn_cap'        => get_static_option('champions_pair_txn_cap') ?: 2,
                'champions_pending_hold_days'   => get_static_option('champions_pending_hold_days') ?: 14,
                'champions_block_repeat_winner' => get_static_option('champions_block_repeat_winner') ?? 1,
            ],
        ]);
    }

    /** Prizes and the monthly reward budget (PDF §12, §23, §24), on their own page. */
    public function rewards(Request $request)
    {
        $season = preg_match('/^\d{4}-\d{2}$/', (string) $request->query('season')) ? $request->query('season') : $this->svc->currentSeasonKey();

        $committed = DB::table('champion_winners')->where('season_key', $season)
            ->selectRaw('status, COUNT(*) as winners,
                         SUM(CASE WHEN reward_type = "cash" THEN reward_amount ELSE 0 END) as cash,
                         SUM(CASE WHEN reward_type = "credit" THEN reward_amount ELSE 0 END) as credit')
            ->groupBy('status')->orderBy('status')->get();

        return view('backend.champions.rewards', [
            'season'     => $season,
            'seasons'    => DB::table('champion_seasons')->orderByDesc('season_key')->pluck('season_key')->push($season)->unique()->sortDesc(),
            'rewards'    => $this->svc->rewards(),
            'budget'     => $this->svc->rewardBudget(),
            'committed'  => $committed,
            'unpaidCash' => (int) DB::table('champion_winners')->where(['season_key' => $season, 'reward_type' => 'cash', 'status' => 'approved'])->sum('reward_amount'),
        ]);
    }

    /** Save the Top Five prize table (PDF §12, §23) so it is not fixed in code. */
    public function rewardsSave(Request $request)
    {
        $data = $request->validate([
            'rewards'                  => 'required|array',
            'rewards.*.*.type'         => 'required|in:cash,credit',
            'rewards.*.*.amount'       => 'required|numeric|min:0|max:100000000',
            'rewards.*.*.benefits'     => 'nullable|string|max:255',
        ]);

        $table = [];
        foreach (ChampionsService::REWARDS as $league => $ranks) {
            foreach ($ranks as $rank => $default) {
                $row = $data['rewards'][$league][$rank] ?? null;
                if (!is_array($row)) { $table[$league][$rank] = $default; continue; }
                $table[$league][$rank] = [
                    $row['type'],
                    (int) round((float) $row['amount']),
                    trim((string) ($row['benefits'] ?? '')) ?: $default[2],
                ];
            }
        }
        update_static_option('champions_rewards', json_encode($table));

        // Winners already provisional this season should follow the new table
        $season = preg_match('/^\d{4}-\d{2}$/', (string) $request->input('season')) ? $request->input('season') : $this->svc->currentSeasonKey();
        foreach ($table as $league => $ranks) {
            foreach ($ranks as $rank => [$type, $amount, $benefits]) {
                DB::table('champion_winners')
                    ->where(['season_key' => $season, 'league' => $league, 'rank' => $rank, 'status' => 'provisional'])
                    ->update(['reward_type' => $type, 'reward_amount' => $amount, 'benefits' => $benefits, 'updated_at' => now()]);
            }
        }

        return redirect()->route('admin.champions.rewards', ['season' => $season])->with('success', __('Prize table saved.'));
    }

    /** Put the PDF's own amounts back. */
    public function rewardsReset()
    {
        update_static_option('champions_rewards', '');
        return redirect()->route('admin.champions.rewards')->with('success', __('Prize table reset to the programme defaults.'));
    }

    public function missionUpdate(Request $request, int $id)
    {
        $data = $request->validate([
            'title'         => 'required|string|max:160',
            'description'   => 'nullable|string|max:255',
            'mission_key'   => 'required|string|max:64',
            'target'        => 'nullable|integer|min:1|max:1000',
            'reward_hp'     => 'nullable|integer|min:0|max:10000',
            'bonus_percent' => 'nullable|integer|min:1|max:200',
            'city_id'       => 'nullable|integer',
            'category_id'   => 'nullable|integer',
            'season_key'    => 'nullable|regex:/^\d{4}-\d{2}$/',
        ]);
        abort_unless(DB::table('champion_missions')->where('id', $id)->exists(), 404);

        DB::table('champion_missions')->where('id', $id)->update($data + [
            'target' => $data['target'] ?? 1, 'reward_hp' => $data['reward_hp'] ?? 0, 'updated_at' => now(),
        ]);
        return back()->with('success', __('Mission updated.'));
    }

    public function missionDelete(int $id)
    {
        abort_unless(DB::table('champion_missions')->where('id', $id)->exists(), 404);

        // Progress goes with it; points and badges people already earned stay
        DB::table('champion_mission_progress')->where('mission_id', $id)->delete();
        DB::table('champion_missions')->where('id', $id)->delete();

        return back()->with('success', __('Mission deleted. Points already earned from it are kept.'));
    }

    public function missionToggle(int $id)
    {
        DB::table('champion_missions')->where('id', $id)->update(['is_active' => DB::raw('1 - is_active'), 'updated_at' => now()]);
        return back()->with('success', __('Mission updated.'));
    }

    /* ------------------------------------------------------------------ */

    protected function grantWinnerBadge(object $w): void
    {
        $label  = \Carbon\Carbon::createFromFormat('Y-m', $w->season_key)->format('F Y');
        $league = $w->league === 'provider' ? 'Provider' : 'Client';
        [$key, $title] = match ((int) $w->rank) {
            1 => ['champion',  "{$label} {$league} Champion"],
            2 => ['runner_up', "{$label} {$league} Runner-Up"],
            default => ['top_five', "{$label} {$league} Top Five"],
        };
        DB::table('champion_badges')->insertOrIgnore([
            'user_id' => $w->user_id, 'badge_key' => $key, 'label' => $title,
            'season_key' => $w->season_key, 'awarded_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    /** Service/promotional credits land in the main wallet with a clear history label. */
    protected function creditWallet(object $w): void
    {
        $idem = 'Huduma Champions ' . $w->season_key . ' #' . $w->rank . ' ' . $w->league;
        if (DB::table('wallet_histories')->where('buyer_id', $w->user_id)->where('Action', $idem)->exists()) return;

        $wallet = \Modules\Wallet\Entities\Wallet::firstOrCreate(['buyer_id' => $w->user_id], ['balance' => 0, 'status' => 1]);
        $wallet->increment('balance', (float) $w->reward_amount);
        \Modules\Wallet\Entities\WalletHistory::create([
            'buyer_id' => $w->user_id, 'amount' => $w->reward_amount,
            'payment_gateway' => 'Huduma Champions Credit', 'payment_status' => 'complete',
            'status' => 1, 'Action' => $idem,
        ]);
    }
}
