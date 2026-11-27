<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VisualParticipationClosureTest extends TestCase
{
    public function test_the_existing_management_route_exposes_the_checklist_and_guarded_controls(): void
    {
        Http::fake();

        $this->get('/ong/eventos/7/presenca?contexto=ong')->assertOk()
            ->assertSee('Encerramento do Evento')
            ->assertSee('Avaliações obrigatórias pendentes')
            ->assertSee('Finalizar Agenda 4')
            ->assertSee('Nenhuma pessoa selecionada nesta Agenda')
            ->assertSee('data-closure-action="finalizarEvento"', false);

        $this->get('/ong/eventos/8/presenca?contexto=organizador&jornada=independente-eventos&exemplo=encerramento')->assertOk()
            ->assertSee('"storageId":"8.encerramento"', false)
            ->assertSee('Minha atuação')
            ->assertSee('Agenda cancelada')
            ->assertSee('exemplo=encerramento', false);
        $this->get('/ong/eventos/1/presenca?contexto=organizador-vinculado&jornada=vinculado&exemplo=encerramento')->assertOk()
            ->assertSee('"storageId":"1.encerramento"', false)
            ->assertSee('"avaliacoesConcluidas":[]', false);

        Http::assertNothingSent();
    }

    public function test_personal_and_public_views_expose_status_without_management_payloads(): void
    {
        foreach (['/minhas-participacoes?contexto=pessoa', '/minhas-candidaturas?contexto=pessoa&cenario=participacao&evento=7'] as $path) {
            $this->get($path)->assertOk()->assertSee('Andamento do Evento')
                ->assertDontSee('data-closure-action', false)->assertDontSee('avaliacoesConcluidas')
                ->assertDontSee('data-participation-record="70111"', false);
        }

        $this->get('/eventos/7?contexto=visitante&cenario=participacao')->assertOk()
            ->assertSee('data-closure-agenda-badge="7"', false)
            ->assertDontSee('data-participation-fixture', false);
    }

    public function test_the_closure_example_preserves_ownership_and_assignment_guards(): void
    {
        foreach ([
            '/ong/eventos/8/presenca?contexto=ong&exemplo=encerramento',
            '/ong/eventos/7/presenca?contexto=organizador-vinculado&jornada=vinculado',
            '/ong/eventos/1/presenca?contexto=administrador&exemplo=encerramento',
            '/ong/eventos/1/presenca?contexto=pessoa&exemplo=encerramento',
        ] as $path) {
            $this->get($path)->assertForbidden()->assertDontSee('data-closure-action', false);
        }
    }
}
