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

        // 1) Exact route-name rules (route names contain dots, e.g.
        //    "admin.service.delete", so look them up as a flat array key — NOT
        //    via config()'s dot notation, which treats each dot as nesting).
        $required = $name ? (config('admin_permissions.route_map', [])[$name] ?? null) : null;

        // 2) Whole-section rules by URL path prefix (covers many routes at once,
        //    e.g. every Champions / Referral page under one prefix).
        if (! $required) {
            $path = $request->path();
            foreach (config('admin_permissions.path_map', []) as $prefix => $perm) {
                if ($path === $prefix || strpos($path, rtrim($prefix, '/') . '/') === 0) {
                    $required = $perm;
                    break;
                }
            }
        }

        // can() returns false safely for unknown permissions and also honours
        // the Super Admin Gate::before, so it never throws.
        if ($required && ! $admin->can($required)) {
            $message = __("You don't have permission to access that.");

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 403);
            }

            return redirect()
                ->back(302, [], route('admin.home'))
                ->with(['msg' => $message, 'type' => 'danger']);
        }

        return $next($request);
    }
}
