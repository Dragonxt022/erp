<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\UnitController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Rotas de autenticação
Route::get('/', [AuthController::class, 'showLoginForm'])->name('entrar');

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

    Route::get('/email', function () {
        return Inertia::render('Email/Index');
    })->name('email');

    Route::get('/comunidade', function () {
        return Inertia::render('Comunidade/Index');
    })->name('comunidade');

    Route::get('/midias', function () {
        return Inertia::render('Midias/Index');
    })->name('midias');

    Route::get('/megafone', function () {
        return Inertia::render('Megafone/Index');
    })->name('megafone');

    Route::get('/franqueados', function () {
        return Inertia::render('Franqueados/Index');
    })->name('franqueados');

    Route::get('/unidades', function () {
        return Inertia::render('Unidades/Index');
    })->name('unidades');

    Route::get('/insumos', function () {
        return Inertia::render('Insumos/Index');
    })->name('insumos');

    Route::get('/inspetor', function () {
        return Inertia::render('Inspetor/Index');
    })->name('inspetor');

    Route::get('/sair', function () {
        return Inertia::render('sair/Index');
    })->name('sair');


    // API
    Route::get('/api/profile', [AuthController::class, 'getProfile']);

    // Unidades
    Route::get('/api/unidades', [ UnitController::class, 'getUnidades']);
    Route::post('/api/unidades', [UnitController::class, 'createUnidade']);



});
