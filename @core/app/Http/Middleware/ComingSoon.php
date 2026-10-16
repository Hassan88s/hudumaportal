<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ComingSoon
{
    /**
     * When "Coming Soon" mode is on, the public site shows a pre-launch page.
     * Visitors may still register, but after signing up they land on a waitlist
     * queue page until an admin turns the mode off. Admins always bypass it so
     * they can keep working and preview the real site.
     */
    public function handle(Request $request, Closure $next)
    {
        if ((int) get_static_option('coming_soon_enabled') !== 1) {
            return $next($request);
        }

        // Admin panel, admin login, and any logged-in admin bypass completely.
        if ($request->is('admin-home*') || $request->is('login/admin*') || auth()->guard('admin')->check()) {
            return $next($request);
        }

        // The sign-up / sign-in flow, language switch, logout and the waitlist
        // page itself must stay reachable during coming-soon mode.
        $allowed = [
            'register', 'login', 'login-for-online', 'login-with-otp-code', 'login-with-otp-code/*',
            'email-verify', 'otp-verify', 'otp-verify/*',
            'resend-verify-code-again', 'resent-otp-code', 'resent-otp-code/*',
            'resent-otp-code-login', 'resent-otp-code-login/*',
            'user/forget-password', 'login/*',
            'language-chnage', 'logout', 'logout/*', 'buyer/logout', 'seller/logout',
            'queue', 'coming-soon',
        ];
        if ($request->is(...$allowed)) {
            return $next($request);
        }

        // A registered buyer/seller waits in the queue until launch.
        if (auth()->guard('web')->check()) {
            return redirect()->route('coming.soon.queue');
        }

        // Everyone else sees the coming-soon landing page.
        return response()->view('frontend.coming-soon');
    }
}
