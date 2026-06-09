<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->string('codigo', 10)->nullable()->after('nome');
        });

        $mapa = [
            // Custos Fixos (grupo 1)
            2   => '1.0.0',  // Internet
            3   => '1.0.1',  // Energia
            4   => '1.0.2',  // Aluguel
            5   => '1.0.3',  // Gás
            6   => '1.0.4',  // Diárias
            19  => '1.0.5',  // Fornecedores
            24  => '1.0.6',  // Contabilidade
            32  => '1.0.7',  // Água
            33  => '1.0.8',  // Seguros
            35  => '1.0.9',  // Adicionais de salário
            151 => '1.1.0',  // Admissão e Demissão

            // Impostos (grupo 2)
            8   => '2.0.0',  // DARE
            9   => '2.0.1',  // DAS
            10  => '2.0.2',  // FGTS
            34  => '2.0.3',  // INSS
            36  => '2.0.4',  // Alvarás e Licenças

            // Custos Variáveis (grupo 3)
            15  => '3.0.0',  // Motoboy

            // Custos Operacionais (grupo 4)
            22  => '4.0.0',  // Compras Diárias
            26  => '4.0.1',  // Softwares
            27  => '4.0.2',  // Marketing
            28  => '4.0.3',  // Monitoramento
            29  => '4.0.4',  // Manutenção Operacional
            30  => '4.0.5',  // Dedetização
            31  => '4.0.6',  // Reposição de Equipamentos

            // CMV (grupo 5)
            21  => '5.0.0',  // Frete Fornecedores
        ];

        foreach ($mapa as $id => $codigo) {
            DB::table('categorias')->where('id', $id)->update(['codigo' => $codigo]);
        }
    }

    public function down(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->dropColumn('codigo');
        });
    }
};
