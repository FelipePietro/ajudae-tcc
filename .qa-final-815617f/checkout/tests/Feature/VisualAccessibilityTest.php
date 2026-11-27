<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VisualAccessibilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['session.driver' => 'array', 'cache.default' => 'array']);
        DB::shouldReceive('connection')->andThrow(new \LogicException('Database outside visual scope.'));
        Http::preventStrayRequests();
    }

    public function test_brand_is_consistent_for_every_persona_and_pdf_template(): void
    {
        foreach (['visitante' => '/', 'pessoa' => '/perfil?area=privada', 'ong' => '/ong/painel', 'organizador' => '/organizador', 'organizador-vinculado' => '/organizador?jornada=vinculado-varios', 'administrador' => '/admin'] as $context => $path) {
            $this->get($path.(str_contains($path, '?') ? '&' : '?').'contexto='.$context)
                ->assertOk()->assertSee('Ajudae')->assertDontSee('Ajudaê')->assertDontSee('AJUDAÊ');
        }
        $certificate = (object) [
            'nome_participante' => 'Pessoa de Exemplo', 'nome_evento' => 'Ação comunitária',
            'total_minutos' => 120, 'nome_responsavel' => 'Organização de Exemplo',
            'emitido_em' => now(), 'codigo_verificacao' => 'AJD-'.str_repeat('A', 32),
        ];
        $html = view('certificados.documento', ['certificado' => $certificate])->render();
        $this->assertStringContainsString('AJUDAE', $html);
        $this->assertStringNotContainsString('AJUDAÊ', $html);
        $this->assertStringContainsString('Ação comunitária', $html);
        Http::assertNothingSent();
    }

    public function test_navigation_and_search_controls_have_accessible_names(): void
    {
        $this->get('/?contexto=visitante')->assertOk()->assertSee('aria-controls="menu-publico-mobile"', false);
        $this->get('/feed?contexto=pessoa')->assertOk()->assertSee('aria-label="Categoria do evento"', false)->assertSee('aria-label="Cidade do evento"', false);
        $this->get('/ong/eventos?contexto=ong')->assertOk()->assertSee('aria-label="Buscar meus eventos"', false);
        $this->get('/admin?contexto=administrador')->assertOk()->assertSee('data-admin-menu-close', false)->assertSee('<h1 class="sr-only">', false);
        $this->get('/candidatura?contexto=pessoa')->assertOk()->assertSee('<main ', false)->assertSee('>Sua candidatura</h1>', false);
        $html = $this->get('/candidatura?contexto=ong')->assertOk()->getContent();
        foreach (['data-application-next', 'data-apply-agenda', 'data-application-confirm'] as $action) {
            $this->assertMatchesRegularExpression('~<button(?=[^>]*'.$action.')(?=[^>]*disabled)[^>]*>~', $html);
        }
        $this->get('/candidatura?contexto=visitante')->assertOk()->assertSee(route('privacidade').'#contato', false)->assertDontSee('href="/contato"', false)->assertDontSee('href="/ong"', false);
    }

    public function test_landing_does_not_promise_unsupported_sensitive_operations(): void
    {
        $this->get('/?contexto=visitante')->assertOk()
            ->assertDontSee('geolocalização')->assertDontSee('timestamp imutável')->assertDontSee('emitido automaticamente')
            ->assertSee('A carta de apresentação é opcional.')->assertSee('decisão humana');
    }
}
