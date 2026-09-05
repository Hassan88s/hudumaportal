<?php

namespace Modules\JobPost\Entities;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobPackage extends Model
{
    use HasFactory;

    protected $table = 'job_packages';

    protected $fillable = ['name', 'price'];
}

?>