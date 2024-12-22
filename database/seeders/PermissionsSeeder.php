<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Inserir as permissões iniciais na tabela 'permissions'
        DB::table('permissions')->insert([
            ['name' => 'CE'],  // Controle de Estoque
            ['name' => 'SR'],  // Super Resíduos
            ['name' => 'V'],   // Vouchers
            ['name' => 'FC'],  // Fluxo de Caixa
            ['name' => 'D'],   // Despesas
        ]);
    }
}
