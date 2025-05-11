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
        'global',
        'tipo',
        'setor_id',
    ];

    protected $appends = ['tempo'];

    // Acessor para o tempo formatado
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

    // Relacionamento com o usuário que recebe a notificação
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relacionamento com o setor (opcional)
    public function setor()
    {
        return $this->belongsTo(Operacional::class, 'setor_id');
    }

    // Método para verificar se a notificação é global
    public function isGlobal()
    {
        return $this->global;
    }

    // Método para verificar se a notificação é específica de um setor
    public function isSetor()
    {
        return $this->setor_id !== null;
    }
}
