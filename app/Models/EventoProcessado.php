<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventoProcessado extends Model
{
    protected $table = 'eventos_processados';

    protected $primaryKey = 'delivery_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'delivery_id',
        'event_type',
        'status',
        'processando_em',
        'processado_em',
    ];

    protected $casts = [
        'status' => 'boolean',
        'processando_em' => 'datetime',
        'processado_em' => 'datetime',
    ];
}
