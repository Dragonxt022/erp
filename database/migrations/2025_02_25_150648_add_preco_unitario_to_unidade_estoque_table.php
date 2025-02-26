<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPrecoUnitarioToUnidadeEstoqueTable extends Migration
{
    public function up()
    {
        Schema::table('unidade_estoque', function (Blueprint $table) {
            $table->decimal('preco_unitario', 10, 2)->nullable();
        });
    }

    public function down()
    {
        Schema::table('unidade_estoque', function (Blueprint $table) {
            $table->dropColumn('preco_unitario');
        });
    }
}