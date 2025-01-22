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
        Schema::create('unidade_canais_vendas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unidade_id')->constrained('infor_unidade')->onDelete('cascade');
            $table->foreignId('canal_venda_method_id')->constrained('default_canais_vendas')->onDelete('cascade');
            $table->integer('porcentagem')->default(0);
            $table->boolean('status')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unidade_canais_vendas');
    }
};
