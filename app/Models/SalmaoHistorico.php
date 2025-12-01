<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalmaoHistorico extends Model
{
    use HasFactory;

    protected $fillable = [
        'responsavel_id',
        'calibre_id',
        'valor_pago',
        'peso_bruto',
        'peso_limpo',
        'aproveitamento',
        'desperdicio',
        'unidade_id'
    ];

    public function calibre()
    {
        return $this->belongsTo(SalmaoCalibre::class, 'calibre_id');
    }

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'fornecedor_id');
    }

    public function responsavel()
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }

    public function unidade()
    {
        return $this->belongsTo(InforUnidade::class, 'unidade_id');
    }
}
