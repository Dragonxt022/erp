<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Atividade extends Model
{
    protected $fillable = ['name', 'setor_id', 'tempo_estimated', 'profile_photo'];

    public function etapas()
    {
        return $this->hasMany(AtividadeEtapa::class);
    }
}
