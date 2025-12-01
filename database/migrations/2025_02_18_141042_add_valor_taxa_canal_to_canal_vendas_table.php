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
        Schema::table('canal_vendas', function (Blueprint $table) {
            $table->decimal('valor_taxa_canal', 10, 2)->after('valor_total_vendas')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('canal_vendas', function (Blueprint $table) {
            $table->dropColumn('valor_taxa_canal');
        });
    }
};
