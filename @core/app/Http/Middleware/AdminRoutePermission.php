<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * Enforces admin permissions at the route level.
 *
 * Applied to the whole admin-home route group. It only blocks route names
 * listed in config('admin_permissions.route_map'); every other route passes
 * through untouched, so enforcement can be expanded one entry at a time
 * without risking surprise 403s elsewhere. Super Admin always bypasses.
 */
class AdminRoutePermission
{
    public function handle($request, Closure $next)
    {
        $admin = Auth::guard('admin')->user();

        // No admin session, or Super Admin -> let the request through.
        if (! $admin || $admin->hasRole('Super Admin')) {
            return $next($request);
        }

        $route = $request->route();
        $name  = $route ? $route->getName() : null;

        if ($name) {
            // Route names contain dots (e.g. "admin.service.delete"), so they
            // must be looked up as a flat array key — NOT via config()'s dot
            // notation, which would treat each dot as a nested level.
            $required = config('admin_permissions.route_map', [])[$name] ?? null;

            // can() returns false safely for unknown permissions and also
            // honours the Super Admin Gate::before, so it never throws.
            if ($required && ! $admin->can($required)) {
                $message = __("You don't have permission to access that.");

                if ($request->expectsJson()) {
                    return response()->json(['message' => $message], 403);
                }

                return redirect()
                    ->back(302, [], route('admin.home'))
                    ->with(['msg' => $message, 'type' => 'danger']);
            }
        }

        return $next($request);
    }
}
