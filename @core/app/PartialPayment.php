<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PartialPayment extends Model
{
    protected $fillable = [
        'order_id',
        'amount',
        'percentage',
        'status',
    ];
    
     public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
