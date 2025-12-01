<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Support\Str;
use App\Models\InforUnidade;

class UserSyncService
{
    public static function syncUnidadeDetails(array $unidadeData)
    {
        if (empty($unidadeData['id'])) {
            return;
        }

        InforUnidade::updateOrCreate(
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

    public static function syncUser(array $userData, ?int $unidadeId)
    {
        // 🔎 Flags de grupo
        $franqueado = 0;
        $franqueadora = 0;
        $grupoNome = $userData['grupo_nome'] ?? '';

        switch ($grupoNome) {
            case 'Desenvolvedor':
                $franqueado = 1;
                $franqueadora = 1;
                break;
            case 'Franqueado':
            case 'Colaborador':
            case 'Gerente':
            case 'Recepcionista':
                $franqueado = 1;
                break;
            case 'Franqueadora':
                $franqueadora = 1;
                break;
        }

        // 🔎 Cria/atualiza usuário
        $user = User::updateOrCreate(
            ['email' => $userData['email']],
            [
                'id'                 => $userData['id'],
                'name'               => $userData['name'],
                'cpf'                => $userData['cpf'],
                'unidade_id'         => $unidadeId,
                'grupo_id'           => $userData['grupo_id'] ?? null,
                'profile_photo_path' => isset($userData['foto']) ? "https://login.taiksu.com.br/frontend/profiles/" . $userData['foto'] : null,
                'password'           => bcrypt(Str::random(16)),
                'franqueado'         => $franqueado,
                'franqueadora'       => $franqueadora,
            ]
        );

        // ⚡ Permissões default
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

            if (in_array($grupoNome, ['Franqueado', 'Franqueadora', 'Desenvolvedor'])) {
                $permissoesDefault = array_map(fn() => true, $permissoesDefault);
            }

            UserPermission::create(array_merge(
                ['user_id' => $user->id],
                $permissoesDefault
            ));
        }

        return $user;
    }
    public static function syncUnidade(int $unidadeId, string $token)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])->get("https://login.taiksu.com.br/api/colaboradores/{$unidadeId}");

            if (!$response->ok()) {
                Log::error("Falha ao buscar colaboradores da unidade {$unidadeId}", [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return;
            }

            $usuarios = $response->json()['data'] ?? [];

            foreach ($usuarios as $usuario) {
                try {
                    self::syncUser($usuario, $unidadeId);
                } catch (\Throwable $e) {
                    Log::error("Erro ao criar/atualizar usuário {$usuario['email']}", [
                        'exception' => $e->getMessage(),
                        'usuario' => $usuario
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::error("Erro na sincronização da unidade {$unidadeId}", [
                'exception' => $e->getMessage()
            ]);
        }
    }
}
