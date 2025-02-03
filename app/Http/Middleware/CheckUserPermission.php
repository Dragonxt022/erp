<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserPermission
{
    public function handle(Request $request, Closure $next, $permission)
    {
        if (!$request->user() || !$request->user()->permissions || !$request->user()->permissions->$permission) {
            abort(403, 'Acesso negado.');
        }

        return $next($request);
    }
}
