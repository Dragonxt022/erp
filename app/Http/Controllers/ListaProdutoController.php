<?php

namespace App\Http\Controllers;

use App\Models\ListaProduto;
use App\Models\PrecoFornecedore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ListaProdutoController extends Controller
{
    // Lista todos os produtos por grupo de categorias
    public function index()
    {
        // Recupera todos os produtos com suas categorias e preços
        $produtos = ListaProduto::with([
            'categoriaProduto:id,nome', // Relacionamento com CategoriaProduto
            'precos' => function ($query) {
                $query->select('id', 'lista_produto_id', 'fornecedor_id', 'preco_unitario', 'qtd_minima')
                    ->with(['fornecedor:id,razao_social']); // Relacionamento com Fornecedor
            }
        ])
            ->orderBy('categoria_id') // Ordenar por categoria
            ->orderBy('prioridade', 'desc') // Colocar os produtos prioritários no topo
            ->orderBy('nome') // Ordenar alfabeticamente por nome
            ->get();

        // Agrupar os produtos por categoria
        $produtosAgrupados = $produtos->groupBy(function ($produto) {
            return optional($produto->categoriaProduto)->nome ?? 'Sem Categoria';
        });


        // Formatar os dados no formato desejado
        $resultados = $produtosAgrupados->map(function ($produtosPorCategoria, $categoriaNome) {
            return [
                'categoria_nome' => $categoriaNome,
                'produtos' => $produtosPorCategoria->map(function ($produto) {
                    return [
                        'id' => $produto->id,
                        'nome' => $produto->nome,
                        'unidadeDeMedida' => $produto->unidadeDeMedida,
                        'prioridade' => $produto->prioridade,
                        'categoria_id' => $produto->categoria_id,
                        'profile_photo' => $produto->profile_photo ?? null,
                        'estrela' => $produto->prioridade == 1 ? '★' : null,
                        'precos' => $produto->precos->map(function ($preco) use ($produto) {
                            // Verifica se o produto é unitário e converte qtd_minima para inteiro
                            $qtd_minima = $produto->unidadeDeMedida === 'unitario' ? intval($preco->qtd_minima) : $preco->qtd_minima;

                            return [
                                'preco_id' => $preco->id,
                                'fornecedor_id' => $preco->fornecedor_id,
                                'fornecedor' => $preco->fornecedor->razao_social, // Nome do fornecedor
                                'preco_unitario' => $preco->preco_unitario,
                                'qtd_minima' => $qtd_minima, // Quantidade mínima convertida se for unitário
                            ];
                        })
                    ];
                })
            ];
        });

        return response()->json($resultados);
    }

    public function store(Request $request)
    {

        // Validação dos dados
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'prioridade' => 'required|in:true,false',
            'categoria_id' => 'required|string|max:255',
            'unidadeDeMedida' => 'required|string|max:255',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:8048',
            'precos' => 'nullable|array',
            'precos.*' => 'nullable|string',
            'quantidades' => 'nullable|array',
            'quantidades.*' => 'nullable|string',
        ]);


        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Converter "prioridade" para booleano corretamente
        $prioridade = filter_var($request->prioridade, FILTER_VALIDATE_BOOLEAN);

        // Caminho da imagem
        $profilePhotoPath = null;

        if ($request->hasFile('profile_photo')) {
            // Caminho da pasta de fotos (armazenamento público)
            $folderPath = public_path('storage/images');

            // Verificar se a pasta existe, caso contrário, cria
            if (!File::exists($folderPath)) {
                File::makeDirectory($folderPath, 0755, true);
            }

            // Processar a imagem
            $profilePhoto = $request->file('profile_photo');
            $fileName = time() . '_' . $profilePhoto->getClientOriginalName();
            $profilePhoto->move($folderPath, $fileName);

            // Ajustar permissões
            chmod($folderPath . '/' . $fileName, 0644);

            // Caminho para salvar no banco de dados
            $profilePhotoPath = 'storage/images/' . $fileName;
        }

        // Criar o produto
        $produto = ListaProduto::create([
            'nome' => $request->nome,
            'prioridade' => $prioridade,
            'categoria_id' => $request->categoria_id,
            'unidadeDeMedida' => $request->unidadeDeMedida,
            'profile_photo' => $profilePhotoPath,
        ]);

        // Processar os preços
        foreach ($request->precos as $fornecedorId => $preco) {
            // Remover o símbolo R$ e substituir vírgulas por pontos
            $preco = preg_replace('/[^\d.,]/', '', $preco);
            $preco = str_replace(',', '.', $preco);
            $precoDecimal = (float) $preco;

            // Tratando a quantidade mínima do fornecedor
            $qtdMinima = isset($request->quantidades[$fornecedorId]) ? $request->quantidades[$fornecedorId] : null;

            if ($qtdMinima !== null) {
                $qtdMinima = str_replace(',', '.', $qtdMinima);
                $qtdMinima = number_format((float) $qtdMinima, 3, '.', '');
            }

            // Buscar registro existente no banco de dados
            $precoExistente = PrecoFornecedore::where('lista_produto_id', $produto->id)
                ->where('fornecedor_id', $fornecedorId)
                ->first();

            if ($precoDecimal > 0 && !is_nan($precoDecimal)) {
                // Se existir, atualizar, senão criar um novo
                if ($precoExistente) {
                    $precoExistente->update([
                        'preco_unitario' => $precoDecimal,
                        'qtd_minima' => $qtdMinima,
                    ]);
                } else {
                    PrecoFornecedore::create([
                        'lista_produto_id' => $produto->id,
                        'fornecedor_id' => $fornecedorId,
                        'preco_unitario' => $precoDecimal,
                        'qtd_minima' => $qtdMinima,
                    ]);
                }
            } else {
                // Se o preço for inválido e já existir no banco, excluir o registro
                if ($precoExistente) {
                    $precoExistente->delete();
                }
            }
        }

        return response()->json([
            'message' => 'Produto cadastrado com sucesso!',
            'produto' => $produto
        ], 201);
    }

    // Função de atualização
    public function update(Request $request)
    {
        // Cnverte a json de precos
        if ($request->has('precos') && is_string($request->precos)) {
            $decodedPrecos = json_decode($request->precos, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json(['error' => 'Formato de precos inválido'], 422);
            }
            // Mescla o array decodificado na request
            $request->merge(['precos' => $decodedPrecos]);
        }

        // Validação dos dados
        $validator = Validator::make($request->all(), [
            'id'               => 'required|exists:lista_produtos,id',
            'nome'             => 'required|string|max:255',
            'prioridade'       => 'nullable',
            'categoria_id'     => 'required|string|max:255',
            'unidadeDeMedida'  => 'required|string|max:255',
            'profile_photo'    => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:8048',
            'precos'                           => 'nullable|array',
            'precos.fornecedores'              => 'nullable|array',
            'precos.fornecedores.*.fornecedor_id' => 'nullable',
            'precos.fornecedores.*.preco_unitario' => 'nullable',
            'precos.fornecedores.*.qtd_minima'   => 'nullable',
        ]);

        if ($validator->fails()) {
            Log::warning('Validação falhou', ['errors' => $validator->errors()]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Buscar o produto pelo ID
        $produto = ListaProduto::findOrFail($request->id);

        // Atualizar os dados do produto
        $produto->update([
            'nome' => $request->nome,
            'prioridade' => filter_var($request->prioridade, FILTER_VALIDATE_BOOLEAN),
            'categoria_id' => $request->categoria_id,
            'unidadeDeMedida' => $request->unidadeDeMedida,
        ]);

        // Processar a nova imagem, se enviada
        if ($request->hasFile('profile_photo')) {
            Log::info('Processando a imagem do produto', ['produto_id' => $produto->id]);

            // Remover a imagem antiga, se existir
            if ($produto->profile_photo && file_exists(public_path($produto->profile_photo))) {
                unlink(public_path($produto->profile_photo));
            }

            // Salvar a nova imagem
            $profilePhoto = $request->file('profile_photo');
            $fileName = time() . '_' . $profilePhoto->getClientOriginalName();
            $folderPath = public_path('storage/images');

            if (!file_exists($folderPath)) {
                mkdir($folderPath, 0755, true);
            }

            $profilePhoto->move($folderPath, $fileName);
            chmod($folderPath . '/' . $fileName, 0644);

            // Atualizar caminho no banco
            $produto->profile_photo = 'storage/images/' . $fileName;
            $produto->save();
        }

        // Processar os preços enviados
        if (
            $request->has('precos') &&
            isset($request->precos['fornecedores']) &&
            is_array($request->precos['fornecedores'])
        ) {
            foreach ($request->precos['fornecedores'] as $precoData) {
                $fornecedorId = $precoData['fornecedor_id'];
                // Remover "R$" e espaços antes de converter
                $precoUnitario = str_replace(['R$', ' '], '', $precoData['preco_unitario']);
                $precoUnitario = str_replace(',', '.', $precoUnitario);
                $qtdMinima = str_replace(',', '.', $precoData['qtd_minima']);

                // Garantir o formato numérico correto
                $precoUnitario = number_format((float) $precoUnitario, 2, '.', '');
                $qtdMinima = number_format((float) $qtdMinima, 3, '.', '');

                // Se os valores estiverem zerados ou nulos, ignora a criação/atualização
                if ((float)$precoUnitario == 0 || (float)$qtdMinima == 0) {
                    Log::info('Valores zerados ou nulos, ignorados', [
                        'produto_id'    => $produto->id,
                        'fornecedor_id' => $fornecedorId,
                        'preco_unitario' => $precoUnitario,
                        'qtd_minima'    => $qtdMinima,
                    ]);
                    continue; // Pula para o próximo fornecedor
                }

                // Buscar preço existente para este fornecedor e produto
                $precoExistente = PrecoFornecedore::where('lista_produto_id', $produto->id)
                    ->where('fornecedor_id', $fornecedorId)
                    ->first();

                if ($precoExistente) {
                    $precoExistente->update([
                        'preco_unitario' => $precoUnitario,
                        'qtd_minima'     => $qtdMinima,
                    ]);
                    Log::info('Preço atualizado', ['produto_id' => $produto->id, 'fornecedor_id' => $fornecedorId]);
                } else {
                    PrecoFornecedore::create([
                        'lista_produto_id' => $produto->id,
                        'fornecedor_id'    => $fornecedorId,
                        'preco_unitario'   => $precoUnitario,
                        'qtd_minima'       => $qtdMinima,
                    ]);
                    Log::info('Novo preço criado', ['produto_id' => $produto->id, 'fornecedor_id' => $fornecedorId]);
                }
            }
        }


        Log::info('Produto atualizado com sucesso', ['produto_id' => $produto->id]);

        return response()->json([
            'message' => 'Produto atualizado com sucesso!',
            'produto' => $produto
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Buscar o produto pelo ID
        $produto = ListaProduto::find($id);


        if (!$produto) {
            return response()->json(['error' => 'Produto não encontrado!'], 404);
        }

        // Verificar se existe um arquivo de imagem associado e excluí-lo
        if ($produto->profile_photo && File::exists(public_path($produto->profile_photo))) {
            // Excluir o arquivo de imagem
            File::delete(public_path($produto->profile_photo));
        }

        // Excluir o produto
        $produto->delete();

        return response()->json([
            'message' => 'Produto excluído com sucesso!'
        ], 200);
    }
}
