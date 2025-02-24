<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalmaoCalibresSeeder extends Seeder
{
    public function run()
    {
        DB::table('salmao_calibres')->insert([
            [
                'nome' => 'Salmão 10/12',
                'tipo' => 'salmao_10_12',
            ],
            [
                'nome' => 'Salmão 12/14',
                'tipo' => 'salmao_12_14',
            ],
        ]);
    }
}
