<?php

namespace App\Support;

use Illuminate\Contracts\View\View;

/** Presentation-only inbox identity. Never an authentication/authorization boundary. */
final class NotificationFixtures
{
    public static function configuration(): ?array
    {
        $context = request()->query('contexto', 'visitante');
        if (! in_array($context, array_diff(NavigationContext::CONTEXTS, ['visitante']), true)) {
            return null;
        }
        $variant = request()->query('visao', 'completa');
        $variant = in_array($variant, ['completa', 'revisao', 'privacidade'], true) ? $variant : 'completa';
        $permissions = match ($variant) {
            'revisao' => ['usuarios.validar', 'eventos.aprovar'],
            'privacidade' => ['lgpd.visualizar'],
            default => array_values(array_filter(array_column(VisualFourFixtures::PAGES, 1))),
        };
        $role = $context === 'pessoa' && (request()->query('papel') === 'padrinho' || request()->query('apadrinhamento') === 'padrinho') ? 'padrinho' : 'titular';

        return ['context' => $context, 'identity' => $context === 'pessoa' && $role === 'padrinho' ? 'padrinho-principal' : $context.':1',
            'role' => $role, 'variant' => $variant, 'permissions' => $context === 'administrador' ? $permissions : [],
            'scenario' => request()->query('avisos', 'historico')];
    }

    public static function page(bool $institution = false): View
    {
        $config = self::configuration();
        abort_unless($config && (! $institution || $config['context'] === 'ong'), 403);
        abort_if(request()->hasAny(['destinatario', 'pessoa', 'ong_id']), 404);
        abort_unless(in_array($config['scenario'], ['historico', 'vazio', 'falha', 'indisponivel', 'carregando', 'destino-ausente'], true), 404);
        abort_unless(in_array(request()->query('papel'), [null, 'titular', 'padrinho'], true), 404);
        abort_if(request()->query('papel') === 'padrinho' && $config['context'] !== 'pessoa', 404);

        $admin = $config['context'] === 'administrador';
        $data = ['notificationConfig' => $config];
        if ($admin) {
            $pages = array_filter(VisualFourFixtures::PAGES, fn ($meta) => $meta[1] === null || in_array($meta[1], $config['permissions'], true));
            $data += ['admin' => true, 'title' => 'Notificações', 'page' => 'notificacoes', 'variant' => $config['variant'],
                'pages' => $pages, 'queues' => [], 'params' => ['contexto' => 'administrador', 'visao' => $config['variant']]];
        }

        return view('notificacoes.central', $data);
    }
}
