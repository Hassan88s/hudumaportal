<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $table = 'projects';

    protected $fillable = [
        'freelancer_id',
        'name',
        'description',
        'video',
        'slug',
        'cate_id',
    ];

    public function images()
    {
          return $this->hasMany(ProjectImage::class, 'portfolio_id', 'id');
    }
                public function votes() {
                return $this->hasMany(Vote::class);
            }
            
            public function voteCount() {
                return $this->votes()->count();
            }
            
            public function isVotedBy($user) {
                 if (!$user) return false;
                return $this->votes()->where('user_id', $user->id)->exists();
            }
            
            public function comments()
        {
            return $this->hasMany(Comment::class)->whereNull('parent_id');
        }
    
}
