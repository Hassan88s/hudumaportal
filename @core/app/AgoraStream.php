<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgoraStream extends Model
{
    use HasFactory;

    protected $table = 'agora_streams';

    protected $fillable = [
        'channel_name',
        'host_id',
        'title',
        'description',
        'is_live',
        'started_at',
        'ended_at',
    ];

    // Optional: relationship with User model (assuming you have App\Models\User)
    public function host()
    {
        return $this->belongsTo(User::class, 'host_id');
    }
}
