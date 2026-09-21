<?php

namespace App\Http\Controllers;

use App\Services\ChampionsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        $provider = $this->svc->leaderboard('provider', $season, 20);
        $client   = $this->svc->leaderboard('client', $season, 20);

        // PDF §29/§30 — risk signals for the manual Top 20 review
        $risk = [];
        foreach (['provider' => $provider, 'client' => $client] as $lg => $rows) {
            foreach ($rows as $r) $risk[$lg][$r->user_id] = $this->svc->riskFlags((int) $r->user_id, $lg, $season);
        }

        return view('backend.champions.index', [
            'season'    => $season,
            'seasonRow' => DB::table('champion_seasons')->where('season_key', $season)->first(),
            'seasons'   => DB::table('champion_seasons')->orderByDesc('season_key')->pluck('season_key'),
            'provider'  => $provider,
            'client'    => $client,
            'risk'      => $risk,
            'settings'  => [
                'champions_min_order_tzs'      => get_static_option('champions_min_order_tzs') ?: 0,
                'champions_pair_txn_cap'       => get_static_option('champions_pair_txn_cap') ?: 2,
                'champions_pending_hold_days'  => get_static_option('champions_pending_hold_days') ?: 14,
                'champions_block_repeat_winner'=> get_static_option('champions_block_repeat_winner') ?? 1,
            ],
            'winners'   => $winners,
            'stats'     => $stats,
            'missions'  => DB::table('champion_missions')->orderByDesc('id')->get(),
        ]);
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

    public function runJob(Request $request)
    {
        $data = $request->validate(['action' => 'required|in:confirm,bonuses,finalize', 'season_key' => 'nullable|regex:/^\d{4}-\d{2}$/']);
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

    /** Program settings: minimum qualifying order, pair cap, pending hold, repeat-winner rule. */
    public function settings(Request $request)
    {
        $data = $request->validate([
            'champions_min_order_tzs'       => 'required|numeric|min:0|max:100000000',
            'champions_pair_txn_cap'        => 'required|integer|min:1|max:50',
            'champions_pending_hold_days'   => 'required|integer|min:0|max:60',
            'champions_block_repeat_winner' => 'required|in:0,1',
        ]);
        foreach ($data as $name => $value) update_static_option($name, $value);
        return back()->with('success', __('Champions settings saved.'));
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
