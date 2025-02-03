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
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('controle_estoque')->default(false);
            $table->boolean('controle_saida_estoque')->default(false);
            $table->boolean('gestao_equipe')->default(false);
            $table->boolean('fluxo_caixa')->default(false);
            $table->boolean('dre')->default(false);
            $table->boolean('contas_pagar')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
    }
};
