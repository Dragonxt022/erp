<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtividadeEtapa extends Model
{
    protected $fillable = ['descricao', 'atividade_id'];

    public function atividade()
    {
        return $this->belongsTo(Atividade::class);
    }
}
