<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Rota Principal (GET) - Exibe a página de login
Route::get('/', [AuthController::class, 'paginLogin'])->name('pagina.login');

// Rota para o login (POST) - Processa o login
Route::post('/entrar', [AuthController::class, 'login'])->name('entrar.painel');

// Defina a rota para buscar o token
Route::get('/get-token', function () {
    // Verifique se o usuário está autenticado
    if (Auth::check()) {
        // Recupere o usuário autenticado
        $user = Auth::user();

        // Busque o token do usuário na tabela de tokens
        $token = $user->tokens->first(); // A primeira token vinculada ao usuário

        // Retorne o token ou algum outro dado necessário
        return response()->json([
            'status' => 'success',
            'token' => $token->token,
        ]);
    }

    return response()->json(['status' => 'unauthorized'], 401);
});

// Rotas do sistema de retirada de estoque!
Route::get('/logoin-estoque', [AuthController::class, 'loginComPin'])->name('login.estoque');


// Carregar rotas do painel administrativo
require __DIR__ . '/admin.php';

// Carregar rotas do painel de usuários
require __DIR__ . '/user.php';

require __DIR__ . '/spa.php';
