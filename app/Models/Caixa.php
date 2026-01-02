<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Services\AnalyticService;

class Caixa extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::saved(function ($model) {
            AnalyticService::invalidateCache($model->unidade_id);
        });

        static::deleted(function ($model) {
            AnalyticService::invalidateCache($model->unidade_id);
        });
    }

    protected $fillable = [
        'unidade_id',
        'responsavel_id',
        'valor_inicial',
        'valor_final',
        'status',
        'motivo',
    ];

    // Relacionamento com a unidade
    public function unidade()
    {
        return $this->belongsTo(InforUnidade::class, 'unidade_id');
    }

    // Relacionamento com o usuário responsável
    public function responsavel()
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }

    // Relacionamento com a tabela de fechamento de caixas
    public function fechamentoCaixas()
    {
        return $this->hasMany(FechamentoCaixa::class);
    }

    // Relacionamento com a tabela de canal de vendas
    public function canaisVendas()
    {
        return $this->hasMany(CanalVenda::class);
    }

    // Relacionamento com a tabela de fluxo de caixa
    public function fluxosCaixa()
    {
        return $this->hasMany(FluxoCaixa::class);
    }

    // Relacionamento com histórico de métodos de pagamento
    public function historicoMetodosPagamento()
    {
        return $this->hasMany(HistoricoMetodoPagamento::class);
    }

    // Relacionamento com histórico de canais de vendas
    public function historicoCanaisVendas()
    {
        return $this->hasMany(HistoricoCanalVenda::class);
    }
}
