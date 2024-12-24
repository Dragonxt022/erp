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
            'G' => 'Gerente',
            'CE' => 'Controle de Estoque',
            'SR' => 'Super Resíduos',
            'V' => 'Vouchers',
            'FC' => 'Fluxo de Caixa',
            'D' => 'Despesas',
        ];

        // Retorna o nome completo do cargo baseado nas iniciais
        return isset($mapaCargos[$value]) ? $mapaCargos[$value] : $value;
    }
}
