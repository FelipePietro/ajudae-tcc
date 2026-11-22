<?php

namespace App\Support;

use Carbon\Carbon;

/** Local synthetic event data; never models, database records or API responses. */
final class VisualTwoFixtures
{
    /** Demo-only lookup: accept a catalogued Event belonging to this public ONG. */
    public static function profileOrigin(mixed $origin, int $organization): ?int
    {
        $ids = array_map('strval', array_column(self::feedLegacy()['eventos'], 'evento_id'));
        $ids[] = '7'; // Public institutional example introduced by organizer journeys.
        if (! is_string($origin) || ! in_array($origin, $ids, true)) {
            return null;
        }

        $event = self::page('detail', null, (int) $origin)['evento'];

        return $event->ong?->ong_id === $organization ? (int) $origin : null;
    }

    public const SCENARIOS = ['publicado', 'unica', 'pendente', 'reanalise', 'reprovado', 'cancelado', 'finalizado', 'lotada', 'janela-fechada', 'candidatura-pendente', 'candidatura-aprovada', 'candidatura-rejeitada', 'desistencia', 'sem-candidatos', 'vazio', 'carregando', 'falha', 'pessoa-pendente', 'visitante', 'organizador', 'agenda-cancelada', 'agenda-finalizada', 'desistencia-tardia', 'isencao'];

    public static function make(mixed $scenario = null, int $id = 1): array
    {
        $scenario = is_string($scenario) && in_array($scenario, self::SCENARIOS, true) ? $scenario : 'publicado';
        $status = match ($scenario) {
            'pendente', 'reanalise' => 'aguardando a confirmação', 'reprovado' => 'reprovado', 'cancelado' => 'cancelado', 'finalizado' => 'finalizado', default => 'ativo'
        };
        $start = now('America/Sao_Paulo')->addDays(7)->startOfDay()->setHour(9);
        if ($scenario === 'janela-fechada') {
            $start = now('America/Sao_Paulo')->addMinutes(45);
        }
        if ($scenario === 'desistencia-tardia') {
            $start = now('America/Sao_Paulo')->addHours(12);
        }
        $subscription = match ($scenario) {
            'candidatura-pendente' => 'pendente', 'candidatura-aprovada', 'desistencia-tardia', 'isencao' => 'aprovado', 'candidatura-rejeitada' => 'reprovado', 'desistencia' => 'desistente', default => null
        };
        $agendas = [];
        foreach (range(1, $scenario === 'unica' ? 1 : 2) as $n) {
            $date = $start->copy()->addDays($n - 1);
            $agendas[] = ['agenda_id' => $id * 100 + $n, 'evento_id' => $id, 'data_inicio' => $date->toIso8601String(),
                'data_fim' => $date->copy()->addHours(2)->toIso8601String(), 'vagas_agenda' => 2,
                'status_ativo' => match ($scenario) {
                    'agenda-cancelada', 'cancelado' => 'cancelado', 'agenda-finalizada', 'finalizado' => 'finalizado', default => 'ativo'
                },
                // Presentation aggregates: future source is actual inscriptions, not new Agenda columns.
                'ocupadas' => $scenario === 'lotada' ? 2 : ($n === 1 && $scenario !== 'sem-candidatos' ? 1 : 0),
                'inscricao' => $n === 1 ? $subscription : null,
                'desistencia_sem_penalidade' => $scenario === 'isencao',
                'has_inscricoes' => $scenario !== 'sem-candidatos',
            ];
        }
        $candidates = $scenario === 'sem-candidatos' ? [] : [
            ['inscricao_id' => 501, 'agenda_id' => $agendas[0]['agenda_id'], 'xp_pessoa' => 150, 'eventos_concluidos_mock' => 0, 'pessoa_id' => 11, 'nome' => 'Alex de Exemplo', 'habilidades_compativeis' => ['Trabalho em equipe'], 'carta_apresentacao_mock' => 'Gostaria de colaborar com o cuidado do parque e aprender com a equipe.', 'status_inscricao' => 'pendente', 'match' => ['habilidades' => 1, 'causas' => 1, 'recursos' => 0, 'total' => 2], 'confiabilidade' => 'Dados insuficientes'],
            ['inscricao_id' => 502, 'agenda_id' => $agendas[0]['agenda_id'], 'xp_pessoa' => 300, 'eventos_concluidos_mock' => 1, 'pessoa_id' => 12, 'nome' => 'Participante de Exemplo', 'habilidades_compativeis' => [], 'carta_apresentacao_mock' => null, 'status_inscricao' => 'pendente', 'match' => ['habilidades' => 0, 'causas' => 0, 'recursos' => 0, 'total' => 0], 'confiabilidade' => 'Dados insuficientes'],
            ['inscricao_id' => 503, 'agenda_id' => $agendas[0]['agenda_id'], 'xp_pessoa' => 450, 'eventos_concluidos_mock' => 2, 'pessoa_id' => 13, 'nome' => 'Voluntário de Exemplo', 'habilidades_compativeis' => ['Trabalho em equipe'], 'carta_apresentacao_mock' => null, 'status_inscricao' => 'aprovado', 'match' => ['habilidades' => 1, 'causas' => 0, 'recursos' => 0, 'total' => 1], 'confiabilidade' => 'Dados insuficientes'],
        ];

        // Distinct presentation keys across multiple event panels, never database IDs.
        foreach ($candidates as &$candidate) {
            $candidate['inscricao_id'] += ($id - 1) * 1000;
        } unset($candidate);

        $applications = [];
        foreach ($agendas as $index => $itemAgenda) {
            if ($itemAgenda['inscricao'] !== null && $scenario !== 'vazio') {
                $applications[] = ['inscricao_id' => 601 + $index, 'agenda' => $itemAgenda, 'status_inscricao' => $itemAgenda['inscricao'], 'titulo' => 'Limpeza de Parques'];
            }
        }

        return ['scenario' => $scenario, 'event_status' => $status, 'event_id' => $id, 'event_title' => 'Limpeza de Parques — Parque da Cantareira', 'my_applications' => $applications,
            'event_label' => ['ativo' => 'Publicado', 'aguardando a confirmação' => 'Aprovação pendente', 'reprovado' => 'Recusado', 'cancelado' => 'Cancelado', 'finalizado' => 'Finalizado'][$status],
            'reanalysis' => $scenario === 'reanalise', 'agendas' => $agendas, 'candidates' => $candidates,
            'eligible' => ! in_array($scenario, ['pessoa-pendente', 'visitante', 'organizador'], true),
            'catalogs' => ['causas' => [1 => 'Meio ambiente', 2 => 'Educação'], 'recursos' => [1 => 'Ferramentas', 2 => 'Notebook']],
            'empty' => $scenario === 'vazio', 'loading' => $scenario === 'carregando', 'error' => $scenario === 'falha'];
    }

