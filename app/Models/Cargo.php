<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cargo extends Model
{
    use HasFactory;

    // Definir os campos que podem ser preenchidos
    protected $fillable = ['name'];

    // Relacionamento com a tabela de usuários
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
