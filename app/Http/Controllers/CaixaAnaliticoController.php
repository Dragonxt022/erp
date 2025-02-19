<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\FechamentoCaixa;
use App\Models\FluxoCaixa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CaixaAnaliticoController extends Controller
{

    public function listarMetodosPagamento(Request $request)
    {
        try {
            // Obter as datas de início e fim, se não forem enviadas, usa a data atual
            $startDate = $request->input('start_date', now()->format('d-m-Y'));
            $endDate = $request->input('end_date', now()->format('d-m-Y'));

            // Validar período (padrão: 'total' caso não seja enviado)
            $periodo = $request->input('periodo', 'total');

            if (!in_array($periodo, ['almoco', 'janta', 'total'])) {
                return response()->json(['error' => 'Período inválido. Use "almoco", "janta" ou "total".'], 400);
            }

            // Converter as datas para Carbon e aplicar início e fim do dia
            $startDateConverted = Carbon::createFromFormat('d-m-Y', $startDate)->startOfDay();
            $endDateConverted = Carbon::createFromFormat('d-m-Y', $endDate)->endOfDay();

            // Definir horários específicos para "almoço" e "janta"
            if ($periodo === 'almoco') {
                $horarioInicio = $startDateConverted->copy()->setHour(5)->setMinute(0)->setSecond(0);
                $horarioFim = $startDateConverted->copy()->setHour(15)->setMinute(30)->setSecond(59);
            } elseif ($periodo === 'janta') {
                $horarioInicio = $startDateConverted->copy()->setHour(15)->setMinute(30)->setSecond(0);
                $horarioFim = $endDateConverted;
            } else {
                // Período total: inclui todas as horas do dia
                $horarioInicio = $startDateConverted;
                $horarioFim = $endDateConverted;
            }

            // Consultar todos os caixas dentro do intervalo de tempo
            $caixas = Caixa::where('unidade_id', Auth::user()->unidade_id)
                ->whereBetween('created_at', [$horarioInicio, $horarioFim])
                ->orderBy('created_at', 'asc')
                ->get();

            if ($caixas->isEmpty()) {
                return response()->json(['error' => 'Nenhum caixa encontrado para o período informado.'], 404);
            }

            // Consultar métodos de pagamento e somar valores com base nos caixas encontrados
            $metodosPagamento = FechamentoCaixa::select(
                'metodo_pagamento_id',
                DB::raw('SUM(valor_total_vendas) as total_vendas')
            )
                ->where('unidade_id', Auth::user()->unidade_id)
                ->whereIn('caixa_id', $caixas->pluck('id')) // Considera todos os caixas encontrados
                ->groupBy('metodo_pagamento_id')
                ->with('metodoPagamento')
                ->get();

            // Calcular o total geral de vendas
            $totalVendas = $metodosPagamento->sum('total_vendas');

            // Formatar os dados e calcular a porcentagem
            $dadosFormatados = $metodosPagamento->map(function ($item) use ($totalVendas) {
                return [
                    'img_icon' => $item->metodoPagamento->img_icon ?? null,
                    'nome' => $item->metodoPagamento->nome ?? 'Método não definido',
                    'valor' => 'R$ ' . number_format($item->total_vendas, 2, ',', '.'),
                    'valor_raw' => $item->total_vendas,
                    'porcentagem' => ($totalVendas > 0) ? number_format(($item->total_vendas / $totalVendas) * 100, 2, ',', '.') . '%' : '0%',
                ];
            });

            // Preparar os dados para o gráfico
            $graficoLabels = $dadosFormatados->pluck('nome');
            $graficoPorcentagem = $dadosFormatados->pluck('porcentagem');
            $graficoData = $dadosFormatados->pluck('valor_raw');

            // Consultar histórico de Fluxo de Caixa e associar o nome do responsável
            $historico = FluxoCaixa::with('responsavel')
                ->where('unidade_id', Auth::user()->unidade_id)
                ->whereBetween('created_at', [$horarioInicio, $horarioFim])
                ->orderBy('created_at', 'desc')
                ->get();

            // Formatar os dados do histórico
            $historicoFormatado = $historico->map(function ($item) {
                return [
                    'operacao' => $item->operacao,
                    'valor' => 'R$' . number_format($item->valor, 2, ',', '.'),
                    'motivo' => $item->motivo,
                    'responsavel' => $item->responsavel->name ?? 'Responsável não definido',
                    'hora' => Carbon::parse($item->hora)->format('H:i'),
                ];
            });

            // Retornar resposta com os dados do histórico, métodos de pagamento e gráfico
            return response()->json([
                'start_date' => $startDate,
                'end_date' => $endDate,
                'metodos' => $dadosFormatados,
                'total' => 'R$ ' . number_format($totalVendas, 2, ',', '.'),
                'grafico' => [
                    'labels' => $graficoLabels,
                    'porcentagem' => $graficoPorcentagem,
                    'data' => $graficoData,
                ],
                'historico' => $historicoFormatado,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro interno ao processar os dados.'], 500);
        }
    }
}
