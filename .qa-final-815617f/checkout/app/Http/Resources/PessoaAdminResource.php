<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class PessoaAdminResource extends PessoaPrivateResource
{
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'cpf_pessoa' => $this->cpf_pessoa,
            'rg_pessoa' => $this->rg_pessoa,
            'admin_master' => $this->admin_master,
            'admin_analisando_id' => $this->admin_analisando_id,
            'documentos' => DocumentoPessoaResource::collection($this->whenLoaded('documentos')),
            'historico_validacao' => HistoricoValidacaoPessoaResource::collection($this->whenLoaded('historicoValidacao')),
        ]);
    }
}
