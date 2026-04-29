<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InforUnidade;
use App\Models\SalmaoHistorico;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnalyticsApiController extends Controller
{
    public function cmv(Request $request)
    {
        $unidadeId = $request->query('unidade');

        if ($unidadeId && (int) $unidadeId > 0) {
            try {
                $inicio = Carbon::createFromFormat('Y-m-d', $request->query('inicio'))->startOfDay();
                $final  = Carbon::createFromFormat('Y-m-d', $request->query('final'))->endOfDay();
            } catch (\Exception) {
                return response()->json(['error' => 'Datas inválidas. Use o formato Y-m-d.'], 400);
            }

            $data = $this->processUnitData((int) $unidadeId, $inicio, $final);

            return response()->json([
                'valor_cmv'            => $data['cmv'],
                'porcentagem_cmv'      => $data['porcentagem_cmv'],
                'limiar_maximo'        => $data['limiar_maximo'],
                'alerta'               => $data['alerta'],
                'saidas_estoque'       => $data['saidas_estoque'],
                'aproveitamento_salmao' => $data['aproveitamento_salmao'],
            ]);
        }

        return $this->cmvGlobal($request);
    }

    public function cmvGlobal(Request $request)
    {
        try {
            $inicio = Carbon::createFromFormat('Y-m-d', $request->query('inicio'))->startOfDay();
            $final  = Carbon::createFromFormat('Y-m-d', $request->query('final'))->endOfDay();
        } catch (\Exception) {
            return response()->json(['error' => 'Datas inválidas. Use o formato Y-m-d.'], 400);
        }

        $units    = InforUnidade::all();
        $detalhes = [];
        $globalCmv = 0;
        $somaDasPorcentagens   = 0;
        $totalUnidadesComDados = 0;

        foreach ($units as $unit) {
            $data = $this->processUnitData($unit->id, $inicio, $final);

            if ($data['cmv'] <= 0) {
                continue;
            }

            $detalhes[] = [
                'unidade_id'      => $unit->id,
                'nome_unidade'    => $unit->cidade,
                'valor_cmv'       => $data['cmv'],
                'porcentagem_cmv' => $data['porcentagem_cmv'],
                'limiar_maximo'   => $data['limiar_maximo'],
                'alerta'          => $data['alerta'],
            ];

            $globalCmv             += $data['cmv'];
            $somaDasPorcentagens   += $data['porcentagem_cmv'];
            $totalUnidadesComDados++;
        }

        $porcentagemCmvGlobal = $totalUnidadesComDados > 0
            ? round($somaDasPorcentagens / $totalUnidadesComDados, 2)
            : 0;

        return response()->json([
            'valor_cmv_global'       => round($globalCmv, 2),
            'porcentagem_cmv_global' => $porcentagemCmvGlobal,
            'unidades'               => $detalhes,
        ]);
    }

    public function aproveitamento(Request $request)
    {
        $unidadeId = (int) $request->query('unidade');

        try {
            $inicio = Carbon::createFromFormat('Y-m-d', $request->query('inicio'))->startOfDay();
            $final  = Carbon::createFromFormat('Y-m-d', $request->query('final'))->endOfDay();
        } catch (\Exception) {
            return response()->json(['error' => 'Datas inválidas. Use o formato Y-m-d.'], 400);
        }

        $aproveitamento = round(
            $this->calcularAproveitamentoSalmao($unidadeId, $inicio, $final),
            2
        );

        return response()->json([
            'unidade_id'            => $unidadeId,
            'aproveitamento_salmao' => $aproveitamento,
        ]);
    }

    private function processUnitData(int $unidadeId, Carbon $inicio, Carbon $final): array
    {
        $cmvApi = $this->fetchCmvFromApi($unidadeId, $inicio, $final);

        $aproveitamentoSalmao = round(
            $this->calcularAproveitamentoSalmao($unidadeId, $inicio, $final),
            2
        );

        if ($cmvApi === null) {
            return [
                'faturamento'           => 0,
                'cmv'                   => 0,
                'porcentagem_cmv'       => 0,
                'limiar_maximo'         => null,
                'alerta'                => null,
                'saidas_estoque'        => null,
                'aproveitamento_salmao' => $aproveitamentoSalmao,
            ];
        }

        return [
            'faturamento'           => (float) $cmvApi['faturamento'],
            'cmv'                   => round($this->parseMoedaBr($cmvApi['saidas_estoque']), 2),
            'porcentagem_cmv'       => (float) $cmvApi['porcentagem_cmv'],
            'limiar_maximo'         => (int)   $cmvApi['limiar_maximo'],
            'alerta'                => (bool)  $cmvApi['alerta'],
            'saidas_estoque'        => $cmvApi['saidas_estoque'],
            'aproveitamento_salmao' => $aproveitamentoSalmao,
        ];
    }

    private function fetchCmvFromApi(int $unidadeId, Carbon $inicio, Carbon $final): ?array
    {
        $baseUrl = env('API_CMV_NOVO_URL');

        if (!$baseUrl) {
            Log::warning('API_CMV_NOVO_URL não configurada.');
            return null;
        }

        try {
            $response = Http::timeout(10)->get($baseUrl, [
                'unidade' => $unidadeId,
                'inicio'  => $inicio->format('Y-m-d'),
                'final'   => $final->format('Y-m-d'),
            ]);

            if ($response->status() === 400) {
                return null;
            }

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning("CMV API retornou erro para unidade #{$unidadeId}", [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        } catch (\Throwable $e) {
            Log::error("Falha ao chamar CMV API para unidade #{$unidadeId}", [
                'exception' => $e->getMessage(),
            ]);
        }

        return null;
    }

    private function parseMoedaBr(string $valor): float
    {
        // Remove tudo que não for dígito, ponto ou vírgula (inclui non-breaking space  )
        $clean = preg_replace('/[^\d.,]/', '', $valor);
        $clean = str_replace('.', '', $clean);  // remove separador de milhar
        $clean = str_replace(',', '.', $clean); // vírgula decimal → ponto
        return (float) $clean;
    }

    private function calcularAproveitamentoSalmao(int $unidadeId, Carbon $inicio, Carbon $final): float
    {
        $dados = SalmaoHistorico::where('unidade_id', $unidadeId)
            ->whereBetween('created_at', [$inicio, $final])
            ->selectRaw('SUM(peso_limpo) as total_limpo, SUM(peso_bruto) as total_bruto')
            ->first();

        if (!$dados || $dados->total_bruto <= 0) {
            return 0;
        }

        return ($dados->total_limpo / $dados->total_bruto) * 100;
    }
}
