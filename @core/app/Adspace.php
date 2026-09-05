<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;


class Adspace extends Model
{
    use HasFactory;

    protected $table = 'ads_space';
    protected $fillable = [
        'id',
        'ads_space',
        'ads_code_desktop',
        'ads_code_mobile',
        'd_width',
        'd_height',
        'm_width',
        'm_height',
       
    ];
    
}