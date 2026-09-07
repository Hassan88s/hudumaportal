<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReferralClick extends Model
{
    protected $table = 'referral_clicks';

    protected $guarded = ['id'];

    public $timestamps = false; // only has created_at

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }
}
