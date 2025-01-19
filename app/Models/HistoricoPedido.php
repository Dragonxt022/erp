<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoricoPedido extends Model
{
    use HasFactory;

    // A tabela associada ao modelo
    protected $table = 'historico_pedidos';

    // Definindo os campos que podem ser atribuídos em massa
    protected $fillable = [
        'status_pedido',
        'itens_id',
        'quantidade',
        'valor_unitario',
        'valor_total_item',
        'valor_total_carrinho',
        'unidade_id',
        'usuario_responsavel_id',
        'fornecedor_id',
    ];

    // Cast dos campos JSON
    protected $casts = [
        'itens_id' => 'array',
        'quantidade' => 'array',
        'valor_unitario' => 'array',
        'valor_total_item' => 'array',
    ];

    // Relacionamento com usuário responsável
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_responsavel_id');
    }

    // Relacionamento com unidade
    public function unidade()
    {
        return $this->belongsTo(InforUnidade::class, 'unidade_id');
    }


    // Relacionamento com fornecedor
    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'fornecedor_id');
    }
}
