<?php

namespace App\Http\Controllers;

use App\Models\Ponto;
use Illuminate\Http\Request;

class PontoController extends Controller
{
    public function index()
    {
        return Ponto::all();
    }

    public function update(Request $request, Ponto $ponto)
    {
        $data = $request->validate([
            'pontos' => 'required|integer',
        ]);

        $ponto->update($data);

        return response()->json($ponto);
    }
}
