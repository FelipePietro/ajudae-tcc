<?php

namespace Tests\Unit;

use App\Http\Middleware\RequireBearerAccount;
use App\Http\Middleware\ResolveWebAccount;
use App\Models\Evento;
use App\Models\Ong;
use App\Models\Pessoa;
use App\Policies\EventoPolicy;
use App\Policies\PessoaPolicy;
use App\Services\AccountCredentials;
use App\Support\AccountAccess;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;
use Laravel\Sanctum\TransientToken;
use Tests\TestCase;

class IdentityBoundaryTest extends TestCase
{
    public function test_session_boundaries_are_inclusive(): void
    {
        $this->assertFalse(ResolveWebAccount::expired(['started_at' => 1, 'last_activity' => 1], 1800));
        $this->assertTrue(ResolveWebAccount::expired(['started_at' => 1, 'last_activity' => 1], 1801));
        $this->assertFalse(ResolveWebAccount::expired(['started_at' => 1, 'last_activity' => 43200], 43200));
        $this->assertTrue(ResolveWebAccount::expired(['started_at' => 1, 'last_activity' => 43200], 43201));
        $this->assertTrue(ResolveWebAccount::expired(['started_at' => 6, 'last_activity' => 5], 10));
        $this->assertTrue(ResolveWebAccount::expired(['started_at' => 1, 'last_activity' => 30], 10));
        $this->assertTrue(ResolveWebAccount::expired([], 10));
    }

    public function test_deletion_deadline_and_final_rejection_close_normal_access(): void
    {
        $this->freezeTime();
        foreach ([new Pessoa, new Ong] as $account) {
            $account->setDateFormat('Y-m-d H:i:s');
            $account->forceFill(['exclusao_pendente' => true, 'deletar_em' => now()->addSecond()]);
            $this->assertTrue(AccountAccess::canSignIn($account));
            $this->assertFalse(AccountAccess::canOperate($account));
            $account->deletar_em = now();
            $this->assertFalse(AccountAccess::canSignIn($account));
        }

        $person = (new Pessoa)->forceFill(['status_validacao' => 'reprovado_definitivamente']);
        $this->assertFalse(AccountAccess::canSignIn($person));
    }

    public function test_homologation_accounts_are_disabled_outside_local_and_testing(): void
    {
        $pessoa = (new Pessoa)->forceFill(['login_pessoa' => 'QA72.pessoa.aprovada']);
        $ong = (new Ong)->forceFill(['login_ong' => 'qa72.ong.ativa']);

        $this->assertTrue(AccountAccess::canSignIn($pessoa));
        $this->assertTrue(AccountAccess::canSignIn($ong));

        $this->app->instance('env', 'production');

        $this->assertFalse(AccountAccess::canSignIn($pessoa));
        $this->assertFalse(AccountAccess::canSignIn($ong));
        $this->assertTrue(AccountAccess::canSignIn((new Pessoa)->forceFill(['login_pessoa' => 'pessoa.comum'])));
    }

    public function test_integrated_landing_is_public_without_reenabling_demo_routes(): void
    {
        if (! config('identity.enabled')) {
            $this->markTestSkipped('Execute no modo integrado.');
        }

        $this->get('/?contexto=administrador&visao=privacidade&papel=administrador&cenario=demo')
            ->assertOk()
            ->assertSee('Faça a diferença.')
            ->assertSee(route('cadastro.pessoa'))
            ->assertSee(route('ong.cadastrar.form'))
            ->assertSee(route('feed'))
            ->assertDontSee('/feed?contexto=')
            ->assertDontSee('data-notification-context');
        $this->get('/termos')->assertOk();
        $this->get('/privacidade')->assertOk();
        $this->get('/login?tipo=ong')->assertOk()->assertSee('value="ong" checked', false);
        $this->get('/login?tipo[]=administrador')->assertOk()->assertSee('value="pessoa" checked', false);
        $this->get('/perfil?papel=administrador')->assertRedirect('/login');
        $this->get('/admin?contexto=administrador')->assertRedirect('/login');
        $this->get('/cadastro')->assertOk()->assertSee('O cadastro está indisponível');
        $this->get('/certificados/verificar')->assertNotFound();
    }

