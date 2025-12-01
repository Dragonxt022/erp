<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ponto extends Model
{
    protected $table = 'pontos';

    protected $fillable = [
        'name',
        'pontos',
    ];
}
