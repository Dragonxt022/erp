<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Models\{
    Caixa,
    CanalVenda,
    ContaAPagar,
    ControleSaldoEstoque,
    FechamentoCaixa,
    GrupoDeCategorias,
    MovimentacoesEstoque
};
use Carbon\Carbon;
use Exception;

class AnalyticService
{
    // Constantes
    private const CACHE_TTL_PAST_PERIOD = 300;      // 5 minutos (extremamente rápido)
    private const CACHE_TTL_CURRENT_PERIOD = 30;    // 30 segundos (quase real-time)
    private const CACHE_TTL_STATIC_DATA = 600;      // 10 minutos (ainda seguro)
    private const ROYALTIES_RATE = 0.05;
    private const PROPAGANDA_FUND_RATE = 0.015;
    private const API_CUTOFF_DATE = '2026-01-01';

    // URLs das APIs
    private const API_FATURAMENTO_URL = 'https://caixa.taiksu.com.br/api/faturamento';
    private const API_RH_URL = 'https://rh.taiksu.com.br/folha';

    /**
     * Calcula os dados analíticos de um período.
     *
     * @param int $unidadeId ID da unidade
     * @param Carbon $startDate Data inicial
     * @param Carbon $endDate Data final
     * @param bool $isCalendarMode Modo calendário
     * @param int|null $month Mês (modo calendário)
     * @param int|null $year Ano (modo calendário)
     * @param bool $includeOrderMetrics Incluir métricas de pedidos
     * @return array
     * @throws \InvalidArgumentException
     */
    public function calculatePeriodData(
        int $unidadeId,
        Carbon $startDate,
        Carbon $endDate,
        bool $isCalendarMode = false,
        ?int $month = null,
        ?int $year = null,
        bool $includeOrderMetrics = false
    ): array {
        $this->validatePeriodData($unidadeId, $startDate, $endDate);

        $cacheKey = $this->generateCacheKey(
            $unidadeId,
            $startDate,
            $endDate,
            $isCalendarMode,
            $month,
            $year,
            $includeOrderMetrics
        );

        $cacheTTL = $this->determineCacheTTL($endDate);

        return Cache::remember($cacheKey, $cacheTTL, function () use (
            $unidadeId,
            $startDate,
            $endDate,
            $isCalendarMode,
            $month,
            $includeOrderMetrics
        ) {
            return $this->performCalculations(
                $unidadeId,
                $startDate,
                $endDate,
                $isCalendarMode,
                $month,
                $includeOrderMetrics
            );
        });
    }

    /**
     * Invalida o cache de analytics para uma unidade específica.
     */
    public static function invalidateCache(int $unidadeId): void
    {
        if ($unidadeId <= 0) {
            Log::warning("Tentativa de invalidar cache com unidadeId inválido: {$unidadeId}");
            return;
        }

        $versionKey = "analytics_version_{$unidadeId}";
        Cache::forever($versionKey, now()->timestamp);
        Log::info("Cache de analytics invalidado para unidade {$unidadeId}");
    }

