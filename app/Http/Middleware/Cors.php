<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Cors
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowedOrigins = [
            'https://login.taiksu.com.br',
            'https://beta.taiksu.com.br',
        ];

        $origin = $request->headers->get('Origin');

        // Resposta para preflight OPTIONS
        if ($request->getMethod() === "OPTIONS") {
            $headers = [
                'Access-Control-Allow-Origin' => in_array($origin, $allowedOrigins) ? $origin : '',
                'Access-Control-Allow-Credentials' => 'true',
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, X-XSRF-TOKEN',
            ];
            return response('', 204)->withHeaders($headers);
        }

        $response = $next($request);

        if ($origin && in_array($origin, $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-XSRF-TOKEN');
        }

        return $response;
    }
}
