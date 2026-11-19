<?php

namespace App\Services;

use App\Exceptions\RideException;
use App\Ride;
use App\Rider;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Wallet\Entities\Wallet;
use Modules\Wallet\Entities\WalletHistory;

/**
 * Everything a trip does, in one place.
 *
 * Two rules shape this class.
 *
 * The first is that the fare is fixed. A passenger is quoted a price before
 * they book, and that is the price charged — no surprise at the end, no
 * recalculation from the route actually taken. Riders do not haggle.
 *
 * The second is that the money is taken up front. The fare leaves the
 * passenger's wallet when the trip is booked and sits there until the trip
 * closes, at which point it either reaches the rider or goes back. That is
 * the only way a wallet-only marketplace can promise a rider they will be
 * paid for a trip they have already made.
 */
class RideService
{
    /* ------------------------------------------------------------ settings */

    public function settings(): array
    {
        return [
            'enabled'     => get_static_option('ride_enabled') == '1',
            'base'        => (float) get_static_option('ride_base_fare'),
            'per_km'      => (float) get_static_option('ride_price_per_km'),
            'per_minute'  => (float) get_static_option('ride_price_per_minute'),
            'minimum'     => (float) get_static_option('ride_minimum_fare'),
            'commission'  => (float) get_static_option('ride_commission_percent'),
            'radius_km'   => (float) (get_static_option('ride_search_radius_km') ?: 5),
            'timeout'     => (int) (get_static_option('ride_request_timeout') ?: 180),
            'cancel_fee'  => (float) get_static_option('ride_cancellation_fee'),
            'road_factor' => (float) (get_static_option('ride_road_factor') ?: 1.35),
            'avg_speed'   => (float) (get_static_option('ride_avg_speed_kmh') ?: 20),
            'rounding'    => (float) (get_static_option('ride_fare_rounding') ?: 1),
        ];
    }

    public function currency(): string
    {
        return get_static_option('currency_symbol') ?: 'TZS';
    }

    /* --------------------------------------------------------------- quote */

    /**
     * What this trip would cost.
     *
     * The straight line between the two points is stretched by the road
     * factor, because no street runs as the crow flies, and the time comes
     * from an assumed average speed rather than from live traffic — this has
     * to produce an answer before the trip exists, with nothing to measure.
     */
    public function quote(float $plat, float $plng, float $dlat, float $dlng): array
    {
        $s = $this->settings();

        $straight = Ride::haversineKm($plat, $plng, $dlat, $dlng);
        $distance = round($straight * max(1.0, $s['road_factor']), 2);

        $speed   = $s['avg_speed'] > 0 ? $s['avg_speed'] : 20;
        $minutes = (int) max(1, ceil(($distance / $speed) * 60));

        $raw = $s['base']
            + ($s['per_km'] * $distance)
            + ($s['per_minute'] * $minutes);

        $fare = max($s['minimum'], $raw);

        // Round up to a note people actually carry, so a fare is never an
        // awkward figure nobody can make change for.
        $fare = $s['rounding'] > 1
            ? ceil($fare / $s['rounding']) * $s['rounding']
            : round($fare, 2);

        return [
            'distance_km'  => $distance,
            'duration_min' => $minutes,
            'fare'         => (float) $fare,
            'currency'     => $this->currency(),
            'breakdown'    => [
                'base'            => $s['base'],
                'distance'        => round($s['per_km'] * $distance, 2),
                'time'            => round($s['per_minute'] * $minutes, 2),
                'minimum'         => $s['minimum'],
                'applied_minimum' => $raw < $s['minimum'],
            ],
        ];
    }

    /* -------------------------------------------------------------- wallet */

    public function walletBalance(int $userId): float
    {
        return (float) (Wallet::where('buyer_id', $userId)->value('balance') ?? 0);
    }

