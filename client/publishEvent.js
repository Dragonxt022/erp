const Config = require('../config/Config');

async function publishEvent({ eventId, payload, userId, priority }) {
    try {
        if (!Config.integrations.eventBroker.url) {
            return { skipped: true, reason: 'missing_event_broker_url' };
        }

        const headers = {
            'Content-Type': 'application/json',
            'user': String(userId ?? 'system'),
            'event': eventId,
            'priority': priority || 'normal',
        };

        if (Config.integrations.eventBroker.serviceToken) {
            headers['service-token'] = Config.integrations.eventBroker.serviceToken;
        }

        const response = await fetch(Config.integrations.eventBroker.url, {
            method: 'POST',
            headers,
            body: JSON.stringify(payload),
        });

        // Lança erro se a requisição falhar
        if (!response.ok) {
            const responseText = await response.text();
            throw new Error(`Failed to publish event: ${response.status} ${response.statusText} - ${responseText}`);
        }

        return { ok: true };
    } catch (error) {
        console.error('[publishEvent] Error publishing event: ', error);
        throw error;
    }
};

module.exports = publishEvent;
