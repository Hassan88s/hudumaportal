<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One trip, from the moment a passenger asks for a bike to the moment the
 * money has moved.
 *
 * The fare is decided once, when the trip is booked, and charged unchanged.
 * Nothing on this row is recalculated later: what the passenger was shown is
 * what they pay, and the split between the rider and the house is written
 * down at completion rather than worked out again on every report.
 */
class Ride extends Model
{
    use HasFactory;

    /* The trip's own progress. */
    public const SEARCHING            = 0;
    public const ACCEPTED             = 1;
    public const ARRIVED              = 2;
    public const ONGOING              = 3;
    public const COMPLETED            = 4;
    public const CANCELLED_PASSENGER  = 5;
    public const CANCELLED_RIDER      = 6;
    public const EXPIRED              = 7;

    /* What has happened to the money. */
    public const PAY_NONE     = 0;
    public const PAY_HELD     = 1;
    public const PAY_SETTLED  = 2;
    public const PAY_REFUNDED = 3;

    /** Statuses in which the trip is still going on. */
    public const LIVE = [self::SEARCHING, self::ACCEPTED, self::ARRIVED, self::ONGOING];

    /** Statuses in which nothing more will happen. */
    public const CLOSED = [
        self::COMPLETED,
        self::CANCELLED_PASSENGER,
        self::CANCELLED_RIDER,
        self::EXPIRED,
    ];

    protected $fillable = [
        'passenger_id', 'rider_id', 'status',
        'pickup_address', 'pickup_lat', 'pickup_lng',
        'dropoff_address', 'dropoff_lat', 'dropoff_lng',
        'distance_km', 'duration_min',
        'fare', 'commission', 'rider_earning', 'cancellation_fee', 'refunded_amount',
        'payment_state', 'cancel_reason',
        'rating', 'rating_comment',
        'passenger_rating', 'passenger_rating_comment',
        'requested_at', 'accepted_at', 'arrived_at', 'started_at', 'completed_at', 'cancelled_at',
    ];

    protected $casts = [
        'passenger_id'     => 'integer',
        'rider_id'         => 'integer',
        'status'           => 'integer',
        'payment_state'    => 'integer',
        'duration_min'     => 'integer',
        'rating'           => 'integer',
        'passenger_rating' => 'integer',
        'pickup_lat'       => 'float',
        'pickup_lng'       => 'float',
        'dropoff_lat'      => 'float',
        'dropoff_lng'      => 'float',
        'distance_km'      => 'float',
        'fare'             => 'float',
        'commission'       => 'float',
        'rider_earning'    => 'float',
        'cancellation_fee' => 'float',
        'refunded_amount'  => 'float',
        'requested_at'     => 'datetime',
        'accepted_at'      => 'datetime',
        'arrived_at'       => 'datetime',
        'started_at'       => 'datetime',
        'completed_at'     => 'datetime',
        'cancelled_at'     => 'datetime',
    ];

    public function passenger()
    {
        return $this->belongsTo(User::class, 'passenger_id');
    }

    public function rider()
    {
        return $this->belongsTo(Rider::class, 'rider_id');
    }

    /* ----------------------------------------------------------- questions */

    public function isLive(): bool
    {
        return in_array($this->status, self::LIVE, true);
    }

    public function isClosed(): bool
    {
        return in_array($this->status, self::CLOSED, true);
    }

    /** True while nobody has taken the trip yet. */
    public function isSearching(): bool
    {
        return $this->status === self::SEARCHING;
    }

    /** Whether the passenger may still call it off without paying anything. */
    public function isFreeToCancel(): bool
    {
        return $this->status === self::SEARCHING;
    }

    public function statusLabel(): string
    {
        return [
            self::SEARCHING           => __('Looking for a rider'),
            self::ACCEPTED            => __('Rider on the way'),
            self::ARRIVED             => __('Rider is waiting for you'),
            self::ONGOING             => __('On the way'),
            self::COMPLETED           => __('Completed'),
            self::CANCELLED_PASSENGER => __('Cancelled by passenger'),
            self::CANCELLED_RIDER     => __('Cancelled by rider'),
            self::EXPIRED             => __('No rider was available'),
        ][$this->status] ?? __('Unknown');
    }

    /* -------------------------------------------------------------- scopes */

    public function scopeLive($query)
    {
        return $query->whereIn('status', self::LIVE);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', self::SEARCHING)->whereNull('rider_id');
    }

    /**
     * Straight-line kilometres between two points.
     *
     * Used both to price a trip and to decide which riders are near enough to
     * be shown it, so it lives here rather than in either caller.
     */
    public static function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earth = 6371.0;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earth * 2 * atan2(sqrt($a), sqrt(max(0.0, 1 - $a)));
    }
}
