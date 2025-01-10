<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Cargo;
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
            $user = Auth::user();

            // Buscar o cargo "AD" na tabela de cargos
            $cargoAdmin = Cargo::where('name', 'AD')->first();

            // Verificar se o cargo do usuário é "AD" (Administrador)
            if ($user->cargo_id === $cargoAdmin->id) {
                return redirect()->route('franqueadora.painel'); // Rota para administradores
            }

            return redirect()->route('franqueado.painel'); // Rota para usuários comuns
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

    // public function login(Request $request)
    // {
    //     // Valida o CPF e a senha
    //     $validated = $request->validate([
    //         'cpf' => ['required', 'string'],
    //         'password' => ['required', 'string'],
    //     ]);

    //     try {
    //         // Buscar o usuário pelo CPF
    //         $user = User::where('cpf', $request->cpf)->first();

    //         // Verificar se o usuário existe e se a senha está correta
    //         if (!$user || !Hash::check($request->password, $user->password)) {
    //             return Inertia::render('Auth/Entrar', [
    //                 'status' => 'As credenciais fornecidas estão incorretas.',
    //                 'form' => $request->only('cpf', 'password'),
    //                 'errors' => [
    //                     'cpf' => ['As credenciais fornecidas estão incorretas.'],
    //                     'password' => ['A senha informada está incorreta.'],
    //                 ],
    //             ]);
    //         }

    //         // Autenticar o usuário
    //         Auth::login($user);

    //         // Regenerar a sessão para segurança
    //         $request->session()->regenerate();

    //         // Buscar o cargo "AD" na tabela de cargos
    //         $cargoAdmin = Cargo::where('name', 'AD')->first();

    //         // Verificar o cargo do usuário e redirecionar adequadamente
    //         if ($user->cargo_id === $cargoAdmin->id) {
    //             return redirect()->route('franqueadora.painel'); // Rota para administradores
    //         }

    //         return redirect()->route('franqueado.painel'); // Rota para usuários comuns
    //     } catch (\Exception $e) {
    //         Log::error('Erro ao tentar autenticar usuário: ' . $e->getMessage());

    //         return Inertia::render('Auth/Entrar', [
    //             'status' => 'Ocorreu um erro inesperado. Tente novamente mais tarde.',
    //             'form' => $request->only('cpf', 'password'),
    //             'errors' => [
    //                 'cpf' => ['Erro ao processar o login.'],
    //                 'password' => ['Erro ao processar o login.'],
    //             ],
    //         ]);
    //     }
    // }

    public function login(Request $request)
    {
        // Valida o CPF e a senha
        $validated = $request->validate([
            'cpf' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        try {
            // Buscar o usuário pelo CPF
            $user = User::where('cpf', $request->cpf)->first();

            // Verificar se o usuário existe e se a senha está correta
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

            // Criar o token usando Sanctum
            $token = $user->createToken('TaiksuErp')->plainTextToken;

            // Buscar o cargo "AD" na tabela de cargos
            $cargoAdmin = Cargo::where('name', 'AD')->first();

            // Verificar o cargo do usuário e redirecionar adequadamente
            if ($user->cargo_id === $cargoAdmin->id) {
                // Retornar o token junto com a resposta
                return redirect()->route('franqueadora.painel')->with('token', $token);
            }

            return redirect()->route('franqueado.painel')->with('token', $token);
        } catch (\Exception $e) {
            Log::error('Erro ao tentar autenticar usuário: ' . $e->getMessage());

            return Inertia::render('Auth/Entrar', [
                'status' => 'Ocorreu um erro inesperado. Tente novamente mais tarde.',
                'form' => $request->only('cpf', 'password'),
                'errors' => [
                    'cpf' => ['Erro ao processar o login.'],
                    'password' => ['Erro ao processar o login.'],
                ],
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

    // API
    // Função para enviar os dados do perfil do usuário autenticado
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
        $user = $user->load('permissions', 'userDetails', 'unidade', 'cargo');

        // Adiciona manualmente o nome do cargo, caso o acessor não esteja funcionando
        $user->cargo_name = $user->cargo ? $user->cargo->name : null;

        // Retorna os dados do usuário com os relacionamentos
        return response()->json([
            'status' => 'success',
            'data' => $user,
        ]);
    }
}
