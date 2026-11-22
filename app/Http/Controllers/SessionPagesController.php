<?php

namespace App\Http\Controllers;

use App\Models\Ong;
use App\Models\Pessoa;
use App\Support\AccountAccess;
use App\Support\WebNavigation;
use Illuminate\Http\Request;

final class SessionPagesController extends Controller
{
    public const INSTITUTIONAL_AREAS = [
        'ong.eventos.index' => ['/ong/eventos', 'Meus eventos', 'A consulta e a gestão de Eventos da organização ainda estão indisponíveis.', true],
        'ong.candidatos' => ['/ong/candidatos', 'Candidatos', 'A consulta e a seleção de candidaturas ainda estão indisponíveis.', true],
        'ong.organizadores' => ['/ong/organizadores', 'Organizadores', 'A consulta de convites e vínculos da organização ainda está indisponível.', true],
        'ong.eventos.criar' => ['/ong/eventos/criar', 'Criar evento', 'A criação de Eventos pela organização ainda está indisponível.', true],
        'ong.notificacoes' => ['/ong/notificacoes', 'Notificações', 'As notificações da organização ainda estão indisponíveis aqui.', false],
        'ong.configuracoes' => ['/ong/configuracoes', 'Configurações', 'Você pode gerenciar a segurança da sua conta.', false],
    ];

    public function privatePrivacy(Request $request)
    {
        return view('privacidade.conta', [
            'privateEntry' => true,
            'institution' => WebNavigation::for($request)->organization,
        ]);
    }

    public function applications(Request $request)
    {
        abort_unless(WebNavigation::for($request)->volunteer, 403);

        return view('auth.feature-unavailable', [
            'title' => 'Minhas candidaturas',
            'message' => 'O acompanhamento de candidaturas ainda está indisponível.',
            'backRoute' => 'perfil',
            'backLabel' => 'Voltar ao perfil',
        ]);
    }

    public function personNotifications(Request $request)
    {
        $navigation = WebNavigation::for($request);
        $title = 'Notificações';
        $message = 'As notificações da sua conta ainda estão indisponíveis aqui.';

        if ($navigation->adminPermissions !== []) {
            return view('admin.area-indisponivel', [
                'title' => $title, 'message' => $message, 'integratedAdmin' => true,
            ]);
        }

        return view('auth.feature-unavailable', [
            'title' => $title, 'message' => $message, 'backRoute' => 'perfil', 'backLabel' => 'Voltar ao perfil',
        ]);
    }

    public function organizerEvents(Request $request)
    {
        abort_unless(WebNavigation::for($request)->organizer, 403);

        return view('auth.feature-unavailable', [
            'title' => 'Meus eventos',
            'message' => 'A consulta e a gestão de Eventos sob sua responsabilidade ainda estão indisponíveis.',
            'backRoute' => 'organizador.workspace',
            'backLabel' => 'Voltar para Minha atuação',
        ]);
    }

    public function institutionalArea(Request $request)
    {
        $name = $request->route()->getName();
        [, $title, $message, $requiresActive] = self::INSTITUTIONAL_AREAS[$name];
        $navigation = WebNavigation::for($request);
        abort_if($requiresActive && ! $navigation->institutionalPanel, 403);

        return view('auth.institutional-area', [
            'title' => $title,
            'message' => $message,
            'configuration' => $name === 'ong.configuracoes',
            'backRoute' => $navigation->institutionalPanel ? 'ong.painel' : 'ong.perfil',
        ]);
    }

    public function adminArea(Request $request, string $area)
    {
        $authorizedArea = WebNavigation::for($request)->adminAreas()[$area] ?? null;
        abort_unless($authorizedArea, 403);

        return view('admin.area-indisponivel', [
            'title' => $authorizedArea[0],
            'integratedAdmin' => true,
        ]);
    }

