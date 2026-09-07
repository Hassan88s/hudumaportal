<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReferralReward extends Model
{
    protected $table = 'referral_rewards';

    protected $guarded = ['id'];

    protected $casts = [
        'amount'             => 'decimal:2',
        'protection_ends_at' => 'datetime',
        'approved_at'        => 'datetime',
        'paid_at'            => 'datetime',
        'rejected_at'        => 'datetime',
    ];

    public function referral()
    {
        return $this->belongsTo(Referral::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
