<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ControleSaldoEstoque extends Model
{
    use HasFactory;

    protected $table = 'controle_saldo_estoques';

    protected $fillable = [
        'ajuste_saldo',
        'data_ajuste',
        'motivo_ajuste',
        'unidade_id',
        'responsavel_id',
    ];

    protected $casts = [
        'data_ajuste' => 'datetime',
    ];

    /**
     * Relacionamento com a unidade.
     */
    public function unidade()
    {
        return $this->belongsTo(InforUnidade::class, 'unidade_id');
    }

    /**
     * Relacionamento com o usuário responsável.
     */
    public function responsavel()
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }
}
