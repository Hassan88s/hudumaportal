<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A user who has applied to carry passengers.
 *
 * One row per user. The row exists from the moment somebody applies, so
 * "has a rider row" means "has applied", not "is allowed to ride" — that is
 * what status says.
 */
class Rider extends Model
{
    use HasFactory;

    public const PENDING   = 0;
    public const APPROVED  = 1;
    public const REJECTED  = 2;
    public const SUSPENDED = 3;

    protected $fillable = [
        'user_id',
        'status',
        'full_name',
        'phone',
        'national_id',
        'national_id_photo',
        'licence_number',
        'licence_expiry',
        'licence_photo',
        'bike_plate',
        'bike_model',
        'bike_photo',
        'bike_reg_photo',
        'insurance_photo',
        'rejection_reason',
        'submitted_at',
        'approved_at',
        'approved_by',
        'is_online',
        'last_lat',
        'last_lng',
        'last_location_at',
        'total_trips',
        'rating_sum',
        'rating_count',
    ];

    protected $casts = [
        'user_id'          => 'integer',
        'status'           => 'integer',
        'is_online'        => 'boolean',
        'total_trips'      => 'integer',
        'rating_sum'       => 'integer',
        'rating_count'     => 'integer',
        'licence_expiry'   => 'date',
        'submitted_at'     => 'datetime',
        'approved_at'      => 'datetime',
        'last_location_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isApproved(): bool
    {
        return $this->status === self::APPROVED;
    }

    /**
     * Only an approved rider can be online, so suspending somebody mid-shift
     * takes them off the map without a second flag to remember.
     */
    public function isAvailable(): bool
    {
        return $this->isApproved() && $this->is_online;
    }

    public function statusLabel(): string
    {
        return [
            self::PENDING   => __('Waiting for approval'),
            self::APPROVED  => __('Approved'),
            self::REJECTED  => __('Not approved'),
            self::SUSPENDED => __('Suspended'),
        ][$this->status] ?? __('Unknown');
    }

    public function averageRating(): float
    {
        return $this->rating_count > 0
            ? round($this->rating_sum / $this->rating_count, 2)
            : 0.0;
    }

    /**
     * Whether this rider's ratings are bad enough to be worth a look.
     *
     * Needs a handful of ratings before it says anything: one passenger
     * having a bad morning is not a pattern, and a rider on their second
     * trip should not be flagged because somebody tapped one star.
     */
    public function isPoorlyRated(float $below, int $minRatings = 5): bool
    {
        return $this->rating_count >= $minRatings
            && $this->averageRating() > 0
            && $this->averageRating() < $below;
    }

    /** Documents the application cannot be submitted without. */
    public static function requiredDocuments(): array
    {
        return ['national_id_photo', 'licence_photo', 'bike_reg_photo'];
    }

    public function missingDocuments(): array
    {
        return array_values(array_filter(
            self::requiredDocuments(),
            fn ($field) => empty($this->{$field})
        ));
    }
}
