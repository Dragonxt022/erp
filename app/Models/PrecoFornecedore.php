<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrecoFornecedore extends Model
{
    protected $table = 'precos_fornecedores';

    protected $fillable = [
        'lista_produto_id', // Relacionamento com o produto
        'fornecedor_id', // Relacionamento com o fornecedor
        'quantidade', // Quantidade do produto
        'qtd_minima',
        'preco_unitario', // Preço unitário
    ];

    // Relacionamento com a tabela lista_produtos
    public function produto()
    {
        return $this->belongsTo(ListaProduto::class, 'lista_produto_id');
    }

    // Relacionamento com a tabela fornecedores
    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'fornecedor_id', 'id');
    }
}
