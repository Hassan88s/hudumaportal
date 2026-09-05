<?php

namespace Modules\Subscription\Entities;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TemproryData extends Model
{
    use HasFactory;

    protected $table = 'temprory_data';
    protected $fillable = [
        'data',
        'seller_id',
      
    ];
    // protected $dates = ['expire_date'];
    
    // protected static function newFactory()
    // {
    //     return \Modules\Subscription\Database\factories\SellerSubscriptionFactory::new();
    // }

    // public function subscription(){
    //     return $this->belongsTo(Subscription::class,'subscription_id','id');
    // }

    public function seller(){
        return $this->belongsTo(User::class,'seller_id','id');
    }
}
