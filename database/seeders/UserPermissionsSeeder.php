<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserPermission;

class UserPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Busca todos os usuários cadastrados
        $users = User::all();

        foreach ($users as $user) {
            UserPermission::updateOrCreate(
                ['user_id' => $user->id], // Verifica se já existe um registro para esse usuário
                [
                    'controle_estoque' => false,
                    'controle_saida_estoque' => false,
                    'gestao_equipe' => false,
                    'fluxo_caixa' => false,
                    'dre' => false,
                    'contas_pagar' => false,
                ]
            );
        }
    }
}
