<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Services\AnalyticService;

class ContaAPagar extends Model
{
    protected static function booted()
    {
        static::saved(function ($model) {
            AnalyticService::invalidateCache($model->unidade_id);
        });

        static::deleted(function ($model) {
            AnalyticService::invalidateCache($model->unidade_id);
        });
    }
    protected $table = 'contas_a_pagares';

    protected $fillable = [
        'nome',
        'valor',
        'emitida_em',
        'vencimento',
        'descricao',
        'arquivo',
        'dias_lembrete',
        'status',
        'unidade_id',
        'categoria_id',
        'historico'
    ];

    protected $casts = [
        'historico' => 'array'
    ];

    /**
     * Registra um evento no histórico da conta.
     * 
     * @param string $acao Descrição da ação (ex: 'criacao', 'alteracao_status')
     * @param string|null $statusNovo Novo status, se aplicável
     * @param string|null $statusAnterior Status anterior, se aplicável
     * @param string|null $usuario Nome do usuário que realizou a ação
     */
    public function registrarLog($acao, $statusNovo = null, $statusAnterior = null, $usuario = null)
    {
        $usuario = $usuario ?? (\Illuminate\Support\Facades\Auth::check() ? \Illuminate\Support\Facades\Auth::user()->name : 'Sistema');

        $historico = $this->historico ?? [];

        $historico[] = [
            'data' => now()->format('Y-m-d H:i:s'),
            'acao' => $acao,
            'status_novo' => $statusNovo,
            'status_anterior' => $statusAnterior,
            'usuario' => $usuario,
        ];

        $this->historico = $historico;
        $this->save();
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function unidade()
    {
        return $this->belongsTo(InforUnidade::class, 'unidade_id');
    }
}
