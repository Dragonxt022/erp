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
        'gestao_salmao',
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

        if (!$permissions) {
            return [
                'controle_estoque' => 0,
                'controle_saida_estoque' => 0,
                'gestao_equipe' => 0,
                'fluxo_caixa' => 0,
                'dre' => 0,
                'contas_pagar' => 0,
                'gestao_salmao'=> 0,
            ];
        }

        return $permissions->only([
            'controle_estoque',
            'controle_saida_estoque',
            'gestao_equipe',
            'fluxo_caixa',
            'dre',
            'contas_pagar',
            'gestao_salmao',
        ]);
    }
}
