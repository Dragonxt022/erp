<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class AuthController extends Controller
{

    // Redirecionador da pagina de Login
    public function paginaLoginEstoque()
    {
        // Se não estiver autenticado, exibe a página de login
        return Inertia::render('Auth/LoginEstoque');
    }

    public function loginComPin(Request $request)
    {
        // Valida o PIN recebido
        $dadosValidados = $request->validate([
            'pin' => 'required|digits:4', // O PIN deve ter 4 dígitos
        ]);

        // Busca o usuário com o PIN fornecido
        $usuario = User::where('pin', $dadosValidados['pin'])->first();

        if (!$usuario) {
            // Retorna o erro como uma propriedade no Inertia
            return Inertia::render('Auth/LoginEstoque', [
                'errorMessage' => 'PIN inválido.',
            ]);
        }

        // Busca as permissões do usuário na tabela UserPermission
        $userPermission = UserPermission::where('user_id', $usuario->id)->first();

        // Verifica se o usuário tem permissão para acessar o controle de estoque
        if (!$userPermission || !$userPermission->controle_saida_estoque) {
            // Retorna o erro como uma propriedade no Inertia
            return Inertia::render('Auth/LoginEstoque', [
                'errorMessage' => 'Acesso negado ao controle de estoque.',
            ]);
        }

        // Autentica o usuário manualmente
        Auth::login($usuario);

        // Retorna o redirecionamento usando Inertia
        return Inertia::location(route('franqueado.controleEstoque'));
    }

    // Redirecionador da pagina de Login
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
        return redirect('https://login.taiksu.com.br/');
    }

    public function handleCallback(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            return redirect('https://login.taiksu.com.br/');
        }

        // Chamada à API protegida do IdP
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->get('https://login.taiksu.com.br/api/user/me');

        if ($response->failed()) {
            // Token inválido, expirado ou não autorizado
            return redirect('https://login.taiksu.com.br/');
        }

        $userData = $response->json();

        // Busca o usuário existente pelo email
        $user = \App\Models\User::where('email', $userData['email'])->first();

        if (!$user) {
            // Se o usuário não existe, redireciona para login do IDP
            return redirect('https://login.taiksu.com.br/');
        }

        // Autentica o usuário no Laravel
        Auth::login($user);

        // Redireciona para o painel conforme o tipo
        if ($user->franqueadora) {
            return redirect()->route('franqueadora.painel');
        } elseif ($user->franqueado) {
            return redirect()->route('franqueado.painel');
        }

        // Redirecionamento padrão
        return redirect()->route('pagina.login');
    }



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
            return back()->withErrors([
                'cpf' => 'As credenciais fornecidas estão incorretas.',
                'password' => 'A senha informada está incorreta.',
            ])->withInput($request->only('cpf'));
        }

        Auth::login($user);
        $request->session()->regenerate();

        if ($user->franqueadora) {
            return redirect()->route('franqueadora.painel');
        } elseif ($user->franqueado) {
            return redirect()->route('franqueado.painel');
        }

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

        // Redireciona diretamente para o login do IDP
        return redirect('https://login.taiksu.com.br/');
    }


    public function getProfile()
    {
        $token = request()->bearerToken();
        Log::info('Token recebido: ' . $token); // Verifica o token recebido

        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuário não autenticado.',
            ], 401);
        }

        $user = Auth::user();

        // Carrega os relacionamentos necessários, incluindo 'cargo'
        $user = $user->load('userDetails', 'unidade', 'cargo');

        // Obtém as permissões do usuário e converte 0/1 para booleanos
        $permissions = array_map('boolval', $user->getPermissions());

        // Retorna os dados do usuário com os relacionamentos e permissões
        return response()->json([
            'status' => 'success',
            'data' => array_merge($user->toArray(), ['permissions' => $permissions]),
        ]);
    }
}
