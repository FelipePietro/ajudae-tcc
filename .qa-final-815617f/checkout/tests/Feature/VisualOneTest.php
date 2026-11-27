<?php

namespace Tests\Feature;

use App\Services\WebApi;
use App\Support\VisualOneFixtures;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** UI scenarios only. Any database or outbound API attempt fails these tests. */
class VisualOneTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['session.driver' => 'array', 'cache.default' => 'array']);
        DB::shouldReceive('connection')->andThrow(new \LogicException('Database forbidden.'));
        Http::preventStrayRequests();
        $this->mock(WebApi::class)->shouldNotReceive('call');
    }

    public function test_all_account_scenarios_render_without_database_api_or_authentication(): void
    {
        foreach (VisualOneFixtures::make()['demo']['scenarios'] as $scenario => $label) {
            foreach (['/cadastro', '/perfil', '/perfil/preferencias', '/perfil/editar'] as $page) {
                $response = $this->get($page.'?cenario='.$scenario.'&inspecao=1')->assertOk()->assertDontSee('Integração em preparação');
                if ($page !== '/perfil' || $scenario !== 'publico') {
                    $response->assertSee('data-visual-one', false)->assertSee($label);
                }
            }
        }
        foreach (VisualOneFixtures::make('ong')['demo']['scenarios'] as $scenario => $label) {
            foreach (['/cadastro/ong', '/ong/perfil', '/ong/perfil/editar', '/ong/aprovacao-pendente'] as $page) {
                $this->get($page.'?cenario='.$scenario.'&inspecao=1'.($page === '/ong/perfil' ? '&area=privada' : ''))->assertOk()->assertSee($label)->assertSee('data-visual-one', false);
            }
        }
        $this->get('/login')->assertOk()->assertSee('data-demo-login', false);
        Http::assertNothingSent();
    }

    public function test_registration_contract_fields_and_six_steps_are_present_without_secret_payload(): void
    {
        foreach (['/cadastro' => 'pessoa', '/cadastro/ong' => 'ong'] as $url => $actor) {
            $response = $this->get($url.'?cenario=parcial')->assertOk();
            $html = $response->getContent();
            $this->assertSame(6, preg_match_all('/<section data-step="[0-5]"/', $html));
            $response->assertSee('data-review', false)->assertSee('Esta revisão não cria uma conta');
            foreach (VisualOneFixtures::sections($actor) as $section) {
                foreach ($section['fields'] ?? [] as $field) {
                    $response->assertSee('name="'.$field['key'].'"', false);
                }
            }
            preg_match_all('/<input\b[^>]*type="(?:password|file)"[^>]*>/', $html, $sensitive);
            $this->assertNotEmpty($sensitive[0]);
            foreach ($sensitive[0] as $input) {
                $this->assertDoesNotMatchRegularExpression('/\bname\s*=/', $input);
                $this->assertDoesNotMatchRegularExpression('/\bvalue="[^\"]+"/', $input);
            }
        }
        $this->get('/cadastro')->assertSee('CNH')->assertSee('Opcional')
            ->assertSee('pattern="[0-9]{11}"', false)->assertSee('data-required-selection', false)
            ->assertSee('max="'.now()->subYears(18)->toDateString().'"', false);
    }

    public function test_public_profile_excludes_contact_documents_and_validation_details(): void
    {
        $this->get('/perfil?cenario=publico')->assertOk()->assertSee('Habilidades públicas')->assertDontSee('Alterar preferências')
            ->assertDontSee('pessoa@example.test')->assertDontSee('data-document', false)
            ->assertDontSee('data-tab-panel="validacao"', false)->assertDontSee('cpf_pessoa');
        $this->get('/perfil?cenario=correcao')->assertSee('Correção solicitada')->assertSee('Revisar envio')
            ->assertSee('Precisa de correção');
    }

    public function test_statuses_match_backend_and_unsupported_ong_correction_is_explicit(): void
    {
        $allowedPessoa = ['aguardando_validacao', 'em_analise', 'correcao_solicitada', 'aprovado', 'reprovado', 'reprovado_definitivamente'];
        foreach (VisualOneFixtures::make()['demo']['scenarios'] as $scenario => $_) {
            $this->assertContains(VisualOneFixtures::make('pessoa', $scenario)['demo']['status'], $allowedPessoa);
        }
        foreach (VisualOneFixtures::make('ong')['demo']['scenarios'] as $scenario => $_) {
            $this->assertContains(VisualOneFixtures::make('ong', $scenario)['demo']['status'], ['aguardando aprovação', 'ativo', 'reprovado']);
        }
        $this->get('/ong/aprovacao-pendente?cenario=correcao')->assertSee('Revise as informações da organização')
            ->assertSee('não estão disponíveis nesta página');
        $this->get('/perfil?cenario=definitivo')->assertDontSee('data-demo-resubmit', false)->assertSee('O cadastro foi encerrado');
        $this->get('/perfil?cenario=bloqueado')->assertDontSee('data-demo-resubmit', false)->assertSee('7 dias');
        $this->get('/perfil?cenario=antecedentes-vencidos')->assertSee('não revoga a aprovação global');
        $this->get('/perfil?cenario[]=invalido')->assertOk();
    }

    public function test_empty_preferences_required_fields_and_patch_whitelist_are_visible(): void
    {
        $this->get('/perfil/preferencias?cenario=sem-preferencias')->assertSee('Nenhuma informação adicionada');
        $this->get('/ong/perfil/editar')->assertSee('Este dado não pode ser alterado nesta página.')
            ->assertSee('readonly', false);
        $this->get('/perfil/editar')->assertDontSee('name="nm_pessoa"', false)->assertSee('name="email_pessoa"', false);
    }

    public function test_synthetic_fixtures_never_contain_passwords_files_or_real_identifiers(): void
    {
        foreach (['pessoa', 'ong'] as $actor) {
            $fixture = VisualOneFixtures::make($actor)['demo'];
            $data = json_encode($fixture);
            $this->assertStringNotContainsString('senha_', $data);
            $this->assertStringNotContainsString('storage_path', $data);
            $this->assertStringNotContainsString('plainTextToken', $data);
            foreach ($fixture['account'] as $key => $value) {
                if (str_contains($key, 'cpf') || str_contains($key, 'cnpj')) {
                    $this->assertMatchesRegularExpression('/^0+$/', $value);
                }
                if (str_contains($key, 'email')) {
                    $this->assertStringEndsWith('@example.test', $value);
                }
            }
        }
        $source = file_get_contents(resource_path('js/account-demo.js'));
        foreach (['fetch(', 'XMLHttpRequest', 'localStorage', 'sessionStorage', 'FileReader', 'createObjectURL', '.submit(', 'console.log('] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $source);
        }
    }
}
