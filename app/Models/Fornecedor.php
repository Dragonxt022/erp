<?php

// app/Models/Fornecedor.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fornecedor extends Model
{
    use HasFactory;

    protected $table = 'fornecedores'; // Nome explícito da tabela

    protected $fillable = [
        'cnpj',
        'razao_social',
        'email',
        'whatsapp',
        'estado',
    ];

    /**
     * Relacionamento com a tabela precos_fornecedores
     */
    public function precos()
    {
        return $this->hasMany(PrecoFornecedore::class, 'fornecedor_id', 'id');
    }
}
