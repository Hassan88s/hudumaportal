<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Featureservice extends Model
{
    use HasFactory;

    protected $table = 'feature_service';
    protected $fillable = [
        'id',
        'seller_id',
        'service_id',
        'Payment_status',
        'Payment_gatway',
        'transaction_id',
        'expire_date',
        'price',
        'month'
     
        
    ];
    
    
}