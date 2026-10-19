<?php

namespace Tests\Feature;

use App\Models\Causa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthorizationAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_and_non_admin_access_are_rejected(): void
    {
        $this->getJson('/api/v1/pessoas/me')->assertUnauthorized()->assertJsonPath('success', false);

        $person = $this->createPessoa();
        Sanctum::actingAs($person, ['pessoa']);
        $this->postJson('/api/v1/categorias-evento', ['nome_categoria' => 'Saúde'])->assertForbidden();
    }

    public function test_admin_can_manage_category_and_approval_is_audited(): void
    {
        $admin = $this->createPessoa('administrador');
        $this->grantPermission($admin, 'ongs.validar');
        $ong = $this->createOng();
        Sanctum::actingAs($admin, ['pessoa']);

        $this->postJson('/api/v1/categorias-evento', ['nome_categoria' => 'Saúde'])
            ->assertCreated();

        $this->patchJson("/api/v1/ongs/{$ong->ong_id}/status", ['status_ong' => 'ativo'])
            ->assertOk();

        $this->assertDatabaseHas('admin_log', [
            'pessoa_id' => $admin->pessoa_id,
            'acao' => 'ong.status_atualizado',
            'recurso_id' => $ong->ong_id,
        ]);
    }

    public function test_person_cannot_change_another_person_causes(): void
    {
        $owner = $this->createPessoa();
        $other = $this->createPessoa('voluntario', [
            'cpf_pessoa' => '11144477735',
            'email_pessoa' => 'other@example.com',
            'login_pessoa' => 'other',
        ]);
        $causa = Causa::create(['nome_causa' => 'Educação', 'descricao_causa' => 'Apoio educacional']);

        Sanctum::actingAs($other, ['pessoa']);
        $this->postJson("/api/v1/pessoas/{$owner->pessoa_id}/causas", ['causa_id' => $causa->causa_id])
            ->assertForbidden();
    }

    public function test_duplicate_person_cause_is_rejected(): void
    {
        $owner = $this->createPessoa();
        $causa = Causa::create(['nome_causa' => 'Educação', 'descricao_causa' => 'Apoio educacional']);
        Sanctum::actingAs($owner, ['pessoa']);

        $this->postJson("/api/v1/pessoas/{$owner->pessoa_id}/causas", ['causa_id' => $causa->causa_id])
            ->assertCreated();
        $this->postJson("/api/v1/pessoas/{$owner->pessoa_id}/causas", ['causa_id' => $causa->causa_id])
            ->assertStatus(409);
    }

    public function test_user_can_only_read_own_notifications(): void
    {
        $owner = $this->createPessoa();
        $other = $this->createPessoa('voluntario', [
            'cpf_pessoa' => '11144477735',
            'email_pessoa' => 'other@example.com',
            'login_pessoa' => 'other',
        ]);
        $notification = $owner->notificacoes()->create(['mensagem_notificacao' => 'Mensagem privada']);

        Sanctum::actingAs($other, ['pessoa']);
        $this->getJson("/api/v1/notificacoes/{$notification->notificacao_id}")->assertNotFound();
    }
}
