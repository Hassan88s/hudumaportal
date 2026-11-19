<?php

namespace App\Http\Controllers;

use App\Helpers\FlashMsg;
use App\Rider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Admin side of ride booking: who may carry passengers, and what a ride costs.
 */
class RideManageController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ride-rider-list',    ['only' => ['riders', 'riderDetails']]);
        $this->middleware('permission:ride-rider-approve', ['only' => ['approve', 'reject', 'suspend', 'restore']]);
        $this->middleware('permission:ride-settings',      ['only' => ['settings', 'settingsUpdate']]);
    }

    /* --------------------------------------------------------------- riders */

    public function riders(Request $request)
    {
        $status = $request->query('status');

        $riders = Rider::with('user')
            ->when($status !== null && $status !== '', fn ($q) => $q->where('status', (int) $status))
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [Rider::PENDING])  // waiting first
            ->orderByDesc('submitted_at')
            ->paginate(25)
            ->withQueryString();

        $counts = [
            'pending'   => Rider::where('status', Rider::PENDING)->count(),
            'approved'  => Rider::where('status', Rider::APPROVED)->count(),
            'rejected'  => Rider::where('status', Rider::REJECTED)->count(),
            'suspended' => Rider::where('status', Rider::SUSPENDED)->count(),
            'online'    => Rider::where('status', Rider::APPROVED)->where('is_online', 1)->count(),
        ];

        return view('backend.pages.ride.riders', compact('riders', 'counts', 'status'));
    }

    public function riderDetails($id)
    {
        $rider = Rider::with('user')->findOrFail($id);

        return view('backend.pages.ride.rider-details', compact('rider'));
    }

    public function approve($id)
    {
        $rider = Rider::findOrFail($id);

        // Approving an incomplete application would put a rider on the road
        // without the papers the approval is supposed to confirm.
        $missing = $rider->missingDocuments();
        if ($missing) {
            return redirect()->back()->with(FlashMsg::item_delete(
                __('This application is missing documents: ') . implode(', ', $missing)
            ));
        }

        $rider->update([
            'status'           => Rider::APPROVED,
            'rejection_reason' => null,
            'approved_at'      => now(),
            'approved_by'      => Auth::guard('admin')->id(),
        ]);

        return redirect()->back()->with(FlashMsg::item_new(__('Rider approved.')));
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ], [
            'rejection_reason.required' => __('Say why, so the rider knows what to fix.'),
        ]);

        $rider = Rider::findOrFail($id);

        $rider->update([
            'status'           => Rider::REJECTED,
            'rejection_reason' => $request->rejection_reason,
            'is_online'        => 0,   // a rejected rider must leave the map at once
        ]);

        return redirect()->back()->with(FlashMsg::item_new(__('Rider rejected.')));
    }

    public function suspend(Request $request, $id)
    {
        $rider = Rider::findOrFail($id);

        $rider->update([
            'status'           => Rider::SUSPENDED,
            'rejection_reason' => $request->input('rejection_reason'),
            'is_online'        => 0,
        ]);

        return redirect()->back()->with(FlashMsg::item_new(__('Rider suspended.')));
    }

    public function restore($id)
    {
        $rider = Rider::findOrFail($id);

        $rider->update([
            'status'           => Rider::APPROVED,
            'rejection_reason' => null,
            'approved_at'      => $rider->approved_at ?? now(),
            'approved_by'      => $rider->approved_by ?? Auth::guard('admin')->id(),
        ]);

        return redirect()->back()->with(FlashMsg::item_new(__('Rider restored.')));
    }

    /* ------------------------------------------------------------- settings */

    /** Option name => validation rule. The list is the schema. */
    protected function settingFields(): array
    {
        return [
            'ride_enabled'            => 'nullable|boolean',
            'ride_base_fare'          => 'required|numeric|min:0',
            'ride_price_per_km'       => 'required|numeric|min:0',
            'ride_price_per_minute'   => 'required|numeric|min:0',
            'ride_minimum_fare'       => 'required|numeric|min:0',
            'ride_commission_percent' => 'required|numeric|min:0|max:100',
            'ride_search_radius_km'   => 'required|numeric|min:0.5|max:100',
            'ride_request_timeout'    => 'required|integer|min:5|max:300',
            'ride_cancellation_fee'   => 'required|numeric|min:0',
        ];
    }

    public function settings()
    {
        $settings = [];
        foreach (array_keys($this->settingFields()) as $name) {
            $settings[$name] = get_static_option($name);
        }

        return view('backend.pages.ride.settings', compact('settings'));
    }

    public function settingsUpdate(Request $request)
    {
        $request->validate($this->settingFields(), [
            'ride_commission_percent.max' => __('Commission cannot be more than the whole fare.'),
        ]);

        foreach (array_keys($this->settingFields()) as $name) {
            // An unticked checkbox sends nothing, which has to mean off rather
            // than "leave it as it was".
            $value = $name === 'ride_enabled'
                ? ($request->boolean($name) ? '1' : '0')
                : $request->input($name);

            update_static_option($name, $value);
        }

        return redirect()->back()->with(FlashMsg::item_new(__('Ride settings saved.')));
    }
}
