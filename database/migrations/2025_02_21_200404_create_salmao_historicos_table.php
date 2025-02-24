<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('salmao_historicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('responsavel_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('calibre_id')->constrained('salmao_calibres')->onDelete('cascade');
            $table->decimal('valor_pago', 10, 2);
            $table->decimal('peso_bruto', 10, 3);
            $table->decimal('peso_limpo', 10, 3);
            $table->decimal('aproveitamento', 10, 3);
            $table->decimal('desperdicio', 10, 3);
            $table->foreignId('unidade_id')->nullable()->constrained('infor_unidade')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('salmao_historicos');
    }
};
