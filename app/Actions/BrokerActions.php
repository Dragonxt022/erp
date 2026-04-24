<?php

namespace App\Actions;

use App\Services\BrokerSalmaoHistoricoHandlerService;
use InvalidArgumentException;

class BrokerActions
{
    // Mesmo padrão do client/actions.js: evento recebido -> service/method responsável.
    protected array $eventHandlers = [
        '1' => [BrokerSalmaoHistoricoHandlerService::class, 'handle'],
    ];

    public function dispatch(string $eventType, array $payload, ?string $deliveryId = null): array
    {
        $handler = $this->eventHandlers[(string) $eventType] ?? null;

        if (! $handler) {
            throw new InvalidArgumentException("Evento {$eventType} não possui handler configurado.");
        }

        [$serviceClass, $method] = $handler;
        $service = app($serviceClass);

        if (! method_exists($service, $method)) {
            throw new InvalidArgumentException("Handler inválido para o evento {$eventType}: {$serviceClass}::{$method}");
        }

        return $service->{$method}($payload, $deliveryId, (string) $eventType);
    }

    public function handlers(): array
    {
        return $this->eventHandlers;
    }
}
