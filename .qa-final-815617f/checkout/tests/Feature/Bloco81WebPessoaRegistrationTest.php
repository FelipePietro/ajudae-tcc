<?php

namespace Tests\Feature;

use App\Models\Pessoa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class Bloco81WebPessoaRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        if (! config('identity.enabled')) {
            $this->markTestSkipped('Executar com AJUDAE_WEB_AUTH=true.');
        }
    }

    public function test_cadastro_web_cria_conta_pendente_sem_emitir_token(): void
    {
        $payload = $this->pessoaPayload([
            'nm_pessoa' => 'Voluntária Sintética',
            'login_pessoa' => 'teste.cadastro.web',
            'email_pessoa' => 'cadastro@example.test',
            'role_pessoa' => 'administrador',
            'status_validacao' => 'aprovado',
            'admin_master' => true,
            'pfp_pessoa_link' => 'https://example.test/injetada.jpg',
        ]);

        $this->get(route('cadastro.pessoa'))->assertOk()
            ->assertSee('data-web-registration', false)
            ->assertSee('name="codigo_padrinho"', false)
            ->assertDontSee('data-demo-seed', false);

        $this->postJson(route('identity.registration.store'), $payload)
            ->assertCreated()->assertJsonPath('success', true)->assertJsonPath('data', null)
            ->assertSessionMissing('web_identity');

        $pessoa = Pessoa::where('login_pessoa', $payload['login_pessoa'])->firstOrFail();
        $this->assertSame('voluntario', $pessoa->role_pessoa);
        $this->assertSame('aguardando_validacao', $pessoa->status_validacao);
        $this->assertFalse($pessoa->admin_master);
        $this->assertSame('/images/avatar-pessoa.svg', $pessoa->pfp_pessoa_link);
        $this->assertTrue(Hash::check($payload['senha_pessoa'], $pessoa->getAuthPassword()));
        $this->assertDatabaseHas('assinatura', ['assinante_id' => $pessoa->getKey(), 'tipo_termo' => 'termos_de_uso']);
        $this->assertDatabaseCount('personal_access_tokens', 0);

        $this->postJson(route('identity.registration.store'), $payload)
            ->assertUnprocessable()->assertJsonValidationErrors(['cpf_pessoa', 'email_pessoa', 'login_pessoa']);
    }

    public function test_cadastro_web_recusa_menor_e_codigo_invalido_sem_gravar_conta(): void
    {
        $route = route('identity.registration.store');
        $this->postJson($route, $this->pessoaPayload([
            'dt_nasc' => now()->subYears(17)->toDateString(),
            'codigo_padrinho' => 'inválido',
        ]))->assertUnprocessable()->assertJsonValidationErrors(['dt_nasc', 'codigo_padrinho']);

        $this->post($route, $this->pessoaPayload())->assertStatus(406);
        $this->assertDatabaseCount('pessoa', 0);
        $this->assertDatabaseCount('assinatura', 0);
    }
}
