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
        Schema::create('carga_horario_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carga_horario_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('unidade_id')->constrained('infor_unidade')->onDelete('cascade');
            $table->boolean('status');
            $table->enum('dia_semana', ['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo']);
            $table->enum('periodo', ['manha', 'tarde', 'noite']);
            $table->time('hora_entrada');
            $table->time('hora_saida');
            $table->decimal('carga_horaria_semanal', 5, 2);
            $table->enum('acao', ['criado', 'atualizado', 'excluido']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carga_horario_logs');
    }
};
