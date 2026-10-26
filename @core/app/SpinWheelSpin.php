<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SpinWheelSpin extends Model
{
    protected $table = 'spin_wheel_spins';

    protected $fillable = [
        'user_id', 'try_number', 'segment_label', 'result_type',
        'result_value', 'is_win', 'code',
    ];

    protected $casts = [
        'is_win'       => 'boolean',
        'result_value' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
