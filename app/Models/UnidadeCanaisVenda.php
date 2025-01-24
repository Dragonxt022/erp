<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadeCanaisVenda extends Model
{
    use HasFactory;

    // Nome da tabela
    protected $table = 'unidade_canais_vendas';

    // Campos que podem ser preenchidos
    protected $fillable = [
        'unidade_id',
        'canal_de_vendas_id',  // Alterado para o nome correto da chave estrangeira
        'porcentagem',
        'status',
    ];

    // Relacionamento com a tabela `infor_unidade`
    public function unidade()
    {
        return $this->belongsTo(InforUnidade::class, 'unidade_id');
    }

    // Relacionamento com a tabela `default_canais_vendas` 
    public function canalVenda()
    {
        return $this->belongsTo(DefaultCanaisVenda::class, 'canal_de_vendas_id');  // Relaciona com a chave estrangeira correta
    }
}
