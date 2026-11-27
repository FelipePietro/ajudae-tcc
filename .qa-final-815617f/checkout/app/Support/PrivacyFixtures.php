<?php

namespace App\Support;

/** Frontend-only identities and clock; never authorization or a persisted legal request. */
final class PrivacyFixtures
{
    public const SCENARIOS = ['livre', 'pendencias', 'pendente', 'prazo-encerrado', 'anonimizada', 'vazio', 'falha', 'carregando'];

    public static function configuration(bool $institution = false, bool $admin = false): array
    {
        $context = request()->query('contexto');
        abort_unless($admin ? $context === 'administrador' : ($institution ? $context === 'ong' : in_array($context, VisualFiveFixtures::PERSON_CONTEXTS, true)), 403);
        foreach (['pessoa', 'ong_id', 'titular', 'destinatario'] as $selector) {
            abort_if(request()->query->has($selector), 404);
        }
        $scenario = request()->query('privacidade', $admin && in_array(request()->query('visao'), ['vazio', 'falha'], true) ? request()->query('visao') : 'livre');
        abort_unless(in_array($scenario, self::SCENARIOS, true), 404);

        return [
            'owner' => $institution ? 'ong:1' : 'pessoa:1', 'admin' => $admin,
            'respond' => $admin && request()->query('visao') !== 'privacidade',
            'scenario' => $scenario, 'context' => $context,
            'clock' => '2026-11-18T12:00:00.000Z',
            'back' => $institution ? route('ong.perfil', ['contexto' => 'ong', 'area' => 'privada']) : route('perfil', ['contexto' => $context, 'area' => 'privada']),
            'edit' => route($institution ? 'ong.perfil.editar' : 'perfil.editar', ['contexto' => $context]),
            'home' => route($institution ? 'ong.privacidade' : 'perfil.privacidade', ['contexto' => $context, 'privacidade' => $scenario]),
            'delete' => route($institution ? 'ong.perfil.excluir' : 'perfil.excluir', ['contexto' => $context, 'privacidade' => $scenario]),
            'pending' => route($institution ? 'ong.perfil.exclusao-pendente' : 'perfil.exclusao-pendente', ['contexto' => $context, 'privacidade' => $scenario]),
            'blockers' => $scenario !== 'pendencias' ? [] : ($institution
                ? ['Evento 7 em andamento: encerre as Agendas e resolva as responsabilidades institucionais.']
                : ($context === 'pessoa' ? ['Inscrição aprovada na Agenda 101: resolva sua participação antes de solicitar exclusão.', 'Apadrinhamento ativo: solicite a análise de encerramento do vínculo.']
                    : ['Responsabilidade pelo Evento 7 ativo: resolva as Agendas e a responsabilidade operacional.'])),
        ];
    }

    public static function page(bool $institution = false, string $mode = 'home')
    {
        $privacy = self::configuration($institution);

        return view('privacidade.conta', compact('privacy', 'institution', 'mode'));
    }
}
