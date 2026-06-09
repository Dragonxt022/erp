<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class GrupoDeCategorias extends Model
{
    protected $table = 'grupo_de_categorias';

    protected $fillable = ['nome'];

    protected static function boot(): void
    {
        parent::boot();

        $clearCache = fn() => Cache::forget('category_groups_with_categories');

        static::created($clearCache);
        static::updated($clearCache);
        static::deleted($clearCache);
    }

    public function categorias()
    {
        return $this->hasMany(Categoria::class, 'grupo_id');
    }
}
