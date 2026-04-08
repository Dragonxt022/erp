<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EventoProcessado;
use App\Services\EventBrokerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventoProcessadoApiController extends Controller
{
    public function __construct(
        protected EventBrokerService $eventBrokerService
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

        $validator = Validator::make($request->all(), [
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
            ['delivery_id' => $request->delivery_id],
            [
                'event_type' => $request->event_type,
                'status' => false,
            ]
        );

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
}
