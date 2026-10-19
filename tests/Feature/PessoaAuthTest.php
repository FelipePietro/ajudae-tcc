<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PessoaAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_pessoa_register_login_me_and_logout(): void
    {
        $register = $this->postJson('/api/v1/pessoas/register', $this->pessoaPayload())
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonMissingPath('data.user.cpf_pessoa')
            ->assertJsonMissingPath('data.user.senha_pessoa');

        $token = $register->json('data.token');

        $this->withToken($token)->getJson('/api/v1/pessoas/me')
            ->assertOk()
            ->assertJsonPath('data.email_pessoa', 'maria@example.com');

        $login = $this->postJson('/api/v1/pessoas/login', [
            'login_pessoa' => 'maria.voluntaria',
            'senha_pessoa' => 'senha-segura',
        ])->assertOk()->assertJsonPath('success', true);

        $loginToken = $login->json('data.token');
        $this->withToken($loginToken)->postJson('/api/v1/pessoas/logout')->assertOk();
        $this->assertDatabaseMissing('personal_access_tokens', ['token' => hash('sha256', $loginToken)]);
    }

    public function test_revoked_token_is_rejected_on_a_new_request_cycle(): void
    {
        $pessoa = $this->createPessoa();
        $token = $pessoa->createToken('revoked-token', ['pessoa'])->plainTextToken;
        $pessoa->tokens()->delete();

        $this->withToken($token)->getJson('/api/v1/pessoas/me')
            ->assertUnauthorized()
            ->assertJsonPath('success', false);
    }

    public function test_duplicate_and_invalid_documents_are_rejected(): void
    {
        $this->postJson('/api/v1/pessoas/register', $this->pessoaPayload())->assertCreated();

        $this->postJson('/api/v1/pessoas/register', $this->pessoaPayload())
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['cpf_pessoa', 'email_pessoa', 'login_pessoa']);

        $this->postJson('/api/v1/pessoas/register', $this->pessoaPayload([
            'cpf_pessoa' => '11111111111',
            'email_pessoa' => 'outra@example.com',
            'login_pessoa' => 'outra',
        ]))->assertUnprocessable()->assertJsonValidationErrors('cpf_pessoa');
    }

    public function test_login_does_not_enumerate_accounts(): void
    {
        $this->createPessoa();

        foreach ([
            ['login_pessoa' => 'inexistente', 'senha_pessoa' => 'senha-segura'],
            ['login_pessoa' => 'maria.voluntaria', 'senha_pessoa' => 'senha-errada'],
        ] as $credentials) {
            $this->postJson('/api/v1/pessoas/login', $credentials)
                ->assertUnauthorized()
                ->assertExactJson(['success' => false, 'message' => 'Credenciais inválidas.', 'errors' => null]);
        }
    }

    public function test_public_person_resource_does_not_expose_sensitive_data(): void
    {
        $pessoa = $this->createPessoa();

        $response = $this->getJson("/api/v1/pessoas/{$pessoa->pessoa_id}")->assertOk();
        foreach (['cpf_pessoa', 'rg_pessoa', 'dt_nasc', 'tele_pessoa', 'senha_pessoa', 'antecedentes_pessoa_link', 'logradouro_pessoa'] as $field) {
            $response->assertJsonMissingPath("data.{$field}");
        }
    }
}
