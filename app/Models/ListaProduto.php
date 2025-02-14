<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListaProduto extends Model
{
    protected $fillable = [
        'nome',
        'profile_photo',
        'categoria',
        'categoria_id',
        'prioridade',
        'unidadeDeMedida',
    ];

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
