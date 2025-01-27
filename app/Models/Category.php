<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['nome', 'descricao', 'unidade_id']; // Incluindo unidade_id

    // Relacionamento de "pertence a" com a unidade
    public function unidade()
    {
        return $this->belongsTo(InforUnidade::class, 'unidade_id');
    }
}
