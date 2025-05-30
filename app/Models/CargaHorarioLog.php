<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CargaHorarioLog extends Model
{
    protected $table = 'carga_horario_logs';
    protected $fillable = [
        'carga_horario_id',
        'user_id',
        'unidade_id',
        'status',
        'dia_semana',
        'periodo',
        'hora_entrada',
        'hora_saida',
        'carga_horaria_semanal',
        'acao',
    ];
}
