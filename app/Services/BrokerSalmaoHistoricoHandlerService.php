<?php

namespace App\Services;

use App\Models\Fornecedor;
use App\Models\SalmaoCalibre;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class BrokerSalmaoHistoricoHandlerService
{
    public function __construct(
        protected SalmaoHistoricoService $salmaoHistoricoService
    ) {
    }

    public function handle(array $payload, ?string $deliveryId = null, string $eventType = '1'): array
    {
        $validator = Validator::make($payload, [
            'responsavel_id' => 'required|integer',
            'fornecedor' => 'required|integer',
            'calibre' => 'required|string|max:255',
            'valor_caixa' => 'required|numeric|min:0',
            'peso_liquido' => 'required|numeric|min:0',
            'peso_limpo' => 'required|numeric|min:0',
            'porcentagem_aproveitamento' => 'required|numeric|between:0,100',
            'desperdicio' => 'required|numeric|min:0',
            'unidade_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException(
                'Payload inválido para o evento 1: ' . $validator->errors()->toJson(JSON_UNESCAPED_UNICODE)
            );
        }

        $validated = $validator->validated();

        $fornecedor = Fornecedor::query()->find($validated['fornecedor']);
        if (! $fornecedor) {
            throw new InvalidArgumentException("Fornecedor {$validated['fornecedor']} não encontrado.");
        }

        $calibre = $this->resolveCalibre((string) $validated['calibre']);
        if (! $calibre) {
            throw new InvalidArgumentException("Calibre '{$validated['calibre']}' não encontrado.");
        }

        $user = $this->resolveResponsavel((int) $validated['responsavel_id'], (int) $validated['unidade_id']);

        $historicoData = [
            'responsavel_id' => (int) $user->id,
            'calibre_id' => (int) $calibre->id,
            'fornecedor_id' => (int) $fornecedor->id,
            'valor_pago' => (float) $validated['valor_caixa'],
            'peso_bruto' => (float) $validated['peso_liquido'],
            'peso_limpo' => (float) $validated['peso_limpo'],
            'aproveitamento' => (float) $validated['porcentagem_aproveitamento'],
            'desperdicio' => (float) $validated['desperdicio'],
            'unidade_id' => (int) $validated['unidade_id'],
            'publish_broker_event' => false,
        ];

        $result = $this->salmaoHistoricoService->registrarHistorico($historicoData, $user);

        Log::info('Evento do broker processado com sucesso.', [
            'event_type' => $eventType,
            'delivery_id' => $deliveryId,
            'handler' => static::class . '::handle',
            'responsavel_id' => $user->id,
            'unidade_id' => $validated['unidade_id'],
            'calibre_id' => $calibre->id,
            'fornecedor_id' => $fornecedor->id,
            'historico_id' => $result['historico']->id,
        ]);

        return [
            'handler' => static::class,
            'method' => 'handle',
            'historico_id' => $result['historico']->id,
            'estoque_count' => $result['estoque']->count(),
        ];
    }

    protected function resolveCalibre(string $calibreNome): ?SalmaoCalibre
    {
        $calibreNome = trim($calibreNome);
        $faixa = preg_replace('/^(Calibre|Salm[aã]o)\s+/iu', '', $calibreNome);

        $candidatos = array_values(array_unique(array_filter([
            $calibreNome,
            'Salmão ' . $faixa,
            'Calibre ' . $faixa,
            $faixa,
        ])));

        return SalmaoCalibre::query()
            ->whereIn('nome', $candidatos)
            ->orWhere(function ($query) use ($faixa) {
                $query->where('nome', 'like', '%' . $faixa . '%');
            })
            ->orderBy('id')
            ->first();
    }

    protected function resolveResponsavel(int $responsavelId, int $unidadeId): User
    {
        $user = User::query()->find($responsavelId);

        if (! $user) {
            $user = new User();
            $user->id = $responsavelId;
            $user->name = "Usuário Broker #{$responsavelId}";
            $user->email = "broker-user-{$responsavelId}@taiksu.com.br";
            $user->password = bcrypt(bin2hex(random_bytes(16)));
            $user->colaborador = 1;
            $user->franqueado = 0;
            $user->franqueadora = 0;
            $user->status = 'ativo';
        }

        if (! $user->unidade_id) {
            $user->unidade_id = $unidadeId;
        }

        $user->save();

        return $user;
    }
}
