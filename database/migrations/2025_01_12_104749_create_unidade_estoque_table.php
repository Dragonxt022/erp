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
        Schema::create('unidade_estoque', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insumo_id')->constrained('lista_produtos')->onDelete('cascade');
            $table->foreignId('fornecedor_id')->constrained('fornecedores')->onDelete('cascade');
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->decimal('quantidade', 10, 3);
            $table->decimal('preco_insumo', 10, 2);
            $table->enum('operacao', ['Retirada', 'Entrada', 'Saida']);
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
        Schema::dropIfExists('unidade_estoque');
    }
};
