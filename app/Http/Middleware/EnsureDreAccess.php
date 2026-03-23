<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureDreAccess
{
    /**
     * Permite acesso ao DRE apenas para franqueado puro.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        $canAccessDre = $user
            && (bool) $user->franqueado
            && !(bool) $user->colaborador;

        if (!$canAccessDre) {
            abort(403, 'Você não tem permissão para acessar o DRE.');
        }

        return $next($request);
    }
}
