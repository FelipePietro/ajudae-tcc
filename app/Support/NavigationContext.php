<?php

namespace App\Support;

use Illuminate\Http\Request;

/** Presentation-only fixtures. Query context never grants server permissions. */
final class NavigationContext
{
    public const CONTEXTS = ['visitante', 'pessoa', 'ong', 'organizador', 'organizador-vinculado', 'administrador'];

    /** UI-only memory: two enum values, never account data, login or authorization. */
    public static function preparePresentation(Request $request): void
    {
        if ($request->routeIs('logout')) {
            $request->session()->put('demo_navigation', ['context' => 'visitante']);
        }
        if ($request->routeIs('login.store')) {
            $request->session()->put('demo_navigation', ['context' => $request->input('tipo_login') === 'ong' ? 'ong' : 'pessoa']);
        }
        $supplied = $request->query('contexto');
        $explicit = is_string($supplied) && in_array($supplied, self::CONTEXTS, true);
        $remembered = $request->session()->get('demo_navigation', []);
        $remembered = is_array($remembered) ? $remembered : [];
        $context = $explicit ? $supplied : (! $request->query->has('contexto') && in_array($remembered['context'] ?? null, self::CONTEXTS, true)
            ? $remembered['context'] : self::defaultContext($request));
        $journey = $request->query('jornada');
        if (! $explicit && ! $request->query->has('contexto') && ! $request->query->has('jornada')) {
            $journey = $remembered['journey'] ?? null;
        }
        $journey = is_string($journey) && in_array($journey, VisualThreeFixtures::SCENARIOS, true) ? $journey : null;
        $request->query->set('contexto', $context);
        if ($journey !== null) {
            $request->query->set('jornada', $journey);
        } else {
            $request->query->remove('jornada');
        }
        if ($explicit) {
            $request->session()->put('demo_navigation', array_filter(['context' => $context, 'journey' => $journey]));
        }
    }

    private static function defaultContext(Request $request): string
    {
        return match (true) {
            $request->routeIs('admin.*') => 'administrador',
            $request->routeIs('organizador.solicitacao') => 'pessoa',
            $request->routeIs('organizador.workspace') => 'organizador',
            $request->routeIs('ong.cadastrar.form') => 'visitante',
            $request->routeIs('ong.perfil') && $request->query('area') !== 'privada' => 'visitante',
            $request->routeIs('ong.*') => 'ong',
            $request->routeIs('perfil') && ($request->query('area') === 'privada' || ($request->has('cenario') && $request->query('cenario') !== 'publico')) => 'pessoa',
            $request->routeIs('perfil.privacidade', 'perfil.editar', 'perfil.preferencias', 'perfil.excluir', 'perfil.exclusao-pendente', 'conta', 'candidatura.*', 'candidaturas', 'participacoes', 'certificados.index', 'notificacoes', 'ranking') => 'pessoa',
            default => 'visitante',
        };
    }

    public static function homeUrl(mixed $context = null): string
    {
        $navigation = self::resolve($context ?? request()->query('contexto'), self::defaultContext(request()));
        $route = match ($navigation['key']) {
            'pessoa' => 'feed',
            'ong' => 'ong.painel',
            'organizador', 'organizador-vinculado' => 'organizador.workspace',
            'administrador' => 'admin.admin',
            default => 'inicio',
        };

        return route($route, array_filter(['contexto' => $navigation['key'], 'jornada' => $navigation['journey']]));
    }

    /** Presentation context for the account owner; never an authorization check. */
    public static function personParameters(): array
    {
        $context = request()->query('contexto');
        $journey = request()->query('jornada');

        return array_filter([
            'contexto' => in_array($context, ['pessoa', 'organizador', 'organizador-vinculado'], true) ? $context : 'pessoa',
            'jornada' => is_string($journey) && in_array($journey, VisualThreeFixtures::SCENARIOS, true) ? $journey : null,
        ]);
    }

    public static function privateProfileUrl(): string
    {
        return route('perfil', ['area' => 'privada', ...self::personParameters()]);
    }

