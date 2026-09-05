<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomOffer extends Model
{
    use HasFactory;

    protected $table = 'Custom_offer';
    protected $fillable = [
        'id',
        'seller_id',
        'buyer_id',
        'title',
        'price',
        'end_date',
        'description',
        'cjob_timelimit'
        
    ];
    
    
}