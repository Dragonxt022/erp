<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class AuthController extends Controller
{

     // Método que exibe a página de login
     public function showLoginForm()
     {
         // Verificar se o usuário já está autenticado
         if (Auth::check()) {
             // Redirecionar para o painel se o usuário já estiver autenticado
             return redirect()->route('painel');
         }

         // Caso o usuário não esteja autenticado, exibe a página de login
         return Inertia::render('Auth/Entrar');
     }
    /**
     * Handle the login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function login(Request $request)
    {

        // Valida o CPF como uma string e a senha como uma string
        $validated = $request->validate([
            'cpf' => ['required', 'string'], // Não há validação de CPF, apenas requer que seja uma string
            'password' => ['required', 'string'],
        ]);


        try {
            // Tentando buscar o usuário pelo CPF
            $user = User::where('cpf', $request->cpf)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return Inertia::render('Auth/Entrar', [
                    'status' => 'As credenciais fornecidas estão incorretas.',
                    'form' => $request->only('cpf', 'password'),
                    'errors' => [
                        'cpf' => ['As credenciais fornecidas estão incorretas.'],
                        'password' => ['A senha informada está incorreta.'],
                    ],
                ]);
            }

            // Autenticar o usuário
            Auth::login($user);

            // Regenerar a sessão para segurança
            $request->session()->regenerate();

            // Retornar para a página principal do dashboard
            return Inertia::render('Painel/Index');
        } catch (\Exception $e) {
            Log::error('Erro ao tentar autenticar usuário: ' . $e->getMessage());

            return Inertia::render('Auth/Entrar', [
                'status' => 'Ocorreu um erro inesperado. Tente novamente mais tarde.',
                'form' => $request->only('cpf', 'password'),
                'errors' => [
                    'cpf' => ['Erro ao processar o login.'],
                    'password' => ['Erro ao processar o login.'],
                ]
            ]);
        }
    }




    /**
     * Handle the logout request.
     *
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Retornar a resposta para o frontend, indicando que o logout foi realizado
        return Inertia::render('Auth/Entrar'); // Exemplo, retornar à página de login
    }
}
