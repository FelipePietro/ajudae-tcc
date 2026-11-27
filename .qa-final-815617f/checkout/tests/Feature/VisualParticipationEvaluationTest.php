<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VisualParticipationEvaluationTest extends TestCase
{
    public function test_examples_share_the_tab_history_key_between_the_responsible_ong_and_the_participant(): void
    {
        Http::fake();

        $this->get('/ong/eventos?contexto=ong&jornada=ong-convites&exemplo=avaliacoes')->assertOk()
            ->assertSee('Responsabilidade institucional')
            ->assertSee('/ong/eventos/7/presenca?contexto=ong&amp;jornada=ong-convites&amp;exemplo=avaliacoes', false);

        $this->get('/ong/eventos/7/presenca?contexto=ong&jornada=ong-convites&exemplo=avaliacoes')
            ->assertOk()
            ->assertSee('"canReviewVolunteers":true', false)
            ->assertSee('"storageId":"7.avaliacoes"', false)
            ->assertSee('data-volunteer-evaluation="11"', false)
            ->assertSee('data-evaluation-action="avaliarVoluntario"', false);

        $this->get('/minhas-participacoes?contexto=pessoa&exemplo=avaliacoes')->assertOk()
            ->assertSee('"storageId":"7.avaliacoes"', false)
            ->assertSee('data-evaluation-action="avaliarOng"', false)
            ->assertDontSee('Alex de Exemplo')
            ->assertDontSee('avaliacoesConcluidas')
            ->assertDontSee('data-volunteer-evaluation', false);

        $this->get('/ong/eventos/7/presenca?contexto=ong&exemplo=avaliacoes-concluidas')
            ->assertOk()
            ->assertSee('"storageId":"7.avaliacoes-concluidas"', false)
            ->assertSee('"personId":11,"nota":5', false);

        Http::assertNothingSent();
    }

    public function test_assignment_and_private_projections_are_preserved(): void
    {
        $this->get('/ong/eventos/1/presenca?contexto=ong&jornada=ong-vinculos')->assertOk()
            ->assertSee('"canReviewVolunteers":false', false);
        $this->get('/ong/eventos/1/presenca?contexto=organizador-vinculado&jornada=vinculado')->assertOk()
            ->assertSee('"canReviewVolunteers":true', false);
        $this->get('/ong/eventos/8/presenca?contexto=organizador&jornada=independente-eventos')->assertOk()
            ->assertSee('"canReviewVolunteers":true', false)
            ->assertDontSee('data-ong-evaluation', false);
        $this->get('/ong/eventos/7/presenca?contexto=organizador-vinculado&jornada=vinculado-varios&exemplo=avaliacoes')
            ->assertForbidden();
        $this->get('/minhas-participacoes?contexto=organizador-vinculado&exemplo=avaliacoes')->assertOk()
            ->assertDontSee('data-ong-evaluation', false);
        $this->get('/minhas-participacoes?contexto=pessoa&jornada=aprovada&exemplo=avaliacoes')->assertOk()
            ->assertDontSee('data-ong-evaluation', false);
        $this->get('/minhas-participacoes?contexto=pessoa&pessoa=11&exemplo=avaliacoes')->assertNotFound();

        $this->get('/eventos/7?contexto=visitante&cenario=participacao&exemplo=avaliacoes')->assertOk()
            ->assertSee('data-closure-event-badge="7.avaliacoes"', false)
            ->assertDontSee('data-participation-fixture', false)
            ->assertDontSee('Avaliações de voluntários');
        $this->get('/minhas-candidaturas?contexto=pessoa&cenario=participacao&evento=7&exemplo=avaliacoes')->assertOk()
            ->assertSee('"storageId":"7.avaliacoes"', false)
            ->assertDontSee('data-ong-evaluation', false);
    }
}
