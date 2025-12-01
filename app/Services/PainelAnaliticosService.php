<?php

namespace App\Services;

use App\Models\ContaAPagar;
use Carbon\Carbon;

class PainelAnaliticosService
{
    public function analitycsBuscar($unidadeId, $categoriaId, $dataInicio, $dataFim)
    {
        $startDate = Carbon::parse($dataInicio)->startOfDay();
        $endDate   = Carbon::parse($dataFim)->endOfDay();

        $query = ContaAPagar::with('categoria')
            ->where('unidade_id', $unidadeId)
            ->whereIn('status', ['agendada', 'pendente', 'pago', 'atrasado'])
            ->whereBetween('emitida_em', [$startDate, $endDate]);

        if ($categoriaId) {
            $query->where('categoria_id', $categoriaId);
        }

        $dados = $query->get()->map(function ($item) {
            $registro = $item->toArray();

            unset($registro['created_at'], $registro['updated_at'], $registro['categoria']);

            $registro['valor_formatado'] = 'R$ ' . number_format($item->valor, 2, ',', '.');
            $registro['categoria'] = $item->categoria->nome ?? null;

            return $registro;
        });

        if ($dados->isEmpty()) {
            return [
                'mensagem' => 'Nenhum registro encontrado para os filtros informados.'
            ];
        }

        $total = $dados->sum('valor');

        return [
            'total'       => 'R$ ' . number_format($total, 2, ',', '.'),
            'quantidade'  => $dados->count(),
            'itens'       => $dados
        ];
    }
}
