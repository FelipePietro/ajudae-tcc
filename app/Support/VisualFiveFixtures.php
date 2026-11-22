<?php

namespace App\Support;

use Carbon\CarbonInterface;

/** Synthetic participation records shared by the operational and personal views. */
final class VisualFiveFixtures
{
    public const PERSONAL_EVENT = 7;

    public const PERSON_CONTEXTS = ['pessoa', 'organizador', 'organizador-vinculado'];

    public static function event(int $id): ?array
    {
        if (! in_array($id, [1, 7, 8], true)) {
            return null;
        }

        $event = VisualThreeFixtures::ownership($id);
        $today = now('America/Sao_Paulo')->startOfDay();
        $agendas = [
            self::agenda($id, 1, $today->copy()->subDay()->setHour(9), 60),
            self::agenda($id, 2, $today->copy()->addDays(2)->setHour(9), 90),
        ];
        $records = [
            self::record($agendas[0], 11, 'Alex de Exemplo'),
            self::record($agendas[0], 12, 'Participante de Exemplo', ['presence' => 'ausente']),
            self::record($agendas[0], 13, 'Voluntário de Exemplo', ['presence' => 'presente']),
            self::record($agendas[1], 11, 'Alex de Exemplo'),
        ];
        $closureExample = self::usesClosureExample($id);
        if ($closureExample) {
            $agendas[1]['state'] = 'cancelada';
            $records = array_slice($records, 0, 3);
            if ($id === 8) {
                $records[2]['presence'] = 'ausente';
            }
        }

        if ($id === self::PERSONAL_EVENT) {
            $owner = VisualThreeFixtures::person();
            $person = $owner['nm_pessoa'];
            $agendas[] = self::agenda($id, 3, $today->copy()->subDay()->setHour(13), 30, 'cancelada');
            $agendas[] = self::agenda($id, 4, $today->copy()->subDay()->setHour(15), 30);
            $agendas[] = self::agenda($id, 5, $today->copy()->addHours(25), 60, exemption: true);
            $agendas[] = self::agenda($id, 6, $today->copy()->subDays(2)->setHour(9), 60, 'finalizada');
            $agendas[] = self::agenda($id, 7, $today->copy()->subDays(3)->setHour(9), 30, 'finalizada');
            $records = [
                ...$records,
                self::record($agendas[0], $owner['pessoa_id'], $person, ['student' => true, 'monitoring' => 'pendente']),
                self::record($agendas[1], $owner['pessoa_id'], $person, ['student' => true, 'monitoring' => 'pendente']),
                self::record($agendas[2], $owner['pessoa_id'], $person),
                self::record($agendas[4], $owner['pessoa_id'], $person),
                self::record($agendas[5], $owner['pessoa_id'], $person, ['withdrawal' => 'tardia']),
                self::record($agendas[6], $owner['pessoa_id'], $person, ['presence' => 'presente']),
            ];
        }

        $reviewExample = self::reviewExample($id);
        if ($reviewExample !== null) {
            $complete = $reviewExample === 'avaliacoes-concluidas';
            $agendas = [
                self::agenda($id, 1, $today->copy()->subDays(2)->setHour(9), 60, 'finalizada'),
                self::agenda($id, 2, $today->copy()->subDay()->setHour(9), 90, $complete ? 'finalizada' : 'ativa'),
                self::agenda($id, 3, $today->copy()->subDay()->setHour(13), 30, 'cancelada'),
            ];
            $records = [
                self::record($agendas[0], 11, 'Alex de Exemplo', ['presence' => 'presente']),
                self::record($agendas[1], 11, 'Alex de Exemplo', ['presence' => 'presente']),
                self::record($agendas[1], $owner['pessoa_id'], $person, ['presence' => 'presente']),
                self::record($agendas[1], 12, 'Participante de Exemplo', ['presence' => 'ausente']),
                self::record($agendas[2], 13, 'Voluntário de Exemplo', ['presence' => 'presente']),
            ];
        }

        $xpExample = self::xpExample($id);
        if ($xpExample !== null) {
            $agendas = [
                self::agenda($id, 1, $today->copy()->subDay()->setHour(9), 90),
                self::agenda($id, 2, $today->copy()->subDays(2)->setHour(9), 60, 'finalizada'),
                self::agenda($id, 3, $today->copy()->subDay()->setHour(13), 30, 'cancelada'),
                self::agenda($id, 4, $today->copy()->subDays(3)->setHour(9), 30, 'finalizada'),
            ];
            $records = [
                self::record($agendas[0], $owner['pessoa_id'], $person, ['presence' => 'presente']),
                self::record($agendas[0], 11, 'Alex de Exemplo'),
                self::record($agendas[0], 12, 'Participante de Exemplo', ['presence' => 'ausente']),
                self::record($agendas[1], $owner['pessoa_id'], $person, ['presence' => 'presente']),
                self::record($agendas[2], $owner['pessoa_id'], $person, ['presence' => 'presente']),
                self::record($agendas[3], $owner['pessoa_id'], $person, ['presence' => 'presente']),
            ];
        }

        if ($id === self::PERSONAL_EVENT && self::reliabilityExample() !== null) {
            $count = match (self::reliabilityExample()) {
                'confiabilidade-zero' => 0,
                'confiabilidade-dois' => 2,
                default => 3,
            };
            $agendas = [];
            $records = [];
            for ($n = 1; $n <= 8; $n++) {
                $agenda = self::agenda($id, $n, $today->copy()->subDays(10 - $n)->setHour(9), 60, $n === 8 ? 'cancelada' : 'finalizada');
                $agendas[] = $agenda;
                $state = $n <= $count ? ['presence' => 'presente'] : match ($n) {
                    4 => ['presence' => 'ausente'],
                    5 => ['withdrawal' => 'tardia'],
                    6 => ['withdrawal' => 'normal'],
                    7 => ['presence' => 'ausente', 'justification' => ['status' => 'deferida', 'reason' => 'transporte']],
                    default => [],
                };
                $records[] = self::record($agenda, $owner['pessoa_id'], $person, $state);
                foreach (self::rankingPeople() as $candidate) {
                    if ($candidate['id'] !== $owner['pessoa_id'] && $n <= ($candidate['id'] % 5 + 1)) {
                        $records[] = self::record($agenda, $candidate['id'], $candidate['name'], ['presence' => 'presente']);
                    }
                }
            }
        }

        $certificateExample = $id === self::PERSONAL_EVENT ? self::certificateExample() : null;
        if ($id === self::PERSONAL_EVENT && $certificateExample !== null) {
            $journey = $certificateExample === 'certificados-jornada';
            $agendas = [
                self::agenda($id, 1, $today->copy()->subDays(2)->setHour(9), 30, $journey ? 'ativa' : 'finalizada'),
                self::agenda($id, 2, $today->copy()->subDay()->setHour(9), 90, $journey ? 'ativa' : 'finalizada'),
                self::agenda($id, 3, $today->copy()->subDay()->setHour(13), 30, 'cancelada'),
                self::agenda($id, 4, $today->copy()->subDay()->setHour(15), 60, $journey ? 'ativa' : 'finalizada'),
            ];
            $records = [];
            foreach ($agendas as $index => $agenda) {
                $records[] = self::record($agenda, $owner['pessoa_id'], $person, [
                    'presence' => $journey ? 'pendente' : ($index === 3 || $certificateExample === 'certificados-ausente' ? 'ausente' : 'presente'),
                    'student' => true,
                    'monitoring' => $certificateExample === 'certificados-monitoria' ? 'validada' : 'pendente',
                ]);
            }
        }

        $campaigns = [];
        if ($xpExample !== null) {
            $period = [
                'categoryId' => $event['cat_evento_id'],
                'start' => $today->copy()->subDay()->toIso8601String(),
                'end' => $today->copy()->addDay()->endOfDay()->toIso8601String(),
                'configuredAt' => $today->copy()->subDays(4)->toIso8601String(),
                'active' => true,
            ];
            $campaigns = [
                ['id' => 1, 'name' => 'Rede de apoio', 'factor' => 2] + $period,
                ['id' => 2, 'name' => 'Semana da comunidade', 'factor' => $xpExample === 'xp-campanha-atualizada' ? 1.25 : 1.75] + $period,
            ];
        }

        return [
            'id' => $id,
            'title' => $event['nm_evento'],
            'categoryId' => $event['cat_evento_id'] ?? null,
            'campaigns' => $campaigns,
            'agendas' => $agendas,
            'records' => $records,
            'eventStatus' => $certificateExample !== null && ! in_array($certificateExample, ['certificados-ativo', 'certificados-jornada'], true) ? 'finalizado' : 'ativo',
            'certificateIssuer' => $event['ong_id'] ? 'ONG de Exemplo' : 'Organizador de Exemplo',
            'storageId' => self::storageId($id),
        ];
    }

