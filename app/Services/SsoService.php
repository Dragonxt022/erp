<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserPermission;
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
        // Mapeamento de grupos para flags booleanas
        $franqueado   = 0;
        $franqueadora = 0;
        $colaborador  = 0;
        $grupoNome    = $userData['grupo_nome'] ?? $userData['grupo'] ?? '';

        switch ($grupoNome) {
            case 'Desenvolvedor':
                $franqueado   = 1;
                $franqueadora = 1;
                $colaborador  = 1;
                break;
            case 'Franqueado':
            case 'Gerente':
                $franqueado = 1;
                break;
            case 'Colaborador':
            case 'Recepcionista':
            case 'Operador de Caixa':
                $colaborador = 1;
                break;
            case 'Franqueadora':
                $franqueadora = 1;
                break;
        }

        // Recupera ou cria uma instância do usuário pelo ID (se vier do SSO) ou E-mail
        // O ideal é confiar no ID do SSO se os bancos estiverem sincronizados, 
        // mas como fallback usamos e-mail e atualizamos o ID se possível (embora ID seja PK).
        // Aqui assumiremos que o ID da 'api/user/me' é o ID global que deve bater com o local.
        
        $userId = $userData['id'] ?? null;
        $email = $userData['email'];

        $user = null;

        if ($userId) {
            $user = User::find($userId);
        }

        // Se não achou por ID, tenta por e-mail
        if (!$user) {
            $user = User::where('email', $email)->first();
        }

        if (!$user) {
            $user = new User();
            if ($userId) {
                // Força o ID para bater com o SSO
                $user->id = $userId;
            }
            $user->email = $email;
            $user->password = bcrypt(Str::random(16)); // Senha dummy, autenticação é via token
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
        ]);

        $user->save();

        // ⚡ Permissões default (Mantém a lógica do UserSyncService)
        // Só cria se não existir. Se o SSO passar permissões específicas, poderíamos atualizar aqui.
        if (!UserPermission::where('user_id', $user->id)->exists()) {
            $permissoesDefault = [
                'controle_estoque'       => false,
                'controle_saida_estoque' => false,
                'gestao_equipe'          => false,
                'fluxo_caixa'            => false,
                'dre'                    => false,
                'contas_pagar'           => false,
                'gestao_salmao'          => false,
            ];

            $gruposComPermissaoTotal = [
                'Franqueado',
                'Franqueadora',
                'Desenvolvedor',
                'Gerente',
                'Recepcionista',
                'Operador de Caixa',
            ];

            if (in_array($grupoNome, $gruposComPermissaoTotal)) {
                $permissoesDefault = array_map(fn() => true, $permissoesDefault);
            }

            UserPermission::create(array_merge(
                ['user_id' => $user->id],
                $permissoesDefault
            ));
        }

        return $user;
    }
}
