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
use App\Services\AnalyticService; // Importe o AnalyticService
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PainelAnaliticos extends Controller
{
    /**
     * Serviço próprio para lidar com CMV e DRE
     */
    protected $analyticService;

    public function __construct(AnalyticService $analyticService)
    {
        $this->analyticService = $analyticService;
    }

    /**
     * Calcula o CMV (Custo de Mercadoria Vendida) de um período.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function calcularCMV(Request $request)
    {
        $usuario = Auth::user();
        $unidadeId = $usuario->unidade_id;

        // 1. Obter e validar as datas de início e fim.
        try {
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('d-m-Y'));
            $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('d-m-Y'));

            $startDateConverted = Carbon::createFromFormat('d-m-Y', $startDate)->startOfDay();
            $endDateConverted = Carbon::createFromFormat('d-m-Y', $endDate)->endOfDay();

            if ($startDateConverted->greaterThan($endDateConverted)) {
                return response()->json(['error' => 'A data de início não pode ser posterior à data de fim.'], 400);
            }

        } catch (\Exception $e) {
            Log::error('Erro ao converter datas para CMV: ' . $e->getMessage());
            return response()->json(['error' => 'Formato de data inválido. Use o formato DD-MM-YYYY.'], 400);
        }

        // --- USO DO SERVIÇO PARA CALCULAR OS VALORES ---
        try {
            // 2. Chamar o método do serviço para calcular todos os dados do período.
            $analysedData = $this->analyticService->calculatePeriodData(
                $unidadeId,
                $startDateConverted,
                $endDateConverted
            );

            // 3. Extrair e formatar os valores específicos para a resposta.
            $estoqueInicialValor = $analysedData['estoqueInicialValor'] ?? 0;
            $comprasValor = $analysedData['comprasValor'] ?? 0;
            $estoqueFinalValor = $analysedData['estoqueFinalValor'] ?? 0;
            $cmv = $analysedData['cmv'] ?? 0;

            Log::info('Calculando CMV para unidade', [
                'unidade_id' => $unidadeId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'estoque_inicial_valor' => $estoqueInicialValor,
                'compras_valor' => $comprasValor,
                'estoque_final_valor' => $estoqueFinalValor,
                'cmv_calculado' => $cmv,
            ]);

            // 4. Retornar os resultados formatados no JSON.
            return response()->json([
                'start_date' => $startDate,
                'end_date' => $endDate,
                'saldo_estoque_inicial' => number_format($estoqueInicialValor, 2, ',', '.'),
                'entradas_durante_periodo' => number_format($comprasValor, 2, ',', '.'),
                'saldo_estoque_final' => number_format($estoqueFinalValor, 2, ',', '.'),
                'cmv' => number_format($cmv, 2, ',', '.'),
            ]);

        } catch (\Exception $e) {
            // Se houver qualquer erro no serviço, trate aqui.
            Log::error('Erro no AnalyticService ao calcular CMV: ' . $e->getMessage());
            return response()->json(['error' => 'Ocorreu um erro ao calcular o CMV.'], 500);
        }
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

            $startDateConverted = Carbon::createFromFormat('d-m-Y', $startDate)->startOfDay();
            $endDateConverted = Carbon::createFromFormat('d-m-Y', $endDate)->endOfDay();
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
            'start_date' => $startDateConverted->format('d-m-Y'),
            'end_date' => $endDateConverted->format('d-m-Y'),
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
            ->whereBetween('created_at', [Carbon::now()->subDays(30)->startOfDay(), Carbon::now()->endOfDay()])
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

            $startDateConverted = Carbon::createFromFormat('d-m-Y', $startDate)->startOfDay();
            $endDateConverted = Carbon::createFromFormat('d-m-Y', $endDate)->endOfDay();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Formato de data inválido. Use o formato DD-MM-YYYY.'], 400);
        }

        // 1. Calcular o CMV (usando a mesma lógica da calculatePeriodData)
        $saldoInicialRegistro = ControleSaldoEstoque::where('unidade_id', $unidadeId)
            ->whereDate('data_ajuste', $startDateConverted->toDateString())
            ->orderBy('data_ajuste', 'desc')
            ->first();

        $estoqueInicialValor = 0;
        if ($saldoInicialRegistro) {
            $estoqueInicialValor = $saldoInicialRegistro->ajuste_saldo;
        } else {
            $saldoInicialRegistro = ControleSaldoEstoque::where('unidade_id', $unidadeId)
                ->whereDate('data_ajuste', '<', $startDateConverted->toDateString())
                ->orderBy('data_ajuste', 'desc')
                ->first();

            if ($saldoInicialRegistro) {
                $estoqueInicialValor = $saldoInicialRegistro->ajuste_saldo;
            } else {
                $primeiroAjuste = ControleSaldoEstoque::where('unidade_id', $unidadeId)
                    ->orderBy('data_ajuste', 'asc')
                    ->first();

                if ($primeiroAjuste) {
                     if ($primeiroAjuste->data_ajuste->greaterThan($startDateConverted)) {
                         $estoqueInicialValor = 0;
                     } else {
                         $estoqueInicialValor = $primeiroAjuste->ajuste_saldo;
                     }
                } else {
                    $estoqueInicialValor = 0;
                }
            }
        }

        // Compras no período
        $comprasValor = MovimentacoesEstoque::where('unidade_id', $unidadeId)
            ->where('operacao', 'Entrada')
            ->whereBetween('created_at', [$startDateConverted, $endDateConverted])
            ->get()
            ->sum(function ($item) {
                $preco = is_numeric($item->preco_insumo) ? (float) $item->preco_insumo : 0;
                $quantidade = is_numeric($item->quantidade) ? (float) $item->quantidade : 0;
                return $preco * $quantidade;
            });

        // Estoque final
        $saldoFinalControle = ControleSaldoEstoque::where('unidade_id', $unidadeId)
            ->where('data_ajuste', '<=', $endDateConverted)
            ->orderBy('data_ajuste', 'desc')
            ->first();
        $estoqueFinalValor = $saldoFinalControle ? $saldoFinalControle->ajuste_saldo : 0;

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

    // Função que junta 3 funções em uma só
    public function calcularIndicadores(Request $request)
    {
        $usuario = Auth::user();
        $unidadeId = $usuario->unidade_id;

        // Obter as datas de início e fim
        try {
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('d-m-Y'));
            $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('d-m-Y'));

            // Converter para Carbon e garantir que as datas incluam todo o período do dia
            $startDateConverted = Carbon::createFromFormat('d-m-Y', $startDate)->startOfDay();
            $endDateConverted = Carbon::createFromFormat('d-m-Y', $endDate)->endOfDay();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Formato de data inválido. Use o formato DD-MM-YYYY.'], 400);
        }


       // 1. Calcular o CMV (usando a mesma lógica da calculatePeriodData)
        $saldoInicialRegistro = ControleSaldoEstoque::where('unidade_id', $unidadeId)
            ->whereDate('data_ajuste', $startDateConverted->toDateString())
            ->orderBy('data_ajuste', 'desc')
            ->first();

        $estoqueInicialValor = 0;
        if ($saldoInicialRegistro) {
            $estoqueInicialValor = $saldoInicialRegistro->ajuste_saldo;
        } else {
            $saldoInicialRegistro = ControleSaldoEstoque::where('unidade_id', $unidadeId)
                ->whereDate('data_ajuste', '<', $startDateConverted->toDateString())
                ->orderBy('data_ajuste', 'desc')
                ->first();

            if ($saldoInicialRegistro) {
                $estoqueInicialValor = $saldoInicialRegistro->ajuste_saldo;
            } else {
                $primeiroAjuste = ControleSaldoEstoque::where('unidade_id', $unidadeId)
                    ->orderBy('data_ajuste', 'asc')
                    ->first();

                if ($primeiroAjuste) {
                     if ($primeiroAjuste->data_ajuste->greaterThan($startDateConverted)) {
                         $estoqueInicialValor = 0;
                     } else {
                         $estoqueInicialValor = $primeiroAjuste->ajuste_saldo;
                     }
                } else {
                    $estoqueInicialValor = 0;
                }
            }
        }

        // Compras no período
        $comprasValor = MovimentacoesEstoque::where('unidade_id', $unidadeId)
            ->where('operacao', 'Entrada')
            ->whereBetween('created_at', [$startDateConverted, $endDateConverted])
            ->get()
            ->sum(function ($item) {
                $preco = is_numeric($item->preco_insumo) ? (float) $item->preco_insumo : 0;
                $quantidade = is_numeric($item->quantidade) ? (float) $item->quantidade : 0;
                return $preco * $quantidade;
            });

        // Estoque final
        $saldoFinalControle = ControleSaldoEstoque::where('unidade_id', $unidadeId)
            ->where('data_ajuste', '<=', $endDateConverted)
            ->orderBy('data_ajuste', 'desc')
            ->first();
        $estoqueFinalValor = $saldoFinalControle ? $saldoFinalControle->ajuste_saldo : 0;

        // Calcular o CMV
        $cmv = $estoqueInicialValor + $comprasValor - $estoqueFinalValor;


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

            $startDateConverted = Carbon::createFromFormat('d-m-Y', $startDate)->startOfDay();
            $endDateConverted = Carbon::createFromFormat('d-m-Y', $endDate)->endOfDay();
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

            $startDateCarbon = Carbon::createFromFormat('d-m-Y', $startDate)->startOfDay();
            $endDateCarbon = Carbon::createFromFormat('d-m-Y', $endDate)->endOfDay();

        } catch (\Exception $e) {
            return response()->json(['error' => 'Formato de data inválido. Use o formato DD-MM-YYYY.'], 400);
        }

        // Chame o serviço para obter todos os dados necessários para o período.
        $dreData = $this->analyticService->calculatePeriodData(
            $unidadeId,
            $startDateCarbon,
            $endDateCarbon,
            false,
            null,
            null
        );

        $year = $startDateCarbon->year;
        $meses = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
        ];

        $calendario = collect(range(1, 12))->map(function ($month) use ($year, $unidadeId, $meses) {
            $startOfMonth = Carbon::create($year, $month, 1)->startOfDay();
            $endOfMonth = Carbon::create($year, $month, Carbon::create($year, $month, 1)->daysInMonth)->endOfDay();

            // Chame o serviço para obter os dados de cada mês
            $monthData = $this->analyticService->calculatePeriodData($unidadeId, $startOfMonth, $endOfMonth, true, $month, $year);

            $totalCaixasMes = $monthData['total_caixas'];
            $totalDespesasMes = $monthData['total_despesas_categorias'];
            $cmvMes = $monthData['cmv'];
            $resultadoDoPeriodoMes = $totalCaixasMes - $totalDespesasMes;

            // Lógica para meses futuros no calendário: se não há faturamento, zera despesas e resultado
            // Adicionado Carbon::today() para garantir que a comparação seja com o dia atual, não o final do mês atual.
            if ($startOfMonth->greaterThan(Carbon::today()->endOfDay()) && $totalCaixasMes == 0) {
                $totalDespesasMes = 0;
                $resultadoDoPeriodoMes = 0;
                $cmvMes = 0;
            }

            return [
                'nome_mes' => $meses[$month],
                'data_inicio_mes' => $startOfMonth->format('Y-m-d H:i:s'),
                'data_final_mes' => $endOfMonth->format('Y-m-d H:i:s'),
                'total_caixas' => number_format($totalCaixasMes, 2, ',', '.'),
                'total_despesas' => number_format($totalDespesasMes, 2, ',', '.'),
                'resultado_do_periodo' => number_format($resultadoDoPeriodoMes, 2, ',', '.'),
                'valor_cmv' => number_format($cmvMes, 2, ',', '.'),
            ];
        });

        // Formatar os dados para o response da API principal (DRE do período selecionado)
        $dadosGruposFormatados = $dreData['dados_grupos']->map(function ($grupo) use ($dreData) {
            $grupo['categorias'] = $grupo['categorias']->map(function ($categoria) use ($dreData) {
                $valor = $categoria['valor'];
                $porcentagem = ($dreData['total_caixas'] > 0) ? ($valor / $dreData['total_caixas']) * 100 : 0;

                return [
                    'categoria' => $categoria['categoria'],
                    'total' => number_format($valor, 2, ',', '.'),
                    'porcentagem' => number_format($porcentagem, 2, ',', '.') . '%'
                ];
            });
            return $grupo;
        });

        // --- INÍCIO DA CORREÇÃO PARA O GRAFICO_DATA ---
        $valoresParaGrafico = []; // Array para armazenar os valores monetários reais
        $porcentagensParaGrafico = []; // Array para armazenar as porcentagens

        $totalCaixas = $dreData['total_caixas'];
        $totalLiquido = $dreData['Total_Liquido'];


        // 1. CMV
        $valoresParaGrafico['CMV'] = $dreData['cmv'];
        $porcentagensParaGrafico['CMV'] = ($totalLiquido > 0) ? ($dreData['cmv'] / $totalLiquido) * 100 : 0;

        // 2. Custos Fixos (para o gráfico) - soma diretamente as categorias do grupo
        $grupoCustosFixos = $dreData['dados_grupos']->firstWhere('nome_grupo', 'Custos Fixos');

        $totalCustosFixosGrafico = 0;
        if ($grupoCustosFixos && isset($grupoCustosFixos['categorias'])) {
            // 'categorias' é uma Collection de arrays com a chave 'valor'
            $totalCustosFixosGrafico = $grupoCustosFixos['categorias']->sum('valor');
        }

        $valoresParaGrafico['Custos Fixos'] = $totalCustosFixosGrafico;
        $porcentagensParaGrafico['Custos Fixos'] = ($totalCaixas > 0) ? ($totalCustosFixosGrafico / $totalCaixas) * 100 : 0;

        // 3. Impostos (para o gráfico) - soma o grupo 'Impostos' diretamente
        $grupoImpostos = $dreData['dados_grupos']->firstWhere('nome_grupo', 'Impostos');
        $totalImpostosGrafico = ($grupoImpostos && isset($grupoImpostos['categorias'])) ? $grupoImpostos['categorias']->sum('valor') : 0;

        $valoresParaGrafico['Impostos'] = $totalImpostosGrafico;
        $porcentagensParaGrafico['Impostos'] = ($totalCaixas > 0) ? ($totalImpostosGrafico / $totalCaixas) * 100 : 0;


        // 4. Outras Despesas (total de despesas gerais menos os já categorizados para o gráfico)
        $outrasDespesasGrafico = $dreData['total_despesas_categorias'] - $dreData['cmv'] - $totalCustosFixosGrafico - $totalImpostosGrafico;
        $outrasDespesasGrafico = max(0, $outrasDespesasGrafico); // Garante que não seja negativo
        $valoresParaGrafico['Outras Despesas'] = $outrasDespesasGrafico;
        $porcentagensParaGrafico['Outras Despesas'] = ($totalCaixas > 0) ? ($outrasDespesasGrafico / $totalCaixas) * 100 : 0;

        // 5. Resultado do Período (Lucro ou Prejuízo)
        $resultadoPeriodo = $totalCaixas - $dreData['total_despesas_categorias'];
        $valoresParaGrafico['Resultado do Período'] = $resultadoPeriodo;
        $porcentagensParaGrafico['Resultado do Período'] = ($totalCaixas > 0) ? ($resultadoPeriodo / $totalCaixas) * 100 : 0;

        $graficoData = [
            'labels' => array_keys($valoresParaGrafico), // Usar as chaves (nomes) dos valores
            'data' => array_values($valoresParaGrafico), // Passar os valores monetários reais
            'porcentagens' => array_map(fn($valor) => number_format($valor, 2, ',', '.') . '%', array_values($porcentagensParaGrafico)) // Passar as porcentagens formatadas
        ];
        // --- FIM DA CORREÇÃO PARA O GRAFICO_DATA ---

        // Formatação dos valores para Real brasileiro com separadores
        $totalCaixasFormatado = number_format($dreData['total_caixas'], 2, ',', '.');
        $cmvFormatado = number_format($dreData['cmv'], 2, ',', '.');
        $custosFixosFormatado = number_format($valoresParaGrafico['Custos Fixos'], 2, ',', '.');
        $impostosFormatado = number_format($valoresParaGrafico['Impostos'], 2, ',', '.');
        $outrasDespesasFormatado = number_format($valoresParaGrafico['Outras Despesas'], 2, ',', '.');
        $resultadoPeriodoFormatado = number_format($resultadoPeriodo, 2, ',', '.');

        $porcentagens = $graficoData['porcentagens']; // já formatadas com %

        // Explicação autoexplicativa, separada em parágrafos para facilitar leitura
        $explicacao = <<<EOT
        O Demonstrativo de Resultado do Exercício (DRE) apresenta o desempenho financeiro da sua empresa no período selecionado.

        - O faturamento total foi de R$ {$totalCaixasFormatado}, que representa toda a receita obtida com vendas.
        - O Custo das Mercadorias Vendidas (CMV) foi de R$ {$cmvFormatado} ({$porcentagens[0]}), que é o custo direto dos produtos vendidos.
        - Os Custos Fixos somaram R$ {$custosFixosFormatado} ({$porcentagens[1]}), que são despesas recorrentes como aluguel e salários.
        - Os Impostos pagos totalizaram R$ {$impostosFormatado} ({$porcentagens[2]}).
        - Outras despesas diversas foram de R$ {$outrasDespesasFormatado} ({$porcentagens[3]}), incluindo despesas administrativas e operacionais.

        Após deduzir todas essas despesas, o resultado líquido foi de R$ {$resultadoPeriodoFormatado} ({$porcentagens[4]}), indicando o lucro ou prejuízo no período.

        Esta análise ajuda a entender onde estão concentrados os principais custos e como eles impactam a rentabilidade do seu negócio.
        EOT;


        return response()->json([
            'start_date' => $startDateCarbon->format('Y-m-d H:i:s'),
            'end_date' => $endDateCarbon->format('Y-m-d H:i:s'),
            'total_caixas' => number_format($totalCaixas, 2, ',', '.'),
            'total_despesas_categorias' => number_format($dreData['total_despesas_categorias'], 2, ',', '.'),
            'resultado_do_periodo' => number_format($resultadoPeriodo, 2, ',', '.'),
            'total_salarios' => number_format($dreData['total_salarios'], 2, ',', '.'),
            'grupos' => $dadosGruposFormatados,
            'calendario' => $calendario,
            'grafico_data' => $graficoData,
            'explicacao_dre' => $explicacao,
        ]);
    }

}
