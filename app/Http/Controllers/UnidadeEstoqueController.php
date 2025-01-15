<?php

namespace App\Http\Controllers;

use App\Models\Fornecedor;
use App\Models\UnidadeEstoque;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UnidadeEstoqueController extends Controller
{


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

            // Filtra os lotes com quantidade > 0
            $lotesDisponiveis = $lotes->filter(function ($lote) {
                return $lote->quantidade > 0; // Só manter os lotes com estoque maior que 0
            });

            // Se não houver lotes disponíveis, não exibe o produto
            if ($lotesDisponiveis->isEmpty()) {
                return null; // Retorna null para ocultar o produto
            }

            // Calcular a soma do valor total do lote (valor unitário * quantidade)
            $valorTotalLotes = $lotesDisponiveis->sum(function ($lote) {
                return ($lote->preco_insumo / 100) * $lote->quantidade;
            });

            // Calcular a soma do valor pago por quilo para os produtos 'a_granel'
            $valorPagoPorQuiloLote = null;
            if ($insumo->unidadeDeMedida === 'a_granel') {
                $valorPagoPorQuiloLote = $lotesDisponiveis->sum(function ($lote) {
                    return ($lote->preco_insumo / 100); // Soma apenas o valor unitário (não multiplicado pela quantidade)
                });
            }

            return [
                'id' => $insumo->id,
                'nome' => $insumo->nome,
                'profile_photo' => $insumo->profile_photo,
                'categoria' => $insumo->categoria,
                'unidadeDeMedida' => $insumo->unidadeDeMedida,
                'lotes' => $lotesDisponiveis->map(function ($lote) use ($insumo) {
                    // Calcular o valor unitário
                    $valorUnitario = $lote->preco_insumo / 100;
                    $valorTotal = $valorUnitario * $lote->quantidade; // Valor total do lote

                    // Calcular o valor pago por quilo, se for 'a_granel'
                    $valorPagoPorQuilo = $insumo->unidadeDeMedida === 'a_granel'
                        ? 'R$ ' . number_format($valorUnitario, 2, ',', '.') // Apenas o valor unitário
                        : null;

                    return [
                        'id' => $lote->id,
                        'unidadeDeMedida' => $insumo->unidadeDeMedida,
                        'data' => $lote->created_at->format('d/m/Y'),
                        'fornecedor' => $lote->fornecedor->razao_social,
                        'quantidade' => $lote->quantidade,
                        'preco_unitario' => 'R$ ' . number_format($valorUnitario, 2, ',', '.'),
                        'valor_total' => 'R$ ' . number_format($valorTotal, 2, ',', '.'),
                        // Adicionando cálculo do valor pago por quilo para produtos 'kg'
                        'valor_pago_por_quilo' => $lote->unidade === 'kg'
                            ? 'R$ ' . number_format(($lote->preco_insumo / 100) / $lote->quantidade, 2, ',', '.')
                            : null,
                    ];
                }),
                // Soma o valor total de todos os lotes do insumo
                'valor_total_lote' => 'R$ ' . number_format($valorTotalLotes, 2, ',', '.'),
                // Soma do valor pago por quilo de todos os lotes do insumo
                'valor_pago_por_quilo_lote' => $valorPagoPorQuiloLote !== null
                    ? 'R$ ' . number_format($valorPagoPorQuiloLote, 2, ',', '.') : null,
            ];
        })->filter(); // Remove os produtos que foram marcados como null

        // Retorna os dados no formato JSON
        return response()->json($produtosAgrupados);
    }


    // Responsavel pelos dados da tela de estoque
    public function painelInicialEstoque()
    {
        $unidadeId = Auth::user()->unidade_id;

        // Obtém todos os itens do estoque da unidade, mas filtra os itens com quantidade > 0
        $estoque = UnidadeEstoque::where('unidade_id', $unidadeId)
            ->where('quantidade', '>', 0)  // Filtra os itens com quantidade maior que 0
            ->get();

        // Calcula o valor total dos insumos considerando a unidade de medida
        $valorInsumos = $estoque->reduce(function ($total, $item) {
            $preco = $item->preco_insumo / 100; // Convertendo centavos para reais
            $quantidade = $item->quantidade;

            // Verifica se a unidade é 'kg' ou 'unidade' e calcula corretamente
            if ($item->unidade === 'kg') {
                return $total + $preco; // Preço total já é o valor final para 'kg'
            } elseif ($item->unidade === 'unidade') {
                return $total + ($preco * $quantidade); // Multiplica pela quantidade
            }

            return $total;
        }, 0);

        // Quantidade total de itens no estoque (contando "kg" como 1 item)
        $itensNoEstoque = $estoque->reduce(function ($total, $item) {
            if ($item->unidade === 'kg') {
                return $total + 1; // Conta cada 'kg' como 1 item
            }
            return $total + $item->quantidade; // Conta as unidades normalmente
        }, 0);

        $historicoMovimentacoes = UnidadeEstoque::with(['insumo', 'usuario'])
            ->where('unidade_id', $unidadeId)
            ->where('quantidade', '>', 0)  // Filtra itens com quantidade maior que 0 no histórico também
            ->orderBy('id', 'desc')
            ->take(10)
            ->get()
            ->map(function ($estoque) {
                return [
                    'operacao' => $estoque->operacao,
                    'unidade' => $estoque->unidade,
                    'quantidade' => $estoque->quantidade,
                    'item' => $estoque->insumo->nome ?? 'N/A',
                    'data' => $estoque->created_at->format('d/m/Y - H:i:s'),
                    'responsavel' => $estoque->usuario->name ?? 'Desconhecido',
                ];
            });

        return response()->json([
            'valorInsumos' => number_format($valorInsumos, 2, ',', '.'),
            'itensNoEstoque' => $itensNoEstoque,
            'historicoMovimentacoes' => $historicoMovimentacoes,
        ]);
    }



    // Lista os fornecederes
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

    // Atualiza o lote
    // public function update(Request $request, $loteId)
    // {
    //     // Validação dos dados
    //     $request->validate([
    //         'quantidade' => 'required|numeric|min:0',
    //     ]);

    //     // Busca o lote pelo ID
    //     $lote = UnidadeEstoque::findOrFail($loteId);

    //     // Obtém os dados da tabela
    //     $unidade = $lote->unidade; // Exemplo: 'unidade' ou 'kg'

    //     // Calcula a diferença de quantidade
    //     $quantidadeAntiga = $lote->quantidade;
    //     $novaQuantidade = $request->input('quantidade');
    //     $diferencaQuantidade = $novaQuantidade - $quantidadeAntiga;

    //     // Realiza os cálculos com base na unidade
    //     if ($unidade === 'unidade') {
    //         // Produto por unidade: calcula o preço unitário em centavos
    //         $precoUnitarioCentavos = $lote->preco_insumo / $quantidadeAntiga;
    //         $novoValorTotalCentavos = $novaQuantidade * $precoUnitarioCentavos;
    //     } elseif ($unidade === 'kg') {
    //         // Produto por quilo: calcula o valor por quilo em centavos
    //         $valorPorQuiloCentavos = $lote->preco_insumo / $quantidadeAntiga;
    //         $novoValorTotalCentavos = $novaQuantidade * $valorPorQuiloCentavos;
    //     } else {
    //         return response()->json(['error' => 'Unidade de medida inválida.'], 400);
    //     }

    //     // Atualiza a quantidade e o preço total no lote (em centavos)
    //     $lote->quantidade = $novaQuantidade;
    //     $lote->preco_insumo = round($novoValorTotalCentavos); // Armazena em centavos
    //     $lote->updated_at = now(); // Atualiza o timestamp

    //     $lote->save();

    //     return response()->json([
    //         'message' => 'Quantidade e valores atualizados com sucesso!',
    //         'lote' => [
    //             'id' => $lote->id,
    //             'insumo_id' => $lote->insumo_id,
    //             'quantidade' => $lote->quantidade,
    //             'preco_insumo' => number_format($lote->preco_insumo / 100, 2, ',', '.'), // Exibe em reais
    //         ],
    //     ]);
    // }

    public function update(Request $request, $loteId)
    {
        // Validação dos dados
        $request->validate([
            'quantidade' => 'required|numeric|min:0',
        ]);

        // Busca o lote pelo ID
        $lote = UnidadeEstoque::findOrFail($loteId);

        // Obtém a nova quantidade
        $novaQuantidade = $request->input('quantidade');

        // Atualiza a quantidade e o timestamp
        $lote->quantidade = $novaQuantidade;
        $lote->updated_at = now(); // Atualiza o timestamp

        $lote->save();

        return response()->json([
            'message' => 'Quantidade atualizada com sucesso!',
            'lote' => [
                'id' => $lote->id,
                'quantidade' => $lote->quantidade,
            ],
        ], 201);
    }
}
