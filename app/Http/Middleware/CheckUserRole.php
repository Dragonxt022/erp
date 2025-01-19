<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Verifica se o usuário está autenticado
        if (Auth::check()) {
            $user = Auth::user();

            // Redireciona com base no tipo de usuário
            if ($user->franqueadora) {
                return redirect()->route('franqueadora.painel');
            } elseif ($user->franqueado) {
                return redirect()->route('franqueado.painel');
            }
        }

        // Se não estiver autenticado, permite continuar na rota atual (como login)
        return $next($request);
    }
}
