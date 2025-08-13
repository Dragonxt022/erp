<?php

namespace App\Services;

use App\Models\Caixa;
use App\Models\CanalVenda;
use App\Models\Categoria;
use App\Models\ContaAPagar;
use App\Models\ControleSaldoEstoque;
use App\Models\FechamentoCaixa;
use App\Models\FluxoCaixa;
use App\Models\GrupoDeCategorias;
use App\Models\MovimentacoesEstoque;
use App\Models\UnidadeEstoque;
use App\Models\UnidadePaymentMethod;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AnalyticService
{
    /**
     * Calcula os dados analíticos de um período.
     *
     * @param int $unidadeId
     * @param Carbon $startDateCarbon
     * @param Carbon $endDateCarbon
     * @param bool $isCalendarMode Indica se a chamada é para o calendário (afeta o tratamento de saldo inicial CMV)
     * @param int|null $month Usado apenas no modo calendário para o primeiro mês.
     * @param int|null $year Usado apenas no modo calendário para o primeiro mês.
     * @return array
     */
    public function calculatePeriodData(int $unidadeId, Carbon $startDateCarbon, Carbon $endDateCarbon, bool $isCalendarMode = false, ?int $month = null, ?int $year = null): array
    {
        // 1. Saldo Inicial de Estoque
        $saldoInicialData = ControleSaldoEstoque::where('unidade_id', $unidadeId)
            ->where('data_ajuste', '<', $startDateCarbon)
            ->orderBy('data_ajuste', 'desc')
            ->first();

        $estoqueInicialValor = $saldoInicialData ? $saldoInicialData->ajuste_saldo : 0;

        // Lógica para o primeiro ajuste da unidade, principalmente para o calendário
        if ($isCalendarMode && $month === 1 && !$saldoInicialData) {
            $primeiroAjusteGeral = ControleSaldoEstoque::where('unidade_id', $unidadeId)
                ->orderBy('data_ajuste', 'asc')
                ->first();
            $estoqueInicialValor = $primeiroAjusteGeral ? $primeiroAjusteGeral->ajuste_saldo : 0;
        }

        // 2. Compras (Entradas) no período
        $compras = MovimentacoesEstoque::where('unidade_id', $unidadeId)
            ->where('operacao', 'Entrada')
            ->whereBetween('created_at', [$startDateCarbon, $endDateCarbon])
            ->get();
        $comprasValor = $compras->sum(fn($item) => $item->preco_insumo * $item->quantidade);

        // 3. Saldo Final de Estoque
        $saldoFinalControle = ControleSaldoEstoque::where('unidade_id', $unidadeId)
            ->where('data_ajuste', '<=', $endDateCarbon)
            ->orderBy('data_ajuste', 'desc')
            ->first();
        $estoqueFinalValor = $saldoFinalControle ? $saldoFinalControle->ajuste_saldo : 0;

        // Calcular o CMV
        $cmv = $estoqueInicialValor + $comprasValor - $estoqueFinalValor;

        // --- CÁLCULO DE OUTRAS DESPESAS E RECEITAS ---

        $totalCaixas = Caixa::where('unidade_id', $unidadeId)
            ->where('status', 0)
            ->whereBetween('created_at', [$startDateCarbon, $endDateCarbon])
            ->sum('valor_final');

        $totalSalarios = User::where('unidade_id', $unidadeId)
            ->whereNotNull('salario')
            ->where('colaborador', true)
            ->sum('salario');

        $totalMotoboy = ContaAPagar::where('unidade_id', $unidadeId)
            ->whereHas('categoria', function ($query) {
                $query->where('nome', 'Motoboy');
            })
            ->whereIn('status', ['pago', 'pendente'])
            ->whereBetween('emitida_em', [$startDateCarbon, $endDateCarbon])
            ->sum('valor');

        $totalLiquido = $totalCaixas - $totalMotoboy;
        $totalRoyalties = $totalLiquido * 0.05;
        $totalFundoPropaganda = $totalLiquido * 0.015;

        // CORREÇÃO APLICADA AQUI: FGTS é 8% do totalSalarios
        $totalFGTS = $totalSalarios * 0.08;

        $creditoIds = DB::table('default_payment_methods')->where('tipo', 'credito')->pluck('id');
        $totalTaxasCredito = FechamentoCaixa::where('unidade_id', $unidadeId)
            ->whereIn('metodo_pagamento_id', $creditoIds)
            ->whereBetween('created_at', [$startDateCarbon, $endDateCarbon])
            ->sum('valor_taxa_metodo');

        $debitoIds = DB::table('default_payment_methods')->where('tipo', 'debito')->pluck('id');
        $totalTaxasDebito = FechamentoCaixa::where('unidade_id', $unidadeId)
            ->whereIn('metodo_pagamento_id', $debitoIds)
            ->whereBetween('created_at', [$startDateCarbon, $endDateCarbon])
            ->sum('valor_taxa_metodo');

        $vrAlimentacaoIds = DB::table('default_payment_methods')->where('tipo', 'vr_alimentacao')->pluck('id');
        $totalTaxasVrAlimentacao = FechamentoCaixa::where('unidade_id', $unidadeId)
            ->whereIn('metodo_pagamento_id', $vrAlimentacaoIds)
            ->whereBetween('created_at', [$startDateCarbon, $endDateCarbon])
            ->sum('valor_taxa_metodo');

        $totalTaxasCanais = CanalVenda::where('unidade_id', $unidadeId)
            ->whereBetween('created_at', [$startDateCarbon, $endDateCarbon])
            ->sum('valor_taxa_canal');

        $totalTaxasDelivery = CanalVenda::where('unidade_id', $unidadeId)
            ->whereBetween('created_at', [$startDateCarbon, $endDateCarbon])
            ->sum('valor_taxa_canal');


        // --- CÁLCULO DE CATEGORIAS DE DESPESA ---

        $categoriasRemovidas = ["Fornecedores"];
        $categoriasIgnoradasNaSoma = ["Fornecedores"];

        $grupos = GrupoDeCategorias::with('categorias')->get();
        $totalDespesasCategorias = 0;
        $totalDespesasCategoriasSemFolha = 0;

        $dadosGrupos = $grupos->map(function ($grupo) use (
            $unidadeId, $startDateCarbon, $endDateCarbon, $totalSalarios, $totalRoyalties,
            $totalFundoPropaganda, $totalLiquido, $totalFGTS, $totalTaxasCredito, $totalTaxasDebito,
            $totalTaxasVrAlimentacao, $totalTaxasCanais, $cmv, $totalTaxasDelivery,
            &$totalDespesasCategorias, &$totalDespesasCategoriasSemFolha,
            $categoriasRemovidas, $categoriasIgnoradasNaSoma,
        ) {
            $categoriasFormatadas = $grupo->categorias
                ->reject(fn($categoria) => in_array($categoria->nome, $categoriasRemovidas))
                ->map(function ($categoria) use (
                    $unidadeId, $startDateCarbon, $endDateCarbon, $totalSalarios, $totalRoyalties,
                    $totalFundoPropaganda, $totalLiquido, $totalFGTS, $totalTaxasCredito, $totalTaxasDebito,
                    $totalTaxasVrAlimentacao, $totalTaxasCanais, $cmv, $totalTaxasDelivery,
                    &$totalDespesasCategorias, &$totalDespesasCategoriasSemFolha,
                    $categoriasIgnoradasNaSoma,
                ) {
                    $valor = ContaAPagar::where('categoria_id', $categoria->id)
                        ->where('unidade_id', $unidadeId)
                        ->whereIn('status', ['pago', 'pendente'])
                        ->whereBetween('emitida_em', [$startDateCarbon, $endDateCarbon])
                        ->sum('valor');

                    $valoresFixos = [
                        "Mercadoria Vendida" => $cmv,
                        "FGTS" => $totalFGTS,
                        "Folha de pagamento" => $totalSalarios,
                        "Royalties" => $totalRoyalties,
                        "Fundo de propaganda" =>  $totalFundoPropaganda,
                        "Taxa de Crédito" => $totalTaxasCredito,
                        "Taxa de Débito" => $totalTaxasDebito,
                        "Plataformas de Delivery" => $totalTaxasCanais,
                        "Taxas de Delivery" => $totalTaxasDelivery,
                        "Voucher Alimentação" => $totalTaxasVrAlimentacao
                    ];


                    if (isset($valoresFixos[$categoria->nome])) {
                        $valor = $valoresFixos[$categoria->nome];
                    }

                    if (!in_array($categoria->nome, $categoriasIgnoradasNaSoma)) {
                        $totalDespesasCategoriasSemFolha += $valor;
                    }
                    $totalDespesasCategorias += $valor;

                    return [
                        'categoria' => $categoria->nome,
                        'total' => $valor,
                        'valor' => $valor
                    ];
                });

            return [
                'nome_grupo' => $grupo->nome,
                'categorias' => $categoriasFormatadas
            ];
        });

        $resultado_do_periodo_sem_folha = $totalCaixas - $totalDespesasCategoriasSemFolha;
        $resultado_do_periodo_sem_folha = max($resultado_do_periodo_sem_folha, 0);

        return [
            'estoqueInicialValor' => $estoqueInicialValor,
            'comprasValor' => $comprasValor,
            'estoqueFinalValor' => $estoqueFinalValor,
            'cmv' => $cmv,

            'total_caixas' => $totalCaixas,
            'total_salarios' => $totalSalarios,
            'total_motoboy' => $totalMotoboy,
            'total_royalties' => $totalRoyalties,
            'total_fundo_propaganda' => $totalFundoPropaganda,
            "Total_Liquido" => $totalLiquido,
            'total_fgts' => $totalFGTS,
            'total_taxas_credito' => $totalTaxasCredito,
            'total_taxas_debito' => $totalTaxasDebito,
            'total_taxas_vr_alimentacao' => $totalTaxasVrAlimentacao,
            "total_taxas_canais" => $totalTaxasDelivery,
            'total_despesas_categorias' => $totalDespesasCategorias,
            'total_despesas_categorias_sem_folha' => $totalDespesasCategoriasSemFolha,
            'resultado_do_periodo_sem_folha' => $resultado_do_periodo_sem_folha,
            'dados_grupos' => $dadosGrupos,
        ];


    }
}
