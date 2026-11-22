<?php

namespace Tests\Feature;

use App\Services\WebApi;
use App\Support\PublicProfileFixtures;
use App\Support\VisualTwoFixtures;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VisualNavigationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['session.driver' => 'array', 'cache.default' => 'array']);
        DB::shouldReceive('connection')->andThrow(new \LogicException('Database forbidden.'));
        Http::preventStrayRequests();
        $this->mock(WebApi::class)->shouldNotReceive('call');
    }

    public function test_existing_button_components_keep_links_and_support_semantic_local_actions(): void
    {
        foreach (['primario', 'secundario', 'dourado', 'declined'] as $variant) {
            $html = Blade::render('<x-botao-'.$variant.' href="/feed?contexto=ong" texto="Continuar" id="link-proof" />');
            $this->assertStringContainsString('href="/feed?contexto=ong"', $html);
            $this->assertStringContainsString('btn-'.$variant, $html);
            $this->assertStringContainsString('id="link-proof"', $html);
            $html = Blade::render('<x-botao-'.$variant.' as="button" :href="null" type="submit" disabled size="sm" icon="✓" data-local-action>Revisar</x-botao-'.$variant.'>');
            foreach (['<button', 'type="submit"', 'disabled', 'btn-sm', 'data-local-action', 'Revisar', 'aria-hidden="true"'] as $expected) {
                $this->assertStringContainsString($expected, $html);
            }
            $html = Blade::render('<x-botao-'.$variant.' href="/proibido" disabled texto="Indisponível" />');
            $this->assertStringNotContainsString('href=', $html);
            $this->assertStringContainsString('aria-disabled="true"', $html);
        }
    }

    public function test_contextual_breadcrumb_extension_preserves_legacy_root(): void
    {
        $legacy = Blade::render('<x-breadcrumb-ong :itens="[]" />');
        $this->assertStringContainsString('Dashboard', $legacy);
        $custom = Blade::render('<x-breadcrumb-ong raiz-href="/feed?contexto=pessoa" raiz-label="Explorar eventos" :itens="[]" />');
        $this->assertStringContainsString('href="/feed?contexto=pessoa"', $custom);
        $this->assertStringContainsString('Explorar eventos', $custom);
    }

    public function test_original_graphical_stepper_is_above_application_and_has_four_stages(): void
    {
        $html = $this->get('/candidatura?agenda=102')->assertOk()->getContent();
        $this->assertLessThan(strpos($html, 'data-application-wizard'), strpos($html, 'data-application-progress'));
        $this->assertSame(4, substr_count($html, 'data-progress-circle'));
        $this->assertSame(3, substr_count($html, 'data-progress-connector='));
        foreach (['data-step-state="current"', 'data-step-state="future"', 'data-application-confirm', 'data-button-component="primario"', 'data-application-another'] as $marker) {
            $this->assertStringContainsString($marker, $html);
        }
        $this->assertStringNotContainsString('<ol class="flex flex-wrap gap-3', $html);
    }

    public function test_event_candidate_disclosures_are_separate_and_do_not_leak_private_letters(): void
    {
        $html = $this->get('/ong/candidatos')->assertOk()->getContent();
        $this->assertSame(2, substr_count($html, 'data-candidate-management'));
        $this->assertSame(2, substr_count($html, 'data-candidate-flow'));
        $this->assertStringContainsString('candidate-event-1', $html);
        $this->assertStringContainsString('candidate-event-7', $html);
        $this->assertStringContainsString('eventos concluídos', $html);
        $this->assertStringContainsString('XP', $html);
        $this->assertStringContainsString('cand-hab is-match', $html);
        foreach (['/feed', '/eventos/1', '/ong/candidatos?contexto=pessoa', '/ong/perfil?contexto=visitante'] as $page) {
            $this->get($page)->assertOk()->assertDontSee(VisualTwoFixtures::make()['candidates'][0]['carta_apresentacao_mock']);
        }
    }

    public function test_public_ong_profile_preserves_all_navigation_contexts_without_private_data(): void
    {
        foreach (['visitante', 'pessoa', 'ong', 'organizador', 'organizador-vinculado', 'administrador'] as $context) {
            $this->get('/eventos/1?contexto='.$context)->assertOk()->assertSee('/ong/perfil?contexto='.$context, false)->assertSee(PublicProfileFixtures::ong()['usuario']['nome']);
            $this->get('/ong/perfil?contexto='.$context)->assertOk()->assertSee('data-nav-context="'.$context.'"', false)->assertSee('org-card', false)->assertDontSee('responsavel@example.test')->assertDontSee('cpf_responsavel_ong')->assertDontSee('perfil_ong.css');
        }
    }

    public function test_candidatures_management_action_keeps_event_and_context_in_standard_button(): void
    {
        $html = $this->get('/ong/eventos?contexto=organizador-vinculado')->assertOk()->getContent();
        $this->assertStringContainsString('Candidaturas por Agenda', $html);
        $this->assertStringContainsString('contexto=organizador-vinculado', $html);
        $this->assertStringContainsString('data-button-component="secundario"', $html);
    }

    public function test_standard_management_link_keeps_decoded_query_parameters(): void
    {
        $html = $this->get('/ong/eventos?contexto=organizador-vinculado')->assertOk()->getContent();
        preg_match('~<a\b[^>]*href="([^"<>]*ong/candidatos[^"<>]*)"[^>]*>\s*Candidaturas por Agenda\s*</a>~s', $html, $match);
        $this->assertNotEmpty($match);
        parse_str(parse_url(html_entity_decode($match[1], ENT_QUOTES, 'UTF-8'), PHP_URL_QUERY), $query);
        $this->assertSame('organizador-vinculado', $query['contexto']);
        $this->assertSame('1', $query['evento']);
        $this->assertArrayHasKey('cenario', $query);
        $this->assertArrayNotHasKey('amp;contexto', $query);
    }
}
