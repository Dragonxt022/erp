<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContasSeeder extends Seeder
{
    public function run()
    {
        // Grupos de categorias
        $grupos = [
            'Custos Fixos',
            'Impostos',
            'Variáveis',
            'Outros',
        ];

        foreach ($grupos as $grupo) {
            $grupoId = DB::table('grupo_de_categorias')->insertGetId([
                'nome' => $grupo,
            ]);

            // Categorias para cada grupo
            $categorias = match ($grupo) {
                'Custos Fixos' => [

                    'Internet',
                    'Energia',
                    'Aluguel',
                    'Gás',
                    'Diárias',
                    'Contabilidade',
                ],
                'Impostos' => [
                    'DARE',
                    'DAS',
                    'FGTS',
                ],
                'Variáveis' => [
                    'Folha de pagamento',
                    'Royalties',
                    'Fundo de propaganda',
                    'Taxa de Crédito',
                    'Taxa de Débito',
                    'Motoboy',
                    'Plataformas de Delivery',
                    'Voucher Alimentação',
                ],
                'Outros' => [
                    'Outros',
                ],
                default => [],
            };

            foreach ($categorias as $categoria) {
                DB::table('categorias')->insert([
                    'nome' => $categoria,
                    'grupo_id' => $grupoId,
                ]);
            }
        }
    }
}