    public function person(Request $request)
    {
        /** @var Pessoa $account */
        $account = $request->attributes->get('web_account');

        return view('perfil.area-privada', [
            'webNavigation' => WebNavigation::for($request),
            'ownProfile' => [
                'name' => $account->nm_pessoa,
                'email' => $account->email_pessoa,
                'place' => implode(', ', array_filter([$account->cidade_pessoa, $account->uf_pessoa])),
                'volunteer' => $account->role_pessoa === 'voluntario',
                'approved' => $account->status_validacao === 'aprovado',
                'statusTone' => match ($account->status_validacao) {
                    'aprovado' => 'success',
                    'correcao_solicitada', 'reprovado' => 'danger',
                    default => 'info',
                },
                'status' => match ($account->status_validacao) {
                    'aprovado' => 'Cadastro aprovado',
                    'em_analise' => 'Cadastro em análise',
                    'correcao_solicitada' => 'Correção solicitada',
                    'reprovado' => 'Cadastro reprovado',
                    default => 'Aguardando validação',
                },
                'preferences' => [
                    'Causas' => $account->causas()->orderBy('nome_causa')->pluck('nome_causa')->all(),
                    'Habilidades' => $account->habilidades()->orderBy('nome_habilidade')->pluck('nome_habilidade')->all(),
                    'Recursos' => $account->recursos()->orderBy('nome_recurso')->pluck('nome_recurso')->all(),
                ],
            ],
        ]);
    }

    public function organization(Request $request)
    {
        /** @var Ong $account */
        $account = $request->attributes->get('web_account');

        return view('ong.area-cadastral', [
            'webNavigation' => WebNavigation::for($request),
            'ownProfile' => [
                'name' => $account->nome_fantasia,
                'email' => $account->email_ong,
                'place' => implode(', ', array_filter([$account->cidade_ong, $account->uf_ong])),
                'description' => $account->descricao_ong,
                'cnpj' => $account->cnpj_ong,
                'statusTone' => match ($account->status_ong) {
                    'ativo' => 'success',
                    'reprovado' => 'danger',
                    default => 'info',
                },
                'status' => match ($account->status_ong) {
                    'ativo' => 'Cadastro aprovado',
                    'reprovado' => 'Cadastro reprovado',
                    default => 'Aguardando aprovação',
                },
            ],
        ]);
    }

    public function institutionalPanel(Request $request)
    {
        $navigation = WebNavigation::for($request);
        abort_unless($navigation->institutionalPanel, 403);

        /** @var Ong $account */
        $account = $request->attributes->get('web_account');

        return view('ong.painel_ong', ['institutionalEntry' => [
            'name' => $navigation->name,
            'initials' => $navigation->initials,
            'place' => implode(', ', array_filter([$account->cidade_ong, $account->uf_ong])),
            'cnpj' => $account->cnpj_ong,
            'site' => $account->site_url_ong,
            'email' => $account->email_ong,
        ]]);
    }

    public function organizer(Request $request)
    {
        abort_unless(WebNavigation::for($request)->organizer, 403);

        /** @var Pessoa $account */
        $account = $request->attributes->get('web_account');
        $link = $account->vinculoOngAtivo()->with('ong')->first();
        $institution = $link?->ong;

        return view('organizadores.workspace', [
            'organizerName' => $account->nm_pessoa,
            'organizerLinked' => $link !== null,
            'activeInstitution' => $institution && AccountAccess::activeOng($institution) ? $institution->nome_fantasia : null,
            'organizerEntry' => match (true) {
                $link === null => 'Organizador independente',
                $institution !== null && AccountAccess::activeOng($institution) => 'Organizador vinculado a '.$institution->nome_fantasia,
                default => 'Organizador com vínculo institucional indisponível',
            },
        ]);
    }

    public function admin(Request $request)
    {
        $navigation = WebNavigation::for($request);
        abort_if($navigation->adminPermissions === [], 403);

        return view('admin.admin', [
            'title' => 'Administração',
            'integratedAdmin' => true,
            'permissions' => $navigation->adminPermissions,
        ]);
    }

    public function privacyQueue(Request $request)
    {
        abort_unless(isset(WebNavigation::for($request)->adminPermissions['lgpd.visualizar']), 403);

        return view('admin.solicitacoes-lgpd', ['title' => 'Solicitações de privacidade', 'integratedAdmin' => true]);
    }
}
