<?php

// app/Http/Controllers/FornecedorController.php

namespace App\Http\Controllers;

use App\Models\Fornecedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FornecedorController extends Controller
{
    public function store(Request $request)
    {
        // Validação dos dados
        $validated = $request->validate([
            'cnpj' => 'required|string|max:18',  // Ajuste conforme o formato do CNPJ
            'razao_social' => 'required|string|max:255',
            'whatsapp' => 'nullable|string|max:15',
            'estado' => 'nullable|string|max:255',
            'email' => 'required|email|max:255', // Exemplo de validação para e-mail
        ]);

        // Criação do fornecedor
        $fornecedor = Fornecedor::create($validated);

        return response()->json([
            'message' => 'Fornecedor cadastrado com sucesso!',
            'data' => $fornecedor
        ], 201);
    }

    public function index()
    {
        // Recupera todos os fornecedores
        $fornecedores = Fornecedor::all();

        // Usando o map para selecionar os dados desejados
        $fornecedoresData = $fornecedores->map(function ($fornecedor) {
            return [
                'id' => $fornecedor->id,
                'cnpj' => $fornecedor->cnpj,
                'razao_social' => $fornecedor->razao_social,
                'email' => $fornecedor->email,
                'whatsapp' => $fornecedor->whatsapp,
                'estado' => $fornecedor->estado,
            ];
        });

        return response()->json([
            'data' => $fornecedoresData
        ], 200);
    }

    // Atualiza um campo específico do fornecedor
    public function update(Request $request)
    {

        // Validação condicional, apenas para os campos presentes
        $validator = Validator::make($request->all(), [
            'razao_social' => 'nullable|string|max:255',
            'whatsapp' => 'nullable|string|max:15',
            'estado' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        // Verifica falha na validação
        if ($validator->fails()) {
            Log::warning('Validação falhou', ['errors' => $validator->errors()]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Buscar o produto pelo ID
        $fornecedor = Fornecedor::find($request->id);

        if (!$fornecedor) {
            Log::error('Produto não encontrado', ['produto_id' => $request->id]);
            return response()->json(['message' => 'Produto não encontrado'], 404);
        }

        $fornecedor->razao_social = $request->razao_social;
        $fornecedor->whatsapp = $request->whatsapp;
        $fornecedor->estado = $request->estado;
        $fornecedor->email = $request->email;

        // Salvar as alterações
        $fornecedor->save();

        // Retorna a resposta com o fornecedor atualizado
        return response()->json([
            'message' => 'Fornecedor atualizado com sucesso!',
            'data' => $fornecedor
        ], 200);
    }

    public function destroy($id)
    {

        // Buscar   ID
        $fornecedor = Fornecedor::find($id);

        if (!$fornecedor) {
            return response()->json(['error' => 'fornecedor não encontrado!'], 404);
        }

        // Excluir o fornecedor
        $fornecedor->delete();

        return response()->json([
            'message' => 'fornecedor excluído com sucesso!'
        ], 200);
    }
}
