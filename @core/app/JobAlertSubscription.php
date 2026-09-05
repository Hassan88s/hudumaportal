<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class JobAlertSubscription extends Model
{
    use HasFactory;
    
    protected $table = 'job_alert_subscriptions';
    protected $fillable = ['freelancer_id','category_id'];

    
 public function freelancer()
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }

}
