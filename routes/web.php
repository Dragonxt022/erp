<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CargoController;
use App\Http\Controllers\FornecedorController;
use App\Http\Controllers\ListaProdutoController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
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

    Route::get('/fornecedores', function () {
        return Inertia::render('Fornecedores/Index');
    })->name('fornecedores');

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
    Route::get('/api/profile', [AuthController::class, 'getProfile'])->name('profile.get');

    // Unidades
    Route::get('/api/unidades', [UnitController::class, 'getUnidades'])->name('unidades.get');
    Route::post('/api/unidades', [UnitController::class, 'createUnidade'])->name('unidades.create');
    Route::put('/api/unidades/{id}', [UnitController::class, 'updateUnidade'])->name('unidades.update');

    // Usuários
    Route::get('/api/usuarios', [UserController::class, 'index'])->name('usuarios.index');
    Route::post('/api/usuarios', [UserController::class, 'store'])->name('usuarios.store');
    Route::delete('/api/usuario/{id}', [UserController::class, 'destroy']);


    // Cargos
    Route::get('/api/cargos', [CargoController::class, 'index'])->name('cargos.index');

    // Fornecedores
    Route::post('/api/fornecedores', [FornecedorController::class, 'store'])->name('fornecedores.store');
    Route::get('/api/fornecedores', [FornecedorController::class, 'index'])->name('fornecedores.index');
    Route::post('/api/atualizar-fornecedores', [FornecedorController::class, 'update'])->name('fornecedores.update');
    Route::delete('/api/excluir-fornecedor/{id}', [FornecedorController::class, 'destroy'])->name('excluir.fornecedor');

    // Lista de Produtos
    Route::get('/api/lista-produtos', [ListaProdutoController::class, 'index'])->name('listaProdutos.index');
    Route::post('/api/cadastar-produtos', [ListaProdutoController::class, 'store'])->name('cadastrar.store');
    Route::post('/api/atualizar-produtos', [ListaProdutoController::class, 'update'])->name('atualizarProdutos.update');

    Route::delete('/api/excluir-produto/{id}', [ListaProdutoController::class, 'destroy'])->name('excluir.produto');
});
