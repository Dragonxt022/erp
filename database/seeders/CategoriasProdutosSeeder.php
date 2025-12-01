<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriasProdutosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            'Carnes',
            'Receitas',
            'Mercearia',
            'Legumes',
            'Utensílios',
            'Molhos',
            'Óleo',
            'Limpeza',
            'Peixes',
            'Frutos do Mar',
            'Embalagens',
            'Salmão Caixa',
            'Salmão Limpo',
        ];

        foreach ($categorias as $categoria) {
            DB::table('categorias_produtos')->insert([
                'nome' => $categoria,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