    public static function manager(int $id): ?array
    {
        $context = request()->query('contexto');
        $event = self::event($id);
        if (! $event || ! in_array($context, ['ong', 'organizador', 'organizador-vinculado'], true)) {
            return null;
        }
        $work = VisualThreeFixtures::make($context);
        if ($context !== 'ong' && $context !== $work['actingContext']) {
            return null;
        }
        $managed = collect($work['visibleEvents'])->firstWhere('evento_id', $id);
        if (! $managed) {
            return null;
        }
        $status = $id === self::PERSONAL_EVENT && self::certificateExample() !== null ? $event['eventStatus'] : match (request()->query('cenario')) {
            'pendente', 'reanalise', 'reprovado', 'cancelado', 'finalizado' => request()->query('cenario'),
            default => $managed['status_evento'],
        };
        foreach ($event['agendas'] as &$agenda) {
            if ($status === 'cancelado') {
                $agenda['state'] = 'cancelada';
            } elseif ($status === 'finalizado' && $agenda['state'] === 'ativa') {
                $agenda['state'] = 'finalizada';
            }
        }
        unset($agenda);

        $actor = $context === 'ong' ? ['type' => 'ong', 'id' => 1] : ['type' => 'organizador', 'id' => $work['person']['pessoa_id']];
        $reviewExample = self::reviewExample($id);
        // This alternate start represents an Event operated by its owning ONG.
        $responsible = $reviewExample === null && ! ($id === self::PERSONAL_EVENT && self::certificateExample() !== null) ? $managed['responsavel_pessoa_id'] : null;
        $evaluator = $responsible === null
            ? ['type' => 'ong', 'id' => $managed['ong_id']]
            : ['type' => 'organizador', 'id' => $responsible];
        if ($reviewExample !== null && $actor !== $evaluator) {
            return null;
        }

        return array_replace($event, [
            'actor' => $actor,
            'eventStatus' => $status,
            'writable' => $status === 'ativo',
            'canClose' => $status === 'ativo',
            'canReviewVolunteers' => $actor === $evaluator,
            'avaliacoesConcluidas' => $reviewExample === 'avaliacoes-concluidas' ? [
                ['eventId' => $id, 'personId' => 11, 'nota' => 5],
                ['eventId' => $id, 'personId' => VisualThreeFixtures::person()['pessoa_id'], 'nota' => 4],
            ] : [],
        ]);
    }

