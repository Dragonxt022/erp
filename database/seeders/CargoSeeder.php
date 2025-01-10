<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cargo; // Adicionando a importação do modelo Cargo

class CargoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Cargos que serão inseridos
        $cargos = [
            'SM',  // sushiman
            'SW',  // sushiwoman
            'AC',  // aux. cozinha
            'R',   // recepcionista
            'CO',  // cozinheira(o)
            'G',   // gerente
            'AD',  // Administrador
            'F',   // financeiro
            'CT',  // contabilidade
            'E'    // entregador
        ];

        // Inserir os cargos na tabela 'cargos'
        foreach ($cargos as $cargo) {
            Cargo::create(['name' => $cargo]);
        }
    }
}
