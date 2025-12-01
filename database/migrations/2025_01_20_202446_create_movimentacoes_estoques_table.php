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
        Schema::create('movimentacoes_estoques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insumo_id')->constrained('lista_produtos')->onDelete('cascade');
            $table->foreignId('fornecedor_id')->constrained('fornecedores')->onDelete('cascade');
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->float('quantidade', 2, 2);
            $table->decimal('preco_insumo', 10, 2);
            $table->enum('operacao', ['Retirada', 'Entrada', 'Saida', 'Ajuste']);
            $table->enum('unidade', ['unidade', 'kg']);
            $table->foreignId('unidade_id')->constrained('infor_unidade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retiradas_estoque');
    }
};
