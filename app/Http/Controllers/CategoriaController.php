<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categoria;
use Illuminate\Support\Facades\DB;

class CategoriaController extends Controller
{
    public function index()
    {
        // Listar todas as categorias com seus grupos
        $categorias = Categoria::with('grupo')->get();

        // Retornar resposta em JSON
        return response()->json($categorias);
    }

    public function listarPorGrupo()
    {
        $grupoNome = 'Custos Fixos';

        // Buscar o grupo de categoria pelo nome
        $grupo = DB::table('grupo_de_categorias')->where('nome', $grupoNome)->first();

        // Verificar se o grupo existe
        if (!$grupo) {
            return response()->json(['message' => 'Grupo de categoria não encontrado'], 404);
        }

        // Buscar as categorias associadas a este grupo
        $categorias = DB::table('categorias')->where('grupo_id', $grupo->id)->get();

        // Retornar as categorias em formato JSON
        return response()->json($categorias);
    }

    public function listaSeletorCaixa()
    {
        // Listar apenas categorias onde exibir_seletor_caixa é true (1)
        $categorias = Categoria::where('exibir_seletor_caixa', 1)
            ->with('grupo') // Opcional: incluir o relacionamento com grupo
            ->get();

        return response()->json($categorias);
    }

        public function listaSeletorContasApagar()
    {
        // Listar apenas categorias onde exibir_seletor_caixa é true (1)
        $categorias = Categoria::where('exibir_contas_apagar', 1)
            ->with('grupo') // Opcional: incluir o relacionamento com grupo
            ->get();

        return response()->json($categorias);
    }
}
