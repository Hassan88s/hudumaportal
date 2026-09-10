<?php

namespace App\Http\Controllers;

use App\Referral;
use App\ReferralReward;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Admin panel: Rafiki Rewards — Referrals list, detail, actions.
 * All routes live under the admin group in routes/admin.php and are
 * therefore protected by the admin auth middleware there.
 */
class ReferralsAdminController extends Controller
{
    /**
     * GET /admin-home/referrals
     * Table view with filters, search, and top stats strip.
     */
    public function index(Request $request)
    {
        $q      = trim((string) $request->query('q'));
        $status = $request->query('status');   // qualifying | approved | rejected | blocked | pending
        $track  = $request->query('track');    // provider | client | business
        $from   = $request->query('from');
        $to     = $request->query('to');

        $query = Referral::query()
            ->with(['referrer:id,name,username,email,referral_code',
                    'referredUser:id,name,username,email'])
            ->withSum(['rewards as rewards_total' => function ($q) {
                $q->where('type', 'cash');
            }], 'amount');

        if ($status)  $query->where('status', $status);
        if ($track)   $query->where('track', $track);
        if ($from)    $query->whereDate('created_at', '>=', $from);
        if ($to)      $query->whereDate('created_at', '<=', $to);

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->whereHas('referrer', function ($u) use ($q) {
                    $u->where('name', 'like', "%$q%")
                      ->orWhere('email', 'like', "%$q%")
                      ->orWhere('username', 'like', "%$q%")
                      ->orWhere('referral_code', 'like', "%$q%");
                })->orWhereHas('referredUser', function ($u) use ($q) {
                    $u->where('name', 'like', "%$q%")
                      ->orWhere('email', 'like', "%$q%")
                      ->orWhere('username', 'like', "%$q%");
                })->orWhere('code_used', 'like', "%$q%");
            });
        }

        $referrals = $query->orderByDesc('id')->paginate(25)->appends($request->query());

        // Stats strip (global totals — not filtered)
        $stats = [
            'total'       => Referral::count(),
            'qualifying'  => Referral::where('status', 'qualifying')->count(),
            'approved'    => Referral::where('status', 'approved')->count(),
            'rejected'    => Referral::whereIn('status', ['rejected', 'blocked'])->count(),
            'flagged'     => Referral::where('status', 'flagged')->count(),
            'paid_month'  => (float) ReferralReward::where('status', 'paid')
                                                    ->where('paid_at', '>=', now()->startOfMonth())
                                                    ->sum('amount'),
            'pending_all' => (float) ReferralReward::where('status', 'pending')->sum('amount'),
        ];

        return view('backend.referrals.index', compact('referrals', 'stats', 'q', 'status', 'track', 'from', 'to'));
    }

    /**
     * GET /admin-home/referrals/{id} — detail view (Step B).
     */
    public function show($id)
    {
        $referral = Referral::with([
            'referrer:id,name,username,email,phone,referral_code,created_at',
            'referredUser:id,name,username,email,phone,user_type,created_at,email_verified',
            'rewards',
        ])->findOrFail($id);

        return view('backend.referrals.show', compact('referral'));
    }

    /**
     * POST /admin-home/referrals/{id}/status — change status (Step B).
     * Body: status=approved|rejected|blocked|qualifying, reason (optional)
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,qualifying,approved,rejected,blocked',
            'reason' => 'nullable|string|max:255',
        ]);

        $referral = Referral::findOrFail($id);
        $referral->status = $request->status;
        if ($request->filled('reason')) {
            $referral->rejection_reason = $request->reason;
        }
        $referral->save();

        // If rejected/blocked, mark all its pending rewards as rejected too.
        if (in_array($request->status, ['rejected', 'blocked'])) {
            ReferralReward::where('referral_id', $referral->id)
                ->whereIn('status', ['pending', 'qualifying', 'approved'])
                ->update(['status' => 'rejected', 'rejected_at' => now(), 'updated_at' => now()]);
        }

        return redirect()->back()->with('success', __('Referral status updated.'));
    }

    /**
     * POST /admin-home/referrals/{id}/credit — manual credit adjustment (Step B).
     */
    public function manualCredit(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'reason' => 'required|string|max:255',
        ]);

        $referral = Referral::findOrFail($id);

        ReferralReward::create([
            'referral_id'     => $referral->id,
            'user_id'         => $referral->referrer_id,
            'event'           => 'admin_manual_credit',
            'amount'          => $request->amount,
            'currency'        => 'TZS',
            'type'            => 'cash',
            'status'          => 'approved',
            'reason'          => __('Admin manual credit: ') . $request->reason,
            'approved_at'     => now(),
            'idempotency_key' => 'manual:' . $referral->id . ':' . now()->timestamp,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return redirect()->back()->with('success', __('Manual credit added.'));
    }

    /**
     * GET /admin-home/referral-clicks — analytics for /r/{code} visits.
     * Aggregates the referral_clicks table into totals + top-codes + conversion
     * rate (clicks that later signed up / total clicks).
     */
    public function clicksAnalytics(Request $request)
    {
        $days = max(1, min(365, (int) $request->query('days', 30)));
        $since = now()->subDays($days);

        // Global totals within the window
        $totalClicks = \App\ReferralClick::where('created_at', '>=', $since)->count();
        $uniqueIps   = \App\ReferralClick::where('created_at', '>=', $since)
            ->distinct()->count('ip_address');
        $converted   = \App\ReferralClick::where('created_at', '>=', $since)
            ->whereNotNull('converted_user_id')->count();
        $invalid     = \App\ReferralClick::where('created_at', '>=', $since)
            ->whereNull('referrer_id')->count();

        $conversionRate = $totalClicks > 0 ? round(($converted / $totalClicks) * 100, 1) : 0.0;

        // Top codes by clicks in the window (joined with referrer name)
        $topCodes = DB::table('referral_clicks')
            ->select(
                'referral_clicks.code',
                'referral_clicks.referrer_id',
                'users.name as referrer_name',
                'users.email as referrer_email',
                DB::raw('COUNT(*) as clicks'),
                DB::raw('SUM(CASE WHEN converted_user_id IS NOT NULL THEN 1 ELSE 0 END) as conversions')
            )
            ->leftJoin('users', 'users.id', '=', 'referral_clicks.referrer_id')
            ->where('referral_clicks.created_at', '>=', $since)
            ->whereNotNull('referral_clicks.referrer_id')
            ->groupBy('referral_clicks.code', 'referral_clicks.referrer_id', 'users.name', 'users.email')
            ->orderByDesc('clicks')
            ->limit(20)
            ->get();

        // Channel breakdown (from the ?ch= param captured on landing)
        $channels = DB::table('referral_clicks')
            ->select('channel', DB::raw('COUNT(*) as clicks'))
            ->where('created_at', '>=', $since)
            ->groupBy('channel')
            ->orderByDesc('clicks')
            ->get();

        // Daily clicks trend (last 14 days for the sparkline)
        $trend = DB::table('referral_clicks')
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('COUNT(*) as clicks'))
            ->where('created_at', '>=', now()->subDays(14))
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        return view('backend.referrals.clicks', compact(
            'days', 'totalClicks', 'uniqueIps', 'converted', 'invalid',
            'conversionRate', 'topCodes', 'channels', 'trend'
        ));
    }

    /**
     * GET /admin-home/referrer-leaderboard — top referrers by count + earnings.
     */
    public function leaderboard(Request $request)
    {
        $period = $request->query('period', 'month'); // month | year | all
        $since = match ($period) {
            'month' => now()->startOfMonth(),
            'year'  => now()->startOfYear(),
            default => null,
        };

        // Rank by number of referrals brought in the window
        $byCount = DB::table('referrals')
            ->select(
                'referrals.referrer_id',
                'users.name',
                'users.email',
                'users.referral_code',
                DB::raw('COUNT(*) as ref_count'),
                DB::raw('SUM(CASE WHEN referrals.status = "approved" THEN 1 ELSE 0 END) as approved_count')
            )
            ->leftJoin('users', 'users.id', '=', 'referrals.referrer_id')
            ->when($since, fn ($q) => $q->where('referrals.created_at', '>=', $since))
            ->groupBy('referrals.referrer_id', 'users.name', 'users.email', 'users.referral_code')
            ->orderByDesc('ref_count')
            ->limit(20)
            ->get();

        // Rank by TOTAL earnings in the window
        $byEarnings = DB::table('referral_rewards')
            ->select(
                'referral_rewards.user_id as referrer_id',
                'users.name',
                'users.email',
                'users.referral_code',
                DB::raw('SUM(referral_rewards.amount) as earnings'),
                DB::raw('COUNT(*) as reward_count')
            )
            ->leftJoin('users', 'users.id', '=', 'referral_rewards.user_id')
            ->where('referral_rewards.type', 'cash')
            ->whereIn('referral_rewards.status', ['approved', 'paid'])
            ->when($since, fn ($q) => $q->where('referral_rewards.created_at', '>=', $since))
            ->groupBy('referral_rewards.user_id', 'users.name', 'users.email', 'users.referral_code')
            ->orderByDesc('earnings')
            ->limit(20)
            ->get();

        return view('backend.referrals.leaderboard', compact('byCount', 'byEarnings', 'period'));
    }

    /**
     * GET /admin-home/referral-rewards — full rewards ledger (Step C).
     */
    public function rewardsLedger(Request $request)
    {
        $status = $request->query('status');
        $type   = $request->query('type');
        $q      = trim((string) $request->query('q'));

        $query = ReferralReward::query()
            ->with(['user:id,name,email,username', 'referral:id,referrer_id,referred_user_id']);

        if ($status) $query->where('status', $status);
        if ($type)   $query->where('type', $type);
        if ($q !== '') {
            $query->whereHas('user', function ($u) use ($q) {
                $u->where('name', 'like', "%$q%")
                  ->orWhere('email', 'like', "%$q%")
                  ->orWhere('username', 'like', "%$q%");
            });
        }

        $rewards = $query->orderByDesc('id')->paginate(30)->appends($request->query());

        $totals = [
            'pending'  => (float) ReferralReward::where('status', 'pending')->sum('amount'),
            'approved' => (float) ReferralReward::where('status', 'approved')->sum('amount'),
            'paid'     => (float) ReferralReward::where('status', 'paid')->sum('amount'),
            'rejected' => (float) ReferralReward::where('status', 'rejected')->sum('amount'),
        ];

        return view('backend.referrals.rewards', compact('rewards', 'totals', 'status', 'type', 'q'));
    }
}
