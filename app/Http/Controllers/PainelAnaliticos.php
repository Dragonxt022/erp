<?php

namespace App\Http\Controllers;

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

class PainelAnaliticos extends Controller
{
    /**
     * Calcula o CMV (Custo de Mercadoria Vendida) de um período
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function calcularCMV(Request $request)
    {
        $usuario = Auth::user();
        $unidadeId = $usuario->unidade_id;

        // Obter as datas de início e fim
        try {
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('d-m-Y'));
            $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('d-m-Y'));

            // Converter para Carbon e garantir que as datas incluam todo o período do dia
            $startDateConverted = Carbon::createFromFormat('d-m-Y', $startDate)->startOfDay(); // 00:00:00
            $endDateConverted = Carbon::createFromFormat('d-m-Y', $endDate)->endOfDay(); // 23:59:59
        } catch (\Exception $e) {
            return response()->json(['error' => 'Formato de data inválido. Use o formato DD-MM-YYYY.'], 400);
        }



        // Função para calcular o valor baseado na unidade (kg ou unidade)
        // 1. Calcular o CMV
        $saldoInicial = ControleSaldoEstoque::where('unidade_id', $unidadeId)
            ->whereDate('data_ajuste', '=', $startDateConverted)
            ->orderBy('data_ajuste', 'desc')
            ->first();

        if (!$saldoInicial) {
            $saldoInicial = ControleSaldoEstoque::where('unidade_id', $unidadeId)
                ->whereDate('data_ajuste', '<', $startDateConverted)
                ->orderBy('data_ajuste', 'desc')
                ->first();

            if (!$saldoInicial) {
                $saldoInicial = ControleSaldoEstoque::where('unidade_id', $unidadeId)
                    ->orderBy('data_ajuste', 'asc') // Busca o primeiro ajuste da unidade
                    ->first();

                if (!$saldoInicial) {
                    return response()->json(['error' => 'Não há saldo inicial disponível para esta unidade.'], 400);
                }
            }
        }

        $estoqueInicialValor = $saldoInicial ? $saldoInicial->ajuste_saldo : 0;
    
        // Compras no período
        $compras = MovimentacoesEstoque::where('unidade_id', $unidadeId)
            ->where('operacao', 'Entrada')
            ->whereBetween('created_at', [$startDateConverted, $endDateConverted])
            ->get();
    
        $comprasValor = $compras->sum(fn($item) => $item->preco_insumo * $item->quantidade);
    
        // Estoque final
        $estoqueFinal = UnidadeEstoque::where('unidade_id', $unidadeId)
            ->where('quantidade', '>', 0)
            ->where('created_at', '<=', $endDateConverted)
            ->get();
    
        $estoqueFinalValor = $estoqueFinal->sum(fn($item) => $item->preco_insumo * $item->quantidade);
    
        // Calcular o CMV
        $cmv = $estoqueInicialValor + $comprasValor - $estoqueFinalValor;

        Log::info('Calculando CMV', [
            'estoqueInicialValor' => $estoqueInicialValor,
            'comprasValor' => $comprasValor,
            'estoqueFinalValor' => $estoqueFinalValor
        ]);

        // Retornar os resultados em um único JSON
        return response()->json([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'saldo_estoque_inicial' => number_format($estoqueInicialValor, 2, ',', '.'),
            'entradas_durante_periodo' => number_format($comprasValor, 2, ',', '.'),
            'saldo_estoque_final' => number_format($estoqueFinalValor, 2, ',', '.'),
            'cmv' => number_format($cmv, 2, ',', '.'),

        ]);
    }


    /**
     * Somar todos os valores dos caixas de uma unidade
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function somarTodosOsCaixas(Request $request)
    {
        $usuario = Auth::user();
        $unidade_id = $usuario->unidade_id;

        // Verifica se as datas foram enviadas, senão usa o mês atual
        try {
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('d-m-Y'));
            $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('d-m-Y'));

            $startDateConverted = Carbon::createFromFormat('d-m-Y', $startDate)->format('Y-m-d');
            $endDateConverted = Carbon::createFromFormat('d-m-Y', $endDate)->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Formato de data inválido. Use o formato DD-MM-YYYY.'], 400);
        }

        // Soma apenas os caixas fechados (status = 0) no período selecionado
        $totalCaixas = Caixa::where('unidade_id', $unidade_id)
            ->where('status', 0) // Apenas caixas fechados
            ->whereBetween('created_at', [$startDateConverted, $endDateConverted]) // Filtro por data
            ->sum('valor_final');

        // 3. Quantidade de pedidos e faturamento
        $pedidos = CanalVenda::where('unidade_id', $unidade_id)
            ->whereBetween('created_at', [$startDateConverted, $endDateConverted])
            ->get();

        $quantidadePedidos = $pedidos->sum('quantidade_vendas_feitas'); // Total de pedidos realizados
        $faturamentoTotal = $pedidos->sum('valor_total_vendas'); // Faturamento total durante o período

        // 4. Calcular o Ticket Médio
        $ticketMedio = $quantidadePedidos > 0 ? $faturamentoTotal / $quantidadePedidos : 0;

        return response()->json([
            'start_date' => $startDateConverted,
            'end_date' => $endDateConverted,
            'total_caixas' => number_format($totalCaixas, 2, ',', '.'),
            'quantidade_pedidos' => $quantidadePedidos,
            'ticket_medio' => number_format($ticketMedio, 2, ',', '.'),


        ]);
    }


    /**
     * Retorna o faturamento dos últimos 30 dias
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function diasDoMes()
    {
        $usuario = Auth::user();
        $unidade_id = $usuario->unidade_id;

        // Geração da lista de dias numéricos do último mês
        $dias = collect(range(0, 30))->map(function ($i) {
            return Carbon::now()->subDays($i)->format('d');
        })->reverse();

        // Detecta o banco de dados e ajusta o formato do dia
        $driver = DB::connection()->getPDO()->getAttribute(\PDO::ATTR_DRIVER_NAME);
        $diaFormat = $driver === 'mysql' ? "DAY(created_at)" : "strftime('%d', created_at)";

        // Consulta o faturamento diário do caixa fechado
        $faturamento = Caixa::where('unidade_id', $unidade_id)
            ->where('status', 0)
            ->whereBetween('created_at', [Carbon::now()->subDays(30), Carbon::now()])
            ->selectRaw("$diaFormat as dia, SUM(valor_final) as total")
            ->groupBy('dia')
            ->orderBy('dia', 'asc')
            ->get()
            ->mapWithKeys(fn($item) => [(int) $item->dia => $item->total]); // Converte a chave para inteiro

        // Garante que todos os dias apareçam, mesmo se não houver faturamento
        $faturamentoPorDia = $dias->map(fn($dia) => [
            'dia' => $dia,
            'total' => $faturamento[(int) $dia] ?? 0, // Retorna 0 se não houver faturamento
        ]);

        return response()->json([
            'status' => 'sucesso',
            'data_resposta' => now()->format('d-m-Y H:i:s'),
            'faturamento' => $faturamentoPorDia,
        ]);
    }

    /**
     * Calcula o CMV e soma o valor de todos os caixas de uma unidade no mesmo período
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function calcularCMVESomarCaixas(Request $request)
    {
        $usuario = Auth::user();
        $unidadeId = $usuario->unidade_id;

        // Obter as datas de início e fim
        try {
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('d-m-Y'));
            $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('d-m-Y'));

            $startDateConverted = Carbon::createFromFormat('d-m-Y', $startDate)->format('Y-m-d');
            $endDateConverted = Carbon::createFromFormat('d-m-Y', $endDate)->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Formato de data inválido. Use o formato DD-MM-YYYY.'], 400);
        }

        // 1. Calcular o CMV
        $saldoInicial = ControleSaldoEstoque::where('unidade_id', $unidadeId)
            ->whereDate('data_ajuste', '=', $startDateConverted)
            ->orderBy('data_ajuste', 'desc')
            ->first();

        if (!$saldoInicial) {
            $saldoInicial = ControleSaldoEstoque::where('unidade_id', $unidadeId)
                ->whereDate('data_ajuste', '<', $startDateConverted)
                ->orderBy('data_ajuste', 'desc')
                ->first();

            if (!$saldoInicial) {
                $saldoInicial = ControleSaldoEstoque::where('unidade_id', $unidadeId)
                    ->orderBy('data_ajuste', 'asc') // Busca o primeiro ajuste da unidade
                    ->first();

                if (!$saldoInicial) {
                    return response()->json(['error' => 'Não há saldo inicial disponível para esta unidade.'], 400);
                }
            }
        }

        $estoqueInicialValor = $saldoInicial ? $saldoInicial->ajuste_saldo : 0;
    
        // Compras no período
        $compras = MovimentacoesEstoque::where('unidade_id', $unidadeId)
            ->where('operacao', 'Entrada')
            ->whereBetween('created_at', [$startDateConverted, $endDateConverted])
            ->get();
    
        $comprasValor = $compras->sum(fn($item) => $item->preco_insumo * $item->quantidade);
    
        // Estoque final
        $estoqueFinal = UnidadeEstoque::where('unidade_id', $unidadeId)
            ->where('quantidade', '>', 0)
            ->where('created_at', '<=', $endDateConverted)
            ->get();
    
        $estoqueFinalValor = $estoqueFinal->sum(fn($item) => $item->preco_insumo * $item->quantidade);
    
        // Calcular o CMV
        $cmv = $estoqueInicialValor + $comprasValor - $estoqueFinalValor;

        // 2. Somar todos os caixas fechados no período
        $totalCaixas = Caixa::where('unidade_id', $unidadeId)
            ->where('status', 0) // Apenas caixas fechados
            ->whereBetween('created_at', [$startDateConverted, $endDateConverted])
            ->sum('valor_final');

        // Retornar os resultados em um único JSON
        return response()->json([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'saldo_estoque_inicial' => number_format($estoqueInicialValor, 2, ',', '.'),
            'entradas_durante_periodo' => number_format($comprasValor, 2, ',', '.'),
            'saldo_estoque_final' => number_format($estoqueFinalValor, 2, ',', '.'),
            'cmv' => number_format($cmv, 2, ',', '.'),
            'total_caixas' => number_format($totalCaixas, 2, ',', '.'),
        ]);
    }

    // Foruma que junta 3 fuções em uma só
    public function calcularIndicadores(Request $request)
    {
        $usuario = Auth::user();
        $unidadeId = $usuario->unidade_id;

        // Obter as datas de início e fim
        try {
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('d-m-Y'));
            $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('d-m-Y'));

            // Converter para Carbon e garantir que as datas incluam todo o período do dia
            $startDateConverted = Carbon::createFromFormat('d-m-Y', $startDate)->startOfDay(); // 00:00:00
            $endDateConverted = Carbon::createFromFormat('d-m-Y', $endDate)->endOfDay(); // 23:59:59
        } catch (\Exception $e) {
            return response()->json(['error' => 'Formato de data inválido. Use o formato DD-MM-YYYY.'], 400);
        }


       // 1. Calcular o CMV
        $saldoInicial = ControleSaldoEstoque::where('unidade_id', $unidadeId)
            ->whereDate('data_ajuste', '=', $startDateConverted)
            ->orderBy('data_ajuste', 'desc')
            ->first();

        if (!$saldoInicial) {
            $saldoInicial = ControleSaldoEstoque::where('unidade_id', $unidadeId)
                ->whereDate('data_ajuste', '<', $startDateConverted)
                ->orderBy('data_ajuste', 'desc')
                ->first();

            if (!$saldoInicial) {
                $saldoInicial = ControleSaldoEstoque::where('unidade_id', $unidadeId)
                    ->orderBy('data_ajuste', 'asc') // Busca o primeiro ajuste da unidade
                    ->first();

                if (!$saldoInicial) {
                    return response()->json(['error' => 'Não há saldo inicial disponível para esta unidade.'], 400);
                }
            }
        }

        $estoqueInicialValor = $saldoInicial ? $saldoInicial->ajuste_saldo : 0;
    
        // Compras no período
        $compras = MovimentacoesEstoque::where('unidade_id', $unidadeId)
            ->where('operacao', 'Entrada')
            ->whereBetween('created_at', [$startDateConverted, $endDateConverted])
            ->get();
    
        $comprasValor = $compras->sum(fn($item) => $item->preco_insumo * $item->quantidade);
    
        // Estoque final
        $estoqueFinal = UnidadeEstoque::where('unidade_id', $unidadeId)
            ->where('quantidade', '>', 0)
            ->where('created_at', '<=', $endDateConverted)
            ->get();
    
        $estoqueFinalValor = $estoqueFinal->sum(fn($item) => $item->preco_insumo * $item->quantidade);
    
        // Calcular o CMV
        $cmv = $estoqueInicialValor + $comprasValor - $estoqueFinalValor;


        $startDateConverted = Carbon::parse($startDate)->startOfDay(); // Começa em 00:00:00
        $endDateConverted = Carbon::parse($endDate)->endOfDay(); // Termina em 23:59:59

        // 2. Somar todos os caixas fechados no período
        $totalCaixas = Caixa::where('unidade_id', $unidadeId)
            ->where('status', 0) // Apenas caixas fechados
            ->whereBetween('created_at', [$startDateConverted, $endDateConverted])
            ->sum('valor_final');



        // 3. Quantidade de pedidos e faturamento
        $pedidos = CanalVenda::where('unidade_id', $unidadeId)
            ->whereBetween('created_at', [$startDateConverted, $endDateConverted])
            ->get();

        $quantidadePedidos = $pedidos->sum('quantidade_vendas_feitas'); // Total de pedidos realizados
        $faturamentoTotal = $pedidos->sum('valor_total_vendas'); // Faturamento total durante o período

        // 4. Calcular o Ticket Médio
        $ticketMedio = $quantidadePedidos > 0 ? $faturamentoTotal / $quantidadePedidos : 0;

        // Retornar os resultados em um único JSON
        return response()->json([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'saldo_estoque_inicial' => number_format($estoqueInicialValor, 2, ',', '.'),
            'entradas_durante_periodo' => number_format($comprasValor, 2, ',', '.'),
            'saldo_estoque_final' => number_format($estoqueFinalValor, 2, ',', '.'),
            'cmv' => number_format($cmv, 2, ',', '.'),
            'total_caixas' => number_format($totalCaixas, 2, ',', '.'),
            'quantidade_pedidos' => $quantidadePedidos,
            'ticket_medio' => number_format($ticketMedio, 2, ',', '.'),
        ]);
    }

    /**
     * Calcula o Ticket Médio e a quantidade de pedidos de uma unidade em um período
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function calcularTicketMedioEQuantidadePedidos(Request $request)
    {
        $usuario = Auth::user();
        $unidade_id = $usuario->unidade_id;

        // Obter as datas de início e fim
        try {
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('d-m-Y'));
            $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('d-m-Y'));

            $startDateConverted = Carbon::createFromFormat('d-m-Y', $startDate)->format('Y-m-d');
            $endDateConverted = Carbon::createFromFormat('d-m-Y', $endDate)->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Formato de data inválido. Use o formato DD-MM-YYYY.'], 400);
        }

        // 1. Quantidade de pedidos e faturamento
        $pedidos = CanalVenda::where('unidade_id', $unidade_id)
            ->whereBetween('created_at', [$startDateConverted, $endDateConverted])
            ->get();

        $quantidadePedidos = $pedidos->sum('quantidade_vendas_feitas'); // Total de pedidos realizados
        $faturamentoTotal = $pedidos->sum('valor_total_vendas'); // Faturamento total durante o período

        // 2. Calcular o Ticket Médio
        $ticketMedio = $quantidadePedidos > 0 ? $faturamentoTotal / $quantidadePedidos : 0;

        // Retornar as informações em formato JSON
        return response()->json([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'quantidade_pedidos' => $quantidadePedidos,
            'ticket_medio' => number_format($ticketMedio, 2, ',', '.'),
        ]);
    }


    /**
     * Analitycs da Pagina DRE
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function analitycsDRE(Request $request)
    {
        $usuario = Auth::user();
        $unidadeId = $usuario->unidade_id;

        try {
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('d-m-Y'));
            $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('d-m-Y'));

            $startDateConverted = Carbon::createFromFormat('d-m-Y', $startDate)->startOfDay()->format('Y-m-d H:i:s');
            $endDateConverted = Carbon::createFromFormat('d-m-Y', $endDate)->endOfDay()->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Formato de data inválido. Use o formato DD-MM-YYYY.'], 400);
        }

        // 1. Calcular o CMV
        $saldoInicial = ControleSaldoEstoque::where('unidade_id', $unidadeId)
            ->whereDate('data_ajuste', '=', $startDateConverted)
            ->orderBy('data_ajuste', 'desc')
            ->first();

        if (!$saldoInicial) {
            $saldoInicial = ControleSaldoEstoque::where('unidade_id', $unidadeId)
                ->whereDate('data_ajuste', '<', $startDateConverted)
                ->orderBy('data_ajuste', 'desc')
                ->first();

            if (!$saldoInicial) {
                $saldoInicial = ControleSaldoEstoque::where('unidade_id', $unidadeId)
                    ->orderBy('data_ajuste', 'asc') // Busca o primeiro ajuste da unidade
                    ->first();

                if (!$saldoInicial) {
                    return response()->json(['error' => 'Não há saldo inicial disponível para esta unidade.'], 400);
                }
            }
        }

        $estoqueInicialValor = $saldoInicial ? $saldoInicial->ajuste_saldo : 0;
    
        // Compras no período
        $compras = MovimentacoesEstoque::where('unidade_id', $unidadeId)
            ->where('operacao', 'Entrada')
            ->whereBetween('created_at', [$startDateConverted, $endDateConverted])
            ->get();
    
        $comprasValor = $compras->sum(fn($item) => $item->preco_insumo * $item->quantidade);
    
        // Estoque final
        $estoqueFinal = UnidadeEstoque::where('unidade_id', $unidadeId)
            ->where('quantidade', '>', 0)
            ->where('created_at', '<=', $endDateConverted)
            ->get();
    
        $estoqueFinalValor = $estoqueFinal->sum(fn($item) => $item->preco_insumo * $item->quantidade);
    
        // Calcular o CMV
        $cmv = $estoqueInicialValor + $comprasValor - $estoqueFinalValor;

        // Soma total dos caixas fechados no período
        $totalCaixas = Caixa::where('unidade_id', $unidadeId)
            ->where('status', 0)
            ->whereBetween('created_at', [$startDateConverted, $endDateConverted])
            ->sum('valor_final');


        // Soma dos salários dos usuários da unidade dentro do período e com salário registrado
        $totalSalarios = User::where('unidade_id', $unidadeId)
            ->whereNotNull('salario') // Garante que o salário não é nulo
            ->whereBetween('created_at', [$startDateConverted, $endDateConverted]) // Filtra pela data de criação do usuário
            ->sum('salario');

        // Soma total pago para Motoboy no período
        $totalMotoboy = ContaAPagar::where('unidade_id', $unidadeId)
            ->where('categoria_id', function ($query) {
                $query->select('id')
                    ->from('categorias')
                    ->where('nome', 'Motoboy');
            })
            ->whereIn('status', ['pago', 'pendente'])
            ->whereBetween('emitida_em', [$startDateConverted, $endDateConverted])
            ->sum('valor');

        // Calcula Royalties: 5% de (total de caixas - valor pago ao motoboy)
        $totalLiquido = $totalCaixas - $totalMotoboy;
        $totalRoyalties = $totalLiquido * 0.05;

        //Calucla o Fundo de Propaganda: 1.5% ( total de caixas - valor pago ao motoboy)
        $totalLiquido = $totalCaixas - $totalMotoboy;
        $totalFundoPropaganda = $totalLiquido * 0.015;


        // Calcula FGTS: 8% da soma total dos salários
        $totalFGTS = $totalSalarios * 0.08;

        // Soma total das taxas de métodos de pagamento por tipo no período
        $creditoIds = DB::table('default_payment_methods')->where('tipo', 'credito')->pluck('id');
        $totalTaxasCredito = FechamentoCaixa::where('unidade_id', $unidadeId)
            ->whereIn('metodo_pagamento_id', $creditoIds)
            ->whereBetween('created_at', [$startDateConverted, $endDateConverted])
            ->sum('valor_taxa_metodo');

        $debitoIds = DB::table('default_payment_methods')->where('tipo', 'debito')->pluck('id');
        $totalTaxasDebito = FechamentoCaixa::where('unidade_id', $unidadeId)
            ->whereIn('metodo_pagamento_id', $debitoIds)
            ->whereBetween('created_at', [$startDateConverted, $endDateConverted])
            ->sum('valor_taxa_metodo');

        $vrAlimentacaoIds = DB::table('default_payment_methods')->where('tipo', 'vr_alimentacao')->pluck('id');
        $totalTaxasVrAlimentacao = FechamentoCaixa::where('unidade_id', $unidadeId)
            ->whereIn('metodo_pagamento_id', $vrAlimentacaoIds)
            ->whereBetween('created_at', [$startDateConverted, $endDateConverted])
            ->sum('valor_taxa_metodo');

        // Soma total das taxas de canais de vendas no período
        $totalTaxasCanais = CanalVenda::where('unidade_id', $unidadeId)
            ->whereBetween('created_at', [$startDateConverted, $endDateConverted])
            ->sum('valor_taxa_canal');

        $categoriasRemovidas = ["Fornecedores"];

        // Defina as categorias que devem ser ignoradas na soma
        $categoriasIgnoradasNaSoma = ["Folha de pagamento", "Fornecedores"];

        // Buscar todos os grupos de categorias com suas categorias associadas
        $grupos = GrupoDeCategorias::with('categorias')->get();

        // Variáveis para calcular os totais
        $totalDespesasCategorias = 0;
        $totalDespesasCategoriasSemFolha = 0;

        $dadosGrupos = $grupos->map(function ($grupo) use (
            $unidadeId,
            $startDateConverted,
            $endDateConverted,
            $totalSalarios,
            $totalRoyalties,
            $totalFundoPropaganda,
            $totalFGTS,
            $totalTaxasCredito,
            $totalTaxasDebito,
            $totalTaxasVrAlimentacao,
            $totalTaxasCanais,
            $cmv,
            &$totalDespesasCategorias,
            &$totalDespesasCategoriasSemFolha,
            $categoriasRemovidas,
            $categoriasIgnoradasNaSoma
        ) {
            $categoriasFormatadas = $grupo->categorias
                ->reject(fn($categoria) => in_array($categoria->nome, $categoriasRemovidas)) // Remove categorias indesejadas
                ->map(function ($categoria) use (
                    $unidadeId,
                    $startDateConverted,
                    $endDateConverted,
                    $totalSalarios,
                    $totalRoyalties,
                    $totalFundoPropaganda,
                    $totalFGTS,
                    $totalTaxasCredito,
                    $totalTaxasDebito,
                    $totalTaxasVrAlimentacao,
                    $totalTaxasCanais,
                    $cmv,
                    &$totalDespesasCategorias,
                    &$totalDespesasCategoriasSemFolha,
                    $categoriasIgnoradasNaSoma
                ) {
                    // Valor padrão obtido de ContaAPagar
                    $valor = ContaAPagar::where('categoria_id', $categoria->id)
                        ->where('unidade_id', $unidadeId)
                        ->whereIn('status', ['pago', 'pendente'])
                        ->whereBetween('emitida_em', [$startDateConverted, $endDateConverted])
                        ->sum('valor');

                    // Se a categoria tiver valor especial, sobrescreve
                    $valoresFixos = [
                        "Mercadoria Vendida" => $cmv,
                        "FGTS" => $totalFGTS,
                        "Folha de pagamento" => $totalSalarios,
                        "Royalties" => $totalRoyalties,
                        "Fundo de propaganda" =>  $totalFundoPropaganda,
                        "Taxa de Crédito" => $totalTaxasCredito,
                        "Taxa de Débito" => $totalTaxasDebito,
                        "Plataformas de Delivery" => $totalTaxasCanais,
                        "Voucher Alimentação" => $totalTaxasVrAlimentacao
                    ];

                    if (isset($valoresFixos[$categoria->nome])) {
                        $valor = $valoresFixos[$categoria->nome];
                    }

                    // Soma apenas se a categoria NÃO estiver na lista de ignoradas
                    if (!in_array($categoria->nome, $categoriasIgnoradasNaSoma)) {
                        $totalDespesasCategoriasSemFolha += $valor;
                    }

                    // Soma sempre no total geral
                    $totalDespesasCategorias += $valor;

                    return [
                        'categoria' => $categoria->nome,
                        'total' => number_format($valor, 2, ',', '.')
                    ];
                });

            return [
                'nome_grupo' => $grupo->nome,
                'categorias' => $categoriasFormatadas
            ];
        });

        // Resultado considerando todas as categorias
        $resultado_do_periodo = $totalDespesasCategorias - $totalCaixas;

        // Resultado ignorando "Folha de pagamento"
        $resultado_do_periodo_sem_folha = $totalCaixas - $totalDespesasCategoriasSemFolha;


        $year = Carbon::parse($startDateConverted)->year; // ou um ano específico, se enviado no request
        $calendario = $this->getCalendarData($year, $unidadeId);



        return response()->json([
            'start_date' => $startDateConverted,
            'end_date' => $endDateConverted,
            'total_caixas' => number_format($totalCaixas, 2, ',', '.'),
            'total_despesas_categorias' => number_format($totalDespesasCategorias, 2, ',', '.'),
            'resultado_do_periodo' => number_format($resultado_do_periodo_sem_folha, 2, ',', '.'),
            'total_salarios'  => number_format($totalSalarios, 2, ',', '.'),
            'grupos' => $dadosGrupos,
            'calendario' => $calendario,

        ]);
    }

    private function getCalendarData($year, $unidadeId)
    {
        $meses = [
            1  => 'Janeiro',
            2  => 'Fevereiro',
            3  => 'Março',
            4  => 'Abril',
            5  => 'Maio',
            6  => 'Junho',
            7  => 'Julho',
            8  => 'Agosto',
            9  => 'Setembro',
            10 => 'Outubro',
            11 => 'Novembro',
            12 => 'Dezembro'
        ];

        return collect(range(1, 12))->map(function ($month) use ($year, $unidadeId, $meses) {
            // Definindo o início e o fim do mês com a formatação completa de data e hora
            $startMonth = Carbon::create($year, $month, 1)->startOfDay(); // 00:00:00
            $endMonth = Carbon::create($year, $month, Carbon::create($year, $month, 1)->daysInMonth)->endOfDay(); // 23:59:59

            // 1. Calcular o CMV
            // Saldo inicial de estoque
            $saldoInicial = ControleSaldoEstoque::where('unidade_id', $unidadeId)
                ->whereDate('data_ajuste', '=', [$startMonth->format('Y-m-d H:i:s'), $endMonth->format('Y-m-d H:i:s')])
                ->orderBy('data_ajuste', 'desc')
                ->first();

            if (!$saldoInicial) {
                $saldoInicial = ControleSaldoEstoque::where('unidade_id', $unidadeId)
                    ->whereDate('data_ajuste', '<', [$startMonth->format('Y-m-d H:i:s'), $endMonth->format('Y-m-d H:i:s')])
                    ->orderBy('data_ajuste', 'desc')
                    ->first();

                if (!$saldoInicial) {
                    $saldoInicial = ControleSaldoEstoque::where('unidade_id', $unidadeId)
                        ->orderBy('data_ajuste', 'asc') // Busca o primeiro ajuste da unidade
                        ->first();

                    if (!$saldoInicial) {
                        return response()->json(['error' => 'Não há saldo inicial disponível para esta unidade.'], 400);
                    }
                }
            }



            $estoqueInicialValor = $saldoInicial ? $saldoInicial->ajuste_saldo : 0;


            // Compras no período
            $compras = MovimentacoesEstoque::where('unidade_id', $unidadeId)
                ->where('operacao', 'Entrada')
                ->whereBetween('created_at', [
                    Carbon::parse($startMonth)->addDay()->format('Y-m-d H:i:s'),
                    $endMonth->format('Y-m-d H:i:s')
                ])
                ->get();
            
            $comprasValor = $compras->sum(fn($item) => $item->preco_insumo * $item->quantidade);

            // Estoque final
            $estoqueFinal = UnidadeEstoque::where('unidade_id', $unidadeId)
                ->whereDate('created_at', '<=', $endMonth->format('Y-m-d H:i:s'))
                ->get();

            $estoqueFinalValor = $estoqueFinal->sum(fn($item) => $item->preco_insumo * $item->quantidade);


            // Calcular o CMV
            $valor_cmv = $estoqueInicialValor + $comprasValor - $estoqueFinalValor;


            // Calcular outras despesas do mês (usando os mesmos filtros do DRE)
            $totalCaixas = Caixa::where('unidade_id', $unidadeId)
                ->where('status', 0)
                ->whereBetween('created_at', [$startMonth->format('Y-m-d H:i:s'), $endMonth->format('Y-m-d H:i:s')])
                ->sum('valor_final');

            $totalSalarios = User::where('unidade_id', $unidadeId)
                ->whereNotNull('salario')
                ->whereBetween('created_at', [$startMonth->format('Y-m-d H:i:s'), $endMonth->format('Y-m-d H:i:s')])
                ->sum('salario');

            $totalMotoboy = ContaAPagar::where('unidade_id', $unidadeId)
                ->where('categoria_id', function ($query) {
                    $query->select('id')
                        ->from('categorias')
                        ->where('nome', 'Motoboy');
                })
                ->whereIn('status', ['pago', 'pendente'])
                ->whereBetween('emitida_em', [$startMonth->format('Y-m-d H:i:s'), $endMonth->format('Y-m-d H:i:s')])
                ->sum('valor');

            // Calcula Royalties: 5% de (total de caixas - valor pago ao motoboy)
            $totalLiquido = $totalCaixas - $totalMotoboy;
            $totalRoyalties = $totalLiquido * 0.05;

            //Calucla o Fundo de Propaganda: 1.5% ( total de caixas - valor pago ao motoboy)
            $totalLiquido = $totalCaixas - $totalMotoboy;
            $totalFundoPropaganda = $totalLiquido * 0.015;


            // Calcula FGTS: 8% da soma total dos salários
            $totalFGTS = $totalSalarios * 0.08;

            // Soma total das taxas de métodos de pagamento por tipo no período
            $creditoIds = DB::table('default_payment_methods')->where('tipo', 'credito')->pluck('id');
            $totalTaxasCredito = FechamentoCaixa::where('unidade_id', $unidadeId)
                ->whereIn('metodo_pagamento_id', $creditoIds)
                ->whereBetween('created_at', [$startMonth->format('Y-m-d H:i:s'), $endMonth->format('Y-m-d H:i:s')])
                ->sum('valor_taxa_metodo');

            $debitoIds = DB::table('default_payment_methods')->where('tipo', 'debito')->pluck('id');
            $totalTaxasDebito = FechamentoCaixa::where('unidade_id', $unidadeId)
                ->whereIn('metodo_pagamento_id', $debitoIds)
                ->whereBetween('created_at', [$startMonth->format('Y-m-d H:i:s'), $endMonth->format('Y-m-d H:i:s')])
                ->sum('valor_taxa_metodo');

            $vrAlimentacaoIds = DB::table('default_payment_methods')->where('tipo', 'vr_alimentacao')->pluck('id');
            $totalTaxasVrAlimentacao = FechamentoCaixa::where('unidade_id', $unidadeId)
                ->whereIn('metodo_pagamento_id', $vrAlimentacaoIds)
                ->whereBetween('created_at', [$startMonth->format('Y-m-d H:i:s'), $endMonth->format('Y-m-d H:i:s')])
                ->sum('valor_taxa_metodo');

            $totalTaxasCanais = CanalVenda::where('unidade_id', $unidadeId)
                ->whereBetween('created_at', [$startMonth->format('Y-m-d H:i:s'), $endMonth->format('Y-m-d H:i:s')])
                ->sum('valor_taxa_canal');


            // Defina as categorias que devem ser removidas completamente
            $categoriasRemovidas = ["Fornecedores"];

            // Defina as categorias que devem ser ignoradas na soma
            $categoriasIgnoradasNaSoma = ["Folha de pagamento", "Fornecedores"];

            // Buscar todos os grupos de categorias com suas categorias associadas
            $grupos = GrupoDeCategorias::with('categorias')->get();

            // Variáveis para calcular os totais
            $totalDespesasCategorias = 0;
            $totalDespesasCategoriasSemFolha = 0;

            $dadosGrupos = $grupos->map(function ($grupo) use (
                $unidadeId,
                $startMonth,
                $endMonth,
                $totalSalarios,
                $totalRoyalties,
                $totalFundoPropaganda,
                $totalFGTS,
                $totalTaxasCredito,
                $totalTaxasDebito,
                $totalTaxasVrAlimentacao,
                $totalTaxasCanais,
                $valor_cmv,
                &$totalDespesasCategorias,
                &$totalDespesasCategoriasSemFolha,
                $categoriasRemovidas,
                $categoriasIgnoradasNaSoma
            ) {
                $categoriasFormatadas = $grupo->categorias
                    ->reject(fn($categoria) => in_array($categoria->nome, $categoriasRemovidas)) // Remove categorias indesejadas
                    ->map(function ($categoria) use (
                        $unidadeId,
                        $startMonth,
                        $endMonth,
                        $totalSalarios,
                        $totalRoyalties,
                        $totalFundoPropaganda,
                        $totalFGTS,
                        $totalTaxasCredito,
                        $totalTaxasDebito,
                        $totalTaxasVrAlimentacao,
                        $totalTaxasCanais,
                        $valor_cmv,
                        &$totalDespesasCategorias,
                        &$totalDespesasCategoriasSemFolha,
                        $categoriasIgnoradasNaSoma
                    ) {
                        // Valor padrão obtido de ContaAPagar
                        $valor = ContaAPagar::where('categoria_id', $categoria->id)
                            ->where('unidade_id', $unidadeId)
                            ->whereIn('status', ['pago', 'pendente'])
                            ->whereBetween('emitida_em', [$startMonth->format('Y-m-d H:i:s'), $endMonth->format('Y-m-d H:i:s')])
                            ->sum('valor');

                        // Se a categoria tiver valor especial, sobrescreve
                        $valoresFixos = [
                            "Mercadoria Vendida" => $valor_cmv,
                            "FGTS" => $totalFGTS,
                            "Folha de pagamento" => $totalSalarios,
                            "Royalties" => $totalRoyalties,
                            "Fundo de propaganda" =>  $totalFundoPropaganda,
                            "Taxa de Crédito" => $totalTaxasCredito,
                            "Taxa de Débito" => $totalTaxasDebito,
                            "Plataformas de Delivery" => $totalTaxasCanais,
                            "Voucher Alimentação" => $totalTaxasVrAlimentacao
                        ];

                        if (isset($valoresFixos[$categoria->nome])) {
                            $valor = $valoresFixos[$categoria->nome];
                        }

                        // Soma apenas se a categoria NÃO estiver na lista de ignoradas
                        if (!in_array($categoria->nome, $categoriasIgnoradasNaSoma)) {
                            $totalDespesasCategoriasSemFolha += $valor;
                        }

                        // Soma sempre no total geral
                        $totalDespesasCategorias += $valor;

                        return [
                            'categoria' => $categoria->nome,
                            'total' => number_format($valor, 2, ',', '.')
                        ];
                    });

                return [
                    'nome_grupo' => $grupo->nome,
                    'categorias' => $categoriasFormatadas
                ];
            });

            // Resultado considerando todas as categorias
            $resultado_do_periodo = $totalDespesasCategorias - $totalCaixas;

            // Resultado ignorando "Folha de pagamento"
            $resultado_do_periodo_sem_folha = $totalCaixas - $totalDespesasCategoriasSemFolha;

            return [
                'nome_mes' => $meses[$month],
                'categorias' => [
                    [
                        'data_inicio_mes' => $startMonth->format('Y-m-d H:i:s'),
                        'data_final_mes' => $endMonth->format('Y-m-d H:i:s'),
                        'total_caixas' => number_format($totalCaixas, 2, ',', '.'),
                        'total_dispesas' => number_format($totalDespesasCategorias, 2, ',', '.'),
                        'resultado_do_periodo' => number_format($resultado_do_periodo_sem_folha, 2, ',', '.'),
                        'valor_cmv' => number_format($valor_cmv, 2, ',', '.'),

                    ]
                ]
            ];
        });
    }
}
