<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VisualParticipationJustificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['session.driver' => 'array', 'cache.default' => 'array']);
        DB::shouldReceive('connection')->andThrow(new \LogicException('Banco fora do escopo.'));
        Http::preventStrayRequests();
    }

    public function test_person_and_management_share_the_same_scenario_and_record_identity(): void
    {
        $fixtures = [];
        foreach (['/minhas-participacoes?contexto=pessoa&exemplo=confiabilidade', '/ong/eventos/7/presenca?contexto=ong&jornada=ong-convites&exemplo=confiabilidade&utm_source=chatgpt.com'] as $path) {
            $html = $this->get($path)->assertOk()->assertSee('Justificativa desta Agenda')->assertSee('data-record-justification-reason', false)->getContent();
            preg_match('~<script type="application/json" data-participation-fixture>(.*?)</script>~s', $html, $match);
            $fixtures[] = json_decode($match[1], true, flags: JSON_THROW_ON_ERROR);
        }
        $this->assertSame('7.confiabilidade', $fixtures[0]['storageId']);
        $this->assertSame($fixtures[0]['storageId'], $fixtures[1]['storageId']);
        foreach ([70401, 70501] as $id) {
            $personal = collect($fixtures[0]['records'])->firstWhere('id', $id);
            $managed = collect($fixtures[1]['records'])->firstWhere('id', $id);
            $this->assertSame($personal, $managed);
            $this->assertSame(1, $managed['personId']);
        }
        $this->assertTrue($fixtures[1]['writable']);
        Http::assertNothingSent();
    }

    public function test_navbar_keeps_the_recognized_participation_example(): void
    {
        foreach (['confiabilidade', 'confiabilidade-zero', 'confiabilidade-dois'] as $example) {
            $this->get('/minhas-participacoes?contexto=pessoa&exemplo='.$example)->assertOk()
                ->assertSee('href="/ranking?contexto=pessoa&amp;exemplo='.$example.'"', false)
                ->assertSee('href="/minhas-candidaturas?contexto=pessoa&amp;exemplo='.$example.'&amp;cenario=participacao&amp;evento=7"', false)
                ->assertSee(e(route('perfil', ['area' => 'privada', 'contexto' => 'pessoa', 'exemplo' => $example])), false);
        }
        $html = $this->get('/minhas-participacoes?contexto=pessoa&exemplo=invalido')->assertOk()->getContent();
        preg_match('~<ul[^>]+id="navbar-links".*?</ul>~s', $html, $nav);
        $this->assertNotEmpty($nav);
        $this->assertStringNotContainsString('exemplo=invalido', $nav[0]);
    }

    public function test_justification_remains_private_and_does_not_expand_management_scope(): void
    {
        foreach (['pessoa', 'visitante', 'administrador', 'organizador'] as $context) {
            $this->get('/ong/eventos/7/presenca?contexto='.$context.'&exemplo=confiabilidade')->assertForbidden();
        }
        $this->get('/minhas-participacoes?contexto=pessoa&pessoa=99&exemplo=confiabilidade')->assertNotFound();
        $this->get('/ranking?contexto=visitante&exemplo=confiabilidade')->assertOk()
            ->assertDontSee('data-record-justification', false)->assertDontSee('"justification"', false);
        $this->get('/perfil?area=publica&pessoa=11&contexto=visitante&exemplo=confiabilidade')->assertOk()
            ->assertDontSee('data-record-justification', false);
        Http::assertNothingSent();
    }
}
