<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalmaoHistoricoResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'responsavel_id' => $this->responsavel_id,
            'calibre_id' => $this->calibre_id,
            'valor_pago' => 'R$ ' . number_format($this->valor_pago, 2, ',', '.'), // Ex.: R$ 5.000,00
            'peso_bruto' => number_format($this->peso_bruto, 3, ',', '.') . ' kg', // Ex.: 120,000 kg
            'peso_limpo' => number_format($this->peso_limpo, 3, ',', '.') . ' kg', // Ex.: 87,200 kg
            'aproveitamento' => number_format($this->aproveitamento, 2, ',', '.') . '%', // Ex.: 72,67%
            'desperdicio' => number_format($this->desperdicio, 3, ',', '.') . ' kg', // Ex.: 32,800 kg
            'unidade_id' => $this->unidade_id,
            'created_at' => $this->created_at->format('d/m/Y H:i'), // Ex.: 24/02/2025 19:25
            'updated_at' => $this->updated_at->format('d/m/Y H:i'),
            'calibre' => [
                'id' => $this->calibre->id,
                'nome' => $this->calibre->nome,
                'tipo' => $this->calibre->tipo,
            ],
            'responsavel' => [
                'id' => $this->responsavel->id,
                'name' => $this->responsavel->name,
                'email' => $this->responsavel->email,
                'cpf' => $this->responsavel->cpf,
            ],
        ];
    }
}
