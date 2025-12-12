<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Alterar a coluna 'operacao' de ENUM para VARCHAR(50)
        DB::statement("ALTER TABLE movimentacoes_estoques MODIFY COLUMN operacao VARCHAR(50) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimentacoes_estoques', function (Blueprint $table) {
            //
        });
    }
};
