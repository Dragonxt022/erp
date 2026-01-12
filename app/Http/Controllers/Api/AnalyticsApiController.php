<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SalmaoHistorico;
use App\Services\AnalyticService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AnalyticsApiController extends Controller
{
    public function cmv(Request $request, AnalyticService $service)
    {
        $unidadeId = $request->query('unidade');

        if ($unidadeId && (int) $unidadeId > 0) {
            // Datas em formato ISO obrigatório
            try {
                $inicio = Carbon::createFromFormat('Y-m-d', $request->query('inicio'))->startOfDay();
                $final = Carbon::createFromFormat('Y-m-d', $request->query('final'))->endOfDay();
            } catch (\Exception $e) {
                return response()->json(['error' => 'Datas inválidas. Use o formato Y-m-d.'], 400);
            }

            $data = $this->processUnitData($service, (int) $unidadeId, $inicio, $final);
            return response()->json([
                'valor_cmv' => $data['cmv'],
                'porcentagem_cmv' => $data['porcentagem_cmv'],
                'aproveitamento_salmao' => $data['aproveitamento_salmao'],
            ]);
        }

        // Se não passou unidade, redireciona para a lógica global
        return $this->cmvGlobal($request, $service);
    }

    public function cmvGlobal(Request $request, AnalyticService $service)
    {
        // Datas em formato ISO obrigatório
        try {
            $inicio = Carbon::createFromFormat('Y-m-d', $request->query('inicio'))->startOfDay();
            $final = Carbon::createFromFormat('Y-m-d', $request->query('final'))->endOfDay();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Datas inválidas. Use o formato Y-m-d.'], 400);
        }

        // Caso global: todas as unidades
        $units = \App\Models\InforUnidade::all();
        $detalhes = [];
        $globalCmv = 0;
        $globalFaturamento = 0;

        foreach ($units as $unit) {
            $data = $this->processUnitData($service, $unit->id, $inicio, $final);

            $detalhes[] = [
                'unidade_id' => $unit->id,
                'nome_unidade' => $unit->cidade,
                'valor_cmv' => $data['cmv'],
                'porcentagem_cmv' => $data['porcentagem_cmv'],
            ];

            $globalCmv += $data['cmv'];
            $globalFaturamento += $data['faturamento'];
        }

        $porcentagemCmvGlobal = $globalFaturamento > 0
            ? round(($globalCmv / $globalFaturamento) * 100, 2)
            : 0;

        return response()->json([
            'valor_cmv_global' => round($globalCmv, 2),
            'porcentagem_cmv_global' => $porcentagemCmvGlobal,
            'unidades' => $detalhes,
        ]);
    }

    public function aproveitamento(Request $request)
    {
        $unidadeId = (int) $request->query('unidade');

        try {
            $inicio = Carbon::createFromFormat('Y-m-d', $request->query('inicio'))->startOfDay();
            $final = Carbon::createFromFormat('Y-m-d', $request->query('final'))->endOfDay();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Datas inválidas. Use o formato Y-m-d.'], 400);
        }

        $aproveitamento = round(
            $this->calcularAproveitamentoSalmao($unidadeId, $inicio, $final),
            2
        );

        return response()->json([
            'unidade_id' => $unidadeId,
            'aproveitamento_salmao' => $aproveitamento,
        ]);
    }

    /**
     * Processa os dados de uma unidade específica
     */
    private function processUnitData(AnalyticService $service, int $unidadeId, Carbon $inicio, Carbon $final): array
    {
        // Dados financeiros
        $data = $service->calculatePeriodData(
            $unidadeId,
            $inicio,
            $final,
            false,
            null,
            null,
            false
        );

        $faturamento = (float) $data['total_caixas'];
        $cmv = round((float) $data['cmv'], 2);

        $porcentagemCmv = $faturamento > 0
            ? round(($cmv / $faturamento) * 100, 2)
            : 0;

        // Reaproveitamento real do salmão
        $aproveitamentoSalmao = round(
            $this->calcularAproveitamentoSalmao(
                $unidadeId,
                $inicio,
                $final
            ),
            2
        );

        return [
            'faturamento' => $faturamento,
            'cmv' => $cmv,
            'porcentagem_cmv' => $porcentagemCmv,
            'aproveitamento_salmao' => $aproveitamentoSalmao,
        ];
    }

    /**
     * Aproveitamento real do salmão (operacional)
     */
    private function calcularAproveitamentoSalmao(
        int $unidadeId,
        Carbon $inicio,
        Carbon $final
    ): float {
        $dados = SalmaoHistorico::where('unidade_id', $unidadeId)
            ->whereBetween('created_at', [$inicio, $final])
            ->selectRaw('
                SUM(peso_limpo) as total_limpo,
                SUM(peso_bruto) as total_bruto
            ')
            ->first();

        if (!$dados || $dados->total_bruto <= 0) {
            return 0;
        }

        return ($dados->total_limpo / $dados->total_bruto) * 100;
    }
}
