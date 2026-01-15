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
            case 'Recepcionista':
            case 'Operador de Caixa':
                $franqueado = 1;
                break;
            case 'Colaborador':
                $franqueado  = 1;
                $colaborador = 1;
                break;
            case 'Franqueadora':
                $franqueadora = 1;
                break;
        }

        // ✅ REGRA: usuário SEMPRE identificado pelo e-mail
        if (empty($userData['email'])) {
            return null;
        }

        $user = User::where('email', $userData['email'])->first();

        // 🔎 Dados básicos
        $name = $userData['name'] ?? $userData['nome'] ?? 'Usuário SSO';
        $cpf  = $userData['cpf'] ?? null;

        // 🔎 Foto (corrige duplicação de URL)
        $foto = $userData['foto'] ?? null;
        $profilePhoto = $foto
            ? (str_starts_with($foto, 'http')
                ? $foto
                : "https://login.taiksu.com.br/frontend/profiles/{$foto}")
            : null;

        // 🆕 Criação
        if (!$user) {
            $user = new User();

            // id externo SOMENTE no create
            if (!empty($userData['id'])) {
                $user->id = $userData['id'];
            }

            $user->email    = $userData['email'];
            $user->password = bcrypt(Str::random(16));
        }

        // ✏ Atualização segura (sem alterar email)
        $user->fill([
            'name'               => $name,
            'cpf'                => $cpf,
            'unidade_id'         => $unidadeId,
            'profile_photo_path' => $profilePhoto,
            'franqueado'         => $franqueado,
            'franqueadora'       => $franqueadora,
            'colaborador'        => $colaborador,
        ]);

        $user->save();

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

            if (in_array($grupoNome, [
                'Franqueado',
                'Franqueadora',
                'Desenvolvedor',
                'Gerente',
                'Recepcionista',
                'Operador de Caixa',
            ])) {
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
