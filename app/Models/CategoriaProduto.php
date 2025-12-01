<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaProduto extends Model
{
    use HasFactory;

    protected $table = 'categorias_produtos'; // Nome da tabela

    protected $fillable = ['nome', 'status'];

    // Relacionamento um para muitos com ListaProduto
    public function listaProdutos()
    {
        return $this->hasMany(ListaProduto::class, 'categoria_id'); // categoria_id é a chave estrangeira em ListaProduto
    }

    public function unidadeEstoque()
    {
        return $this->hasMany(UnidadeEstoque::class, 'categoria_id'); // categoria_id é a chave estrangeira em ListaProduto
    }
}
