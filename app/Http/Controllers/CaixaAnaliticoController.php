<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\FechamentoCaixa;
use App\Models\FluxoCaixa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CaixaAnaliticoController extends Controller
{
    private function formatCurrency($value)
    {
        return 'R$ ' . number_format($value ?? 0, 2, ',', '.');
    }

    public function listarMetodosPagamento(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'start_date' => 'nullable|date_format:d-m-Y',
                'end_date' => 'nullable|date_format:d-m-Y',
                'periodo' => 'nullable|in:almoco,janta,total',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            // Se não houver datas enviadas, usar o dia atual (24 horas)
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $periodo = $request->input('periodo', 'total'); // Default para 'total'

            if (!$startDate && !$endDate) {
                $startDateConverted = now()->startOfDay();
                $endDateConverted = now()->endOfDay();
            } else {
                $startDate = $startDate ?? now()->format('d-m-Y');
                $endDate = $endDate ?? now()->format('d-m-Y');
                $startDateConverted = Carbon::createFromFormat('d-m-Y', $startDate)->startOfDay();
                $endDateConverted = Carbon::createFromFormat('d-m-Y', $endDate)->endOfDay();
            }

            // Ajustar horários conforme o período
            if ($periodo === 'almoco') {
                $horarioInicio = $startDateConverted->copy()->setHour(5)->setMinute(0)->setSecond(0);
                $horarioFim = $startDateConverted->copy()->setHour(15)->setMinute(30)->setSecond(59);
            } elseif ($periodo === 'janta') {
                $horarioInicio = $startDateConverted->copy()->setHour(15)->setMinute(30)->setSecond(0);
                $horarioFim = $endDateConverted;
            } else {
                $horarioInicio = $startDateConverted;
                $horarioFim = $endDateConverted;
            }

            // Consultar métodos de pagamento do período atual
            $metodosPagamento = FechamentoCaixa::select(
                'metodo_pagamento_id',
                DB::raw('SUM(valor_total_vendas) as total_vendas')
            )
                ->join('caixas', 'fechamento_caixas.caixa_id', '=', 'caixas.id')
                ->where('fechamento_caixas.unidade_id', Auth::user()->unidade_id)
                ->whereBetween('caixas.created_at', [$horarioInicio, $horarioFim])
                ->groupBy('metodo_pagamento_id')
                ->with('metodoPagamento')
                ->get();

            // Se não houver dados no período atual e for o dia atual, tentar o dia anterior
            $usouDiaAnterior = false;
            if ($metodosPagamento->isEmpty() && $startDateConverted->isToday()) {
                $diaAnteriorInicio = $startDateConverted->copy()->subDay()->startOfDay();
                $diaAnteriorFim = $endDateConverted->copy()->subDay()->endOfDay();

                $metodosPagamento = FechamentoCaixa::select(
                    'metodo_pagamento_id',
                    DB::raw('SUM(valor_total_vendas) as total_vendas')
                )
                    ->join('caixas', 'fechamento_caixas.caixa_id', '=', 'caixas.id')
                    ->where('fechamento_caixas.unidade_id', Auth::user()->unidade_id)
                    ->whereBetween('caixas.created_at', [$diaAnteriorInicio, $diaAnteriorFim])
                    ->groupBy('metodo_pagamento_id')
                    ->with('metodoPagamento')
                    ->get();

                if ($metodosPagamento->isNotEmpty()) {
                    $startDate = $diaAnteriorInicio->format('d-m-Y');
                    $endDate = $diaAnteriorFim->format('d-m-Y');
                    $horarioInicio = $diaAnteriorInicio;
                    $horarioFim = $diaAnteriorFim;
                    $usouDiaAnterior = true;
                }
            }

            if ($metodosPagamento->isEmpty()) {
                return response()->json(['error' => 'Nenhum dado encontrado para o período informado.'], 404);
            }

            $totalVendas = $metodosPagamento->sum('total_vendas');

            $dadosFormatados = $metodosPagamento->map(function ($item) use ($totalVendas) {
                $metodo = $item->metodoPagamento ?? new \stdClass();
                return [
                    'img_icon' => $metodo->img_icon ?? null,
                    'nome' => $metodo->nome ?? 'Método não definido',
                    'valor' => $this->formatCurrency($item->total_vendas),
                    'valor_raw' => $item->total_vendas ?? 0,
                    'porcentagem' => ($totalVendas > 0) ? number_format(($item->total_vendas / $totalVendas) * 100, 2, ',', '.') . '%' : '0%',
                ];
            });

            $graficoLabels = $dadosFormatados->pluck('nome');
            $graficoPorcentagem = $dadosFormatados->pluck('porcentagem');
            $graficoData = $dadosFormatados->pluck('valor_raw');

            $historico = FluxoCaixa::with('responsavel')
                ->where('unidade_id', Auth::user()->unidade_id)
                ->whereBetween('created_at', [$horarioInicio, $horarioFim])
                ->orderBy('created_at', 'desc')
                ->get();

            $historicoFormatado = $historico->map(function ($item) {
                return [
                    'operacao' => $item->operacao,
                    'valor' => $this->formatCurrency($item->valor),
                    'motivo' => $item->motivo,
                    'responsavel' => $item->responsavel->name ?? 'Responsável não definido',
                    'hora' => Carbon::parse($item->hora)->format('d/m H:i'),
                ];
            });

            return response()->json([
                'start_date' => $startDate,
                'end_date' => $endDate,
                'metodos' => $dadosFormatados,
                'total' => $this->formatCurrency($totalVendas),
                'grafico' => [
                    'labels' => $graficoLabels,
                    'porcentagem' => $graficoPorcentagem,
                    'data' => $graficoData,
                ],
                'historico' => $historicoFormatado,
                'usou_dia_anterior' => $usouDiaAnterior, // Opcional: indica se usou dados do dia anterior
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro interno ao processar os dados: ' . $e->getMessage()], 500);
        }
    }
}