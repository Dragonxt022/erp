<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Rotas protegidas
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    Route::get('/franqueado/painel', function () {
        return Inertia::render('Users/Painel/Index');
    })->name('franqueado.painel');

    Route::get('/franqueado/email', function () {
        return Inertia::render('Users/Email/Index');
    })->name('franqueado.email');

    Route::get('/franqueado/comunidade', function () {
        return Inertia::render('Users/Comunidade/Index');
    })->name('franqueado.comunidade');

    Route::get('/franqueado/midias', function () {
        return Inertia::render('Users/Midias/Index');
    })->name('franqueado.midias');

    Route::get('/franqueado/estoque', function () {
        return Inertia::render('Users/Estoque/Index');
    })->name('franqueado.estoque');

    Route::get('/franqueado/inventario', function () {
        return Inertia::render('Users/Inventario/Index');
    })->name('franqueado.inventario');

    Route::get('/franqueado/fornecedores', function () {
        return Inertia::render('Users/Fornecedores/Index');
    })->name('franqueado.fornecedores');

    Route::get('/franqueado/pedidos', function () {
        return Inertia::render('Users/Pedidos/Index');
    })->name('franqueado.pedidos');
});
