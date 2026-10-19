<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class OngAdminResource extends OngPrivateResource
{
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'cpf_responsavel_ong' => $this->cpf_responsavel_ong,
            'rg_responsavel_ong_link' => $this->rg_responsavel_ong_link,
        ]);
    }
}
