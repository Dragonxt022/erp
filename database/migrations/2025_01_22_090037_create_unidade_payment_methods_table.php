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
        Schema::create('unidade_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unidade_id')->constrained('infor_unidade')->onDelete('cascade');
            $table->foreignId('default_payment_method_id')->constrained('default_payment_methods')->onDelete('cascade');
            $table->decimal('porcentagem', 5, 2)->default(0);
            $table->boolean('status')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unidade_payment_methods');
    }
};
