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
            $table->id(); // ID auto incrementável
            $table->string('nome'); // Nome do produto
            $table->string('profile_photo')->nullable(); // Foto do produto (pode ser nula)
            $table->string('categoria')->nullable(); // Categoria do produto (pode ser nula)
            $table->timestamps(); // Colunas created_at e updated_at
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
