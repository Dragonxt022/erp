<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DefaultCanaisVenda extends Model
{
    use HasFactory;

    // Nome da tabela no banco
    protected $table = 'default_canais_vendas';

    // Campos que podem ser preenchidos
    protected $fillable = [
        'nome',
        'tipo',
        'img_icon',
        'status',
    ];

    // Relacionamento com a tabela `unidade_canais_vendas` 
    public function unidadesCanaisVenda()
    {
        return $this->hasMany(UnidadeCanaisVenda::class, 'canal_venda_method_id');
    }
}
