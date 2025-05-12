<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Atividade extends Model
{
    protected $table = 'atividades';

    protected $fillable = [
        'name',
        'setor_id',
        'tempo_estimated',
        'profile_photo'
    ];

    protected $appends = ['profile_photo_url'];

    public function getProfilePhotoUrlAttribute()
    {
        if ($this->profile_photo && Storage::disk('public')->exists($this->profile_photo)) {
            return asset('storage/' . $this->profile_photo);
        }

        return asset('storage/images/no-imagem.jpg');
    }

    public function etapas()
    {
        return $this->hasMany(AtividadeEtapa::class);
    }
}
