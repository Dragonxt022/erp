<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ponto;

class PontoSeeder extends Seeder
{
    public function run()
    {
        $criterios = [
            ['name' => 'Iniciou o bloco', 'pontos' => 6],
            ['name' => 'Iniciou o bloco atrasado', 'pontos' => -1],
            ['name' => 'Terminou o bloco atrasado', 'pontos' => -1],
            ['name' => 'Atividades incompletas', 'pontos' => -2],
            ['name' => 'Recebeu ajuda', 'pontos' => -2],
            ['name' => 'Concluiu o bloco adiantado', 'pontos' => 2],
            ['name' => 'Ajudou outro colaborador', 'pontos' => 2],
            ['name' => 'Iniciou 10 minutos adiantado', 'pontos' => 2],
            ['name' => 'Concluiu todas atividades', 'pontos' => 2],
        ];

        foreach ($criterios as $criterio) {
            Ponto::updateOrCreate(
                ['name' => $criterio['name']],
                ['pontos' => $criterio['pontos']]
            );
        }
    }
}
