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

    public function paginLogin()
    {
        // Verifica se o usuário está autenticado
        if (Auth::check()) {
            // Obtém o usuário autenticado
            $user = Auth::user();

            // Verifica o tipo de usuário e redireciona para o painel correspondente
            if ($user->franqueadora) {
                return redirect()->route('franqueadora.painel'); // Painel da franqueadora
            } elseif ($user->franqueado) {
                return redirect()->route('franqueado.painel'); // Painel do franqueado
            }
        }

        // Se não estiver autenticado, exibe a página de login
        return Inertia::render('Auth/Entrar');
    }


    public function login(Request $request)
    {

        // Valida o CPF e a senha
        $request->validate([
            'cpf' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Buscar o usuário pelo CPF
        $user = User::where('cpf', $request->cpf)->first();

        // Verificar se o usuário existe e se a senha está correta
        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'cpf' => 'As credenciais fornecidas estão incorretas.',
                'password' => 'A senha informada está incorreta.',
            ])->withInput($request->only('cpf'));
        }

        // Autenticar o usuário
        Auth::login($user);

        // Regenerar a sessão para segurança
        $request->session()->regenerate();

        // Redirecionar com base no tipo de usuário
        if ($user->franqueadora) {
            return redirect()->route('franqueadora.painel');
        } elseif ($user->franqueado) {
            return redirect()->route('franqueado.painel');
        }

        // Caso não seja franqueadora nem franqueado, desconectar e exibir erro
        Auth::logout();
        return back()->withErrors([
            'general' => 'Não foi possível determinar o acesso do usuário. Entre em contato com o suporte.',
        ]);
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
        return redirect()->route('pagina.login');
    }

    public function getProfile()
    {
        $token = request()->bearerToken();
        Log::info('Token recebido: ' . $token);  // Verifica o token recebido

        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuário não autenticado.',
            ], 401);
        }

        $user = Auth::user();

        // Carrega os relacionamentos necessários
        $user = $user->load('userDetails', 'unidade');



        // Retorna os dados do usuário com os relacionamentos
        return response()->json([
            'status' => 'success',
            'data' => $user,
        ]);
    }
}
