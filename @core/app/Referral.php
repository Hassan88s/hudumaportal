<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    protected $table = 'referrals';

    protected $guarded = ['id'];

    protected $casts = [
        'stage1_at'  => 'datetime',
        'stage2_at'  => 'datetime',
        'stage3_at'  => 'datetime',
    ];

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referredUser()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function rewards()
    {
        return $this->hasMany(ReferralReward::class);
    }
}
