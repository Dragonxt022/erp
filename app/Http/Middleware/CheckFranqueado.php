<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckFranqueado
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
        if (!$user || !$user->franqueado) {
            // Redireciona de volta para a página anterior com mensagem de erro
            return back()->with('error', 'Você não tem permissão para acessar esta área.');
        }

        return $next($request);
    }
}
