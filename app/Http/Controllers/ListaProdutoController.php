<?php

namespace App\Http\Controllers;

use App\Models\ListaProduto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ListaProdutoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Recupera todas as unidades
        $produtos = ListaProduto::orderBy('id', 'desc')->get();

        // Para cada unidade, recupera os dados necessários e adiciona a lógica da estrela
        $resultados = $produtos->map(function ($produto) {
            return [
                'id' => $produto->id,
                'nome' => $produto->nome,
                'categoria' => $produto->categoria,
                'profile_photo' => $produto->profile_photo ?? null, // Verifica se profile_photo é null e substitui
                'estrela' => $produto->categoria === 'principal' ? '★' : null, // Adiciona estrela para categoria 'Principal'
            ];
        });

        return response()->json($resultados);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validação dos dados
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'categoria' => 'required|string|max:255',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
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
            'profile_photo' => $profilePhotoPath,
        ]);

        return response()->json([
            'message' => 'Produto cadastrado com sucesso!',
            'produto' => $produto
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request)
    {
        // Registrar o início da atualização
        Log::info('Iniciando atualização do produto', ['produto_id' => $request->id]);

        // Registrar dados da requisição
        Log::info('Dados recebidos para atualização', ['nome' => $request->nome, 'profile_photo' => $request->profile_photo]);

        // Validação dos dados
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:lista_produtos,id',  // Validar se o ID existe
            'nome' => 'required|string|max:255',
            'categoria' => 'required|string|max:255',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
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

        // Atualizar o nome
        $produto->nome = $request->nome;
        $produto->categoria = $request->categoria;

        // Processar a nova imagem, se enviada
        if ($request->has('profile_photo') && $request->profile_photo) {
            // Log para indicar que está tratando a imagem
            Log::info('Processando a imagem do produto', ['produto_id' => $request->id]);

            // Remover a imagem antiga, se existir
            if ($produto->profile_photo && file_exists(public_path($produto->profile_photo))) {
                unlink(public_path($produto->profile_photo));  // Remove a imagem antiga
            }

            // Salvar a nova imagem
            $profilePhoto = $request->file('profile_photo');
            $fileName = time() . '_' . $profilePhoto->getClientOriginalName();

            // Definir o caminho da pasta de armazenamento
            $folderPath = public_path('storage/images');

            // Verificar se a pasta existe, se não, criar a pasta
            if (!file_exists($folderPath)) {
                mkdir($folderPath, 0755, true);
            }

            // Mover a imagem para o diretório público
            $profilePhoto->move($folderPath, $fileName);

            // Atualizar o caminho da imagem no banco de dados
            $produto->profile_photo = 'storage/images/' . $fileName;
        }

        // Salvar as alterações
        $produto->save();

        // Registrar o sucesso da atualização
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
