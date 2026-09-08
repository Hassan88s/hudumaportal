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
