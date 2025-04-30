<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Notificacao extends Model
{
    use HasFactory;

    protected $table = 'notificacoes';

    protected $fillable = [
        'user_id',
        'titulo',
        'mensagem',
        'lida',
        'lida_em',
    ];

    protected $appends = ['tempo'];


    public function getTempoAttribute()
    {
        $createdAt = Carbon::parse($this->created_at);
        $now = Carbon::now();
    
        $diffInSeconds = $createdAt->diffInSeconds($now);
    
        if ($diffInSeconds < 60) {
            return 'Agora';
        }
    
        $diffInMinutes = floor($diffInSeconds / 60);
        if ($diffInMinutes < 60) {
            return $diffInMinutes . ' minutos atrás';
        }
    
        $diffInHours = floor($diffInMinutes / 60);
        if ($diffInHours < 24) {
            return $diffInHours . ' horas atrás';
        }
    
        return $createdAt->format('d/m/Y H:i');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


}
