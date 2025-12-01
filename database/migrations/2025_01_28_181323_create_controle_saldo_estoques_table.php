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
        Schema::create('controle_saldo_estoques', function (Blueprint $table) {
            $table->id();
            $table->decimal('ajuste_saldo', 10, 2)->default(0);
            $table->timestamp('data_ajuste')->useCurrent();
            $table->string('motivo_ajuste')->nullable();
            $table->foreignId('unidade_id')->nullable()->constrained('infor_unidade')->onDelete('set null');
            $table->foreignId('responsavel_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('controle_saldo_estoques');
    }
};
