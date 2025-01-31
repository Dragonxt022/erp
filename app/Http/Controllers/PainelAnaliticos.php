<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\CanalVenda;
use App\Models\ControleSaldoEstoque;
use App\Models\FechamentoCaixa;
use App\Models\FluxoCaixa;
use App\Models\MovimentacoesEstoque;
use App\Models\UnidadeEstoque;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PainelAnaliticos extends Controller
{
    /**
     * Calcula o CMV (Custo de Mercadoria Vendida) de um período
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function calcularCMV(Request $request)
    {
        // Identifica o usuário autenticado
        $usuario = Auth::user();
        $unidade_id = $usuario->unidade_id;

        // Obter as datas de início e fim, caso não sejam enviadas, usa a data atual
        $startDate = $request->input('start_date', now()->format('d-m-Y'));
        $endDate = $request->input('end_date', now()->format('d-m-Y'));

        // Tentar converter as datas recebidas para o formato do banco (Y-m-d)
        try {
            $startDateConverted = Carbon::createFromFormat('d-m-Y', $startDate)->format('Y-m-d');
            $endDateConverted = Carbon::createFromFormat('d-m-Y', $endDate)->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Formato de data inválido. Use o formato DD-MM-YYYY.'], 400);
        }

        // Função para calcular o valor baseado na unidade (kg ou unidade)
        $calcularValorMovimentacao = function ($quantidade, $preco, $unidade) {
            if ($unidade == 'kg') {
                // Calcular o preço por quilo
                $precoPorQuilo = $preco / $quantidade; // preço total dividido pela quantidade de quilos
                return $quantidade * $precoPorQuilo;  // Multiplica pela quantidade de kg, agora com o preço por quilo
            } else {
                // Para as unidades, multiplicamos a quantidade pela preço unitário
                return $quantidade * $preco;  // Multiplica a quantidade pela preço unitário
            }
        };

        $saldoInicial = ControleSaldoEstoque::where('unidade_id', $unidade_id)
            ->whereDate('data_ajuste', '=', $startDateConverted)
            ->orderBy('data_ajuste', 'desc')
            ->first();

        if (!$saldoInicial) {
            $saldoInicial = ControleSaldoEstoque::where('unidade_id', $unidade_id)
                ->whereDate('data_ajuste', '<', $startDateConverted)
                ->orderBy('data_ajuste', 'desc')
                ->first();

            // Se ainda não encontrar registros, define um valor padrão (exemplo)
            if (!$saldoInicial) {
                $saldoInicial = ControleSaldoEstoque::where('unidade_id', $unidade_id)
                    ->orderBy('data_ajuste', 'asc') // Busca o primeiro ajuste da unidade
                    ->first();

                if (!$saldoInicial) {
                    // Lança uma exceção ou retorna um erro
                    throw new \Exception('Não há saldo inicial disponível para esta unidade.');
                }
            }
        }

        $estoqueInicialValor = $saldoInicial ? $saldoInicial->ajuste_saldo : 0;

        // Compras (entradas) no período (do dia seguinte ao inicial até o dia final)
        $compras = MovimentacoesEstoque::where('unidade_id', $unidade_id)
            ->where('operacao', 'Entrada')  // Garantir que seja "Entrada"
            ->whereBetween('created_at', [Carbon::parse($startDateConverted)->addDay(), $endDateConverted])  // Intervalo entre o dia seguinte ao inicial até o final
            ->get();

        $comprasValor = $compras->sum(function ($item) use ($calcularValorMovimentacao) {
            return $calcularValorMovimentacao($item->quantidade, $item->preco_insumo, $item->unidade);
        });

        $estoqueFinal = UnidadeEstoque::where('unidade_id', $unidade_id)
            ->whereDate('created_at', '<=', $endDateConverted)
            ->get();

        if ($estoqueFinal->isEmpty()) {
            $estoqueFinal = UnidadeEstoque::where('unidade_id', $unidade_id)
                ->whereDate('data_ajuste', '<=', $endDateConverted) // Inclui ajustes até a data final
                ->orderBy('created_at', 'desc')
                ->get();

            // Se ainda não encontrar registros, define um valor padrão
            if ($estoqueFinal->isEmpty()) {
                $estoqueFinal = collect(); // Array vazio
            }
        }

        $estoqueFinalValor = $estoqueFinal->sum(function ($item) use ($calcularValorMovimentacao) {
            return $calcularValorMovimentacao($item->quantidade, $item->preco_insumo, $item->unidade);
        });

        // Tratamento de erros (exemplo)
        try {
            // ... código ...
        } catch (\Exception $e) {
            Log::error($e); // Registra o erro
            return response()->json(['error' => 'Erro ao calcular estoque'], 500);
        }

        // Aplicando a fórmula do CMV (Compras + Estoque Inicial - Estoque Final - Vendas)
        $cmv = $estoqueInicialValor + $comprasValor - $estoqueFinalValor;

        // Retorno da API em formato JSON
        return response()->json([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'saldo_estoque_inicial' => number_format($estoqueInicialValor, 2, ',', '.'),
            'entradas_durante_período' => number_format($comprasValor, 2, ',', '.'),
            'saldo_estoque_final' => number_format($estoqueFinalValor, 2, ',', '.'),
            'cmv' => number_format($cmv, 2, ',', '.') // Formata o valor para melhor leitura
        ]);
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

            $startDateConverted = Carbon::createFromFormat('d-m-Y', $startDate)->format('Y-m-d');
            $endDateConverted = Carbon::createFromFormat('d-m-Y', $endDate)->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Formato de data inválido. Use o formato DD-MM-YYYY.'], 400);
        }

        // Soma apenas os caixas fechados (status = 0) no período selecionado
        $totalCaixas = Caixa::where('unidade_id', $unidade_id)
            ->where('status', 0) // Apenas caixas fechados
            ->whereBetween('created_at', [$startDateConverted, $endDateConverted]) // Filtro por data
            ->sum('valor_final');

        return response()->json([
            'start_date' => $startDateConverted,
            'end_date' => $endDateConverted,
            'total_caixas' => number_format($totalCaixas, 2, ',', '.'),

        ]);
    }


    /**
     * Retorna o faturamento dos últimos 7 dias
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function faturamentoUltimos7Dias()
    {
        $usuario = Auth::user();
        $unidade_id = $usuario->unidade_id;

        // Obtém os últimos 30 dias
        $dias = collect();
        for ($i = 30; $i >= 0; $i--) {
            $dias->push(Carbon::now()->subDays($i)->format('d')); // Apenas o dia numérico
        }

        // Verifica qual banco de dados está sendo usado
        $driver = DB::connection()->getPDO()->getAttribute(\PDO::ATTR_DRIVER_NAME);

        // Define a função correta de extração do dia
        $diaFormat = $driver === 'sqlite' ? "strftime('%d', created_at)" : "DAY(created_at)";

        // Consulta dinâmica baseada no banco de dados
        $faturamento = Caixa::where('unidade_id', $unidade_id)
            ->where('status', 0) // Apenas caixas fechados
            ->where('created_at', '>=', Carbon::now()->subDays(30)->startOfDay()) // Filtra últimos 30 dias
            ->selectRaw("$diaFormat as dia, SUM(valor_final) as total") // Exibe apenas o dia
            ->groupBy('dia')
            ->orderBy('dia', 'asc')
            ->get()
            ->keyBy('dia');

        // Garante que todos os dias apareçam, mesmo se não houver faturamento
        $faturamentoPorDia = $dias->map(function ($dia) use ($faturamento) {
            return [
                'dia' => $dia,
                'total' => $faturamento[$dia]->total ?? 0,
            ];
        });

        return response()->json([
            'status' => 'sucesso',
            'data_resposta' => now()->format('d-m-Y H:i:s'),
            'faturamento' => $faturamentoPorDia
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
        $unidade_id = $usuario->unidade_id;

        // Obter as datas de início e fim
        try {
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('d-m-Y'));
            $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('d-m-Y'));

            $startDateConverted = Carbon::createFromFormat('d-m-Y', $startDate)->format('Y-m-d');
            $endDateConverted = Carbon::createFromFormat('d-m-Y', $endDate)->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Formato de data inválido. Use o formato DD-MM-YYYY.'], 400);
        }

        // Função para calcular o valor baseado na unidade (kg ou unidade)
        $calcularValorMovimentacao = function ($quantidade, $preco, $unidade) {
            if ($unidade == 'kg') {
                // Calcular o preço por quilo
                $precoPorQuilo = $preco / $quantidade;
                return $quantidade * $precoPorQuilo;
            } else {
                // Para as unidades, multiplicamos a quantidade pela preço unitário
                return $quantidade * $preco;
            }
        };

        // 1. Calcular o CMV
        // Saldo inicial de estoque
        $saldoInicial = ControleSaldoEstoque::where('unidade_id', $unidade_id)
            ->whereDate('data_ajuste', '=', $startDateConverted)
            ->orderBy('data_ajuste', 'desc')
            ->first();

        if (!$saldoInicial) {
            $saldoInicial = ControleSaldoEstoque::where('unidade_id', $unidade_id)
                ->whereDate('data_ajuste', '<', $startDateConverted)
                ->orderBy('data_ajuste', 'desc')
                ->first();

            if (!$saldoInicial) {
                $saldoInicial = ControleSaldoEstoque::where('unidade_id', $unidade_id)
                    ->orderBy('data_ajuste', 'asc') // Busca o primeiro ajuste da unidade
                    ->first();

                if (!$saldoInicial) {
                    return response()->json(['error' => 'Não há saldo inicial disponível para esta unidade.'], 400);
                }
            }
        }

        $estoqueInicialValor = $saldoInicial ? $saldoInicial->ajuste_saldo : 0;

        // Compras no período
        $compras = MovimentacoesEstoque::where('unidade_id', $unidade_id)
            ->where('operacao', 'Entrada')
            ->whereBetween('created_at', [Carbon::parse($startDateConverted)->addDay(), $endDateConverted])
            ->get();

        $comprasValor = $compras->sum(function ($item) use ($calcularValorMovimentacao) {
            return $calcularValorMovimentacao($item->quantidade, $item->preco_insumo, $item->unidade);
        });

        // Estoque final
        $estoqueFinal = UnidadeEstoque::where('unidade_id', $unidade_id)
            ->whereDate('created_at', '<=', $endDateConverted)
            ->get();

        $estoqueFinalValor = $estoqueFinal->sum(function ($item) use ($calcularValorMovimentacao) {
            return $calcularValorMovimentacao($item->quantidade, $item->preco_insumo, $item->unidade);
        });

        // Calcular o CMV
        $cmv = $estoqueInicialValor + $comprasValor - $estoqueFinalValor;

        // 2. Somar todos os caixas fechados no período
        $totalCaixas = Caixa::where('unidade_id', $unidade_id)
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

    // Foruma que junta 3 fuções em uma só
    public function calcularIndicadores(Request $request)
    {
        $usuario = Auth::user();
        $unidade_id = $usuario->unidade_id;

        // Obter as datas de início e fim
        try {
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('d-m-Y'));
            $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('d-m-Y'));

            // Converter para Carbon e garantir que as datas incluam todo o período do dia
            $startDateConverted = Carbon::createFromFormat('d-m-Y', $startDate)->startOfDay(); // 00:00:00
            $endDateConverted = Carbon::createFromFormat('d-m-Y', $endDate)->endOfDay(); // 23:59:59
        } catch (\Exception $e) {
            return response()->json(['error' => 'Formato de data inválido. Use o formato DD-MM-YYYY.'], 400);
        }


        // Função para calcular o valor baseado na unidade (kg ou unidade)
        $calcularValorMovimentacao = function ($quantidade, $preco, $unidade) {
            if ($unidade == 'kg') {
                // Calcular o preço por quilo
                $precoPorQuilo = $preco / $quantidade;
                return $quantidade * $precoPorQuilo;
            } else {
                // Para as unidades, multiplicamos a quantidade pela preço unitário
                return $quantidade * $preco;
            }
        };

        // 1. Calcular o CMV
        // Saldo inicial de estoque
        $saldoInicial = ControleSaldoEstoque::where('unidade_id', $unidade_id)
            ->whereDate('data_ajuste', '=', $startDateConverted)
            ->orderBy('data_ajuste', 'desc')
            ->first();

        if (!$saldoInicial) {
            $saldoInicial = ControleSaldoEstoque::where('unidade_id', $unidade_id)
                ->whereDate('data_ajuste', '<', $startDateConverted)
                ->orderBy('data_ajuste', 'desc')
                ->first();

            if (!$saldoInicial) {
                $saldoInicial = ControleSaldoEstoque::where('unidade_id', $unidade_id)
                    ->orderBy('data_ajuste', 'asc') // Busca o primeiro ajuste da unidade
                    ->first();

                if (!$saldoInicial) {
                    return response()->json(['error' => 'Não há saldo inicial disponível para esta unidade.'], 400);
                }
            }
        }

        $estoqueInicialValor = $saldoInicial ? $saldoInicial->ajuste_saldo : 0;

        // Compras no período
        $compras = MovimentacoesEstoque::where('unidade_id', $unidade_id)
            ->where('operacao', 'Entrada')
            ->whereBetween('created_at', [Carbon::parse($startDateConverted)->addDay(), $endDateConverted])
            ->get();

        $comprasValor = $compras->sum(function ($item) use ($calcularValorMovimentacao) {
            return $calcularValorMovimentacao($item->quantidade, $item->preco_insumo, $item->unidade);
        });

        // Estoque final
        $estoqueFinal = UnidadeEstoque::where('unidade_id', $unidade_id)
            ->whereDate('created_at', '<=', $endDateConverted)
            ->get();

        $estoqueFinalValor = $estoqueFinal->sum(function ($item) use ($calcularValorMovimentacao) {
            return $calcularValorMovimentacao($item->quantidade, $item->preco_insumo, $item->unidade);
        });

        // Calcular o CMV
        $cmv = $estoqueInicialValor + $comprasValor - $estoqueFinalValor;


        $startDateConverted = Carbon::parse($startDate)->startOfDay(); // Começa em 00:00:00
        $endDateConverted = Carbon::parse($endDate)->endOfDay(); // Termina em 23:59:59

        // 2. Somar todos os caixas fechados no período
        $totalCaixas = Caixa::where('unidade_id', $unidade_id)
            ->where('status', 0) // Apenas caixas fechados
            ->whereBetween('created_at', [$startDateConverted, $endDateConverted])
            ->sum('valor_final');



        // 3. Quantidade de pedidos e faturamento
        $pedidos = CanalVenda::where('unidade_id', $unidade_id)
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

            $startDateConverted = Carbon::createFromFormat('d-m-Y', $startDate)->format('Y-m-d');
            $endDateConverted = Carbon::createFromFormat('d-m-Y', $endDate)->format('Y-m-d');
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
}
