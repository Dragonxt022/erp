<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'controle_estoque',
        'controle_saida_estoque',
        'gestao_equipe',
        'fluxo_caixa',
        'dre',
        'contas_pagar',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Método para retornar as permissões de um usuário específico
     *
     * @param int $userId
     * @return array
     */
    public static function getPermissions(int $userId)
    {
        $permissions = self::where('user_id', $userId)->first();

        // Se não encontrar permissões, retornar permissões padrão
        if (!$permissions) {
            return [
                'controle_estoque' => false,
                'controle_saida_estoque' => false,
                'gestao_equipe' => false,
                'fluxo_caixa' => false,
                'dre' => false,
                'contas_pagar' => false,
            ];
        }

        return $permissions->only([
            'controle_estoque',
            'controle_saida_estoque',
            'gestao_equipe',
            'fluxo_caixa',
            'dre',
            'contas_pagar',
        ]);
    }
}
