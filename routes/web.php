<?php

use App\Http\Controllers\Api\EventoProcessadoApiController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PainelAnaliticos;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Rota Principal (GET) - Exibe a página de login
Route::get('/', [AuthController::class, 'paginLogin'])->name('pagina.login');

// Rota para o callback do IDP
Route::get('/callback', [AuthController::class, 'handleCallback'])->name('idp.callback');

// Rota para o login (POST) - Processa o login
Route::post('/entrar', [AuthController::class, 'login'])->name('entrar.painel');

Route::post('/resetar-password/{token}', [UserController::class, 'updateRecupePassword'])->name('updateRecupe.update');

//  IA
Route::get('/painel-analitycs', [PainelAnaliticos::class, 'analitycsBuscar']);

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
Route::get('/login-estoque', [AuthController::class, 'paginaLoginEstoque'])->name('login.pagina.estoque');
Route::post('/login-estoque', [AuthController::class, 'loginComPin'])->name('login.estoque');

// Compatibilidade com consumidores Node do ecossistema Taiksu.
Route::prefix('events')
    ->withoutMiddleware([ValidateCsrfToken::class])
    ->group(function () {
        Route::get('/heartbeat', [EventoProcessadoApiController::class, 'heartbeat']);
        Route::post('/', [EventoProcessadoApiController::class, 'store']);
        Route::post('/receive', [EventoProcessadoApiController::class, 'store']);
        Route::post('/check', [EventoProcessadoApiController::class, 'check']);
    });


// Carregar rotas do painel administrativo
require __DIR__ . '/admin.php';

// Carregar rotas do painel de usuários
require __DIR__ . '/user.php';

require __DIR__ . '/spa.php';
