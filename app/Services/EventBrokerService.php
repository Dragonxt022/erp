<?php

namespace App\Services;

use App\Models\EventoProcessado;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EventBrokerService
{
    public function heartbeat(): JsonResponse
    {
        try {
            DB::connection()->getPdo();

            return response()->json([
                'success' => true,
                'message' => 'Service is online',
                'time' => now()->toIso8601String(),
            ]);
        } catch (\Throwable $exception) {
            Log::error('Falha no heartbeat do broker.', [
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Service is offline',
                'error' => $exception->getMessage(),
                'time' => now()->toIso8601String(),
            ], 500);
        }
    }

    public function publishEvent(string|int $eventId, array $payload, string|int|null $userId = null, ?string $priority = 'urgent'): array
    {
        $url = config('services.event_broker.url');
        $priority = $this->normalizePriority($priority);

        if (! $url) {
            Log::warning('Publicação no broker ignorada por falta de URL.', [
                'event_id' => $eventId,
                'user_id' => $userId,
            ]);

            return [
                'skipped' => true,
                'reason' => 'missing_event_broker_url',
            ];
        }

        $headers = [
            'Content-Type' => 'application/json',
            'user' => (string) ($userId ?? 'system'),
            'event' => (string) $eventId,
            'priority' => $priority,
        ];

        if (config('services.event_broker.service_token')) {
            $headers['service-token'] = config('services.event_broker.service_token');
        }

        Log::info('Enviando evento para o broker.', [
            'url' => $url,
            'event_id' => (string) $eventId,
            'user_id' => (string) ($userId ?? 'system'),
            'priority' => $priority,
            'payload' => $payload,
        ]);

        $response = Http::withHeaders($headers)->post($url, $payload);

        $response->throw();

        Log::info('Evento enviado para o broker com sucesso.', [
            'url' => $url,
            'event_id' => (string) $eventId,
            'status' => $response->status(),
            'body' => $response->json() ?? $response->body(),
        ]);

        return [
            'ok' => true,
            'status' => $response->status(),
            'body' => $response->json() ?? $response->body(),
        ];
    }

    public function publishEventSafely(string|int $eventId, array $payload, string|int|null $userId = null, ?string $priority = 'urgent'): array
    {
        try {
            return $this->publishEvent($eventId, $payload, $userId, $priority);
        } catch (\Throwable $exception) {
            Log::error('Falha ao publicar evento no broker.', [
                'event_id' => $eventId,
                'user_id' => $userId,
                'payload' => $payload,
                'error' => $exception->getMessage(),
            ]);

            return [
                'ok' => false,
                'error' => $exception->getMessage(),
            ];
        }
    }

    public function checkIncomingEvent(Request $request): JsonResponse
    {
        $deliveryId = (string) $request->header('delivery-id', '');
        $eventType = (string) $request->header('event-type', '');

        if ($deliveryId === '' || $eventType === '') {
            Log::warning('Header delivery-id ou event-type não informado.', [
                'delivery_id' => $deliveryId,
                'event_type' => $eventType,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Header delivery-id ou event-type não informado',
            ], 400);
        }

        $event = EventoProcessado::query()->find($deliveryId);

        if (! $event) {
            $event = EventoProcessado::query()->create([
                'delivery_id' => $deliveryId,
                'event_type' => $eventType,
                'status' => false,
            ]);

            Log::info('Evento novo liberado para processamento.', [
                'delivery_id' => $deliveryId,
                'event_type' => $eventType,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Evento liberado para processamento.',
                'data' => $event,
            ], 200);
        }

        if ($event->status === true) {
            $this->confirmProcessoSafely($deliveryId);

            return response()->json([
                'status' => 'error',
                'message' => 'Evento já processado',
            ], 409);
        }

        $this->marcaProcessandoSafely($deliveryId);

        return response()->json([
            'status' => 'error',
            'message' => 'Evento em processamento',
        ], 409);
    }

    public function marcaProcessando(string $deliveryId): array
    {
        $brokerBaseUrl = $this->getBrokerBaseUrl();

        Http::withHeaders($this->serviceHeaders())->put($brokerBaseUrl . '/api/delivery/processando', [
            'delivery_id' => $deliveryId,
        ])->throw();

        EventoProcessado::query()
            ->where('delivery_id', $deliveryId)
            ->update([
                'processando_em' => now(),
            ]);

        Log::info('Evento marcado como processando no broker.', [
            'delivery_id' => $deliveryId,
        ]);

        return ['ok' => true];
    }

    public function marcaProcessandoSafely(string $deliveryId): array
    {
        try {
            return $this->marcaProcessando($deliveryId);
        } catch (\Throwable $exception) {
            Log::error('Erro ao marcar evento como processando no broker.', [
                'delivery_id' => $deliveryId,
                'error' => $exception->getMessage(),
            ]);

            return [
                'ok' => false,
                'error' => $exception->getMessage(),
            ];
        }
    }

    public function confirmaProcesso(string $deliveryId): array
    {
        $brokerBaseUrl = $this->getBrokerBaseUrl();

        Http::withHeaders($this->serviceHeaders())->put($brokerBaseUrl . '/api/delivery/ok', [
            'delivery_id' => $deliveryId,
        ])->throw();

        EventoProcessado::query()
            ->where('delivery_id', $deliveryId)
            ->update([
                'status' => true,
                'processado_em' => now(),
            ]);

        Log::info('Evento confirmado como processado no broker.', [
            'delivery_id' => $deliveryId,
        ]);

        return ['ok' => true];
    }

    public function confirmProcessoSafely(string $deliveryId): array
    {
        try {
            return $this->confirmaProcesso($deliveryId);
        } catch (\Throwable $exception) {
            Log::error('Erro ao confirmar processamento no broker.', [
                'delivery_id' => $deliveryId,
                'error' => $exception->getMessage(),
            ]);

            return [
                'ok' => false,
                'error' => $exception->getMessage(),
            ];
        }
    }

    protected function normalizePriority(?string $priority): string
    {
        $priority = strtolower(trim((string) ($priority ?: 'urgent')));
        $allowed = ['low', 'medium', 'high', 'urgent'];

        if (! in_array($priority, $allowed, true)) {
            Log::warning('Prioridade inválida informada para o broker. Usando medium.', [
                'received_priority' => $priority,
            ]);

            return 'medium';
        }

        return $priority;
    }

    protected function getBrokerBaseUrl(): string
    {
        $url = rtrim((string) config('services.event_broker.url'), '/');

        return (string) preg_replace('#/api/event/?$#', '', $url);
    }

    protected function serviceHeaders(): array
    {
        $headers = [
            'Content-Type' => 'application/json',
        ];

        if (config('services.event_broker.service_token')) {
            $headers['service-token'] = (string) config('services.event_broker.service_token');
        }

        return $headers;
    }
}
