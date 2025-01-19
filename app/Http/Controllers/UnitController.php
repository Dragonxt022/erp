<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\InforUnidade;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UnitController extends Controller
{
    /**
     * Exibir a lista de unidades.
     *
     * @return \Illuminate\Http\Response
     */
    public function getUnidades()
    {
        // Recupera todas as unidades
        $unidades = InforUnidade::orderBy('id', 'desc')->get();

        // Para cada unidade, recupera os usuários relacionados
        $resultados = $unidades->map(function ($unidade) {
            return [
                'unidade' => $unidade,
                'usuarios' => User::where('unidade_id', $unidade->id)->get(),
                'cidade' => $unidade->cidade ?? 'Taiksu Franchising', // Verifica se cidade é null e substitui
            ];
        });

        return response()->json($resultados);
    }

    /**
     * Criar uma nova unidade.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function createUnidade(Request $request)
    {
        // Validação dos dados recebidos
        $request->validate([
            'cep' => 'required|string|max:10',
            'cidade' => 'nullable|string|max:255',
            'bairro' => 'required|string|max:255',
            'rua' => 'required|string|max:255',
            'numero' => 'required|string|max:10',
            'cnpj' => 'required|string|max:18', // Exemplo de máscara de CNPJ
        ]);

        // Cria a nova unidade
        $unidade = InforUnidade::create([
            'cep' => $request->cep,
            'cidade' => $request->cidade,
            'bairro' => $request->bairro,
            'rua' => $request->rua,
            'numero' => $request->numero,
            'cnpj' => $request->cnpj,
        ]);

        // Retorna a unidade criada com status 201 (Criado)
        return response()->json($unidade);
    }

    public function updateUnidade(Request $request, $id)
    {
        // Validação dos dados recebidos
        $request->validate([
            'cep' => 'required|string|max:10',
            'cidade' => 'nullable|string|max:255',
            'bairro' => 'required|string|max:255',
            'rua' => 'required|string|max:255',
            'numero' => 'required|string|max:10',
            'cnpj' => 'required|string|max:18', // Exemplo de máscara de CNPJ
        ]);

        // Busca a unidade pelo ID
        $unidade = InforUnidade::find($id);

        // Retorna erro caso a unidade não exista
        if (!$unidade) {
            return response()->json(['message' => 'Unidade não encontrada.'], 404);
        }

        // Atualiza os dados da unidade
        $unidade->update([
            'cep' => $request->cep,
            'cidade' => $request->cidade,
            'bairro' => $request->bairro,
            'rua' => $request->rua,
            'numero' => $request->numero,
            'cnpj' => $request->cnpj,
        ]);

        return back()->with('flash', [
            'message' => 'Atualizado!',
            'type' => 'success',
        ]);
    }
}
