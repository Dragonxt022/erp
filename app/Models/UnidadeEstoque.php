<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadeEstoque extends Model
{
    use HasFactory;

    protected $table = 'unidade_estoque';

    protected $fillable = [
        'insumo_id',
        'fornecedor_id',
        'usuario_id',
        'quantidade',
        'preco_insumo',
        'operacao',
        'unidade_id',
    ];

    // Relacionamento com insumos (produtos)
    public function insumo()
    {
        return $this->belongsTo(ListaProduto::class, 'insumo_id');
    }

    // Relacionamento com fornecedores
    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'fornecedor_id');
    }

    // Relacionamento com usuários
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Relacionamento com unidades
    public function unidade()
    {
        return $this->belongsTo(InforUnidade::class, 'unidade_id');
    }
}
