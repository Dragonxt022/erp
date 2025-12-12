<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\ContaAPagar;
use App\Models\InforUnidade;
use App\Services\AnalyticService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminPainelController extends Controller
{
    protected $analyticService;

    public function __construct(AnalyticService $analyticService)
    {
        $this->analyticService = $analyticService;
    }

    /**
     * Retorna todas as unidades disponíveis para a franqueadora
     */
    public function getUnidades()
    {
        try {
            $unidades = InforUnidade::select('id', 'cidade', 'cnpj')
                ->orderBy('cidade', 'asc')
                ->get();

            return response()->json([
                'unidades' => $unidades,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar unidades: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar unidades.'], 500);
        }
    }

    /**
     * Retorna todos os indicadores (faturamento, CMV, ticket médio, pedidos)
     * para a(s) unidade(s) selecionada(s) no período especificado
     */
    public function getIndicadores(Request $request)
    {
        try {
            // Obter e validar as datas
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('d-m-Y'));
            $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('d-m-Y'));

            $startDateConverted = Carbon::createFromFormat('d-m-Y', $startDate)->startOfDay();
            $endDateConverted = Carbon::createFromFormat('d-m-Y', $endDate)->endOfDay();

            if ($startDateConverted->greaterThan($endDateConverted)) {
                return response()->json(['error' => 'A data de início não pode ser posterior à data de fim.'], 400);
            }

            $unidadeId = $request->input('unidade_id'); // null = todas as unidades

            // Se unidade_id for null, calcular dados agregados de todas as unidades
            if ($unidadeId === null || $unidadeId === 'null') {
                return $this->getIndicadoresAgregados($startDateConverted, $endDateConverted, $startDate, $endDate);
            }

            // Calcular dados para uma unidade específica
            $data = $this->analyticService->calculatePeriodData(
                $unidadeId,
                $startDateConverted,
                $endDateConverted,
                false,
                null,
                null,
                true
            );

            // Calcular período anterior para comparação
            $diffDays = $startDateConverted->diffInDays($endDateConverted);
            $previousStartDate = $startDateConverted->copy()->subDays($diffDays + 1);
            $previousEndDate = $startDateConverted->copy()->subDay();

            $previousData = $this->analyticService->calculatePeriodData(
                $unidadeId,
                $previousStartDate,
                $previousEndDate,
                false,
                null,
                null,
                true
            );

            // Calcular variações percentuais
            $comparisons = $this->calculateComparisons($data, $previousData);

            return response()->json([
                'start_date' => $startDate,
                'end_date' => $endDate,
                'faturamento' => number_format($data['total_caixas'], 2, ',', '.'),
                'faturamento_raw' => $data['total_caixas'],
                'cmv' => number_format($data['cmv'], 2, ',', '.'),
                'cmv_raw' => $data['cmv'],
                'ticket_medio' => number_format($data['ticket_medio'], 2, ',', '.'),
                'ticket_medio_raw' => $data['ticket_medio'],
                'quantidade_pedidos' => $data['quantidade_pedidos'],
                'comparisons' => $comparisons,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao calcular indicadores: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao calcular indicadores.'], 500);
        }
    }

    /**
     * Calcula indicadores agregados de todas as unidades
     */
    private function getIndicadoresAgregados($startDateConverted, $endDateConverted, $startDate, $endDate)
    {
        try {
            $unidades = InforUnidade::pluck('id');

            $totalFaturamento = 0;
            $totalCMV = 0;
            $totalPedidos = 0;
            $totalTicketMedio = 0;

            $previousTotalFaturamento = 0;
            $previousTotalCMV = 0;
            $previousTotalPedidos = 0;
            $previousTotalTicketMedio = 0;

            // Calcular período anterior
            $diffDays = $startDateConverted->diffInDays($endDateConverted);
            $previousStartDate = $startDateConverted->copy()->subDays($diffDays + 1);
            $previousEndDate = $startDateConverted->copy()->subDay();

            foreach ($unidades as $unidadeId) {
                // Período atual
                $data = $this->analyticService->calculatePeriodData(
                    $unidadeId,
                    $startDateConverted,
                    $endDateConverted,
                    false,
                    null,
                    null,
                    true
                );

                $totalFaturamento += $data['total_caixas'];
                $totalCMV += $data['cmv'];
                $totalPedidos += $data['quantidade_pedidos'];

                // Período anterior
                $previousData = $this->analyticService->calculatePeriodData(
                    $unidadeId,
                    $previousStartDate,
                    $previousEndDate,
                    false,
                    null,
                    null,
                    true
                );

                $previousTotalFaturamento += $previousData['total_caixas'];
                $previousTotalCMV += $previousData['cmv'];
                $previousTotalPedidos += $previousData['quantidade_pedidos'];
            }

            // Calcular ticket médio agregado
            $totalTicketMedio = $totalPedidos > 0 ? $totalFaturamento / $totalPedidos : 0;
            $previousTotalTicketMedio = $previousTotalPedidos > 0 ? $previousTotalFaturamento / $previousTotalPedidos : 0;

            // Calcular comparações
            $comparisons = [
                'faturamento' => $this->calculatePercentageChange($totalFaturamento, $previousTotalFaturamento),
                'cmv' => $this->calculatePercentageChange($totalCMV, $previousTotalCMV),
                'ticket_medio' => $this->calculatePercentageChange($totalTicketMedio, $previousTotalTicketMedio),
                'pedidos' => $this->calculatePercentageChange($totalPedidos, $previousTotalPedidos),
            ];

            return response()->json([
                'start_date' => $startDate,
                'end_date' => $endDate,
                'faturamento' => number_format($totalFaturamento, 2, ',', '.'),
                'faturamento_raw' => $totalFaturamento,
                'cmv' => number_format($totalCMV, 2, ',', '.'),
                'cmv_raw' => $totalCMV,
                'ticket_medio' => number_format($totalTicketMedio, 2, ',', '.'),
                'ticket_medio_raw' => $totalTicketMedio,
                'quantidade_pedidos' => $totalPedidos,
                'comparisons' => $comparisons,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao calcular indicadores agregados: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao calcular indicadores agregados.'], 500);
        }
    }

    /**
     * Calcula as comparações percentuais entre períodos
     */
    private function calculateComparisons($currentData, $previousData)
    {
        return [
            'faturamento' => $this->calculatePercentageChange($currentData['total_caixas'], $previousData['total_caixas']),
            'cmv' => $this->calculatePercentageChange($currentData['cmv'], $previousData['cmv']),
            'ticket_medio' => $this->calculatePercentageChange($currentData['ticket_medio'], $previousData['ticket_medio']),
            'pedidos' => $this->calculatePercentageChange($currentData['quantidade_pedidos'], $previousData['quantidade_pedidos']),
        ];
    }

    /**
     * Calcula a variação percentual entre dois valores
     */
    private function calculatePercentageChange($current, $previous)
    {
        if ($previous == 0) {
            return [
                'percentage' => 0,
                'direction' => 'neutral',
                'formatted' => '0%',
            ];
        }

        $change = (($current - $previous) / $previous) * 100;
        $direction = $change > 0 ? 'up' : ($change < 0 ? 'down' : 'neutral');

        return [
            'percentage' => round(abs($change), 1),
            'direction' => $direction,
            'formatted' => number_format(abs($change), 1, ',', '.') . '%',
        ];
    }

    /**
     * Retorna o faturamento diário para o gráfico
     */
    public function getFaturamentoDiario(Request $request)
    {
        try {
            $unidadeId = $request->input('unidade_id');

            // Se unidade_id for null, agregar dados de todas as unidades
            if ($unidadeId === null || $unidadeId === 'null') {
                return $this->getFaturamentoDiarioAgregado();
            }

            // Geração da lista de dias numéricos do último mês
            $dias = collect(range(0, 30))->map(function ($i) {
                return Carbon::now()->subDays($i)->format('d');
            })->reverse();

            // Detecta o banco de dados e ajusta o formato do dia
            $driver = DB::connection()->getPDO()->getAttribute(\PDO::ATTR_DRIVER_NAME);
            $diaFormat = $driver === 'mysql' ? "DAY(created_at)" : "strftime('%d', created_at)";

            // Consulta o faturamento diário do caixa fechado
            $faturamento = Caixa::where('unidade_id', $unidadeId)
                ->where('status', 0)
                ->whereBetween('created_at', [Carbon::now()->subDays(30)->startOfDay(), Carbon::now()->endOfDay()])
                ->selectRaw("$diaFormat as dia, SUM(valor_final) as total")
                ->groupBy('dia')
                ->orderBy('dia', 'asc')
                ->get()
                ->mapWithKeys(fn($item) => [(int) $item->dia => $item->total]);

            // Garante que todos os dias apareçam, mesmo se não houver faturamento
            $faturamentoPorDia = $dias->map(fn($dia) => [
                'dia' => $dia,
                'total' => $faturamento[(int) $dia] ?? 0,
            ]);

            return response()->json([
                'status' => 'sucesso',
                'faturamento' => $faturamentoPorDia,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar faturamento diário: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar faturamento diário.'], 500);
        }
    }

    /**
     * Retorna o faturamento diário agregado de todas as unidades
     */
    private function getFaturamentoDiarioAgregado()
    {
        try {
            $dias = collect(range(0, 30))->map(function ($i) {
                return Carbon::now()->subDays($i)->format('d');
            })->reverse();

            $driver = DB::connection()->getPDO()->getAttribute(\PDO::ATTR_DRIVER_NAME);
            $diaFormat = $driver === 'mysql' ? "DAY(created_at)" : "strftime('%d', created_at)";

            // Consulta agregada de todas as unidades
            $faturamento = Caixa::where('status', 0)
                ->whereBetween('created_at', [Carbon::now()->subDays(30)->startOfDay(), Carbon::now()->endOfDay()])
                ->selectRaw("$diaFormat as dia, SUM(valor_final) as total")
                ->groupBy('dia')
                ->orderBy('dia', 'asc')
                ->get()
                ->mapWithKeys(fn($item) => [(int) $item->dia => $item->total]);

            $faturamentoPorDia = $dias->map(fn($dia) => [
                'dia' => $dia,
                'total' => $faturamento[(int) $dia] ?? 0,
            ]);

            return response()->json([
                'status' => 'sucesso',
                'faturamento' => $faturamentoPorDia,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar faturamento diário agregado: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar faturamento diário agregado.'], 500);
        }
    }

    /**
     * Retorna os compromissos (contas a pagar) próximos
     */
    public function getCompromissos(Request $request)
    {
        try {
            $unidadeId = $request->input('unidade_id');

            $query = ContaAPagar::with('categoria')
                ->where('status', '!=', 'pago')
                ->orderBy('vencimento', 'asc')
                ->limit(10);

            // Se unidade_id não for null, filtrar por unidade
            if ($unidadeId !== null && $unidadeId !== 'null') {
                $query->where('unidade_id', $unidadeId);
            }

            $contas = $query->get()->map(function ($conta) {
                return [
                    'id' => $conta->id,
                    'nome' => $conta->nome,
                    'valor' => $conta->valor,
                    'valor_formatado' => 'R$ ' . number_format($conta->valor, 2, ',', '.'),
                    'vencimento' => $conta->vencimento,
                    'status' => $conta->status,
                    'categoria' => $conta->categoria->nome ?? 'Sem categoria',
                ];
            });

            return response()->json([
                'compromissos' => $contas,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar compromissos: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar compromissos.'], 500);
        }
    }
}
