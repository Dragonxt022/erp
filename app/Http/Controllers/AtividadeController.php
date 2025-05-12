<?php

namespace App\Http\Controllers;

use App\Models\Atividade;
use App\Models\AtividadeEtapa;
use Illuminate\Http\Request;

class AtividadeController extends Controller
{
    // Listar todas as atividades com suas etapas
    public function index()
    {
        $atividades = Atividade::with('etapas')->get();
        return response()->json(['data' => $atividades]);
    }

    // Criar nova atividades
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'setor_id' => 'required|integer|exists:operacionais,id',
            'tempo_estimated' => 'required|integer',
            'profile_photo' => 'nullable|string',
            'etapas' => 'array',
            'etapas.*.descricao' => 'required|string'
        ]);

        $atividade = Atividade::create($data);

        if (!empty($data['etapas'])) {
            foreach ($data['etapas'] as $etapa) {
                $atividade->etapas()->create($etapa);
            }
        }

        return response()->json($atividade->load('etapas'), 201);
    }

    // Ver detalhes de uma atividade
    public function show($id)
    {
        $atividade = Atividade::with('etapas')->findOrFail($id);
        return response()->json($atividade);
    }

    // Atualizar atividade
    public function update(Request $request, $id)
    {
        $atividade = Atividade::findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|string',
            'setor_id' => 'sometimes|integer|exists:operacionais,id',
            'tempo_estimated' => 'sometimes|integer',
            'profile_photo' => 'nullable|string',
        ]);

        $atividade->update($data);

        return response()->json($atividade);
    }

    // Remover atividade e suas etapas
    public function destroy($id)
    {
        $atividade = Atividade::findOrFail($id);
        $atividade->delete();

        return response()->json(['message' => 'Atividade removida com sucesso.']);
    }
}
