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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('pin')->unique()->nullable();  // Alterando o campo para aceitar valores nulos
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->foreignId('unidade_id')->nullable()->constrained('infor_unidade')->onDelete('set null');
            $table->string('cpf')->nullable();  // Novo campo para o CPF do usuário
            $table->string('profile_photo_path', 2048)->nullable();
            $table->decimal('salario', 10, 2)->nullable();  // Novo campo para o salário do usuário
            $table->boolean('franqueadora')->default(false);
            $table->boolean('franqueado')->default(false);
            $table->boolean('controle_retirada_produto')->default(false);
            $table->boolean('colaborador')->default(false);
            // Adicionando o campo cargo_id (relacionamento com cargos)
            $table->foreignId('cargo_id')->nullable()->constrained('cargos')->onDelete('set null');


            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
