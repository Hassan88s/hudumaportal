<?php

namespace App\Http\Middleware;

use App\Rider;
use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * Guards the rider area.
 *
 * Carrying passengers is a service somebody sells, so only a seller account
 * may become a rider. A buyer who lands here is sent to book a ride instead,
 * which is the thing a buyer can actually do.
 *
 * Past that, this looks at whether the user has been approved to carry
 * passengers, and sends everybody else to the application page rather than to
 * a dead end. The application pages themselves are listed as open, otherwise
 * an applicant could never reach the form that would approve them.
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

        // Sellers only. 0 is a seller account; anything else is a buyer.
        if ((int) Auth::guard('web')->user()->user_type !== 0) {
            toastr_error(__('Riding is for seller accounts. Switch to selling to apply.'));

            return redirect()->route('ride.index');
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