    public static function page(string $page, mixed $scenario = null, int $id = 1, mixed $selected = null): array
    {
        if ($scenario === null && in_array($page, ['edit', 'candidates'], true)) {
            $scopeContext = NavigationContext::resolve(request()->query('contexto'), 'ong')['key'];
            $scopeEvent = collect(VisualThreeFixtures::make($scopeContext)['visibleEvents'])->firstWhere('evento_id', $id);
            if ($scopeEvent) {
                $scenario = ['ativo' => 'publicado', 'finalizado' => 'finalizado', 'cancelado' => 'cancelado', 'reprovado' => 'reprovado', 'aguardando a confirmação' => 'pendente'][$scopeEvent['status_evento']];
            }
        }
        $flow = self::make($scenario ?? ($page === 'create' ? 'pendente' : ($page === 'applications' ? 'candidatura-pendente' : null)), $id);
        $participation = $scenario === 'participacao' ? VisualFiveFixtures::event($id) : null;
        if ($participation && in_array($page, ['detail', 'candidates'], true)) {
            $flow['scenario'] = 'participacao';
            $flow['eligible'] = false;
            $flow['event_title'] = $participation['title'];
            $flow['agendas'] = array_map(function ($agenda) use ($id, $participation) {
                $approved = collect($participation['records'])->where('agendaId', $agenda['id'])->whereNull('withdrawal')->count();

                return [
                    'agenda_id' => $agenda['id'], 'evento_id' => $id, 'data_inicio' => $agenda['start'], 'data_fim' => $agenda['end'],
                    'vagas_agenda' => 6, 'ocupadas' => $approved, 'status_ativo' => ['ativa' => 'ativo', 'cancelada' => 'cancelado', 'finalizada' => 'finalizado'][$agenda['state']],
                    'inscricao' => null, 'desistencia_sem_penalidade' => $agenda['exemption'], 'has_inscricoes' => $approved > 0,
                ];
            }, $participation['agendas']);
        }
        if ($page === 'create') {
            foreach ($flow['agendas'] as &$item) {
                $item['has_inscricoes'] = false;
                $item['ocupadas'] = 0;
            } unset($item);
        }
        if ($page === 'applications' && is_scalar($selected) && $flow['scenario'] === 'candidatura-pendente') {
            $chosen = collect($flow['agendas'])->first(fn ($item) => (string) $item['agenda_id'] === (string) $selected);
            if ($chosen) {
                $chosen['inscricao'] = 'pendente';
                $flow['my_applications'] = [['inscricao_id' => 601, 'agenda' => $chosen, 'status_inscricao' => 'pendente', 'titulo' => 'Limpeza de Parques']];
            }
        }
        $context = NavigationContext::resolve(request()->query('contexto'), in_array($page, ['create', 'edit', 'management', 'candidates'], true) ? 'ong' : 'pessoa');
        $flow['owner_label'] = $context['key'] === 'organizador' ? 'Organizador independente' : 'Instituto Esperança';
        $flow['responsible_label'] = in_array($context['key'], ['organizador', 'organizador-vinculado'], true) ? 'Organizador responsável' : 'Conta da organização';
        $flow['eligible'] = $flow['eligible'] && $context['key'] === 'pessoa';
        // Mock ownership context only; API policy remains mandatory for future integration.
        $flow['can_manage_candidates'] = in_array($context['key'], ['ong', 'organizador', 'organizador-vinculado'], true);
        $data = match ($page) {
            'feed' => self::feedLegacy(), 'detail' => self::detailLegacy($id), 'create' => self::createLegacy(), 'edit' => self::editLegacy($id), 'management' => self::managementLegacy(), default => []
        };
        $ownership = VisualThreeFixtures::ownership($id);
        if ($ownership && isset($data['evento'])) {
            $data['evento']->nm_evento = $ownership['nm_evento'];
            if (isset($ownership['descricao_evento'])) {
                $data['evento']->descricao_evento = $ownership['descricao_evento'];
                $data['evento']->cat_evento_id = $ownership['cat_evento_id'];
            }
            $data['evento']->ong = $ownership['ong_id'] ? (object) ['ong_id' => $ownership['ong_id']] : null;
            $data['evento']->pessoa = $ownership['pessoa_id'] ? (object) VisualThreeFixtures::person() : null;
        }
        if ($page === 'feed') {
            foreach ($data['eventos'] as $i => &$event) {
                $event['status_evento'] = 'ativo';
                $event['ong_id'] = $i === 0 ? 1 : 2; // Synthetic public owner, not current navigation identity.
                $event['organizador_nome'] = PublicProfileFixtures::ong($event['ong_id'])['usuario']['nome'];
                $event['data_inicio'] = now()->addDays(7 + $i)->setHour(9)->toDateTimeString();
                $event['data_fim'] = now()->addDays(7 + $i)->setHour(11)->toDateTimeString();
                $event['habilidades_labels'] = ['Trabalho em equipe'];
                $event['causas_labels'] = ['Meio ambiente'];
                $event['confirmados'] = $i === 0 ? $flow['agendas'][0]['ocupadas'] : $event['confirmados'];
                $event['agenda_capacity'] = $i === 0 ? $flow['agendas'][0]['vagas_agenda'] : 30; // Synthetic next-Agenda capacity, independent of vagas_evento.
                $event['agenda_count'] = $i === 0 ? count($flow['agendas']) : 1;
                $event['vagas_disponiveis'] = $i === 0 ? max(0, $flow['agendas'][0]['vagas_agenda'] - $flow['agendas'][0]['ocupadas']) : $event['vagas_disponiveis'];
            }
            unset($event);
            if ($flow['empty'] || $flow['loading'] || $flow['error'] || $flow['event_status'] !== 'ativo') {
                $data['eventos'] = [];
            }
        }
        if (in_array($page, ['detail', 'edit'], true)) {
            $agenda = collect($flow['agendas'])->first(fn ($item) => is_scalar($selected) && (string) $item['agenda_id'] === (string) $selected) ?? $flow['agendas'][0];
            $flow['event_title'] = $data['evento']->nm_evento;
            if (isset($data['evento']->ong)) {
                $data['evento']->ong->ong_id = $ownership['ong_id'] ?? ($id === 1 ? 1 : 2);
                $data['evento']->ong->nm_ong = PublicProfileFixtures::ong($data['evento']->ong->ong_id)['usuario']['nome'];
            }
            $data['evento']->status_evento = $flow['event_status'];
            $data['evento']->cidade_evento ??= 'Guarulhos';
            $data['evento']->uf_evento ??= 'SP';
            $data['evento']->bairro_evento ??= 'Bairro Exemplo';
            $data['agenda'] = (object) array_merge($agenda, ['data_inicio' => Carbon::parse($agenda['data_inicio']), 'data_fim' => Carbon::parse($agenda['data_fim'])]);
            $data['totalVagas'] = $agenda['vagas_agenda'];
            $data['confirmados'] = $agenda['ocupadas'];
            $data['vagasDisponiveis'] = max(0, $agenda['vagas_agenda'] - $agenda['ocupadas']);
            $data['percentualOcupado'] = 100 * $agenda['ocupadas'] / $agenda['vagas_agenda'];
        }
        if ($page === 'management') {
            foreach ($data['eventos'] as $index => &$event) {
                $event['status_evento'] = match ($event['status_evento']) {
                    'publicado' => 'ativo', 'pendente', 'aguardando', 'rascunho' => 'aguardando a confirmação', 'realizado' => 'finalizado', default => $event['status_evento']
                };
                if ($index === 0) {
                    $event['status_evento'] = $flow['event_status'];
                }
                $event['agendas_mock'] = self::make($index === 0 ? $flow['scenario'] : 'publicado', $event['evento_id'])['agendas'];
                $event['data_inicio'] = $event['agendas_mock'][0]['data_inicio'];
                $event['candidaturas_pendentes'] = $index === 0 && $flow['scenario'] === 'sem-candidatos' ? 0 : 2;
                $event['candidaturas_aprovadas'] = array_sum(array_column($event['agendas_mock'], 'ocupadas'));
                $event['scenario'] = match ($event['status_evento']) {
                    'ativo' => 'publicado', 'aguardando a confirmação' => 'pendente', 'cancelado' => 'cancelado', 'finalizado' => 'finalizado', default => 'reprovado'
                };
                $event['status_label'] = ['ativo' => 'Publicado', 'aguardando a confirmação' => 'Aprovação pendente', 'finalizado' => 'Finalizado', 'cancelado' => 'Cancelado', 'reprovado' => 'Recusado'][$event['status_evento']] ?? 'Aprovação pendente';
            } unset($event);
            $data['resumo']['candidaturas_pendentes'] = collect($data['eventos'])->sum('candidaturas_pendentes');
            $data['resumo']['publicados'] = collect($data['eventos'])->where('status_evento', 'ativo')->count();
            $data['resumo']['aguardando'] = collect($data['eventos'])->where('status_evento', 'aguardando a confirmação')->count();
            $data['resumo']['realizados'] = collect($data['eventos'])->where('status_evento', 'finalizado')->count();
        }

        if ($page === 'candidates') {
            $other = self::make('publicado', $id + 1);
            $other['event_title'] = 'Plantio comunitário';
            $other['can_manage_candidates'] = $flow['can_manage_candidates'];
            $flow['candidate_events'] = [$flow, $other]; // Multi-event presentation only; no new API contract.
        }

        if (in_array($page, ['management', 'create', 'edit', 'candidates'], true)) {
            $work = VisualThreeFixtures::make($context['key']);
            $flow['management_allowed'] = in_array($context['key'], ['ong', 'organizador', 'organizador-vinculado'], true)
                && (in_array($page, ['create', 'management'], true) || VisualThreeFixtures::canManage($id, $context['key']));
            $flow['management_scope'] = ['person' => $work['person']['nm_pessoa'], 'institution' => $context['key'] === 'ong' || $work['linked'],
                'label' => $context['key'] === 'ong' || $work['linked'] ? $work['organizations'][0]['nome_fantasia'] : 'Atuação independente',
                'context' => $context['key'], 'scenario' => $work['scenario']];
            if ($page === 'management') {
                $template = $data['eventos'][0];
                $data['eventos'] = [];
                if (in_array($context['key'], ['ong', 'organizador', 'organizador-vinculado'], true)) {
                    foreach ($work['visibleEvents'] as $index => $item) {
                        $event = array_merge($template, $item);
                        if (request()->has('cenario') && $index === 0) {
                            $event['status_evento'] = $flow['event_status'];
                        }
                        $event['agendas_mock'] = self::make(in_array($event['status_evento'], ['cancelado', 'finalizado'], true) ? $event['status_evento'] : $flow['scenario'], $event['evento_id'])['agendas'];
                        $event['data_inicio'] = $event['agendas_mock'][0]['data_inicio'];
                        $event['responsavel'] = $event['responsavel_pessoa_id'] ? ['pessoa_id' => $event['responsavel_pessoa_id'], 'nome' => collect($work['people'])->firstWhere('pessoa_id', $event['responsavel_pessoa_id'])['nm_pessoa'], 'iniciais' => $event['responsavel_pessoa_id'] === 1 ? 'LP' : 'SE'] : null;
                        $event['status_label'] = ['ativo' => 'Publicado', 'finalizado' => 'Finalizado', 'cancelado' => 'Cancelado', 'reprovado' => 'Recusado', 'aguardando a confirmação' => 'Aprovação pendente'][$event['status_evento']];
                        $event['scenario'] = ['ativo' => 'publicado', 'finalizado' => 'finalizado', 'cancelado' => 'cancelado', 'reprovado' => 'reprovado', 'aguardando a confirmação' => 'pendente'][$event['status_evento']];
                        $data['eventos'][] = $event;
                    }
                }
                foreach (['ativos' => 'ativo', 'publicados' => 'ativo', 'aguardando' => 'aguardando a confirmação', 'realizados' => 'finalizado'] as $key => $status) {
                    $data['resumo'][$key] = collect($data['eventos'])->where('status_evento', $status)->count();
                }
                $data['resumo']['candidaturas_pendentes'] = collect($data['eventos'])->sum('candidaturas_pendentes');
            }
            if ($page === 'candidates') {
                $flow['candidate_events'] = [];
                $flow['can_manage_candidates'] = $flow['management_allowed'];
                if ($participation && VisualFiveFixtures::manager($id)) {
                    $flow['candidates'] = array_map(fn ($record) => [
                        'inscricao_id' => $record['id'], 'agenda_id' => $record['agendaId'],
                        'pessoa_id' => $record['personId'], 'nome' => $record['name'], 'status_inscricao' => 'aprovado',
                        'withdrawal' => $record['withdrawal'],
                    ], $participation['records']);

                    return ['flow' => array_replace($flow, ['candidate_events' => [$flow]])];
                }
                foreach ($flow['management_allowed'] ? $work['visibleEvents'] : [] as $item) {
                    $candidateScenario = $scenario ?? (['ativo' => 'publicado', 'finalizado' => 'finalizado', 'cancelado' => 'cancelado', 'aguardando a confirmação' => 'pendente', 'reprovado' => 'reprovado'][$item['status_evento']]);
                    $candidateEvent = self::make($candidateScenario, $item['evento_id']);
                    $candidateEvent['event_title'] = $item['nm_evento'];
                    $candidateEvent['can_manage_candidates'] = true;
                    $flow['candidate_events'][] = $candidateEvent;
                }
            }
        }

        return array_merge($data, ['flow' => $flow]);
    }

