<?php

namespace Tests\Feature;

use App\Services\WebApi;
use App\Support\PublicProfileFixtures;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VisualThreeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['session.driver' => 'array', 'cache.default' => 'array']);
        DB::shouldReceive('connection')->andThrow(new \LogicException('Database forbidden.'));
        Http::preventStrayRequests();
        $this->mock(WebApi::class)->shouldNotReceive('call');
    }

    public function test_promotion_states_keep_identity_and_contract_criteria_in_private_area(): void
    {
        foreach (['nao-elegivel', 'elegivel', 'pendente', 'em-analise', 'aprovada', 'reprovada', 'nova-solicitacao', 'bloqueado', 'apadrinhado', 'cadastro-pendente', 'carregando', 'falha'] as $scenario) {
            $html = $this->get('/organizador/solicitacao?contexto=pessoa&jornada='.$scenario)->assertOk()->getContent();
            if (in_array($scenario, ['carregando', 'falha'])) {
                continue;
            }
            $this->assertStringContainsString(PublicProfileFixtures::pessoa()['nome_user'], $html);
            $this->assertStringContainsString('5 participações confirmadas', $html);
            $this->assertStringContainsString('Confiabilidade formada de ao menos 80', $html);
            $this->assertStringContainsString('Média de avaliações de ao menos 4,0', $html);
            $this->assertStringContainsString('30 dias completos', $html);
            $this->assertSame(in_array($scenario, ['elegivel', 'nova-solicitacao']), str_contains($html, 'data-promotion-form'));
        }
        $this->get('/organizador/solicitacao?jornada=elegivel')->assertSee('maxlength="2000"', false)->assertSee('Justificativa *')->assertSee('mudança para organizador é definitiva');
        $this->get('/perfil?area=privada')->assertSee('Minha atuação como organizador');
        $this->get('/perfil?contexto=pessoa')->assertDontSee('data-promotion-form', false)->assertDontSee('apadrinhados ativos');
        $this->get('/organizador/solicitacao?contexto=ong')->assertForbidden();
    }

    public function test_workspaces_only_expose_events_owned_or_explicitly_assigned_to_the_person(): void
    {
        $this->get('/organizador?contexto=organizador&jornada=independente-vazio')->assertOk()->assertSee('Nenhum Evento próprio');
        $this->get('/organizador?contexto=organizador')->assertOk()->assertSee('Mutirão independente de leitura')->assertDontSee('Oficina de apoio comunitário');
        $this->get('/organizador?contexto=organizador-vinculado')->assertOk()->assertSee('Limpeza de Parques')->assertDontSee('Oficina de apoio comunitário')->assertDontSee('Mutirão independente de leitura');
        $this->get('/ong/eventos?contexto=organizador')->assertOk()->assertSee('/ong/eventos/8/editar', false)->assertDontSee('/ong/eventos/1/editar', false);
        $this->get('/ong/eventos/1/editar?contexto=organizador')->assertOk()->assertDontSee('data-event-editor', false)->assertSee('não está sob sua responsabilidade');
        $this->get('/ong/eventos/8/editar?contexto=organizador')->assertOk()->assertSee('data-event-editor', false)->assertSee('Minha atuação');
        $this->get('/ong/candidatos?contexto=organizador-vinculado&evento=7')->assertOk()->assertDontSee('data-candidate-management', false);
        $this->get('/ong/candidatos?contexto=organizador-vinculado&evento=1&jornada=vinculado')->assertOk()->assertSee('candidate-event-1', false)->assertDontSee('candidate-event-7', false)->assertSee('/ong/eventos?contexto=organizador-vinculado&amp;jornada=vinculado', false);
        $this->get('/ong/eventos?contexto=ong')->assertOk()->assertSee('/ong/eventos/7/editar', false)->assertDontSee('/ong/eventos/8/editar', false);
        $this->get('/organizador?contexto=pessoa')->assertForbidden();
        $this->get('/organizador?contexto=organizador-vinculado&jornada=vinculado-sem-eventos')->assertOk()->assertSee('Nenhum Evento atribuído')->assertDontSee('Evento e Agendas');
        $this->get('/ong/eventos/8/editar?contexto=organizador&jornada=convite-pendente')->assertOk()->assertSee('Finalizado');
        $this->get('/ong/organizadores?contexto=organizador-vinculado')->assertForbidden();
    }

    public function test_invite_link_and_responsibility_scenarios_use_real_enums_without_private_documents(): void
    {
        foreach (['ong-vazia', 'ong-convites', 'ong-vinculos', 'encerrado', 'carregando', 'falha'] as $scenario) {
            $this->get('/ong/organizadores?contexto=ong&jornada='.$scenario)->assertOk();
        }
        foreach (['convite-pendente', 'convite-bloqueado', 'vinculado', 'vinculo-livre', 'encerrado'] as $scenario) {
            $this->get('/organizador?contexto='.(in_array($scenario, ['vinculado', 'vinculo-livre'], true) ? 'organizador-vinculado' : 'organizador').'&jornada='.$scenario)->assertOk()->assertSee('data-organizer-state', false);
        }
        $html = $this->get('/ong/organizadores?contexto=ong')->assertOk()->getContent();
        foreach (['data-sidebar-item="organizadores"', 'data-organizer-invite', 'data-organizer-responsibility', 'data-organizer-confirm', 'data-button-component="primario"'] as $expected) {
            $this->assertStringContainsString($expected, $html);
        }
        preg_match_all('~data-sidebar-item="([^"]+)"[^>]*aria-current="page"~', $html, $active);
        $this->assertSame(['organizadores'], $active[1]);
        foreach (['senha_pessoa', 'cpf_pessoa', 'antecedentes_pessoa_link', 'responsavel@example.test'] as $private) {
            $this->assertStringNotContainsString($private, $html);
        }
        $this->get('/ong/painel')->assertOk()->assertSee('Gerenciar organizadores')->assertDontSee('A remoção de um funcionário');
        Http::assertNothingSent();
    }

    public function test_independent_public_event_and_existing_return_flow_remain_coherent(): void
    {
        $this->get('/eventos/8?contexto=pessoa')->assertOk()->assertSee('Mutirão independente de leitura')->assertSee('Ver perfil do organizador')->assertDontSee('Ver perfil da ONG');
        $this->get('/ong/perfil?contexto=pessoa&ong=1&evento_origem=7')->assertOk()->assertSee('Retornar ao evento')->assertSee('/eventos/7?contexto=pessoa', false);
        $this->get('/ong/perfil?contexto=pessoa&ong=1&evento_origem=8')->assertOk()->assertDontSee('Retornar ao evento');
    }
}