    public function test_bearer_middleware_rejects_transient_and_wrong_type_abilities(): void
    {
        $person = new Pessoa;
        $request = Request::create('/api/v1/pessoas/me');
        $request->setUserResolver(fn () => $person);
        $route = (new Route('GET', 'api/v1/pessoas/me', fn () => null))->name('api.pessoa.me');
        $request->setRouteResolver(fn () => $route);
        $middleware = new RequireBearerAccount;
        $respond = fn () => response()->noContent();

        $person->withAccessToken(new TransientToken);
        $this->assertSame(401, $middleware->handle($request, $respond)->getStatusCode());

        $person->withAccessToken(new PersonalAccessToken(['abilities' => ['ong']]));
        $this->assertSame(403, $middleware->handle($request, $respond)->getStatusCode());

        $person->withAccessToken(new PersonalAccessToken(['abilities' => ['pessoa']]));
        $person->setDateFormat('Y-m-d H:i:s');
        $person->forceFill(['exclusao_pendente' => true, 'deletar_em' => now()->addDay()]);
        $this->assertSame(204, $middleware->handle($request, $respond)->getStatusCode());
        $route->name('.privada');
        $this->assertSame(403, $middleware->handle($request, $respond)->getStatusCode());
    }

    public function test_web_guard_alone_cannot_authenticate_api(): void
    {
        Auth::guard('web_pessoa')->setUser((new Pessoa)->forceFill(['pessoa_id' => 42]));
        $this->getJson('/api/v1/pessoas/me')->assertUnauthorized();
    }

    public function test_login_dispatches_to_the_explicit_guard_without_using_a_database(): void
    {
        if (! config('identity.enabled')) {
            $this->markTestSkipped('Execute no modo integrado.');
        }

        $this->get('/conta')->assertRedirect('/login');
        $this->get('/admin?contexto=administrador')->assertRedirect('/login');
        $this->get('/inspecao/estados/ativo')->assertNotFound();

        $ong = (new Ong)->forceFill([
            'ong_id' => 42, 'auth_version' => 7, 'status_ong' => 'ativo',
            'senha_ong' => password_hash('credencial-sintetica', PASSWORD_BCRYPT),
        ]);
        $personGuardKey = Auth::guard('web_pessoa')->getName();
        $ongGuardKey = Auth::guard('web_ong')->getName();
        $session = $this->app['session.store'];
        $beforeLogin = [$session->getId(), $session->token()];
        $this->mock(AccountCredentials::class)
            ->shouldReceive('authenticate')->once()->with('ong', 'conta.sintetica', 'credencial-sintetica')->andReturn($ong);

        $this->post('/login', [
            'tipo_login' => 'ong',
            'login' => 'conta.sintetica',
            'senha' => 'credencial-sintetica',
        ])->assertRedirect('/ong/painel')
            ->assertSessionHas('web_identity.type', 'ong')
            ->assertSessionHas('web_identity.version', 7)
            ->assertSessionHas($ongGuardKey, 42)
            ->assertSessionMissing($personGuardKey)
            ->assertSessionMissing('_old_input.senha');

        $beforeSwitch = [$session->getId(), $session->token()];
        $this->assertNotSame($beforeLogin[0], $beforeSwitch[0]);
        $this->assertNotSame($beforeLogin[1], $beforeSwitch[1]);

        $pessoa = (new Pessoa)->forceFill([
            'pessoa_id' => 42, 'auth_version' => 3,
            'senha_pessoa' => password_hash('outra-credencial-sintetica', PASSWORD_BCRYPT),
        ]);
        $this->mock(AccountCredentials::class)
            ->shouldReceive('authenticate')->once()->with('pessoa', 'pessoa.sintetica', 'outra-credencial-sintetica')->andReturn($pessoa);

        $this->post('/login', [
            'tipo_login' => 'pessoa',
            'login' => 'pessoa.sintetica',
            'senha' => 'outra-credencial-sintetica',
        ])->assertRedirect('/perfil')
            ->assertSessionHas('web_identity.type', 'pessoa')
            ->assertSessionHas('web_identity.version', 3)
            ->assertSessionHas($personGuardKey, 42)
            ->assertSessionMissing($ongGuardKey);

        $beforeLogout = [$session->getId(), $session->token()];
        $this->assertNotSame($beforeSwitch[0], $beforeLogout[0]);
        $this->assertNotSame($beforeSwitch[1], $beforeLogout[1]);

        $this->post('/logout')->assertRedirect('/login')->assertSessionMissing('web_identity');
        $this->assertNotSame($beforeLogout[0], $session->getId());
        $this->assertNotSame($beforeLogout[1], $session->token());
    }

