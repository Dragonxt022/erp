<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = [
        'category_id', 'parent_id', 'label', 'icon', 'link', 'is_logout', 'required_permission', 'order'
    ];

    public function children()
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('order');
    }

    public function category()
    {
        return $this->belongsTo(MenuCategory::class, 'category_id');
    }
}


