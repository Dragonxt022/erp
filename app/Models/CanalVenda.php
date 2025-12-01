<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CanalVenda extends Model
{
    use HasFactory;

    protected $table = 'canal_vendas';

    protected $fillable = [
        'unidade_id',
        'canal_de_vendas_id',
        'caixa_id',
        'valor_total_vendas',
        'quantidade_vendas_feitas',
        'valor_taxa_canal',
    ];

    public function unidade()
    {
        return $this->belongsTo(InforUnidade::class, 'unidade_id');
    }

    public function canalDeVendas()
    {
        return $this->belongsTo(DefaultCanaisVenda::class, 'canal_de_vendas_id');
    }


    public function caixa()
    {
        return $this->belongsTo(Caixa::class, 'caixa_id');
    }
}
