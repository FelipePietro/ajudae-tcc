<?php

namespace Tests\Feature;

use App\Support\PrivacyFixtures;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VisualPrivacyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['session.driver' => 'array', 'cache.default' => 'array']);
        DB::shouldReceive('connection')->andThrow(new \LogicException('Database outside visual scope.'));
        Http::preventStrayRequests();
    }

    public function test_private_journeys_and_existing_deletion_routes_share_the_same_contract(): void
    {
        foreach (['pessoa', 'organizador', 'organizador-vinculado'] as $context) {
            foreach (['/perfil/privacidade', '/perfil/excluir', '/perfil/exclusao-pendente'] as $path) {
                $this->get($path.'?contexto='.$context)->assertOk()->assertSee('data-privacy-config', false)
                    ->assertSee('pessoa:1')->assertSee('7 dias')->assertDontSee('blob:')->assertDontSee('base64');
            }
        }
        foreach (['/ong/privacidade', '/ong/perfil/excluir', '/ong/perfil/exclusao-pendente'] as $path) {
            $this->get($path.'?contexto=ong')->assertOk()->assertSee('ong:1')->assertDontSee('data-local-file', false);
        }
        Http::assertNothingSent();
    }

    public function test_negative_contexts_and_foreign_identity_selectors_are_rejected(): void
    {
        foreach (['visitante', 'ong', 'administrador'] as $context) {
            $this->get('/perfil/privacidade?contexto='.$context)->assertForbidden();
            $this->get('/perfil/excluir?contexto='.$context)->assertForbidden();
        }
        $this->get('/ong/privacidade?contexto=pessoa')->assertForbidden();
        foreach (['pessoa=2', 'ong_id=2', 'titular=2', 'privacidade=invalida'] as $selector) {
            $this->get('/perfil/privacidade?contexto=pessoa&'.$selector)->assertNotFound();
        }
    }

    public function test_scenarios_permissions_and_public_projection(): void
    {
        foreach (PrivacyFixtures::SCENARIOS as $scenario) {
            $this->get('/perfil/privacidade?contexto=pessoa&privacidade='.$scenario)->assertOk();
        }
        $this->get('/perfil/excluir?contexto=pessoa&privacidade=pendencias')->assertOk()->assertSee('Inscrição aprovada na Agenda 101')->assertSee('Apadrinhamento ativo');
        $this->get('/ong/perfil/excluir?contexto=ong&privacidade=pendencias')->assertOk()->assertSee('Evento 7 em andamento');
        $this->get('/admin/solicitacoes-lgpd?contexto=administrador&visao=privacidade')->assertOk()->assertSee('"respond":false', false);
        $this->get('/admin/solicitacoes-lgpd?contexto=administrador')->assertOk()->assertSee('"respond":true', false);
        foreach (['pessoa', 'ong', 'visitante'] as $context) {
            $this->get('/admin/solicitacoes-lgpd?contexto='.$context)->assertForbidden();
        }
        $this->get('/admin/solicitacoes-lgpd?contexto=administrador&visao=revisao')->assertForbidden();
        foreach (['pendente', 'prazo-encerrado', 'anonimizada'] as $scenario) {
            $this->get('/perfil?area=publica&pessoa=1&contexto=visitante&privacidade='.$scenario)->assertNotFound();
            $this->get('/ong/perfil?ong=1&contexto=visitante&privacidade='.$scenario)->assertNotFound();
            $this->get('/ranking?contexto=visitante&privacidade='.$scenario)->assertOk()->assertViewHas('people', fn ($people) => ! collect($people)->contains('id', 1));
        }
        $this->get('/perfil?area=publica&pessoa=2&contexto=visitante&privacidade=anonimizada')->assertOk()->assertDontSee('data-privacy-config', false);
    }
}
