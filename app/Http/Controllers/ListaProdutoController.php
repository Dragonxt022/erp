<?php

namespace App\Http\Controllers;

use App\Models\ListaProduto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
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

        // Para cada unidade, recupera os usuários relacionados
        $resultados = $produtos->map(function ($produtos) {
            return [
                'id' => $produtos->id,
                'nome' => $produtos->nome,
                'profile_photo' => $produtos->profile_photo ?? null, // Verifica se cidade é null e substitui
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
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
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
    public function update(Request $request, string $id)
    {
        //
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
