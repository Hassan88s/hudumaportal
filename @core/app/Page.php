<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $table = 'pages';
    protected $fillable = ['title','slug','page_content','status','visibility','page_builder_status','layout','sidebar_layout','navbar_variant',
        'page_class','back_to_top','breadcrumb_status','footer_variant','widget_style','left_column','right_column'];
    
    
     protected $casts = [
        'title_translations' => 'array',
        'page_content_translations' => 'array',
    ];

    // Usage: {{ $page->title_t }}
    public function getTitleTAttribute(): string
    {
        $t = $this->title_translations ?? [];
        $locale = app()->getLocale();

        return $t[$locale] ?? $t['en'] ?? (string)($this->attributes['title'] ?? '');
    }

    // Usage: {!! $page->page_content_t !!}
    public function getPageContentTAttribute(): string
    {
        $t = $this->page_content_translations ?? [];
        $locale = app()->getLocale();

        return $t[$locale] ?? $t['en'] ?? (string)($this->attributes['page_content'] ?? '');
    }
    
    public function meta_data(){
        return $this->morphOne(MetaData::class,'meta_taggable');
    }
}
