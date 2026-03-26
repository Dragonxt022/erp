<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SsoService
{
    /**
     * Sincroniza os detalhes da unidade (empresa) com base nos dados do SSO.
     *
     * @param array $unidadeData
     * @return void
     */
    public function syncUnidadeDetails(array $unidadeData)
    {
        if (empty($unidadeData['id'])) {
            return;
        }

        // Usa o Model InforUnidade que já existe no projeto
        \App\Models\InforUnidade::updateOrCreate(
            ['id' => $unidadeData['id']],
            [
                'cep'    => $unidadeData['cep'],
                'cidade' => $unidadeData['cidade'],
                'bairro' => $unidadeData['bairro'],
                'rua'    => $unidadeData['rua'],
                'numero' => $unidadeData['numero'],
                'cnpj'   => $unidadeData['cnpj'],
            ]
        );
    }

    /**
     * Valida o token no SSO e retorna os dados do usuário.
     *
     * @param string $token
     * @return array|null
     */
    public function validateToken(string $token): ?array
    {
        try {
            // Tenta validar no endpoint do SSO
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])->timeout(5)->get('https://login.taiksu.com.br/api/user/me');

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Falha na validação do token SSO', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Erro de conexão com SSO para validação de token', [
                'exception' => $e->getMessage()
            ]);
        }

        return null;
    }

    /**
     * Sincroniza o usuário do SSO com a base local para garantir integridade.
     *
     * @param array $userData
     * @param int|null $unidadeId
     * @return User
     */
    public function syncUser(array $userData, ?int $unidadeId): User
    {
        // Mapeamento Simplificado de Grupos
        $franqueado   = 0;
        $franqueadora = 0;
        $colaborador  = 0;
        $grupoNome    = $userData['grupo_nome'] ?? $userData['grupo'] ?? '';

        switch ($grupoNome) {
            case 'Desenvolvedor':
                $franqueado   = 1;
                $franqueadora = 1;
                break;
            case 'Franqueado':
                $franqueado = 1;
                break;
            case 'Franqueadora':
                $franqueadora = 1;
                $franqueado = 1;
                break;
            case 'Gerente':
                $colaborador = 1;
                $franqueado = 1;
                break;
            default:
                $colaborador = 1;
                $franqueado = 1;
                break;
        }

        // Recupera ou cria uma instância do usuário pelo ID (se vier do SSO) ou E-mail

        $userId = $userData['id'] ?? null;
        $email = $userData['email'];

        $user = null;

        if ($userId) {
            $user = User::find($userId);
        }

        if (!$user) {
            $user = User::where('email', $email)->first();
        }

        if (!$user) {
            $user = new User();
            if ($userId) {
                $user->id = $userId;
            }
            $user->email = $email;
            $user->password = bcrypt(Str::random(16));
        }

        // Tratamento da foto
        $foto = $userData['foto'] ?? null;
        $profilePhoto = $foto
            ? (str_starts_with($foto, 'http')
                ? $foto
                : "https://login.taiksu.com.br/frontend/profiles/{$foto}")
            : null;

        // Atualiza campos
        $user->forceFill([
            'name'               => $userData['name'] ?? $userData['nome'] ?? 'Usuário SSO',
            'cpf'                => $userData['cpf'] ?? null,
            'unidade_id'         => $unidadeId,
            'profile_photo_path' => $profilePhoto,
            'franqueado'         => $franqueado,
            'franqueadora'       => $franqueadora,
            'colaborador'        => $colaborador,
            'status'             => $userData['status'] ?? 'ativo',
            // 'pin'                => $userData['pin'] ?? null,
        ]);

        $user->save();

        // Removido sistema de UserPermission conforme solicitado

        return $user;
    }

    /**
     * Garante que o usuário existe localmente. Se não existir, tenta sincronizar com o SSO.
     *
     * @param int $userId
     * @param string|null $token
     * @return User
     */
    public function ensureUserExists(int $userId, ?string $token = null): User
    {
        $user = User::find($userId);

        if ($user) {
            return $user;
        }

        // Se não existir, tenta buscar no SSO
        $userData = $this->fetchUserById($userId, $token);

        if ($userData) {
            $unidadeId = $userData['unidade']['id'] ?? $userData['unidade_id'] ?? null;
            return $this->syncUser($userData, $unidadeId);
        }

        // Fallback: Cria um usuário "stub" para não quebrar FKs se o SSO não retornar nada
        Log::warning("Usuário SSO #{$userId} não encontrado ou sem permissão de acesso. Criando stub local.", [
            'user_id' => $userId
        ]);

        $user = new User();
        $user->id = $userId;
        $user->name = "Usuário SSO #{$userId}";
        $user->email = "sso-stub-{$userId}@taiksu.com.br";
        $user->password = bcrypt(Str::random(16));
        $user->colaborador = 1; // Default seguro
        $user->franqueado = 0;
        $user->franqueadora = 0;
        $user->status = 'ativo';
        $user->save();

        return $user;
    }

    /**
     * Busca dados de um usuário específico no SSO por ID.
     *
     * @param int $userId
     * @param string|null $token
     * @return array|null
     */
    public function fetchUserById(int $userId, ?string $token = null): ?array
    {
        try {
            $token = $token ?? session('rh_token') ?? request()->bearerToken();

            if (!$token) {
                return null;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])->timeout(5)->get("https://login.taiksu.com.br/api/user/find/{$userId}");

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning("Falha ao buscar usuário por ID no SSO", [
                'user_id' => $userId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Throwable $e) {
            Log::error("Erro de conexão com SSO para buscar usuário #{$userId}", [
                'exception' => $e->getMessage()
            ]);
        }

        return null;
    }
}
