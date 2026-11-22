<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VisualParticipationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['session.driver' => 'array', 'cache.default' => 'array']);
        DB::shouldReceive('connection')->andThrow(new \LogicException('Banco fora do escopo.'));
        Http::preventStrayRequests();
    }

    public function test_personal_views_share_the_event_and_only_render_the_holder_records(): void
    {
        foreach (['/minhas-participacoes?contexto=pessoa', '/minhas-candidaturas?contexto=pessoa&cenario=participacao&evento=7'] as $path) {
            $this->get($path)->assertOk()
                ->assertSee('Oficina de apoio comunitário')
                ->assertSee('data-participation-record="70101"', false)
                ->assertSee('data-participation-action="justificar"', false)
                ->assertDontSee('data-participation-record="70111"', false)
                ->assertDontSee('data-participation-action="presente"', false);
        }

        $this->get('/minhas-participacoes?contexto=organizador')->assertOk()
            ->assertSee('data-participation-record="70601"', false)
            ->assertSee('data-participation-record="70701"', false)
            ->assertDontSee('data-participation-record="70101"', false);
        $this->get('/minhas-participacoes?contexto=pessoa&situacao=vazio')->assertOk()->assertSee('Você ainda não tem participações');
        $this->get('/minhas-participacoes?contexto=pessoa&situacao=indisponivel')->assertOk()->assertSee('Tentar novamente');
        $this->get('/minhas-participacoes?contexto=pessoa&pessoa=11')->assertNotFound();
        $this->get('/minhas-participacoes?contexto=pessoa&situacao[]=vazio')->assertNotFound();
        $this->get('/perfil?area=publica&pessoa=1&contexto=pessoa')->assertOk()
            ->assertDontSee('Minhas participações')->assertDontSee('data-participation-fixture', false);
        Http::assertNothingSent();
    }

    public function test_operational_routes_require_the_institution_or_authorized_organizer(): void
    {
        foreach ([
            '/ong/eventos/7/presenca?contexto=ong',
            '/ong/eventos/1/presenca?contexto=organizador-vinculado&jornada=vinculado',
            '/ong/eventos/8/presenca?contexto=organizador&jornada=independente-eventos',
        ] as $path) {
            $this->get($path)->assertOk()->assertSee('Presença e monitoria')->assertSee('Marcar presença');
        }
        foreach (['visitante', 'pessoa', 'administrador', 'organizador', 'organizador-vinculado'] as $context) {
            $this->get('/ong/eventos/7/presenca?contexto='.$context)->assertForbidden()->assertDontSee('data-participation-fixture', false);
        }
        $this->get('/ong/eventos/8/presenca?contexto=ong')->assertForbidden();
        $this->get('/ong/eventos/99/presenca?contexto=ong')->assertNotFound();
        $this->get('/minhas-participacoes?contexto=ong')->assertForbidden();
        $this->get('/minhas-candidaturas?contexto=visitante&cenario=participacao')->assertForbidden();
        $this->get('/ong/candidatos?contexto=pessoa&evento=7&cenario=participacao')->assertForbidden();
    }

    public function test_routes_link_participation_to_the_original_management_and_private_navigation(): void
    {
        $this->get('/ong/eventos?contexto=ong')->assertOk()->assertSee('/ong/eventos/7/presenca', false);
        $this->get('/ong/eventos/7/editar?contexto=ong')->assertOk()->assertSee('/ong/eventos/7/presenca', false);
        $this->get('/ong/eventos/7/presenca?contexto=ong&cenario=reanalise')->assertOk()->assertSee('O evento está em consulta.');
        $this->get('/ong/candidatos?contexto=ong&evento=7&cenario=participacao')->assertOk()
            ->assertSee('Oficina de apoio comunitário')->assertSee('data-participation-selection="70101"', false)
            ->assertSee('#agenda-702', false);
        $this->get('/perfil?area=privada&contexto=pessoa')->assertOk()->assertSee('/minhas-participacoes', false);
        $this->get('/eventos/7?contexto=visitante&cenario=participacao')->assertOk()
            ->assertSee('Agenda cancelada')->assertDontSee('data-participation-record', false)
            ->assertDontSee('Monitoria estudantil pendente');
    }
}
