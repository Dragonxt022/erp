<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ApiAuthController extends Controller
{
    /**
     * Login via PIN (API)
     */
    public function loginComPin(Request $request)
    {
        $request->validate([
            'pin' => 'required|digits:4',
        ]);

        $user = User::where('pin', $request->pin)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'PIN inválido.',
            ], 401);
        }

        $userPermission = UserPermission::where('user_id', $user->id)->first();

        if (!$userPermission || !$userPermission->controle_saida_estoque) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acesso negado ao controle de estoque.',
            ], 403);
        }

        Auth::login($user);

        return response()->json([
            'status' => 'success',
            'message' => 'Autenticado com sucesso',
            'user' => $user->only(['id', 'name', 'email']),
        ]);
    }

    /**
     * Login via CPF e senha (API)
     */
    public function login(Request $request)
    {
        $request->validate([
            'cpf' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginInput = $request->cpf;
        if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $loginInput)->first();
        } else {
            $cpfNumeros = preg_replace('/\D/', '', $loginInput);
            if (strlen($cpfNumeros) === 11) {
                $loginInput = substr($cpfNumeros, 0, 3) . '.' .
                              substr($cpfNumeros, 3, 3) . '.' .
                              substr($cpfNumeros, 6, 3) . '-' .
                              substr($cpfNumeros, 9, 2);
            }
            $user = User::where('cpf', $loginInput)->first();
        }

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Credenciais inválidas',
            ], 401);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'status' => 'success',
            'user' => $user->only(['id', 'name', 'email']),
        ]);
    }

    /**
     * Logout API
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout realizado',
        ]);
    }

    /**
     * Get authenticated user profile (API)
     */
    public function getProfile()
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuário não autenticado',
            ], 401);
        }

        $user = Auth::user()->load('userDetails', 'unidade', 'cargo');

        $permissions = array_map('boolval', $user->getPermissions());

        return response()->json([
            'status' => 'success',
            'data' => array_merge($user->toArray(), ['permissions' => $permissions]),
        ]);
    }
}