    /**
     * Take money out of a wallet.
     *
     * The balance is read inside the transaction with a row lock, so two
     * requests cannot both see enough money and both spend it.
     */
    protected function debit(int $userId, float $amount, string $gateway, string $action): WalletHistory
    {
        $wallet = Wallet::firstOrCreate(
            ['buyer_id' => $userId],
            ['balance' => 0, 'status' => 1]
        );

        $locked = (float) DB::table('wallets')->where('id', $wallet->id)
            ->lockForUpdate()->value('balance');

        if ($locked + 0.001 < $amount) {
            throw new RideException(__('There is not enough money in your wallet.'));
        }

        DB::table('wallets')->where('id', $wallet->id)->update([
            'balance'    => $locked - $amount,
            'updated_at' => now(),
        ]);

        return WalletHistory::create([
            'buyer_id'        => $userId,
            'amount'          => $amount,
            'payment_gateway' => $gateway,
            'payment_status'  => 'complete',
            'status'          => 1,
            'Action'          => $action,
        ]);
    }

    protected function credit(int $userId, float $amount, string $gateway, string $action): ?WalletHistory
    {
        if ($amount <= 0) {
            return null;
        }

        $wallet = Wallet::firstOrCreate(
            ['buyer_id' => $userId],
            ['balance' => 0, 'status' => 1]
        );

        DB::table('wallets')->where('id', $wallet->id)->update([
            'balance'    => DB::raw('balance + ' . (float) $amount),
            'updated_at' => now(),
        ]);

        return WalletHistory::create([
            'buyer_id'        => $userId,
            'amount'          => $amount,
            'payment_gateway' => $gateway,
            'payment_status'  => 'complete',
            'status'          => 1,
            'Action'          => $action,
        ]);
    }

    /* ----------------------------------------------------------- passenger */

