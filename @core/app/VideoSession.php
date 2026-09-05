<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VideoSession extends Model
{
    protected $table = 'video_sessions';

    protected $fillable = [
        'seller_id',
        'buyer_id',
        'session_name',
        'status',
        'started_at',
        'ended_at',
    ];
}
