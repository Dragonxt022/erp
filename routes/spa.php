<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CaixaAnaliticoController;
use App\Http\Controllers\CaixaController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CategoriaProdutoController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContaAPagarController;
use App\Http\Controllers\DefaultCanaisVendaController;
use App\Http\Controllers\DefaultPaymentMethodController;
use App\Http\Controllers\FornecedorController;
use App\Http\Controllers\HistoricoPedidoController;
use App\Http\Controllers\ListaProdutoController;
use App\Http\Controllers\PainelAnaliticos;
use App\Http\Controllers\SalmaoCalibreController;
use App\Http\Controllers\SalmaoHistoricoController;
use App\Http\Controllers\UnidadeEstoqueController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\CheckFranqueado;
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
        Route::get('/lista-insumos', [ListaProdutoController::class, 'index'])->name('listaInsumos.index');
        Route::post('/cadastrar', [ListaProdutoController::class, 'store'])->name('cadastrar.store');
        Route::post('/atualizar', [ListaProdutoController::class, 'update'])->name('atualizarProdutos.update');
        Route::delete('/excluir/{id}', [ListaProdutoController::class, 'destroy'])->name('excluir.produto');
    });

    // Cadastro de Categorias de Produtos
    Route::prefix('categorias-produtos')->group(function () {
        Route::get('/lista', [CategoriaProdutoController::class, 'index']);
    });

    // Metodos de pagamentos
    Route::prefix('admin-metodos-pagamentos')->group(function () {
        Route::get('/lista', [DefaultPaymentMethodController::class, 'listaIndex'])->name('metodosPagamentos.admin.index');
        Route::post('/alternar-status', [DefaultPaymentMethodController::class, 'toggleStatus'])->name('metodosPagamentos.admin.toggleStatus');

        Route::post('/cadastrar', [DefaultPaymentMethodController::class, 'store'])->name('metodosPagamentos.admin.store');
        Route::post('/atualizar', [DefaultPaymentMethodController::class, 'update'])->name('metodosPagamentos.admin.update');
        Route::delete('/excluir/{id}', [DefaultPaymentMethodController::class, 'destroy'])->name('metodosPagamentos.admin.destroy');
    });

    // Canais de vendas
    Route::prefix('admin-canais-vendas')->group(function () {
        Route::get('/lista', [DefaultCanaisVendaController::class, 'listaIndex'])->name('canaisVendas.admin.index');
        Route::post('/alternar-status', [DefaultCanaisVendaController::class, 'toggleStatus'])->name('canaisVendas.admin.toggleStatus');

        Route::post('/cadastrar', [DefaultCanaisVendaController::class, 'store'])->name('canaisVendas.admin.store');
        Route::post('/atualizar', [DefaultCanaisVendaController::class, 'update'])->name('canaisVendas.admin.update');
        Route::delete('/excluir/{id}', [DefaultCanaisVendaController::class, 'destroy'])->name('canaisVendas.admin.destroy');
    });
});