    /** The trip a passenger is in the middle of, if any. */
    public function activeRideFor(int $userId): ?Ride
    {
        $this->expireStale();

        return Ride::with('rider.user')
            ->where('passenger_id', $userId)
            ->live()
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Book a trip.
     *
     * The fare is quoted again here rather than trusted from the form: the
     * price has to come from the settings in force at this moment, not from
     * whatever a browser was told, or from whatever a browser chose to send.
     */
    public function book(int $passengerId, array $input): Ride
    {
        $s = $this->settings();

        if (! $s['enabled']) {
            throw new RideException(__('Ride booking is not available right now.'));
        }

        if ($this->activeRideFor($passengerId)) {
            throw new RideException(__('You already have a trip in progress.'));
        }

        $quote = $this->quote(
            (float) $input['pickup_lat'],
            (float) $input['pickup_lng'],
            (float) $input['dropoff_lat'],
            (float) $input['dropoff_lng']
        );

        if ($quote['distance_km'] <= 0.05) {
            throw new RideException(__('Pick-up and drop-off are the same place.'));
        }

        return DB::transaction(function () use ($passengerId, $input, $quote) {
            $ride = Ride::create([
                'passenger_id'    => $passengerId,
                'status'          => Ride::SEARCHING,
                'pickup_address'  => $input['pickup_address'] ?? null,
                'pickup_lat'      => $input['pickup_lat'],
                'pickup_lng'      => $input['pickup_lng'],
                'dropoff_address' => $input['dropoff_address'] ?? null,
                'dropoff_lat'     => $input['dropoff_lat'],
                'dropoff_lng'     => $input['dropoff_lng'],
                'distance_km'     => $quote['distance_km'],
                'duration_min'    => $quote['duration_min'],
                'fare'            => $quote['fare'],
                'requested_at'    => now(),
            ]);

            $this->debit(
                $passengerId,
                $ride->fare,
                'Ride Booking',
                __('Fare held for trip #:id', ['id' => $ride->id])
            );

            $ride->update(['payment_state' => Ride::PAY_HELD]);

            return $ride->fresh();
        });
    }

    /**
     * The passenger calls it off.
     *
     * Before anybody has accepted, this costs nothing. Once a rider is on
     * their way it costs the cancellation fee, which goes to the rider in
     * full: they have already spent fuel and time on a trip that will not
     * happen, and the house has done nothing to earn a share of that.
     */
    public function cancelByPassenger(Ride $ride, ?string $reason = null): Ride
    {
        if (! $ride->isLive()) {
            throw new RideException(__('That trip has already finished.'));
        }

        if ($ride->status === Ride::ONGOING) {
            throw new RideException(__('The trip has already started.'));
        }

        $s   = $this->settings();
        $fee = $ride->isFreeToCancel() ? 0.0 : min($s['cancel_fee'], $ride->fare);

        return DB::transaction(function () use ($ride, $fee, $reason) {
            $refund = max(0.0, $ride->fare - $fee);

            if ($ride->payment_state === Ride::PAY_HELD) {
                $this->credit(
                    $ride->passenger_id,
                    $refund,
                    'Ride Refund',
                    __('Trip #:id cancelled — fare returned', ['id' => $ride->id])
                );

                if ($fee > 0 && $ride->rider_id) {
                    $riderUserId = Rider::where('id', $ride->rider_id)->value('user_id');
                    if ($riderUserId) {
                        $this->credit(
                            $riderUserId,
                            $fee,
                            'Ride Cancellation Fee',
                            __('Trip #:id cancelled by passenger', ['id' => $ride->id])
                        );
                    }
                }
            }

            $ride->update([
                'status'           => Ride::CANCELLED_PASSENGER,
                'cancel_reason'    => $reason,
                'cancellation_fee' => $fee,
                'refunded_amount'  => $refund,
                'payment_state'    => Ride::PAY_REFUNDED,
                'cancelled_at'     => now(),
            ]);

            return $ride->fresh();
        });
    }

    /** Rate the trip. One rating per trip, and only once it is over. */
    public function rate(Ride $ride, int $stars, ?string $comment = null): Ride
    {
        if ($ride->status !== Ride::COMPLETED) {
            throw new RideException(__('Only a finished trip can be rated.'));
        }

        if ($ride->rating !== null) {
            throw new RideException(__('You have already rated this trip.'));
        }

        $stars = max(1, min(5, $stars));

        return DB::transaction(function () use ($ride, $stars, $comment) {
            $ride->update(['rating' => $stars, 'rating_comment' => $comment]);

            // The rider's average is kept as a running sum so a profile never
            // has to read every trip they have ever made.
            if ($ride->rider_id) {
                DB::table('riders')->where('id', $ride->rider_id)->update([
                    'rating_sum'   => DB::raw('rating_sum + ' . $stars),
                    'rating_count' => DB::raw('rating_count + 1'),
                    'updated_at'   => now(),
                ]);
            }

            return $ride->fresh();
        });
    }

    /* --------------------------------------------------------------- rider */

    /** The trip this rider is currently carrying out, if any. */
    public function activeTripFor(Rider $rider): ?Ride
    {
        return Ride::with('passenger')
            ->where('rider_id', $rider->id)
            ->whereIn('status', [Ride::ACCEPTED, Ride::ARRIVED, Ride::ONGOING])
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Trips a rider could take, nearest first.
     *
     * Distance is worked out in PHP after a cheap bounding-box filter in SQL:
     * the box throws away everything obviously too far using the index, and
     * only the handful left over is measured properly.
     */
    public function openRequestsFor(Rider $rider): Collection
    {
        $this->expireStale();

        if (! $rider->isAvailable()) {
            return collect();
        }

        if ($this->activeTripFor($rider)) {
            return collect();   // one trip at a time
        }

        if ($rider->last_lat === null || $rider->last_lng === null) {
            return collect();   // a rider off the map cannot be sent anywhere
        }

        $s      = $this->settings();
        $radius = $s['radius_km'];

        $lat = (float) $rider->last_lat;
        $lng = (float) $rider->last_lng;

        $latSpan = $radius / 111.0;
        $lngSpan = $radius / max(0.00001, 111.0 * cos(deg2rad($lat)));

        return Ride::open()
            ->where('passenger_id', '!=', $rider->user_id)   // nobody carries themselves
            ->whereBetween('pickup_lat', [$lat - $latSpan, $lat + $latSpan])
            ->whereBetween('pickup_lng', [$lng - $lngSpan, $lng + $lngSpan])
            ->with('passenger')
            ->orderBy('requested_at')
            ->limit(25)
            ->get()
            ->map(function (Ride $ride) use ($lat, $lng) {
                $ride->pickup_distance_km = round(
                    Ride::haversineKm($lat, $lng, (float) $ride->pickup_lat, (float) $ride->pickup_lng),
                    2
                );

                return $ride;
            })
            ->filter(fn (Ride $ride) => $ride->pickup_distance_km <= $radius)
            ->sortBy('pickup_distance_km')
            ->values();
    }

    /**
     * Take a trip.
     *
     * Two riders can tap accept on the same request at the same moment, so
     * the claim is a single conditional update: whoever's statement changes a
     * row has the trip, and the other is told it has gone.
     */
    public function accept(Rider $rider, int $rideId): Ride
    {
        if (! $rider->isAvailable()) {
            throw new RideException(__('Go online before taking a trip.'));
        }

        if ($this->activeTripFor($rider)) {
            throw new RideException(__('Finish your current trip first.'));
        }

        $claimed = Ride::where('id', $rideId)
            ->where('status', Ride::SEARCHING)
            ->whereNull('rider_id')
            ->update([
                'rider_id'    => $rider->id,
                'status'      => Ride::ACCEPTED,
                'accepted_at' => now(),
                'updated_at'  => now(),
            ]);

        if (! $claimed) {
            throw new RideException(__('That trip has already been taken.'));
        }

        return Ride::with('passenger')->findOrFail($rideId);
    }

    /** Move a trip one step on, with the step checked against where it is. */
    public function advance(Rider $rider, Ride $ride, string $to): Ride
    {
        if ($ride->rider_id !== $rider->id) {
            throw new RideException(__('That is not your trip.'));
        }

        $allowed = [
            'arrived' => [[Ride::ACCEPTED], Ride::ARRIVED, 'arrived_at'],
            'start'   => [[Ride::ACCEPTED, Ride::ARRIVED], Ride::ONGOING, 'started_at'],
        ];

        if (! isset($allowed[$to])) {
            throw new RideException(__('Unknown step.'));
        }

        [$from, $status, $stamp] = $allowed[$to];

        if (! in_array($ride->status, $from, true)) {
            throw new RideException(__('The trip has already moved on.'));
        }

        $ride->update(['status' => $status, $stamp => now()]);

        return $ride->fresh();
    }

    /**
     * The trip is over: settle up.
     *
     * The held fare is split by the commission percentage in force now, the
     * rider's share lands in their wallet, and the house keeps the rest. Both
     * figures are written onto the trip, so the books never depend on that
     * setting staying the same.
     */
    public function complete(Rider $rider, Ride $ride): Ride
    {
        if ($ride->rider_id !== $rider->id) {
            throw new RideException(__('That is not your trip.'));
        }

        if ($ride->status !== Ride::ONGOING) {
            throw new RideException(__('Start the trip before finishing it.'));
        }

        $s = $this->settings();

        return DB::transaction(function () use ($ride, $rider, $s) {
            $commission = round($ride->fare * ($s['commission'] / 100), 2);
            $earning    = round($ride->fare - $commission, 2);

            if ($ride->payment_state === Ride::PAY_HELD) {
                $this->credit(
                    $rider->user_id,
                    $earning,
                    'Ride Earning',
                    __('Trip #:id earning', ['id' => $ride->id])
                );
            }

            $ride->update([
                'status'        => Ride::COMPLETED,
                'commission'    => $commission,
                'rider_earning' => $earning,
                'payment_state' => Ride::PAY_SETTLED,
                'completed_at'  => now(),
            ]);

            DB::table('riders')->where('id', $rider->id)->update([
                'total_trips' => DB::raw('total_trips + 1'),
                'updated_at'  => now(),
            ]);

            return $ride->fresh();
        });
    }

    /**
     * The rider drops a trip they had accepted.
     *
     * The passenger is made whole — they did nothing wrong — and the trip is
     * closed rather than put back out for somebody else, so the passenger is
     * told plainly and can decide whether to ask again.
     */
    public function cancelByRider(Rider $rider, Ride $ride, ?string $reason = null): Ride
    {
        if ($ride->rider_id !== $rider->id) {
            throw new RideException(__('That is not your trip.'));
        }

        if (! in_array($ride->status, [Ride::ACCEPTED, Ride::ARRIVED], true)) {
            throw new RideException(__('This trip can no longer be dropped.'));
        }

        return DB::transaction(function () use ($ride, $reason) {
            if ($ride->payment_state === Ride::PAY_HELD) {
                $this->credit(
                    $ride->passenger_id,
                    $ride->fare,
                    'Ride Refund',
                    __('Trip #:id cancelled by rider — fare returned', ['id' => $ride->id])
                );
            }

            $ride->update([
                'status'          => Ride::CANCELLED_RIDER,
                'cancel_reason'   => $reason,
                'refunded_amount' => $ride->fare,
                'payment_state'   => Ride::PAY_REFUNDED,
                'cancelled_at'    => now(),
            ]);

            return $ride->fresh();
        });
    }

    /* -------------------------------------------------------------- rescue */

    /**
     * Close a trip that has got stuck, and give the passenger their money back.
     *
     * A rider who accepts a trip and then simply walks away leaves the fare
     * held with nobody able to release it: the passenger cannot cancel once
     * the trip is marked as moving, and the rider is the only one who can
     * finish it. Somebody has to be able to end that, so an admin can — and
     * the passenger is refunded in full, because a trip nobody completed is
     * not a trip anybody should pay for.
     */
    public function forceClose(Ride $ride, ?string $reason = null): Ride
    {
        if (! $ride->isLive()) {
            throw new RideException(__('That trip has already finished.'));
        }

        return DB::transaction(function () use ($ride, $reason) {
            $refund = 0.0;

            if ($ride->payment_state === Ride::PAY_HELD) {
                $refund = (float) $ride->fare;

                $this->credit(
                    $ride->passenger_id,
                    $refund,
                    'Ride Refund',
                    __('Trip #:id closed by support — fare returned', ['id' => $ride->id])
                );
            }

            $ride->update([
                'status'          => Ride::CANCELLED_RIDER,
                'cancel_reason'   => $reason ?: __('Closed by support'),
                'refunded_amount' => $refund,
                'payment_state'   => Ride::PAY_REFUNDED,
                'cancelled_at'    => now(),
            ]);

            return $ride->fresh();
        });
    }

    /* -------------------------------------------------------------- expiry */

    /**
     * Close requests nobody took, and give the money back.
     *
     * There is no scheduler on this hosting, so this runs on the way through:
     * every screen that looks at trips calls it first. Expiry therefore
     * happens while somebody is watching, which is when it matters.
     */
    public function expireStale(): int
    {
        $timeout = $this->settings()['timeout'];

        $stale = Ride::where('status', Ride::SEARCHING)
            ->where('requested_at', '<', now()->subSeconds($timeout))
            ->limit(50)
            ->get();

        $closed = 0;

        foreach ($stale as $ride) {
            DB::transaction(function () use ($ride, &$closed) {
                // Claim it first, so two requests arriving together cannot
                // both refund the same fare.
                $won = Ride::where('id', $ride->id)
                    ->where('status', Ride::SEARCHING)
                    ->update([
                        'status'       => Ride::EXPIRED,
                        'cancelled_at' => now(),
                        'updated_at'   => now(),
                    ]);

                if (! $won) {
                    return;
                }

                if ($ride->payment_state === Ride::PAY_HELD) {
                    $this->credit(
                        $ride->passenger_id,
                        $ride->fare,
                        'Ride Refund',
                        __('Trip #:id — no rider available, fare returned', ['id' => $ride->id])
                    );

                    Ride::where('id', $ride->id)->update([
                        'refunded_amount' => $ride->fare,
                        'payment_state'   => Ride::PAY_REFUNDED,
                    ]);
                }

                $closed++;
            });
        }

        return $closed;
    }
}
