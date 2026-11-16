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

    /**
     * The project this image belongs to.
     *
     * The column is called portfolio_id but it points at projects.id, which
     * is why this has to name the key explicitly.
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'portfolio_id');
    }
}
