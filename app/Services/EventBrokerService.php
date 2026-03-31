<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class EventBrokerService
{
    public function publishEvent(string|int $eventId, array $payload, string|int|null $userId = null, ?string $priority = 'normal'): array
    {
        $url = config('services.event_broker.url');

        if (! $url) {
            return [
                'skipped' => true,
                'reason' => 'missing_event_broker_url',
            ];
        }

        $headers = [
            'Content-Type' => 'application/json',
            'user' => (string) ($userId ?? 'system'),
            'event' => (string) $eventId,
            'priority' => $priority ?: 'normal',
        ];

        if (config('services.event_broker.service_token')) {
            $headers['service-token'] = config('services.event_broker.service_token');
        }

        $response = Http::withHeaders($headers)->post($url, $payload);

        $response->throw();

        return [
            'ok' => true,
            'status' => $response->status(),
            'body' => $response->json() ?? $response->body(),
        ];
    }
}
