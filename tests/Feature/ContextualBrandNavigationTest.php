<?php

namespace Tests\Feature;

use App\Services\WebApi;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ContextualBrandNavigationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['session.driver' => 'array', 'cache.default' => 'array']);
        DB::shouldReceive('connection')->andThrow(new \LogicException('Database forbidden.'));
        Http::preventStrayRequests();
        $this->mock(WebApi::class)->shouldNotReceive('call');
    }

    private function assertBrands(string $html, string $expected): void
    {
        preg_match_all('~<a\b[^>]*data-brand-home[^>]*href="([^"]+)"~', $html, $matches);
        $this->assertNotEmpty($matches[1]);
        foreach ($matches[1] as $url) {
            $this->assertSame($expected, html_entity_decode($url));
        }
    }

    public function test_every_shared_public_surface_uses_the_observers_contextual_home(): void
    {
        foreach (['visitante' => 'inicio', 'pessoa' => 'feed', 'organizador' => 'organizador.workspace', 'organizador-vinculado' => 'organizador.workspace', 'ong' => 'ong.painel', 'administrador' => 'admin.admin'] as $context => $home) {
            foreach (['/', '/feed', '/perfil?area=publica&pessoa=2', '/ong/perfil?ong=2', '/termos', '/privacidade', '/login'] as $page) {
                $response = $this->get($page.(str_contains($page, '?') ? '&' : '?').'contexto='.$context)->assertOk();
                $this->assertBrands($response->getContent(), route($home, ['contexto' => $context]));
                if ($context !== 'visitante') {
                    // The login form can still say Entrar; its navigation must not offer the visitor link.
                    preg_match('~<nav\b.*?</nav>~s', $response->getContent(), $nav);
                    $this->assertStringNotContainsString('href="/login"', $nav[0]);
                }
            }
        }
        Http::assertNothingSent();
    }

    public function test_private_profile_reuses_person_nav_and_groups_distinct_owner_actions(): void
    {
        foreach (['pessoa', 'organizador', 'organizador-vinculado'] as $context) {
            $html = $this->get('/perfil?area=privada&contexto='.$context)->assertOk()
                ->assertSee('data-nav-context="'.$context.'"', false)->assertDontSee('>Entrar<', false)->getContent();
            preg_match('~<div class="account-profile-actions"[^>]*>(.*?)</div>~s', $html, $group);
            $this->assertNotEmpty($group);
            $this->assertStringContainsString('Meu cadastro', $group[1]);
            $this->assertStringContainsString('Ver perfil público', $group[1]);
            $this->assertStringContainsString(e(route('perfil.editar', ['cenario' => 'aprovado', 'contexto' => $context])), $group[1]);
            $this->assertStringContainsString(e(route('perfil', ['area' => 'publica', 'pessoa' => 1, 'contexto' => $context])), $group[1]);
            $this->assertSame(2, substr_count($group[1], 'data-button-component="secundario"'));
        }
    }

    public function test_missing_parameters_keep_only_established_demo_context_and_valid_scenario(): void
    {
        $this->get('/feed?contexto=organizador-vinculado&jornada=vinculado-varios')->assertOk();
        foreach (['/eventos/1', '/perfil?area=publica&pessoa=2', '/ong/perfil?ong=2', '/feed'] as $page) {
            $html = $this->get($page)->assertOk()->assertSee('data-nav-context="organizador-vinculado"', false)->getContent();
            $this->assertBrands($html, route('organizador.workspace', ['contexto' => 'organizador-vinculado', 'jornada' => 'vinculado-varios']));
        }
        $this->assertSame(['context' => 'organizador-vinculado', 'journey' => 'vinculado-varios'], session('demo_navigation'));
        $this->get('/feed?contexto=visitante')->assertOk();
        $this->get('/perfil?area=publica&pessoa=2')->assertOk()->assertSee('data-nav-context="visitante"', false)
            ->assertDontSee('Meu cadastro')->assertDontSee('Alterar preferências')->assertSee('Sara de Exemplo');
        $this->assertSame(['context' => 'visitante'], session('demo_navigation'));
    }

    public function test_cold_visitor_and_invalid_inputs_never_select_a_private_home(): void
    {
        foreach (['/', '/feed', '/perfil?area=publica&pessoa=2', '/ong/perfil?ong=2'] as $page) {
            $this->assertBrands($this->get($page)->assertOk()->getContent(), route('inicio', ['contexto' => 'visitante']));
        }
        foreach (['contexto[]=ong', 'contexto=https://example.test', 'contexto=//example.test'] as $query) {
            $html = $this->withSession(['demo_navigation' => ['context' => 'invalid', 'journey' => ['invalid']]])
                ->get('/feed?'.$query.'&return_url=https://example.test')->assertOk()->getContent();
            $this->assertBrands($html, route('inicio', ['contexto' => 'visitante']));
            $this->assertStringNotContainsString('href="https://example.test', $html);
        }
    }

    public function test_mock_login_logout_and_private_sidebar_brands_do_not_activate_authentication(): void
    {
        foreach (['pessoa' => '/feed', 'ong' => '/ong/painel'] as $context => $path) {
            $this->post('/login', ['tipo_login' => $context])->assertRedirect($path)->assertSessionMissing('bff');
            $html = $this->get($path)->assertOk()->getContent();
            $this->assertBrands($html, route($context === 'ong' ? 'ong.painel' : 'feed', ['contexto' => $context]));
        }
        $this->assertBrands(Blade::render('<x-ong-sidebar />'), route('ong.painel', ['contexto' => 'ong']));
        $this->get('/admin?contexto=administrador')->assertOk();
        $this->assertBrands(Blade::render('<x-admin-sidebar />'), route('admin.admin', ['contexto' => 'administrador']));
        $this->post('/logout')->assertRedirect('/login');
        $this->get('/feed')->assertOk()->assertSee('data-nav-context="visitante"', false);
        $this->assertFalse(auth()->check());
    }
}
