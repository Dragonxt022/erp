<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserPermission;
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

        // TODO: Avaliar se syncUnidade (lista completa) ainda é desejado no login
        // Originalmente estava aqui. Se for pesado, pode mover para job.
        // Mantendo compatibilidade com UserSyncService original, mas usando UserSyncService legado para isso se necessário
        // ou movendo essa lógica para SsoService se for crítica.
        // O user disse "parar de depender das tabelas locais", mas "syncUnidade" puxa todos os colaboradores...
        // Talvez seja melhor manter a chamada ao servico antigo se ele ainda existir, ou omitir se o foco for só Auth.
        // O plano diz "Remover a dependência de UserSyncService e usar SsoService".
        // Vou assumir que o syncUnidade em massa não é o foco "da rota que é usada em todas as aplicações",
        // mas é bom manter se o sistema precisa listar colaboradores offline.
        
        // if (in_array($grupoNome, ['Desenvolvedor', 'Franqueadora', 'Franqueado', 'Gerente'])) {
        //     try {
        //         // Mantendo a sincronização de unidade via UserSyncService legado por enquanto se necessário, 
        //         // ou implementando no SsoService se for vital.
        //         // Como não copiei syncUnidade (massa) para SsoService, vou deixar comentado ou usar o legado temporariamente?
        //         // O código original usava UserSyncService::syncUnidade.
        //         // Vou manter o uso estático do UserSyncService para essa função específica de *bulk sync* se ela for necessária,
        //         // mas o AuthController agora depende primariamente do SsoService.
        //         \App\Services\UserSyncService::syncUnidade($unidadeId, $token);
        //     } catch (\Throwable $e) {
        //         Log::error("Erro na sincronização de usuários da unidade {$unidadeId}: " . $e->getMessage());
        //     }
        // }

        Log::info("Usuário autenticado: {$user->email}, Grupo: {$grupoNome}, Redirecionando...");

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
     * Redireciona o usuário para sua última página visitada ou para o painel padrão.
     */
    private function redirectUser($user)
    {
        Log::info("Redirecionando usuário {$user->email}: franqueadora={$user->franqueadora}, franqueado={$user->franqueado}, last_visited_url={$user->last_visited_url}");

        if ($user->last_visited_url) {
            return redirect($user->last_visited_url);
        }

        if ($user->franqueadora) {
            return redirect()->route('franqueadora.painel');
        }

        if ($user->franqueado) {
            return redirect()->route('franqueado.painel');
        }

        Log::warning("Usuário {$user->email} sem permissões de acesso (franqueador/franqueado = 0).");
        return redirect('https://login.taiksu.com.br/')->with('error', 'Você não tem permissão para acessar este sistema.');
    }
}
