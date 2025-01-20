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
        Schema::create('historico_pedidos', function (Blueprint $table) {
            $table->id();
            $table->enum('status_pedido', ['enviado', 'pendente'])->default('pendente');
            $table->foreignId('usuario_responsavel_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('unidade_id')->constrained('infor_unidade')->onDelete('cascade');
            $table->foreignId('fornecedor_id')->constrained('fornecedores')->onDelete('cascade');
            $table->integer('valor_total_carrinho'); // Em centavos (como um inteiro)
            $table->json('itens_id'); // Usando string para armazenar JSON
            $table->string('quantidade'); // Usando string para armazenar JSON
            $table->string('valor_unitario'); // Usando string para armazenar JSON
            $table->string('valor_total_item'); // Usando string para armazenar JSON
            $table->string('nome_primeiro_fornecedor')->nullable(); // Usando string para armazenar JSON
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
        Schema::dropIfExists('historico_pedidos');
    }
};
