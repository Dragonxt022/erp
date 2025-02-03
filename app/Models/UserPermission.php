<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'controle_estoque',
        'controle_saida_estoque',
        'gestao_equipe',
        'fluxo_caixa',
        'dre',
        'contas_pagar',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
