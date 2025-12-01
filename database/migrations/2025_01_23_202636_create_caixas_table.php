<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('caixas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unidade_id')->nullable()->constrained('infor_unidade')->onDelete('set null');
            $table->foreignId('responsavel_id')->constrained('users')->onDelete('cascade');  // Relacionamento com o usuário
            $table->decimal('valor_inicial', 10, 2);  // Valor inicial ao abrir o caixa
            $table->decimal('valor_final', 10, 2)->nullable();    // Valor final ao fechar o caixa
            $table->boolean('status')->default(true);  // Caixa aberto ou fechado
            $table->string('motivo');  // Motivo da operação (caixa aberto, caixa fechado)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caixas');
    }
};
