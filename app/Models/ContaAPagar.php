<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Services\AnalyticService;

class ContaAPagar extends Model
{
    protected static function booted()
    {
        static::saved(function ($model) {
            AnalyticService::invalidateCache($model->unidade_id);
        });

        static::deleted(function ($model) {
            AnalyticService::invalidateCache($model->unidade_id);
        });
    }
    protected $table = 'contas_a_pagares';

    protected $fillable = [
        'nome',
        'valor',
        'emitida_em',
        'vencimento',
        'descricao',
        'arquivo',
        'dias_lembrete',
        'status',
        'unidade_id',
        'categoria_id'
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function unidade()
    {
        return $this->belongsTo(InforUnidade::class, 'unidade_id');
    }
}