    public static function application(mixed $scenario, int $eventId, mixed $selected): array
    {
        $data = self::page('detail', $scenario, $eventId, $selected);
        $agenda = $data['agenda'];

        return array_merge($data, ['id_evento' => $eventId, 'nome_evento' => $data['evento']->nm_evento, 'nome_ong' => $data['evento']->ong?->nm_ong ?? VisualThreeFixtures::person()['nm_pessoa'],
            'data_evento' => $agenda->data_inicio->format('d/m/Y'), 'hora_inicio_evento' => $agenda->data_inicio->format('H:i'), 'hora_fim_evento' => $agenda->data_fim->format('H:i'),
            'endereco_evento' => $data['evento']->logradouro_evento, 'vagas_evento' => $data['vagasDisponiveis'],
            'nome_user' => 'Alex de Exemplo', 'cidade_user' => 'Guarulhos', 'uf_user' => 'SP', 'nivel_user' => 3, 'titulo_nivel' => 'Aprendiz', 'xp_user' => 1000, 'badges_user' => 3, 'eventos_user' => 8,
            'habilidades_user' => ['Comunicação', 'Organização']]);
    }

    private static function feedLegacy(int $id = 1): array
    {

        $usuario = [

            'pessoa_id' => 1,

            'nome' => 'Lucas Pereira',

            'xp' => 450,

            'posicao_ranking' => 42,

        ];

        $categorias = [

            [
                'categoria_id' => 1,
                'nome_categoria' => 'Meio Ambiente',
            ],

            [
                'categoria_id' => 2,
                'nome_categoria' => 'Educação',
            ],

            [
                'categoria_id' => 3,
                'nome_categoria' => 'Saúde',
            ],

            [
                'categoria_id' => 4,
                'nome_categoria' => 'Culinária',
            ],

            [
                'categoria_id' => 5,
                'nome_categoria' => 'Assistência Social',
            ],

        ];

        $eventos = [

            [
                'evento_id' => 1,

                'nm_evento' => 'Limpeza de Parques — Parque da Cantareira',

                'imagem_evento_link' => null,

                'icone' => '🌿',

                'tipo_organizador' => 'ONG',

                'categoria' => [
                    'categoria_id' => 1,
                    'nome_categoria' => 'Meio Ambiente',
                ],

                'modalidade_evento' => 'presencial',

                'data_formatada' => '15 maio 2026',

                'data_inicio' => '2026-08-27 08:00:00',

                'data_fim' => '2026-08-27 16:00:00',

                'horario_formatado' => '08h–16h',

                'cidade_evento' => 'São Paulo',

                'uf_evento' => 'SP',

                'organizador_nome' => 'ONG Verde SP · Carlos F.',

                'vagas_evento' => 30,

                'confirmados' => 8,

                'vagas_disponiveis' => 22,

                'percentual_ocupacao' => 26,
            ],

            [
                'evento_id' => 2,

                'nm_evento' => 'Aulas de Reforço Escolar — Ensino Fundamental',

                'imagem_evento_link' => null,

                'icone' => '📚',

                'tipo_organizador' => 'Organizador ind.',

                'categoria' => [
                    'categoria_id' => 2,
                    'nome_categoria' => 'Educação',
                ],

                'data_formatada' => '18 maio 2026',

                'data_inicio' => '2026-08-27 08:00:00',

                'data_fim' => '2026-08-27 16:00:00',

                'horario_formatado' => '14h–17h',

                'modalidade_evento' => 'presencial',

                'cidade_evento' => 'Santo André',

                'uf_evento' => 'SP',

                'organizador_nome' => 'Org. Educar · Ana M.',

                'vagas_evento' => 30,

                'confirmados' => 15,

                'vagas_disponiveis' => 15,

                'percentual_ocupacao' => 50,
            ],

            [
                'evento_id' => 3,

                'nm_evento' => 'Campanha de Vacinação Comunitária',

                'imagem_evento_link' => null,

                'icone' => '💉',

                'tipo_organizador' => 'ONG',

                'categoria' => [
                    'categoria_id' => 3,
                    'nome_categoria' => 'Saúde',
                ],

                'data_formatada' => '20 maio 2026',

                'modalidade_evento' => 'presencial',

                'data_inicio' => '2026-08-27 08:00:00',

                'data_fim' => '2026-08-27 16:00:00',

                'horario_formatado' => '09h–13h',

                'cidade_evento' => 'Guarulhos',

                'uf_evento' => 'SP',

                'organizador_nome' => 'Cruz Vermelha BR',

                'vagas_evento' => 30,

                'confirmados' => 27,

                'vagas_disponiveis' => 3,

                'percentual_ocupacao' => 90,
            ],

            [
                'evento_id' => 4,

                'nm_evento' => 'Aula de Culinária Social — Receitas Econômicas',

                'imagem_evento_link' => null,

                'icone' => '🍳',

                'tipo_organizador' => 'Organizador ind.',

                'categoria' => [
                    'categoria_id' => 4,
                    'nome_categoria' => 'Culinária',
                ],

                'data_formatada' => '22 maio 2026',

                'data_inicio' => '2026-08-27 08:00:00',

                'data_fim' => '2026-08-27 16:00:00',

                'horario_formatado' => '10h–13h',

                'modalidade_evento' => 'presencial',

                'cidade_evento' => 'São Paulo',

                'uf_evento' => 'SP',

                'organizador_nome' => 'Org. Sabor Social · Beatriz K.',

                'vagas_evento' => 30,

                'confirmados' => 10,

                'vagas_disponiveis' => 20,

                'percentual_ocupacao' => 33,
            ],

            [
                'evento_id' => 5,

                'nm_evento' => 'Mutirão de Plantio de Árvores Nativas',

                'imagem_evento_link' => null,

                'icone' => '🌳',

                'tipo_organizador' => 'ONG',

                'categoria' => [
                    'categoria_id' => 1,
                    'nome_categoria' => 'Meio Ambiente',
                ],

                'data_formatada' => '25 maio 2026',

                'data_inicio' => '2026-08-27 08:00:00',

                'data_fim' => '2026-08-27 16:00:00',

                'horario_formatado' => '07h–12h',

                'modalidade_evento' => 'presencial',

                'cidade_evento' => 'Osasco',

                'uf_evento' => 'SP',

                'organizador_nome' => 'Instituto Raízes Vivas',

                'vagas_evento' => 30,

                'confirmados' => 18,

                'vagas_disponiveis' => 12,

                'percentual_ocupacao' => 60,
            ],

            [
                'evento_id' => 6,

                'nm_evento' => 'Doação de Roupas — Triagem e Separação',

                'imagem_evento_link' => null,

                'icone' => '👕',

                'tipo_organizador' => 'ONG',

                'categoria' => [
                    'categoria_id' => 5,
                    'nome_categoria' => 'Assistência Social',
                ],

                'data_formatada' => '28 maio 2026',

                'horario_formatado' => '09h–17h',

                'data_inicio' => '2026-08-27 08:00:00',

                'data_fim' => '2026-08-27 16:00:00',

                'cidade_evento' => 'Diadema',

                'modalidade_evento' => 'presencial',

                'uf_evento' => 'SP',

                'organizador_nome' => 'Instituto Esperança Social',

                'vagas_evento' => 30,

                'confirmados' => 2,

                'vagas_disponiveis' => 28,

                'percentual_ocupacao' => 7,
            ],

        ];

        $cidades =
            collect($eventos)
                ->pluck('cidade_evento')
                ->unique()
                ->values()
                ->all();

        $topRanking = [

            [
                'posicao' => 1,
                'nome' => 'Ana Costa',
                'iniciais' => 'AC',
                'xp' => 1200,
            ],

            [
                'posicao' => 2,
                'nome' => 'Bruno Melo',
                'iniciais' => 'BM',
                'xp' => 980,
            ],

            [
                'posicao' => 3,
                'nome' => 'Carlos F.',
                'iniciais' => 'CF',
                'xp' => 850,
            ],

        ];

        $badges = [

            [
                'nome' => 'Plantador',
                'icone' => '🌱',
                'conquistado' => true,
            ],

            [
                'nome' => 'Aliado',
                'icone' => '🤝',
                'conquistado' => true,
            ],

            [
                'nome' => 'Solidário',
                'icone' => '❤️',
                'conquistado' => true,
            ],

            [
                'nome' => 'Estrela',
                'icone' => '🌟',
                'conquistado' => false,
            ],

            [
                'nome' => 'Campeão',
                'icone' => '🏆',
                'conquistado' => false,
            ],

            [
                'nome' => 'Transformador',
                'icone' => '🦋',
                'conquistado' => false,
            ],

        ];

        $badgesConquistados =
            collect($badges)
                ->where('conquistado', true)
                ->count();

        return [

            'usuario' => $usuario,

            'categorias' => $categorias,

            'cidades' => $cidades,

            'eventos' => $eventos,

            'topRanking' => $topRanking,

            'badges' => $badges,

            'badgesConquistados' => $badgesConquistados,

        ];

    }

