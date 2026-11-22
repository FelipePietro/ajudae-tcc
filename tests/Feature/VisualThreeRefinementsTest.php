<?php

namespace Tests\Feature;

use App\Services\WebApi;
use App\Support\PublicProfileFixtures;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VisualThreeRefinementsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['session.driver' => 'array', 'cache.default' => 'array']);
        DB::shouldReceive('connection')->andThrow(new \LogicException('Database forbidden.'));
        Http::preventStrayRequests();
        $this->mock(WebApi::class)->shouldNotReceive('call');
    }

    public function test_public_identity_is_selected_independently_of_observer_and_never_opens_private_area(): void
    {
        foreach (['visitante', 'pessoa', 'organizador', 'organizador-vinculado', 'ong', 'administrador'] as $context) {
            foreach ([1, 2, 3, 11, 12, 13] as $id) {
                $response = $this->get('/perfil?area=publica&pessoa='.$id.'&contexto='.$context.'&cenario=correcao')->assertOk()
                    ->assertSee(PublicProfileFixtures::selectedPerson($id)['nome_user'])->assertSee('data-nav-context="'.$context.'"', false);
                foreach (['Meu cadastro', 'Alterar preferências', 'data-visual-one', 'email_pessoa', 'cpf_pessoa', 'antecedentes', 'data-document', 'data-promotion-form'] as $private) {
                    $response->assertDontSee($private, false);
                }
            }
        }
        foreach (['999', '01', '-1', 'https://example.test'] as $id) {
            $this->get('/perfil?area=publica&pessoa='.urlencode($id))->assertNotFound();
        }
        $this->get('/perfil?pessoa[]=1')->assertNotFound();
        $this->get('/perfil?area=privada')->assertOk()->assertSee('data-visual-one', false);
    }

    public function test_public_return_is_internal_and_matches_source_identity(): void
    {
        $this->get('/perfil?pessoa=2&contexto=ong&origem=organizadores&convites=3')->assertOk()->assertSee('Retornar aos organizadores')->assertSee('convites=3');
        $this->get('/perfil?pessoa=1&contexto=visitante&origem=evento&evento_origem=8')->assertOk()->assertSee('Retornar ao evento')->assertSee('/eventos/8?contexto=visitante', false);
        $this->get('/perfil?pessoa=2&contexto=visitante&origem=evento&evento_origem=8')->assertOk()->assertDontSee('Retornar ao evento');
        $this->get('/perfil?pessoa=11&contexto=organizador-vinculado&origem=candidatos&evento_origem=1')->assertOk()->assertSee('Retornar às candidaturas');
        $this->get('/perfil?pessoa=11&contexto=organizador-vinculado&origem=candidatos&evento_origem=7')->assertOk()->assertDontSee('Retornar às candidaturas');
        $this->get('/perfil?pessoa=2&contexto=ong&origem=https://example.test&return_url=https://example.test')->assertOk()->assertDontSee('https://example.test');
    }

    public function test_private_promotion_summary_has_all_states_without_main_nav_shortcut(): void
    {
        foreach (['nao-elegivel' => 'Ainda não elegível', 'elegivel' => 'Você pode solicitar', 'pendente' => 'Aguardando análise', 'aprovada' => 'Condição de organizador aprovada', 'reprovada' => 'Solicitação recusada'] as $scenario => $status) {
            $html = $this->get('/perfil?area=privada&contexto=pessoa&jornada='.$scenario)->assertOk()->assertSee('Minha atuação como organizador')->assertSee($status)->assertDontSee('data-promotion-form', false)->getContent();
            $this->assertStringNotContainsString('Organizar atividades', $html);
        }
        $this->get('/feed?contexto=pessoa')->assertOk()->assertDontSee('/organizador/solicitacao', false);
    }

    public function test_independent_cards_keep_their_own_ids_actions_and_states(): void
    {
        foreach (['independente-vazio' => 0, 'independente-eventos' => 1, 'independente-dois' => 2, 'independente-varios' => 4] as $scenario => $count) {
            $html = $this->get('/organizador?contexto=organizador&jornada='.$scenario)->assertOk()->getContent();
            $this->assertSame($count, substr_count($html, 'data-organizer-event='));
            $this->assertStringNotContainsString('data-organizer-event="1"', $html);
            if ($count) {
                $this->assertStringContainsString('/ong/eventos/8/editar', $html);
            }
        }
        $this->get('/organizador?contexto=organizador&jornada=independente-varios')->assertSee('Aprovação pendente')->assertSee('Recusado')->assertSee('Finalizado')->assertSee('Você é proprietário e responsável');
        $this->get('/organizador?contexto=organizador-vinculado&jornada=vinculado-varios')->assertOk()->assertSee('data-organizer-event="1"', false)->assertSee('data-organizer-event="7"', false)->assertDontSee('data-organizer-event="8"', false);
    }

    public function test_invite_form_and_disposable_pending_ids_are_separate_from_membership(): void
    {
        $html = $this->get('/ong/organizadores?contexto=ong&jornada=ong-vazia&convites=3')->assertOk()->assertSee('Nome de usuário ou e-mail')->assertSee('Digite o usuário ou e-mail do organizador')->assertSee('Convites enviados')->assertSee('Organizadores vinculados')->getContent();
        $this->assertSame(1, substr_count($html, 'data-organizer-invite'));
        $this->assertStringContainsString('"pessoa_id":3,"status":"convite_pendente"', $html);
        preg_match('~<form[^>]*data-organizer-invite[^>]*>(.*?)</form>~s', $html, $invite);
        $this->assertStringNotContainsString('<select', $invite[1]);
        foreach (['convites[]=3', 'convites=https://example.test', 'convites=999'] as $invalid) {
            $this->get('/ong/organizadores?contexto=ong&jornada=ong-vazia&'.$invalid)->assertOk()->assertSee('"links":[]', false);
        }
        Http::assertNothingSent();
    }

    public function test_every_corrected_dialog_uses_bounded_variant_and_accessible_close(): void
    {
        foreach (['/organizador/solicitacao?jornada=elegivel', '/ong/organizadores', '/ong/candidatos', '/perfil/editar', '/minhas-candidaturas', '/ong/eventos/criar', '/ong/eventos/8/editar?contexto=organizador'] as $page) {
            $this->get($page)->assertOk()->assertSee('ajudae-dialog', false)->assertSee('aria-modal="true"', false)->assertSee('data-dialog-dismiss', false)->assertSee('Fechar diálogo');
        }
    }
}
