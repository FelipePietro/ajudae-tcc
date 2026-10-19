<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class PessoaPrivateResource extends PessoaPublicResource
{
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'genero_pessoa' => $this->genero_pessoa,
            'dt_nasc' => $this->dt_nasc?->toDateString(),
            'email_pessoa' => $this->email_pessoa,
            'tele_pessoa' => $this->tele_pessoa,
            'cep_pessoa' => $this->cep_pessoa,
            'logradouro_pessoa' => $this->logradouro_pessoa,
            'compl_pessoa' => $this->compl_pessoa,
            'cidade_pessoa' => $this->cidade_pessoa,
            'bairro_pessoa' => $this->bairro_pessoa,
            'uf_pessoa' => $this->uf_pessoa,
            'login_pessoa' => $this->login_pessoa,
            'role_pessoa' => $this->role_pessoa,
            'status_validacao' => $this->status_validacao,
            'analise_assumida_em' => $this->analise_assumida_em?->toISOString(),
            'aprovado_em' => $this->aprovado_em?->toISOString(),
            'reprovado_definitivamente_em' => $this->reprovado_definitivamente_em?->toISOString(),
            'bloqueado_ate' => $this->bloqueado_ate?->toISOString(),
            'documentos' => DocumentoPessoaResource::collection($this->whenLoaded('documentos')),
            'exclusao_pendente' => $this->exclusao_pendente,
            'deletar_em' => $this->deletar_em?->toISOString(),
        ]);
    }
}
