<?php

namespace Tests\Feature;

use App\Models\Ong;
use App\Models\Pessoa;
use App\Support\WebNavigation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
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
        DB::statement('CREATE TABLE agenda (agenda_id INTEGER PRIMARY KEY, evento_id INTEGER NOT NULL)');
        $this->get('/feed')->assertOk()
            ->assertSeeInOrder(['Encontre eventos', 'busca-eventos', 'btn-filtros', 'filtro-categoria', 'filtro-cidade', 'painel-filtros', 'filtro-modalidade', 'lista-eventos'], false)
            ->assertSee('Nenhum evento publicado está disponível no momento.')
            ->assertSee('Top ranking')->assertSee('Dica:')
            ->assertSee('Os filtros de categoria e período ainda estão indisponíveis.');
        DB::table('evento')->insert([
            ['evento_id' => 1, 'nm_evento' => 'Mutirão publicado', 'descricao_evento' => 'Cuidado comunitário.', 'cidade_evento' => 'Guarulhos', 'uf_evento' => 'SP', 'modalidade_evento' => 'presencial', 'status_evento' => 'ativo', 'logradouro_evento' => 'Endereço omitido'],
            ['evento_id' => 2, 'nm_evento' => 'Evento em revisão', 'descricao_evento' => '', 'cidade_evento' => 'Cidade reservada', 'uf_evento' => '', 'modalidade_evento' => 'online', 'status_evento' => 'aguardando_aprovacao', 'logradouro_evento' => 'Endereço reservado'],
            ['evento_id' => 3, 'nm_evento' => 'Oficina remota', 'descricao_evento' => '', 'cidade_evento' => 'São Paulo', 'uf_evento' => 'SP', 'modalidade_evento' => 'online', 'status_evento' => 'ativo', 'logradouro_evento' => 'Endereço reservado'],
            ['evento_id' => 4, 'nm_evento' => 'Registro legado sem Agenda', 'descricao_evento' => '', 'cidade_evento' => 'Guarulhos', 'uf_evento' => 'SP', 'modalidade_evento' => 'online', 'status_evento' => 'ativo', 'logradouro_evento' => ''],
        ]);
        DB::table('agenda')->insert([['evento_id' => 1], ['evento_id' => 3]]);

        $this->get('/feed?contexto=administrador&visao=revisao')->assertOk()
            ->assertSee('Mutirão publicado')->assertSee('Oficina remota')->assertDontSee('Evento em revisão')->assertDontSee('Registro legado sem Agenda')
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
        $this->get('/eventos/4')->assertNotFound();
        $this->get('/candidatura?evento=1')->assertRedirect('/login');

        $person = (new Pessoa)->forceFill([
            'pessoa_id' => 42, 'nm_pessoa' => 'Pessoa Sintética', 'role_pessoa' => 'voluntario',
            'status_validacao' => 'aprovado',
        ]);
        $this->sessionFor($person);
        $this->get('/eventos/1')->assertOk()->assertSee(route('candidatura.form', ['evento' => 1]));
        $this->get('/candidatura?evento=1')->assertOk()->assertViewIs('candidatura')
            ->assertSeeInOrder(['Escolha da Agenda', 'Carta opcional', 'Revisão', 'Confirmação'])
            ->assertSee('Pessoa Sintética')->assertDontSee('data-event-config', false);
        $this->get('/candidatura?evento=2')->assertNotFound();
        $this->get('/candidatura?evento[]=1')->assertNotFound();
        $person->status_validacao = 'aguardando_validacao';
        $this->get('/candidatura?evento=1&cenario=aprovado')->assertForbidden();
        $this->flushSession();
        Auth::forgetGuards();

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
            ->assertSee('data-application-history', false)
            ->assertDontSee('href="'.route('conta').'"', false);
        $this->get('/perfil/editar?pessoa=999')->assertOk()->assertViewIs('perfil.editar-perfil')
            ->assertSee('titular@example.test')->assertDontSee('marcador-cpf');
        $this->get('/perfil/preferencias')->assertOk()->assertSee('Educação');
        $this->get('/minhas-participacoes')->assertOk()->assertViewIs('participacoes.pessoal');
        $this->get('/meus-certificados')->assertOk()->assertViewIs('certificados.lista')
            ->assertDontSee('data-certificate-fixture', false);
        $this->get('/notificacoes')->assertOk()->assertViewIs('notificacoes.central');

        $person->status_validacao = 'aprovado';
        $this->get('/perfil')->assertOk()->assertSee('account-status success', false)
            ->assertSee('id="confiabilidade"', false)->assertSee('id="meu-xp"', false);
        $person->role_pessoa = 'organizador';
        $this->get('/minhas-participacoes')->assertOk();
        $this->get('/meus-certificados')->assertOk();
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
        $this->get('/ong/eventos/criar?contexto=organizador')->assertOk()->assertViewIs('ong.criar')
            ->assertSee('Agendas do evento')->assertSee('fieldset disabled', false)
            ->assertDontSee('modal-sucesso-evento', false);
        $this->post(route('ong.eventos.criar'))->assertMethodNotAllowed();
    }

    public function test_private_privacy_and_workspaces_redirect_visitors_to_login(): void
    {
        foreach (['perfil.privacidade', 'ong.privacidade', 'ong.eventos.index', 'admin.relatorios', 'organizador.eventos', 'notificacoes'] as $name) {
            $this->get(route($name).'?contexto=administrador&visao=master')->assertRedirect('/login');
        }
        $this->get('/ranking')->assertOk()->assertSee('data-web-ranking', false)->assertDontSee('data-ranking-fixture', false);
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

    public function test_public_profiles_keep_public_projection_and_recheck_eligibility(): void
    {
        DB::statement('CREATE TABLE pessoa (pessoa_id INTEGER PRIMARY KEY, nm_pessoa TEXT, role_pessoa TEXT, status_validacao TEXT, xp_pessoa INTEGER, login_pessoa TEXT, email_pessoa TEXT, exclusao_pendente BOOLEAN)');
        DB::statement('CREATE TABLE ong (ong_id INTEGER PRIMARY KEY, nome_fantasia TEXT, descricao_ong TEXT, status_ong TEXT, cidade_ong TEXT, uf_ong TEXT, login_ong TEXT, email_ong TEXT, exclusao_pendente BOOLEAN)');
        DB::table('pessoa')->insert([
            'pessoa_id' => 42, 'nm_pessoa' => 'Pessoa Pública Sintética', 'role_pessoa' => 'voluntario',
            'status_validacao' => 'aprovado', 'xp_pessoa' => 200, 'email_pessoa' => 'privado@example.test',
            'exclusao_pendente' => false,
        ]);
        DB::table('ong')->insert([
            'ong_id' => 7, 'nome_fantasia' => 'ONG Pública Sintética', 'descricao_ong' => 'Atividade comunitária.',
            'status_ong' => 'ativo', 'cidade_ong' => 'Guarulhos', 'uf_ong' => 'SP',
            'email_ong' => 'privado@example.test', 'exclusao_pendente' => false,
        ]);

        $this->get('/pessoas/42/perfil?contexto=administrador&visao=master')->assertOk()
            ->assertViewIs('perfil.index-perfil')->assertSee('Pessoa Pública Sintética')
            ->assertSee('200 XP')->assertDontSee('privado@example.test')->assertDontSee('Meu cadastro');
        $this->get('/ongs/7/perfil?contexto=ong')->assertOk()->assertViewIs('ong.perfil_ong')
            ->assertSee('ONG Pública Sintética')->assertSee('Atividade comunitária.')
            ->assertDontSee('privado@example.test')->assertDontSee('Criar primeiro evento');

        DB::table('pessoa')->where('pessoa_id', 42)->update(['exclusao_pendente' => true]);
        DB::table('ong')->where('ong_id', 7)->update(['status_ong' => 'reprovado']);
        $this->get('/pessoas/42/perfil')->assertNotFound();
        $this->get('/ongs/7/perfil')->assertNotFound();
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
            ->assertDontSee('href="'.route('perfil').'"', false)->assertSee('href="'.route('conta').'"', false)
            ->assertSeeInOrder(['admin-stats', 'admin-corpo', 'admin-tabs', 'data-admin-capability="lgpd.visualizar"', 'Atalhos administrativos'], false)
            ->assertDontSee('data-admin-capability="eventos.aprovar"', false)
            ->assertDontSee('Resposta a solicitações de privacidade');
        $this->get('/admin/solicitacoes-lgpd')->assertOk()->assertSee('data-admin-operations="solicitacoes-lgpd"', false)
            ->assertSee('data-privacy-read="1" data-privacy-respond="0"', false)
            ->assertDontSee('href="'.route('admin.conclusoes-padrinho').'"', false);
        $this->patch('/admin/solicitacoes-lgpd/1/status')->assertForbidden();
        DB::table('permissao_pessoa')->where('pessoa_id', 42)->update(['permissao_id' => 2]);
        $this->get('/admin')->assertOk()->assertSee('Resposta a solicitações de privacidade')
            ->assertDontSee('Consulta de solicitações de privacidade')
            ->assertDontSee(route('admin.solicitacoes-lgpd'));
        $this->get('/admin/solicitacoes-lgpd')->assertForbidden();
        $this->get('/admin/responder-lgpd')->assertOk()->assertSee('Voltar ao painel')
            ->assertSee('data-privacy-read="0" data-privacy-respond="1"', false);
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

    public function test_admin_roles_cannot_enter_personal_web_journeys(): void
    {
        DB::statement('CREATE TABLE permissao (permissao_id INTEGER PRIMARY KEY, nome TEXT)');
        DB::statement('CREATE TABLE permissao_pessoa (permissao_id INTEGER, pessoa_id INTEGER)');
        DB::table('permissao')->insert([
            ['permissao_id' => 1, 'nome' => 'lgpd.visualizar'],
            ['permissao_id' => 2, 'nome' => 'lgpd.responder'],
        ]);
        DB::table('permissao_pessoa')->insert([
            ['permissao_id' => 1, 'pessoa_id' => 1],
            ['permissao_id' => 2, 'pessoa_id' => 2],
        ]);

        foreach (['Leitor', 'Respondente', 'Master'] as $index => $label) {
            $admin = (new Pessoa)->forceFill([
                'pessoa_id' => $index + 1, 'nm_pessoa' => $label, 'role_pessoa' => 'administrador',
                'admin_master' => $label === 'Master', 'status_validacao' => 'aprovado',
            ]);
            $this->sessionFor($admin);

            foreach (['/admin', '/notificacoes', '/'] as $page) {
                $this->get($page.'?contexto=pessoa&papel=voluntario')->assertOk()
                    ->assertDontSee('Meu perfil')->assertDontSee('href="'.route('perfil').'"', false)
                    ->assertDontSee('href="'.route('candidaturas').'"', false)
                    ->assertDontSee('href="'.route('ranking').'"', false)
                    ->assertSee('Sair');
            }
            $this->get('/admin')->assertOk()->assertSee('Gerenciar minha conta')
                ->assertSee('id="admin-menu"', false)->assertSee('aria-controls="admin-sidebar"', false)
                ->assertDontSee('Segurança da conta');
            $query = '?contexto=pessoa&papel=voluntario&role_pessoa=voluntario&admin_master=0';
            $this->get('/conta'.$query)->assertOk()->assertSee($label)->assertSee('Alterar senha')
                ->assertDontSee('Excluir conta')->assertDontSee('Solicitar exclusão')
                ->assertDontSee('action="'.route('identity.deletion.schedule').'"', false);
            $this->post('/conta/exclusao'.$query, ['confirmar_exclusao' => '1', 'role_pessoa' => 'voluntario'])
                ->assertForbidden();
            $this->postJson('/conta/exclusao'.$query)->assertForbidden();

            foreach (['/perfil', '/perfil/editar', '/perfil/preferencias', '/perfil/privacidade',
                '/candidatura', '/minhas-candidaturas', '/minhas-participacoes', '/meus-certificados'] as $page) {
                $url = $page.'?contexto=pessoa&papel=voluntario&jornada=independente&visao=master&apadrinhamento=padrinho&evento=1';
                $this->get($url)->assertForbidden();
                $this->head($url)->assertForbidden();
            }
            $this->get('/organizador?contexto=organizador')->assertForbidden();
            $this->get('/ranking')->assertOk();
            $this->getJson('/api/v1/minhas-inscricoes')->assertUnauthorized();
            $this->getJson('/api/v1/padrinhos/me')->assertUnauthorized();
        }
    }

    public function test_admin_api_cannot_schedule_deletion_or_change_existing_history(): void
    {
        $this->createDeletionTables();
        DB::table('admin_log')->insert(['pessoa_id' => 1, 'acao' => 'validacao.registrada']);
        DB::table('permissao')->insert([
            ['permissao_id' => 1, 'nome' => 'lgpd.visualizar'],
            ['permissao_id' => 2, 'nome' => 'lgpd.responder'],
        ]);
        DB::table('permissao_pessoa')->insert([
            ['permissao_id' => 1, 'pessoa_id' => 1],
            ['permissao_id' => 2, 'pessoa_id' => 2],
        ]);

        foreach ([1, 2, 3] as $id) {
            DB::table('pessoa')->insert([
                'pessoa_id' => $id, 'role_pessoa' => 'administrador',
                'admin_master' => $id === 3, 'auth_version' => 7,
            ]);
            $actor = Pessoa::findOrFail($id);
            if ($id === 1) {
                $actor->forceFill(['role_pessoa' => 'voluntario']);
            }
            $token = (new PersonalAccessToken)->forceFill(['abilities' => ['pessoa']]);
            $this->actingAs($actor->withAccessToken($token), 'sanctum');

            $this->deleteJson("/api/v1/pessoas/{$id}?contexto=pessoa&role_pessoa=voluntario", [
                'papel' => 'voluntario', 'admin_master' => false,
            ])->assertForbidden();
            $this->assertDatabaseHas('pessoa', [
                'pessoa_id' => $id, 'exclusao_pendente' => false, 'deletar_em' => null, 'auth_version' => 7,
            ]);
        }

        $this->assertDatabaseCount('admin_log', 1);
        $this->assertDatabaseHas('admin_log', ['pessoa_id' => 1, 'acao' => 'validacao.registrada']);
    }

    public function test_volunteer_and_organization_can_schedule_and_cancel_deletion(): void
    {
        $this->createDeletionTables();
        $this->freezeTime();
        DB::table('pessoa')->insert(['pessoa_id' => 1, 'role_pessoa' => 'voluntario', 'auth_version' => 7]);
        DB::table('ong')->insert(['ong_id' => 2, 'auth_version' => 7]);

        foreach ([Pessoa::findOrFail(1), Ong::findOrFail(2)] as $account) {
            $this->sessionFor($account);
            $this->get('/conta')->assertOk()->assertSee('Excluir conta')->assertSee('Solicitar exclusão');
            $this->post('/conta/exclusao', ['confirmar_exclusao' => '1'])->assertRedirect('/login');
            $account->refresh();
            $this->assertTrue($account->exclusao_pendente);
            $this->assertSame(now()->addDays(7)->toDateTimeString(), $account->deletar_em->toDateTimeString());
            $this->assertSame(8, $account->auth_version);
            $this->sessionFor($account);

            $this->get('/conta')->assertOk()->assertSee('Cancelar exclusão')->assertDontSee('Solicitar exclusão');
            $this->patch('/conta/exclusao')->assertRedirect('/login');
            $account->refresh();
            $this->assertFalse($account->exclusao_pendente);
            $this->assertNull($account->deletar_em);
            $this->assertSame(9, $account->auth_version);

            foreach (['conta.exclusao_agendada', 'conta.exclusao_cancelada'] as $action) {
                $this->assertDatabaseHas('admin_log', [
                    'acao' => $action, 'recurso_type' => $account->getMorphClass(), 'recurso_id' => $account->getKey(),
                ]);
            }
        }
        $this->assertDatabaseCount('admin_log', 4);
    }

    private function createDeletionTables(): void
    {
        DB::statement('CREATE TABLE pessoa (pessoa_id INTEGER PRIMARY KEY, role_pessoa TEXT, admin_master BOOLEAN DEFAULT 0, auth_version INTEGER, exclusao_pendente BOOLEAN DEFAULT 0, deletar_em DATETIME, updated_at DATETIME)');
        DB::statement('CREATE TABLE ong (ong_id INTEGER PRIMARY KEY, auth_version INTEGER, exclusao_pendente BOOLEAN DEFAULT 0, deletar_em DATETIME, updated_at DATETIME)');
        DB::statement('CREATE TABLE evento (evento_id INTEGER PRIMARY KEY, ong_id INTEGER, pessoa_id INTEGER, responsavel_pessoa_id INTEGER, status_evento TEXT)');
        DB::statement('CREATE TABLE vinculo_ong_organizador (ong_id INTEGER, pessoa_id INTEGER, status TEXT)');
        DB::statement('CREATE TABLE agenda (agenda_id INTEGER PRIMARY KEY, evento_id INTEGER, status_ativo TEXT)');
        DB::statement('CREATE TABLE p_ag_inscreve (pessoa_id INTEGER, agenda_id INTEGER, status_inscricao TEXT)');
        DB::statement('CREATE TABLE solicitacao_organizador (pessoa_id INTEGER, status TEXT)');
        DB::statement('CREATE TABLE acompanhamento (padrinho_id INTEGER, apadrinhado_id INTEGER, status TEXT)');
        DB::statement('CREATE TABLE solicitacao_padrinho (padrinho_id INTEGER, apadrinhado_id INTEGER, status TEXT)');
        DB::statement('CREATE TABLE admin_log (admin_log_id INTEGER PRIMARY KEY, pessoa_id INTEGER, acao TEXT, recurso_type TEXT, recurso_id INTEGER, detalhes TEXT, created_at DATETIME, updated_at DATETIME)');
        DB::statement('CREATE TABLE personal_access_tokens (id INTEGER PRIMARY KEY, tokenable_type TEXT, tokenable_id INTEGER)');
        DB::statement('CREATE TABLE permissao (permissao_id INTEGER PRIMARY KEY, nome TEXT)');
        DB::statement('CREATE TABLE permissao_pessoa (permissao_id INTEGER, pessoa_id INTEGER)');
    }

    public function test_admin_bearer_does_not_open_personal_api_resources(): void
    {
        DB::statement('CREATE TABLE pessoa (pessoa_id INTEGER PRIMARY KEY, role_pessoa TEXT, status_validacao TEXT)');
        DB::table('pessoa')->insert(['pessoa_id' => 2, 'role_pessoa' => 'voluntario', 'status_validacao' => 'aprovado']);
        $admin = (new Pessoa)->forceFill([
            'pessoa_id' => 1, 'role_pessoa' => 'administrador', 'admin_master' => true,
        ]);
        $token = (new PersonalAccessToken)->forceFill(['abilities' => ['pessoa']]);
        $this->actingAs($admin->withAccessToken($token), 'sanctum');

        foreach (['/api/v1/minhas-inscricoes', '/api/v1/pessoas/2/inscricoes',
            '/api/v1/pessoas/2/confiabilidade', '/api/v1/padrinhos/me', '/api/v1/eventos/1/certificado',
            '/api/v1/pessoas/me/solicitacao-organizador', '/api/v1/pessoas/me/convites-ong'] as $page) {
            $this->getJson($page.'?contexto=pessoa&papel=voluntario')->assertForbidden();
        }
        $this->postJson('/api/v1/eventos/1/certificado')->assertForbidden();
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
        $this->get('/organizador/eventos/criar')->assertOk()->assertViewIs('ong.criar');
        DB::table('vinculo_ong_organizador')->insert(['pessoa_id' => 42, 'ong_id' => 8, 'status' => 'ativo']);
        $this->get('/organizador?contexto=pessoa&jornada=independente')->assertOk()
            ->assertSee('Organizador vinculado a Instituição Vinculada')
            ->assertSee('id="navbar-links"', false)->assertDontSee('Criar Evento')
            ->assertDontSee('href="'.route('conta').'"', false)
            ->assertSee('Convites recebidos')->assertSee('Histórico de vínculos')->assertSee('Ver gestão de Eventos');
        $this->get('/organizador/eventos/criar?jornada=independente')->assertForbidden();
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
        $account->forceFill(['auth_version' => $account->auth_version ?? 7]);
        $guard->setUser($account);
        $this->withSession([
            $guard->getName() => $account->getKey(),
            'web_identity' => [
                'type' => $type, 'id' => $account->getKey(), 'version' => $account->auth_version,
                'started_at' => now()->timestamp, 'last_activity' => now()->timestamp,
            ],
        ]);
    }
}
