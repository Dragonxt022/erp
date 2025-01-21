<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimentacoesEstoque extends Model
{
    use HasFactory;

    protected $table = 'movimentacoes_estoques';

    protected $fillable = [
        'insumo_id',
        'fornecedor_id',
        'usuario_id',
        'quantidade',
        'preco_insumo',
        'operacao',
        'unidade',
        'unidade_id'




    ];

    // Relacionamento com o insumo (produto)
    public function insumo()
    {
        return $this->belongsTo(ListaProduto::class, 'insumo_id');
    }

    // Relacionamento com o fornecedor
    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'fornecedor_id');
    }

    // Relacionamento com o usuário (quem realizou a operação)
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Relacionamento com a unidade
    public function unidade()
    {
        return $this->belongsTo(InforUnidade::class, 'unidade_id');
    }
}
