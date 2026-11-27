<?php

namespace Tests\Feature;

use App\Services\WebApi;
use App\Support\NavigationContext;
use App\Support\VisualTwoFixtures;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/** Browser state is separate; these checks prohibit all database/API dependencies. */
class VisualTwoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['session.driver' => 'array', 'cache.default' => 'array']);
        DB::shouldReceive('connection')->andThrow(new \LogicException('Database forbidden.'));
        Http::preventStrayRequests();
        $this->mock(WebApi::class)->shouldNotReceive('call');
    }

    public function test_every_event_scenario_renders_in_its_existing_pages_without_database_or_api(): void
    {
        foreach (VisualTwoFixtures::SCENARIOS as $scenario) {
            foreach (['/feed', '/candidatura', '/minhas-candidaturas', '/ong/eventos', '/ong/eventos/criar', '/ong/eventos/1/editar', '/ong/candidatos'] as $page) {
                $this->get($page.'?cenario='.$scenario)->assertOk()->assertSee('data-event-flow', false);
            }
        }
        Http::assertNothingSent();
    }

    public function test_public_discovery_never_publishes_pending_rejected_or_terminal_events(): void
    {
        foreach (['pendente', 'reanalise', 'reprovado', 'cancelado', 'finalizado'] as $scenario) {
            $html = $this->get('/feed?cenario='.$scenario)->assertOk()->getContent();
            $this->assertDoesNotMatchRegularExpression('/<article\b[^>]*\bdata-evento(?:\s|>)/s', $html);
            $this->get('/eventos/1?cenario='.$scenario)->assertNotFound()->assertSee('Esse caminho não foi encontrado.');
        }
        $this->get('/eventos/1')->assertOk()->assertSee('Escolha uma Agenda')->assertSee('Agenda 1')->assertSee('Agenda 2');
        $this->get('/eventos/1?cenario=unica')->assertOk()->assertDontSee('Agenda 2');
    }

    public function test_shared_pages_resolve_each_navigation_context_without_changing_roles_or_permissions(): void
    {
        foreach (['visitante', 'pessoa', 'ong', 'organizador', 'organizador-vinculado', 'administrador'] as $context) {
            foreach (['/feed', '/eventos/1'] as $page) {
                $this->get($page.'?contexto='.$context)->assertOk()->assertSee('data-nav-context="'.$context.'"', false);
            }
        }
        $this->get('/feed?contexto=visitante')->assertSee('Entrar')->assertDontSee('Analisar cadastros');
        $this->get('/feed?contexto=administrador')->assertSee('Analisar eventos')->assertDontSee('admin/log-acoes');
        $this->assertSame('organizador', NavigationContext::resolve('organizador-vinculado')['role']);
        $this->assertSame(['eventos.aprovar', 'usuarios.validar'], NavigationContext::resolve('administrador')['permissions']);
        $this->get('/feed?contexto[]=invalido')->assertOk();
    }

    public function test_404_keeps_true_status_and_safe_public_navigation_without_internal_exception_details(): void
    {
        $this->get('/caminho-inexistente-bloco-2')->assertNotFound()->assertSee('Ir para o início')->assertSee('Explorar eventos')
            ->assertDontSee('Stack trace')->assertDontSee('NotFoundHttpException')->assertDontSee(base_path());
        $this->getJson('/api/v1/nao-existe')->assertNotFound()->assertJsonPath('success', false);
        foreach ([403, 419, 500] as $status) {
            Route::get('/__visual_two_error_'.$status, fn () => abort($status));
            $this->get('/__visual_two_error_'.$status)->assertStatus($status)->assertDontSee('Esse caminho não foi encontrado.');
        }
    }

    public function test_restored_profile_geometry_and_private_separation_are_preserved(): void
    {
        $this->get('/perfil?cenario=publico')->assertOk()->assertSee('max-w-5xl', false)->assertSee('Habilidades públicas')->assertDontSee('Alterar preferências')
            ->assertDontSee('data-document', false)->assertDontSee('email_pessoa')->assertDontSee('pessoa@example.test')->assertDontSee('status_validacao');
        $this->get('/ong/perfil')->assertOk()->assertSee('org-card', false)->assertSee('org-identity', false)->assertDontSee('org-actions', false)
            ->assertDontSee('responsavel@example.test')->assertDontSee('cpf_responsavel_ong')->assertDontSee('documento_identidade');
        $this->get('/perfil?area=privada&cenario=correcao')->assertOk()->assertSee('data-document', false)->assertSee('Correção solicitada');
        $this->get('/ong/perfil?area=privada')->assertOk()->assertSee('Responsável institucional');
    }

    public function test_application_gates_and_documented_capacity_are_available_without_unsupported_payloads(): void
    {
        foreach (['lotada' => 'Todas as vagas', 'janela-fechada' => 'uma hora antes', 'pessoa-pendente' => 'voluntários aprovados', 'candidatura-pendente' => 'já possui uma candidatura', 'agenda-cancelada' => 'Agenda encerrada'] as $scenario => $message) {
            $this->get('/candidatura?cenario='.$scenario)->assertOk()->assertSee($message);
        }
        $this->get('/minhas-candidaturas?cenario=candidatura-pendente&agenda=102')->assertSee('Agenda 102')->assertDontSee('Agenda 101 ·');
        $this->get('/candidatura?agenda=102')->assertSee('value="102" checked', false)->assertDontSee('name="mensagem"', false)->assertDontSee('name="termos"', false);
        $this->get('/ong/candidatos')->assertSee('correspondências')->assertSee('Confiabilidade')->assertDontSee('Aprovar selecionados')->assertDontSee('cpf_pessoa');
        $this->get('/ong/eventos/1/editar')->assertSee('data-agenda-editor', false)->assertSee('data-agenda-row-template', false)->assertSee('30 minutos');
    }

    public function test_fixtures_use_only_real_enums_and_unique_agenda_participation_keys(): void
    {
        foreach (VisualTwoFixtures::SCENARIOS as $scenario) {
            $flow = VisualTwoFixtures::make($scenario);
            $this->assertContains($flow['event_status'], ['ativo', 'aguardando a confirmação', 'reprovado', 'cancelado', 'finalizado']);
            foreach ($flow['agendas'] as $agenda) {
                $this->assertContains($agenda['status_ativo'], ['ativo', 'cancelado', 'finalizado']);
                $this->assertGreaterThanOrEqual(30, Carbon::parse($agenda['data_inicio'])->diffInMinutes(Carbon::parse($agenda['data_fim'])));
                $this->assertLessThanOrEqual($agenda['vagas_agenda'], $agenda['ocupadas']);
            }
            $ids = array_column(array_column($flow['my_applications'], 'agenda'), 'agenda_id');
            $this->assertSame(count($ids), count(array_unique($ids)));
        }
    }

    public function test_frontend_event_actions_have_no_remote_or_persistent_side_effects(): void
    {
        $source = file_get_contents(resource_path('js/events-demo.js'));
        foreach (['fetch(', 'XMLHttpRequest', 'localStorage', 'sessionStorage', '.submit(', 'console.log(', 'FileReader'] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $source);
        }
        $this->assertStringContainsString('stopImmediatePropagation', $source);
        $this->assertStringContainsString('agenda.ocupadas >= agenda.vagas_agenda', $source);
        $this->assertStringContainsString('minutes >= 30', $source);
    }

    public function test_user_visible_pages_do_not_expose_development_terminology(): void
    {
        foreach (['/login', '/cadastro', '/cadastro/ong', '/perfil?area=privada&cenario=definitivo', '/ong/aprovacao-pendente?cenario=correcao', '/ong/perfil/editar', '/feed', '/eventos/1', '/candidatura', '/minhas-candidaturas', '/ong/eventos/criar', '/ong/eventos/1/editar', '/ong/candidatos'] as $page) {
            $html = $this->get($page)->assertOk()->getContent();
            $visible = html_entity_decode(strip_tags(preg_replace('/<(script|style)\b[^>]*>[\s\S]*?<\/\1>/i', '', $html)));
            $this->assertDoesNotMatchRegularExpression('/\b(mock|demonstrativ[oa]|demonstração|endpoint|API V1|Bearer|BFF|fixture|Admin Master|RN\d{3})\b/iu', $visible, $page);
        }
    }
}