    public static function personal(string $context, bool $empty, bool $reviews = false): array
    {
        $event = self::event(self::PERSONAL_EVENT);
        $person = VisualThreeFixtures::person();
        $records = array_values(array_filter($event['records'], fn ($record) => $record['personId'] === $person['pessoa_id']));
        $agendas = array_values(array_filter($event['agendas'], fn ($agenda) => in_array($agenda['id'], array_column($records, 'agendaId'), true)));

        return array_replace($event, [
            'actor' => ['type' => 'pessoa', 'id' => $person['pessoa_id']],
            'writable' => $context === 'pessoa',
            'historicalOnly' => $context !== 'pessoa',
            'records' => $empty ? [] : $records,
            'agendas' => $empty ? [] : $agendas,
            'eventAgendas' => array_map(fn (array $agenda) => array_intersect_key($agenda, array_flip(['id', 'state'])), $event['agendas']),
            'reviewOng' => $reviews && $context === 'pessoa' && VisualThreeFixtures::scenario($context) !== 'aprovada'
                ? VisualThreeFixtures::ownership($event['id'])['ong_id']
                : null,
        ]);
    }

    public static function managerUrl(int $id, ?int $agenda = null): ?string
    {
        $event = self::manager($id);
        if (! $event) {
            return null;
        }

        return route('ong.eventos.presenca', array_filter([
            'id' => $id, 'contexto' => request()->query('contexto'), 'jornada' => request()->query('jornada'),
            'cenario' => $event['writable'] ? null : request()->query('cenario'),
            'exemplo' => self::example($id),
        ])).($agenda ? '#agenda-'.$agenda : '');
    }

