<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SpinWheelSpin extends Model
{
    protected $table = 'spin_wheel_spins';

    protected $fillable = [
        'user_id', 'try_number', 'segment_label', 'result_type',
        'result_value', 'is_win', 'code', 'used_at', 'used_order_id', 'used_context',
        'expires_at',
    ];

    protected $casts = [
        'is_win'       => 'boolean',
        'result_value' => 'float',
        'used_at'      => 'datetime',
        'expires_at'   => 'datetime',
    ];

    /**
     * Codes won before expiry was introduced have no expires_at. Those are
     * left alone rather than retroactively killed — people are holding them.
     */
    public function isExpired(): bool
    {
        return ! empty($this->expires_at) && $this->expires_at->isPast();
    }

    public function isRedeemable(): bool
    {
        return $this->is_win
            && $this->result_type === 'discount'
            && $this->result_value > 0
            && ! empty($this->code)
            && empty($this->used_at)
            && ! $this->isExpired();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
