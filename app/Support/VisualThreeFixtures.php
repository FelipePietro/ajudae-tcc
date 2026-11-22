<?php

namespace App\Support;

/** Synthetic presentation only. Never authentication, API responses or persistence. */
final class VisualThreeFixtures
{
    public const SCENARIOS = [
        'nao-elegivel', 'elegivel', 'pendente', 'em-analise', 'aprovada', 'reprovada', 'nova-solicitacao',
        'bloqueado', 'apadrinhado', 'cadastro-pendente', 'independente-vazio', 'independente-eventos',
        'convite-pendente', 'convite-recusado', 'convite-bloqueado', 'vinculado', 'vinculado-sem-eventos', 'vinculo-livre', 'ong-vazia', 'ong-convites',
        'ong-vinculos', 'evento-iniciado', 'encerrado', 'independente-dois', 'independente-varios', 'vinculado-varios', 'carregando', 'falha',
    ];

    public static function scenario(string $context = 'pessoa'): string
    {
        $value = request()->query('jornada');

        return is_string($value) && in_array($value, self::availableScenarios($context), true) ? $value : match ($context) {
            'ong' => 'ong-vinculos', 'organizador' => 'independente-eventos',
            'organizador-vinculado' => 'vinculado', default => 'nao-elegivel',
        };
    }

    public static function availableScenarios(string $context): array
    {
        return array_merge(match ($context) {
            'pessoa' => ['nao-elegivel', 'elegivel', 'pendente', 'em-analise', 'aprovada', 'reprovada', 'nova-solicitacao', 'bloqueado', 'apadrinhado', 'cadastro-pendente'],
            'ong' => ['ong-vazia', 'ong-convites', 'ong-vinculos', 'evento-iniciado', 'encerrado'],
            default => ['independente-vazio', 'independente-eventos', 'convite-pendente', 'convite-recusado', 'convite-bloqueado', 'vinculado', 'vinculado-sem-eventos', 'vinculo-livre', 'evento-iniciado', 'encerrado', 'independente-dois', 'independente-varios', 'vinculado-varios'],
        }, ['carregando', 'falha']);
    }

    public static function person(): array
    {
        $public = PublicProfileFixtures::pessoa();

        return ['pessoa_id' => $public['id_user'], 'nm_pessoa' => $public['nome_user'],
            'role_pessoa' => 'organizador', 'status_validacao' => 'aprovado'];
    }

    public static function ownership(int $id): ?array
    {
        return match ($id) {
            1 => ['evento_id' => 1, 'nm_evento' => 'Limpeza de Parques', 'ong_id' => 1, 'pessoa_id' => null, 'responsavel_pessoa_id' => 1],
            7 => ['evento_id' => 7, 'nm_evento' => 'Oficina de apoio comunitário', 'descricao_evento' => 'Organização de ações de apoio e acolhimento da comunidade.', 'cat_evento_id' => 4, 'ong_id' => 1, 'pessoa_id' => null, 'responsavel_pessoa_id' => 2],
            8 => ['evento_id' => 8, 'nm_evento' => 'Mutirão independente de leitura', 'descricao_evento' => 'Encontro comunitário para organizar livros e apoiar atividades de leitura.', 'cat_evento_id' => 2, 'ong_id' => null, 'pessoa_id' => 1, 'responsavel_pessoa_id' => 1],
            9 => ['evento_id' => 9, 'nm_evento' => 'Roda de leitura comunitária', 'descricao_evento' => 'Leitura e troca de experiências na comunidade.', 'cat_evento_id' => 2, 'ong_id' => null, 'pessoa_id' => 1, 'responsavel_pessoa_id' => 1],
            10 => ['evento_id' => 10, 'nm_evento' => 'Organização de biblioteca solidária', 'ong_id' => null, 'pessoa_id' => 1, 'responsavel_pessoa_id' => 1],
            14 => ['evento_id' => 14, 'nm_evento' => 'Encontro de contadores de histórias', 'ong_id' => null, 'pessoa_id' => 1, 'responsavel_pessoa_id' => 1],
            default => null,
        };
    }

