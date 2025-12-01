<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserPermission;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission = null)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('entrar.painel')->with('error', 'Você precisa estar autenticado.');
        }

        // Se nenhuma permissão for especificada, prossegue (evita erro em rotas sem restrição)
        if ($permission === null) {
            return $next($request);
        }

        $userPermission = UserPermission::where('user_id', $user->id)->first();

        // Verifica se o usuário tem a permissão
        if (!$userPermission || !$userPermission->$permission) {
            // Se já estiver em /franqueado/painel, não redireciona para evitar loop
            if ($request->routeIs('franqueado.painel')) {
                return $next($request); // Permite acesso à rota de fallback
            }
            return redirect()->route('franqueado.painel')->with('error', 'Você não tem permissão para acessar esta área.');
            // Alternativa: return response()->json(['error' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}