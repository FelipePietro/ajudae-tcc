<?php

namespace Tests\Feature;

use App\Services\WebApi;
use App\Support\PublicProfileFixtures;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OngPublicNavigationTest extends TestCase
{
    public function test_public_profile_returns_to_the_specific_source_event_in_person_and_visitor_contexts(): void
    {
        foreach (['pessoa', 'visitante'] as $context) {
            foreach ([1, 2, 6] as $id) {
                $detail = $this->get('/eventos/'.$id.'?contexto='.$context)->assertOk()->getContent();
                preg_match('~<a\b[^>]*href="([^"<>]*ong/perfil[^"<>]*)"[^>]*>\s*Ver perfil da ONG\s*</a>~s', $detail, $link);
                $this->assertNotEmpty($link);
                $target = parse_url(html_entity_decode($link[1], ENT_QUOTES, 'UTF-8'));
                parse_str($target['query'], $query);
                $this->assertSame((string) $id, $query['evento_origem']);
                $this->assertSame($context, $query['contexto']);
                $profile = $this->get($target['path'].'?'.$target['query'])->assertOk()->getContent();
                preg_match('~<a\b[^>]*href="([^"<>]+)"[^>]*data-button-component="secundario"[^>]*>\s*Retornar ao evento\s*</a>~s', $profile, $back);
                $this->assertNotEmpty($back);
                $return = parse_url(html_entity_decode($back[1], ENT_QUOTES, 'UTF-8'));
                $this->assertSame('/eventos/'.$id, $return['path']);
                parse_str($return['query'], $returnQuery);
                $this->assertSame(['contexto' => $context], $returnQuery);
                $this->get($return['path'].'?'.$return['query'])->assertOk()->assertSee('data-nav-context="'.$context.'"', false);
            }
        }
    }

    public function test_public_profile_omits_return_for_missing_invalid_or_unrelated_source_and_ong_context(): void
    {
        foreach (['pessoa', 'visitante', 'ong'] as $context) {
            $this->get('/ong/perfil?ong=1&contexto='.$context)->assertOk()->assertDontSee('Retornar ao evento');
        }
        foreach (['0', '-1', '999', '01', '1e0', 'https://example.test', '//example.test', '1/../../login', '9999999999999999999999999'] as $source) {
            $this->get('/ong/perfil?ong=1&contexto=pessoa&evento_origem='.urlencode($source))->assertOk()->assertDontSee('Retornar ao evento');
        }
        foreach (['ong=1&contexto=pessoa&evento_origem[]=1', 'ong=1&contexto=visitante&evento_origem=2', 'ong=1&contexto=ong&evento_origem=1', 'ong=1&contexto=pessoa&return_url=https://example.test'] as $query) {
            $this->get('/ong/perfil?'.$query)->assertOk()->assertDontSee('Retornar ao evento');
        }
        Http::assertNothingSent();
    }

    protected function setUp(): void
    {
        parent::setUp();
        config(['session.driver' => 'array', 'cache.default' => 'array']);
        DB::shouldReceive('connection')->andThrow(new \LogicException('Database forbidden.'));
        Http::preventStrayRequests();
        $this->mock(WebApi::class)->shouldNotReceive('call');
    }

    public function test_selected_public_organization_is_independent_of_navigation_and_has_no_private_content(): void
    {
        foreach (['visitante', 'pessoa', 'ong', 'organizador', 'organizador-vinculado', 'administrador'] as $context) {
            foreach ([1, 2] as $id) {
                $html = $this->get('/ong/perfil?ong='.$id.'&contexto='.$context.'&cenario=pendente')->assertOk()->getContent();
                $this->assertStringContainsString(PublicProfileFixtures::ong($id)['usuario']['nome'], $html);
                $this->assertStringContainsString('data-nav-context="'.$context.'"', $html);
                $this->assertStringContainsString('org-card', $html);
                foreach (['aria-label="Breadcrumb"', 'sidebar-ong', 'org-actions', 'Responsável institucional', 'responsavel@example.test'] as $private) {
                    $this->assertStringNotContainsString($private, $html);
                }
                preg_match('~<main\b.*?</main>~s', $html, $main);
                foreach (['Minha organização', 'Configurações', 'Editar', 'area=privada'] as $private) {
                    $this->assertStringNotContainsString($private, $main[0]);
                }
            }
        }
        foreach (['3', 'invalid', 'ong[]=1'] as $id) {
            $this->get('/ong/perfil?'.($id === 'ong[]=1' ? $id : 'ong='.$id))->assertNotFound();
        }
    }

    public function test_event_links_select_the_public_owner_and_keep_context(): void
    {
        foreach ([1, 2] as $id) {
            $html = $this->get('/eventos/'.$id.'?contexto=ong')->assertOk()->getContent();
            preg_match('~<a\b[^>]*href="([^"<>]*ong/perfil[^"<>]*)"[^>]*>\s*Ver perfil da ONG\s*</a>~s', $html, $match);
            $this->assertNotEmpty($match);
            parse_str(parse_url(html_entity_decode($match[1], ENT_QUOTES, 'UTF-8'), PHP_URL_QUERY), $query);
            $this->assertSame((string) $id, $query['ong']);
            $this->assertSame('ong', $query['contexto']);
            $this->assertStringContainsString(PublicProfileFixtures::ong($id)['usuario']['nome'], $html);
        }
    }

    public function test_all_existing_sidebar_categories_use_one_route_based_active_item(): void
    {
        $cases = [
            '/ong/painel?contexto=ong' => 'dashboard',
            '/ong/perfil?area=privada&contexto=ong' => 'perfil',
            '/ong/eventos?contexto=ong&cenario=publicado' => 'eventos',
            '/ong/eventos/2/editar?contexto=ong&cenario=pendente' => 'eventos',
            '/ong/eventos/criar?contexto=ong' => 'criar-evento',
            '/ong/candidatos?evento=2&contexto=ong' => 'candidatos',
            '/feed?contexto=ong' => 'explorar',
            '/eventos/2?contexto=ong' => 'explorar',
            '/ong/notificacoes?contexto=ong' => 'notificacoes',
            '/ong/perfil/editar?contexto=ong' => 'configuracoes',
        ];
        foreach ($cases as $url => $active) {
            $html = $this->get($url)->assertOk()->getContent();
            preg_match_all('~data-sidebar-item="([^"]+)"[^>]*aria-current="page"~', $html, $matches);
            $this->assertSame([$active], $matches[1], $url);
            $this->assertStringContainsString('id="sidebar-ong"', $html);
            $this->assertStringContainsString('data-sidebar-item="explorar"', $html);
            $this->assertStringContainsString('aria-controls="sidebar-ong"', $html);
        }
        $this->get('/ong/configuracoes')->assertRedirect(route('ong.perfil.editar'));
        $this->get('/ong/perfil?area=privada')->assertOk()->assertSee(PublicProfileFixtures::ong()['usuario']['nome']);
        $this->get('/ong/painel')->assertOk()->assertSee('/eventos/1?contexto=ong', false);
    }

    public function test_management_has_no_global_state_box_and_editor_keeps_contextual_status(): void
    {
        foreach (['/ong/eventos', '/ong/candidatos', '/ong/eventos/criar', '/ong/eventos/1/editar'] as $url) {
            $html = $this->get($url.'?cenario=pendente')->assertOk()->getContent();
            $this->assertStringNotContainsString('A publicação depende da situação do evento.', $html);
            $this->assertStringNotContainsString('Organização: Instituto Esperança · Responsável:', $html);
            $breadcrumb = strpos($html, 'Breadcrumb');
            if ($breadcrumb === false) {
                $breadcrumb = strpos($html, 'BREADCRUMB');
            }
            $this->assertNotFalse($breadcrumb);
            if (str_contains($url, 'criar') || str_contains($url, 'editar')) {
                $this->assertGreaterThan(strpos($html, 'data-event-editor'), strpos($html, 'data-event-status'));
                $this->assertGreaterThan($breadcrumb, strpos($html, 'data-event-status'));
            }
            $this->assertStringContainsString('data-event-flow', $html);
        }
        $this->get('/login')->assertOk()->assertSee('class="account-recovery-link"', false)->assertSee('aria-controls="recovery-feedback"', false);
        Http::assertNothingSent();
    }
}
