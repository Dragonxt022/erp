<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categoria;

class CategoriaController extends Controller
{
    public function index()
    {
        // Listar todas as categorias com seus grupos
        $categorias = Categoria::with('grupo')->get();

        // Retornar resposta em JSON
        return response()->json($categorias);
    }
}
