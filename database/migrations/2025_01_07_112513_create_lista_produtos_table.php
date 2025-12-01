<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateListaProdutosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Criar a tabela lista_produtos
        Schema::create('lista_produtos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('profile_photo')->nullable();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias_produtos')->onDelete('set null');
            $table->boolean('prioridade')->default(false);
            $table->string('unidadeDeMedida')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remover a tabela lista_produtos caso seja necessário reverter a migração
        Schema::dropIfExists('lista_produtos');
    }
}
