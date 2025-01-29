<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrupoDeCategorias extends Model
{
    protected $table = 'grupo_de_categorias';

    protected $fillable = ['nome'];

    public function categorias()
    {
        return $this->hasMany(Categoria::class, 'grupo_id');
    }
}
