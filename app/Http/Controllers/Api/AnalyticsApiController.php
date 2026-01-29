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
        try {
            $inicio = Carbon::createFromFormat('Y-m-d', $request->query('inicio'))->startOfDay();
            $final = Carbon::createFromFormat('Y-m-d', $request->query('final'))->endOfDay();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Datas inválidas. Use o formato Y-m-d.'], 400);
        }

        $units = \App\Models\InforUnidade::all();
        $detalhes = [];
        $globalCmv = 0;

        // Novas variáveis para a média da calculadora
        $somaDasPorcentagens = 0;
        $totalUnidadesComDados = 0;

        foreach ($units as $unit) {
            $data = $this->processUnitData($service, $unit->id, $inicio, $final);

            if ($data['cmv'] <= 0) {
                continue;
            }

            $detalhes[] = [
                'unidade_id' => $unit->id,
                'nome_unidade' => $unit->cidade,
                'valor_cmv' => $data['cmv'],
                'porcentagem_cmv' => $data['porcentagem_cmv'],
            ];

            $globalCmv += $data['cmv'];

            // Acumula as porcentagens para tirar a média simples depois
            $somaDasPorcentagens += $data['porcentagem_cmv'];
            $totalUnidadesComDados++;
        }

        // Lógica da Calculadora: Média das porcentagens individuais
        $porcentagemCmvGlobal = $totalUnidadesComDados > 0
            ? round($somaDasPorcentagens / $totalUnidadesComDados, 2)
            : 0;

        return response()->json([
            'valor_cmv_global' => round($globalCmv, 2),
            'porcentagem_cmv_global' => $porcentagemCmvGlobal, // Agora vai bater com a calculadora
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


    private function processUnitData(AnalyticService $service, int $unidadeId, Carbon $inicio, Carbon $final): array
    {
        $dreData = $service->calculatePeriodData(
            $unidadeId,
            $inicio,
            $final,
            false,
            null,
            null,
            false
        );

        $valorCmv = (float) ($dreData['cmv'] ?? 0);

        // USAR O MÉTODO CENTRALIZADO DO SERVIÇO PARA OBTER A PORCENTAGEM DO CMV
        $porcentagemCmv = $service->extractCmvPercentage($dreData);

        $aproveitamentoSalmao = round(
            $this->calcularAproveitamentoSalmao($unidadeId, $inicio, $final),
            2
        );

        return [
            'faturamento' => (float) $dreData['total_caixas'],
            'cmv' => round($valorCmv, 2),
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
