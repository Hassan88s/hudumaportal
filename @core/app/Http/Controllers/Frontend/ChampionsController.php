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

    /** PDF §34 — published Top Five per league for a finished season (approved winners only). */
    public function winners(Request $request)
    {
        $published = DB::table('champion_winners')->whereIn('status', ['approved', 'paid'])
            ->distinct()->orderByDesc('season_key')->pluck('season_key');
        $season = preg_match('/^\d{4}-\d{2}$/', (string) $request->query('season')) ? $request->query('season') : $published->first();

        $rows = $season ? DB::table('champion_winners as w')->join('users as u', 'u.id', '=', 'w.user_id')
            ->where('w.season_key', $season)->whereIn('w.status', ['approved', 'paid'])
            ->select('w.league', 'w.rank', 'w.final_hp', 'w.reward_type', 'w.reward_amount', 'u.name', 'u.username', 'u.image')
            ->orderBy('w.rank')->get()
            ->map(function ($r) {
                $r->display_name = $r->league === 'client' ? $this->svc->anonymize($r->name) : $r->name;
                return $r;
            })->groupBy('league') : collect();

        return view('frontend.champions.winners', [
            'season'      => $season,
            'seasonLabel' => $season ? $this->label($season) : null,
            'published'   => $published,
            'provider'    => $rows['provider'] ?? collect(),
            'client'      => $rows['client'] ?? collect(),
        ]);
    }

    /** PDF §12, §23, §31 — what each place wins, levels and permanent badges. */
    public function rewards()
    {
        $user = Auth::guard('web')->user();
        return view('frontend.champions.rewards', [
            'rewards' => $this->svc->rewards(),
            'levels'  => ChampionsService::LEVELS,
            'badges'  => $user ? DB::table('champion_badges')->where('user_id', $user->id)->orderByDesc('awarded_at')->get() : collect(),
        ]);
    }

    public function rules()
    {
        return view('frontend.champions.rules', [
            'rules'   => ChampionsService::RULES,
            'caps'    => ChampionsService::CAPS,
            'levels'  => ChampionsService::LEVELS,
            'rewards' => $this->svc->rewards(),
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
            // PDF §7 / §19 — how this user is doing against the month-end bonuses
            'bonus'       => $league === 'provider'
                ? $this->svc->providerQuality((int) $user->id, $season)
                : $this->svc->clientLoyalty((int) $user->id, $season),
            // PDF §8 / §18 — referral points earned this month, against the 500 HP cap
            'referral'    => [
                'earned' => (int) DB::table('champion_points')
                    ->where(['user_id' => $user->id, 'season_key' => $season, 'cap_group' => 'referral'])
                    ->where('status', '!=', 'reversed')->sum('points'),
                'cap'    => ChampionsService::CAPS['referral'] ?? 500,
                'rows'   => DB::table('champion_points')
                    ->where(['user_id' => $user->id, 'season_key' => $season, 'cap_group' => 'referral'])
                    ->where('status', '!=', 'reversed')
                    ->selectRaw('reason, COUNT(*) as n, SUM(points) as hp')->groupBy('reason')->get(),
            ],
            'onboardingDone' => DB::table('champion_points')
                ->where(['user_id' => $user->id, 'rule_key' => $league === 'provider' ? 'p_onboarding_tutorial' : 'c_onboarding'])
                ->where('status', '!=', 'reversed')->exists(),
            'nextAction'  => $league === 'provider'
                ? __('Complete your next service — +150 HP')
                : __('Complete your next booking — +150 HP'),
        ]);
    }

    /** PDF §4 — short provider onboarding tutorial, +30 HP once when finished. */
    public function onboarding()
    {
        $user = Auth::guard('web')->user();
        abort_unless($this->leagueFor($user) === 'provider', 404);

        $pct = $this->svc->providerProfilePercent($user);
        $steps = [
            ['icon' => 'la-user-edit',   'title' => __('Complete your profile'),
             'text' => __('Add a clear photo, your city and area, and a short "about" so clients trust you. 80% complete earns +50 HP and 100% earns +100 HP.'),
             'done' => $pct >= 80, 'status' => __(':p% complete', ['p' => $pct]),
             'link' => route('seller.profile.edit'), 'cta' => __('Edit profile')],
            ['icon' => 'la-id-card',     'title' => __('Verify your identity'),
             'text' => __('Upload your ID or business details. Verified providers get more bookings and +100 HP.'),
             'done' => (int) DB::table('seller_verifies')->where('seller_id', $user->id)->value('status') === 1,
             'link' => route('seller.profile.verify'), 'cta' => __('Verify now')],
            ['icon' => 'la-briefcase',   'title' => __('Publish a service with full pricing'),
             'text' => __('Describe what you offer, set a price and delivery time. Each approved service earns +75 HP and complete pricing +25 HP (up to 3).'),
             'done' => DB::table('services')->where('seller_id', $user->id)->exists(),
             'link' => route('seller.add.services'), 'cta' => __('Add a service')],
            ['icon' => 'la-images',      'title' => __('Show your past work'),
             'text' => __('Add photos or videos of real jobs to your portfolio. Each item earns +20 HP (up to 5).'),
             'done' => DB::table('portfolios')->where('freelancer_id', $user->id)->exists(),
             'link' => route('seller.portfolio.create'), 'cta' => __('Add portfolio item')],
            ['icon' => 'la-trophy',      'title' => __('Win clients and climb the Pro League'),
             'text' => __('Reply to enquiries fast (+10 HP within 30 minutes), complete jobs (+150 HP each) and ask happy clients for reviews (+25 HP). The Top Five win cash and promotion every month.'),
             'done' => null,
             'link' => \Route::has('champions.rules') ? route('champions.rules') : route('seller.champions'), 'cta' => __('See all ways to earn')],
        ];

        return view('frontend.champions.onboarding', [
            'steps'    => $steps,
            'finished' => DB::table('champion_points')->where(['user_id' => $user->id, 'rule_key' => 'p_onboarding_tutorial'])
                            ->where('status', '!=', 'reversed')->exists(),
        ]);
    }

    public function onboardingComplete()
    {
        $user = Auth::guard('web')->user();
        abort_unless($this->leagueFor($user) === 'provider', 404);

        $id = $this->svc->award((int) $user->id, 'p_onboarding_tutorial', ['source_type' => 'tutorial', 'source_id' => (int) $user->id]);
        toastr_success($id ? __('Tutorial complete — +30 HP added to your Huduma Champions points!') : __('Tutorial complete.'));
        return redirect()->route('seller.champions');
    }

    /** PDF §14 — short client onboarding, +25 HP once when finished. */
    public function clientOnboarding()
    {
        $user = Auth::guard('web')->user();
        abort_unless($this->leagueFor($user) === 'client', 404);

        $filled = fn (array $cols) => collect($cols)->every(fn ($c) => trim((string) ($user->$c ?? '')) !== '');
        $steps = [
            ['icon' => 'la-user-edit', 'title' => __('Complete your profile'),
             'text' => __('Add your name, phone, photo and address so providers know who they are working for. A complete profile earns +50 HP.'),
             'done' => $filled(['name', 'email', 'phone', 'image', 'address']),
             'link' => route('buyer.profile'), 'cta' => __('Edit profile')],
            ['icon' => 'la-map-marker', 'title' => __('Set your location preferences'),
             'text' => __('Choose your city and area so you see providers who actually work near you. Worth +25 HP.'),
             'done' => $filled(['service_city', 'service_area']),
             'link' => route('buyer.profile'), 'cta' => __('Set city and area')],
            ['icon' => 'la-search', 'title' => __('Find and save providers'),
             'text' => __('Browse services and save the providers you like, so you can book them again later. Each saved provider earns +5 HP.'),
             'done' => DB::table('bookmarks')->where('user_id', $user->id)->exists(),
             'link' => url('/service-list'), 'cta' => __('Browse services')],
            ['icon' => 'la-clipboard-list', 'title' => __('Post a request and compare offers'),
             'text' => __('Describe the job you need done and let providers come to you. A genuine request earns +30 HP, and each provider response +10 HP.'),
             'done' => DB::table('buyer_jobs')->where('buyer_id', $user->id)->exists(),
             'link' => route('buyer.add.job'), 'cta' => __('Post a request')],
            ['icon' => 'la-trophy', 'title' => __('Book, complete and review'),
             'text' => __('Your first completed booking of the month earns +150 HP, every other booking +120 HP, and a verified review +25 HP. The Top Five clients win service credits every month.'),
             'done' => null,
             'link' => \Route::has('champions.rules') ? route('champions.rules') : route('buyer.champions'), 'cta' => __('See all ways to earn')],
        ];

        return view('frontend.champions.onboarding', [
            'steps'    => $steps,
            'isSeller' => false,
            'reward'   => 25,
            'finished' => DB::table('champion_points')->where(['user_id' => $user->id, 'rule_key' => 'c_onboarding'])
                            ->where('status', '!=', 'reversed')->exists(),
        ]);
    }

    public function clientOnboardingComplete()
    {
        $user = Auth::guard('web')->user();
        abort_unless($this->leagueFor($user) === 'client', 404);

        $id = $this->svc->award((int) $user->id, 'c_onboarding', ['source_type' => 'tutorial', 'source_id' => (int) $user->id]);
        toastr_success($id ? __('Tutorial complete — +25 HP added to your Huduma Champions points!') : __('Tutorial complete.'));
        return redirect()->route('buyer.champions');
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