    private static function detailLegacy(int $id = 1): array
    {

        $evento = (object) [

            'evento_id' => (int) $id,

            'nm_evento' => 'Limpeza de Parques — Parque Estadual da Cantareira',

            'descricao_evento' => 'Junte-se a nós para uma manhã inteira de limpeza e preservação
            do Parque Estadual da Cantareira. Vamos recolher lixo nas trilhas
            e margens da represa, remover plantas invasoras e plantar mudas
            nativas.

            Traremos luvas, sacos de lixo e ferramentas para toda a equipe.
            Você precisa apenas levar protetor solar, água e roupas
            confortáveis para atividade ao ar livre.

            Ao final, haverá um momento de confraternização com lanche
            coletivo.',

            'cep_evento' => '02377-000',

            'logradouro_evento' => 'Rua do Horto, 1488',

            'compl_evento' => null,

            'bairro_evento' => 'Horto Florestal',

            'cidade_evento' => 'São Paulo',

            'uf_evento' => 'SP',

            'vagas_evento' => 30,

            'modalidade_evento' => 'presencial',

            'status_evento' => 'publicado',

            'imagem_evento_link' => null,

            'categoria' => (object) [

                'cat_evento_id' => 1,

                'nome_categoria' => 'Meio Ambiente',

            ],

            'ong' => (object) [

                'ong_id' => 1,

                'nm_ong' => 'ONG Verde SP',

            ],

            'habilidades' => collect([

                (object) [
                    'habilidade_id' => 1,
                    'nome_habilidade' => 'Trabalho em equipe',
                ],

                (object) [
                    'habilidade_id' => 2,
                    'nome_habilidade' => 'Disposição física',
                ],

                (object) [
                    'habilidade_id' => 3,
                    'nome_habilidade' => 'Consciência ambiental',
                ],

            ]),

        ];

        $agenda = (object) [

            'agenda_id' => 1,

            'data_inicio' => Carbon::parse(
                '2026-09-15 08:00:00'
            ),

            'data_fim' => Carbon::parse(
                '2026-09-15 16:00:00'
            ),

            'status_ativo' => 'ativo',

        ];

        $totalVagas =
            $evento->vagas_evento;

        $confirmados = 8;

        $vagasDisponiveis =
            max(
                0,
                $totalVagas - $confirmados
            );

        $percentualOcupado =
            $totalVagas > 0
                ? ($confirmados / $totalVagas) * 100
                : 0;

        return [

            'evento' => $evento,

            'agenda' => $agenda,

            'totalVagas' => $totalVagas,

            'confirmados' => $confirmados,

            'vagasDisponiveis' => $vagasDisponiveis,

            'percentualOcupado' => $percentualOcupado,

        ];

    }

