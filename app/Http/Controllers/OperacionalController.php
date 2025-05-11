<?php

namespace App\Http\Controllers;

use App\Models\Notificacao;
use App\Models\Operacional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class OperacionalController extends Controller
{
    // Listar todos
    public function index()
    {
        $operacionais = Operacional::all();
        return response()->json(['data' => $operacionais]);
    }

    // Exibir um específico
    public function show($id)
    {
        $operacional = Operacional::findOrFail($id);
        return response()->json(['data' => $operacional]);
    }

    // Cadastrar
    public function store(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'profile_photo' => 'nullable|file|image|max:2048', // Corrigido para validar como imagem
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $profilePhotoPath = null;

        if ($request->hasFile('profile_photo')) {
            $folderPath = public_path('storage/images');

            if (!File::exists($folderPath)) {
                File::makeDirectory($folderPath, 0755, true);
            }

            $profilePhoto = $request->file('profile_photo');
            $fileName = time() . '_' . $profilePhoto->getClientOriginalName();
            $profilePhoto->move($folderPath, $fileName);

            chmod($folderPath . '/' . $fileName, 0644);

            $profilePhotoPath = 'images/' . $fileName;
        }

        $operacional = Operacional::create([
            'name' => $request->input('name'),
            'profile_photo' => $profilePhotoPath,
        ]);

        return response()->json(['data' => $operacional], 201);
    }

    public function update(Request $request, $id)
    {
        $operacional = Operacional::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'profile_photo' => 'nullable|image|max:2048', // Aceita imagem até 2MB
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [
            'name' => $request->input('name'),
        ];

        // Se houver uma nova imagem, processa e salva
        if ($request->hasFile('profile_photo')) {
            $folderPath = public_path('storage/images');

            if (!File::exists($folderPath)) {
                File::makeDirectory($folderPath, 0755, true);
            }

            $profilePhoto = $request->file('profile_photo');
            $fileName = time() . '_' . $profilePhoto->getClientOriginalName();
            $profilePhoto->move($folderPath, $fileName);

            chmod($folderPath . '/' . $fileName, 0644);

            $data['profile_photo'] = 'images/' . $fileName;
        }

        $operacional->update($data);

        return response()->json(['data' => $operacional], 200);
    }


    // Excluir
    public function destroy($id)
    {
        $operacional = Operacional::findOrFail($id);

        // Deleta a imagem se existir
        if ($operacional->profile_photo && Storage::exists($operacional->profile_photo)) {
            Storage::delete($operacional->profile_photo);
        }

        $operacional->delete();

        return response()->json(['message' => 'Operacional excluído com sucesso.']);
    }
}
