<?php

namespace App\Support;

use App\Models\Ong;
use App\Models\Pessoa;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class WebNavigation
{
    private const ADMIN_PERMISSIONS = [
        'usuarios.validar' => 'Validação de pessoas',
        'ongs.validar' => 'Validação de ONGs',
        'eventos.aprovar' => 'Aprovação de eventos',
        'usuarios.moderar' => 'Moderação de contas',
        'lgpd.visualizar' => 'Consulta de solicitações de privacidade',
        'lgpd.responder' => 'Resposta a solicitações de privacidade',
        'admin.logs.visualizar' => 'Consulta de auditoria',
        'organizadores.validar' => 'Validação de organizadores',
        'xp.campanhas.gerenciar' => 'Gestão de campanhas de XP',
    ];

    public const ADMIN_AREAS = [
        'fila-eventos' => ['Fila de eventos', 'eventos.aprovar', '📋'],
        'cadastros-ong' => ['Cadastros de ONG', 'ongs.validar', '🏢'],
        'upgrades-org' => ['Promoções a organizador', 'organizadores.validar', '⬆'],
        'candidatos' => ['Validação de Pessoas', 'usuarios.validar', '👥'],
        'solicitacoes-lgpd' => ['Solicitações de privacidade', 'lgpd.visualizar', '🔒'],
        'responder-lgpd' => ['Resposta a solicitações de privacidade', 'lgpd.responder', '✎'],
        'denuncias' => ['Ocorrências', 'usuarios.moderar', '🚩'],
        'usuarios' => ['Usuários', 'usuarios.moderar', '👤'],
        'relatorios' => ['Relatórios', 'admin.logs.visualizar', '📊'],
        'configuracoes' => ['Configurações', null, '⚙'],
        'log-acoes' => ['Log de ações', 'admin.logs.visualizar', '📜'],
        'campanhas-xp' => ['Campanhas de XP', 'xp.campanhas.gerenciar', '✦'],
    ];

    public readonly ?string $profile;

    public readonly ?string $name;

    public readonly string $initials;

    public readonly bool $restricted;

    public readonly bool $organization;

    public readonly bool $organizer;

    public readonly bool $volunteer;

    public readonly bool $institutionalPanel;

    public readonly array $adminPermissions;

    private readonly bool $master;

    private function __construct(Pessoa|Ong|null $account)
    {
        $this->restricted = $account?->exclusao_pendente ?? false;
        $this->organization = $account instanceof Ong;
        $visible = $account !== null && ! $this->restricted;
        $this->profile = $visible ? ($this->organization ? 'ong.perfil' : 'perfil') : null;
        $this->name = $visible ? ($this->organization ? $account->nome_fantasia : $account->nm_pessoa) : null;
        $words = Str::of($this->name ?? '')->squish()->explode(' ')->filter();
        $this->initials = mb_strtoupper(mb_substr($words->first() ?? '', 0, 1)
            .($words->count() > 1 ? mb_substr($words->last(), 0, 1) : ''));
        $this->institutionalPanel = $this->organization && AccountAccess::activeOng($account);
        $this->organizer = $visible && $account instanceof Pessoa
            && $account->role_pessoa === 'organizador' && $account->status_validacao === 'aprovado';
        $this->volunteer = $visible && $account instanceof Pessoa && $account->role_pessoa === 'voluntario';
        $permissions = [];
        $this->master = $visible && $account instanceof Pessoa
            && $account->role_pessoa === 'administrador' && $account->admin_master;

        if ($visible && $account instanceof Pessoa && $account->role_pessoa === 'administrador') {
            $permissions = $this->master
                ? self::ADMIN_PERMISSIONS
                : array_intersect_key(self::ADMIN_PERMISSIONS, array_flip($account->permissoes()->pluck('nome')->all()));
        }

        $this->adminPermissions = $permissions;
    }

    public static function for(Request $request): self
    {
        return $request->attributes->get(self::class) ?? tap(
            new self($request->attributes->get('web_account')),
            fn (self $navigation) => $request->attributes->set(self::class, $navigation)
        );
    }

    public function destination(): string
    {
        return match (true) {
            $this->restricted => 'conta',
            $this->adminPermissions !== [] => 'admin.admin',
            $this->organizer => 'organizador.workspace',
            $this->institutionalPanel => 'ong.painel',
            default => $this->profile ?? 'login',
        };
    }

    public function links(): array
    {
        if ($this->restricted) {
            return ['conta' => 'Acompanhar exclusão'];
        }

        $links = ['feed' => 'Explorar eventos'];

        if (! $this->profile) {
            return $links + [
                'login' => 'Entrar',
                'cadastro.pessoa' => 'Cadastrar-se como Pessoa',
                'ong.cadastrar.form' => 'Cadastrar ONG',
            ];
        }

        $links[$this->profile] = $this->organization ? 'Minha organização' : 'Meu perfil';
        if ($this->institutionalPanel) {
            $links['ong.painel'] = 'Painel da ONG';
        }
        if ($this->organizer) {
            $links['organizador.workspace'] = 'Minha atuação';
        }
        if ($this->adminPermissions !== []) {
            $links['admin.admin'] = 'Administração';
        }
        if (isset($this->adminPermissions['lgpd.visualizar'])) {
            $links['admin.solicitacoes-lgpd'] = 'Solicitações de privacidade';
        }

        return $links;
    }

    public function adminAreas(): array
    {
        return array_filter(self::ADMIN_AREAS, fn (array $area) => $area[1] === null ? $this->master : isset($this->adminPermissions[$area[1]]));
    }

    public function adminAreaUrl(string $area): string
    {
        return route('admin.'.$area);
    }
}
