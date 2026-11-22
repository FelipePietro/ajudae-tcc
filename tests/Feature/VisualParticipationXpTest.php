<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VisualParticipationXpTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['session.driver' => 'array', 'cache.default' => 'array']);
        DB::shouldReceive('connection')->andThrow(new \LogicException('Banco fora do escopo.'));
        Http::preventStrayRequests();
    }

    public function test_xp_uses_the_same_tab_key_and_private_person_identity_after_promotion(): void
    {
        foreach (['pessoa', 'organizador', 'organizador-vinculado'] as $context) {
            $this->get("/perfil?area=privada&contexto={$context}&exemplo=xp")->assertOk()
                ->assertSee('data-xp-profile', false)
                ->assertSee('data-tab="meu-xp"', false)
                ->assertSee('"storageId":"7.xp"', false)
                ->assertSee('"personId":1', false)
                ->assertDontSee('"personId":11', false)
                ->assertSee("contexto={$context}&amp;exemplo=xp", false);
        }

        $this->get('/minhas-participacoes?contexto=pessoa&exemplo=xp-campanha-atualizada')->assertOk()
            ->assertSee('"storageId":"7.xp"', false)
            ->assertSee('data-record-xp="70101"', false)
            ->assertSee('exemplo=xp-campanha-atualizada#meu-xp', false)
            ->assertDontSee('data-record-xp="70111"', false);
        Http::assertNothingSent();
    }

    public function test_only_operational_context_sees_recipient_credits_and_previews(): void
    {
        $this->get('/ong/eventos/7/presenca?contexto=ong&jornada=ong-convites&exemplo=xp')->assertOk()
            ->assertSee('"storageId":"7.xp"', false)
            ->assertSee('data-agenda-xp', false)
            ->assertSee('data-record-xp="70111"', false)
            ->assertSee('Rede de apoio');

        $this->get('/ong/eventos/7/presenca?contexto=pessoa&exemplo=xp')->assertForbidden();
        $this->get('/ong/eventos/7/presenca?contexto=organizador&exemplo=xp')->assertForbidden();
        $this->get('/perfil?area=publica&pessoa=11&contexto=pessoa&exemplo=xp')->assertOk()
            ->assertDontSee('data-xp-profile', false)
            ->assertDontSee('7.xp', false);
        $this->get('/eventos/7?contexto=visitante&cenario=participacao&exemplo=xp')->assertOk()
            ->assertSee('data-closure-event-badge="7.xp"', false)
            ->assertDontSee('data-record-xp', false)
            ->assertDontSee('Rede de apoio');
        Http::assertNothingSent();
    }
}
