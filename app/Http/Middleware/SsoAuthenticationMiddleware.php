<?php

namespace App\Http\Middleware;

use App\Services\SsoService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SsoAuthenticationMiddleware
{
    protected $ssoService;

    public function __construct(SsoService $ssoService)
    {
        $this->ssoService = $ssoService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. Verifica se tem Bearer Token
        $token = $request->bearerToken();

        if (!$token) {
            // Tenta pegar da query string 'token' ou da sessão se for web tradicional
            // Mas o foco aqui é a API que valida api/user/me
            $token = $request->query('token') ?? session('rh_token');
        }

        if (!$token) {
            return response()->json(['message' => 'Unauthenticated (No Token)'], 401);
        }

        // 2. Valida o token no SSO
        $ssoUser = $this->ssoService->validateToken($token);

        if (!$ssoUser) {
            return response()->json(['message' => 'Unauthenticated (Invalid Token)'], 401);
        }

        // 3. Sincroniza Unidade e Usuário Local
        try {
            $unidadeData = $ssoUser['unidade'] ?? null;
            $unidadeId   = $unidadeData['id'] ?? null;

            if ($unidadeData) {
                $this->ssoService->syncUnidadeDetails($unidadeData);
            }

            $user = $this->ssoService->syncUser($ssoUser, $unidadeId);

            // 4. Autentica no Laravel
            Auth::login($user);

            // Armazena o token na requisição/sessão para uso posterior (ex: repassar para outras APIs)
            $request->attributes->set('sso_token', $token);
            
        } catch (\Exception $e) {
            Log::error('Erro ao sincronizar usuário SSO durante autenticação', [
                'exception' => $e->getMessage(),
                'sso_user' => $ssoUser
            ]);
            return response()->json(['message' => 'Internal Server Error during Auth Sync'], 500);
        }

        return $next($request);
    }
}