    /** Synthetic known lookup aliases; no public username or identity search API exists. */
    public static function invitationIds(): array
    {
        $value = request()->query('convites');

        return is_string($value) && preg_match('/^[123](,[123]){0,2}$/D', $value) ? array_values(array_unique(array_map('intval', explode(',', $value)))) : [];
    }

    public static function make(string $context = 'pessoa'): array
    {
        $scenario = self::scenario($context);
        $person = self::person();
        if ($context === 'pessoa' && $scenario !== 'aprovada') {
            $person['role_pessoa'] = 'voluntario';
        }
        $people = [$person, ['pessoa_id' => 2, 'nm_pessoa' => 'Sara de Exemplo', 'role_pessoa' => 'organizador', 'status_validacao' => 'aprovado'],
            ['pessoa_id' => 3, 'nm_pessoa' => 'Rafael de Exemplo', 'role_pessoa' => 'organizador', 'status_validacao' => 'aprovado']];
        foreach ($people as &$entry) {
            $entry['demo_username'] = [1 => 'lucas.pereira', 2 => 'sara.exemplo', 3 => 'rafael.exemplo'][$entry['pessoa_id']];
            $entry['demo_email'] = $entry['demo_username'].'@example.test';
        } unset($entry);
        $organizations = array_map(fn ($id) => ['ong_id' => $id, 'nome_fantasia' => PublicProfileFixtures::ong($id)['usuario']['nome'], 'status_ong' => 'ativo'], [1, 2]);
        $status = match ($scenario) {
            'pendente' => 'pendente', 'em-analise' => 'em_analise', 'aprovada' => 'aprovada',
            'reprovada', 'nova-solicitacao' => 'reprovada', default => null,
        };
        $criteria = [
            ['label' => 'Cadastro aprovado e função de voluntário', 'value' => $scenario === 'cadastro-pendente' ? 'Aguardando validação' : 'Cadastro aprovado', 'met' => $scenario !== 'cadastro-pendente'],
            ['label' => 'Ao menos 5 participações confirmadas', 'value' => $scenario === 'nao-elegivel' ? '3 de 5 participações' : '5 de 5 participações', 'met' => $scenario !== 'nao-elegivel'],
            ['label' => 'Confiabilidade formada de ao menos 80', 'value' => '90', 'met' => true],
            ['label' => 'Média de avaliações de ao menos 4,0', 'value' => '4,5 de 5', 'met' => true],
            ['label' => 'Sem bloqueio ativo', 'value' => $scenario === 'bloqueado' ? 'Bloqueio ativo' : 'Sem bloqueio', 'met' => $scenario !== 'bloqueado'],
            ['label' => 'Sem apadrinhados ativos', 'value' => $scenario === 'apadrinhado' ? '1 acompanhamento ativo' : 'Nenhum acompanhamento ativo', 'met' => $scenario !== 'apadrinhado'],
            ['label' => 'Sem solicitação aberta', 'value' => in_array($status, ['pendente', 'em_analise'], true) ? 'Análise ainda não concluída' : 'Nenhuma solicitação aberta', 'met' => ! in_array($status, ['pendente', 'em_analise'], true)],
            ['label' => 'Respeitar 30 dias completos após uma recusa', 'value' => $scenario === 'reprovada' ? 'Disponível em 20 dias' : 'Intervalo cumprido ou não aplicável', 'met' => $scenario !== 'reprovada'],
        ];
        $eligible = ! in_array(false, array_column($criteria, 'met'), true) && $status !== 'aprovada';
        $linked = in_array($scenario, ['vinculado', 'vinculado-varios', 'vinculado-sem-eventos', 'vinculo-livre', 'ong-vinculos', 'evento-iniciado'], true)
            || ($context === 'organizador-vinculado' && ! in_array($scenario, ['encerrado', 'convite-pendente', 'convite-recusado', 'convite-bloqueado'], true));
        $links = [];
        if ($scenario !== 'ong-vazia') {
            $linkStatus = match (true) {
                $scenario === 'encerrado' => 'encerrado', $scenario === 'convite-recusado' => 'recusado', $linked => 'ativo', default => 'convite_pendente',
            };
            $links[] = ['vinculo_ong_organizador_id' => 10, 'ong_id' => 1, 'pessoa_id' => 1, 'status' => $linkStatus];
            if ($context === 'ong') {
                $links[] = ['vinculo_ong_organizador_id' => 20, 'ong_id' => 1, 'pessoa_id' => 2, 'status' => $scenario === 'ong-vinculos' ? 'ativo' : 'convite_pendente'];
                $links[] = ['vinculo_ong_organizador_id' => 30, 'ong_id' => 1, 'pessoa_id' => 3, 'status' => 'encerrado'];
            } else {
                $links[] = ['vinculo_ong_organizador_id' => 40, 'ong_id' => 2, 'pessoa_id' => 1, 'status' => 'recusado'];
            }
        }
        foreach (self::invitationIds() as $personId) {
            if (! collect($links)->contains(fn ($link) => $link['pessoa_id'] === $personId && in_array($link['status'], ['ativo', 'convite_pendente']))) {
                $links[] = ['vinculo_ong_organizador_id' => 50 + $personId, 'ong_id' => 1, 'pessoa_id' => $personId, 'status' => 'convite_pendente'];
            }
        }
        $events = [];
        $eventIds = $scenario === 'independente-varios' ? [1, 7, 8, 9, 10, 14] : ($scenario === 'independente-dois' ? [1, 7, 8, 9] : [1, 7, 8]);
        foreach ($eventIds as $id) {
            $event = self::ownership($id);
            if ($event['ong_id'] !== null && ! count(array_filter($links, fn ($link) => $link['pessoa_id'] === $event['responsavel_pessoa_id'] && $link['status'] === 'ativo'))) {
                $event['responsavel_pessoa_id'] = null;
            }
            $event['status_evento'] = $scenario === 'vinculo-livre' || ($id === 8 && $scenario !== 'independente-eventos' && $scenario !== 'convite-bloqueado') ? 'finalizado' : 'ativo';
            if ($scenario === 'vinculado-sem-eventos' && $id !== 8) {
                $event['responsavel_pessoa_id'] = null;
            }
            if ($scenario === 'vinculado-varios' && $id === 7) {
                $event['responsavel_pessoa_id'] = 1;
            }
            if (in_array($scenario, ['independente-dois', 'independente-varios']) && $id === 8) {
                $event['status_evento'] = 'ativo';
            }
            if ($id === 9) {
                $event['status_evento'] = 'aguardando a confirmação';
            }
            if ($id === 10) {
                $event['status_evento'] = 'reprovado';
            }
            if ($id === 14) {
                $event['status_evento'] = 'finalizado';
            }
            $event['started'] = $scenario === 'evento-iniciado' && $id === 1; // UI-only timing fact; API source is actual Agenda dates.
            $events[] = $event;
        }
        if ($scenario === 'independente-vazio') {
            $events = array_values(array_filter($events, fn ($event) => $event['pessoa_id'] !== 1));
        }
        $acting = $linked ? 'organizador-vinculado' : 'organizador';
        $visible = array_values(array_filter($events, fn ($event) => $context === 'ong' ? $event['ong_id'] === 1 : ($linked
            ? $event['ong_id'] === 1 && $event['responsavel_pessoa_id'] === 1
            : $event['ong_id'] === null && $event['pessoa_id'] === 1)));
        $blockedIndependent = count(array_filter($events, fn ($event) => $event['pessoa_id'] === 1 && ! in_array($event['status_evento'], ['cancelado', 'finalizado'], true))) > 0;

        return ['scenario' => $scenario, 'context' => $context, 'person' => $person, 'people' => $people, 'organizations' => $organizations,
            'criteria' => $criteria, 'eligible' => $eligible, 'promotion' => ['status' => $status, 'motivo_decisao' => $status === 'reprovada' ? 'Reforce seu histórico de participação antes de uma nova solicitação.' : null],
            'links' => $links, 'events' => $events, 'visibleEvents' => $visible, 'actingContext' => $acting, 'linked' => $linked,
            'blockedIndependent' => $blockedIndependent, 'loading' => $scenario === 'carregando', 'error' => $scenario === 'falha'];
    }

    /** Presentation guard matching ownership/responsibility; never a server policy. */
    public static function canManage(int $id, string $context): bool
    {
        $work = self::make($context);

        return in_array($context, ['ong', 'organizador', 'organizador-vinculado'], true)
            && in_array($id, array_column($work['visibleEvents'], 'evento_id'), true);
    }
}
