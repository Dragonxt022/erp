<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExibirColumnsToCategoriasTable extends Migration
{
    public function up()
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->boolean('exibir_contas_apagar')->default(1);
            $table->boolean('exibir_dre')->default(1);
            $table->boolean('exibir_seletor_caixa')->default(1);
        });
    }

    public function down()
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->dropColumn('exibir_contas_apagar');
            $table->dropColumn('exibir_dre');
            $table->dropColumn('exibir_seletor_caixa');
        });
    }
}