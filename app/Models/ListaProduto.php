<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListaProduto extends Model
{
    protected $fillable = [
        'nome',
        'profile_photo',
    ];
}
