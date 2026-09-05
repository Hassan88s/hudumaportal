<?php

namespace Modules\Subscription\Entities;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SellerSubscription extends Model
{
    use HasFactory;

    protected $table = 'seller_subscriptions';
    protected $fillable = [
        'subscription_id',
        'seller_id',
        'type',
        'connect',
        'price',
        'initial_connect',
        'initial_service',
        'initial_job',
        'initial_price',
        'Intial_promoted_services',
        'total',
        'expire_date',
        'status',
        'payment_gateway',
        'payment_status',
        'transaction_id',
        'manual_payment_image',
        'note',
        'initialprojects_allowed','initialcashback_percentage','sms_notifications','Website_enabled','personal_enabled','partialpayment_enabled','aidescription_enabled'
    ];
    protected $dates = ['expire_date'];
    
    protected static function newFactory()
    {
        return \Modules\Subscription\Database\factories\SellerSubscriptionFactory::new();
    }

    public function subscription(){
        return $this->belongsTo(Subscription::class,'subscription_id','id');
    }

    public function seller(){
        return $this->belongsTo(User::class,'seller_id','id');
    }
}
