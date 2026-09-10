<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

/**
 * Handles the public /r/{code} shortlink and the seller /seller/earn dashboard.
 */
class ReferralController extends Controller
{
    protected ReferralService $referrals;

    public function __construct(ReferralService $referrals)
    {
        $this->referrals = $referrals;
    }

    /**
     * Public entry: /r/{code}
     * Records the click, sets the attribution cookie, redirects to register.
     * Attribution cookie is respected by RegisterController on sign-up.
     */
    public function land(Request $request, string $code)
    {
        $code = strtoupper(trim($code));
        $referrer = $this->referrals->findReferrerByCode($code);

        // Always log the click (even if the code is invalid — useful for spam/typo analytics)
        $channel = $request->query('ch'); // optional ?ch=whatsapp
        $this->referrals->trackClick($code, $request, $channel);

        // Invalid code -> just send them home, no cookie
        if (!$referrer) {
            return redirect('/');
        }

        // Attribution window (default 30 days from settings)
        $days = (int) (\App\StaticOption::where('option_name', 'referral_attribution_days')->value('option_value') ?? 30);

        // If the visitor is already logged in, don't overwrite their own attribution
        if (Auth::check()) {
            return redirect('/')->with('info', __('Thanks for visiting — you are already registered.'));
        }

        // Drop cookie for 30 days (RegisterController checks this)
        Cookie::queue('hp_ref', $code, $days * 24 * 60);

        return redirect('/register?ref=' . urlencode($code));
    }

    /**
     * GET /referral — public marketing landing page for the Rafiki Rewards program.
     * Overrides the dynamic page slug so we get a purpose-built layout.
     */
    public function publicLanding(Request $request)
    {
        $s = fn($name, $default) => (float) (\App\StaticOption::where('option_name', $name)->value('option_value') ?? $default);

        $rewards = [
            'p1'         => $s('referral_stage1_provider_amount', 500),
            'p2'         => $s('referral_stage2_provider_cash', 1000),
            'p2c'        => $s('referral_stage2_provider_credit', 1000),
            'p3'         => $s('referral_stage3_provider_amount', 1500),
            'c_welcome'  => $s('referral_client_welcome_credit', 1000),
            'c1'         => $s('referral_client_first_booking', 750),
            'c2'         => $s('referral_client_second_booking', 750),
            'prot_days'  => (int) $s('referral_protection_days', 14),
            'min_wd'     => $s('referral_min_withdrawal', 5000),
            'attr_days'  => (int) $s('referral_attribution_days', 30),
        ];
        $rewards['provider_total'] = $rewards['p1'] + $rewards['p2'] + $rewards['p3'];
        $rewards['client_total']   = $rewards['c1'] + $rewards['c2'];

        // Platform trust signals — non-personal aggregate stats.
        $stats = [
            'total_users'       => \App\User::count(),
            'total_referrals'   => \App\Referral::count(),
            'total_paid'        => (float) \App\ReferralReward::whereIn('status', ['approved', 'paid'])->sum('amount'),
        ];

        $user = Auth::guard('web')->user();

        return view('frontend.referral-landing', compact('rewards', 'stats', 'user'));
    }

    /**
     * POST /seller/earn/transfer — move approved referral earnings to main wallet.
     */
    public function transfer(Request $request)
    {
        $user   = Auth::guard('web')->user();
        $result = $this->referrals->transferToMainWallet($user->id);

        return redirect()->route('seller.earn')
            ->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    /**
     * POST /buyer/earn/transfer — buyer equivalent of the above.
     */
    public function buyerTransfer(Request $request)
    {
        $user   = Auth::guard('web')->user();
        $result = $this->referrals->transferToMainWallet($user->id);

        return redirect()->route('buyer.earn')
            ->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Seller dashboard "Earn" tab.
     */
    public function earn(Request $request)
    {
        return $this->renderEarn('frontend.user.seller.earn.index');
    }

    /**
     * Buyer dashboard "Earn" tab.
     */
    public function buyerEarn(Request $request)
    {
        return $this->renderEarn('frontend.user.buyer.earn.index');
    }

    /**
     * Shared view builder — same data, different wrapper view.
     */
    protected function renderEarn(string $view)
    {
        $user  = Auth::guard('web')->user();
        $stats = $this->referrals->statsForUser($user->id);

        // Paginate 5 per page each. Different page-name so the two tables
        // don't share the ?page= query param and page independently.
        $referrals = \App\Referral::with('referredUser')
            ->where('referrer_id', $user->id)
            ->latest()
            ->paginate(5, ['*'], 'refs_page');

        $rewards = \App\ReferralReward::where('user_id', $user->id)
            ->latest()
            ->paginate(5, ['*'], 'rewards_page');

        $shareUrl  = url('/r/' . $user->referral_code);
        $shareCode = $user->referral_code;

        return view($view, compact(
            'stats', 'referrals', 'rewards', 'shareUrl', 'shareCode', 'user'
        ));
    }
}
