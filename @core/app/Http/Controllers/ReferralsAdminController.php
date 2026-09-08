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
