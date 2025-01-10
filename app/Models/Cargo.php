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

    /**
     * Acessor para obter o nome completo do cargo baseado nas iniciais.
     */
    public function getNameAttribute($value)
    {
        $mapaCargos = [
            'SM' => 'Sushiman',
            'SW' => 'Sushiwoman',
            'AC' => 'Auxiliar de Cozinha',
            'R'  => 'Recepcionista',
            'CO' => 'Cozinheira(o)',
            'G'  => 'Gerente',
            'AD' => 'Administrador',
            'F'  => 'Financeiro',
            'CT' => 'Contabilidade',
            'E'  => 'Entregador',
        ];

        // Retorna o nome completo do cargo baseado nas iniciais
        return isset($mapaCargos[$value]) ? $mapaCargos[$value] : $value;
    }
}
