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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();  // Coluna para armazenar o nome da permissão
            $table->timestamps();  // Para controlar o tempo de criação e atualização das permissões
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
