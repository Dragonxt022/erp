<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDetails extends Model
{
    use HasFactory;

    // A tabela no banco de dados
    protected $table = 'user_details';

    // Definindo os campos que podem ser preenchidos
    protected $fillable = [
        'user_id',  // Relação com o usuário
        'cep',      // CEP
        'cidade',   // Cidade
        'bairro',   // Bairro
        'rua',      // Rua
        'numero',   // Número
    ];

    // Definindo a relação com o modelo User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

