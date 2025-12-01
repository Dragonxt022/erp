<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;
use Illuminate\Support\Facades\DB as FacadesDB;

class CargosTableSeeder extends Seeder
{
    public function run()
    {
        // Inserir os cargos na tabela 'cargos'
        FacadesDB::table('cargos')->insert([
            ['nome' => 'FRANQUEADO'],
            ['nome' => 'SUSHIWOMAN'],
            ['nome' => 'SUSHIMAN'],
            ['nome' => 'AUX. COZINHA'],
            ['nome' => 'RECEPCIONISTA'],
            ['nome' => 'GERENTE'],
            ['nome' => 'CONTABILIDADE'],
            ['nome' => 'ENTREGADOR'],
        ]);
    }
}
