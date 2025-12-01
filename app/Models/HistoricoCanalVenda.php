<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoricoCanalVenda extends Model
{
    use HasFactory;

    protected $fillable = [
        'unidade_id',
        'responsavel_id',
        'caixa_id',
        'canal_de_vendas_id',
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

    public function canalDeVendas()
    {
        return $this->belongsTo(DefaultCanaisVenda::class, 'canal_de_vendas_id');
    }
}
