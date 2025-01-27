<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        // Recupera as categorias associadas à unidade do usuário autenticado
        $categorias = Category::where('unidade_id', $request->user()->unidade_id)
            ->orderBy('nome', 'asc') // Ordena por nome (opcional)
            ->get();

        return response()->json($categorias, 200);
    }

    public function store(Request $request)
    {
        // Validação dos dados
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
        ]);

        // Identificando o usuário autenticado
        $user = Auth::user();

        // Verificando se o usuário está autenticado e se ele tem uma unidade associada
        if (!$user || !$user->unidade_id) {
            return response()->json(['error' => 'Usuário não possui unidade associada.'], 400);
        }

        // Criação da nova categoria associada à unidade do usuário autenticado
        $category = Category::create([
            'nome' => $validated['nome'],
            'descricao' => $validated['descricao'] ?? null,
            'unidade_id' => $user->unidade_id,  // Usando a unidade associada ao usuário
        ]);

        return response()->json($category, 201);
    }
}
