<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\ChampionsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * HUDUMA CHAMPIONS — user-facing pages.
 *
 *   GET /champions                 public league boards (Pro / Client)
 *   GET /champions/hall-of-fame    past champions (PDF §35)
 *   GET /champions/rules           official rules (PDF §39, checklist)
 *   GET /seller/champions          provider personal dashboard (PDF §31)
 *   GET /buyer/champions           client personal dashboard (PDF §31)
 */
class ChampionsController extends Controller
{
    public function __construct(protected ChampionsService $svc) {}

    public function board(Request $request)
    {
        $league = $request->query('league') === 'client' ? 'client' : 'provider';
        $season = preg_match('/^\d{4}-\d{2}$/', (string) $request->query('season')) ? $request->query('season') : $this->svc->currentSeasonKey();
        $period = in_array($request->query('period'), ['week', 'year'], true) ? $request->query('period') : 'month';
        $board  = $this->svc->leaderboard($league, $season, 100, $period);

        $me = null;
        if ($user = Auth::guard('web')->user()) {
            $myLeague = $this->leagueFor($user);
            if ($myLeague === $league) $me = $this->svc->position($user->id, $league, $season, $period);
        }

        $now = $this->svc->now();
        return view('frontend.champions.board', [
            'league'      => $league,
            'season'      => $season,
            'period'      => $period,
            'periodLabel' => match ($period) {
                'week'  => __('This week') . ' · ' . $now->copy()->startOfWeek()->format('d M') . ' – ' . $now->copy()->endOfWeek()->format('d M'),
                'year'  => __('Year') . ' ' . $now->format('Y'),
                default => $this->label($season),
            },
            'seasonLabel' => $this->label($season),
            'board'       => $board,
            'me'          => $me,
            'daysLeft'    => $season === $this->svc->currentSeasonKey() ? $this->svc->daysRemaining() : null,
            'seasons'     => DB::table('champion_seasons')->orderByDesc('season_key')->limit(12)->pluck('season_key'),
        ]);
    }

    public function hallOfFame()
    {
        $rows = DB::table('champion_winners as w')
            ->join('users as u', 'u.id', '=', 'w.user_id')
            ->where('w.rank', 1)->whereIn('w.status', ['approved', 'paid'])
            ->select('w.season_key', 'w.league', 'u.name', 'u.username')
            ->orderByDesc('w.season_key')->get()
            ->groupBy('season_key')
            ->map(function ($g, $season) {
                $p = $g->firstWhere('league', 'provider');
                $c = $g->firstWhere('league', 'client');
                return (object) [
                    'season'   => $this->label($season),
                    'provider' => $p->name ?? null,
                    'client'   => $c ? $this->svc->anonymize($c->name) : null,
                ];
            });

        return view('frontend.champions.hall-of-fame', ['rows' => $rows]);
    }

    public function rules()
    {
        return view('frontend.champions.rules', [
            'rules'   => ChampionsService::RULES,
            'caps'    => ChampionsService::CAPS,
            'levels'  => ChampionsService::LEVELS,
            'rewards' => ChampionsService::REWARDS,
        ]);
    }

    public function dashboard(Request $request)
    {
        $user   = Auth::guard('web')->user();
        $league = $this->leagueFor($user);
        $season = $this->svc->currentSeasonKey();

        $totals   = $this->svc->userTotals($user->id, $league, $season);
        $position = $this->svc->position($user->id, $league, $season);
        $level    = $this->svc->levelFor($league, $totals['confirmed']);

        $history = DB::table('champion_points')
            ->where(['user_id' => $user->id, 'league' => $league, 'season_key' => $season])
            ->orderByDesc('id')->paginate(10);

        $missions = DB::table('champion_missions as m')
            ->leftJoin('champion_mission_progress as p', function ($j) use ($user, $season) {
                $j->on('p.mission_id', '=', 'm.id')->where('p.user_id', $user->id)->where('p.season_key', $season);
            })
            ->where('m.league', $league)->where('m.is_active', 1)->where('m.type', 'mission')
            ->where(fn ($q) => $q->whereNull('m.season_key')->orWhere('m.season_key', $season))
            ->select('m.*', DB::raw('COALESCE(p.progress,0) as progress'), 'p.completed_at')
            ->get();

        $badges = DB::table('champion_badges')->where('user_id', $user->id)->orderByDesc('awarded_at')->get();

        return view('frontend.champions.dashboard', [
            'league'      => $league,
            'season'      => $season,
            'seasonLabel' => $this->label($season),
            'totals'      => $totals,
            'position'    => $position,
            'level'       => $level,
            'history'     => $history,
            'missions'    => $missions,
            'badges'      => $badges,
            'daysLeft'    => $this->svc->daysRemaining(),
            'isSeller'    => $league === 'provider',
            'nextAction'  => $league === 'provider'
                ? __('Complete your next service — +150 HP')
                : __('Complete your next booking — +150 HP'),
        ]);
    }

    protected function leagueFor($user): string
    {
        // Sellers are user_type 0 on Huduma Portal; everyone else plays the Client League.
        return (int) $user->user_type === 0 ? 'provider' : 'client';
    }

    protected function label(string $season): string
    {
        return mb_strtoupper(Carbon::createFromFormat('Y-m', $season)->format('F Y'));
    }
}
