<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DefaultPaymentMethod extends Model
{
    use HasFactory;

    // Nome da tabela no banco
    protected $table = 'default_payment_methods';

    // Campos que podem ser preenchidos
    protected $fillable = [
        'nome',
        'tipo',
        'img_icon',
        'status',
    ];

    // app/Models/DefaultPaymentMethod.php

    public function unidadePaymentMethods()
    {
        return $this->hasMany(UnidadePaymentMethod::class, 'default_payment_method_id');
    }
}
