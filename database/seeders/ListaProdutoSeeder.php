<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ListaProdutoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 30; $i++) {
            DB::table('lista_produtos')->insert([
                'nome' => 'Produto ' . $i,
                'imagem' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
