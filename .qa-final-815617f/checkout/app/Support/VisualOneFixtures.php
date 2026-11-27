<?php

namespace App\Support;

/** Synthetic presentation fixtures only. No model, API, credential or file access. */
final class VisualOneFixtures
{
    public static function make(string $actor = 'pessoa', mixed $scenario = null): array
    {
        $ong = $actor === 'ong';
        $scenarios = $ong ? [
            'inicio' => 'Começar cadastro', 'parcial' => 'Cadastro parcial', 'pendente' => 'Aguardando aprovação',
            'correcao' => 'Revisar cadastro', 'aprovada' => 'ONG aprovada', 'recusada' => 'ONG recusada',
        ] : [
            'inicio' => 'Começar cadastro', 'parcial' => 'Cadastro parcial', 'aguardando' => 'Aguardando validação',
            'em-analise' => 'Em análise', 'correcao' => 'Correção documental', 'aprovado' => 'Pessoa aprovada',
            'sem-preferencias' => 'Sem preferências', 'reprovado' => 'Reprovado', 'bloqueado' => 'Reenvio bloqueado',
            'definitivo' => 'Reprovação definitiva', 'antecedentes-vencidos' => 'Renovação de antecedentes', 'publico' => 'Perfil público',
        ];
        $scenario = is_string($scenario) && array_key_exists($scenario, $scenarios) ? $scenario : ($ong ? 'pendente' : 'aprovado');
        $status = $ong ? match ($scenario) {
            'aprovada' => 'ativo', 'correcao', 'recusada' => 'reprovado', default => 'aguardando aprovação',
        } : match ($scenario) {
            'inicio', 'parcial', 'aguardando' => 'aguardando_validacao', 'em-analise' => 'em_analise',
            'correcao' => 'correcao_solicitada', 'reprovado', 'bloqueado' => 'reprovado',
            'definitivo' => 'reprovado_definitivamente', default => 'aprovado',
        };
        $statuses = [
            'aguardando_validacao' => ['Aguardando validação', 'warning', 'Seu cadastro e seus documentos aguardam avaliação.'],
            'em_analise' => ['Em análise', 'info', 'A análise está em andamento. Você pode acompanhar o próprio cadastro.'],
            'correcao_solicitada' => ['Correção solicitada', 'warning', 'Revise o item indicado antes de solicitar nova análise. Correção não conta como reprovação.'],
            'aprovado' => ['Pessoa aprovada', 'success', 'Seu cadastro está aprovado para participar das atividades.'],
            'reprovado' => ['Reprovado', 'danger', 'Confira a orientação disponível e as condições de reapresentação.'],
            'reprovado_definitivamente' => ['Reprovado definitivamente', 'danger', 'O cadastro foi encerrado pela administração. Novo envio indisponível.'],
            'aguardando aprovação' => ['Aguardando aprovação', 'warning', 'Cadastro institucional em avaliação. Cadastro não significa aprovação para operar eventos.'],
            'ativo' => ['ONG aprovada', 'success', 'Cadastro institucional aprovado. A conta representa o responsável da organização.'],
        ];
        $account = $ong ? [
            'nome_fantasia' => PublicProfileFixtures::ong()['usuario']['nome'], 'cnpj_ong' => '00000000000000',
            'email_ong' => 'ong@example.test', 'tel_ong' => '00000000000', 'login_ong' => 'ong.exemplo',
            'cep_ong' => '00000000', 'logradouro_ong' => 'Rua de Exemplo, 0', 'bairro_ong' => 'Bairro Exemplo',
            'cidade_ong' => 'Guarulhos', 'uf_ong' => 'SP', 'descricao_ong' => 'Uma organização dedicada a ações de cuidado com a comunidade.',
            'site_url_ong' => 'https://exemplo.invalid', 'nome_responsavel_ong' => 'Representante de Exemplo',
            'cpf_responsavel_ong' => '00000000000', 'email_responsavel_ong' => 'responsavel@example.test',
            'tel_responsavel_ong' => '00000000000', 'status_ong' => $status,
        ] : [
            'nm_pessoa' => PublicProfileFixtures::pessoa()['nome_user'], 'genero_pessoa' => 'prefiro não dizer', 'dt_nasc' => '1995-05-10',
            'cpf_pessoa' => '00000000000', 'rg_pessoa' => 'DEMO-000', 'email_pessoa' => 'pessoa@example.test',
            'tele_pessoa' => '00000000000', 'login_pessoa' => 'alex.exemplo', 'cep_pessoa' => '00000000',
            'logradouro_pessoa' => 'Rua de Exemplo, 0', 'bairro_pessoa' => 'Bairro Exemplo', 'cidade_pessoa' => 'Guarulhos',
            'uf_pessoa' => 'SP', 'compl_pessoa' => '', 'role_pessoa' => 'voluntario', 'status_validacao' => $status,
        ];
        $empty = in_array($scenario, ['inicio', 'sem-preferencias'], true);
        $documents = [];
        foreach (['documento_identidade' => 'Documento de identidade', 'antecedentes_criminais' => 'Certidão de antecedentes', 'cnh' => 'CNH'] as $type => $label) {
            $documentStatus = $type === 'cnh' || in_array($scenario, ['inicio', 'parcial'], true) ? null
                : ($status === 'aprovado' ? 'aprovado' : (in_array($scenario, ['correcao', 'reprovado', 'bloqueado', 'definitivo'], true) && $type === 'documento_identidade' ? 'rejeitado' : 'pendente'));
            $documents[] = ['tipo' => $type, 'label' => $label, 'required' => $type !== 'cnh', 'status' => $documentStatus,
                'rejection_reason' => $documentStatus === 'rejeitado' ? 'O documento precisa estar legível e completo.' : null,
                'expires_at' => $type === 'antecedentes_criminais' && $scenario === 'antecedentes-vencidos' ? '2026-10-01' : null];
        }

        return ['demo' => [
            'actor' => $actor, 'scenario' => $scenario, 'scenarios' => $scenarios, 'account' => $account,
            'status' => $status, 'statusMeta' => $statuses[$status], 'private' => $scenario !== 'publico',
            'blocked' => $scenario === 'bloqueado', 'terminal' => $scenario === 'definitivo',
            'canResubmit' => ! $ong && in_array($scenario, ['correcao', 'reprovado'], true),
            'reason' => in_array($scenario, ['correcao', 'reprovado', 'bloqueado', 'definitivo', 'recusada'], true) ? 'Confira a identificação cadastral e a legibilidade dos documentos antes de continuar.' : null,
            'ongCorrectionGap' => $ong && $scenario === 'correcao',
            'documents' => $documents,
            'catalogs' => ['habilidades' => [1 => 'Comunicação', 2 => 'Organização', 3 => 'Tecnologia'],
                'causas' => [1 => 'Meio ambiente', 2 => 'Educação', 3 => 'Apoio à comunidade'],
                'recursos' => [1 => 'Notebook', 2 => 'Veículo próprio', 3 => 'Ferramentas']],
            'preferences' => ['habilidades' => $empty ? [] : [1 => 3, 2 => 4], 'causas' => $empty ? [] : [1, 2], 'recursos' => $empty ? [] : [1]],
            'seed' => ! in_array($scenario, ['inicio'], true),
        ]];
    }

