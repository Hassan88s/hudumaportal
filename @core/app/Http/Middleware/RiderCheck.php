<?php

namespace App\Http\Middleware;

use App\Rider;
use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * Guards the rider area.
 *
 * Riding is opt-in: any signed-in user may apply, so this does not look at
 * user_type. It looks at whether this user has been approved to carry
 * passengers, and sends everybody else to the application page rather than
 * to a dead end.
 *
 * The application pages themselves are listed as open, otherwise an applicant
 * could never reach the form that would approve them.
 */
class RiderCheck
{
    /** Rider pages reachable without an approved rider row. */
    protected array $open = [
        'rider/apply',
        'rider/status',
    ];

    public function handle($request, Closure $next)
    {
        if (! Auth::guard('web')->check()) {
            return redirect()->route('user.login');
        }

        // Switching the whole feature off must not leave the pages reachable.
        if (get_static_option('ride_enabled') != '1') {
            abort(404);
        }

        if (in_array($request->path(), $this->open, true)) {
            return $next($request);
        }

        $rider = Rider::where('user_id', Auth::guard('web')->id())->first();

        if (! $rider) {
            return redirect()->route('rider.apply');
        }

        if (! $rider->isApproved()) {
            return redirect()->route('rider.status');
        }

        return $next($request);
    }
}
