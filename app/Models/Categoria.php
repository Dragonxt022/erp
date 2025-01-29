<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $table = 'categorias';

    protected $fillable = ['nome', 'grupo_id'];

    public function grupo()
    {
        return $this->belongsTo(GrupoDeCategorias::class, 'grupo_id');
    }



    public function contas()
    {
        return $this->hasMany(ContaAPagar::class, 'categoria_id');
    }
}
