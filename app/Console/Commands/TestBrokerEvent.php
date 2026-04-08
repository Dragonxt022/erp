<?php

namespace App\Console\Commands;

use App\Services\EventBrokerService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class TestBrokerEvent extends Command
{
    protected $signature = 'broker:test
        {eventId=101 : ID do evento a publicar}
        {unidadeId=8 : ID da unidade a enviar no payload}
        {--userId=broker-test : ID do usuário enviado no header}
        {--priority=medium : Prioridade do evento}
        {--payload= : Payload JSON completo para substituir o exemplo padrão}';

    protected $description = 'Testa a publicação de eventos no broker com log detalhado';

    public function __construct(
        protected EventBrokerService $eventBrokerService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $eventId = (string) $this->argument('eventId');
        $unidadeId = (string) $this->argument('unidadeId');
        $userId = (string) $this->option('userId');
        $priority = $this->normalizePriority((string) $this->option('priority'));
        $traceId = (string) Str::uuid();

        $payload = $this->resolvePayload($unidadeId);

        $this->writeTestLog('INFO', 'Iniciando teste do broker.', [
            'trace_id' => $traceId,
            'event_id' => $eventId,
            'unidade_id' => $unidadeId,
            'user_id' => $userId,
            'priority' => $priority,
            'broker_url' => config('services.event_broker.url'),
            'payload' => $payload,
        ]);

        $this->line('----------------------------------------');
        $this->info('Teste do Broker');
        $this->line('Trace ID: ' . $traceId);
        $this->line('Broker URL: ' . (config('services.event_broker.url') ?: '[não configurada]'));
        $this->line('Event ID: ' . $eventId);
        $this->line('User ID: ' . $userId);
        $this->line('Priority: ' . $priority);
        $this->line('Payload:');
        $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        try {
            $result = $this->eventBrokerService->publishEvent($eventId, $payload, $userId, $priority);

            $this->writeTestLog('INFO', 'Teste do broker finalizado com sucesso.', [
                'trace_id' => $traceId,
                'result' => $result,
            ]);

            $this->line('----------------------------------------');
            $this->info('Broker respondeu com sucesso.');
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->line('Log detalhado: storage/logs/broker_test.log');

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $this->writeTestLog('ERROR', 'Teste do broker falhou.', [
                'trace_id' => $traceId,
                'error' => $exception->getMessage(),
            ]);

            $this->line('----------------------------------------');
            $this->error('Falha ao enviar evento para o broker.');
            $this->error($exception->getMessage());
            $this->line('Log detalhado: storage/logs/broker_test.log');

            return self::FAILURE;
        }
    }

    protected function resolvePayload(string $unidadeId): array
    {
        $payloadOption = $this->option('payload');

        if (! empty($payloadOption)) {
            $decoded = json_decode((string) $payloadOption, true);

            if (! is_array($decoded)) {
                throw new \InvalidArgumentException('O --payload precisa ser um JSON válido.');
            }

            if (isset($decoded['produtos'])) {
                return $this->normalizeGroupedPayload($decoded, $unidadeId);
            }

            if (isset($decoded[0]['produtos'])) {
                return $this->normalizeGroupedPayload($decoded[0], $unidadeId);
            }

            return [
                'unidade_id' => $unidadeId,
                'produtos' => $this->normalizeProdutos($decoded),
            ];
        }

        return [
            'unidade_id' => $unidadeId,
            'produtos' => [
                [
                    'insumo_id' => '39',
                    'quantidade' => '1.000',
                ],
                [
                    'insumo_id' => '99',
                    'quantidade' => '3.549',
                ],
                [
                    'insumo_id' => '5',
                    'quantidade' => '3.000',
                ],
            ],
        ];
    }

    protected function normalizeGroupedPayload(array $grupo, string $unidadeId): array
    {
        return [
            'unidade_id' => (string) ($grupo['unidade_id'] ?? $unidadeId),
            'produtos' => $this->normalizeProdutos($grupo['produtos'] ?? []),
        ];
    }

    protected function normalizeProdutos(array $produtos): array
    {
        return array_map(function (array $item) {
            $quantidade = $item['quantidade'] ?? $item['quantiadade'] ?? null;

            return [
                'insumo_id' => (string) ($item['insumo_id'] ?? ''),
                'quantidade' => number_format((float) $quantidade, 3, '.', ''),
            ];
        }, $produtos);
    }

    protected function writeTestLog(string $level, string $message, array $context = []): void
    {
        $line = sprintf(
            "[%s] %s: %s %s\n",
            now()->format('Y-m-d H:i:s'),
            strtoupper($level),
            $message,
            json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        file_put_contents(storage_path('logs/broker_test.log'), $line, FILE_APPEND);
    }

    protected function normalizePriority(string $priority): string
    {
        $priority = strtolower(trim($priority ?: 'medium'));
        $allowed = ['low', 'medium', 'high', 'urgent'];

        if (! in_array($priority, $allowed, true)) {
            throw new \InvalidArgumentException(
                'Prioridade inválida. Use apenas: low, medium, high ou urgent.'
            );
        }

        return $priority;
    }
}
