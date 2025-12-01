<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contas_a_pagares', function (Blueprint $table) {
            // Altera o enum para adicionar 'Agendada'
            $table->enum('status', ['agendada', 'pendente', 'pago', 'atrasado'])
                  ->default('agendada')
                  ->change();
        });
    }

    public function down(): void
    {
        Schema::table('contas_a_pagares', function (Blueprint $table) {
            // Reverte para o enum antigo
            $table->enum('status', ['pendente', 'pago', 'atrasado'])
                  ->default('pendente')
                  ->change();
        });
    }
};
