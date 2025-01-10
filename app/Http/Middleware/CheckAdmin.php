<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAdmin
{
    public function handle(Request $request, Closure $next)
    {
        // Verifica se o usuário está autenticado e possui o cargo 'AD'
        if (Auth::check() && Auth::user()->cargo->name === 'AD') {
            return $next($request);
        }

        // Redireciona para a página de login ou erro
        return redirect()->route('entrar')->with('error', 'Acesso restrito a administradores.');
    }
}
