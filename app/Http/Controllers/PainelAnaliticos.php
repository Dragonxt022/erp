<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\CanalVenda;
use App\Services\AnalyticService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\PainelAnaliticosService;

class PainelAnaliticos extends Controller
{
    /**
     * Serviço próprio para lidar com CMV e DRE
     */
    protected $analyticService, $service;

    public function __construct(AnalyticService $analyticService)
    {
        $this->analyticService = $analyticService;
        $this->service = new PainelAnaliticosService();
    }

    public function analitycsBuscar(Request $request)
    {
        $request->validate([
            'unidade_id' => 'required|integer',
            'data_inicio' => 'required|date',
            'data_fim'    => 'required|date',
            'categoria_id' => 'nullable|integer'
        ]);

        $resposta = $this->service->analitycsBuscar(
            $request->unidade_id,
            $request->categoria_id,
            $request->data_inicio,
            $request->data_fim,
        );

        return response()->json($resposta);
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

        // Usar o serviço para obter todos os dados, incluindo métricas de pedidos
        $data = $this->analyticService->calculatePeriodData($unidade_id, $startDateConverted, $endDateConverted, false, null, null, true);

        return response()->json([
            'start_date' => $startDateConverted->format('d-m-Y'),
            'end_date' => $endDateConverted->format('d-m-Y'),
            'total_caixas' => number_format($data['total_caixas'], 2, ',', '.'),
            'quantidade_pedidos' => $data['quantidade_pedidos'],
            'ticket_medio' => number_format($data['ticket_medio'], 2, ',', '.'),
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

        $data = $this->analyticService->calculatePeriodData($unidadeId, $startDateConverted, $endDateConverted);

        // Retornar os resultados em um único JSON
        return response()->json([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'saldo_estoque_inicial' => number_format($data['estoqueInicialValor'], 2, ',', '.'),
            'entradas_durante_periodo' => number_format($data['comprasValor'], 2, ',', '.'),
            'saldo_estoque_final' => number_format($data['estoqueFinalValor'], 2, ',', '.'),
            'cmv' => number_format($data['cmv'], 2, ',', '.'),
            'total_caixas' => number_format($data['total_caixas'], 2, ',', '.'),
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

        // Usar o serviço para obter todos os dados, incluindo métricas de pedidos
        $data = $this->analyticService->calculatePeriodData($unidadeId, $startDateConverted, $endDateConverted, false, null, null, true);

        // Retornar os resultados em um único JSON
        return response()->json([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'saldo_estoque_inicial' => number_format($data['estoqueInicialValor'], 2, ',', '.'),
            'entradas_durante_periodo' => number_format($data['comprasValor'], 2, ',', '.'),
            'saldo_estoque_final' => number_format($data['estoqueFinalValor'], 2, ',', '.'),
            'cmv' => number_format($data['cmv'], 2, ',', '.'),
            'total_caixas' => number_format($data['total_caixas'], 2, ',', '.'),
            'quantidade_pedidos' => $data['quantidade_pedidos'],
            'ticket_medio' => number_format($data['ticket_medio'], 2, ',', '.'),
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

        // Usar o serviço para obter métricas de pedidos
        $data = $this->analyticService->calculatePeriodData($unidade_id, $startDateConverted, $endDateConverted, false, null, null, true);

        // Retornar as informações em formato JSON
        return response()->json([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'quantidade_pedidos' => $data['quantidade_pedidos'],
            'ticket_medio' => number_format($data['ticket_medio'], 2, ',', '.'),
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
            // Default to previous month if not provided
            $defaultStartDate = Carbon::now()->subMonth()->startOfMonth()->format('d-m-Y');
            $defaultEndDate = Carbon::now()->subMonth()->endOfMonth()->format('d-m-Y');

            $startDate = $request->input('start_date', $defaultStartDate);
            $endDate = $request->input('end_date', $defaultEndDate);

            $startDateCarbon = Carbon::createFromFormat('d-m-Y', $startDate)->startOfDay();
            $endDateCarbon = Carbon::createFromFormat('d-m-Y', $endDate)->endOfDay();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Formato de data inválido. Use o formato DD-MM-YYYY.'], 400);
        }

        // Allow unit selection for franqueadora users
        if ($request->has('unidade_id') && $usuario->franqueadora) {
            $unidadeId = $request->unidade_id;
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

        $calendario = $this->generateCalendarData($startDateCarbon->year, $unidadeId);
        $dadosGruposFormatados = $this->formatGroupData($dreData);
        $graficoData = $this->calculateChartData($dreData);
        $explicacao = $this->generateExplanation($dreData, $graficoData);

        $resultadoPeriodo = $dreData['total_caixas'] - $dreData['total_despesas_categorias'];

        return response()->json([
            'start_date' => $startDateCarbon->format('Y-m-d H:i:s'),
            'end_date' => $endDateCarbon->format('Y-m-d H:i:s'),
            'total_caixas' => number_format($dreData['total_caixas'], 2, ',', '.'),
            'total_despesas_categorias' => number_format($dreData['total_despesas_categorias'], 2, ',', '.'),
            'resultado_do_periodo' => number_format($resultadoPeriodo, 2, ',', '.'),
            'total_salarios' => number_format($dreData['total_salarios'], 2, ',', '.'),
            'grupos' => $dadosGruposFormatados,
            'calendario' => $calendario,
            'grafico_data' => $graficoData,
            'explicacao_dre' => $explicacao,
        ]);
    }

    public function faturamentoAnalitico(Request $request)
    {
        $usuario = Auth::user();
        $unidadeId = $usuario->unidade_id;

        if ($request->has('unidade_id') && ($usuario->franqueadora || $usuario->is_admin)) {
            $unidadeId = $request->unidade_id;
        }

        $meses = [];
        $currentMonth = Carbon::now()->startOfMonth();

        for ($i = 0; $i < 12; $i++) {
            $monthStart = $currentMonth->copy()->subMonths($i)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();

            $faturamento = $this->analyticService->calculateTotalCaixas((int)$unidadeId, $monthStart, $monthEnd);

            $meses[] = [
                'mes' => $monthStart->format('m'),
                'ano' => $monthStart->format('Y'),
                'nome_mes' => $monthStart->translatedFormat('F'),
                'faturamento' => $faturamento,
                'faturamento_formatado' => number_format($faturamento, 2, ',', '.'),
            ];
        }

        // Inverter para ficar em ordem cronológica
        $meses = array_reverse($meses);

        // Calcular diferenças
        foreach ($meses as $index => &$mes) {
            if ($index > 0) {
                $prevFaturamento = $meses[$index - 1]['faturamento'];
                $diferenca = $mes['faturamento'] - $prevFaturamento;
                $mes['diferenca'] = $diferenca;
                $mes['diferenca_formatada'] = number_format($diferenca, 2, ',', '.');
                $mes['percentual'] = $prevFaturamento > 0 ? round(($diferenca / $prevFaturamento) * 100, 2) : 0;
            } else {
                $mes['diferenca'] = 0;
                $mes['diferenca_formatada'] = '0,00';
                $mes['percentual'] = 0;
            }
        }

        return response()->json([
            'dados' => $meses
        ]);
    }

    private function generateCalendarData(int $year, int $unidadeId): array
    {
        $meses = [
            1 => 'Janeiro',
            2 => 'Fevereiro',
            3 => 'Março',
            4 => 'Abril',
            5 => 'Maio',
            6 => 'Junho',
            7 => 'Julho',
            8 => 'Agosto',
            9 => 'Setembro',
            10 => 'Outubro',
            11 => 'Novembro',
            12 => 'Dezembro'
        ];

        return collect(range(1, 12))->map(function ($month) use ($year, $unidadeId, $meses) {
            $startOfMonth = Carbon::create($year, $month, 1)->startOfDay();
            $endOfMonth = Carbon::create($year, $month, $startOfMonth->daysInMonth)->endOfDay();

            $monthData = $this->analyticService->calculatePeriodData($unidadeId, $startOfMonth, $endOfMonth, true, $month, $year);

            $totalCaixasMes = $monthData['total_caixas'];
            $totalDespesasMes = $monthData['total_despesas_categorias'];
            $cmvMes = $monthData['cmv'];
            $resultadoDoPeriodoMes = $totalCaixasMes - $totalDespesasMes;

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
        })->toArray();
    }

    private function formatGroupData(array $dreData): array
    {
        return $dreData['dados_grupos']->map(function ($grupo) use ($dreData) {
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
        })->toArray();
    }

    private function calculateChartData(array $dreData): array
    {
        $valoresParaGrafico = [];
        $porcentagensParaGrafico = [];

        $totalCaixas = $dreData['total_caixas'];
        $totalLiquido = $dreData['Total_Liquido'];

        // 1. CMV
        $valoresParaGrafico['CMV'] = $dreData['cmv'];
        $porcentagensParaGrafico['CMV'] = ($totalLiquido > 0) ? ($dreData['cmv'] / $totalLiquido) * 100 : 0;

        // 2. Custos Fixos
        $grupoCustosFixos = $dreData['dados_grupos']->firstWhere('nome_grupo', 'Custos Fixos');
        $totalCustosFixosGrafico = ($grupoCustosFixos && isset($grupoCustosFixos['categorias'])) ? $grupoCustosFixos['categorias']->sum('valor') : 0;

        $valoresParaGrafico['Custos Fixos'] = $totalCustosFixosGrafico;
        $porcentagensParaGrafico['Custos Fixos'] = ($totalCaixas > 0) ? ($totalCustosFixosGrafico / $totalCaixas) * 100 : 0;

        // 3. Impostos
        $grupoImpostos = $dreData['dados_grupos']->firstWhere('nome_grupo', 'Impostos');
        $totalImpostosGrafico = ($grupoImpostos && isset($grupoImpostos['categorias'])) ? $grupoImpostos['categorias']->sum('valor') : 0;

        $valoresParaGrafico['Impostos'] = $totalImpostosGrafico;
        $porcentagensParaGrafico['Impostos'] = ($totalCaixas > 0) ? ($totalImpostosGrafico / $totalCaixas) * 100 : 0;

        // 4. Outras Despesas
        $outrasDespesasGrafico = $dreData['total_despesas_categorias'] - $dreData['cmv'] - $totalCustosFixosGrafico - $totalImpostosGrafico;
        $outrasDespesasGrafico = max(0, $outrasDespesasGrafico);
        $valoresParaGrafico['Outras Despesas'] = $outrasDespesasGrafico;
        $porcentagensParaGrafico['Outras Despesas'] = ($totalCaixas > 0) ? ($outrasDespesasGrafico / $totalCaixas) * 100 : 0;

        // 5. Resultado do Período
        $resultadoPeriodo = $totalCaixas - $dreData['total_despesas_categorias'];
        $valoresParaGrafico['Resultado do Período'] = $resultadoPeriodo;
        $porcentagensParaGrafico['Resultado do Período'] = ($totalCaixas > 0) ? ($resultadoPeriodo / $totalCaixas) * 100 : 0;

        return [
            'labels' => array_keys($valoresParaGrafico),
            'data' => array_values($valoresParaGrafico),
            'porcentagens' => array_map(fn($valor) => number_format($valor, 2, ',', '.') . '%', array_values($porcentagensParaGrafico))
        ];
    }

    private function generateExplanation(array $dreData, array $graficoData): string
    {
        $totalCaixasFormatado = number_format($dreData['total_caixas'], 2, ',', '.');
        $cmvFormatado = number_format($dreData['cmv'], 2, ',', '.');

        // We need to access the raw values again or store them. 
        // Since $graficoData['data'] has raw values, we can use them by index.
        // Order: CMV, Custos Fixos, Impostos, Outras Despesas, Resultado

        $custosFixosFormatado = number_format($graficoData['data'][1], 2, ',', '.');
        $impostosFormatado = number_format($graficoData['data'][2], 2, ',', '.');
        $outrasDespesasFormatado = number_format($graficoData['data'][3], 2, ',', '.');
        $resultadoPeriodoFormatado = number_format($graficoData['data'][4], 2, ',', '.');

        $porcentagens = $graficoData['porcentagens'];

        return <<<EOT
        O Demonstrativo de Resultado do Exercício (DRE) apresenta o desempenho financeiro da sua empresa no período selecionado.

        - O faturamento total foi de R$ {$totalCaixasFormatado}, que representa toda a receita obtida com vendas.
        - O Custo das Mercadorias Vendidas (CMV) foi de R$ {$cmvFormatado} ({$porcentagens[0]}), que é o custo direto dos produtos vendidos.
        - Os Custos Fixos somaram R$ {$custosFixosFormatado} ({$porcentagens[1]}), que são despesas recorrentes como aluguel e salários.
        - Os Impostos pagos totalizaram R$ {$impostosFormatado} ({$porcentagens[2]}).
        - Outras despesas diversas foram de R$ {$outrasDespesasFormatado} ({$porcentagens[3]}), incluindo despesas administrativas e operacionais.

        Após deduzir todas essas despesas, o resultado líquido foi de R$ {$resultadoPeriodoFormatado} ({$porcentagens[4]}), indicando o lucro ou prejuízo no período.

        Esta análise ajuda a entender onde estão concentrados os principais custos e como eles impactam a rentabilidade do seu negócio.
        EOT;
    }
}
