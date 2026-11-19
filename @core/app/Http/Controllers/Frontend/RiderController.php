<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Rider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * The rider side of ride booking.
 *
 * Phase one: apply, watch the application, and once approved go on and off
 * duty while the phone reports where the bike is. Trips themselves arrive in
 * the next phase; everything here is what has to exist before a trip can be
 * offered to anybody.
 */
class RiderController extends Controller
{
    /** Where rider documents are written, under public/. */
    protected const UPLOAD_DIR = 'assets/uploads/riders';

    /** Uploads accepted for every document field. */
    protected const DOC_RULES = 'file|mimes:jpg,jpeg,png,pdf|max:5120';

    protected function rider(): ?Rider
    {
        return Rider::where('user_id', Auth::guard('web')->id())->first();
    }

    /* ---------------------------------------------------------------- apply */

    public function apply(Request $request)
    {
        $rider = $this->rider();

        // Somebody already approved has no business on the form.
        if ($rider && $rider->isApproved()) {
            return redirect()->route('rider.dashboard');
        }

        if ($request->isMethod('post')) {
            return $this->storeApplication($request, $rider);
        }

        return view('frontend.user.rider.apply', compact('rider'));
    }

    protected function storeApplication(Request $request, ?Rider $rider)
    {
        // A rejected rider may fix their papers and send them again; a pending
        // one may correct a document. Both land here, so a file is required
        // only when there is not already one on file.
        $needs = fn (string $field) => (optional($rider)->{$field} ? 'nullable' : 'required') . '|' . self::DOC_RULES;

        $request->validate([
            'full_name'         => 'required|string|max:191',
            'phone'             => 'required|string|max:50',
            'national_id'       => 'required|string|max:100',
            'licence_number'    => 'required|string|max:100',
            'licence_expiry'    => 'required|date|after:today',
            'bike_plate'        => 'required|string|max:50',
            'bike_model'        => 'nullable|string|max:100',
            'national_id_photo' => $needs('national_id_photo'),
            'licence_photo'     => $needs('licence_photo'),
            'bike_reg_photo'    => $needs('bike_reg_photo'),
            'bike_photo'        => 'nullable|' . self::DOC_RULES,
            'insurance_photo'   => 'nullable|' . self::DOC_RULES,
        ], [
            'licence_expiry.after' => __('That licence has already expired.'),
        ]);

        $data = $request->only([
            'full_name', 'phone', 'national_id',
            'licence_number', 'licence_expiry', 'bike_plate', 'bike_model',
        ]);

        foreach ([
            'national_id_photo' => 'nid',
            'licence_photo'     => 'lic',
            'bike_reg_photo'    => 'reg',
            'bike_photo'        => 'bike',
            'insurance_photo'   => 'ins',
        ] as $field => $prefix) {
            $stored = $this->storeDocument($request, $field, $prefix);
            if ($stored !== null) {
                $data[$field] = $stored;
            }
        }

        $data['status']           = Rider::PENDING;
        $data['rejection_reason'] = null;   // a fresh submission clears the old refusal
        $data['submitted_at']     = now();

        Rider::updateOrCreate(['user_id' => Auth::guard('web')->id()], $data);

        toastr_success(__('Your application has been sent. We will review it shortly.'));

        return redirect()->route('rider.status');
    }

    /**
     * Moves one uploaded document into place.
     *
     * Returns null when nothing was uploaded for this field, so the caller can
     * tell "leave what is already there" apart from "replace it".
     */
    protected function storeDocument(Request $request, string $field, string $prefix): ?string
    {
        if (! $request->hasFile($field)) {
            return null;
        }

        $file = $request->file($field);
        $name = $prefix . '_' . Auth::guard('web')->id() . '_' . time() . '_'
            . Str::random(6) . '.' . strtolower($file->getClientOriginalExtension());

        $file->move(public_path(self::UPLOAD_DIR), $name);

        return self::UPLOAD_DIR . '/' . $name;
    }

    /* --------------------------------------------------------------- status */

    public function status()
    {
        $rider = $this->rider();

        if (! $rider) {
            return redirect()->route('rider.apply');
        }

        if ($rider->isApproved()) {
            return redirect()->route('rider.dashboard');
        }

        return view('frontend.user.rider.status', compact('rider'));
    }

    /* ------------------------------------------------------------ dashboard */

    public function dashboard()
    {
        $rider = $this->rider();

        return view('frontend.user.rider.dashboard', [
            'rider'      => $rider,
            'fare'       => [
                'base'       => (float) get_static_option('ride_base_fare'),
                'per_km'     => (float) get_static_option('ride_price_per_km'),
                'per_minute' => (float) get_static_option('ride_price_per_minute'),
                'minimum'    => (float) get_static_option('ride_minimum_fare'),
                'commission' => (float) get_static_option('ride_commission_percent'),
            ],
        ]);
    }

    /**
     * Go on or off duty.
     *
     * The phone posts this, so it answers JSON. Whether the rider ends up
     * online is returned rather than assumed, because a suspended rider is
     * refused here and the screen has to show that.
     */
    public function toggleOnline(Request $request)
    {
        $request->validate([
            'online' => 'required|boolean',
            'lat'    => 'nullable|numeric|between:-90,90',
            'lng'    => 'nullable|numeric|between:-180,180',
        ]);

        $rider = $this->rider();

        if (! $rider || ! $rider->isApproved()) {
            return response()->json([
                'ok'      => false,
                'online'  => false,
                'message' => __('Your rider account is not approved.'),
            ], 403);
        }

        $going = (bool) $request->boolean('online');

        $update = ['is_online' => $going];

        // Going online without a position would put the rider nowhere, so the
        // first fix is recorded with the switch itself.
        if ($request->filled('lat') && $request->filled('lng')) {
            $update['last_lat']         = $request->lat;
            $update['last_lng']         = $request->lng;
            $update['last_location_at'] = now();
        }

        $rider->update($update);

        return response()->json([
            'ok'      => true,
            'online'  => $going,
            'message' => $going ? __('You are online.') : __('You are offline.'),
        ]);
    }

    /**
     * Position ping from the rider's phone.
     *
     * Deliberately cheap: one row, no history. Where a bike was ten minutes
     * ago matters for a trip that is running, and that belongs with the trip,
     * not here.
     */
    public function ping(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $rider = $this->rider();

        if (! $rider || ! $rider->isApproved()) {
            return response()->json(['ok' => false], 403);
        }

        // A ping from a rider who is off duty is not an error — the screen may
        // still be open — but it must not put them back on the map.
        $rider->update([
            'last_lat'         => $request->lat,
            'last_lng'         => $request->lng,
            'last_location_at' => now(),
        ]);

        return response()->json(['ok' => true, 'online' => (bool) $rider->is_online]);
    }
}