// Rotas protegidas por autenticação Usuarios
Route::prefix('api')->middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    CheckFranqueado::class
])->group(function () {

    // Rotas do usuário
    Route::prefix('usuarios')->group(function () {
        Route::get('/colaboradores', [UserController::class, 'listColaboradores'])->name('listColaboradores');
        Route::get('/cargos', [UserController::class, 'listarCargos'])->name('listarCargos.get');
        Route::post('/cadastrar-colaborador', [UserController::class, 'novoColaborador'])->name('cadastrar.novoColaborador');
        Route::post('/atualiza-colaborador', [UserController::class, 'atualizarColaborador'])->name('atualizarColaborador.update');

        Route::post('/upsert-permissions', [UserController::class, 'upsertPermissions'])->name('upsertPermissions.upsert');
        Route::post('/regenera-pin', [UserController::class, 'atualizarPin'])->name('atualizarPin.regenera');

        Route::post('/password/update', [UserController::class, 'updatePassword'])->name('usuario.password.update');
        

        Route::delete('/delete/{id}', [UserController::class, 'destroy']);
    });

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
        // Route::post('/retirada-produtos', [UnidadeEstoqueController::class, 'enviarCarrinho'])->name('retirada.produtos');
        Route::post('/consumir-estoque', [UnidadeEstoqueController::class, 'consumirEstoque'])->name('consumir.estoque');
    });


    // Rotas do histórico de vendas
    Route::prefix('historico')->group(function () {
        Route::get('lista', [HistoricoPedidoController::class, 'index'])->name('lista.historico');
    });

    //  Rotas de produtos
    Route::prefix('produtos')->group(function () {
        Route::get('/lista', [ListaProdutoController::class, 'index'])->name('listaProdutos.index');
    });

    // metos de pagamentos
    Route::prefix('metodos-pagamentos')->group(function () {
        Route::get('/lista', [DefaultPaymentMethodController::class, 'index'])->name('metodosPagamentos.index');
        Route::get('/verificar-pagamentos/{id}', [DefaultPaymentMethodController::class, 'show'])->name('metodosPagamento.verificar');
        Route::post('/upsert', [DefaultPaymentMethodController::class, 'upsert'])->name('metodoPagamento.upsert');
    });

    // Rotas do Canais de Vendas
    Route::prefix('canais-vendas')->group(function () {
        Route::get('/lista', [DefaultCanaisVendaController::class, 'index'])->name('canaisVendas.index');
        Route::get('/verificar-pagamentos/{id}', [DefaultCanaisVendaController::class, 'show'])->name('canaisVendas.verificar');
        Route::post('/upsert', [DefaultCanaisVendaController::class, 'upsert'])->name('canaisVendas.upsert');
    });

    // Rotas dos sistema do fluxo de caixa
    Route::prefix('caixas')->group(function () {
        Route::post('/abrir', [CaixaController::class, 'abrirCaixa'])->name('abrirCaixa');
        Route::post('/fechar-caixa', [CaixaController::class, 'fecharCaixa'])->name('fecharCaixa');
        Route::get('detalhes/{id}', [CaixaController::class, 'detalhesCaixa'])->name('detalhesCaixa');
        Route::get('abertos', [CaixaController::class, 'listarCaixasAbertos'])->name('listarCaixasAbertos');

        Route::get('/metodos-canais-ativos', [CaixaController::class, 'listarMetodosEcanaisAtivos'])->name('listaMetodosEcanaisAtivos');
        Route::post('/adicionar-suprimento', [CaixaController::class, 'adicionarSuprimento']);
        Route::post('/remover-suprimento', [CaixaController::class, 'removeSuprimento']);
        Route::get('/valor-disponivel', [CaixaController::class, 'valorDisponivel']);

        Route::get('/lista', [CaixaController::class, 'getCaixas']);
    });

    // Analitycos do caixa
    Route::prefix('analyticos')->group(function () {
        Route::get('/lista-metodos-pagamentos', [CaixaAnaliticoController::class, 'listarMetodosPagamento'])->name('listarMetodosPagamento.index');
    });

    // Categorias
    Route::prefix('categorias')->group(function () {
        Route::get('/lista', [CategoriaController::class, 'index']);
        Route::get('/seleto-caixa', [CategoriaController::class, 'listaSeletorCaixa']);
        Route::get('/seleto-contas', [CategoriaController::class, 'listaSeletorContasApagar']);

        Route::post('/contas-a-pagar', [CategoriaController::class, 'store']);
        Route::get('/listar-por-grupo', [CategoriaController::class, 'listarPorGrupo']);
    });

    // contas a pagar
    Route::prefix('cursto')->group(function () {
        Route::post('/contas-a-pagar', [ContaAPagarController::class, 'store']);
        Route::post('/contas-a-pagar/{id}/pagar', [ContaAPagarController::class, 'marcarComoPago']);
        Route::delete('/contas-a-pagar/{id}', [ContaAPagarController::class, 'destroy']);
        Route::get('/listar-contas-a-pagar', [ContaAPagarController::class, 'index']);
    });

    // Painel de Analitycos
    Route::prefix('painel-analitycos')->group(function () {
        Route::get('/calcular-cmv', [PainelAnaliticos::class, 'calcularCMV'])->name('calcularCMV');
        Route::get('/calcular-fluxo-caixa', [PainelAnaliticos::class, 'somarTodosOsCaixas'])->name('somarTodosOsCaixas');
        Route::get('/faturamento-por-dia-mes', [PainelAnaliticos::class, 'diasDoMes'])->name('diasDoMes');
        Route::get('/calculo-ticket-medio-quantidade', [PainelAnaliticos::class, 'calcularTicketMedioEQuantidadePedidos'])->name('calcularTicketMedioEQuantidadePedidos');
        Route::get('/calcular-cmv-caixas-tickets', [PainelAnaliticos::class, 'calcularIndicadores'])->name('calcularIndicadores');
        Route::get('/calcula-cmv-soma-caixas-mes', [PainelAnaliticos::class, 'calcularCMVESomarCaixas'])->name('calcularCMVESomarCaixas');
    });

    Route::prefix('painel-dre')->group(function () {
        Route::get('/analitycs-dre', [PainelAnaliticos::class, 'analitycsDRE'])->name('analitycsDRE');
    });

    //** Gestão de residuos */
    Route::prefix('gestao-residuos')->group(function () {
        Route::get('/limpeza', [SalmaoHistoricoController::class, 'index'])->name('salmao.limpeza');
        Route::post('/adicionar', [SalmaoHistoricoController::class, 'store'])->name('salmao.adicionar');
        Route::get('/listar', [SalmaoHistoricoController::class, 'getHistoricoSalmao'])->name('salmao.lista');
    });
});
