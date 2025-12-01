<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalmaoCalibre extends Model
{
    use HasFactory;

    protected $fillable = ['nome', 'tipo'];

    public function historicos()
    {
        return $this->hasMany(SalmaoHistorico::class, 'calibre_id');
    }
}
