<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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


        if (!$user || (!$user->franqueado && !$user->colaborador && !$user->franqueadora)) {
            Log::error("Acesso negado ao painel do franqueado para o usuário: " . ($user ? $user->email : 'não autenticado'));
            return redirect('/')->with('error', 'Você não tem permissão para acessar esta área.');
        }

        return $next($request);
    }
}
