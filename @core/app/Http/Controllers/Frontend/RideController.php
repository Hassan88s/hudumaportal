<?php

namespace App\Http\Controllers\Frontend;

use App\Exceptions\RideException;
use App\Http\Controllers\Controller;
use App\Ride;
use App\Services\RideService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * The passenger side of ride booking.
 *
 * The screens are written for a phone held in one hand at the side of a road,
 * so every page answers one question: where am I going, who is coming, how
 * much. Anything that changes while waiting is polled rather than reloaded —
 * there is no socket here, and a bike journey is short enough that a few
 * seconds of lag costs nobody anything.
 */
class RideController extends Controller
{
    protected RideService $rides;

    public function __construct(RideService $rides)
    {
        $this->rides = $rides;
    }

    protected function userId(): int
    {
        return (int) Auth::guard('web')->id();
    }

    /**
     * Where this user tops up.
     *
     * Buyers and sellers have separate wallet screens, and the wallet itself
     * is a module that can be absent — in which case there is nowhere to send
     * anybody and the home page is the honest answer.
     */
    protected function walletUrl(): string
    {
        if (! moduleExists('Wallet')) {
            return url('/');
        }

        return (int) optional(Auth::guard('web')->user())->user_type === 1
            ? route('buyer.wallet.history')
            : route('seller.wallet.history');
    }

    /* -------------------------------------------------------------- book it */

    public function index()
    {
        $active = $this->rides->activeRideFor($this->userId());

        // Somebody with a trip running has no use for the booking form.
        if ($active) {
            return redirect()->route('ride.trip', $active->id);
        }

        return view('frontend.ride.book', [
            'settings'   => $this->rides->settings(),
            'balance'    => $this->rides->walletBalance($this->userId()),
            'currency'   => $this->rides->currency(),
            'walletUrl'  => $this->walletUrl(),
            'recent'     => Ride::where('passenger_id', $this->userId())
                ->whereIn('status', Ride::CLOSED)
                ->orderByDesc('id')
                ->limit(3)
                ->get(),
        ]);
    }

    /** Price a trip before it exists. Answers the booking screen's map. */
    public function quote(Request $request)
    {
        $request->validate($this->pointRules());

        $quote = $this->rides->quote(
            (float) $request->pickup_lat,
            (float) $request->pickup_lng,
            (float) $request->dropoff_lat,
            (float) $request->dropoff_lng
        );

        $balance = $this->rides->walletBalance($this->userId());

        return response()->json([
            'ok'       => true,
            'quote'    => $quote,
            'balance'  => $balance,
            'affords'  => $balance + 0.001 >= $quote['fare'],
            'shortfall' => max(0, round($quote['fare'] - $balance, 2)),
        ]);
    }

    public function book(Request $request)
    {
        $request->validate($this->pointRules() + [
            'pickup_address'  => 'nullable|string|max:255',
            'dropoff_address' => 'nullable|string|max:255',
        ]);

        try {
            $ride = $this->rides->book($this->userId(), $request->only([
                'pickup_lat', 'pickup_lng', 'pickup_address',
                'dropoff_lat', 'dropoff_lng', 'dropoff_address',
            ]));
        } catch (RideException $e) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
            }

            toastr_error($e->getMessage());

            return redirect()->route('ride.index');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok'       => true,
                'ride_id'  => $ride->id,
                'redirect' => route('ride.trip', $ride->id),
            ]);
        }

        return redirect()->route('ride.trip', $ride->id);
    }

    protected function pointRules(): array
    {
        return [
            'pickup_lat'   => 'required|numeric|between:-90,90',
            'pickup_lng'   => 'required|numeric|between:-180,180',
            'dropoff_lat'  => 'required|numeric|between:-90,90',
            'dropoff_lng'  => 'required|numeric|between:-180,180',
        ];
    }

    /* ------------------------------------------------------------- watch it */

    public function trip($id)
    {
        $ride = $this->ownRide($id);

        return view('frontend.ride.trip', [
            'ride'     => $ride,
            'currency' => $this->rides->currency(),
            'settings' => $this->rides->settings(),
        ]);
    }

    /**
     * The trip as the waiting screen needs it.
     *
     * Everything the passenger is shown comes from here, so the page itself
     * never has to work anything out: it draws whatever this says.
     */
    public function status($id)
    {
        $this->rides->expireStale();

        $ride  = $this->ownRide($id);
        $rider = $ride->rider;

        return response()->json([
            'ok'       => true,
            'status'   => $ride->status,
            'label'    => $ride->statusLabel(),
            'live'     => $ride->isLive(),
            'closed'   => $ride->isClosed(),
            'can_cancel' => in_array($ride->status, [Ride::SEARCHING, Ride::ACCEPTED, Ride::ARRIVED], true),
            'free_cancel' => $ride->isFreeToCancel(),
            'fare'     => (float) $ride->fare,
            'refunded' => (float) $ride->refunded_amount,
            'fee'      => (float) $ride->cancellation_fee,
            'rated'    => $ride->rating !== null,
            'rider'    => $rider ? [
                'name'   => $rider->full_name ?: optional($rider->user)->name,
                'phone'  => $rider->phone,
                'plate'  => $rider->bike_plate,
                'model'  => $rider->bike_model,
                'rating' => $rider->rating_count ? $rider->averageRating() : null,
                'trips'  => (int) $rider->total_trips,
                'lat'    => $rider->last_lat !== null ? (float) $rider->last_lat : null,
                'lng'    => $rider->last_lng !== null ? (float) $rider->last_lng : null,
            ] : null,
        ]);
    }

    public function cancel(Request $request, $id)
    {
        $request->validate(['reason' => 'nullable|string|max:255']);

        $ride = $this->ownRide($id);

        try {
            $ride = $this->rides->cancelByPassenger($ride, $request->input('reason'));
        } catch (RideException $e) {
            return $this->answer($request, false, $e->getMessage(), 422);
        }

        return $this->answer($request, true, __('Trip cancelled. :amount has gone back to your wallet.', [
            'amount' => $this->rides->currency() . ' ' . number_format($ride->refunded_amount, 0),
        ]));
    }

    public function rate(Request $request, $id)
    {
        $request->validate([
            'rating'  => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:500',
        ]);

        $ride = $this->ownRide($id);

        try {
            $this->rides->rate($ride, (int) $request->rating, $request->input('comment'));
        } catch (RideException $e) {
            return $this->answer($request, false, $e->getMessage(), 422);
        }

        return $this->answer($request, true, __('Thanks for the rating.'));
    }

    public function history()
    {
        $this->rides->expireStale();

        return view('frontend.ride.history', [
            'rides'    => Ride::with('rider')
                ->where('passenger_id', $this->userId())
                ->orderByDesc('id')
                ->paginate(15),
            'currency' => $this->rides->currency(),
        ]);
    }

    /* -------------------------------------------------------------- shared */

    /**
     * A trip belonging to the signed-in passenger, or a 404.
     *
     * Trip ids are sequential and guessable, so ownership is a condition of
     * the lookup rather than a check afterwards.
     */
    protected function ownRide($id): Ride
    {
        return Ride::with('rider.user')
            ->where('id', $id)
            ->where('passenger_id', $this->userId())
            ->firstOrFail();
    }

    /** Answer a form post and a fetch() from the same method. */
    protected function answer(Request $request, bool $ok, string $message, int $code = 200)
    {
        if ($request->expectsJson()) {
            return response()->json(['ok' => $ok, 'message' => $message], $ok ? 200 : $code);
        }

        $ok ? toastr_success($message) : toastr_error($message);

        return redirect()->back();
    }
}
