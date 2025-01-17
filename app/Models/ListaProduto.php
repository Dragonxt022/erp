<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListaProduto extends Model
{
    protected $fillable = [
        'nome',
        'profile_photo',
        'categoria',
        'unidadeDeMedida',
    ];

    public function precos()
    {
        return $this->hasMany(PrecoFornecedore::class, 'lista_produto_id');
    }
}
