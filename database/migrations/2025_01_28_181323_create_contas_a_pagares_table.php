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
        Schema::create('contas_a_pagares', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->decimal('valor', 10, 2);
            $table->date('emitida_em');
            $table->date('vencimento');
            $table->text('descricao')->nullable();
            $table->string('arquivo')->nullable();
            $table->integer('dias_lembrete');
            $table->enum('status', ['pendente', 'pago', 'atrasado'])->default('pendente');
            $table->foreignId('unidade_id')->constrained('infor_unidade')->onDelete('cascade');
            $table->foreignId('categoria_id')->constrained('categorias')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contas_a_pagares');
    }
};
