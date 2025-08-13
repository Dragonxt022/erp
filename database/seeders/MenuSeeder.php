<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MenuCategory;
use App\Models\MenuItem;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // ===== Categoria: Início =====
        $catInicio = MenuCategory::create(['name' => '', 'order' => 1]);
        MenuItem::create([
            'category_id' => $catInicio->id,
            'label' => 'Inicio',
            'icon' => '/storage/images/inicio.svg',
            'link' => 'franqueado.painel',
            'order' => 1
        ]);

        // ===== Categoria: Ferramentas =====
        $catFerramentas = MenuCategory::create(['name' => 'Ferramentas', 'order' => 2]);

        MenuItem::create([
            'category_id' => $catFerramentas->id,
            'label' => 'E-mail',
            'icon' => '/storage/images/email.svg',
            'link' => 'https://arquivos.taiksu.com.br/apps/mail/box/1',
            'order' => 1
        ]);
        MenuItem::create([
            'category_id' => $catFerramentas->id,
            'label' => 'Comunidade',
            'icon' => '/storage/images/diversity_4.svg',
            'link' => 'https://arquivos.taiksu.com.br/call/wprjfww7',
            'order' => 2
        ]);
        MenuItem::create([
            'category_id' => $catFerramentas->id,
            'label' => 'Mídias',
            'icon' => '/storage/images/perm_media.svg',
            'link' => 'https://arquivos.taiksu.com.br/s/P5nypCADKccgcib',
            'order' => 3
        ]);

        // ===== Categoria: Gestão da Loja =====
        $catGestaoLoja = MenuCategory::create(['name' => 'Gestão da loja', 'order' => 3]);

        // Gestão de estoque
        $gestaoEstoque = MenuItem::create([
            'category_id' => $catGestaoLoja->id,
            'label' => 'Gestão de estoque',
            'icon' => '/storage/images/estoque.svg',
            'link' => 'franqueado.estoque',
            'required_permission' => 'controle_estoque',
            'order' => 1
        ]);
        MenuItem::create([
            'category_id' => $catGestaoLoja->id,
            'parent_id' => $gestaoEstoque->id,
            'label' => 'Visão Geral',
            'link' => 'franqueado.estoque',
            'required_permission' => 'controle_estoque',
            'order' => 1
        ]);
        MenuItem::create([
            'category_id' => $catGestaoLoja->id,
            'parent_id' => $gestaoEstoque->id,
            'label' => 'Inventário',
            'link' => 'franqueado.inventario',
            'required_permission' => 'controle_estoque',
            'order' => 2
        ]);
        MenuItem::create([
            'category_id' => $catGestaoLoja->id,
            'parent_id' => $gestaoEstoque->id,
            'label' => 'Saida de estoque',
            'link' => 'franqueado.controleEstoque',
            'required_permission' => 'controle_saida_estoque',
            'order' => 3
        ]);
        MenuItem::create([
            'category_id' => $catGestaoLoja->id,
            'parent_id' => $gestaoEstoque->id,
            'label' => 'Fornecedores',
            'link' => 'franqueado.fornecedores',
            'required_permission' => 'controle_estoque',
            'order' => 4
        ]);
        MenuItem::create([
            'category_id' => $catGestaoLoja->id,
            'parent_id' => $gestaoEstoque->id,
            'label' => 'Novo Pedidos',
            'link' => 'franqueado.pedidos',
            'required_permission' => 'controle_estoque',
            'order' => 5
        ]);
        MenuItem::create([
            'category_id' => $catGestaoLoja->id,
            'parent_id' => $gestaoEstoque->id,
            'label' => 'Histórico de Pedidos',
            'link' => 'franqueado.historicoPedidos',
            'required_permission' => 'controle_estoque',
            'order' => 6
        ]);

        // Gestão de resíduos
        $gestaoResiduos = MenuItem::create([
            'category_id' => $catGestaoLoja->id,
            'label' => 'Gestão de resíduos',
            'icon' => '/storage/images/delete_branco.svg',
            'link' => 'franqueado.supervisaoResidos',
            'required_permission' => 'gestao_salmao',
            'order' => 2
        ]);
        MenuItem::create([
            'category_id' => $catGestaoLoja->id,
            'parent_id' => $gestaoResiduos->id,
            'label' => 'Visão Geral',
            'link' => 'franqueado.supervisaoResidos',
            'required_permission' => 'gestao_salmao',
            'order' => 1
        ]);
        MenuItem::create([
            'category_id' => $catGestaoLoja->id,
            'parent_id' => $gestaoResiduos->id,
            'label' => 'Limpeza de salmão',
            'link' => 'franqueado.limpesaSalmoes',
            'required_permission' => 'gestao_salmao',
            'order' => 2
        ]);

        // Gestão de equipe
        $gestaoEquipe = MenuItem::create([
            'category_id' => $catGestaoLoja->id,
            'label' => 'Gestão de equipe',
            'icon' => '/storage/images/gestao_servisos.svg',
            'link' => 'franqueado.gestaoEquipe',
            'required_permission' => 'gestao_equipe',
            'order' => 3
        ]);
        MenuItem::create([
            'category_id' => $catGestaoLoja->id,
            'parent_id' => $gestaoEquipe->id,
            'label' => 'Visão Geral',
            'icon' => '/storage/images/add_product.svg',
            'link' => 'franqueado.gestaoEquipe',
            'required_permission' => 'gestao_equipe',
            'order' => 1
        ]);
        MenuItem::create([
            'category_id' => $catGestaoLoja->id,
            'parent_id' => $gestaoEquipe->id,
            'label' => 'Carga Horária',
            'icon' => '/storage/images/add_product.svg',
            'link' => 'franqueado.cargaHoraria',
            'required_permission' => 'gestao_equipe',
            'order' => 2
        ]);
        MenuItem::create([
            'category_id' => $catGestaoLoja->id,
            'parent_id' => $gestaoEquipe->id,
            'label' => 'Folha de pagamento',
            'link' => 'franqueado.folhaPagamento',
            'required_permission' => 'gestao_equipe',
            'order' => 3
        ]);

        // ===== Categoria: Gestão de Redes =====
        $catGestaoRedes = MenuCategory::create(['name' => 'Gestão de Redes', 'order' => 4]);

        $produtividade = MenuItem::create([
            'category_id' => $catGestaoRedes->id,
            'label' => 'Produtividade',
            'icon' => '/storage/images/skillet.svg',
            'link' => 'franqueado.produtividade.geral',
            'order' => 1
        ]);
        MenuItem::create([
            'category_id' => $catGestaoRedes->id,
            'parent_id' => $produtividade->id,
            'label' => 'Visão Geral',
            'link' => 'franqueado.produtividade.geral',
            'order' => 1
        ]);
        MenuItem::create([
            'category_id' => $catGestaoRedes->id,
            'parent_id' => $produtividade->id,
            'label' => 'Agenda de Produção',
            'link' => 'franqueado.AgendaProducao',
            'order' => 2
        ]);

        // ===== Categoria: Financeiro =====
        $catFinanceiro = MenuCategory::create(['name' => 'Financeiro', 'order' => 5]);

        $fluxoCaixa = MenuItem::create([
            'category_id' => $catFinanceiro->id,
            'label' => 'Fluxo de caixa',
            'icon' => '/storage/images/fluxo_caixa.svg',
            'link' => 'franqueado.abrirCaixa',
            'required_permission' => 'fluxo_caixa',
            'order' => 1
        ]);
        MenuItem::create([
            'category_id' => $catFinanceiro->id,
            'parent_id' => $fluxoCaixa->id,
            'label' => 'Visão Geral',
            'link' => 'franqueado.abrirCaixa',
            'required_permission' => 'fluxo_caixa',
            'order' => 1
        ]);
        MenuItem::create([
            'category_id' => $catFinanceiro->id,
            'parent_id' => $fluxoCaixa->id,
            'label' => 'Métodos de pagamento',
            'link' => 'franqueado.metodosPagamentos',
            'required_permission' => 'fluxo_caixa',
            'order' => 2
        ]);
        MenuItem::create([
            'category_id' => $catFinanceiro->id,
            'parent_id' => $fluxoCaixa->id,
            'label' => 'Canais de Vendas',
            'link' => 'franqueado.canaisVendas',
            'required_permission' => 'fluxo_caixa',
            'order' => 3
        ]);
        MenuItem::create([
            'category_id' => $catFinanceiro->id,
            'parent_id' => $fluxoCaixa->id,
            'label' => 'Histórico de Caixa',
            'link' => 'franqueado.historicoCaixa',
            'required_permission' => 'fluxo_caixa',
            'order' => 4
        ]);
        MenuItem::create([
            'category_id' => $catFinanceiro->id,
            'parent_id' => $fluxoCaixa->id,
            'label' => 'Caixa Retroativo',
            'link' => 'franqueado.caixaRetroativo',
            'required_permission' => 'fluxo_caixa',
            'order' => 5
        ]);

        $contasPagar = MenuItem::create([
            'category_id' => $catFinanceiro->id,
            'label' => 'Contas a pagar',
            'icon' => '/storage/images/attach_money.svg',
            'link' => 'franqueado.contasApagar',
            'required_permission' => 'contas_pagar',
            'order' => 2
        ]);
        MenuItem::create([
            'category_id' => $catFinanceiro->id,
            'parent_id' => $contasPagar->id,
            'label' => 'Visão Geral',
            'link' => 'franqueado.contasApagar',
            'required_permission' => 'contas_pagar',
            'order' => 1
        ]);
        MenuItem::create([
            'category_id' => $catFinanceiro->id,
            'parent_id' => $contasPagar->id,
            'label' => 'Histórico de Despesas',
            'link' => 'franqueado.historicoContas',
            'required_permission' => 'contas_pagar',
            'order' => 2
        ]);

        MenuItem::create([
            'category_id' => $catFinanceiro->id,
            'label' => 'DRE Gerencial',
            'icon' => '/storage/images/analitic.svg',
            'link' => 'franqueado.dreGerencial',
            'required_permission' => 'dre',
            'order' => 3
        ]);

        // ===== Categoria: Logout =====
        $catLogout = MenuCategory::create(['name' => '', 'order' => 6]);
        MenuItem::create([
            'category_id' => $catLogout->id,
            'label' => 'Sair',
            'icon' => '/storage/images/log-out.png',
            'link' => 'logout',
            'order' => 1
        ]);
    }
}
