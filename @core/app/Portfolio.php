<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    use HasFactory;

    protected $table = 'portfolios';

    protected $fillable = [
        'freelancer_id',
        'name',
        'description',
        'video',
        'timeline',
        'project_cost'
    ];

    public function images()
    {
        return $this->hasMany(PortfolioImage::class, 'portfolio_id');
    }
}
