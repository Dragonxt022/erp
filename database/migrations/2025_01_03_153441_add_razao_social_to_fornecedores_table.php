<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('fornecedores', function (Blueprint $table) {
            $table->string('razao_social')->nullable()->after('email'); // Adiciona a coluna após 'nome'
        });
    }

    public function down()
    {
        Schema::table('fornecedores', function (Blueprint $table) {
            $table->dropColumn('razao_social');
        });
    }
};
