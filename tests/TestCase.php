<?php

namespace Tests;

use App\Models\Ong;
use App\Models\Permissao;
use App\Models\Pessoa;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Hash;

abstract class TestCase extends BaseTestCase
{
    protected function pessoaPayload(array $overrides = []): array
    {
        return array_merge([
            'nm_pessoa' => 'Maria Voluntária',
            'genero_pessoa' => 'feminino',
            'cpf_pessoa' => '52998224725',
            'dt_nasc' => '1995-05-10',
            'rg_pessoa' => '123456789',
            'email_pessoa' => 'maria@example.com',
            'tele_pessoa' => '11999999999',
            'cep_pessoa' => '01001000',
            'logradouro_pessoa' => 'Praça da Sé',
            'compl_pessoa' => null,
            'cidade_pessoa' => 'São Paulo',
            'bairro_pessoa' => 'Sé',
            'uf_pessoa' => 'SP',
            'login_pessoa' => 'maria.voluntaria',
            'senha_pessoa' => 'senha-segura',
            'senha_pessoa_confirmation' => 'senha-segura',
            'antecedentes_pessoa_link' => 'documentos/antecedentes.pdf',
            'cnh_pessoa_link' => null,
            'rg_pessoa_link' => 'documentos/rg.pdf',
            'pfp_pessoa_link' => 'imagens/maria.jpg',
            'aceitou_termos' => true,
        ], $overrides);
    }

    protected function ongPayload(array $overrides = []): array
    {
        return array_merge([
            'login_ong' => 'ong.exemplo',
            'nome_fantasia' => 'ONG Exemplo',
            'cnpj_ong' => '11222333000181',
            'email_ong' => 'contato@ong.test',
            'senha_ong' => 'senha-segura',
            'senha_ong_confirmation' => 'senha-segura',
            'tel_ong' => '1133334444',
            'cep_ong' => '01001000',
            'logradouro_ong' => 'Praça da Sé',
            'bairro_ong' => 'Sé',
            'cidade_ong' => 'São Paulo',
            'uf_ong' => 'SP',
            'descricao_ong' => 'Organização de apoio comunitário.',
            'site_url_ong' => 'https://ong.test',
            'pfp_ong_link' => 'imagens/ong.jpg',
            'nome_responsavel_ong' => 'João Responsável',
            'cpf_responsavel_ong' => '11144477735',
            'rg_responsavel_ong_link' => 'documentos/rg-responsavel.pdf',
            'email_responsavel_ong' => 'responsavel@ong.test',
            'tel_responsavel_ong' => '11988887777',
            'aceitou_termos' => true,
        ], $overrides);
    }

    protected function createPessoa(string $role = 'voluntario', array $overrides = []): Pessoa
    {
        $data = $this->pessoaPayload($overrides);
        $statusValidacao = $data['status_validacao'] ?? 'aprovado';
        $adminMaster = (bool) ($data['admin_master'] ?? false);
        unset($data['status_validacao'], $data['admin_master']);
        unset($data['senha_pessoa_confirmation'], $data['aceitou_termos']);
        $data['senha_pessoa'] = Hash::make($data['senha_pessoa']);

        $pessoa = Pessoa::create($data);
        $pessoa->forceFill([
            'role_pessoa' => $role,
            'status_validacao' => $statusValidacao,
            'admin_master' => $adminMaster,
            'aprovado_em' => $statusValidacao === 'aprovado' ? now() : null,
        ])->save();

        return $pessoa;
    }

    protected function createOng(string $status = 'aguardando aprovação', array $overrides = []): Ong
    {
        $data = $this->ongPayload($overrides);
        unset($data['senha_ong_confirmation'], $data['aceitou_termos']);
        $data['senha_ong'] = Hash::make($data['senha_ong']);

        $ong = Ong::create($data);
        $ong->forceFill(['status_ong' => $status])->save();

        return $ong;
    }

    protected function grantPermission(Pessoa $pessoa, string $nome): Permissao
    {
        $permissao = Permissao::firstOrCreate(['nome' => $nome]);
        $pessoa->permissoes()->syncWithoutDetaching([$permissao->permissao_id]);

        return $permissao;
    }
}