    private static function createLegacy(int $id = 1): array
    {

        $categorias = collect([

            (object) [
                'cat_evento_id' => 1,
                'nome_categoria' => 'Meio Ambiente',
            ],

            (object) [
                'cat_evento_id' => 2,
                'nome_categoria' => 'Educação',
            ],

            (object) [
                'cat_evento_id' => 3,
                'nome_categoria' => 'Saúde',
            ],

            (object) [
                'cat_evento_id' => 4,
                'nome_categoria' => 'Assistência Social',
            ],

        ]);

        $habilidades = collect([

            (object) [
                'habilidade_id' => 1,
                'nome_habilidade' => 'Trabalho em equipe',
            ],

            (object) [
                'habilidade_id' => 2,
                'nome_habilidade' => 'Disposição física',
            ],

            (object) [
                'habilidade_id' => 3,
                'nome_habilidade' => 'Consciência ambiental',
            ],

            (object) [
                'habilidade_id' => 4,
                'nome_habilidade' => 'Liderança',
            ],

            (object) [
                'habilidade_id' => 5,
                'nome_habilidade' => 'Primeiros socorros',
            ],

            (object) [
                'habilidade_id' => 6,
                'nome_habilidade' => 'Jardinagem',
            ],

            (object) [
                'habilidade_id' => 7,
                'nome_habilidade' => 'Comunicação',
            ],

        ]);

        return [

            'categorias' => $categorias,

            'habilidades' => $habilidades,

        ];

    }

