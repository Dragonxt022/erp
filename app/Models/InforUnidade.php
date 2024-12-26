<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InforUnidade extends Model
{
    use HasFactory;

    protected $table = 'infor_unidade';  // Definir o nome da tabela, caso seja diferente do padrão

    protected $fillable = [
        'cep',
        'cidade',
        'bairro',
        'rua',
        'numero',
        'cnpj',
    ];

     // Relacionamento com os usuários
     public function users()
     {
         return $this->hasMany(User::class);  // Uma unidade pode ter muitos usuários
     }


}