    public function test_reader_cannot_reach_privacy_write_handler(): void
    {
        $admin = \Mockery::mock(Pessoa::class)->makePartial();
        $admin->forceFill(['pessoa_id' => 8, 'role_pessoa' => 'administrador']);
        $admin->shouldReceive('hasPermission')->with('lgpd.responder')->once()->andReturnFalse();
        Sanctum::actingAs($admin, ['pessoa']);

        $this->patchJson('/api/v1/admin/solicitacoes-lgpd/1/status', [
            'status' => 'em_analise',
        ])->assertForbidden();
    }

    public function test_login_limits_the_sixth_failure_for_the_same_type_login_and_ip(): void
    {
        if (! config('identity.enabled')) {
            $this->markTestSkipped('Execute no modo integrado.');
        }

        $this->mock(AccountCredentials::class)->shouldReceive('authenticate')->times(5)->andReturnNull();
        $payload = ['tipo_login' => 'pessoa', 'login' => 'sintetico.limitador', 'senha' => 'credencial-sintetica'];

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->from('/login')->post('/login', $payload)
                ->assertRedirect('/login')->assertSessionHasErrors('login')->assertSessionMissing('_old_input.senha');
        }

        $organization = (new Ong)->forceFill([
            'ong_id' => 42, 'auth_version' => 7, 'status_ong' => 'ativo', 'nome_fantasia' => 'Conta sintética anterior',
        ]);
        $guard = Auth::guard('web_ong');
        $guard->setUser($organization);

        $this->withSession([
            $guard->getName() => $organization->getKey(),
            'web_identity' => [
                'type' => 'ong', 'id' => 42, 'version' => 7,
                'started_at' => now()->timestamp, 'last_activity' => now()->timestamp,
            ],
        ])->post('/login', $payload)
            ->assertStatus(429)->assertHeader('Retry-After')
            ->assertSessionMissing('web_identity')->assertSessionMissing($guard->getName())
            ->assertDontSee(route('ong.perfil'))->assertDontSee(route('ong.painel'));
    }

    public function test_organization_ownership_activity_and_operational_responsibility(): void
    {
        $ong = (new Ong)->forceFill(['ong_id' => 8, 'status_ong' => 'aguardando aprovação']);
        $evento = (new Evento)->forceFill(['ong_id' => 8, 'responsavel_pessoa_id' => 8]);
        $policy = new EventoPolicy;

        $this->assertFalse($policy->manage($ong, $evento));
        $ong->status_ong = 'ativo';
        $this->assertTrue($policy->manage($ong, $evento));
        $this->assertFalse($policy->operate($ong, $evento));
        $evento->responsavel_pessoa_id = null;
        $this->assertTrue($policy->operate($ong, $evento));
        $evento->ong_id = 9;
        $this->assertFalse($policy->manage($ong, $evento));
    }

    public function test_admin_master_only_sees_the_assigned_case_during_review(): void
    {
        $admin = (new Pessoa)->forceFill(['pessoa_id' => 5, 'role_pessoa' => 'administrador', 'admin_master' => true]);
        $subject = (new Pessoa)->forceFill(['pessoa_id' => 6, 'status_validacao' => 'em_analise']);
        $policy = new PessoaPolicy;

        $this->assertFalse($policy->viewPrivateHistory($admin, $subject));
        $subject->setDateFormat('Y-m-d H:i:s');
        $subject->admin_analisando_id = 5;
        $subject->analise_assumida_em = now()->subHours(24);
        $this->assertFalse($policy->viewPrivateHistory($admin, $subject));
        $subject->analise_assumida_em = now();
        $this->assertTrue($policy->viewPrivateHistory($admin, $subject));
        $admin->role_pessoa = 'voluntario';
        $this->assertFalse($policy->viewPrivateHistory($admin, $subject));
    }
}