    /**
     * Valida os dados do período.
     *
     * @throws \InvalidArgumentException
     */
    private function validatePeriodData(int $unidadeId, Carbon $startDate, Carbon $endDate): void
    {
        if ($unidadeId <= 0) {
            throw new \InvalidArgumentException("ID da unidade deve ser maior que zero");
        }

        if ($startDate->greaterThan($endDate)) {
            throw new \InvalidArgumentException("Data inicial não pode ser maior que data final");
        }
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
    private function generateCacheKey(
        int $unidadeId,
        Carbon $startDate,
        Carbon $endDate,
        bool $isCalendarMode,
        ?int $month,
        ?int $year,
        bool $includeOrderMetrics
    ): string {
        $version = $this->getCacheVersion($unidadeId);

        return sprintf(
            'analytics_%d_%s_%s_%d_%d_%d_%d_v%d',
            $unidadeId,
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d'),
            $isCalendarMode ? 1 : 0,
            $month ?? 0,
            $year ?? 0,
            $includeOrderMetrics ? 1 : 0,
            $version
        );
    }

    /**
     * Determina o TTL do cache baseado se o período é passado ou atual.
     */
    private function determineCacheTTL(Carbon $endDate): int
    {
        $isCurrentPeriod = $endDate->endOfDay()->greaterThanOrEqualTo(now()->startOfDay());

        return $isCurrentPeriod
            ? self::CACHE_TTL_CURRENT_PERIOD
            : self::CACHE_TTL_PAST_PERIOD;
    }

    /**
     * Executa os cálculos analíticos.
     */
    private function performCalculations(
        int $unidadeId,
        Carbon $startDate,
        Carbon $endDate,
        bool $isCalendarMode,
        ?int $month,
        bool $includeOrderMetrics
    ): array {
        $cutoffDate = Carbon::parse(self::API_CUTOFF_DATE);

        if ($startDate->greaterThanOrEqualTo($cutoffDate)) {
            try {
                return $this->performCalculationsExternal(
                    $unidadeId,
                    $startDate,
                    $endDate,
                    $isCalendarMode,
                    $month,
                    $includeOrderMetrics
                );
            } catch (Exception $e) {
                Log::error("Erro ao buscar dados externos, usando fallback local", [
                    'unidade_id' => $unidadeId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        return $this->performCalculationsLocal(
            $unidadeId,
            $startDate,
            $endDate,
            $isCalendarMode,
            $month,
            $includeOrderMetrics
        );
    }

    /**
     * Realiza os cálculos usando a API externa.
     */
    private function performCalculationsExternal(
        int $unidadeId,
        Carbon $startDate,
        Carbon $endDate,
        bool $isCalendarMode,
        ?int $month,
        bool $includeOrderMetrics
    ): array {
        $externalData = $this->fetchExternalFaturamentoData($unidadeId, $startDate, $endDate);

        // Validar dados da API
        $this->validateExternalData($externalData);

        // Extrair dados da API
        $apiMetrics = $this->extractApiMetrics($externalData);

        // Calcular métricas locais
        $stockMetrics = $this->calculateStockMetrics($unidadeId, $startDate, $endDate, $isCalendarMode, $month);
        $totalSalarios = $this->fetchSalaries($unidadeId);
        $totalFGTS = $this->calculateFGTS($unidadeId, $startDate, $endDate);

        // Determinar qual faturamento usar baseado no modo_estrito
        $modoEstrito = $apiMetrics['modoEstrito'];
        $faturamentoPeriodo = $modoEstrito
            ? $apiMetrics['faturamentoAuditado']     // Quando true (estrito), usa auditado
            : $apiMetrics['faturamentoNaoAuditado']; // Quando false, usa não auditado

        // Cálculos derivados usando o faturamento correto
        $totalLiquido = $faturamentoPeriodo - $apiMetrics['totalMotoboy'];
        $totalRoyalties = $totalLiquido * self::ROYALTIES_RATE;
        $totalFundoPropaganda = $totalLiquido * self::PROPAGANDA_FUND_RATE;

        // Montar contexto
        $context = $this->buildCalculationContext(
            $unidadeId,
            $startDate,
            $endDate,
            $totalSalarios,
            $totalRoyalties,
            $totalFundoPropaganda,
            $totalLiquido,
            $totalFGTS,
            $apiMetrics['taxFees'],
            $stockMetrics['cmv'],
            $apiMetrics['totalMotoboy']
        );

        // Calcular categorias
        $categoryData = $this->calculateCategoryGroups($context);

        // Montar resultado
        $result = $this->buildResult(
            $stockMetrics,
            $faturamentoPeriodo, // Usa o faturamento correto baseado no modo
            $totalSalarios,
            $apiMetrics['totalMotoboy'],
            $totalRoyalties,
            $totalFundoPropaganda,
            $totalLiquido,
            $totalFGTS,
            $apiMetrics['taxFees'],
            $categoryData,
            $apiMetrics['faturamentoNaoAuditado']
        );

        if ($includeOrderMetrics) {
            $result = array_merge($result, [
                'quantidade_pedidos' => $apiMetrics['quantidadePedidos'],
                'faturamento_total' => $faturamentoPeriodo,
                'ticket_medio' => $apiMetrics['ticketMedio'],
            ]);
        }

        return $result;
    }

    /**
     * Valida os dados retornados pela API externa.
     *
     * @throws Exception
     */
    private function validateExternalData(array $data): void
    {
        $requiredFields = [
            'modo_estrito',
            'faturamento',
            'faturamento_nao_auditado',
            'quantidade_pedidos',
            'ticket_medio',
            'custo_entregas',
            'taxas'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new Exception("Campo obrigatório ausente na resposta da API: {$field}");
            }
        }
    }

    /**
     * Extrai métricas da resposta da API.
     */
    private function extractApiMetrics(array $externalData): array
    {
        $taxas = $externalData['taxas'] ?? [];

        return [
            'modoEstrito' => (bool) $externalData['modo_estrito'],
            'faturamentoAuditado' => (float) $externalData['faturamento'],
            'faturamentoNaoAuditado' => (float) $externalData['faturamento_nao_auditado'],
            'quantidadePedidos' => (int) $externalData['quantidade_pedidos'],
            'ticketMedio' => (float) $externalData['ticket_medio'],
            'totalMotoboy' => (float) $externalData['custo_entregas'],
            'taxFees' => [
                'pix' => (float) ($taxas['pix'] ?? 0),
                'credito' => (float) ($taxas['credito'] ?? 0),
                'debito' => (float) ($taxas['debito'] ?? 0),
                'vr_alimentacao' => (float) ($taxas['voucher'] ?? 0),
                'delivery' => (float) ($externalData['taxas_canais_venda'] ?? 0),
                'canais' => (float) ($externalData['taxas_canais_venda'] ?? 0),
            ]
        ];
    }

    /**
     * Executa os cálculos analíticos com dados locais.
     */
    private function performCalculationsLocal(
        int $unidadeId,
        Carbon $startDate,
        Carbon $endDate,
        bool $isCalendarMode,
        ?int $month,
        bool $includeOrderMetrics
    ): array {
        $stockMetrics = $this->calculateStockMetrics($unidadeId, $startDate, $endDate, $isCalendarMode, $month);
        $totalCaixas = $this->calculateTotalCaixas($unidadeId, $startDate, $endDate);
        $totalSalarios = $this->fetchSalaries($unidadeId);
        $operationalExpenses = $this->calculateOperationalExpenses($unidadeId, $startDate, $endDate);

        $totalLiquido = $totalCaixas - $operationalExpenses['motoboy'];
        $totalRoyalties = $totalLiquido * self::ROYALTIES_RATE;
        $totalFundoPropaganda = $totalLiquido * self::PROPAGANDA_FUND_RATE;

        $taxFees = $this->calculateTaxFees($unidadeId, $startDate, $endDate);

        $context = $this->buildCalculationContext(
            $unidadeId,
            $startDate,
            $endDate,
            $totalSalarios,
            $totalRoyalties,
            $totalFundoPropaganda,
            $totalLiquido,
            $operationalExpenses['fgts'],
            $taxFees,
            $stockMetrics['cmv'],
            $operationalExpenses['motoboy']
        );

        $categoryData = $this->calculateCategoryGroups($context);

        $result = $this->buildResult(
            $stockMetrics,
            $totalCaixas,
            $totalSalarios,
            $operationalExpenses['motoboy'],
            $totalRoyalties,
            $totalFundoPropaganda,
            $totalLiquido,
            $operationalExpenses['fgts'],
            $taxFees,
            $categoryData
        );

        if ($includeOrderMetrics) {
            $orderMetrics = $this->calculateOrderMetrics($unidadeId, $startDate, $endDate);
            $result = array_merge($result, $orderMetrics);
        }

        return $result;
    }

    /**
     * Constrói o contexto para cálculo de categorias.
     */
    private function buildCalculationContext(
        int $unidadeId,
        Carbon $startDate,
        Carbon $endDate,
        float $totalSalarios,
        float $totalRoyalties,
        float $totalFundoPropaganda,
        float $totalLiquido,
        float $totalFGTS,
        array $taxFees,
        float $cmv,
        float $totalMotoboy = 0.0
    ): array {
        return [
            'unidadeId' => $unidadeId,
            'startDateCarbon' => $startDate,
            'endDateCarbon' => $endDate,
            'totalSalarios' => $totalSalarios,
            'totalMotoboy' => $totalMotoboy,
            'totalRoyalties' => $totalRoyalties,
            'totalFundoPropaganda' => $totalFundoPropaganda,
            'totalLiquido' => $totalLiquido,
            'totalFGTS' => $totalFGTS,
            'taxFees' => $taxFees,
            'cmv' => $cmv,
        ];
    }

    /**
     * Constrói o array de resultado final.
     */
    private function buildResult(
        array $stockMetrics,
        float $totalCaixas,
        float $totalSalarios,
        float $totalMotoboy,
        float $totalRoyalties,
        float $totalFundoPropaganda,
        float $totalLiquido,
        float $totalFGTS,
        array $taxFees,
        array $categoryData,
        float $faturamentoNaoAuditado = null
    ): array {
        $resultado_do_periodo_sem_folha = max(
            $totalCaixas - $categoryData['totalDespesasCategoriasSemFolha'],
            0
        );

        return [
            // Métricas de estoque
            'estoqueInicialValor' => $stockMetrics['estoqueInicialValor'],
            'comprasValor' => $stockMetrics['comprasValor'],
            'estoqueFinalValor' => $stockMetrics['estoqueFinalValor'],
            'cmv' => $stockMetrics['cmv'],

            // Métricas financeiras
            'total_caixas' => $totalCaixas,
            'faturamento_nao_auditado' => $faturamentoNaoAuditado ?? $totalCaixas,
            'total_salarios' => $totalSalarios,
            'total_motoboy' => $totalMotoboy,
            'total_royalties' => $totalRoyalties,
            'total_fundo_propaganda' => $totalFundoPropaganda,
            'Total_Liquido' => $totalLiquido,
            'total_fgts' => $totalFGTS,

            // Taxas
            'total_taxas_pix' => $taxFees['pix'],
            'total_taxas_debito' => $taxFees['debito'],
            'total_taxas_credito' => $taxFees['credito'],
            'total_taxas_vr_alimentacao' => $taxFees['vr_alimentacao'],
            'total_taxas_canais' => $taxFees['delivery'],

            // Despesas e resultado
            'total_despesas_categorias' => $categoryData['totalDespesasCategorias'],
            'total_despesas_categorias_sem_folha' => $categoryData['totalDespesasCategoriasSemFolha'],
            'resultado_do_periodo_sem_folha' => $resultado_do_periodo_sem_folha,
            'dados_grupos' => $categoryData['dadosGrupos'],
        ];
    }

    /**
     * Calcula métricas de estoque.
     */
    private function calculateStockMetrics(
        int $unidadeId,
        Carbon $startDate,
        Carbon $endDate,
        bool $isCalendarMode,
        ?int $month
    ): array {
        try {
            // Saldo inicial
            $estoqueInicialValor = $this->getInitialStockValue(
                $unidadeId,
                $startDate,
                $isCalendarMode,
                $month
            );

            // Compras no período
            $comprasValor = $this->calculatePurchases($unidadeId, $startDate, $endDate);

            // Saldo final
            $estoqueFinalValor = $this->getFinalStockValue($unidadeId, $endDate);

            // CMV
            $cmv = $estoqueInicialValor + $comprasValor - $estoqueFinalValor;

            return [
                'estoqueInicialValor' => $estoqueInicialValor,
                'comprasValor' => $comprasValor,
                'estoqueFinalValor' => $estoqueFinalValor,
                'cmv' => max($cmv, 0), // CMV não pode ser negativo
            ];
        } catch (Exception $e) {
            Log::error("Erro ao calcular métricas de estoque", [
                'unidade_id' => $unidadeId,
                'error' => $e->getMessage()
            ]);

            return [
                'estoqueInicialValor' => 0,
                'comprasValor' => 0,
                'estoqueFinalValor' => 0,
                'cmv' => 0,
            ];
        }
    }

    /**
     * Obtém o valor do estoque inicial.
     */
    private function getInitialStockValue(
        int $unidadeId,
        Carbon $startDate,
        bool $isCalendarMode,
        ?int $month
    ): float {
        $saldoInicial = ControleSaldoEstoque::where('unidade_id', $unidadeId)
            ->where('data_ajuste', '<', $startDate)
            ->orderBy('data_ajuste', 'desc')
            ->first();

        if ($saldoInicial) {
            return (float) $saldoInicial->ajuste_saldo;
        }

        // Lógica especial para primeiro mês no modo calendário
        if ($isCalendarMode && $month === 1) {
            $primeiroAjuste = ControleSaldoEstoque::where('unidade_id', $unidadeId)
                ->orderBy('data_ajuste', 'asc')
                ->first();

            return $primeiroAjuste ? (float) $primeiroAjuste->ajuste_saldo : 0;
        }

        return 0;
    }

    /**
     * Calcula o valor das compras no período.
     */
    private function calculatePurchases(int $unidadeId, Carbon $startDate, Carbon $endDate): float
    {
        return MovimentacoesEstoque::where('unidade_id', $unidadeId)
            ->where('operacao', 'Entrada')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->sum(fn($item) => $item->preco_insumo * $item->quantidade);
    }

    /**
     * Obtém o valor do estoque final.
     */
    private function getFinalStockValue(int $unidadeId, Carbon $endDate): float
    {
        $saldoFinal = ControleSaldoEstoque::where('unidade_id', $unidadeId)
            ->where('data_ajuste', '<=', $endDate)
            ->orderBy('data_ajuste', 'desc')
            ->first();

        return $saldoFinal ? (float) $saldoFinal->ajuste_saldo : 0;
    }

    /**
     * Calcula o total de caixas.
     */
    public function calculateTotalCaixas(int $unidadeId, Carbon $startDate, Carbon $endDate): float
    {
        try {
            return round(
                Caixa::where('unidade_id', $unidadeId)
                    ->where('status', 0)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->sum('valor_final'),
                2
            );
        } catch (Exception $e) {
            Log::error("Erro ao calcular total de caixas", [
                'unidade_id' => $unidadeId,
                'error' => $e->getMessage()
            ]);

            return 0;
        }
    }

    /**
     * Busca salários da API de RH.
     */
    private function fetchSalaries(int $unidadeId): float
    {
        $cacheKey = "salaries_unit_{$unidadeId}";

        // Verificar cache
        $cachedValue = Cache::get($cacheKey);
        if ($cachedValue !== null && $cachedValue > 0) {
            return (float) $cachedValue;
        }

        try {
            $token = $this->getRhToken();

            if (!$token) {
                Log::warning("Token RH não encontrado para unidade {$unidadeId}");
                return 0;
            }

            $url = self::API_RH_URL . "/{$unidadeId}";
            $response = Http::timeout(10)
                ->withToken($token)
                ->get($url);

            if (!$response->successful()) {
                Log::error("Erro ao buscar salários da API RH", [
                    'unidade_id' => $unidadeId,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return 0;
            }

            $data = $response->json();
            $totalSalarios = (float) ($data['total_salarios'] ?? 0);

            // Cachear apenas valores válidos
            if ($totalSalarios > 0) {
                Cache::put($cacheKey, $totalSalarios, 86400); // 24 horas
            }

            return $totalSalarios;

        } catch (Exception $e) {
            Log::error("Falha ao acessar API RH", [
                'unidade_id' => $unidadeId,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Obtém o token da API de RH.
     */
    private function getRhToken(): ?string
    {
        return Auth::user()->rh_token ?? Session::get('rh_token');
    }

    /**
     * Calcula o valor de FGTS.
     */
    private function calculateFGTS(int $unidadeId, Carbon $startDate, Carbon $endDate): float
    {
        try {
            return ContaAPagar::where('unidade_id', $unidadeId)
                ->whereHas('categoria', function ($query) {
                    $query->where('nome', 'LIKE', '%FGTS%');
                })
                ->whereIn('status', ['pago', 'pendente', 'agendada', 'atrasado'])
                ->whereBetween('emitida_em', [$startDate, $endDate])
                ->sum('valor');
        } catch (Exception $e) {
            Log::error("Erro ao calcular FGTS", [
                'unidade_id' => $unidadeId,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Calcula despesas operacionais.
     */
    private function calculateOperationalExpenses(
        int $unidadeId,
        Carbon $startDate,
        Carbon $endDate
    ): array {
        try {
            $totalFGTS = $this->calculateFGTS($unidadeId, $startDate, $endDate);

            $totalMotoboy = ContaAPagar::where('unidade_id', $unidadeId)
                ->whereHas('categoria', function ($query) {
                    $query->where('nome', 'Motoboy');
                })
                ->whereIn('status', ['pago', 'pendente', 'agendada', 'atrasado'])
                ->whereBetween('emitida_em', [$startDate, $endDate])
                ->sum('valor');

            return [
                'fgts' => $totalFGTS,
                'motoboy' => $totalMotoboy,
            ];
        } catch (Exception $e) {
            Log::error("Erro ao calcular despesas operacionais", [
                'unidade_id' => $unidadeId,
                'error' => $e->getMessage()
            ]);

            return [
                'fgts' => 0,
                'motoboy' => 0,
            ];
        }
    }

    /**
     * Calcula taxas de pagamento.
     */
    private function calculateTaxFees(int $unidadeId, Carbon $startDate, Carbon $endDate): array
    {
        try {
            $pixIds = $this->getCachedPaymentMethodIds('pix');
            $creditoIds = $this->getCachedPaymentMethodIds('credito');
            $debitoIds = $this->getCachedPaymentMethodIds('debito');
            $vrAlimentacaoIds = $this->getCachedPaymentMethodIds('vr_alimentacao');

            $totalTaxasPix = $this->calculatePaymentMethodTax(
                        $unidadeId,
                        $pixIds,
                        $startDate,
                        $endDate
                    );
            $totalTaxasCredito = $this->calculatePaymentMethodTax(
                $unidadeId,
                $creditoIds,
                $startDate,
                $endDate
            );

            $totalTaxasDebito = $this->calculatePaymentMethodTax(
                $unidadeId,
                $debitoIds,
                $startDate,
                $endDate
            );

            $totalTaxasVrAlimentacao = $this->calculatePaymentMethodTax(
                $unidadeId,
                $vrAlimentacaoIds,
                $startDate,
                $endDate
            );

            $totalTaxasDelivery = CanalVenda::where('unidade_id', $unidadeId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('valor_taxa_canal');

            return [
                'pix' => $totalTaxasPix,
                'credito' => $totalTaxasCredito,
                'debito' => $totalTaxasDebito,
                'vr_alimentacao' => $totalTaxasVrAlimentacao,
                'delivery' => $totalTaxasDelivery,
                'canais' => $totalTaxasDelivery,
            ];
        } catch (Exception $e) {
            Log::error("Erro ao calcular taxas", [
                'unidade_id' => $unidadeId,
                'error' => $e->getMessage()
            ]);

            return [
                'credito' => 0,
                'debito' => 0,
                'vr_alimentacao' => 0,
                'delivery' => 0,
                'canais' => 0,
            ];
        }
    }

    /**
     * Calcula taxa de um método de pagamento específico.
     */
    private function calculatePaymentMethodTax(
        int $unidadeId,
        array $methodIds,
        Carbon $startDate,
        Carbon $endDate
    ): float {
        if (empty($methodIds)) {
            return 0;
        }

        return FechamentoCaixa::where('unidade_id', $unidadeId)
            ->whereIn('metodo_pagamento_id', $methodIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('valor_taxa_metodo');
    }

    /**
     * Retorna IDs de métodos de pagamento com cache.
     */
    private function getCachedPaymentMethodIds(string $tipo): array
    {
        $cacheKey = "payment_method_ids_{$tipo}";

        return Cache::remember($cacheKey, self::CACHE_TTL_STATIC_DATA, function () use ($tipo) {
            try {
                return DB::table('default_payment_methods')
                    ->where('tipo', $tipo)
                    ->pluck('id')
                    ->toArray();
            } catch (Exception $e) {
                Log::error("Erro ao buscar IDs de métodos de pagamento", [
                    'tipo' => $tipo,
                    'error' => $e->getMessage()
                ]);
                return [];
            }
        });
    }

    /**
     * Calcula grupos de categorias.
     */
    private function calculateCategoryGroups(array $context): array
    {
        $categoriasRemovidas = ['Fornecedores'];
        $categoriasIgnoradasNaSoma = ['Fornecedores'];

        try {
            $grupos = Cache::remember('category_groups_with_categories', self::CACHE_TTL_STATIC_DATA, function () {
                return GrupoDeCategorias::with('categorias')->get();
            });

            $totalDespesasCategorias = 0;
            $totalDespesasCategoriasSemFolha = 0;

            $dadosGrupos = $grupos->map(function ($grupo) use (
                $context,
                $categoriasRemovidas,
                $categoriasIgnoradasNaSoma,
                &$totalDespesasCategorias,
                &$totalDespesasCategoriasSemFolha
            ) {
                $categoriasFormatadas = $grupo->categorias
                    ->reject(fn($categoria) => in_array($categoria->nome, $categoriasRemovidas))
                    ->map(function ($categoria) use (
                        $context,
                        $categoriasIgnoradasNaSoma,
                        &$totalDespesasCategorias,
                        &$totalDespesasCategoriasSemFolha
                    ) {
                        $valor = $this->calculateCategoryValue($categoria, $context);

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
        } catch (Exception $e) {
            Log::error("Erro ao calcular grupos de categorias", [
                'unidade_id' => $context['unidadeId'],
                'error' => $e->getMessage()
            ]);

            return [
                'dadosGrupos' => collect(),
                'totalDespesasCategorias' => 0,
                'totalDespesasCategoriasSemFolha' => 0,
            ];
        }
    }

    /**
     * Calcula o valor de uma categoria específica.
     */
    private function calculateCategoryValue($categoria, array $context): float
    {
        // Valores fixos pré-calculados
        $valoresFixos = [
            'Mercadoria Vendida' => $context['cmv'],
            'FGTS' => $context['totalFGTS'],
            'Folha de pagamento' => $context['totalSalarios'],
            'Royalties' => $context['totalRoyalties'],
            'Fundo de Propaganda' => $context['totalFundoPropaganda'],
            'Taxa de PIX' => $context['taxFees']['pix'],
            'Taxa de Crédito' => $context['taxFees']['credito'],
            'Taxa de Débito' => $context['taxFees']['debito'],
            'Plataformas de Delivery' => $context['taxFees']['canais'],
            'Taxas de Delivery' => $context['taxFees']['delivery'],
            'Voucher Alimentação' => $context['taxFees']['vr_alimentacao'],
            'Motoboy' => $context['totalMotoboy']
        ];

        // Retornar valor fixo se existir
        if (isset($valoresFixos[$categoria->nome])) {
            return $valoresFixos[$categoria->nome];
        }

        // Calcular valor da categoria através de contas a pagar
        try {
            return ContaAPagar::where('categoria_id', $categoria->id)
                ->where('unidade_id', $context['unidadeId'])
                ->whereIn('status', ['pago', 'pendente', 'agendada'])
                ->whereBetween('emitida_em', [
                    $context['startDateCarbon'],
                    $context['endDateCarbon']
                ])
                ->sum('valor');
        } catch (Exception $e) {
            Log::error("Erro ao calcular valor da categoria", [
                'categoria_id' => $categoria->id,
                'categoria_nome' => $categoria->nome,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Calcula métricas relacionadas a pedidos.
     */
    private function calculateOrderMetrics(
        int $unidadeId,
        Carbon $startDate,
        Carbon $endDate
    ): array {
        try {
            $pedidos = CanalVenda::where('unidade_id', $unidadeId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            $quantidadePedidos = $pedidos->sum('quantidade_vendas_feitas');
            $faturamentoTotal = $pedidos->sum('valor_total_vendas');
            $ticketMedio = $quantidadePedidos > 0
                ? round($faturamentoTotal / $quantidadePedidos, 2)
                : 0;

            return [
                'quantidade_pedidos' => $quantidadePedidos,
                'faturamento_total' => $faturamentoTotal,
                'ticket_medio' => $ticketMedio,
            ];
        } catch (Exception $e) {
            Log::error("Erro ao calcular métricas de pedidos", [
                'unidade_id' => $unidadeId,
                'error' => $e->getMessage()
            ]);

            return [
                'quantidade_pedidos' => 0,
                'faturamento_total' => 0,
                'ticket_medio' => 0,
            ];
        }
    }

    /**
     * Busca dados de faturamento da API externa.
     *
     * @throws Exception
     */
    private function fetchExternalFaturamentoData(
        int $unidadeId,
        Carbon $startDate,
        Carbon $endDate
    ): array {
        try {
            $response = Http::timeout(15)
                ->retry(3, 100)
                ->get(self::API_FATURAMENTO_URL, [
                    'unidade' => $unidadeId,
                    'inicio' => $startDate->format('Y-m-d'),
                    'final' => $endDate->format('Y-m-d'),
                ]);

            if (!$response->successful()) {
                throw new Exception(
                    "API de faturamento retornou status {$response->status()}: {$response->body()}"
                );
            }

            $data = $response->json();

            if (!is_array($data)) {
                throw new Exception("Resposta da API de faturamento inválida");
            }

            return $data;

        } catch (Exception $e) {
            Log::error("Erro ao buscar dados da API de faturamento", [
                'unidade_id' => $unidadeId,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Calcula os dados do gráfico (porcentagens) para o DRE.
     *
     * @param array $dreData Dados do DRE retornados por calculatePeriodData
     * @return array
     */
    public function calculateChartData(array $dreData): array
    {
        // USAR O FATURAMENTO NÃO AUDITADO COMO BASE PARA AS PORCENTAGENS
        // Se não existir, usa o total_caixas como fallback
        $baseParaPorcentagem = $dreData['faturamento_nao_auditado'] ?? $dreData['total_caixas'];

        // VALIDAÇÃO IMPORTANTE: evitar divisão por zero
        if ($baseParaPorcentagem <= 0) {
            return [
                'labels' => ['CMV', 'Custos Fixos', 'Impostos', 'Outras Despesas', 'Resultado do Período'],
                'data' => [0, 0, 0, 0, 0],
                'porcentagens' => ['0,00%', '0,00%', '0,00%', '0,00%', '0,00%']
            ];
        }

        // 1. CMV (Custo das Mercadorias Vendidas)
        $cmv = $dreData['cmv'];
        $valoresParaGrafico['CMV'] = $cmv;
        $porcentagensParaGrafico['CMV'] = ($cmv / $baseParaPorcentagem) * 100;

        // 2. Custos Fixos
        $grupoCustosFixos = $dreData['dados_grupos']->firstWhere('nome_grupo', 'Custos Fixos');
        $totalCustosFixosGrafico = ($grupoCustosFixos && isset($grupoCustosFixos['categorias']))
            ? $grupoCustosFixos['categorias']->sum('valor')
            : 0;

        $valoresParaGrafico['Custos Fixos'] = $totalCustosFixosGrafico;
        $porcentagensParaGrafico['Custos Fixos'] = ($totalCustosFixosGrafico / $baseParaPorcentagem) * 100;

        // 3. Impostos
        $grupoImpostos = $dreData['dados_grupos']->firstWhere('nome_grupo', 'Impostos');
        $totalImpostosGrafico = ($grupoImpostos && isset($grupoImpostos['categorias']))
            ? $grupoImpostos['categorias']->sum('valor')
            : 0;

        $valoresParaGrafico['Impostos'] = $totalImpostosGrafico;
        $porcentagensParaGrafico['Impostos'] = ($totalImpostosGrafico / $baseParaPorcentagem) * 100;

        // 4. Outras Despesas
        // Calcula o que sobra após CMV, Custos Fixos e Impostos
        $outrasDespesasGrafico = $dreData['total_despesas_categorias']
                            - $cmv
                            - $totalCustosFixosGrafico
                            - $totalImpostosGrafico;

        // Garante que não seja negativo
        $outrasDespesasGrafico = max(0, $outrasDespesasGrafico);

        $valoresParaGrafico['Outras Despesas'] = $outrasDespesasGrafico;
        $porcentagensParaGrafico['Outras Despesas'] = ($outrasDespesasGrafico / $baseParaPorcentagem) * 100;

        // 5. Resultado do Período (Lucro/Prejuízo)
        $resultadoPeriodo = $dreData['total_caixas'] - $dreData['total_despesas_categorias'];

        $valoresParaGrafico['Resultado do Período'] = $resultadoPeriodo;
        $porcentagensParaGrafico['Resultado do Período'] = ($resultadoPeriodo / $baseParaPorcentagem) * 100;

        // VALIDAÇÃO: A soma das porcentagens deve dar aproximadamente 100%
        $somaPorcentagens = array_sum($porcentagensParaGrafico);

        // Se houver diferença significativa, ajusta no resultado (arredondamento)
        if (abs($somaPorcentagens - 100) > 0.1) {
            // Ajusta a diferença no resultado do período (menos impactante visualmente)
            $diferenca = 100 - $somaPorcentagens;
            $porcentagensParaGrafico['Resultado do Período'] += $diferenca;
        }

        return [
            'labels' => array_keys($valoresParaGrafico),
            'data' => array_values($valoresParaGrafico),
            'porcentagens' => array_map(fn($valor) => number_format($valor, 2, ',', '.') . '%', array_values($porcentagensParaGrafico))
        ];
    }

    /**
     * Extrai apenas a porcentagem do CMV dos dados do gráfico.
     * Útil para APIs que precisam apenas desse valor específico.
     *
     * @param array $dreData Dados do DRE
     * @return float Porcentagem do CMV
     */
    public function extractCmvPercentage(array $dreData): float
    {
        $chartData = $this->calculateChartData($dreData);

        if (isset($chartData['porcentagens'][0])) {
            // Converte "45,32%" em 45.32
            return (float) str_replace(['%', ','], ['', '.'], $chartData['porcentagens'][0]);
        }

        return 0.0;
    }
}
