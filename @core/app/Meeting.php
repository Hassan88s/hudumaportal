<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    protected $table = 'meetings';

    protected $fillable = [
        'seller_id',
        'buyer_id',
        'meeting_id',
        'topic',
        'join_url',
        'start_url',
        'status',
    ];
}
