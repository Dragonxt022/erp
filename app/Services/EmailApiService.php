<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmailApiService
{
    protected $apiUrl = 'https://email.taiksu.com.br/api/email/send';
    protected $serviceId = 1;

    /**
     * Send an email via the external API.
     *
     * @param string $to
     * @param string $subject
     * @param string $body
     * @param array $attachments
     * @return bool
     */
    public function send(string $to, string $subject, string $body, array $attachments = [])
    {
        try {
            $token = session('rh_token') ?? request()->bearerToken();

            $payload = [
                'to' => $to,
                'subject' => $subject,
                'body' => $body,
                'attachments' => $attachments,
                'emailServiceId' => $this->serviceId,
            ];

            $response = Http::withToken($token)
                ->withHeaders([
                    'Accept' => 'application/json',
                ])
                ->post($this->apiUrl, $payload);

            if ($response->successful()) {
                return true;
            }

            Log::error('Erro ao enviar e-mail via API externa', [
                'status' => $response->status(),
                'body' => $response->body(),
                'payload' => $payload
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Exceção ao enviar e-mail via API externa: ' . $e->getMessage());
            return false;
        }
    }
}
