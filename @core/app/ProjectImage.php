<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectImage extends Model
{
    use HasFactory;

    protected $table = 'projects_images';

    protected $fillable = [
        'portfolio_id',
        'image',
    ];

    
}
