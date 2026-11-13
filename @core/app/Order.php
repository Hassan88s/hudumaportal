<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;
use Modules\JobPost\Entities\BuyerJob;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';
    protected $fillable = [
        'invoice',
        'service_id',
        'seller_id',
        'buyer_id',
        'name',
        'email',
        'phone',
        'post_code',
        'address',
        'city',
        'area',
        'country',
        'date',
        'schedule',
        'package_fee',
        'extra_service',
        'sub_total',
        'tax',
        'total',
        'coupon_code',
        'coupon_type',
        'coupon_amount',
        'commission_type',
        'commission_charge',
        'commission_amount',
        'payment_gateway',
        'payment_status',
        'status',
        'is_order_online',
        'order_note',
        'order_complete_request',
        'cancel_order_money_return',
        'manual_payment_image',
        'order_from_job',
        'job_post_id',
        'Custom_offer_id',
        'offer_time_end',
        'partialPayments',
        'partialamount',
        'location_request',
        'latitude',
        'longitude',
    ];
    
    protected $casts = [
        'status' => 'integer',
        'is_order_online' => 'integer'
    ];

    public function service(){
        return $this->belongsTo(Service::class,'service_id','id');
    }

    public function seller(){
        return $this->belongsTo(User::class,'seller_id','id');
    }

    public function buyer(){
        return $this->belongsTo(User::class,'buyer_id','id');
    }

    public function service_city(){
        return $this->belongsTo(ServiceCity::class,'city','id');
    }

    public function service_area(){
        return $this->belongsTo(ServiceArea::class,'area','id');
    }

    public function service_country(){
        return $this->belongsTo(Country::class,'country','id');
    }

    public function orderIncludes(){
        return $this->hasmany(OrderInclude::class,'order_id','id');
    }

    public function orderAdditionals(){
        return $this->hasmany(OrderAdditional::class,'order_id','id');
    }
    public function extraSevices(){
        return $this->hasmany(ExtraService::class,'order_id','id');
    }
    
     public function online_order_ticket(){
        return $this->hasOne(SupportTicket::class,'order_id','id');
    }

    public function job(){
        return $this->belongsTo(BuyerJob::class,'job_post_id','id');
    }

    public function completedeclinehistory(){
        return $this->hasmany(OrderCompleteDecline::class,'order_id','id');
    }
    
     public function partialPayments()
    {
        return $this->hasMany(PartialPayment::class);
    }

    /** status 4 — cancelled, by the buyer or by abandoning the gateway. */
    public const STATUS_CANCELLED = 4;

    /**
     * How long an unpaid order keeps hold of its booking slot.
     *
     * An order row is written before the buyer reaches the payment gateway, so
     * a slot has to be held for long enough that two buyers cannot take it in
     * the same minute. If the payment never completes, the hold lapses and the
     * slot goes back on sale — it used to be held forever, which is how a
     * buyer who closed the gateway tab lost that slot for good.
     */
    public const SLOT_HOLD_MINUTES = 30;

    /** Payment states that mean the money is not coming. */
    public const DEAD_PAYMENT_STATES = ['cancalled', 'cancelled', 'failed', 'refunded', 'declined'];

    /**
     * Orders that genuinely occupy their booking slot.
     *
     * Anything cancelled or failed is out. An order still waiting on the
     * gateway counts only while its hold lasts; everything else — paid, or
     * pending cash on delivery — holds its slot for good.
     */
    public function scopeHoldingSlot($query)
    {
        return $query
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', self::STATUS_CANCELLED);
            })
            ->where(function ($q) {
                $q->whereNull('payment_status')
                  ->orWhereNotIn('payment_status', self::DEAD_PAYMENT_STATES);
            })
            ->where(function ($q) {
                $q->where(function ($paid) {
                      $paid->whereNotNull('payment_status')->where('payment_status', '!=', '');
                  })
                  ->orWhere('created_at', '>=', now()->subMinutes(self::SLOT_HOLD_MINUTES));
            });
    }

    /**
     * The slots already spoken for on one seller's date.
     *
     * @return array<int,string>
     */
    public static function takenSlots($sellerId, $date, $ignoreOrderId = null): array
    {
        return static::query()
            ->where('seller_id', $sellerId)
            ->where('date', $date)
            ->when($ignoreOrderId, fn ($q) => $q->where('id', '!=', $ignoreOrderId))
            ->holdingSlot()
            ->pluck('schedule')
            ->filter(fn ($slot) => $slot !== null && $slot !== '')
            ->unique()
            ->values()
            ->all();
    }
}
