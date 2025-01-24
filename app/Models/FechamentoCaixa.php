<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FechamentoCaixa extends Model
{
    use HasFactory;

    protected $fillable = [
        'unidade_id',
        'metodo_pagamento_id',
        'caixa_id',
        'valor_total_vendas'
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