    private static function editLegacy(int $id = 1): array
    {

        $evento = (object) [

            'evento_id' => (int) $id,

            'nm_evento' => 'Limpeza de Parques — Parque Estadual da Cantareira',

            'descricao_evento' => 'Junte-se a nós para uma manhã inteira de limpeza e preservação do parque.',

            'cep_evento' => '02377000',

            'logradouro_evento' => 'Rua do Horto, 1488',

            'compl_evento' => 'Portão 2',

            'vagas_evento' => 30,

            'modalidade_evento' => 'presencial',

            'status_evento' => 'publicado',

            'cat_evento_id' => 1,

            'imagem_evento_link' => null,

            'habilidades' => collect([

                (object) [
                    'habilidade_id' => 1,
                    'nome_habilidade' => 'Trabalho em equipe',
                ],

                (object) [
                    'habilidade_id' => 3,
                    'nome_habilidade' => 'Consciência ambiental',
                ],

            ]),

        ];

        $agenda = (object) [

            'agenda_id' => 1,

            'data_inicio' => Carbon::parse(
                '2026-09-15 08:00:00'
            ),

            'data_fim' => Carbon::parse(
                '2026-09-15 16:00:00'
            ),

        ];

        $categorias = collect([

            (object) [
                'cat_evento_id' => 1,
                'nome_categoria' => 'Meio Ambiente',
            ],

            (object) [
                'cat_evento_id' => 2,
                'nome_categoria' => 'Educação',
            ],

            (object) [
                'cat_evento_id' => 3,
                'nome_categoria' => 'Saúde',
            ],

            (object) [
                'cat_evento_id' => 4,
                'nome_categoria' => 'Assistência Social',
            ],

        ]);

        $habilidades = collect([

            (object) [
                'habilidade_id' => 1,
                'nome_habilidade' => 'Trabalho em equipe',
            ],

            (object) [
                'habilidade_id' => 2,
                'nome_habilidade' => 'Disposição física',
            ],

            (object) [
                'habilidade_id' => 3,
                'nome_habilidade' => 'Consciência ambiental',
            ],

            (object) [
                'habilidade_id' => 4,
                'nome_habilidade' => 'Liderança',
            ],

            (object) [
                'habilidade_id' => 5,
                'nome_habilidade' => 'Primeiros socorros',
            ],

        ]);

        return [

            'evento' => $evento,

            'agenda' => $agenda,

            'categorias' => $categorias,

            'habilidades' => $habilidades,

        ];

    }

