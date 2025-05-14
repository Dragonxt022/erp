<?php

namespace App\Http\Controllers;

use App\Models\Atividade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,svg,webp|max:2048',
            'etapas' => 'nullable|array',
            'etapas.*.descricao' => 'required|string'
        ]);

        // Armazenar a imagem, se enviada
        if ($request->hasFile('profile_photo')) {
            $profilePhotoPath = $request->file('profile_photo')->store('images', 'public');
            $data['profile_photo'] = $profilePhotoPath;
        }

        // Criar a atividade
        $atividade = Atividade::create($data);

        // Criar as etapas associadas
        if (!empty($data['etapas'])) {
            foreach ($data['etapas'] as $etapa) {
                $atividade->etapas()->create($etapa);
            }
        }

        return response()->json($atividade->load('etapas'), 201);
    }
    public function update(Request $request, $id)
    {
        Log::info('Dados brutos recebidos:', $request->all());
        Log::info('Etapas recebidas:', $request->input('etapas', []));

        $data = $request->validate([
            'name' => 'required|string',
            'setor_id' => 'required|integer|exists:operacionais,id',
            'tempo_estimated' => 'required|integer',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,svg,webp|max:2048',
            'etapas' => 'nullable|array',
            'etapas.*.descricao' => 'required|string'
        ]);

        Log::info('Dados validados:', $data);

        // Buscar a atividade existente
        $atividade = Atividade::findOrFail($id);

        // Armazenar a nova imagem, se enviada
        if ($request->hasFile('profile_photo')) {
            // Excluir a imagem antiga (se houver)
            if ($atividade->profile_photo) {
                Storage::disk('public')->delete($atividade->profile_photo);
            }

            // Fazer o upload da nova imagem
            $profilePhotoPath = $request->file('profile_photo')->store('images', 'public');
            $data['profile_photo'] = $profilePhotoPath;
        }

        // Atualizar a atividade com os novos dados
        $atividade->update([
            'name' => $data['name'],
            'setor_id' => $data['setor_id'],
            'tempo_estimated' => $data['tempo_estimated'],
            'profile_photo' => $data['profile_photo'] ?? $atividade->profile_photo,
        ]);

        // Atualizar as etapas associadas
        if (!empty($data['etapas'])) {
            // Excluir as etapas antigas antes de atualizar
            $atividade->etapas()->delete();

            // Criar as novas etapas associadas
            foreach ($data['etapas'] as $etapa) {
                $atividade->etapas()->create($etapa);
            }
        }

        // Retornar a atividade atualizada com as etapas
        return response()->json($atividade->load('etapas'), 200);
    }

    // Remover atividade e suas etapas
    public function destroy($id)
    {
        $atividade = Atividade::findOrFail($id);
        $atividade->delete();

        return response()->json(['message' => 'Atividade removida com sucesso.']);
    }
}
