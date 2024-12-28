<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // Método que retorna todos os usuários e as empresas relacionadas
    public function index()
    {
        // Carrega usuários junto com as informações das unidades (empresas)
        $users = User::with('unidade')->get();

        // Formata a resposta para incluir as unidades relacionadas
        $data = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'cpf' => $user->cpf,
                'unidade_id' => $user->unidade_id,
                'cargo_id' => $user->cargo_id,
                'pin' => $user->pin,
                'profile_photo_url' => $user->profile_photo_url,
                'unidade' => $user->unidade ? [
                    'id' => $user->unidade->id,
                    'cep' => $user->unidade->cep,
                    'rua' => $user->unidade->rua,
                    'numero' => $user->unidade->numero,
                    'cidade' => $user->unidade->cidade,
                    'bairro' => $user->unidade->bairro,
                    'cnpj' => $user->unidade->cnpj,
                ] : null,
            ];
        });

        return response()->json($data);
    }
}
