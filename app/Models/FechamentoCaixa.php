<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Services\AnalyticService;

class FechamentoCaixa extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::saved(function ($model) {
            AnalyticService::invalidateCache($model->unidade_id);
        });

        static::deleted(function ($model) {
            AnalyticService::invalidateCache($model->unidade_id);
        });
    }

    protected $fillable = [
        'unidade_id',
        'metodo_pagamento_id',
        'caixa_id',
        'valor_total_vendas',
        'valor_taxa_metodo',
    ];

    public function unidade()
    {
        return $this->belongsTo(InforUnidade::class, 'unidade_id');
    }

    public function metodoPagamento()
    {
        return $this->belongsTo(DefaultPaymentMethod::class, 'metodo_pagamento_id');
    }

    public function caixa()
    {
        return $this->belongsTo(Caixa::class, 'caixa_id');
    }
}
