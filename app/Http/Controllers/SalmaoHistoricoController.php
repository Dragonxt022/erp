<?php

namespace App\Http\Controllers;

use App\Models\ListaProduto;
use App\Models\MovimentacoesEstoque;
use App\Models\SalmaoCalibre;
use App\Models\SalmaoHistorico;
use App\Models\UnidadeEstoque;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalmaoHistoricoController extends Controller
{
    public function index()
    {
        // Usuário autenticado
        $user = Auth::user();

        // Verifica se o usuário está autenticado
        if (!$user) {
            return response()->json(['error' => 'Usuário não autenticado.'], 401);
        }

        // Buscar todos os usuários da mesma unidade_id (sem excluir o usuário autenticado)
        $colaboradores = User::where('unidade_id', $user->unidade_id)
            ->get()
            ->map(function ($colaborador) {
                // Retorna apenas os campos desejados
                return [
                    'id' => $colaborador->id,
                    'name' => $colaborador->name,
                    'email' => $colaborador->email,
                    'pin' => $colaborador->pin,
                ];
            });

        // Listar todos os calibres disponíveis
        $calibres = SalmaoCalibre::select('id', 'nome', 'tipo')->get();

        // Retornar os dados como JSON
        return response()->json([
            'calibres' => $calibres,
            'colaboradores' => $colaboradores,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
    //     // Validação dos dados recebidos
    //     $validated = $request->validate([
    //         'responsavel_id' => 'required|exists:users,id',
    //         'calibre_id' => 'required|exists:salmao_calibres,id',
    //         'valor_pago' => 'required|numeric|min:0',
    //         'peso_bruto' => 'required|numeric|min:0',
    //         'peso_limpo' => 'required|numeric|min:0',
    //         'aproveitamento' => 'required|numeric|between:0,100',
    //         'desperdicio' => 'required|numeric|min:0',
    //     ]);

    //     // Usuário autenticado (para pegar a unidade_id)
    //     $user = Auth::user();
    //     if (!$user) {
    //         return response()->json(['error' => 'Usuário não autenticado.'], 401);
    //     }

    //     // Criar o registro no banco de dados
    //     $historico = SalmaoHistorico::create([
    //         'responsavel_id' => $validated['responsavel_id'],
    //         'calibre_id' => $validated['calibre_id'],
    //         'valor_pago' => $validated['valor_pago'],
    //         'peso_bruto' => $validated['peso_bruto'],
    //         'peso_limpo' => $validated['peso_limpo'],
    //         'aproveitamento' => $validated['aproveitamento'],
    //         'desperdicio' => $validated['desperdicio'],
    //         'unidade_id' => $user->unidade_id, // Adiciona a unidade do usuário autenticado
    //     ]);

    //     // Retornar resposta de sucesso
    //     return response()->json([
    //         'message' => 'Registro salvo com sucesso!',
    //         'historico' => $historico,
    //     ], 201);
    // }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Usuário não autenticado.'], 401);
        }

        // Validação dos dados recebidos
        $validated = $request->validate([
            'responsavel_id' => 'required|exists:users,id',
            'calibre_id' => 'required|exists:salmao_calibres,id',
            'valor_pago' => 'required|numeric|min:0',
            'peso_bruto' => 'required|numeric|min:0',
            'peso_limpo' => 'required|numeric|min:0',
            'aproveitamento' => 'required|numeric|between:0,100',
            'desperdicio' => 'required|numeric|min:0',
        ]);

        // Iniciar transação
        DB::beginTransaction();

        try {
            // 1. Registrar no SalmaoHistorico
            $historico = SalmaoHistorico::create([
                'responsavel_id' => $validated['responsavel_id'],
                'calibre_id' => $validated['calibre_id'],
                'valor_pago' => $validated['valor_pago'],
                'peso_bruto' => $validated['peso_bruto'],
                'peso_limpo' => $validated['peso_limpo'],
                'aproveitamento' => $validated['aproveitamento'],
                'desperdicio' => $validated['desperdicio'],
                'unidade_id' => $user->unidade_id,
            ]);

            // 2. Buscar "Salmão Limpo" na ListaProduto
            $salmaoLimpo = ListaProduto::where('nome', 'Salmão Limpo')->first();

            // Se não existir, lançar uma exceção
            if (!$salmaoLimpo) {
                throw new \Exception('O produto "Salmão Limpo" não foi encontrado na lista de produtos. Por favor, cadastre-o antes de continuar.');
            }

            // 3. Adicionar ao estoque (UnidadeEstoque)
            $precoPorKg = $validated['valor_pago'] / $validated['peso_bruto'];
            $estoque = UnidadeEstoque::create([
                'insumo_id' => $salmaoLimpo->id,
                'fornecedor_id' => null,
                'usuario_id' => $validated['responsavel_id'],
                'unidade_id' => $user->unidade_id,
                'quantidade' => $validated['peso_limpo'],
                'preco_insumo' => $precoPorKg,
                'categoria_id' => $salmaoLimpo->categoria_id,
                'operacao' => 'Entrada',
                'unidade' => 'kg',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 4. Registrar movimentação no histórico de estoques
            MovimentacoesEstoque::create([
                'insumo_id' => $salmaoLimpo->id,
                'fornecedor_id' => null,
                'usuario_id' => $validated['responsavel_id'],
                'quantidade' => $validated['peso_limpo'],
                'preco_insumo' => $precoPorKg,
                'operacao' => 'Entrada',
                'unidade' => 'kg',
                'unidade_id' => $user->unidade_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Confirmar transação
            DB::commit();

            return response()->json([
                'message' => 'Registro salvo e estoque atualizado com sucesso!',
                'historico' => $historico,
                'estoque' => $estoque,
            ], 201);
        } catch (\Exception $e) {
            // Reverter transação em caso de erro
            DB::rollBack();
            return response()->json(['error' => 'Erro ao salvar: ' . $e->getMessage()], 500);
        }
    }
}
