<?php

namespace App\Http\Controllers;

use App\Models\FechamentoCaixa;
use App\Models\FluxoCaixa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CaixaAnaliticoController extends Controller
{


    public function listarMetodosPagamento(Request $request)
    {
        // Obter as datas de início e fim, caso não sejam enviadas, usa a data atual
        $startDate = $request->input('start_date', now()->format('d-m-Y'));
        $endDate = $request->input('end_date', now()->format('d-m-Y'));

        // Validar o período (padrão: 'total' caso não seja enviado)
        $periodo = $request->input('periodo', 'total');

        // Validar entrada do período
        if (!in_array($periodo, ['almoco', 'janta', 'total'])) {
            return response()->json(['error' => 'Período inválido. Use "almoco", "janta" ou "total".'], 400);
        }

        // Tentar converter as datas recebidas para o formato do banco (Y-m-d)
        try {
            $startDateConverted = \Carbon\Carbon::createFromFormat('d-m-Y', $startDate)->format('Y-m-d');
            $endDateConverted = \Carbon\Carbon::createFromFormat('d-m-Y', $endDate)->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Formato de data inválido. Use o formato DD-MM-YYYY.'], 400);
        }

        // Definir horários com base nas datas de início e fim
        $inicioDoDia = $startDateConverted . ' 00:00:00';
        $fimDoDia = $endDateConverted . ' 23:59:59';
        $meioDia = $startDateConverted . ' 12:00:00';

        // Lógica para definir o intervalo de tempo, caso o período seja "almoco" ou "janta"
        if ($periodo === 'almoco') {
            $horarioInicio = $inicioDoDia;
            $horarioFim = $meioDia;
        } elseif ($periodo === 'janta') {
            $horarioInicio = $meioDia;
            $horarioFim = $fimDoDia;
        } else {
            // Se o período for "total", usa o dia inteiro
            $horarioInicio = $inicioDoDia;
            $horarioFim = $fimDoDia;
        }

        // Consultar métodos de pagamento e somar valores
        $metodosPagamento = FechamentoCaixa::select(
            'metodo_pagamento_id',
            DB::raw('SUM(valor_total_vendas) as total_vendas')
        )
            ->whereBetween('created_at', [$horarioInicio, $horarioFim])
            ->groupBy('metodo_pagamento_id')
            ->with('metodoPagamento') // Relacionamento com DefaultPaymentMethod
            ->get();

        // Calcular o total geral de vendas
        $totalVendas = $metodosPagamento->sum('total_vendas');

        // Formatar os dados e calcular a porcentagem
        $dadosFormatados = $metodosPagamento->map(function ($item) use ($totalVendas) {
            $valorFormatado = number_format($item->total_vendas, 2, ',', '.');

            // Calcular a porcentagem
            $porcentagem = ($totalVendas > 0) ? number_format(($item->total_vendas / $totalVendas) * 100, 2, ',', '.') : 0;

            return [
                'img_icon' => $item->metodoPagamento->img_icon ?? null,
                'nome' => $item->metodoPagamento->nome ?? 'Método não definido',
                'valor' => 'R$ ' . $valorFormatado,
                'valor_raw' => $item->total_vendas,
                'porcentagem' => $porcentagem . '%' // Adiciona a porcentagem
            ];
        });

        // Formatar o total geral
        $totalGeral = number_format($totalVendas, 2, ',', '.');

        // Preparar os dados para o gráfico
        $graficoLabels = $dadosFormatados->pluck('nome');
        $graficoPorcentagem = $dadosFormatados->pluck('porcentagem');
        $graficoData = $dadosFormatados->pluck('valor_raw');

        // Consultar histórico de Fluxo de Caixa e associar o nome do responsável
        $historico = FluxoCaixa::with('responsavel')
            ->whereBetween('created_at', [$horarioInicio, $horarioFim])
            ->orderBy('created_at', 'desc')
            ->get();

        // Formatar os dados do histórico
        $historicoFormatado = $historico->map(function ($item) {
            $valorFormatado = number_format($item->valor, 2, ',', '.');

            return [
                'operacao' => $item->operacao,
                'valor' => 'R$' . $valorFormatado,
                'motivo' => $item->motivo,
                'responsavel' => $item->responsavel->name ?? 'Responsável não definido',
                'hora' => \Carbon\Carbon::parse($item->hora)->format('H:i'),
            ];
        });

        // Retornar resposta com os dados do histórico, métodos de pagamento e gráfico
        return response()->json([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'metodos' => $dadosFormatados,
            'total' => 'R$ ' . $totalGeral,
            'grafico' => [
                'labels' => $graficoLabels,
                'porcentagem' => $graficoPorcentagem,
                'data' => $graficoData,
            ],
            'historico' => $historicoFormatado,
        ]);
    }
}
