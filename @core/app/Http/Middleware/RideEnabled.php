<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * Guards the passenger side of ride booking.
 *
 * Booking a bike is open to every signed-in user — buyer or seller, it makes
 * no difference who you are on the marketplace — so this only checks that the
 * feature is switched on at all. With it off, the pages are not merely hidden
 * but absent, which is what an admin who turned it off means.
 */
class RideEnabled
{
    public function handle($request, Closure $next)
    {
        if (! Auth::guard('web')->check()) {
            return redirect()->route('user.login');
        }

        if (get_static_option('ride_enabled') != '1') {
            abort(404);
        }

        return $next($request);
    }
}
