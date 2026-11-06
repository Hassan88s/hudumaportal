<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SpinWheelSpin extends Model
{
    protected $table = 'spin_wheel_spins';

    protected $fillable = [
        'user_id', 'try_number', 'segment_label', 'result_type',
        'result_value', 'is_win', 'code', 'used_at', 'used_order_id', 'used_context',
    ];

    protected $casts = [
        'is_win'       => 'boolean',
        'result_value' => 'float',
        'used_at'      => 'datetime',
    ];

    public function isRedeemable(): bool
    {
        return $this->is_win
            && $this->result_type === 'discount'
            && $this->result_value > 0
            && ! empty($this->code)
            && empty($this->used_at);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
