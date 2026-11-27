<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class OngPrivateResource extends OngPublicResource
{
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'cnpj_ong' => $this->cnpj_ong,
            'email_ong' => $this->email_ong,
            'tel_ong' => $this->tel_ong,
            'logradouro_ong' => $this->logradouro_ong,
            'cep_ong' => $this->cep_ong,
            'bairro_ong' => $this->bairro_ong,
            'nome_responsavel_ong' => $this->nome_responsavel_ong,
            'email_responsavel_ong' => $this->email_responsavel_ong,
            'tel_responsavel_ong' => $this->tel_responsavel_ong,
            'login_ong' => $this->login_ong,
            'exclusao_pendente' => $this->exclusao_pendente,
            'deletar_em' => $this->deletar_em?->toISOString(),
        ]);
    }
}
