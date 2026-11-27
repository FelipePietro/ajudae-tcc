<?php

namespace Tests\Feature;

use App\Services\WebApi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VisualThreeCorrectionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['session.driver' => 'array', 'cache.default' => 'array']);
        DB::shouldReceive('connection')->andThrow(new \LogicException('Database forbidden.'));
        Http::preventStrayRequests();
        $this->mock(WebApi::class)->shouldNotReceive('call');
    }

    public function test_own_profile_links_open_private_owner_in_each_person_context(): void
    {
        foreach (['pessoa', 'organizador', 'organizador-vinculado'] as $context) {
            $html = $this->get('/feed?contexto='.$context)->assertOk()->getContent();
            preg_match_all('~<a\b[^>]*href="([^"]+)"[^>]*(?:aria-label="Meu perfil"[^>]*)?>\s*(?:Meu perfil|LP)\s*</a>~', $html, $links);
            $this->assertCount(2, $links[1]);
            foreach ($links[1] as $href) {
                $url = html_entity_decode($href);
                parse_str(parse_url($url, PHP_URL_QUERY), $query);
                $this->assertSame('/perfil', parse_url($url, PHP_URL_PATH));
                $this->assertSame('privada', $query['area']);
                $this->assertSame($context, $query['contexto']);
                $this->assertArrayNotHasKey('pessoa', $query);
                $private = $this->get($url)->assertOk()->assertSee('data-visual-one', false)
                    ->assertSee('Meu cadastro')->assertSee('Alterar preferências')->assertSee('Documentos privados')
                    ->assertSee('Minha atuação como organizador')->assertSee('Ver perfil público');
                if ($context !== 'pessoa') {
                    $private->assertDontSee('Minhas candidaturas');
                } else {
                    $private->assertSee('Minhas candidaturas');
                }
            }
        }
        Http::assertNothingSent();
    }

    public function test_private_public_and_account_return_links_preserve_identity_and_context(): void
    {
        foreach (['pessoa', 'organizador', 'organizador-vinculado'] as $context) {
            $params = ['contexto' => $context, 'jornada' => $context === 'organizador-vinculado' ? 'vinculado-varios' : 'independente-eventos'];
            $privateUrl = route('perfil', ['area' => 'privada', ...$params]);
            $this->get($privateUrl)->assertOk()
                ->assertSee(e(route('perfil', ['area' => 'publica', 'pessoa' => 1, ...$params])), false)
                ->assertSee(e(route('perfil.editar', ['cenario' => 'aprovado', ...$params])), false);
            foreach (['perfil.editar', 'perfil.preferencias'] as $route) {
                $this->get(route($route, $params))->assertOk()->assertSee(e($privateUrl), false);
            }
            $this->get(route('perfil', ['area' => 'publica', 'pessoa' => 1, ...$params]))->assertOk()
                ->assertSee('Lucas Pereira')->assertDontSee('Meu cadastro')->assertDontSee('Alterar preferências')
                ->assertDontSee('data-visual-one', false);
        }
        $this->get('/perfil?area=privada&contexto[]=organizador&jornada[]=invalida')->assertOk()
            ->assertSee('Minhas candidaturas');
    }

    public function test_management_keeps_received_applications_and_public_invitee_links(): void
    {
        $this->get('/organizador?contexto=organizador-vinculado&jornada=vinculado-varios')->assertOk()
            ->assertSee('Candidaturas recebidas')->assertSee('/ong/candidatos', false)
            ->assertDontSee('Minhas candidaturas')->assertSee('data-organizer-event="1"', false)
            ->assertSee('data-organizer-event="7"', false)->assertDontSee('data-organizer-event="8"', false);
        $this->get('/ong/organizadores?contexto=ong')->assertOk()
            ->assertSee('area=publica&amp;pessoa=2', false);
        $this->get('/ong/candidatos?contexto=ong')->assertOk()->assertSee('data-candidate-management', false);
        $this->get('/minhas-candidaturas?contexto=pessoa')->assertOk()->assertSee('Minhas candidaturas');
        $this->get('/perfil?area=publica&pessoa=2&contexto=visitante')->assertOk()
            ->assertSee('Sara de Exemplo')->assertDontSee('Meu cadastro')->assertDontSee('Documentos privados');
    }

    public function test_dialogs_have_one_icon_close_and_only_one_footer_cancel_when_applicable(): void
    {
        foreach (['/organizador?contexto=organizador&jornada=convite-pendente', '/organizador/solicitacao?contexto=pessoa&jornada=elegivel', '/ong/organizadores?contexto=ong', '/ong/candidatos?contexto=ong', '/minhas-candidaturas?contexto=pessoa', '/perfil/editar?contexto=pessoa', '/ong/eventos/criar?contexto=ong', '/ong/eventos/8/editar?contexto=organizador'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            preg_match_all('~<dialog\b[^>]*>(.*?)</dialog>~s', $html, $dialogs);
            $this->assertNotEmpty($dialogs[1], $url);
            foreach ($dialogs[1] as $dialog) {
                $this->assertSame(1, substr_count($dialog, 'aria-label="Fechar diálogo"'), $url);
                $this->assertStringContainsString('<span aria-hidden="true">×</span>', $dialog);
                preg_match_all('~<button\b[^>]*>(.*?)</button>~s', $dialog, $buttons);
                $dismiss = array_filter($buttons[1], fn ($text) => in_array(trim(strip_tags($text)), ['Voltar', 'Fechar', 'Cancelar', 'Manter exclusão'], true));
                $this->assertLessThanOrEqual(1, count($dismiss), $url);
            }
        }
    }
}
