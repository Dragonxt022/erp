<?php

namespace App\Http\Controllers\Api;

use App\Actions\BrokerActions;
use App\Http\Controllers\Controller;
use App\Models\EventoProcessado;
use App\Services\EventBrokerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EventoProcessadoApiController extends Controller
{
    public function __construct(
        protected EventBrokerService $eventBrokerService,
        protected BrokerActions $brokerActions
    ) {
    }

    public function heartbeat()
    {
        return $this->eventBrokerService->heartbeat();
    }

    public function check(Request $request)
    {
        return $this->eventBrokerService->checkIncomingEvent($request);
    }

    public function store(Request $request)
    {
        if ($response = $this->validateServiceToken($request)) {
            return $response;
        }

        $metadata = [
            'delivery_id' => (string) ($request->input('delivery_id') ?: $request->header('delivery-id')),
            'event_type' => (string) ($request->input('event_type') ?: $request->header('event-type')),
        ];

        $validator = Validator::make($metadata, [
            'delivery_id' => 'required|uuid',
            'event_type' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dados inválidos.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $evento = EventoProcessado::firstOrCreate(
            ['delivery_id' => $metadata['delivery_id']],
            [
                'event_type' => $metadata['event_type'],
                'status' => false,
            ]
        );

        if ($evento->status === true) {
            return response()->json([
                'status' => 'success',
                'message' => 'Evento já havia sido processado anteriormente.',
                'data' => $evento,
            ]);
        }

        $payload = $this->extractPayload($request);

        if ($payload !== []) {
            $evento->update([
                'event_type' => $metadata['event_type'],
                'processando_em' => now(),
            ]);

            try {
                $processed = $this->brokerActions->dispatch(
                    $metadata['event_type'],
                    $payload,
                    $metadata['delivery_id']
                );

                $evento->update([
                    'status' => true,
                    'processado_em' => now(),
                ]);

                $this->eventBrokerService->confirmProcessoSafely($metadata['delivery_id']);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Evento recebido e processado com sucesso.',
                    'data' => [
                        'evento' => $evento->fresh(),
                        'processed' => $processed,
                    ],
                ], $evento->wasRecentlyCreated ? 201 : 200);
            } catch (\Throwable $exception) {
                Log::error('Falha ao processar evento recebido do broker.', [
                    'delivery_id' => $metadata['delivery_id'],
                    'event_type' => $metadata['event_type'],
                    'payload' => $payload,
                    'error' => $exception->getMessage(),
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Evento recebido, mas falhou no processamento.',
                    'error' => $exception->getMessage(),
                    'data' => $evento->fresh(),
                ], 500);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => $evento->wasRecentlyCreated
                ? 'Evento registrado com sucesso.'
                : 'Evento já existia na base.',
            'data' => $evento,
        ], $evento->wasRecentlyCreated ? 201 : 200);
    }

    public function show(string $deliveryId, Request $request)
    {
        if ($response = $this->validateServiceToken($request)) {
            return $response;
        }

        $evento = EventoProcessado::find($deliveryId);

        if (! $evento) {
            return response()->json([
                'status' => 'error',
                'message' => 'Evento não encontrado.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $evento,
        ]);
    }

    public function marcarProcessando(Request $request)
    {
        if ($response = $this->validateServiceToken($request)) {
            return $response;
        }

        $validator = Validator::make($request->all(), [
            'delivery_id' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dados inválidos.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $evento = EventoProcessado::find($request->delivery_id);

        if (! $evento) {
            return response()->json([
                'status' => 'error',
                'message' => 'Evento não encontrado.',
            ], 404);
        }

        $evento->update([
            'processando_em' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Evento marcado como processando.',
            'data' => $evento->fresh(),
        ]);
    }

    public function confirmar(Request $request)
    {
        if ($response = $this->validateServiceToken($request)) {
            return $response;
        }

        $validator = Validator::make($request->all(), [
            'delivery_id' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dados inválidos.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $evento = EventoProcessado::find($request->delivery_id);

        if (! $evento) {
            return response()->json([
                'status' => 'error',
                'message' => 'Evento não encontrado.',
            ], 404);
        }

        $evento->update([
            'status' => true,
            'processado_em' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Evento confirmado como processado.',
            'data' => $evento->fresh(),
        ]);
    }

    protected function validateServiceToken(Request $request)
    {
        $expectedToken = config('services.event_broker.service_token');

        if (! $expectedToken) {
            return null;
        }

        if ($request->header('service-token') !== $expectedToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token de serviço inválido.',
            ], 401);
        }

        return null;
    }

    protected function extractPayload(Request $request): array
    {
        $payload = $request->input('payload');

        if (is_array($payload)) {
            return $payload;
        }

        $event = $request->input('event');
        if (is_array($event) && is_array($event['payload'] ?? null)) {
            return $event['payload'];
        }

        $body = $request->except(['delivery_id', 'event_type']);

        return array_filter($body, static fn ($value) => $value !== null);
    }
}
