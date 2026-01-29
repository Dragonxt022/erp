<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
// use App\Jobs\SyncUsuariosDaUnidade;
// use App\Services\UserSyncService;

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

        // Verifica se o usuário tem permissão para acessar o controle de estoque
        // (Simplificado: Se tem PIN, tem acesso, ou checar se é ativo)
        if ($usuario->status !== 'ativo') {
             return Inertia::render('Auth/LoginEstoque', [
                'errorMessage' => 'Usuário inativo.',
            ]);
        }

        // Permissão concedida implicitamente conforme solicitação de desbloqueio total

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

            // Redireciona para a última URL visitada se disponível
            return $this->redirectUser($user);
        }

        // Se não estiver autenticado, exibe a página de login
        return redirect('https://login.taiksu.com.br/');
    }

    public function handleCallback(Request $request, \App\Services\SsoService $ssoService)
    {
        $token = $request->query('token');

        if (!$token) {
            return redirect('https://login.taiksu.com.br/');
        }

        Session::put('rh_token', $token);

        // Valida o token e obtém dados do usuário via SsoService
        $ssoUser = $ssoService->validateToken($token);

        if (!$ssoUser) {
            return redirect('https://login.taiksu.com.br/');
        }

        $userData = $ssoUser;
        $unidadeData = $userData['unidade'] ?? null;
        $unidadeId   = $unidadeData['id'] ?? null;
        $grupoNome   = $userData['grupo_nome'] ?? $userData['grupo'] ?? null;

        // 🔎 Cria/atualiza unidade
        if ($unidadeData) {
            $ssoService->syncUnidadeDetails($unidadeData);
        }

        // Cria/atualiza usuário e permissões
        $user = $ssoService->syncUser($userData, $unidadeId);

        // 🔎 Verifica se já tem sessão e se é outro usuário
        if (Auth::check() && Auth::id() !== $user->id) {
            Auth::logout(); // encerra sessão antiga
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        // ✅ Autentica sempre com os dados mais recentes
        Auth::login($user, true);

        // Redireciona conforme grupo ou última URL
        return $this->redirectUser($user);
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

        Auth::login($user, true);
        $request->session()->regenerate();

        return $this->redirectUser($user);
    }



    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Informa ao Inertia que é para redirecionar o browser para uma URL externa
        return Inertia::location('https://login.taiksu.com.br/');
    }


    public function getProfile()
    {
        $token = request()->bearerToken();
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuário não autenticado.',
            ], 401);
        }

        $user = Auth::user();

        // Carrega os relacionamentos necessários, incluindo 'cargo'
        $user = $user->load('userDetails', 'unidade', 'cargo');

        // Verifica se é franqueado puro (não é colaborador)
        $isFranqueadoPuro = $user->franqueado && !$user->colaborador;

        // Mock de permissões para manter a compatibilidade com o frontend
        // Como o usuário pediu para não bloquear nada, retornamos true para tudo.
        $permissions = [
            'controle_estoque'       => true,
            'controle_saida_estoque' => true,
            'gestao_equipe'          => true,
            'fluxo_caixa'            => true,
            'dre'                    => $isFranqueadoPuro,
            'contas_pagar'           => true,
            'gestao_salmao'          => true,
        ];

        // Obtém o token RH da sessão para o sistema de notificações
        $rhToken = Session::get('rh_token');

        // Retorna os dados do usuário com os relacionamentos e permissões
        return response()->json([
            'status' => 'success',
            'data' => array_merge($user->toArray(), [
                'permissions' => $permissions,
                'rh_token' => $rhToken,
            ]),
        ]);
    }

    /**
     * Mascara email para logs (LGPD)
     */
    private function maskEmail(string $email): string
    {
        return preg_replace('/(^..)[^@]+/', '$1*****', $email);
    }

    private function redirectUser($user)
    {
        $emailMasked = $this->maskEmail($user->email);

        Log::info(
            "Registro de usuario: {$emailMasked} "
            . "franqueadora={$user->franqueadora}, "
            . "franqueado={$user->franqueado}, "
            . "colaborador={$user->colaborador} "
            . "last_visited_url={$user->last_visited_url}"
        );

        if ($user->last_visited_url) {
            return redirect($user->last_visited_url);
        }

        if ($user->franqueadora) {
            return redirect()->route('franqueadora.painel');
        }

        if ($user->franqueado || $user->colaborador) {
            return redirect()->route('franqueado.painel');
        }

        Log::warning(
            "Usuário {$emailMasked} sem permissões de acesso (franqueador/franqueado = 0)."
        );

        return redirect('https://login.taiksu.com.br/')
            ->with('error', 'Você não tem permissão para acessar este sistema.');
    }

}
