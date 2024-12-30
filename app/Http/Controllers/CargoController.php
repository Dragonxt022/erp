<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use Illuminate\Http\Request;

class CargoController extends Controller
{
    /**
     * Retorna todos os cargos.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Pega todos os cargos na base de dados
        $cargos = Cargo::all();

        // Retorna os cargos em formato JSON
        return response()->json($cargos);
    }
}
