<?php

use App\Http\Middleware\CheckFranqueado;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Rotas protegidas
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    CheckFranqueado::class
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

    Route::get('/franqueado/historico-pedidos', function () {
        return Inertia::render('Users/HistoricoPedidos/Index');
    })->name('franqueado.historicoPedidos');

    Route::get('/franqueado/perfil', function () {
        return Inertia::render('Users/Perfil/Index');
    })->name('franqueado.perfil');

    Route::get('/franqueado/supervisao-residos', function () {
        return Inertia::render('Users/SupervisaoResiduos/Index');
    })->name('franqueado.supervisaoResidos');

    Route::get('/franqueado/gestao-equipe', function () {
        return Inertia::render('Users/GestaoEquipes/Index');
    })->name('franqueado.gestaoEquipe');

    Route::get('/franqueado/controle-ponto', function () {
        return Inertia::render('Users/ControlePonto/Index');
    })->name('franqueado.controlePonto');

    Route::get('/franqueado/folha-pagamento', function () {
        return Inertia::render('Users/FolhaPagamento/Index');
    })->name('franqueado.folhaPagamento');

    Route::get('/franqueado/abir-caixa', function () {
        return Inertia::render('Users/FluxoCaixa/Index');
    })->name('franqueado.abrirCaixa');

    Route::get('/franqueado/fluxo-caixa', function () {
        return Inertia::render('Users/FluxoCaixa/Fluxo');
    })->name('franqueado.fluxoCaixa');

    Route::get('/franqueado/metodos-pagamentos', function () {
        return Inertia::render('Users/MetodosPagamentos/Index');
    })->name('franqueado.metodosPagamentos');

    Route::get('/franqueado/canais-vendas', function () {
        return Inertia::render('Users/CanaisVendas/Index');
    })->name('franqueado.canaisVendas');

    Route::get('/franqueado/historico-caixa', function () {
        return Inertia::render('Users/HistoricoCaixa/Index');
    })->name('franqueado.historicoCaixa');


    Route::get('/franqueado/dre-gerencial', function () {
        return Inertia::render('Users/Dre/Index');
    })->name('franqueado.dreGerencial');

    Route::get('/franqueado/lista-contas', function () {
        return Inertia::render('Users/Dre/Lista');
    })->name('franqueado.dreListaContas');

    Route::get('/franqueado/cadastrar-custo', function () {
        return Inertia::render('Users/Dre/Cadastrar');
    })->name('franqueado.dreCadastrar');

    Route::get('/franqueado/dre-categorias', function () {
        return Inertia::render('Users/Dre/Categorias');
    })->name('franqueado.dreCategorias');



    // Rotas Sitemas de Retirada de estoque
    Route::get('/franqueado/controle-estoque', function () {
        return Inertia::render('Users/EstoqueRetirada/Index');
    })->name('franqueado.controleEstoque');
});
