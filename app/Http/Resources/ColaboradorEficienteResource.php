<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ColaboradorEficienteResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'responsavel_id' => $this->responsavel_id,
            'media_aproveitamento' => number_format($this->media_aproveitamento, 2, ',', '.') . '%', // Ex.: 74,50%
            'responsavel' => [
                'id' => $this->responsavel->id,
                'name' => $this->responsavel->name,
                'email' => $this->responsavel->email,
                'cpf' => $this->responsavel->cpf,
            ],
        ];
    }
}
