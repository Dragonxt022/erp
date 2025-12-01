<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cargo extends Model
{
    use HasFactory;

    // Nome da tabela associada ao modelo (opcional se o nome da tabela for o plural do nome do modelo)
    protected $table = 'cargos';

    // Atributos que podem ser preenchidos em massa
    protected $fillable = [
        'nome',
    ];

    // Caso queira configurar o nome da chave primária, se for diferente de 'id'
    // protected $primaryKey = 'id';

    public function users()
    {
        return $this->hasMany(User::class); // Um cargo pode ter muitos usuários
    }
}
