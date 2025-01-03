<?php

// app/Http/Controllers/FornecedorController.php

namespace App\Http\Controllers;

use App\Models\Fornecedor;
use Illuminate\Http\Request;

class FornecedorController extends Controller
{
    public function store(Request $request)
    {
        // Validação dos dados
        $validated = $request->validate([
            'nome_completo' => 'required|string|max:255',
            'email' => 'required|email|unique:fornecedores,email',
            'cnpj' => 'required|string|unique:fornecedores,cnpj',
            'whatsapp' => 'nullable|string',
            'estado' => 'nullable|string',
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
        $fornecedoresData = $fornecedores->map(function($fornecedor) {
            return [
                'id' => $fornecedor->id,
                'nome_completo' => $fornecedor->nome_completo,
                'email' => $fornecedor->email,
                'cnpj' => $fornecedor->cnpj,
                'whatsapp' => $fornecedor->whatsapp,
                'estado' => $fornecedor->estado,
            ];
        });

        return response()->json([
            'data' => $fornecedoresData
        ], 200);
    }

     // Atualiza um campo específico do fornecedor
     public function update(Request $request, $id)
     {
         // Busca o fornecedor pelo ID
         $fornecedor = Fornecedor::findOrFail($id);

         // Validação de todos os campos do fornecedor
         $validated = $request->validate([
             'nome_completo' => 'required|string|max:255',
             'cnpj' => 'required|string|max:18',  // Ajuste conforme o formato do CNPJ
             'whatsapp' => 'nullable|string|max:15',
             'estado' => 'nullable|string|max:255',
             'email' => 'required|email|max:255', // Exemplo de validação para e-mail
         ]);

         // Atualiza todos os campos do fornecedor
         $fornecedor->update($validated);

         // Retorna a resposta com o fornecedor atualizado
         return response()->json([
             'message' => 'Fornecedor atualizado com sucesso!',
             'data' => $fornecedor
         ], 200);
     }


}
