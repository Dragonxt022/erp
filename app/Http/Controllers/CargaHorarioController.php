<?php

namespace App\Http\Controllers;

use App\Models\CargaHorario;
use App\Models\CargaHorarioLog;
use Illuminate\Http\Request;

class CargaHorarioController extends Controller
{
    public function index()
    {
        $horarios = CargaHorario::with(['user', 'unidade'])->get();
        return response()->json($horarios);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'unidade_id' => 'required|exists:infor_unidade,id',
            'status' => 'required|boolean',
            'dia_semana' => 'required|in:segunda,terca,quarta,quinta,sexta,sabado,domingo',
            'periodo' => 'required|in:manha,tarde,noite',
            'hora_entrada' => 'required|date_format:H:i',
            'hora_saida' => 'required|date_format:H:i',
            'carga_horaria_semanal' => 'required|numeric',
        ]);

        $existing = CargaHorario::where([
            'user_id' => $data['user_id'],
            'unidade_id' => $data['unidade_id'],
            'dia_semana' => $data['dia_semana'],
            'periodo' => $data['periodo'],
        ])->first();

        if ($data['status']) {
            if ($existing) {
                $existing->update($data);
                $acao = 'atualizado';
            } else {
                $existing = CargaHorario::create($data);
                $acao = 'criado';
            }
        } else {
            if ($existing) {
                $existing->delete();
                $this->logChange($existing, 'excluido');
                return response()->json(['message' => 'Registro desativado (excluído)'], 200);
            } else {
                return response()->json(['message' => 'Nada a desativar'], 200);
            }
        }

        $this->logChange($existing, $acao);

        return response()->json($existing, $acao === 'criado' ? 201 : 200);
    }

    private function logChange(CargaHorario $registro, string $acao)
    {
        CargaHorarioLog::create([
            'carga_horario_id' => $registro->id,
            'user_id' => $registro->user_id,
            'unidade_id' => $registro->unidade_id,
            'status' => $registro->status,
            'dia_semana' => $registro->dia_semana,
            'periodo' => $registro->periodo,
            'hora_entrada' => $registro->hora_entrada,
            'hora_saida' => $registro->hora_saida,
            'carga_horaria_semanal' => $registro->carga_horaria_semanal,
            'acao' => $acao,
        ]);
    }

    public function show(CargaHorario $cargaHorario)
    {
        return response()->json($cargaHorario->load(['user', 'unidade']));
    }

    public function update(Request $request, CargaHorario $cargaHorario)
    {
        $data = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'unidade_id' => 'sometimes|exists:infor_unidade,id',
            'status' => 'sometimes|boolean',
            'dia_semana' => 'sometimes|in:segunda,terca,quarta,quinta,sexta,sabado,domingo',
            'periodo' => 'sometimes|in:manha,tarde,noite',
            'hora_entrada' => 'sometimes|date_format:H:i',
            'hora_saida' => 'sometimes|date_format:H:i',
            'carga_horaria_semanal' => 'sometimes|numeric',
        ]);

        $cargaHorario->update($data);

        $this->logChange($cargaHorario, 'atualizado');

        return response()->json($cargaHorario);
    }

    public function destroy(CargaHorario $cargaHorario)
    {
        $this->logChange($cargaHorario, 'excluido');
        $cargaHorario->delete();
        return response()->json(null, 204); // 204 No Content
    }
}
