<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckFranqueadora
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Verifica se o usuário é da franqueadora
        if (!$user || !$user->franqueadora) {
            Log::error("Acesso negado ao painel da franqueadora para o usuário: " . ($user ? $user->email : 'não autenticado'));
            return redirect('/')->with('error', 'Você não tem permissão para acessar esta área.');
        }

        return $next($request);
    }
}
