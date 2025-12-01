<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargaHorario extends Model
{
    use HasFactory;

    protected $table = 'carga_horarios';

    protected $fillable = [
        'user_id',
        'unidade_id',
        'status',
        'dia_semana',
        'periodo',
        'hora_entrada',
        'hora_saida',
        'carga_horaria_semanal',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function unidade()
    {
        return $this->belongsTo(InforUnidade::class, 'unidade_id');
    }
}

