<?php

namespace App\Http\Controllers;

use App\Models\Ong;
use App\Models\Pessoa;
use App\Services\ManagedImageStorage;
use App\Support\AccountAccess;
use App\Support\WebNavigation;
use Illuminate\Http\Request;

final class SessionPagesController extends Controller
{
    public const INSTITUTIONAL_AREAS = [
        'ong.eventos.index' => ['/ong/eventos', 'ong.meus-eventos', true],
        'ong.candidatos' => ['/ong/candidatos', 'ong.candidatos', true],
        'ong.organizadores' => ['/ong/organizadores', 'organizadores.ong', true],
        'ong.eventos.criar' => ['/ong/eventos/criar', 'ong.criar', true],
        'ong.notificacoes' => ['/ong/notificacoes', 'notificacoes.central', false],
        'ong.configuracoes' => ['/ong/configuracoes', 'ong.editar_ong', false],
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

        return view('minhas-candidaturas');
    }

    public function personNotifications(Request $request)
    {
        $admin = WebNavigation::for($request)->adminPermissions !== [];

        return view('notificacoes.central', [
            'title' => 'Notificações',
            'notificationContext' => $admin ? 'administrador' : 'pessoa',
            ...($admin ? ['integratedAdmin' => true] : []),
        ]);
    }

    public function organizerEvents(Request $request)
    {
        abort_unless(WebNavigation::for($request)->organizer, 403);

        return view('ong.meus-eventos', ['organizerManagement' => true]);
    }

    public function organizerCreate(Request $request)
    {
        $navigation = WebNavigation::for($request);
        abort_unless($navigation->organizer, 403);

        /** @var Pessoa $account */
        $account = $request->attributes->get('web_account');
        abort_if($account->vinculoOngAtivo()->exists(), 403);

        return view('ong.criar', ['organizerManagement' => true]);
    }

    public function institutionalArea(Request $request)
    {
        [, $view, $requiresActive] = self::INSTITUTIONAL_AREAS[$request->route()->getName()];
        $navigation = WebNavigation::for($request);
        abort_if($requiresActive && ! $navigation->institutionalPanel, 403);

        if ($request->routeIs('ong.configuracoes')) {
            return $this->editOrganization($request);
        }

        return view($view, [
            'notificationContext' => 'ong',
            'institutionName' => $navigation->name,
        ]);
    }

    public function adminArea(Request $request, string $area)
    {
        $authorizedArea = WebNavigation::for($request)->adminAreas()[$area] ?? null;
        abort_unless($authorizedArea, 403);

        $view = match ($area) {
            'responder-lgpd' => 'admin.solicitacoes-lgpd',
            'campanhas-xp' => 'admin.campanhas-xp',
            default => 'admin.'.$area,
        };

        return view($view, [
            'title' => $authorizedArea[0],
            'page' => $area,
            'integratedAdmin' => true,
            'pages' => WebNavigation::for($request)->adminAreas(),
            'params' => [],
            'queues' => [],
            'records' => [],
            'variant' => null,
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
                'publicUrl' => $account->isApprovedVolunteer() ? route('perfil.publico', $account->getKey()) : null,
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
                'preferences' => $this->personPreferences($account),
                'photoUrl' => app(ManagedImageStorage::class)->publicUrl($account),
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
                'publicUrl' => AccountAccess::activeOng($account) ? route('ong.perfil.publico', $account->getKey()) : null,
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
                'photoUrl' => app(ManagedImageStorage::class)->publicUrl($account),
            ],
        ]);
    }

    public function publicPerson(string $id)
    {
        $person = Pessoa::query()
            ->with('habilidades')
            ->select('pessoa_id', 'nm_pessoa', 'role_pessoa', 'status_validacao', 'xp_pessoa', 'login_pessoa', 'exclusao_pendente', 'managed_image_path', 'pfp_pessoa_link')
            ->where('role_pessoa', 'voluntario')->where('status_validacao', 'aprovado')
            ->whereKey($id)->firstOrFail();
        abort_unless(AccountAccess::canOperate($person), 404);

        return view('perfil.index-perfil', [
            'nome_user' => $person->nm_pessoa,
            'xp_user' => $person->xp_pessoa,
            'foto_user' => app(ManagedImageStorage::class)->publicUrl($person),
            'habilidades_user' => $person->habilidades->map(fn ($habilidade) => [
                'nome' => $habilidade->nome_habilidade,
                'nivel' => $habilidade->pivot->nivel_habilidade,
            ])->all(),
        ]);
    }

    public function publicOrganization(string $id)
    {
        $organization = Ong::query()
            ->select('ong_id', 'nome_fantasia', 'descricao_ong', 'cidade_ong', 'uf_ong', 'status_ong', 'login_ong', 'exclusao_pendente', 'managed_image_path')
            ->whereKey($id)->firstOrFail();
        abort_unless(AccountAccess::activeOng($organization), 404);

        return view('ong.perfil_ong', [
            'usuario' => [
                'iniciais' => mb_strtoupper(mb_substr($organization->nome_fantasia, 0, 2)),
                'nome' => $organization->nome_fantasia,
                'cidade' => $organization->cidade_ong,
                'estado' => $organization->uf_ong,
                'tipo' => 'ONG',
                'papel' => 'Organização',
                'descricao' => $organization->descricao_ong,
                'foto' => app(ManagedImageStorage::class)->publicUrl($organization),
            ],
        ]);
    }

    public function editPerson(Request $request)
    {
        /** @var Pessoa $account */
        $account = $request->attributes->get('web_account');

        return view('perfil.editar-perfil', ['accountFields' => [
            'Nome' => $account->nm_pessoa,
            'E-mail' => $account->email_pessoa,
            'Telefone' => $account->tele_pessoa,
            'Cidade' => $account->cidade_pessoa,
            'UF' => $account->uf_pessoa,
        ]]);
    }

    public function preferences(Request $request)
    {
        return view('perfil.preferencias', [
            'savedPreferences' => $this->personPreferences($request->attributes->get('web_account')),
        ]);
    }

    public function editOrganization(Request $request)
    {
        /** @var Ong $account */
        $account = $request->attributes->get('web_account');

        return view('ong.editar_ong', ['accountFields' => [
            'Nome da organização' => $account->nome_fantasia,
            'E-mail' => $account->email_ong,
            'CNPJ' => $account->cnpj_ong,
            'Localidade' => implode(', ', array_filter([$account->cidade_ong, $account->uf_ong])),
            'Descrição' => $account->descricao_ong,
        ]]);
    }

    private function personPreferences(Pessoa $account): array
    {
        return [
            'Causas' => $account->causas()->orderBy('nome_causa')->pluck('nome_causa')->all(),
            'Habilidades' => $account->habilidades()->orderBy('nome_habilidade')->pluck('nome_habilidade')->all(),
            'Recursos' => $account->recursos()->orderBy('nome_recurso')->pluck('nome_recurso')->all(),
        ];
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

        return $this->adminArea($request, 'solicitacoes-lgpd');
    }
}
