<?php

namespace App\Http\Controllers;

use App\Models\Fornecedor;
use App\Models\UnidadeEstoque;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UnidadeEstoqueController extends Controller
{
    // public function index()
    // {
    //     // Obtém a unidade do usuário autenticado
    //     $unidadeId = Auth::user()->unidade_id;

    //     // Filtra os estoques pela unidade, ordena por 'created_at' em ordem decrescente e formata os dados
    //     $estoques = UnidadeEstoque::with(['insumo', 'fornecedor', 'usuario', 'unidade'])
    //         ->where('unidade_id', $unidadeId)
    //         ->orderBy('id', 'desc') // Ordena em ordem decrescente pela data de criação
    //         ->get()
    //         ->map(function ($estoque) {
    //             // Calcula o valor total do insumo (preço * quantidade)
    //             $valorTotal = ($estoque->preco_insumo / 100) * $estoque->quantidade;

    //             return [
    //                 'id' => $estoque->id,
    //                 'insumo' => [
    //                     'id' => $estoque->insumo->id,
    //                     'nome' => $estoque->insumo->nome,
    //                     'profile_photo' => $estoque->insumo->profile_photo,
    //                     'categoria' => $estoque->insumo->categoria,
    //                     'unidadeDeMedida' => $estoque->insumo->unidadeDeMedida,
    //                 ],
    //                 'fornecedor' => [
    //                     'id' => $estoque->fornecedor->id,
    //                     'razao_social' => $estoque->fornecedor->razao_social,
    //                 ],
    //                 'usuario' => [
    //                     'id' => $estoque->usuario->id,
    //                     'nome' => $estoque->usuario->name,
    //                 ],
    //                 'quantidade' => $estoque->quantidade,
    //                 // Converte 'preco_insumo' de centavos para reais e formata como moeda brasileira
    //                 'preco_insumo' => 'R$ ' . number_format($estoque->preco_insumo / 100, 2, ',', '.'),
    //                 // Valor total do insumo (preço * quantidade)
    //                 'valor_total' => 'R$ ' . number_format($valorTotal, 2, ',', '.'),
    //                 'operacao' => $estoque->operacao,
    //                 'data_criacao' => $estoque->created_at->format('d/m/Y H:i'),
    //             ];
    //         });

    //     // Retorna os dados no formato JSON
    //     return response()->json($estoques);
    // }

    public function index()
    {
        // Obtém a unidade do usuário autenticado
        $unidadeId = Auth::user()->unidade_id;

        // Filtra os estoques pela unidade
        $estoques = UnidadeEstoque::with(['insumo', 'fornecedor'])
            ->where('unidade_id', $unidadeId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Agrupa os estoques pelo insumo
        $produtosAgrupados = $estoques->groupBy('insumo_id')->map(function ($lotes) {
            // Pega o primeiro lote para obter informações do produto
            $primeiroLote = $lotes->first();
            $insumo = $primeiroLote->insumo;

            return [
                'id' => $insumo->id,
                'nome' => $insumo->nome,
                'profile_photo' => $insumo->profile_photo,
                'categoria' => $insumo->categoria,
                'unidadeDeMedida' => $insumo->unidadeDeMedida,
                'lotes' => $lotes->map(function ($lote) {
                    return [
                        'data' => $lote->created_at->format('d/m/Y'),
                        'fornecedor' => $lote->fornecedor->razao_social,
                        'quantidade' => $lote->quantidade,
                        'preco_unitario' => 'R$ ' . number_format($lote->preco_insumo / 100, 2, ',', '.'),
                        'valor_total' => 'R$ ' . number_format(($lote->preco_insumo / 100) * $lote->quantidade, 2, ',', '.'),
                    ];
                }),
            ];
        });

        // Retorna os dados no formato JSON
        return response()->json($produtosAgrupados);
    }




    public function painelInicialEstoque()
    {
        $unidadeId = Auth::user()->unidade_id;

        // Calcula o valor inicial do estoque e converte para reais
        $valorInicial = UnidadeEstoque::where('unidade_id', $unidadeId)
            ->sum(DB::raw('quantidade * preco_insumo')) / 100;

        // Valor total em insumos, convertido para reais
        $valorInsumos = UnidadeEstoque::where('unidade_id', $unidadeId)
            ->sum(DB::raw('quantidade * preco_insumo')) / 100;

        // Quantidade total de itens no estoque
        $itensNoEstoque = UnidadeEstoque::where('unidade_id', $unidadeId)->sum('quantidade');

        // Histórico de movimentações
        $historicoMovimentacoes = UnidadeEstoque::with(['insumo', 'usuario'])
            ->where('unidade_id', $unidadeId)
            ->orderBy('id', 'desc')
            ->take(10) // Limita a 10 registros
            ->get()
            ->map(function ($estoque) {
                return [
                    'operacao' => $estoque->operacao, // 'entrada' ou 'retirada'
                    'quantidade' => $estoque->quantidade,
                    'item' => $estoque->insumo->nome,
                    'data' => $estoque->created_at->format('d/m/Y - H:i:s'),
                    'responsavel' => $estoque->usuario->name,
                ];
            });

        return response()->json([
            // 'valorInicial' => number_format($valorInicial, 2, ',', '.'),
            'valorInsumos' => number_format($valorInsumos, 2, ',', '.'),
            'itensNoEstoque' => $itensNoEstoque,
            'historicoMovimentacoes' => $historicoMovimentacoes,
        ]);
    }

    public function unidadeForencedores()
    {
        // Recupera todos os fornecedores
        $fornecedores = Fornecedor::all();

        // Usando o map para selecionar os dados desejados
        $fornecedoresData = $fornecedores->map(function ($fornecedor) {
            return [
                'id' => $fornecedor->id,
                // 'cnpj' => $fornecedor->cnpj,
                'razao_social' => $fornecedor->razao_social,
                // 'email' => $fornecedor->email,
                // 'whatsapp' => $fornecedor->whatsapp,
                // 'estado' => $fornecedor->estado,
            ];
        });

        return response()->json([
            'data' => $fornecedoresData
        ], 200);
    }

    // Adiciona os produtos a unidade selecionada
    public function armazenarEntrada(Request $request)
    {
        // Validação dos dados recebidos
        $validatedData = $request->validate([
            'fornecedor_id' => 'nullable|integer|exists:fornecedores,id',
            'itens' => 'required|array',
            'itens.*.id' => 'required|integer|exists:lista_produtos,id',
            'itens.*.quantidade' => 'required|numeric|min:0',
            'itens.*.valorUnitario' => 'required|numeric|min:0',
            'itens.*.unidadeDeMedida' => 'nullable|string|in:a_granel,unitario',
        ]);

        DB::beginTransaction(); // Inicia uma transação

        try {
            foreach ($validatedData['itens'] as $item) {
                // Adicionando a verificação para garantir que a quantidade seja numérica
                $quantidade = floatval($item['quantidade']);

                DB::table('unidade_estoque')->insert([
                    'insumo_id' => $item['id'],
                    'fornecedor_id' => $validatedData['fornecedor_id'] ?? null, // Usando o fornecedor_id do JSON ou null
                    'usuario_id' => Auth::id(), // ID do usuário autenticado
                    'unidade_id' => Auth::user()->unidade_id, // Referência ao unidade_id do usuário autenticado

                    'quantidade' => $quantidade,
                    'preco_insumo' => $item['valorUnitario'], // Valor já validado no frontend
                    'operacao' => 'Entrada',
                    'unidade' => empty($item['unidadeDeMedida']) ? 'kg' : ($item['unidadeDeMedida'] === 'a_granel' ? 'kg' : 'unidade'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit(); // Confirma a transação

            return response()->json(['message' => 'Itens armazenados com sucesso!'], 201);
        } catch (\Exception $e) {
            DB::rollBack(); // Reverte a transação em caso de erro

            return response()->json(['error' => 'Erro ao armazenar itens: ' . $e->getMessage()], 500);
        }
    }
}
