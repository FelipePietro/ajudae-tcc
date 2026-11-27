<?php

namespace Tests\Feature;

use App\Support\NavigationContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VisualSponsorshipTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['session.driver' => 'array', 'cache.default' => 'array']);
        DB::shouldReceive('connection')->andThrow(new \LogicException('Banco fora do escopo.'));
        Http::preventStrayRequests();
    }

    public function test_referral_prefills_the_existing_person_registration_without_a_new_route(): void
    {
        $this->get('/cadastro?contexto=visitante&padrinho=Q7HF-M9XR-2KVT')->assertOk()
            ->assertSee('Código de indicação de padrinho (opcional)')
            ->assertSee('value="Q7HF-M9XR-2KVT"', false)
            ->assertSee('data-referral-consent', false)
            ->assertSee('Certidão de antecedentes')
            ->assertSessionMissing('padrinho');
        $this->get('/cadastro?padrinho[]=teste')->assertOk()->assertDontSee('value="teste"', false);
        $this->get('/cadastro/ong?contexto=visitante')->assertOk()->assertDontSee('data-referral-code', false);
        Http::assertNothingSent();
        $this->assertFalse(auth()->check());
    }

    public function test_registration_follow_up_is_hidden_until_a_request_is_confirmed_by_the_current_journey(): void
    {
        foreach (['', '&padrinho=Q7HF-M9XR-2KVT', '&padrinho=invalido', '&cenario_solicitante=ativo'] as $query) {
            $response = $this->get('/cadastro?contexto=visitante'.$query)->assertOk()->assertSee('Ir para o login');
            $dom = new \DOMDocument;
            @$dom->loadHTML($response->getContent());
            $nodes = (new \DOMXPath($dom))->query('//a[@data-registration-sponsorship]');
            $this->assertCount(1, $nodes);
            $this->assertTrue($nodes->item(0)->hasAttribute('hidden'));
            $this->assertStringContainsString('apadrinhamento=solicitante', $nodes->item(0)->getAttribute('href'));
        }
        $this->get('/cadastro/ong?contexto=visitante')->assertOk()->assertSee('Ver situação cadastral')->assertDontSee('data-registration-sponsorship', false);
        Http::assertNothingSent();
    }

    public function test_sponsorship_is_private_and_limited_to_the_person_context(): void
    {
        $path = '/perfil?area=privada&apadrinhamento=padrinho';
        $this->get($path.'&contexto=pessoa')->assertOk()->assertSee('Marina de Exemplo')
            ->assertSee('Posso ser padrinho?')->assertDontSee('Documentos privados')->assertDontSee('pessoa@example.test');
        $this->get($path.'&contexto=pessoa&padrinho_cenario=alternativa')->assertOk()->assertSee('Rui de Exemplo');
        foreach (['visitante', 'ong', 'organizador', 'organizador-vinculado', 'administrador'] as $context) {
            $this->get($path.'&contexto='.$context)->assertForbidden()->assertDontSee('data-sponsorship-profile', false);
        }
        $this->get('/perfil?area=publica&pessoa=1&contexto=pessoa&apadrinhamento=padrinho')
            ->assertOk()->assertDontSee('data-sponsorship-profile', false);
        $this->get('/perfil?area=privada&apadrinhamento=solicitante&contexto=pessoa')->assertOk()
            ->assertSee('Meu apadrinhamento')->assertSee('data-sponsorship-correction', false);
    }

    public function test_admin_projection_preserves_acl_and_the_existing_document_preview(): void
    {
        $this->get('/admin/candidatos?contexto=administrador&registro=pessoa-indicacao')->assertOk()
            ->assertSee('data-admin-referral', false)->assertSee('data-admin-media', false)
            ->assertSee('data-admin-status-filter', false);
        $this->get('/admin/candidatos?contexto=pessoa&registro=pessoa-indicacao')->assertForbidden()
            ->assertDontSee('data-admin-referral', false)->assertDontSee('data-admin-media', false);
    }

    public function test_applicant_examples_are_explicitly_scoped_to_the_private_applicant_view(): void
    {
        $path = '/perfil?area=privada&contexto=pessoa&apadrinhamento=solicitante';
        $this->get($path)->assertOk()->assertSee('data-applicant-example=""', false)
            ->assertSee('Cenários de acompanhamento')->assertDontSee('<h1>Lucas Pereira</h1>', false)
            ->assertSee('href="'.e(NavigationContext::privateProfileUrl()).'" class="avatar"', false);

        foreach (['pendente', 'ativo', 'recusado', 'correcao', 'sem-indicacao'] as $example) {
            $this->get($path.'&cenario_solicitante='.$example)->assertOk()
                ->assertSee('data-applicant-example="'.$example.'"', false)
                ->assertSee('Solicitante de exemplo')->assertDontSee('pessoa@example.test');
        }
        $this->get($path.'&cenario_solicitante[]=pendente')->assertNotFound();
        $this->get($path.'&cenario_solicitante=invalido')->assertNotFound();
        $this->get(str_replace('solicitante', 'padrinho', $path).'&cenario_solicitante=pendente')->assertNotFound();
        $this->get(str_replace('contexto=pessoa', 'contexto=ong', $path).'&cenario_solicitante=ativo')->assertForbidden();
        $this->get(str_replace('contexto=pessoa', 'contexto=visitante', $path).'&cenario_solicitante=ativo')->assertForbidden();
        $this->get('/perfil?area=publica&pessoa=1&contexto=pessoa&cenario_solicitante=ativo')
            ->assertOk()->assertDontSee('data-applicant-example', false);
        $this->assertFalse(auth()->check());
    }
}
