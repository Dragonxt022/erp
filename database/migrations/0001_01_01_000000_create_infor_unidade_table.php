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
        Schema::create('infor_unidade', function (Blueprint $table) {
            $table->id();
            $table->string('cep');
            $table->string('cidade')->nullable();
            $table->string('bairro');
            $table->string('rua');
            $table->string('numero');
            $table->string('cnpj')->unique();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('infor_unidade');
    }
};
