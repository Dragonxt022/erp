<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\FornecedorController;
use App\Http\Controllers\HistoricoPedidoController;
use App\Http\Controllers\ListaProdutoController;
use App\Http\Controllers\UnidadeEstoqueController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\CheckFranqueadora;




// rotas abertas para usuarios autenticados!
Route::prefix('api')->middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',

])->group(function () {

    // Principais
    Route::get('/navbar-profile', [AuthController::class, 'getProfile'])->name('profile.get');
});

// Rotas protegidas por autenticação Administrador
Route::prefix('api')->middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    CheckFranqueadora::class
])->group(function () {
    // Unidades
    Route::prefix('unidades')->group(function () {
        Route::get('/', [UnitController::class, 'getUnidades'])->name('unidades.get');
        Route::post('/', [UnitController::class, 'createUnidade'])->name('unidades.create');
        Route::put('/{id}', [UnitController::class, 'updateUnidade'])->name('unidades.update');
    });

    // Usuários
    Route::prefix('usuarios')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('usuarios.index');
        Route::post('/', [UserController::class, 'store'])->name('usuarios.store');
        Route::delete('/{id}', [UserController::class, 'destroy']);
    });



    // Fornecedores
    Route::prefix('fornecedores')->group(function () {
        Route::post('/', [FornecedorController::class, 'store'])->name('fornecedores.store');
        Route::get('/', [FornecedorController::class, 'index'])->name('fornecedores.index');
        Route::post('/atualizar', [FornecedorController::class, 'update'])->name('fornecedores.update');
        Route::delete('/{id}', [FornecedorController::class, 'destroy'])->name('excluir.fornecedor');
    });

    // Lista de Produtos
    Route::prefix('produtos')->group(function () {
        Route::get('/lista', [ListaProdutoController::class, 'index'])->name('listaProdutos.index');
        Route::post('/cadastrar', [ListaProdutoController::class, 'store'])->name('cadastrar.store');
        Route::post('/atualizar', [ListaProdutoController::class, 'update'])->name('atualizarProdutos.update');
        Route::delete('/excluir/{id}', [ListaProdutoController::class, 'destroy'])->name('excluir.produto');
    });
});

// Rotas protegidas por autenticação Usuarios
Route::prefix('api')->middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    // Lista de Produtos
    Route::prefix('estoque')->group(function () {
        Route::get('/lista', [UnidadeEstoqueController::class, 'index'])->name('listaEstoque.index');
        Route::get('/incial', [UnidadeEstoqueController::class, 'painelInicialEstoque'])->name('painelInicialEstoque.index');
        Route::get('/fornecedores', [UnidadeEstoqueController::class, 'unidadeForencedores'])->name('unidadeForencedores.index');
        Route::post('/armazenar-entrada', [UnidadeEstoqueController::class, 'armazenarEntrada'])->name('armazena.entrada');
        Route::post('/criar-pedido', [UnidadeEstoqueController::class, 'criarPedido'])->name('criarPedido');
        Route::put('/estoque/lote/{id}', [UnidadeEstoqueController::class, 'update'])->name('lote.updade');

        // Rotas de controle de retirada
        Route::get('/lista-produtos', [UnidadeEstoqueController::class, 'ListaProdutoEstoque'])->name('ListaProdutoEstoque.index');
    });

    Route::prefix('historico')->group(function () {
        Route::get('lista', [HistoricoPedidoController::class, 'index'])->name('lista.historico');
    });



    Route::prefix('produtos')->group(function () {
        Route::get('/lista', [ListaProdutoController::class, 'index'])->name('listaProdutos.index');
    });
});
