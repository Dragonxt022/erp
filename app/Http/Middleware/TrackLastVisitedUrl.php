<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class TrackLastVisitedUrl
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Só rastreamos se for uma requisição GET bem-sucedida, com usuário autenticado
        // e se não for uma requisição puramente de API (a menos que seja Inertia)
        if (
            $request->isMethod('get') &&
            Auth::check() &&
            $response->getStatusCode() === 200 &&
            !$request->expectsJson() || $request->header('X-Inertia')
        ) {
            $url = $request->fullUrl();

            // Evitar URLs de login, logout, callback ou arquivos estáticos
            $excludedPatterns = [
                '/login',
                '/logout',
                '/callback',
                '/resetar-password',
                '/events.heartbeat',
                '/api/', // Evitar rotas puras de API se não forem Inertia (embora o middleware web geralmente não as pegue se estiverem no grupo api)
            ];

            $shouldTrack = true;
            foreach ($excludedPatterns as $pattern) {
                if (str_contains($url, $pattern)) {
                    $shouldTrack = false;
                    break;
                }
            }

            if ($shouldTrack) {
                Auth::user()->update(['last_visited_url' => $url]);
            }
        }

        return $response;
    }
}
