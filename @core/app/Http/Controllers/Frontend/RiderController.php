<?php

namespace App\Http\Controllers\Frontend;

use App\Exceptions\RideException;
use App\Http\Controllers\Controller;
use App\Ride;
use App\Rider;
use App\Services\RideService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * The rider side of ride booking.
 *
 * Apply, watch the application, and once approved go on duty, take trips,
 * carry them out and get paid. The pages are deliberately plain: a rider
 * reads them at a junction with one hand on the bars.
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
        $rider  = $this->rider();
        $rides  = app(RideService::class);

        return view('frontend.user.rider.dashboard', [
            'rider'      => $rider,
            'active'     => $rides->activeTripFor($rider),
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

    /* ----------------------------------------------------------- trip work */

    /**
     * Trips on offer near this rider.
     *
     * Polled by the dashboard while the rider is online. It also returns the
     * trip they are already on, so one request is enough to know whether to
     * show a list or a job in progress.
     */
    public function requests()
    {
        $rider = $this->rider();

        if (! $rider || ! $rider->isApproved()) {
            return response()->json(['ok' => false], 403);
        }

        $rides   = app(RideService::class);
        $active  = $rides->activeTripFor($rider);
        $cur     = $rides->currency();

        if ($active) {
            return response()->json([
                'ok'       => true,
                'online'   => (bool) $rider->is_online,
                'active'   => true,
                'redirect' => route('rider.trip'),
                'requests' => [],
            ]);
        }

        $open = $rides->openRequestsFor($rider)->map(fn ($ride) => [
            'id'        => $ride->id,
            'pickup'    => $ride->pickup_address ?: __('Pick-up point'),
            'dropoff'   => $ride->dropoff_address ?: __('Drop-off point'),
            'away_km'   => $ride->pickup_distance_km,
            'trip_km'   => (float) $ride->distance_km,
            'minutes'   => (int) $ride->duration_min,
            'fare'      => $cur . ' ' . number_format($ride->fare, 0),
            'earning'   => $cur . ' ' . number_format(
                $ride->fare * (1 - ((float) get_static_option('ride_commission_percent') / 100)),
                0
            ),
            'passenger' => optional($ride->passenger)->name,
            // Who the rider would be carrying matters as much as where to.
            'standing'  => $rides->passengerStanding((int) $ride->passenger_id),
            'accept_url' => route('rider.trip.accept', $ride->id),
        ]);

        return response()->json([
            'ok'       => true,
            'online'   => (bool) $rider->is_online,
            'active'   => false,
            'requests' => $open->values(),
        ]);
    }

    /** Take one of the trips on offer. */
    public function accept(Request $request, $id)
    {
        $rider = $this->rider();

        if (! $rider) {
            return response()->json(['ok' => false], 403);
        }

        try {
            app(RideService::class)->accept($rider, (int) $id);
        } catch (RideException $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 409);
        }

        return response()->json(['ok' => true, 'redirect' => route('rider.trip')]);
    }

    /** The job in hand. */
    public function trip()
    {
        $rider = $this->rider();
        $rides = app(RideService::class);
        $ride  = $rides->activeTripFor($rider);

        if (! $ride) {
            toastr_error(__('You are not on a trip right now.'));

            return redirect()->route('rider.dashboard');
        }

        return view('frontend.user.rider.trip', [
            'rider'    => $rider,
            'ride'     => $ride,
            'standing' => $rides->passengerStanding((int) $ride->passenger_id),
            'currency' => $rides->currency(),
            'earning'  => round(
                $ride->fare * (1 - ((float) get_static_option('ride_commission_percent') / 100)),
                2
            ),
        ]);
    }

    /**
     * Move the job on: arrived, started, finished, or dropped.
     *
     * One endpoint for all four because the screen is one screen, and the
     * service decides whether the step asked for is legal from where the trip
     * actually is — not from what the button said.
     */
    public function tripAction(Request $request, $id, $action)
    {
        $rider = $this->rider();
        $rides = app(RideService::class);

        if (! $rider) {
            return response()->json(['ok' => false], 403);
        }

        $ride = Ride::where('id', $id)->where('rider_id', $rider->id)->first();

        if (! $ride) {
            return response()->json(['ok' => false, 'message' => __('That is not your trip.')], 404);
        }

        try {
            switch ($action) {
                case 'arrived':
                case 'start':
                    $ride = $rides->advance($rider, $ride, $action);
                    break;

                case 'complete':
                    $ride = $rides->complete($rider, $ride);
                    break;

                case 'cancel':
                    $request->validate(['reason' => 'nullable|string|max:255']);
                    $ride = $rides->cancelByRider($rider, $ride, $request->input('reason'));
                    break;

                default:
                    return response()->json(['ok' => false, 'message' => __('Unknown step.')], 422);
            }
        } catch (RideException $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 409);
        }

        return response()->json([
            'ok'       => true,
            'status'   => $ride->status,
            'label'    => $ride->statusLabel(),
            'closed'   => $ride->isClosed(),
            'earning'  => (float) $ride->rider_earning,
            // A finished trip has one more thing to do on it, so it gets its
            // own screen rather than dropping the rider back on the feed.
            'redirect' => $ride->status === Ride::COMPLETED
                ? route('rider.trip.done', $ride->id)
                : ($ride->isClosed() ? route('rider.dashboard') : null),
        ]);
    }

    /**
     * The screen a rider lands on when a trip is finished.
     *
     * Shows what they made, and asks them to rate the passenger while the
     * trip is still fresh — which is the only moment anybody will do it.
     */
    public function tripDone($id)
    {
        $rider = $this->rider();
        $rides = app(RideService::class);

        $ride = Ride::with('passenger')
            ->where('id', $id)
            ->where('rider_id', $rider->id)
            ->firstOrFail();

        if ($ride->status !== Ride::COMPLETED) {
            return redirect()->route('rider.dashboard');
        }

        return view('frontend.user.rider.trip-done', [
            'rider'    => $rider,
            'ride'     => $ride,
            'standing' => $rides->passengerStanding((int) $ride->passenger_id),
            'currency' => $rides->currency(),
        ]);
    }

    /** Rate the passenger who was just carried. */
    public function ratePassenger(Request $request, $id)
    {
        $request->validate([
            'rating'  => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:500',
        ]);

        $rider = $this->rider();

        if (! $rider) {
            return response()->json(['ok' => false], 403);
        }

        $ride = Ride::where('id', $id)->where('rider_id', $rider->id)->first();

        if (! $ride) {
            return response()->json(['ok' => false, 'message' => __('That is not your trip.')], 404);
        }

        try {
            app(RideService::class)->ratePassenger(
                $rider, $ride, (int) $request->rating, $request->input('comment')
            );
        } catch (RideException $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 409);
        }

        return response()->json(['ok' => true, 'message' => __('Thanks for the rating.')]);
    }

    /**
     * What passengers have said.
     *
     * A rider can see their own average, the shape behind it and what people
     * wrote — but not who wrote it. A rating has to be safe to give honestly,
     * and a rider who can put a name to a complaint makes it less so.
     */
    public function ratings()
    {
        $rider = $this->rider();
        $rides = app(RideService::class);

        return view('frontend.user.rider.ratings', [
            'rider'     => $rider,
            'breakdown' => $rides->ratingBreakdown($rider),
            'ratings'   => $rides->recentRatings($rider, 30),
        ]);
    }

    /** What this rider has earned, and from which trips. */
    public function earnings()
    {
        $rider = $this->rider();
        $rides = app(RideService::class);

        $completed = Ride::where('rider_id', $rider->id)
            ->where('status', Ride::COMPLETED);

        return view('frontend.user.rider.earnings', [
            'rider'    => $rider,
            'currency' => $rides->currency(),
            'balance'  => $rides->walletBalance($rider->user_id),
            'today'    => (float) (clone $completed)->whereDate('completed_at', today())->sum('rider_earning'),
            'week'     => (float) (clone $completed)
                ->where('completed_at', '>=', now()->subDays(7))->sum('rider_earning'),
            'lifetime' => (float) (clone $completed)->sum('rider_earning'),
            'trips'    => Ride::where('rider_id', $rider->id)
                ->whereIn('status', Ride::CLOSED)
                ->orderByDesc('id')
                ->paginate(15),
        ]);
    }
}
