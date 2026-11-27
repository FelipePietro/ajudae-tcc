<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VisualNotificationsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['session.driver' => 'array', 'cache.default' => 'array']);
        DB::shouldReceive('connection')->andThrow(new \LogicException('Banco fora do escopo.'));
        Http::preventStrayRequests();
    }

    public function test_inboxes_render_only_the_current_synthetic_identity(): void
    {
        foreach (['pessoa', 'ong', 'organizador', 'organizador-vinculado', 'administrador'] as $context) {
            $this->get('/notificacoes?contexto='.$context)->assertOk()->assertSee('data-notification-center', false)
                ->assertSee('"identity":"'.$context.':1"', false)->assertDontSee('data-certificate-fixture', false);
        }
        $this->get('/ong/notificacoes?contexto=ong')->assertOk();
        $this->get('/notificacoes?contexto=pessoa&papel=padrinho')->assertOk()->assertSee('padrinho-principal');
        Http::assertNothingSent();
    }

    public function test_private_route_and_invalid_destination_selectors_are_rejected(): void
    {
        $this->get('/notificacoes?contexto=visitante')->assertForbidden();
        $this->get('/ong/notificacoes?contexto=pessoa')->assertForbidden();
        $this->get('/notificacoes?contexto=organizador&papel=padrinho')->assertNotFound();
        foreach (['pessoa=12', 'destinatario=ong', 'ong_id=2', 'avisos=invalido', 'papel=alheio'] as $query) {
            $this->get('/notificacoes?contexto=pessoa&'.$query)->assertNotFound();
        }
        $this->get('/feed?contexto=visitante')->assertOk()->assertDontSee('data-notification-config', false);
    }

    public function test_filters_scenarios_and_admin_grants_do_not_grant_write_access(): void
    {
        foreach (['vazio', 'carregando', 'falha', 'indisponivel', 'destino-ausente'] as $scenario) {
            $this->get('/notificacoes?contexto=pessoa&avisos='.$scenario)->assertOk()->assertSee('data-notification-filters', false);
        }
        $this->get('/notificacoes?contexto=administrador&visao=revisao')->assertOk()
            ->assertSee('admin-sidebar', false)->assertSee('"permissions":["usuarios.validar","eventos.aprovar"]', false)->assertDontSee('lgpd.visualizar');
        $this->get('/notificacoes?contexto=administrador&visao=privacidade')->assertOk()
            ->assertSee('"permissions":["lgpd.visualizar"]', false)->assertDontSee('usuarios.validar');
        $this->get('/admin?contexto=administrador&visao=revisao')->assertOk()->assertSee('data-notification-entry', false);
    }
}
