<?php

namespace Tests\Feature;

use App\Services\WebApi;
use App\Support\VisualTwoFixtures;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VisualUsabilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['session.driver' => 'array', 'cache.default' => 'array']);
        DB::shouldReceive('connection')->andThrow(new \LogicException('Database forbidden.'));
        Http::preventStrayRequests();
        $this->mock(WebApi::class)->shouldNotReceive('call');
    }

    public function test_account_deletion_is_accessible_and_reversible_without_mutation(): void
    {
        $this->get('/perfil/editar')->assertOk()->assertSee('Excluir minha conta')->assertSee('/perfil/excluir', false);
        $this->get('/perfil/excluir?contexto=pessoa')->assertOk()->assertSee('data-privacy-delete-form', false);
        $this->get('/perfil/exclusao-pendente?contexto=pessoa')->assertOk()->assertSee('data-privacy-undo', false)->assertSee('Esta revisão não efetiva a exclusão da conta.');
        $this->get('/perfil/editar?exclusao=cancelada')->assertOk()->assertDontSee('Solicitação cancelada nesta visualização.');
    }

    public function test_application_has_all_steps_and_optional_letter_without_post_payload(): void
    {
        $response = $this->get('/candidatura?agenda=102')->assertOk();
        foreach (['data-application-step="0"', 'data-application-step="1"', 'data-application-step="2"', 'data-application-step="3"', 'data-application-confirm', 'data-application-letter'] as $marker) {
            $response->assertSee($marker, false);
        }
        $response->assertSee('Carta de apresentação')->assertSee('(opcional)')->assertSee('value="102" checked', false)->assertDontSee('name="carta_apresentacao"', false);
    }

    public function test_private_letters_are_not_serialized_into_shared_pages_or_unprivileged_management(): void
    {
        $letter = VisualTwoFixtures::make()['candidates'][0]['carta_apresentacao_mock'];
        foreach (['/feed', '/eventos/1', '/candidatura', '/ong/candidatos?contexto=pessoa', '/ong/candidatos?contexto=visitante', '/perfil'] as $page) {
            $this->get($page)->assertOk()->assertDontSee($letter)->assertDontSee('carta_apresentacao_mock');
        }
        $this->get('/ong/candidatos?contexto=ong')->assertOk()->assertSee($letter)->assertSee('Habilidades compatíveis');
        $this->assertNull(VisualTwoFixtures::make()['candidates'][1]['carta_apresentacao_mock']);
    }

    public function test_shared_editors_expose_independent_agenda_capacities_and_postcode_validation(): void
    {
        foreach (['/ong/eventos/criar', '/ong/eventos/1/editar'] as $page) {
            $this->get($page)->assertOk()->assertSee('Vagas informativas do evento')->assertSee('data-agenda-capacity-summary', false)->assertSee('data-event-postcode', false)->assertSee('inputmode="numeric"', false)->assertSee('data-agenda-capacity', false);
        }
        $this->get('/ong/eventos')->assertOk()->assertSee('data-event-disclosure', false)->assertSee('pessoas únicas');
        $this->get('/eventos/1?contexto=ong')->assertOk()->assertSee('aria-describedby="agenda-eligibility"', false)->assertSee('disabled', false)->assertDontSee('href="#agendas-evento"', false);
    }

    public function test_document_review_has_a_local_send_action_and_no_content_reader(): void
    {
        $this->get('/perfil?area=privada&cenario=correcao')->assertOk()->assertSee('Revisar documento')->assertSee('data-document-review', false)->assertSee('data-file-send', false)->assertSee('hidden="hidden"', false);
        $source = file_get_contents(resource_path('js/account-demo.js'));
        foreach (['fetch(', 'FileReader', 'createObjectURL', 'localStorage', 'sessionStorage', 'arrayBuffer(', '.text()'] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $source);
        }
    }
}
