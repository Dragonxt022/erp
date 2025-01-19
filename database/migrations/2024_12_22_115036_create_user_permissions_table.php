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
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');  // Relaciona com a tabela users
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');  // Relaciona com a tabela permissions
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
    }
};
