<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VisualParticipationReliabilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['session.driver' => 'array', 'cache.default' => 'array']);
        DB::shouldReceive('connection')->andThrow(new \LogicException('Banco fora do escopo.'));
        Http::preventStrayRequests();
    }

    public function test_private_reliability_uses_owner_and_existing_store_in_each_person_context(): void
    {
        foreach (['pessoa', 'organizador', 'organizador-vinculado'] as $context) {
            $this->get('/perfil?area=privada&contexto='.$context.'&exemplo=confiabilidade')->assertOk()
                ->assertSee('data-tab="confiabilidade"', false)->assertSee('data-reliability-fixture', false)
                ->assertSee('"storageId":"7.confiabilidade"', false)->assertDontSee('"personId":11', false);
        }
        foreach (['confiabilidade-zero', 'confiabilidade-dois'] as $example) {
            $this->get('/perfil?area=privada&contexto=pessoa&exemplo='.$example)->assertOk()
                ->assertSee('"storageId":"7.'.$example.'"', false);
        }
        Http::assertNothingSent();
    }

    public function test_public_ranking_has_no_private_justification_and_no_fake_own_position(): void
    {
        foreach (['visitante', 'ong', 'pessoa', 'organizador'] as $context) {
            $response = $this->get('/ranking?contexto='.$context.'&exemplo=confiabilidade')->assertOk()
                ->assertSee('data-ranking-fixture', false)->assertDontSee('"justification"', false)
                ->assertDontSee('"reason"', false)->assertDontSee('"name":"Pessoa de Exemplo 26"', false)
                ->assertDontSee('data-participation-action', false)->assertDontSee('data-reliability-value', false);
            if (in_array($context, ['visitante', 'ong'], true)) {
                $response->assertDontSee('data-ranking-own', false);
            }
        }
        $this->get('/perfil?area=publica&pessoa=11&contexto=visitante')->assertOk()
            ->assertDontSee('data-reliability-fixture', false);
        $this->get('/perfil?area=privada&contexto=ong')->assertOk()->assertDontSee('data-reliability-fixture', false);
        $this->get('/ong/eventos/7/presenca?contexto=pessoa&exemplo=confiabilidade')->assertForbidden();
        $this->get('/minhas-participacoes?contexto=pessoa&pessoa=11&exemplo=confiabilidade')->assertNotFound();
        Http::assertNothingSent();
    }
}
