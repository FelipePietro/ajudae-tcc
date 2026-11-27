<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VisualFeedbackTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['session.driver' => 'array', 'cache.default' => 'array', 'app.debug' => false]);
        DB::shouldReceive('connection')->andThrow(new \LogicException('Database outside visual scope.'));
        Http::preventStrayRequests();
    }

    public function test_http_screens_preserve_status_and_avoid_private_details(): void
    {
        foreach ([401, 403, 404, 409, 419, 422, 500, 503] as $status) {
            $this->get('/inspecao/estados/'.$status.'?inspecao=1&contexto=ong')
                ->assertStatus($status)->assertSee('data-http-state="'.$status.'"', false)
                ->assertSee('Ir para o início')->assertDontSee('Stack trace')->assertDontSee('SQLSTATE');
        }
        $this->get('/inspecao/estados/500')->assertNotFound();
        $this->get('/inspecao/estados/999?inspecao=1')->assertNotFound();
        Http::assertNothingSent();
    }

    public function test_real_denials_keep_status_and_contextual_navigation(): void
    {
        $this->get('/perfil/privacidade?contexto=ong')->assertForbidden()
            ->assertSee('Acesso negado')->assertDontSee('>Entrar<', false);
        $this->get('/caminho-que-nao-existe?contexto=pessoa')->assertNotFound()
            ->assertSee(route('feed', ['contexto' => 'pessoa']), false);
    }

    public function test_retry_preserves_persona_and_moves_to_available_scenario(): void
    {
        $this->get('/feed?contexto=ong&cenario=falha')->assertOk()
            ->assertSee('Tentar novamente')->assertSee('contexto=ong', false)
            ->assertSee('cenario=publicado', false);
        $this->get('/feed?contexto=ong&cenario=publicado')->assertOk()->assertDontSee('Não foi possível carregar os eventos.');
    }

    public function test_inspection_shortcuts_are_absent_in_normal_registration(): void
    {
        $this->get('/cadastro')->assertOk()->assertDontSee('data-fill-example', false)->assertDontSee('data-file-example', false);
        $this->get('/cadastro?inspecao=1')->assertOk()->assertSee('data-fill-example', false)->assertSee('data-file-example', false);
        foreach (['/perfil/privacidade?contexto=pessoa', '/admin/solicitacoes-lgpd?contexto=administrador', '/perfil/editar?contexto=pessoa'] as $url) {
            $this->get($url)->assertOk()->assertDontSee('nesta aba')->assertDontSee('cenário fictício')->assertDontSee('backend');
        }
    }
}
