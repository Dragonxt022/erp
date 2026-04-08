<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListaProduto extends Model
{
    protected $fillable = [
        'insumo_id',
        'nome',
        'profile_photo',
        'categoria',
        'categoria_id',
        'prioridade',
        'unidadeDeMedida',
    ];

    public function brokerInsumoId(): string
    {
        return (string) ($this->insumo_id ?: $this->id);
    }

    // Relacionamento pertence a CategoriaProduto
    public function categoriaProduto()
    {
        return $this->belongsTo(CategoriaProduto::class, 'categoria_id');
    }

    public function precos()
    {
        return $this->hasMany(PrecoFornecedore::class, 'lista_produto_id');
    }
}
