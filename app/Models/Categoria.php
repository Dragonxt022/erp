<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Categoria extends Model
{
    protected $table = 'categorias';

    protected $fillable = [
        'nome',
        'grupo_id',
        'exibir_contas_apagar',
        'exibir_dre',
        'exibir_seletor_caixa'
    ];

    protected static function boot(): void
    {
        parent::boot();

        $clearCache = fn() => Cache::forget('category_groups_with_categories');

        static::created($clearCache);
        static::updated($clearCache);
        static::deleted($clearCache);
    }

    public function grupo()
    {
        return $this->belongsTo(GrupoDeCategorias::class, 'grupo_id');
    }

    public function contas()
    {
        return $this->hasMany(ContaAPagar::class, 'categoria_id');
    }
}