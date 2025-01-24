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
        Schema::create('historico_metodos_pagamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unidade_id')->nullable()->constrained('infor_unidade')->onDelete('set null');
            $table->foreignId('responsavel_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('caixa_id')->constrained('caixas')->onDelete('cascade');  // Referência ao caixa
            $table->foreignId('metodo_pagamento_id')->constrained('default_payment_methods')->onDelete('cascade');  // Referência ao método de pagamento
            $table->decimal('valor', 10, 2);
            $table->timestamp('data_operacao');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historico_metodos_pagamentos');
    }
};
