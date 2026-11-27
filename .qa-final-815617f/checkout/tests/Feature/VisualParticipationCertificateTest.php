<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VisualParticipationCertificateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['session.driver' => 'array', 'cache.default' => 'array']);
        DB::shouldReceive('connection')->andThrow(new \LogicException('Banco fora do escopo.'));
        Http::preventStrayRequests();
    }

    public function test_private_certificates_reuse_personal_identity_and_history_without_api(): void
    {
        foreach (['pessoa', 'organizador', 'organizador-vinculado'] as $context) {
            $this->get('/meus-certificados?contexto='.$context.'&exemplo=certificados')->assertOk()
                ->assertSee('"storageId":"7.certificados"', false)->assertSee('Gerar meu certificado')
                ->assertSee('"eventStatus":"finalizado"', false)->assertDontSee('Alex de Exemplo');
        }
        $this->get('/minhas-participacoes?contexto=pessoa&exemplo=certificados')->assertOk()->assertSee('meus-certificados', false);
        Http::assertNothingSent();
    }

    public function test_public_verification_has_no_private_fixture_or_pdf_endpoint(): void
    {
        $this->get('/certificados/verificar?contexto=visitante&codigo=invalido')->assertOk()
            ->assertSee('Código de verificação')->assertDontSee('data-certificate-fixture', false)
            ->assertDontSee('pessoa_id')->assertDontSee('avaliacoesConcluidas');
    }

    public function test_permissions_and_management_are_read_only(): void
    {
        foreach (['ong', 'visitante', 'administrador'] as $context) {
            $this->get('/meus-certificados?contexto='.$context)->assertForbidden();
        }
        $this->get('/meus-certificados?contexto=pessoa&pessoa=11')->assertNotFound();
        $this->get('/ong/eventos/7/presenca?contexto=ong&exemplo=certificados')->assertOk()
            ->assertSee('data-certificates="management"', false)->assertDontSee('data-certificate-issue', false);
    }
}
