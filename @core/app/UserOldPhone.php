<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserOldPhone extends Model
{
    protected $table = 'user_old_phones';

    protected $fillable = [
        'user_id',
        'old_phone',
    ];

    // Optional: define relationship to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
