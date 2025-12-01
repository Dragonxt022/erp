<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notificacoes', function (Blueprint $table) {
            // Verifica se a coluna 'global' já existe antes de tentar adicioná-la
            if (!Schema::hasColumn('notificacoes', 'global')) {
                $table->boolean('global')->default(false); // Adiciona a coluna 'global' se não existir
            }

            // Verifica se a coluna 'setor_id' já existe antes de tentar adicioná-la
            if (!Schema::hasColumn('notificacoes', 'setor_id')) {
                $table->foreignId('setor_id')->nullable()->constrained('operacionais')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('notificacoes', function (Blueprint $table) {
            // Remover os campos adicionados
            if (Schema::hasColumn('notificacoes', 'global')) {
                $table->dropColumn('global');
            }
            
            if (Schema::hasColumn('notificacoes', 'setor_id')) {
                $table->dropForeign(['setor_id']);
                $table->dropColumn('setor_id');
            }
        });
    }
};
