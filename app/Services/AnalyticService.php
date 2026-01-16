<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Arr;
use App\Models\Caixa;
use App\Models\CanalVenda;
use App\Models\Categoria;
use App\Models\ContaAPagar;
use App\Models\ControleSaldoEstoque;
use App\Models\FechamentoCaixa;
use App\Models\FluxoCaixa;
use App\Models\GrupoDeCategorias;
use App\Models\MovimentacoesEstoque;
use App\Models\UnidadeEstoque;
use App\Models\UnidadePaymentMethod;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class AnalyticService
{
    /**
     * Calcula os dados analíticos de um período.
     *
     * @param int $unidadeId
     * @param Carbon $startDateCarbon
     * @param Carbon $endDateCarbon
     * @param bool $isCalendarMode Indica se a chamada é para o calendário (afeta o tratamento de saldo inicial CMV)
     * @param int|null $month Usado apenas no modo calendário para o primeiro mês.
     * @param int|null $year Usado apenas no modo calendário para o primeiro mês.
     * @return array
     */
    public function calculatePeriodData(int $unidadeId, Carbon $startDateCarbon, Carbon $endDateCarbon, bool $isCalendarMode = false, ?int $month = null, ?int $year = null, bool $includeOrderMetrics = false): array
    {
        // Gerar chave de cache única baseada nos parâmetros
        $cacheKey = $this->generateCacheKey($unidadeId, $startDateCarbon, $endDateCarbon, $isCalendarMode, $month, $year, $includeOrderMetrics);

        // Determinar TTL baseado se o período é passado ou atual
        $cacheTTL = $this->determineCacheTTL($endDateCarbon);

        return Cache::remember($cacheKey, $cacheTTL, function () use ($unidadeId, $startDateCarbon, $endDateCarbon, $isCalendarMode, $month, $includeOrderMetrics) {
            return $this->performCalculations($unidadeId, $startDateCarbon, $endDateCarbon, $isCalendarMode, $month, $includeOrderMetrics);
        });
    }

    /**
     * Invalida o cache de analytics para uma unidade específica.
     * Atualiza o timestamp da "versão" do cache para essa unidade.
     */
    public static function invalidateCache(int $unidadeId): void
    {
        $versionKey = "analytics_version_{$unidadeId}";
        Cache::forever($versionKey, now()->timestamp);
        Log::info("Cache de analytics invalidado para unidade {$unidadeId}");
    }

    /**
     * Obtém a versão atual do cache para uma unidade.
     */
    private function getCacheVersion(int $unidadeId): int
    {
        $versionKey = "analytics_version_{$unidadeId}";
        return Cache::rememberForever($versionKey, function () {
            return now()->timestamp;
        });
    }

    /**
     * Gera uma chave de cache única para os parâmetros fornecidos.
     */
    private function generateCacheKey(int $unidadeId, Carbon $startDateCarbon, Carbon $endDateCarbon, bool $isCalendarMode, ?int $month, ?int $year, bool $includeOrderMetrics): string
    {
        $startDate = $startDateCarbon->format('Y-m-d');
        $endDate = $endDateCarbon->format('Y-m-d');
        $calendarFlag = $isCalendarMode ? '1' : '0';
        $orderMetricsFlag = $includeOrderMetrics ? '1' : '0';
        $version = $this->getCacheVersion($unidadeId);

        return "analytics_{$unidadeId}_{$startDate}_{$endDate}_{$calendarFlag}_{$month}_{$year}_{$orderMetricsFlag}_v{$version}";
    }

    /**
     * Determina o TTL do cache baseado se o período é passado ou atual.
     * Períodos passados: 1 hora (3600s)
     * Período atual (inclui hoje): 5 minutos (300s)
     */
    private function determineCacheTTL(Carbon $endDateCarbon): int
    {
        $now = Carbon::now();

        // Se o período termina antes de hoje, é um período passado (cache mais longo)
        if ($endDateCarbon->endOfDay()->lt($now->startOfDay())) {
            return 3600; // 1 hora
        }

        // Se o período inclui hoje, cache mais curto
        return 300; // 5 minutos
    }

    /**
     * Executa os cálculos analíticos (lógica original extraída).
     */
    private function performCalculations(int $unidadeId, Carbon $startDateCarbon, Carbon $endDateCarbon, bool $isCalendarMode, ?int $month, bool $includeOrderMetrics): array
    {
        // Data de corte para usar a nova API
        $cutoffDate = Carbon::create(2026, 1, 1, 0, 0, 0);

        // Se o início do período for maior ou igual a 01/01/2026, usa a API externa para dados de faturamento
        if ($startDateCarbon->greaterThanOrEqualTo($cutoffDate)) {
            try {
                return $this->performCalculationsExternal($unidadeId, $startDateCarbon, $endDateCarbon, $isCalendarMode, $month, $includeOrderMetrics);
            } catch (\Exception $e) {
                Log::error("Erro ao buscar dados externos, retornando fallback local: " . $e->getMessage());
            }
        }

        return $this->performCalculationsLocal($unidadeId, $startDateCarbon, $endDateCarbon, $isCalendarMode, $month, $includeOrderMetrics);
    }

    /**
     * Realiza os cálculos usando a API externa para faturamento e dados financeiros principais.
     */
    private function performCalculationsExternal(int $unidadeId, Carbon $startDateCarbon, Carbon $endDateCarbon, bool $isCalendarMode, ?int $month, bool $includeOrderMetrics): array
    {
        // 1. Buscar dados da API Externa
        $externalData = $this->fetchExternalFaturamentoData($unidadeId, $startDateCarbon, $endDateCarbon);

        // Mapear dados da API
        $totalCaixas = (float) $externalData['faturamento'];
        $quantidadePedidos = (int) $externalData['quantidade_pedidos'];
        $ticketMedio = (float) $externalData['ticket_medio'];
        $totalTaxasDelivery = (float) ($externalData['taxas_canais_venda'] ?? 0);

        $taxas = $externalData['taxas'] ?? [];
        $totalTaxasCredito = (float) ($taxas['credito'] ?? 0);
        $totalTaxasDebito = (float) ($taxas['debito'] ?? 0);
        $totalTaxasVrAlimentacao = (float) ($taxas['voucher'] ?? 0);

        // Mapping 'custo_entregas' to 'totalMotoboy'
        $totalMotoboy = (float) $externalData['custo_entregas'];

        // 2. Calcular métricas que NÃO vêm da API (Estoque/CMV, Salários, FGTS)
        $stockMetrics = $this->calculateStockMetrics($unidadeId, $startDateCarbon, $endDateCarbon, $isCalendarMode, $month);
        $totalSalarios = $this->fetchSalaries($unidadeId);

        // FGTS continua local
        $operationalExpensesLocal = $this->calculateOperationalExpenses($unidadeId, $startDateCarbon, $endDateCarbon);
        $totalFGTS = $operationalExpensesLocal['fgts'];
        // Motoboy veio da API, então ignoramos o local

        // 3. Cálculos Derivados
        $totalLiquido = $totalCaixas - $totalMotoboy;
        $totalRoyalties = $totalLiquido * 0.05;
        $totalFundoPropaganda = $totalLiquido * 0.015;

        // Montar array de taxas para o contexto
        $taxFees = [
            'credito' => $totalTaxasCredito,
            'debito' => $totalTaxasDebito,
            'vr_alimentacao' => $totalTaxasVrAlimentacao,
            'delivery' => $totalTaxasDelivery,
            'canais' => $totalTaxasDelivery,
        ];

        $context = [
            'unidadeId' => $unidadeId,
            'startDateCarbon' => $startDateCarbon,
            'endDateCarbon' => $endDateCarbon,
            'totalSalarios' => $totalSalarios,
            'totalRoyalties' => $totalRoyalties,
            'totalFundoPropaganda' => $totalFundoPropaganda,
            'totalLiquido' => $totalLiquido,
            'totalFGTS' => $totalFGTS,
            'taxFees' => $taxFees,
            'cmv' => $stockMetrics['cmv'],
        ];

        // Calcular Categorias (Despesas) com os novos valores base
        $categoryData = $this->calculateCategoryGroups($context);

        $resultado_do_periodo_sem_folha = max($totalCaixas - $categoryData['totalDespesasCategoriasSemFolha'], 0);

        $result = [
            'estoqueInicialValor' => $stockMetrics['estoqueInicialValor'],
            'comprasValor' => $stockMetrics['comprasValor'],
            'estoqueFinalValor' => $stockMetrics['estoqueFinalValor'],
            'cmv' => $stockMetrics['cmv'],

            'total_caixas' => $totalCaixas,
            'total_salarios' => $totalSalarios,
            'total_motoboy' => $totalMotoboy,
            'total_royalties' => $totalRoyalties,
            'total_fundo_propaganda' => $totalFundoPropaganda,
            "Total_Liquido" => $totalLiquido,
            'total_fgts' => $totalFGTS,
            'total_taxas_credito' => $taxFees['credito'],
            'total_taxas_debito' => $taxFees['debito'],
            'total_taxas_vr_alimentacao' => $taxFees['vr_alimentacao'],
            "total_taxas_canais" => $taxFees['delivery'],
            'total_despesas_categorias' => $categoryData['totalDespesasCategorias'],
            'total_despesas_categorias_sem_folha' => $categoryData['totalDespesasCategoriasSemFolha'],
            'resultado_do_periodo_sem_folha' => $resultado_do_periodo_sem_folha,
            'dados_grupos' => $categoryData['dadosGrupos'],
        ];

        if ($includeOrderMetrics) {
            // Métricas de pedidos vêm da API
            $orderMetrics = [
                'quantidade_pedidos' => $quantidadePedidos,
                'faturamento_total' => $totalCaixas,
                'ticket_medio' => $ticketMedio,
            ];
            $result = array_merge($result, $orderMetrics);
        }

        return $result;
    }

    /**
     * Executa os cálculos analíticos (Lógica Original Local).
     */
    private function performCalculationsLocal(int $unidadeId, Carbon $startDateCarbon, Carbon $endDateCarbon, bool $isCalendarMode, ?int $month, bool $includeOrderMetrics): array
    {
        $stockMetrics = $this->calculateStockMetrics($unidadeId, $startDateCarbon, $endDateCarbon, $isCalendarMode, $month);

        $totalCaixas = $this->calculateTotalCaixas($unidadeId, $startDateCarbon, $endDateCarbon);
        $totalSalarios = $this->fetchSalaries($unidadeId);

        $operationalExpenses = $this->calculateOperationalExpenses($unidadeId, $startDateCarbon, $endDateCarbon);
        $totalFGTS = $operationalExpenses['fgts'];
        $totalMotoboy = $operationalExpenses['motoboy'];

        $totalLiquido = $totalCaixas - $totalMotoboy;
        $totalRoyalties = $totalLiquido * 0.05;
        $totalFundoPropaganda = $totalLiquido * 0.015;

        $taxFees = $this->calculateTaxFees($unidadeId, $startDateCarbon, $endDateCarbon);

        $context = [
            'unidadeId' => $unidadeId,
            'startDateCarbon' => $startDateCarbon,
            'endDateCarbon' => $endDateCarbon,
            'totalSalarios' => $totalSalarios,
            'totalRoyalties' => $totalRoyalties,
            'totalFundoPropaganda' => $totalFundoPropaganda,
            'totalLiquido' => $totalLiquido,
            'totalFGTS' => $totalFGTS,
            'taxFees' => $taxFees,
            'cmv' => $stockMetrics['cmv'],
        ];

        $categoryData = $this->calculateCategoryGroups($context);

        $resultado_do_periodo_sem_folha = max($totalCaixas - $categoryData['totalDespesasCategoriasSemFolha'], 0);

        $result = [
            'estoqueInicialValor' => $stockMetrics['estoqueInicialValor'],
            'comprasValor' => $stockMetrics['comprasValor'],
            'estoqueFinalValor' => $stockMetrics['estoqueFinalValor'],
            'cmv' => $stockMetrics['cmv'],

            'total_caixas' => $totalCaixas,
            'total_salarios' => $totalSalarios,
            'total_motoboy' => $totalMotoboy,
            'total_royalties' => $totalRoyalties,
            'total_fundo_propaganda' => $totalFundoPropaganda,
            "Total_Liquido" => $totalLiquido,
            'total_fgts' => $totalFGTS,
            'total_taxas_credito' => $taxFees['credito'],
            'total_taxas_debito' => $taxFees['debito'],
            'total_taxas_vr_alimentacao' => $taxFees['vr_alimentacao'],
            "total_taxas_canais" => $taxFees['delivery'],
            'total_despesas_categorias' => $categoryData['totalDespesasCategorias'],
            'total_despesas_categorias_sem_folha' => $categoryData['totalDespesasCategoriasSemFolha'],
            'resultado_do_periodo_sem_folha' => $resultado_do_periodo_sem_folha,
            'dados_grupos' => $categoryData['dadosGrupos'],
        ];

        if ($includeOrderMetrics) {
            $orderMetrics = $this->calculateOrderMetrics($unidadeId, $startDateCarbon, $endDateCarbon);
            $result = array_merge($result, $orderMetrics);
        }

        return $result;
    }

    private function calculateStockMetrics(int $unidadeId, Carbon $startDateCarbon, Carbon $endDateCarbon, bool $isCalendarMode, ?int $month): array
    {
        // 1. Saldo Inicial de Estoque
        $saldoInicialData = ControleSaldoEstoque::where('unidade_id', $unidadeId)
            ->where('data_ajuste', '<', $startDateCarbon)
            ->orderBy('data_ajuste', 'desc')
            ->first();

        $estoqueInicialValor = $saldoInicialData ? $saldoInicialData->ajuste_saldo : 0;

        // Lógica para o primeiro ajuste da unidade, principalmente para o calendário
        if ($isCalendarMode && $month === 1 && !$saldoInicialData) {
            $primeiroAjusteGeral = ControleSaldoEstoque::where('unidade_id', $unidadeId)
                ->orderBy('data_ajuste', 'asc')
                ->first();
            $estoqueInicialValor = $primeiroAjusteGeral ? $primeiroAjusteGeral->ajuste_saldo : 0;
        }

        // 2. Compras (Entradas) no período
        $compras = MovimentacoesEstoque::where('unidade_id', $unidadeId)
            ->where('operacao', 'Entrada')
            ->whereBetween('created_at', [$startDateCarbon, $endDateCarbon])
            ->get();
        $comprasValor = $compras->sum(fn($item) => $item->preco_insumo * $item->quantidade);

        // 3. Saldo Final de Estoque
        $saldoFinalControle = ControleSaldoEstoque::where('unidade_id', $unidadeId)
            ->where('data_ajuste', '<=', $endDateCarbon)
            ->orderBy('data_ajuste', 'desc')
            ->first();
        $estoqueFinalValor = $saldoFinalControle ? $saldoFinalControle->ajuste_saldo : 0;

        // Calcular o CMV
        $cmv = $estoqueInicialValor + $comprasValor - $estoqueFinalValor;

        return [
            'estoqueInicialValor' => $estoqueInicialValor,
            'comprasValor' => $comprasValor,
            'estoqueFinalValor' => $estoqueFinalValor,
            'cmv' => $cmv,
        ];
    }

    public function calculateTotalCaixas(int $unidadeId, Carbon $startDateCarbon, Carbon $endDateCarbon): float
    {
        return Caixa::where('unidade_id', $unidadeId)
            ->where('status', 0)
            ->whereBetween('created_at', [$startDateCarbon, $endDateCarbon])
            ->sum('valor_final');
    }

    private function fetchSalaries(int $unidadeId): float
    {
        $cacheKey = "salaries_unit_{$unidadeId}";

        // Tenta obter do cache primeiro
        $cachedValue = Cache::get($cacheKey);
        if ($cachedValue !== null && $cachedValue > 0) {
            return $cachedValue;
        }

        try {
            // Token da API do RH
            $token = Auth::user()->rh_token ?? Session::get('rh_token');

            if (!$token) {
                // Se não tiver token, não podemos buscar, mas não cacheamos o erro "0" por muito tempo
                return 0;
            }

            $url = "https://rh.taiksu.com.br/folha/{$unidadeId}";
            $response = Http::withToken($token)->get($url);

            if ($response->successful()) {
                $data = $response->json();
                $totalSalarios = (float) Arr::get($data, 'total_salarios', 0);

                // Só cacheamos se o valor for maior que 0 (evita cachear falhas silenciosas ou unidades sem dados temporários)
                if ($totalSalarios > 0) {
                    Cache::put($cacheKey, $totalSalarios, 86400);
                }

                return $totalSalarios;
            } else {
                Log::error("Erro ao buscar salários da API RH: {$response->status()} - {$response->body()}");
                return 0;
            }
        } catch (\Throwable $e) {
            Log::error('Falha ao acessar API RH: ' . $e->getMessage());
            return 0;
        }
    }

    private function calculateOperationalExpenses(int $unidadeId, Carbon $startDateCarbon, Carbon $endDateCarbon): array
    {
        $totalFGTS = ContaAPagar::where('unidade_id', $unidadeId)
            ->whereHas('categoria', function ($query) {
                $query->where('nome', 'LIKE', '%FGTS%');
            })
            ->whereIn('status', ['pago', 'pendente', 'agendada', 'atrasado'])
            ->whereBetween('emitida_em', [$startDateCarbon, $endDateCarbon])
            ->sum('valor');

        $totalMotoboy = ContaAPagar::where('unidade_id', $unidadeId)
            ->whereHas('categoria', function ($query) {
                $query->where('nome', 'Motoboy');
            })
            ->whereIn('status', ['pago', 'pendente', 'agendada', 'atrasado'])
            ->whereBetween('emitida_em', [$startDateCarbon, $endDateCarbon])
            ->sum('valor');

        return [
            'fgts' => $totalFGTS,
            'motoboy' => $totalMotoboy,
        ];
    }

    private function calculateTaxFees(int $unidadeId, Carbon $startDateCarbon, Carbon $endDateCarbon): array
    {
        // Cache de IDs de métodos de pagamento (dados estáticos)
        $creditoIds = $this->getCachedPaymentMethodIds('credito');
        $debitoIds = $this->getCachedPaymentMethodIds('debito');
        $vrAlimentacaoIds = $this->getCachedPaymentMethodIds('vr_alimentacao');

        $totalTaxasCredito = FechamentoCaixa::where('unidade_id', $unidadeId)
            ->whereIn('metodo_pagamento_id', $creditoIds)
            ->whereBetween('created_at', [$startDateCarbon, $endDateCarbon])
            ->sum('valor_taxa_metodo');

        $totalTaxasDebito = FechamentoCaixa::where('unidade_id', $unidadeId)
            ->whereIn('metodo_pagamento_id', $debitoIds)
            ->whereBetween('created_at', [$startDateCarbon, $endDateCarbon])
            ->sum('valor_taxa_metodo');

        $totalTaxasVrAlimentacao = FechamentoCaixa::where('unidade_id', $unidadeId)
            ->whereIn('metodo_pagamento_id', $vrAlimentacaoIds)
            ->whereBetween('created_at', [$startDateCarbon, $endDateCarbon])
            ->sum('valor_taxa_metodo');

        // Note: In original code, totalTaxasCanais and totalTaxasDelivery were identical queries.
        $totalTaxasDelivery = CanalVenda::where('unidade_id', $unidadeId)
            ->whereBetween('created_at', [$startDateCarbon, $endDateCarbon])
            ->sum('valor_taxa_canal');

        return [
            'credito' => $totalTaxasCredito,
            'debito' => $totalTaxasDebito,
            'vr_alimentacao' => $totalTaxasVrAlimentacao,
            'delivery' => $totalTaxasDelivery,
            'canais' => $totalTaxasDelivery, // Keeping both keys if needed, but they are same value
        ];
    }

    /**
     * Retorna IDs de métodos de pagamento com cache permanente.
     */
    private function getCachedPaymentMethodIds(string $tipo): array
    {
        $cacheKey = "payment_method_ids_{$tipo}";

        return Cache::rememberForever($cacheKey, function () use ($tipo) {
            return DB::table('default_payment_methods')
                ->where('tipo', $tipo)
                ->pluck('id')
                ->toArray();
        });
    }

    private function calculateCategoryGroups(array $context): array
    {
        $categoriasRemovidas = ["Fornecedores"];
        $categoriasIgnoradasNaSoma = ["Fornecedores"];

        // Cache de grupos de categorias por 1 hora (estrutura muda raramente)
        $grupos = Cache::remember('category_groups_with_categories', 3600, function () {
            return GrupoDeCategorias::with('categorias')->get();
        });
        $totalDespesasCategorias = 0;
        $totalDespesasCategoriasSemFolha = 0;

        $dadosGrupos = $grupos->map(function ($grupo) use ($context, $categoriasRemovidas, $categoriasIgnoradasNaSoma, &$totalDespesasCategorias, &$totalDespesasCategoriasSemFolha) {
            $categoriasFormatadas = $grupo->categorias
                ->reject(fn($categoria) => in_array($categoria->nome, $categoriasRemovidas))
                ->map(function ($categoria) use ($context, $categoriasIgnoradasNaSoma, &$totalDespesasCategorias, &$totalDespesasCategoriasSemFolha) {

                    $valor = ContaAPagar::where('categoria_id', $categoria->id)
                        ->where('unidade_id', $context['unidadeId'])
                        ->whereIn('status', ['pago', 'pendente', 'agendada'])
                        ->whereBetween('emitida_em', [$context['startDateCarbon'], $context['endDateCarbon']])
                        ->sum('valor');

                    $valoresFixos = [
                        "Mercadoria Vendida" => $context['cmv'],
                        "FGTS" => $context['totalFGTS'],
                        "Folha de pagamento" => $context['totalSalarios'],
                        "Royalties" => $context['totalRoyalties'],
                        "Fundo de Propaganda" =>  $context['totalFundoPropaganda'],
                        "Taxa de Crédito" => $context['taxFees']['credito'],
                        "Taxa de Débito" => $context['taxFees']['debito'],
                        "Plataformas de Delivery" => $context['taxFees']['canais'],
                        "Taxas de Delivery" => $context['taxFees']['delivery'],
                        "Voucher Alimentação" => $context['taxFees']['vr_alimentacao']
                    ];

                    if (isset($valoresFixos[$categoria->nome])) {
                        $valor = $valoresFixos[$categoria->nome];
                    }

                    if (!in_array($categoria->nome, $categoriasIgnoradasNaSoma)) {
                        $totalDespesasCategoriasSemFolha += $valor;
                    }
                    $totalDespesasCategorias += $valor;

                    return [
                        'categoria' => $categoria->nome,
                        'total' => $valor,
                        'valor' => $valor
                    ];
                });

            return [
                'nome_grupo' => $grupo->nome,
                'categorias' => $categoriasFormatadas
            ];
        });

        return [
            'dadosGrupos' => $dadosGrupos,
            'totalDespesasCategorias' => $totalDespesasCategorias,
            'totalDespesasCategoriasSemFolha' => $totalDespesasCategoriasSemFolha,
        ];
    }

    /**
     * Calcula métricas relacionadas a pedidos (quantidade, faturamento e ticket médio).
     *
     * @param int $unidadeId
     * @param Carbon $startDateCarbon
     * @param Carbon $endDateCarbon
     * @return array
     */
    private function calculateOrderMetrics(int $unidadeId, Carbon $startDateCarbon, Carbon $endDateCarbon): array
    {
        $pedidos = CanalVenda::where('unidade_id', $unidadeId)
            ->whereBetween('created_at', [$startDateCarbon, $endDateCarbon])
            ->get();

        $quantidadePedidos = $pedidos->sum('quantidade_vendas_feitas');
        $faturamentoTotal = $pedidos->sum('valor_total_vendas');
        $ticketMedio = $quantidadePedidos > 0 ? $faturamentoTotal / $quantidadePedidos : 0;

        return [
            'quantidade_pedidos' => $quantidadePedidos,
            'faturamento_total' => $faturamentoTotal,
            'ticket_medio' => $ticketMedio,
        ];
    }

    /**
     * Busca dados de faturamento da API externa.
     */
    private function fetchExternalFaturamentoData(int $unidadeId, Carbon $startDate, Carbon $endDate): array
    {
        $url = "https://caixa.taiksu.com.br/api/faturamento";

        $response = Http::get($url, [
            'unidade' => $unidadeId,
            'inicio' => $startDate->format('Y-m-d'),
            'final' => $endDate->format('Y-m-d'),
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception("Falha na API de faturamento: " . $response->status());
    }
}
