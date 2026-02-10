<?php

namespace App\Services;

use App\Models\SalmaoHistorico;
use App\Models\ListaProduto;
use App\Models\UnidadeEstoque;
use App\Models\MovimentacoesEstoque;
use Illuminate\Support\Facades\DB;
use Exception;

class SalmaoHistoricoService
{
    /**
     * Registra o histórico de salmão e atualiza estoques.
     *
     * @param array $data Dados validados
     * @param \App\Models\User $user Usuário responsável/autenticado
     * @return array
     * @throws Exception
     */
    public function registrarHistorico(array $data, $user)
    {
        // Iniciar transação
        DB::beginTransaction();

        try {
            // 1. Registrar no SalmaoHistorico
            $historico = SalmaoHistorico::create([
                'responsavel_id' => $data['responsavel_id'],
                'calibre_id' => $data['calibre_id'],
                'valor_pago' => $data['valor_pago'],
                'peso_bruto' => $data['peso_bruto'],
                'peso_limpo' => $data['peso_limpo'],
                'aproveitamento' => $data['aproveitamento'],
                'desperdicio' => $data['desperdicio'],
                'unidade_id' => $user->unidade_id,
            ]);

            // 2. Buscar "Salmão Limpo" na ListaProduto
            $salmaoLimpo = ListaProduto::firstOrCreate(
                ['id' => 84], // Busca por ID fixo conforme original
                [
                    'categoria_id' => 13,
                    'unidadeDeMedida' => 'a_granel',
                    'prioridade' => 1,
                ]
            );

            // Se não existir (caso firstOrCreate falhe silenciosamente ou retorne algo inesperado, embora improvável com Eloquent)
            if (!$salmaoLimpo) {
                throw new Exception('O produto "Salmão Limpo" não foi encontrado na lista de produtos.');
            }

            $precoPorQuilo = $data['peso_limpo'] > 0 ? $data['valor_pago'] / $data['peso_limpo'] : 0;

            // 3. Adicionar ao estoque (UnidadeEstoque)
            $estoqueItem = UnidadeEstoque::create([
                'insumo_id' => $salmaoLimpo->id,
                'fornecedor_id' => $data['fornecedor_id'],
                'usuario_id' => $user->id,
                'unidade_id' => $user->unidade_id,
                'quantidade' => $data['peso_limpo'],
                'preco_insumo' => $precoPorQuilo,
                'categoria_id' => $salmaoLimpo->categoria_id,
                'operacao' => 'Entrada',
                'unidade' => 'kg',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 4. Registrar movimentação no histórico de estoques
            MovimentacoesEstoque::create([
                'insumo_id' => $salmaoLimpo->id,
                'fornecedor_id' => $data['fornecedor_id'],
                'usuario_id' => $user->id,
                'quantidade' => $data['peso_limpo'],
                'preco_insumo' => $precoPorQuilo,
                'operacao' => 'Entrada',
                'unidade' => 'kg',
                'unidade_id' => $user->unidade_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Dados principais do painel
            $estoque = UnidadeEstoque::where('unidade_id', $user->unidade_id)->where('quantidade', '>', 0)->get();
            $valorInsumos = $estoque->reduce(function ($total, $item) {
                $preco = $item->preco_insumo;
                $quantidade = $item->quantidade;
                return $item->unidade === 'kg' ? $total + $preco : $total + ($preco * $quantidade);
            }, 0);

            $saldoAtual = $valorInsumos;

            DB::table('controle_saldo_estoques')->insert([
                'ajuste_saldo' => $saldoAtual,
                'data_ajuste' => now(),
                'motivo_ajuste' => 'Atualização após entrada',
                'unidade_id' => $user->unidade_id,
                'responsavel_id' => $data['responsavel_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Confirmar transação
            DB::commit();

            return [
                'historico' => $historico,
                'estoque' => $estoque,
            ];
        } catch (Exception $e) {
            // Reverter transação em caso de erro
            DB::rollBack();
            throw $e;
        }
    }
}