    /** Form definitions use existing API field names; files/passwords never get a name. */
    public static function sections(string $actor): array
    {
        $f = fn ($key, $label, $type = 'text', $required = true, $hint = '') => compact('key', 'label', 'type', 'required', 'hint');
        if ($actor === 'ong') {
            return [
                ['title' => 'A instituição', 'intro' => 'Comece pela identidade da organização.', 'fields' => [
                    $f('nome_fantasia', 'Nome fantasia'), $f('cnpj_ong', 'CNPJ', 'text', true, 'Informe somente os 14 dígitos.'),
                    $f('descricao_ong', 'Descrição da organização', 'textarea'), $f('site_url_ong', 'Site', 'url', false)]],
                ['title' => 'Contato e endereço', 'intro' => 'Informações institucionais, separadas das do responsável.', 'fields' => [
                    $f('email_ong', 'E-mail institucional', 'email'), $f('tel_ong', 'Telefone institucional'),
                    $f('cep_ong', 'CEP'), $f('logradouro_ong', 'Logradouro e número'), $f('bairro_ong', 'Bairro'), $f('cidade_ong', 'Cidade'), $f('uf_ong', 'UF')]],
                ['title' => 'Responsável', 'intro' => 'A conta da ONG representa seu responsável. Não é necessário outro cadastro Pessoa.', 'fields' => [
                    $f('nome_responsavel_ong', 'Nome do responsável'), $f('cpf_responsavel_ong', 'CPF do responsável', 'text', true, 'Informe os 11 dígitos do CPF.'),
                    $f('email_responsavel_ong', 'E-mail do responsável', 'email'), $f('tel_responsavel_ong', 'Telefone do responsável')]],
                ['title' => 'Documentação', 'intro' => 'Confira a documentação. Nenhum arquivo será enviado nesta revisão.', 'special' => 'ong-documents'],
                ['title' => 'Acesso', 'intro' => 'A senha será descartada ao sair desta etapa.', 'special' => 'credentials'],
                ['title' => 'Revisão e termos', 'intro' => 'Confira o resumo antes de continuar.', 'special' => 'review'],
            ];
        }

        return [
            ['title' => 'Identificação', 'intro' => 'O Ajudae é destinado a Pessoas com 18 anos ou mais.', 'fields' => [
                $f('nm_pessoa', 'Nome completo'), $f('dt_nasc', 'Data de nascimento', 'date'),
                $f('genero_pessoa', 'Gênero', 'gender'), $f('cpf_pessoa', 'CPF', 'text', true, 'Informe os 11 dígitos do CPF. Confira a identificação antes de continuar.'),
                $f('rg_pessoa', 'RG ou identificação equivalente')]],
            ['title' => 'Contato e endereço', 'intro' => 'Seus dados de contato pertencem à área privada.', 'fields' => [
                $f('email_pessoa', 'E-mail', 'email'), $f('tele_pessoa', 'Telefone'), $f('cep_pessoa', 'CEP'),
                $f('logradouro_pessoa', 'Logradouro e número'), $f('bairro_pessoa', 'Bairro'), $f('cidade_pessoa', 'Cidade'),
                $f('uf_pessoa', 'UF'), $f('compl_pessoa', 'Complemento', 'text', false)]],
            ['title' => 'Preferências', 'intro' => 'Conte como você gostaria de contribuir. Você pode revisar suas preferências depois.', 'special' => 'preferences'],
            ['title' => 'Documentos', 'intro' => 'Identidade e antecedentes são obrigatórios; CNH é opcional, conforme a atividade.', 'special' => 'documents'],
            ['title' => 'Acesso', 'intro' => 'Defina seu login. A senha não será mantida na próxima etapa.', 'special' => 'credentials'],
            ['title' => 'Revisão e termos', 'intro' => 'Confira os dados de apresentação, sem mostrar identificadores ou documentos.', 'special' => 'review'],
        ];
    }
}
