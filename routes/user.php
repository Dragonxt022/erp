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

    // Route::get('/franqueado/email', function () {
    //     return Inertia::render('Admin/Email/Index');
    // })->name('franqueado.email');

    // Route::get('/franqueado/comunidade', function () {
    //     return Inertia::render('Admin/Comunidade/Index');
    // })->name('franqueado.comunidade');

    // Route::get('/franqueado/midias', function () {
    //     return Inertia::render('Admin/Midias/Index');
    // })->name('franqueado.midias');

    // Route::get('/franqueado/megafone', function () {
    //     return Inertia::render('Admin/Megafone/Index');
    // })->name('franqueado.megafone');

    // Route::get('/franqueado/franqueados', function () {
    //     return Inertia::render('Admin/Franqueados/Index');
    // })->name('franqueado.franqueados');

    // Route::get('/franqueado/unidades', function () {
    //     return Inertia::render('Admin/Unidades/Index');
    // })->name('franqueado.unidades');

    // Route::get('/franqueado/fornecedores', function () {
    //     return Inertia::render('Admin/Fornecedores/Index');
    // })->name('franqueado.fornecedores');

    // Route::get('/franqueado/insumos', function () {
    //     return Inertia::render('Admin/Insumos/Index');
    // })->name('franqueado.insumos');

    // Route::get('/franqueado/inspetor', function () {
    //     return Inertia::render('Admin/Inspetor/Index');
    // })->name('franqueado.inspetor');

    // Route::get('/franqueado/sair', function () {
    //     return Inertia::render('Admin/sair/Index');
    // })->name('franqueado.sair');
});