    public static function storageId(int $id): string
    {
        $example = self::xpExample($id) === null ? self::example($id) : 'xp';

        return (string) $id.($example === null ? '' : '.'.$example);
    }

    private static function example(int $id): ?string
    {
        return self::usesClosureExample($id) ? 'encerramento' : (self::reviewExample($id) ?? self::xpExample($id) ?? ($id === self::PERSONAL_EVENT ? (self::certificateExample() ?? self::reliabilityExample()) : null));
    }

    public static function certificateExample(): ?string
    {
        $value = request()->query('exemplo');

        return in_array($value, ['certificados', 'certificados-monitoria', 'certificados-ausente', 'certificados-ativo', 'certificados-jornada'], true) ? $value : null;
    }

    private static function xpExample(int $id): ?string
    {
        $value = request()->query('exemplo');

        return $id === self::PERSONAL_EVENT && in_array($value, ['xp', 'xp-campanha-atualizada'], true) ? $value : null;
    }

    private static function reviewExample(int $id): ?string
    {
        $value = request()->query('exemplo');

        return $id === self::PERSONAL_EVENT && in_array($value, ['avaliacoes', 'avaliacoes-concluidas'], true) ? $value : null;
    }

    public static function usesClosureExample(int $id): bool
    {
        // Alternate synthetic starts. Keep their histories independent of the 5.1 scenarios.
        return in_array($id, [1, 8], true) && request()->query('exemplo') === 'encerramento';
    }

    public static function personalUrl(?int $agenda = null): string
    {
        return route('participacoes', [
            ...NavigationContext::personParameters(),
            'exemplo' => self::example(self::PERSONAL_EVENT),
        ]).($agenda ? '#agenda-'.$agenda : '');
    }

    public static function reliabilityExample(): ?string
    {
        $value = request()->query('exemplo');

        return in_array($value, ['confiabilidade', 'confiabilidade-zero', 'confiabilidade-dois'], true) ? $value : null;
    }

    /** Public synthetic projection: no contacts, scores or private justification. */
    public static function rankingPeople(): array
    {
        $people = [
            ['id' => 1, 'name' => VisualThreeFixtures::person()['nm_pessoa'], 'city' => 'Guarulhos', 'approved' => true, 'role' => 'voluntario'],
        ];
        for ($id = 11; $id <= 28; $id++) {
            $people[] = [
                'id' => $id, 'name' => match ($id) {
                    11 => 'Alex de Exemplo', 12 => 'Participante de Exemplo', 13 => 'Voluntário de Exemplo', default => 'Pessoa de Exemplo '.$id
                },
                'city' => $id % 2 ? 'Guarulhos' : 'São Paulo',
                'approved' => $id !== 26, 'deleted' => $id === 27, 'anonymous' => $id === 28,
                'role' => $id === 13 ? 'organizador' : 'voluntario',
            ];
        }

        return $people;
    }

    public static function experienceUrl(): string
    {
        return route('perfil', [
            'area' => 'privada',
            ...NavigationContext::personParameters(),
            'exemplo' => self::example(self::PERSONAL_EVENT),
        ]).'#meu-xp';
    }

    private static function agenda(int $event, int $number, CarbonInterface $starts, int $minutes, string $state = 'ativa', bool $exemption = false): array
    {
        return [
            'id' => $event * 100 + $number, 'number' => $number, 'state' => $state,
            'start' => $starts->toIso8601String(), 'end' => $starts->copy()->addMinutes($minutes)->toIso8601String(),
            'minutes' => $minutes, 'exemption' => $exemption,
            'initiallyClosed' => $state === 'finalizada',
        ];
    }

    private static function record(array $agenda, int $person, string $name, array $overrides = []): array
    {
        return $overrides + [
            'id' => $agenda['id'] * 100 + $person, 'agendaId' => $agenda['id'], 'personId' => $person,
            'name' => $name, 'selection' => 'aprovada', 'presence' => 'pendente', 'student' => false,
            'monitoring' => 'dispensada', 'withdrawal' => null, 'justification' => null,
        ];
    }
}
