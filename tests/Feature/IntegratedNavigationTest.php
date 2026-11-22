<?php

namespace Tests\Feature;

use App\Models\Ong;
use App\Models\Pessoa;
use App\Support\WebNavigation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class IntegratedNavigationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! config('identity.enabled')) {
            $this->markTestSkipped('Execute com AJUDAE_WEB_AUTH=true.');
        }

        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $this->withoutVite();
    }

    public function test_event_pages_expose_only_published_public_fields(): void
    {
        DB::statement('CREATE TABLE evento (evento_id INTEGER PRIMARY KEY, nm_evento TEXT, descricao_evento TEXT, cidade_evento TEXT, uf_evento TEXT, modalidade_evento TEXT, status_evento TEXT, logradouro_evento TEXT)');
        $this->get('/feed')->assertOk()
            ->assertSeeInOrder(['Encontre eventos', 'busca-eventos', 'btn-filtros', 'filtro-categoria', 'filtro-cidade', 'painel-filtros', 'filtro-modalidade', 'lista-eventos'], false)
            ->assertSee('Nenhum evento publicado está disponível no momento.')
            ->assertSee('Top ranking')->assertSee('Dica:')
            ->assertSee('Os filtros de categoria e período ainda estão indisponíveis.');
        DB::table('evento')->insert([
            ['evento_id' => 1, 'nm_evento' => 'Mutirão publicado', 'descricao_evento' => 'Cuidado comunitário.', 'cidade_evento' => 'Guarulhos', 'uf_evento' => 'SP', 'modalidade_evento' => 'presencial', 'status_evento' => 'ativo', 'logradouro_evento' => 'Endereço omitido'],
            ['evento_id' => 2, 'nm_evento' => 'Evento em revisão', 'descricao_evento' => '', 'cidade_evento' => 'Cidade reservada', 'uf_evento' => '', 'modalidade_evento' => 'online', 'status_evento' => 'aguardando_aprovacao', 'logradouro_evento' => 'Endereço reservado'],
            ['evento_id' => 3, 'nm_evento' => 'Oficina remota', 'descricao_evento' => '', 'cidade_evento' => 'São Paulo', 'uf_evento' => 'SP', 'modalidade_evento' => 'online', 'status_evento' => 'ativo', 'logradouro_evento' => 'Endereço reservado'],
        ]);

        $this->get('/feed?contexto=administrador&visao=revisao')->assertOk()
            ->assertSee('Mutirão publicado')->assertSee('Oficina remota')->assertDontSee('Evento em revisão')
            ->assertDontSee('Cidade reservada')->assertDontSee('Endereço reservado')
            ->assertDontSee('aria-label="Administração"', false);
        $this->get('/feed?q=Mutirão&cidade=Guarulhos&modalidade=presencial')
            ->assertOk()->assertSee('Mutirão publicado')->assertDontSee('Oficina remota');
        $this->get('/feed?q=São+Paulo')->assertOk()->assertSee('Oficina remota')->assertDontSee('Mutirão publicado');
        $this->get('/feed?modalidade=online')->assertOk()->assertSee('Oficina remota')->assertDontSee('Mutirão publicado');
        $this->get('/feed?q=%25')->assertOk()->assertSee('Nenhum evento encontrado')
            ->assertDontSee('Nenhum evento publicado está disponível no momento.');
        $this->get('/feed?q[]=teste&cidade[]=teste&modalidade[]=online')->assertOk()->assertSee('Mutirão publicado');
        $this->get('/eventos/1')->assertOk()->assertSee('Guarulhos, SP')->assertDontSee('Endereço omitido');
        $this->get('/eventos/2')->assertNotFound();

        $this->sessionFor((new Ong)->forceFill(['ong_id' => 42, 'nome_fantasia' => 'ONG Sintética', 'status_ong' => 'ativo']));
        $feed = $this->get('/feed?contexto=pessoa')->assertOk()->assertSee('data-integrated-ong-sidebar', false);
        $this->assertMatchesRegularExpression('/data-sidebar-item="explorar"\s+aria-current="page"/', $feed->getContent());
    }

    public function test_person_profile_ignores_persona_and_public_profile_selectors(): void
    {
        DB::statement('CREATE TABLE habilidade (habilidade_id INTEGER PRIMARY KEY, nome_habilidade TEXT)');
        DB::statement('CREATE TABLE habilidade_pessoa (habilidade_id INTEGER, pessoa_id INTEGER)');
        DB::statement('CREATE TABLE causa (causa_id INTEGER PRIMARY KEY, nome_causa TEXT)');
        DB::statement('CREATE TABLE causa_pessoa (causa_id INTEGER, pessoa_id INTEGER)');
        DB::statement('CREATE TABLE recurso (recurso_id INTEGER PRIMARY KEY, nome_recurso TEXT)');
        DB::statement('CREATE TABLE pessoa_recurso (recurso_id INTEGER, pessoa_id INTEGER)');
        DB::table('causa')->insert(['causa_id' => 11, 'nome_causa' => 'Educação']);
        DB::table('causa_pessoa')->insert(['causa_id' => 11, 'pessoa_id' => 42]);

        $person = (new Pessoa)->forceFill([
            'pessoa_id' => 42, 'nm_pessoa' => 'Titular Sintética', 'status_validacao' => 'aguardando_validacao',
            'role_pessoa' => 'voluntario', 'email_pessoa' => 'titular@example.test', 'cpf_pessoa' => 'marcador-cpf',
            'rg_pessoa_link' => 'documento-reservado', 'admin_master' => true,
        ]);
        $this->sessionFor($person);

        $this->get('/perfil?area=publica&pessoa=999&contexto=administrador&papel=ong&visao=revisao&exemplo=teste')
            ->assertOk()->assertHeaderContains('Cache-Control', 'no-store')
            ->assertSee('Titular Sintética')->assertSee('titular@example.test')->assertSee('Educação')
            ->assertSee('Aguardando validação')->assertDontSee('marcador-cpf')->assertDontSee('documento-reservado')
            ->assertSee('account-profile-grid', false)->assertSee('account-status info', false)
            ->assertSee('Acompanhe as próximas etapas')->assertSee('Minha atuação como organizador')
            ->assertSee('id="apadrinhamento"', false)->assertDontSee('id="meu-xp"', false)
            ->assertSee('href="'.route('perfil.privacidade').'"', false)
            ->assertSee('href="'.route('conta').'"', false)
            ->assertDontSee('Painel da ONG');
        $this->get('/admin?contexto=administrador')->assertForbidden();
        $this->get('/ong/perfil')->assertForbidden();
        $this->getJson('/api/v1/pessoas/me')->assertUnauthorized();

        $this->get('/perfil/privacidade?contexto=ong&papel=administrador')->assertOk()
            ->assertSeeInOrder(['Privacidade e dados', 'O que fica visível?', 'Corrigir meus dados', 'Consultar ou obter meus dados', 'Minhas solicitações', 'Exclusão da conta'])
            ->assertSee(route('perfil'))->assertSee(route('conta'))->assertDontSee('data-privacy-config', false);
        $this->get('/ong/privacidade')->assertForbidden();
        $this->get('/minhas-candidaturas')->assertOk()
            ->assertSee('O acompanhamento de candidaturas ainda está indisponível.')
            ->assertDontSee('href="'.route('conta').'"', false);

        $person->status_validacao = 'aprovado';
        $this->get('/perfil')->assertOk()->assertSee('account-status success', false)
            ->assertSee('id="confiabilidade"', false)->assertSee('id="meu-xp"', false);
        $person->role_pessoa = 'organizador';
        $this->get('/perfil')->assertOk()->assertSee('Acessar minha atuação')
            ->assertSee(route('organizador.workspace'))->assertDontSee('id="candidaturas"', false)
            ->assertDontSee('id="apadrinhamento"', false)->assertDontSee('href="'.route('candidaturas').'"', false);
        $this->get('/minhas-candidaturas')->assertForbidden();
        $this->get('/organizador/eventos')->assertOk()->assertSee('Voltar para Minha atuação');
    }

    public function test_pending_organization_has_its_own_profile_but_no_workspace(): void
    {
        $organization = (new Ong)->forceFill([
            'ong_id' => 42, 'nome_fantasia' => 'Instituição Sintética', 'status_ong' => 'reprovado',
            'email_responsavel_ong' => 'reservado@example.test',
        ]);
        $this->sessionFor($organization);

        $this->get('/ong/perfil?papel=ong&cenario=ativo')->assertOk()->assertSee('Instituição Sintética')
            ->assertSee('Cadastro reprovado')->assertDontSee('reservado@example.test')
            ->assertSee('data-integrated-ong-sidebar', false)
            ->assertSee('href="'.route('conta').'"', false)
            ->assertDontSee(route('ong.painel'))->assertDontSee('data-notification-entry', false);
        $this->get('/perfil')->assertForbidden();
        $this->get('/ong/painel')->assertForbidden();
        $this->get('/ong/privacidade')->assertOk()->assertSee('Voltar ao cadastro')->assertSee(route('ong.perfil'));
        $this->get('/perfil/privacidade')->assertForbidden();
        foreach (['ong.eventos.index', 'ong.eventos.criar', 'ong.candidatos', 'ong.organizadores'] as $name) {
            $this->get(route($name).'?contexto=ong&visao=master')->assertForbidden();
        }

        $organization->status_ong = 'ativo';
        $this->get('/ong/painel?contexto=organizador&papel=administrador')->assertOk()
            ->assertSeeInOrder(['stats-grid', 'content-grid', 'eventos-ong', 'organizadores-ong', 'candidaturas-ong'], false)
            ->assertSee('Dados cadastrais')->assertSee('Atividade recente')->assertSee('Acesso rápido')
            ->assertSee('href="'.route('ong.eventos.index').'"', false)
            ->assertDontSee('href="'.route('conta').'"', false)
            ->assertDontSee('Instituto Esperança Social')->assertDontSee('Criar novo evento');
        foreach (['ong.eventos.index', 'ong.eventos.criar', 'ong.candidatos', 'ong.organizadores', 'ong.notificacoes', 'ong.configuracoes'] as $name) {
            $this->get(route($name))->assertOk()
                ->assertSee('href="'.route($name).'"', false)
                ->assertSee('aria-current="page"', false)->assertSee(route('ong.painel'));
        }
        $this->get('/ong/configuracoes')->assertOk()->assertSee('href="'.route('conta').'"', false);
        $this->post(route('ong.eventos.criar'))->assertMethodNotAllowed();
    }

    public function test_private_privacy_and_workspaces_redirect_visitors_to_login(): void
    {
        foreach (['perfil.privacidade', 'ong.privacidade', 'ong.eventos.index', 'admin.relatorios', 'organizador.eventos', 'notificacoes'] as $name) {
            $this->get(route($name).'?contexto=administrador&visao=master')->assertRedirect('/login');
        }
        $this->get('/ranking')->assertOk()->assertSee('A classificação ainda está indisponível.');
    }

    public function test_pending_deletion_allows_public_pages_without_personalized_operations(): void
    {
        $account = new Pessoa;
        $account->setDateFormat('Y-m-d H:i:s');
        $this->sessionFor($account->forceFill([
            'pessoa_id' => 42, 'nm_pessoa' => 'Titular sob exclusão', 'role_pessoa' => 'administrador',
            'admin_master' => true, 'exclusao_pendente' => true, 'deletar_em' => now()->addDay(),
        ]));

        $this->get('/perfil')->assertRedirect('/conta');
        $this->get('/admin')->assertRedirect('/conta');
        $this->get('/perfil/privacidade')->assertRedirect('/conta');
        $this->post('/conta/senha')->assertRedirect('/conta');
        $this->get('/')->assertOk()->assertSee('Acompanhar exclusão')->assertDontSee('Titular sob exclusão');
        $this->get('/conta')->assertOk()->assertSee('Cancelar exclusão')->assertDontSee('Alterar senha');
    }

    public function test_admin_entry_and_queue_require_their_actual_permissions(): void
    {
        DB::statement('CREATE TABLE permissao (permissao_id INTEGER PRIMARY KEY, nome TEXT)');
        DB::statement('CREATE TABLE permissao_pessoa (permissao_id INTEGER, pessoa_id INTEGER)');
        DB::table('permissao')->insert([
            ['permissao_id' => 1, 'nome' => 'lgpd.visualizar'],
            ['permissao_id' => 2, 'nome' => 'lgpd.responder'],
        ]);
        DB::table('permissao_pessoa')->insert(['permissao_id' => 1, 'pessoa_id' => 42]);

        $admin = (new Pessoa)->forceFill([
            'pessoa_id' => 42, 'nm_pessoa' => 'Conta Leitora', 'role_pessoa' => 'administrador',
        ]);
        $this->sessionFor($admin);

        $this->get('/admin?visao=revisao')->assertOk()->assertSee('Consulta de solicitações de privacidade')
            ->assertSee('id="admin-sidebar"', false)->assertSee(route('admin.solicitacoes-lgpd'))
            ->assertSee('href="'.route('perfil').'"', false)->assertDontSee('href="'.route('conta').'"', false)
            ->assertSeeInOrder(['admin-stats', 'admin-corpo', 'admin-tabs', 'data-admin-capability="lgpd.visualizar"', 'Atalhos administrativos'], false)
            ->assertDontSee('data-admin-capability="eventos.aprovar"', false)
            ->assertDontSee('Resposta a solicitações de privacidade');
        $this->get('/admin/solicitacoes-lgpd')->assertOk()->assertSee('A fila de solicitações ainda está indisponível');
        $this->patch('/admin/solicitacoes-lgpd/1/status')->assertNotFound();
        DB::table('permissao_pessoa')->where('pessoa_id', 42)->update(['permissao_id' => 2]);
        $this->get('/admin')->assertOk()->assertSee('Resposta a solicitações de privacidade')
            ->assertDontSee('Consulta de solicitações de privacidade')
            ->assertDontSee(route('admin.solicitacoes-lgpd'));
        $this->get('/admin/solicitacoes-lgpd')->assertForbidden();
        $this->get('/admin/responder-lgpd')->assertOk()->assertSee('Voltar ao painel');
        $this->get('/admin/relatorios?visao=master')->assertForbidden();
        $this->get('/admin/configuracoes?papel=master')->assertForbidden();

        $admin->admin_master = true;
        $this->get('/admin?visao=privacidade')->assertOk()
            ->assertSee('href="'.route('admin.fila-eventos').'"', false)
            ->assertSee('href="'.route('admin.campanhas-xp').'"', false)
            ->assertSee('data-admin-capability="usuarios.validar"', false)
            ->assertSee('data-admin-capability="lgpd.responder"', false)
            ->assertSee('Atividade recente');
        $this->get('/admin/solicitacoes-lgpd')->assertOk()->assertSee('permissão para consultar e responder');
        foreach (array_keys(WebNavigation::ADMIN_AREAS) as $area) {
            $this->get(route('admin.'.$area))->assertOk()
                ->assertSee('href="'.route('admin.'.$area).'"', false)
                ->assertSee('aria-current="page"', false);
        }
    }

    public function test_organizer_entry_rechecks_the_active_institutional_link(): void
    {
        DB::statement('CREATE TABLE ong (ong_id INTEGER PRIMARY KEY, nome_fantasia TEXT, status_ong TEXT)');
        DB::statement('CREATE TABLE vinculo_ong_organizador (vinculo_ong_organizador_id INTEGER PRIMARY KEY, pessoa_id INTEGER, ong_id INTEGER, status TEXT)');
        DB::table('ong')->insert(['ong_id' => 8, 'nome_fantasia' => 'Instituição Vinculada', 'status_ong' => 'ativo']);

        $this->sessionFor((new Pessoa)->forceFill([
            'pessoa_id' => 42, 'nm_pessoa' => 'Organizador Sintético',
            'role_pessoa' => 'organizador', 'status_validacao' => 'aprovado',
        ]));

        $this->get('/organizador?jornada=vinculado')->assertOk()->assertSee('Organizador independente');
        DB::table('vinculo_ong_organizador')->insert(['pessoa_id' => 42, 'ong_id' => 8, 'status' => 'ativo']);
        $this->get('/organizador?contexto=pessoa&jornada=independente')->assertOk()
            ->assertSee('Organizador vinculado a Instituição Vinculada')
            ->assertSee('id="navbar-links"', false)->assertDontSee('Criar Evento')
            ->assertDontSee('href="'.route('conta').'"', false)
            ->assertSee('Convites recebidos')->assertSee('Histórico de vínculos')->assertSee('Ver gestão de Eventos');
        DB::table('ong')->where('ong_id', 8)->update(['status_ong' => 'reprovado']);
        $this->get('/organizador')->assertOk()
            ->assertSee('vínculo institucional indisponível')->assertDontSee('Instituição Vinculada');
        DB::table('vinculo_ong_organizador')->where('pessoa_id', 42)->update(['status' => 'encerrado']);
        $this->get('/organizador')->assertOk()
            ->assertSee('Organizador independente')->assertDontSee('Instituição Vinculada');
        $this->get('/ong/eventos/99/editar')->assertNotFound();
    }

    public function test_expired_and_revoked_sessions_drop_private_navigation(): void
    {
        $account = (new Ong)->forceFill(['ong_id' => 42, 'nome_fantasia' => 'Nome reservado', 'status_ong' => 'ativo']);
        $this->sessionFor($account);
        $this->withSession(['web_identity.last_activity' => now()->subMinutes(31)->timestamp]);
        $this->get('/ong/perfil')->assertRedirect('/login')->assertSessionMissing('web_identity');

        $this->sessionFor($account);
        $account->forceFill(['auth_version' => 8]);
        $this->get('/')->assertOk()->assertDontSee('Nome reservado')->assertSessionMissing('web_identity');
    }

    public function test_public_pages_and_head_do_not_extend_the_idle_window(): void
    {
        $start = now()->startOfSecond();
        $this->travelTo($start);
        $this->sessionFor((new Ong)->forceFill([
            'ong_id' => 42, 'nome_fantasia' => 'ONG Sintética', 'status_ong' => 'ativo',
        ]));

        $this->travelTo($start->copy()->addSeconds(1799));
        $this->get('/')->assertOk()->assertSee(route('ong.painel'))
            ->assertSessionHas('web_identity.last_activity', $start->timestamp);
        $this->head('/ong/painel')->assertOk()
            ->assertSessionHas('web_identity.last_activity', $start->timestamp);
        $this->get('/ong/painel', ['Sec-Purpose' => 'prefetch'])->assertOk()
            ->assertSessionHas('web_identity.last_activity', $start->timestamp);
        $this->get('/perfil')->assertForbidden()
            ->assertSessionHas('web_identity.last_activity', $start->timestamp);

        $this->travelTo($start->copy()->addMinutes(30));
        $this->get('/')->assertOk()->assertDontSee(route('ong.painel'))->assertSessionMissing('web_identity');
        $this->get('/ong/painel')->assertRedirect('/login');
    }

    public function test_successful_private_navigation_extends_only_the_idle_window(): void
    {
        $start = now()->startOfSecond();
        $this->travelTo($start);
        $this->sessionFor((new Ong)->forceFill([
            'ong_id' => 42, 'nome_fantasia' => 'ONG Sintética', 'status_ong' => 'ativo',
        ]));

        $visit = $start->copy()->addSeconds(1799);
        $this->travelTo($visit);
        $this->get('/ong/painel')->assertOk()
            ->assertSessionHas('web_identity.last_activity', $visit->timestamp)
            ->assertSessionHas('web_identity.started_at', $start->timestamp);
    }

    public function test_password_change_revokes_the_previous_identity_and_recalculates_navigation(): void
    {
        DB::statement('CREATE TABLE ong (ong_id INTEGER PRIMARY KEY, nome_fantasia TEXT, login_ong TEXT, senha_ong TEXT, status_ong TEXT, auth_version INTEGER, updated_at DATETIME)');
        DB::statement('CREATE TABLE personal_access_tokens (id INTEGER PRIMARY KEY, tokenable_type TEXT, tokenable_id INTEGER)');
        DB::table('ong')->insert([
            'ong_id' => 42, 'nome_fantasia' => 'ONG Sintética', 'login_ong' => 'ong.sintetica',
            'senha_ong' => Hash::make('credencial-anterior'), 'status_ong' => 'ativo', 'auth_version' => 7,
        ]);
        DB::table('personal_access_tokens')->insert(['tokenable_type' => Ong::class, 'tokenable_id' => 42]);

        $this->sessionFor(Ong::findOrFail(42));
        $this->post('/conta/senha', [
            'senha_atual' => 'credencial-anterior', 'senha' => 'credencial-nova', 'senha_confirmation' => 'credencial-nova',
        ])->assertRedirect('/login')->assertSessionMissing('web_identity');
        $this->assertDatabaseHas('ong', ['ong_id' => 42, 'auth_version' => 8]);
        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_type' => Ong::class, 'tokenable_id' => 42]);
        $this->get('/ong/painel')->assertRedirect('/login');

        $this->post('/login', [
            'tipo_login' => 'ong', 'login' => 'ong.sintetica', 'senha' => 'credencial-nova',
        ])->assertRedirect('/ong/painel')->assertSessionHas('web_identity.version', 8);
    }

    private function sessionFor(Pessoa|Ong $account): void
    {
        $type = $account instanceof Pessoa ? 'pessoa' : 'ong';
        $guard = Auth::guard('web_'.$type);
        $account->forceFill(['auth_version' => 7]);
        $guard->setUser($account);
        $this->withSession([
            $guard->getName() => $account->getKey(),
            'web_identity' => [
                'type' => $type, 'id' => $account->getKey(), 'version' => 7,
                'started_at' => now()->timestamp, 'last_activity' => now()->timestamp,
            ],
        ]);
    }
}
