<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoricoMetodoPagamento extends Model
{
    use HasFactory;

    protected $fillable = [
        'unidade_id',
        'responsavel_id',
        'caixa_id',
        'metodo_pagamento_id',
        'valor',
        'data_operacao'
    ];

    public function unidade()
    {
        return $this->belongsTo(InforUnidade::class, 'unidade_id');
    }

    public function responsavel()
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }

    public function caixa()
    {
        return $this->belongsTo(Caixa::class, 'caixa_id');
    }

    public function metodoPagamento()
    {
        return $this->belongsTo(DefaultPaymentMethod::class, 'metodo_pagamento_id');
    }
}
