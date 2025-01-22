<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultCanaisVendasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('default_canais_vendas')->insert([
            [
                'nome' => 'iFood',
                'tipo' => 'ifood',
                'status' => true,
                'img_icon' => 'storage/images/ifood_icon.svg',  // Se tiver um ícone
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Anota Aí',
                'tipo' => 'anota_ai',
                'status' => true,
                'img_icon' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Delivery Much',
                'tipo' => 'delivery_much',
                'status' => true,
                'img_icon' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'AiQFome',
                'tipo' => 'aiqfome',
                'status' => true,
                'img_icon' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
