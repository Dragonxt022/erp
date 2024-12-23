<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Rotas livres
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});


// Rotas de autenticação
Route::get('/entrar', [AuthController::class, 'showLoginForm'])->name('entrar');

// Rota para o login (POST), utilizando o AuthController
Route::post('/entrar', [AuthController::class, 'login'])->name('entrar.painal');



// Rotas protegidas
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    Route::get('/painel', function () {
        return Inertia::render('Painel/Index');
    })->name('painel');


    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

});
