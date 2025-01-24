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
        Schema::create('fechamento_caixas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unidade_id')->nullable()->constrained('infor_unidade')->onDelete('set null');
            $table->foreignId('metodo_pagamento_id')->constrained('default_payment_methods')->onDelete('cascade');
            $table->foreignId('caixa_id')->constrained('caixas')->onDelete('cascade');  // Referência ao caixa
            $table->decimal('valor_total_vendas', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fechamento_caixas');
    }
};
