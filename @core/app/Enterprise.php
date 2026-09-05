<?php
namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enterprise extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'business_type',
        'industry',
        'enterprise_email',
        'phone_number',
        'website',
        'office_address',
        'representative_name',
        'representative_position',
        'representative_email',
        'representative_phone',
        'rejection_reason',
        'status',
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}


?>