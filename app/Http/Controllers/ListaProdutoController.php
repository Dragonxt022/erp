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

    public function index()
    {
        // Recupera todos os produtos com seus preços
        $produtos = ListaProduto::with('precos.fornecedor')->get();

        // Classifica os produtos: "principal" no topo e ordem alfabética para cada grupo
        $produtosOrdenados = $produtos->sort(function ($a, $b) {
            // Primeiro, coloca "principal" no topo
            if ($a->categoria === 'principal' && $b->categoria !== 'principal') {
                return -1;
            }
            if ($a->categoria !== 'principal' && $b->categoria === 'principal') {
                return 1;
            }

            // Se as categorias forem iguais, ordena alfabeticamente (locale-aware)
            return strcoll($a->nome, $b->nome);
        });

        // Mapeia os dados e converte para array
        $resultados = $produtosOrdenados->map(function ($produto) {
            // Formata os preços de cada fornecedor
            $precos = $produto->precos->map(function ($preco) {
                return [
                    'preco_id' => $preco->id, // ID do registro em precos_fornecedores
                    'fornecedor_id' => $preco->fornecedor->id,
                    'fornecedor' => $preco->fornecedor->razao_social, // Nome do fornecedor
                    'preco_unitario' => $preco->preco_unitario, // Preço unitário
                ];
            });

            return [
                'id' => $produto->id,
                'nome' => $produto->nome,
                'categoria' => $produto->categoria,
                'unidadeDeMedida' => $produto->unidadeDeMedida,
                'profile_photo' => $produto->profile_photo ?? null,
                'estrela' => $produto->categoria === 'principal' ? '★' : null,
                'precos' => $precos, // Lista de preços de fornecedores com IDs
            ];
        })->values()->toArray(); // Reorganiza os índices e converte para array

        return response()->json($resultados);
    }



    public function store(Request $request)
    {
        // dd(request()->all()); // Verifique o conteúdo da requisição

        // Validação dos dados
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'categoria' => 'required|string|max:255',
            'unidadeDeMedida' => 'required|string|max:255',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:8048',
            'precos' => 'nullable|array',
            'precos.*' => 'nullable|string', // Agora são strings para incluir os valores com "R$"
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

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
            'categoria' => $request->categoria,
            'unidadeDeMedida' => $request->unidadeDeMedida,
            'profile_photo' => $profilePhotoPath,
        ]);

        // Processar os preços
        foreach ($request->precos as $fornecedorId => $preco) {
            // Remover o símbolo R$ e substituir as vírgulas por pontos
            $preco = preg_replace('/[^\d.,]/', '', $preco); // Remove caracteres não numéricos
            $preco = str_replace(',', '.', $preco); // Substitui a vírgula por ponto
            $precoCentavos = (float) $preco * 100; // Converte para centavos

            // Ignorar valores inválidos (0 ou NaN)
            if ($precoCentavos > 0 && !is_nan($precoCentavos)) {
                PrecoFornecedore::create([
                    'lista_produto_id' => $produto->id,
                    'fornecedor_id' => $fornecedorId,
                    'preco_unitario' => (int) $precoCentavos, // Salvar como inteiro em centavos
                ]);
            }
        }

        return response()->json([
            'message' => 'Produto cadastrado com sucesso!',
            'produto' => $produto
        ], 201);
    }


    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request)
    {
        // Validação dos dados
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:lista_produtos,id',
            'nome' => 'required|string|max:255',
            'categoria' => 'required|string|max:255',
            'unidadeDeMedida' => 'required|string|max:255',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:8048', // Mantém como nullable
            'precos' => 'nullable|array',
            'precos.*' => 'nullable|string',
        ]);


        // Verifica falha na validação
        if ($validator->fails()) {
            Log::warning('Validação falhou', ['errors' => $validator->errors()]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Buscar o produto pelo ID
        $produto = ListaProduto::find($request->id);

        if (!$produto) {
            Log::error('Produto não encontrado', ['produto_id' => $request->id]);
            return response()->json(['message' => 'Produto não encontrado'], 404);
        }

        // Atualizar os dados principais do produto
        $produto->nome = $request->nome;
        $produto->categoria = $request->categoria;
        $produto->unidadeDeMedida = $request->unidadeDeMedida;

        // Processar a nova imagem, se enviada
        if ($request->has('profile_photo') && $request->profile_photo) {
            Log::info('Processando a imagem do produto', ['produto_id' => $request->id]);

            // Remover a imagem antiga, se existir
            if ($produto->profile_photo && file_exists(public_path($produto->profile_photo))) {
                if (!unlink(public_path($produto->profile_photo))) {
                    Log::warning('Falha ao remover imagem antiga', ['produto_id' => $request->id]);
                }
            }

            // Salvar a nova imagem
            $profilePhoto = $request->file('profile_photo');
            $fileName = time() . '_' . $profilePhoto->getClientOriginalName();

            $folderPath = public_path('storage/images');

            if (!file_exists($folderPath)) {
                mkdir($folderPath, 0755, true);
            }

            $profilePhoto->move($folderPath, $fileName);
            $produto->profile_photo = 'storage/images/' . $fileName;
        }

        // Processar os preços enviados
        if ($request->has('precos') && is_array($request->precos)) {
            foreach ($request->precos as $precoUnitario) {
                Log::info('Processando preço', ['preco_unitario' => $precoUnitario]);

                // Decodificar o preço do fornecedor
                $precoUnitario = json_decode($precoUnitario, true);

                if (isset($precoUnitario['fornecedor_id']) && isset($precoUnitario['preco_unitario'])) {
                    // Remover o símbolo R$ e substituir as vírgulas por pontos
                    $preco = preg_replace('/[^\d.,]/', '', $precoUnitario['preco_unitario']);
                    $preco = str_replace(',', '.', $preco); // Substitui a vírgula por ponto
                    $precoCentavos = (float) $preco;

                    // Ignorar valores inválidos (0 ou NaN)
                    if ($precoCentavos > 0 && !is_nan($precoCentavos)) {
                        // Buscar o preço com base no produto_id e fornecedor_id
                        $precoExistente = PrecoFornecedore::where('lista_produto_id', $produto->id)
                            ->where('fornecedor_id', $precoUnitario['fornecedor_id'])
                            ->first();

                        // Se o preço for encontrado, atualizar
                        if ($precoExistente) {
                            $precoExistente->update([
                                'preco_unitario' => (int) $precoCentavos, // Atualiza para centavos
                            ]);
                            Log::info('Preço atualizado', ['produto_id' => $produto->id, 'fornecedor_id' => $precoUnitario['fornecedor_id']]);
                        } else {
                            // Se não encontrar o preço, criar um novo preço
                            PrecoFornecedore::create([
                                'lista_produto_id' => $produto->id,
                                'fornecedor_id' => $precoUnitario['fornecedor_id'],
                                'preco_unitario' => (int) $precoCentavos, // Salvar como inteiro em centavos
                            ]);
                            Log::info('Novo preço criado', ['produto_id' => $produto->id, 'fornecedor_id' => $precoUnitario['fornecedor_id']]);
                        }
                    } else {
                        Log::warning('Preço inválido ou preço em formato incorreto', [
                            'produto_id' => $produto->id,
                            'preco_unitario' => $precoUnitario['preco_unitario'],
                        ]);
                    }
                } else {
                    Log::warning('Preço ou fornecedor inválido, ignorando', [
                        'produto_id' => $produto->id,
                        'fornecedor_id' => $precoUnitario['fornecedor_id'] ?? 'undefined',
                    ]);
                }
            }
        }

        // Salvar as alterações no produto
        $produto->save();

        Log::info('Produto atualizado com sucesso', ['produto_id' => $request->id]);

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
