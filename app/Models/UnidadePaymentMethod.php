<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadePaymentMethod extends Model
{
    use HasFactory;

    // Nome da tabela
    protected $table = 'unidade_payment_methods';

    // Campos que podem ser preenchidos
    protected $fillable = [
        'unidade_id',
        'default_payment_method_id',
        'porcentagem',
        'status',
    ];

    // Relacionamento com a tabela `infor_unidade`
    public function unidade()
    {
        return $this->belongsTo(InforUnidade::class, 'unidade_id');
    }

    // Relacionamento com a tabela `default_payment_methods`
    public function defaultPaymentMethod()
    {
        return $this->belongsTo(DefaultPaymentMethod::class, 'default_payment_method_id');
    }
}
