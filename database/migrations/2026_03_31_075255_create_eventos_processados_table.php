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
        Schema::create('eventos_processados', function (Blueprint $table) {
            $table->uuid('delivery_id')->primary();
            $table->string('event_type', 100);
            $table->boolean('status')->default(false)->comment('true = processado, false = pendente');
            $table->timestamp('processando_em')->nullable();
            $table->timestamp('processado_em')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eventos_processados');
    }
};
