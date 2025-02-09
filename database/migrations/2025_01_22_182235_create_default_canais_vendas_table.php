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
        Schema::create('default_canais_vendas', function (Blueprint $table) {
            $table->id();
            $table->string('nome'); // Nome do método

            $table->enum(
                'tipo',
                [
                    'ifood',
                    'anota_ai',
                    'delivery_much',
                    'aiqfome',
                    'outros',


                ]
            );
            $table->boolean('status')->default(true);
            $table->string('img_icon')->nullable(); // Ícone do método
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('default_canais_vendas');
    }
};
