<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Operacional extends Model
{
    protected $table = 'operacionais';

    protected $fillable = [
        'name',
        'profile_photo',
    ];

    protected $appends = ['profile_photo_url'];

    public function getProfilePhotoUrlAttribute()
    {
        if ($this->profile_photo && Storage::disk('public')->exists($this->profile_photo)) {
            return asset('storage/' . $this->profile_photo);
        }

        // Caminho da imagem padrão
        return asset('storage/images/no-imagem.webp');
    }

    public function notificacoes(): HasMany
    {
        return $this->hasMany(Notificacao::class, 'setor_id');
    }

    
}