    /** Local navigation only. Query parameters never grant API permissions. */
    public static function ongActive(): ?string
    {
        $request = request();

        return match (true) {
            $request->routeIs('ong.painel', 'ong.dashboard') => 'dashboard',
            $request->routeIs('ong.eventos.criar') => 'criar-evento',
            $request->routeIs('ong.eventos.*') => 'eventos',
            $request->routeIs('ong.candidatos') => 'candidatos',
            $request->routeIs('ong.organizadores') => 'organizadores',
            $request->routeIs('feed', 'eventos.show') => 'explorar',
            $request->routeIs('ong.notificacoes') => 'notificacoes',
            $request->routeIs('ong.privacidade', 'ong.configuracoes', 'ong.perfil.editar') => 'configuracoes',
            $request->routeIs('ong.perfil') && $request->query('area') !== 'privada' => null,
            $request->routeIs('ong.perfil', 'ong.perfil.*') => 'perfil',
            default => null,
        };
    }

    public static function resolve(mixed $key = null, string $default = 'pessoa'): array
    {
        $contexts = self::CONTEXTS;
        $key = is_string($key) && in_array($key, $contexts, true) ? $key : $default;
        $links = match ($key) {
            'visitante' => [['/feed', 'Explorar eventos'], ['/login', 'Entrar'], ['/cadastro', 'Criar conta']],
            'ong' => [['/feed', 'Explorar eventos'], ['/ong/painel', 'Minha organização'], ['/ong/eventos', 'Gerenciar eventos']],
            'organizador' => [['/organizador', 'Minha atuação'], ['/feed', 'Feed'], ['/ong/eventos', 'Meus eventos'], ['/ong/eventos/criar', 'Criar evento'], ['/perfil', 'Meu perfil']],
            'organizador-vinculado' => [['/organizador', 'Minha atuação'], ['/feed', 'Feed'], ['/ong/eventos', 'Eventos da organização'], ['/ong/candidatos', 'Candidaturas recebidas'], ['/perfil', 'Meu perfil']],
            // Only explicitly supplied mock permissions; no master-role shortcuts.
            'administrador' => [['/feed', 'Explorar eventos'], ['/admin/fila-eventos', 'Analisar eventos'], ['/admin/usuarios', 'Analisar cadastros']],
            default => [['/feed', 'Feed'], ['/minhas-candidaturas', 'Candidaturas'], ['/perfil', 'Meu perfil'], ['/ranking', 'Ranking'], ['/notificacoes', 'Notificações']],
        };

        return ['key' => $key, 'journey' => is_string(request()->query('jornada')) && in_array(request()->query('jornada'), VisualThreeFixtures::SCENARIOS, true) ? request()->query('jornada') : null, 'layout' => $key === 'visitante' ? 'public' : ($key === 'ong' ? 'institution' : 'person'),
            'role' => match ($key) {
                'organizador', 'organizador-vinculado' => 'organizador', 'administrador' => 'administrador', 'pessoa' => 'voluntario', default => null
            },
            'permissions' => $key === 'administrador' ? ['eventos.aprovar', 'usuarios.validar'] : [],
            'linked' => $key === 'organizador-vinculado',
            'links' => array_map(function ($link) use ($key) {
                $parameters = array_filter(['contexto' => $key, 'jornada' => is_string(request()->query('jornada')) && in_array(request()->query('jornada'), VisualThreeFixtures::SCENARIOS, true) ? request()->query('jornada') : null]);
                $example = VisualFiveFixtures::reliabilityExample();
                if ($example !== null && in_array($link[0], ['/perfil', '/ranking', '/minhas-candidaturas'], true)) {
                    $parameters['exemplo'] = $example;
                    if ($link[0] === '/minhas-candidaturas' && $key === 'pessoa') {
                        $parameters['cenario'] = 'participacao';
                        $parameters['evento'] = VisualFiveFixtures::PERSONAL_EVENT;
                    }
                }

                return ['href' => ($link[1] === 'Meu perfil' ? route('perfil', ['area' => 'privada']) : $link[0]).($link[1] === 'Meu perfil' ? '&' : '?').http_build_query($parameters), 'label' => $link[1]];
            }, $links)];
    }
}
