<?php

namespace Tests\Feature;

use App\Models\CatEvento;
use App\Models\Evento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OngAuthAndEventoTest extends TestCase
{
    use RefreshDatabase;

    private function categoria(): CatEvento
    {
        return CatEvento::create(['nome_categoria' => 'Educação']);
    }

    private function eventoPayload(CatEvento $categoria): array
    {
        return [
            'nm_evento' => 'Aula online',
            'descricao_evento' => 'Apoio escolar remoto para estudantes.',
            'vagas_evento' => 10,
            'modalidade_evento' => 'online',
            'imagem_evento_link' => 'imagens/evento.jpg',
            'cat_evento_id' => $categoria->cat_evento_id,
        ];
    }

    public function test_ong_register_login_and_me(): void
    {
        $register = $this->postJson('/api/v1/ongs/register', $this->ongPayload())
            ->assertCreated()
            ->assertJsonPath('data.ong.status_ong', 'aguardando aprovação');

        $this->withToken($register->json('data.token'))->getJson('/api/v1/ongs/me')
            ->assertOk()
            ->assertJsonPath('data.email_ong', 'contato@ong.test');

        $this->postJson('/api/v1/ongs/login', [
            'login_ong' => 'ong.exemplo',
            'senha_ong' => 'senha-segura',
        ])->assertOk()->assertJsonPath('success', true);
    }

    public function test_duplicate_cnpj_and_invalid_cnpj_are_rejected(): void
    {
        $this->postJson('/api/v1/ongs/register', $this->ongPayload())->assertCreated();

        $this->postJson('/api/v1/ongs/register', $this->ongPayload([
            'login_ong' => 'segunda.ong',
            'email_ong' => 'segunda@ong.test',
            'cpf_responsavel_ong' => '52998224725',
        ]))->assertUnprocessable()->assertJsonValidationErrors('cnpj_ong');

        $this->postJson('/api/v1/ongs/register', $this->ongPayload([
            'login_ong' => 'terceira.ong',
            'email_ong' => 'terceira@ong.test',
            'cnpj_ong' => '11111111111111',
            'cpf_responsavel_ong' => '52998224725',
        ]))->assertUnprocessable()->assertJsonValidationErrors('cnpj_ong');
    }

    public function test_pending_ong_cannot_create_event_but_approved_ong_can(): void
    {
        $categoria = $this->categoria();
        $ong = $this->createOng();
        Sanctum::actingAs($ong, ['ong']);

        $this->postJson('/api/v1/eventos', $this->eventoPayload($categoria))->assertForbidden();

        $ong->forceFill(['status_ong' => 'ativo'])->save();
        Sanctum::actingAs($ong->fresh(), ['ong']);

        $this->postJson('/api/v1/eventos', $this->eventoPayload($categoria))
            ->assertCreated()
            ->assertJsonPath('data.nm_evento', 'Aula online');

        $this->assertDatabaseHas('evento', [
            'ong_id' => $ong->ong_id,
            'pessoa_id' => null,
            'modalidade_evento' => 'online',
            'logradouro_evento' => null,
        ]);
    }

    public function test_organizer_can_manage_own_event_and_cannot_manage_another_owner_event(): void
    {
        $categoria = $this->categoria();
        $owner = $this->createPessoa('organizador');
        $other = $this->createPessoa('organizador', [
            'cpf_pessoa' => '11144477735',
            'email_pessoa' => 'outro@example.com',
            'login_pessoa' => 'outro.organizador',
        ]);

        Sanctum::actingAs($owner, ['pessoa']);
        $created = $this->postJson('/api/v1/eventos', $this->eventoPayload($categoria))->assertCreated();
        $eventoId = $created->json('data.evento_id');

        $this->patchJson("/api/v1/eventos/{$eventoId}", ['nm_evento' => 'Novo nome'])
            ->assertOk();

        Sanctum::actingAs($other, ['pessoa']);
        $this->patchJson("/api/v1/eventos/{$eventoId}", ['nm_evento' => 'Ataque'])
            ->assertForbidden();
    }

    public function test_public_ong_and_event_resources_hide_sensitive_data_and_unpublished_events(): void
    {
        $categoria = $this->categoria();
        $ong = $this->createOng('ativo');
        $evento = new Evento([
            ...$this->eventoPayload($categoria),
            'status_evento' => 'aguardando a confirmação',
        ]);
        $evento->forceFill([
            'ong_id' => $ong->ong_id,
            'pessoa_id' => null,
        ])->save();

        $ongResponse = $this->getJson("/api/v1/ongs/{$ong->ong_id}")->assertOk();
        foreach (['cnpj_ong', 'cpf_responsavel_ong', 'rg_responsavel_ong_link', 'email_responsavel_ong', 'senha_ong'] as $field) {
            $ongResponse->assertJsonMissingPath("data.{$field}");
        }

        $this->getJson('/api/v1/eventos')->assertOk()->assertJsonMissing(['evento_id' => $evento->evento_id]);
    }
}
