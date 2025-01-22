<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultPaymentMethodsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentMethods = [
            ['nome' => 'Cartão de Crédito', 'tipo' => 'credito', 'img_icon' => 'storage/images/cartao_credito.svg'],
            ['nome' => 'Cartão de Débito', 'tipo' => 'debito', 'img_icon' => 'storage/images/cartao_debito.svg'],
            ['nome' => 'Dinheiro', 'tipo' => 'especie', 'img_icon' => 'storage/images/especie.svg'],
            ['nome' => 'Cartão Cabal', 'tipo' => 'cabal', 'img_icon' => 'storage/images/cartao_cabal.svg'],
            ['nome' => 'Cartão Alelo', 'tipo' => 'alelo', 'img_icon' => 'storage/images/cartao_alelo.svg'],
            ['nome' => 'PIX', 'tipo' => 'pix', 'img_icon' => 'storage/images/pix.svg'],
            ['nome' => 'Ticket', 'tipo' => 'ticket', 'img_icon' => 'storage/images/ticket.svg'],
            ['nome' => 'Drex', 'tipo' => 'drex', 'img_icon' => 'storage/images/drex.svg'],
            ['nome' => 'VR Alimentação', 'tipo' => 'vr_alimentacao', 'img_icon' => 'storage/images/vr_alimentacao.svg'],
        ];

        DB::table('default_payment_methods')->insert($paymentMethods);
    }
}