    private static function managementLegacy(int $id = 1): array
    {

        $eventos = [

            [
                'evento_id' => 1,

                'nm_evento' => 'Aula de Reforço Escolar',

                'descricao_resumida' => 'Aulas de reforço em matemática e português para estudantes do ensino fundamental na região de Cidade Tiradentes.',

                'data_inicio' => '2026-09-12 14:00:00',

                'cidade_evento' => 'São Paulo',

                'bairro_evento' => 'Cidade Tiradentes',

                'status_evento' => 'publicado',

                'responsavel' => [
                    'pessoa_id' => 1,
                    'nome' => 'Carlos Ferreira',
                    'iniciais' => 'CF',
                ],

                'candidaturas_pendentes' => 4,

                'candidaturas_aprovadas' => 6,
            ],

            [
                'evento_id' => 2,

                'nm_evento' => 'Mutirão de Adoção de Animais',

                'descricao_resumida' => 'Feira de adoção em parceria com o abrigo municipal. Voluntários vão ajudar na recepção dos adotantes e organização do espaço.',

                'data_inicio' => '2026-09-25 09:00:00',

                'cidade_evento' => 'Guarulhos',

                'bairro_evento' => 'Parque Bonsucesso',

                'status_evento' => 'publicado',

                'responsavel' => [
                    'pessoa_id' => 1,
                    'nome' => 'Carlos Ferreira',
                    'iniciais' => 'CF',
                ],

                'candidaturas_pendentes' => 9,

                'candidaturas_aprovadas' => 8,
            ],

            [
                'evento_id' => 3,

                'nm_evento' => 'Arrecadação de Agasalhos de Inverno',

                'descricao_resumida' => 'Ponto de coleta e triagem de roupas de inverno para famílias em situação de vulnerabilidade atendidas pela ONG.',

                'data_inicio' => '2026-10-03 09:00:00',

                'cidade_evento' => 'São Paulo',

                'bairro_evento' => 'Zona Leste',

                'status_evento' => 'publicado',

                'responsavel' => [
                    'pessoa_id' => 1,
                    'nome' => 'Carlos Ferreira',
                    'iniciais' => 'CF',
                ],

                'candidaturas_pendentes' => 6,

                'candidaturas_aprovadas' => 3,
            ],

            [
                'evento_id' => 4,

                'nm_evento' => 'Campanha de Vacinação Pet',

                'descricao_resumida' => 'Apoio à campanha de vacinação antirrábica em parceria com a UBS do Centro. Voluntários vão ajudar na organização das filas.',

                'data_inicio' => '2026-10-18 09:00:00',

                'cidade_evento' => 'São Paulo',

                'bairro_evento' => 'Centro',

                'status_evento' => 'aguardando',

                'responsavel' => [
                    'pessoa_id' => 1,
                    'nome' => 'Carlos Ferreira',
                    'iniciais' => 'CF',
                ],

                'candidaturas_pendentes' => 7,

                'candidaturas_aprovadas' => 3,
            ],

            [
                'evento_id' => 5,

                'nm_evento' => 'Limpeza da Praia de Santos',

                'descricao_resumida' => 'Manhã de limpeza e conservação da praia. Ainda não enviado para aprovação — continue de onde parou na página Criar evento.',

                'data_inicio' => '2026-11-07 08:00:00',

                'cidade_evento' => 'Santos',

                'bairro_evento' => 'Av. Ana Costa',

                'status_evento' => 'rascunho',

                'responsavel' => [
                    'pessoa_id' => 1,
                    'nome' => 'Carlos Ferreira',
                    'iniciais' => 'CF',
                ],

                'candidaturas_pendentes' => 0,

                'candidaturas_aprovadas' => 0,
            ],

        ];

        $resumo = [

            'ativos' => collect($eventos)
                ->where(
                    'status_evento',
                    'publicado'
                )
                ->count(),

            'aguardando' => collect($eventos)
                ->where(
                    'status_evento',
                    'aguardando'
                )
                ->count(),

            'candidaturas_pendentes' => collect($eventos)
                ->sum(
                    'candidaturas_pendentes'
                ),

            'realizados' => collect($eventos)
                ->where(
                    'status_evento',
                    'realizado'
                )
                ->count(),

        ];

        return [

            'eventos' => $eventos,

            'resumo' => $resumo,

        ];

    }
}
