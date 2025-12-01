<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ShareUserPermissions
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user()->load('userDetails', 'unidade');
            $permissions = array_map('boolval', $user->getPermissions());
            $userData = array_merge($user->toArray(), ['permissions' => $permissions]);

            Inertia::share('auth', [
                'user' => $userData,
            ]);
        }

        return $next($request);
    }
}