<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrecosFornecedoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Criar a tabela precos_fornecedores
        Schema::create('precos_fornecedores', function (Blueprint $table) {
            $table->id(); // ID auto incrementável
            $table->foreignId('lista_produto_id')->constrained('lista_produtos')->onDelete('cascade'); // Relacionamento com a tabela lista_produtos
            $table->foreignId('fornecedor_id')->constrained('fornecedores')->onDelete('cascade'); // Relacionamento com a tabela fornecedores
            $table->float('quantidade', 10, 2)->nullable(); // Quantidade do produto
            $table->decimal('preco_unitario', 10, 2)->nullable(); // Preço unitário do produto
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
        // Remover a tabela precos_fornecedores caso seja necessário reverter a migração
        Schema::dropIfExists('precos_fornecedores');
    }
}
