<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('categorias')->insert([
            'nome'                 => 'Admissão e Demissão',
            'grupo_id'             => 1,
            'exibir_contas_apagar' => 1,
            'exibir_dre'           => 1,
            'exibir_seletor_caixa' => 0,
        ]);
    }

    public function down(): void
    {
        DB::table('categorias')->where('nome', 'Admissão e Demissão')->delete();
    }
};
