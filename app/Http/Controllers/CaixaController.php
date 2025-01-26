<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\CanalVenda;
use App\Models\FechamentoCaixa;
use App\Models\FluxoCaixa;
use App\Models\UnidadeCanaisVenda;
use App\Models\UnidadePaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CaixaController extends Controller
{
    // Método para abrir o caixa
    public function abrirCaixa(Request $request)
    {
        // Validação do valor inicial
        $request->validate([
            'valor_inicial' => 'required|numeric|min:1',
        ]);

        // Obtém o ID da unidade e o usuário logado
        $unidadeId = Auth::user()->unidade_id;
        $responsavelId = Auth::id();
        $valorInicial = $request->valor_inicial;

        DB::beginTransaction();

        try {
            // Cria um novo registro de caixa
            $caixa = Caixa::create([
                'unidade_id' => $unidadeId,
                'responsavel_id' => $responsavelId,
                'valor_inicial' => $valorInicial,
                'valor_final' => $valorInicial,
                'status' => 1, // Marca o caixa como aberto
                'motivo' => 'Abertura inicial',
            ]);

            // Registro no histórico (fluxo de caixa)
            FluxoCaixa::create([
                'unidade_id' => $unidadeId,
                'responsavel_id' => $responsavelId,
                'caixa_id' => $caixa->id,
                'operacao' => 'abertura',
                'valor' => $valorInicial, // Valor inicial do caixa
                'hora' => now(),
                'motivo' => 'Abertura do caixa',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Caixa aberto com sucesso!',
                'caixa' => $caixa,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Erro ao abrir o caixa: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Método para fechar o caixa
    public function fecharCaixa(Request $request)
    {
        // Identifica o usuário autenticado
        $usuario = Auth::user();
        $unidade_id = $usuario->unidade_id;

        // Busca o caixa aberto da unidade (com status 1)
        $caixa = Caixa::where('unidade_id', $unidade_id)
            ->where('status', 1)
            ->first();

        if (!$caixa || !$caixa->id) {
            return response()->json(['message' => 'Nenhum caixa aberto encontrado.'], 404);
        }

        // Limpando e estruturando os dados de métodos de pagamento
        $metodosLimpos = array_map(function ($metodo) {
            return [
                'metodo_pagamento_id' => $metodo['default_payment_method']['id'] ?? null,
                'valor_total_vendas' => (float) str_replace(['R$', '.', ','], ['', '', '.'], $metodo['total_vendas_metodos_pagamento'] ?? 0),
            ];
        }, $request->metodos);

        // Limpando e estruturando os dados de canais de venda
        $canaisLimpos = array_map(function ($canal) {
            return [
                'canal_de_vendas_id' => $canal['default_canal_de_vendas']['id'] ?? null, // Corrigido para acessar 'default_canal_de_vendas'
                'valor_total_vendas' => (float) str_replace(['R$', '.', ','], ['', '', '.'], $canal['total_vendas_canais_vendas'] ?? 0),
                'quantidade_vendas_feitas' => (int) ($canal['quantidade_vendas_canais_vendas'] ?? 0),
            ];
        }, $request->canais);


        // Calcula o valor final somando os totais de métodos e canais de venda
        $totalMetodosPagamento = array_sum(array_column($metodosLimpos, 'valor_total_vendas'));
        $totalCanaisVendas = array_sum(array_column($canaisLimpos, 'valor_total_vendas'));
        $valorFinal = $totalMetodosPagamento + $totalCanaisVendas;

        // dd($totalMetodosPagamento, $totalCanaisVendas, $valorFinal, $metodosLimpos, $canaisLimpos, $request);

        DB::beginTransaction();

        try {
            // Criar o fechamento de caixa para métodos de pagamento
            foreach ($metodosLimpos as $metodo) {
                if ($metodo['metodo_pagamento_id']) {
                    FechamentoCaixa::create([
                        'unidade_id' => $unidade_id,
                        'metodo_pagamento_id' => $metodo['metodo_pagamento_id'],
                        'caixa_id' => $caixa->id,
                        'valor_total_vendas' => $metodo['valor_total_vendas'],
                    ]);
                }
            }

            // Criar os registros de canais de venda
            foreach ($canaisLimpos as $canal) {
                if ($canal['canal_de_vendas_id']) {
                    CanalVenda::create([
                        'unidade_id' => $unidade_id,
                        'canal_de_vendas_id' => $canal['canal_de_vendas_id'],
                        'caixa_id' => $caixa->id,
                        'valor_total_vendas' => $canal['valor_total_vendas'],
                        'quantidade_vendas_feitas' => $canal['quantidade_vendas_feitas'],
                    ]);
                }
            }

            // Registro no histórico (fechamento)
            FluxoCaixa::create([
                'unidade_id' => $unidade_id,
                'responsavel_id' => $usuario->id,
                'caixa_id' => $caixa->id,
                'operacao' => 'fechamento',
                'valor' => $valorFinal,
                'hora' => now(),
                'motivo' => $request->motivo ?? 'Fechamento',
            ]);

            // Atualiza o valor final e o status do caixa
            $caixa->valor_final = $valorFinal;
            $caixa->status = 0;
            $caixa->motivo = $request->motivo ?? 'Fechamento de caixa';
            $caixa->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Caixa fechado com sucesso!',
                'caixa' => $caixa,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }


    // Método para exibir os detalhes do caixa
    public function detalhesCaixa($id)
    {
        // Busca o caixa pelo ID e carrega os relacionamentos necessários
        $caixa = Caixa::with([
            'unidade',
            'responsavel',
            'fluxosCaixa',
            'fechamentoCaixas',
            'canaisVendas',
            'historicoMetodosPagamento',
            'historicoCanaisVendas'
        ])->findOrFail($id);

        return response()->json([
            'caixa' => $caixa,
        ]);
    }

    // Método para listar todos os caixas abertos
    public function listarCaixasAbertos()
    {
        // Obtém todos os caixas abertos da unidade do usuário logado
        $caixas = Caixa::where('unidade_id', Auth::user()->unidade_id)
            ->where('status', 1) // Verifica se o status é 1 (aberto)
            ->get();

        return response()->json([
            'aberto' => !$caixas->isEmpty(), // Retorna true se houver caixas abertos
            'caixas' => $caixas,
        ]);
    }


    // Lista os métodos de pagamentos e canais de vendas ativos da unidade do usuário autenticado
    public function listarMetodosEcanaisAtivos()
    {
        // Obtém o ID da unidade do usuário autenticado
        $unidadeId = Auth::user()->unidade_id;

        if (!$unidadeId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuário não pertence a nenhuma unidade.',
            ], 403);
        }

        // Filtra os métodos de pagamento pela unidade do usuário e status ativo
        $metodosPagamento = UnidadePaymentMethod::where('unidade_id', $unidadeId)
            ->where('status', 1) // Apenas métodos ativos
            ->with('defaultPaymentMethod') // Carrega os detalhes do método de pagamento padrão
            ->get();

        // Filtra os canais de vendas pela unidade do usuário e status ativo
        $canaisVendas = UnidadeCanaisVenda::where('unidade_id', $unidadeId)
            ->where('status', 1) // Apenas canais ativos
            ->with('defaultCanalDeVendas') // Carrega os detalhes do canal de vendas padrão
            ->get();

        return response()->json([
            'status' => 'success',
            'metodosPagamento' => $metodosPagamento,
            'canaisVendas' => $canaisVendas,
        ], 200);
    }
}
