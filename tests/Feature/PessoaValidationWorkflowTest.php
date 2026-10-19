<?php

namespace Tests\Feature;

use App\Models\DocumentoPessoa;
use App\Services\PessoaValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PessoaValidationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $suffix, bool $master = false)
    {
        $admin = $this->createPessoa('administrador', [
            'cpf_pessoa' => str_pad($suffix, 11, '0', STR_PAD_LEFT),
            'email_pessoa' => "admin{$suffix}@example.com",
            'login_pessoa' => "admin{$suffix}",
            'admin_master' => $master,
        ]);
        if (! $master) {
            $this->grantPermission($admin, 'usuarios.validar');
        }

        return $admin;
    }

    public function test_lock_de_analise_dura_24_horas_e_reassumir_e_idempotente(): void
    {
        Carbon::setTestNow('2026-10-09 10:00:00');
        $pessoa = $this->createPessoa('voluntario', ['status_validacao' => 'aguardando_validacao']);
        $adminA = $this->admin('61');
        $adminB = $this->admin('62');

        Sanctum::actingAs($adminA, ['pessoa']);
        $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/assumir")
            ->assertOk()
            ->assertJsonPath('data.status_validacao', 'em_analise')
            ->assertJsonPath('data.admin_analisando_id', $adminA->pessoa_id);
        $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/assumir")->assertOk();
        $this->assertDatabaseCount('historico_validacao_pessoa', 1);

        Sanctum::actingAs($adminB, ['pessoa']);
        $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/assumir")->assertStatus(409);

        Carbon::setTestNow(now()->addHours(25));
        $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/assumir")
            ->assertOk()
            ->assertJsonPath('data.admin_analisando_id', $adminB->pessoa_id);
        $this->assertDatabaseCount('historico_validacao_pessoa', 2);
    }

    public function test_correcao_exige_motivo_limpa_lock_e_nao_conta_como_reprovacao(): void
    {
        $pessoa = $this->createPessoa('voluntario', ['status_validacao' => 'em_analise']);
        $admin = $this->admin('63');
        $pessoa->forceFill(['admin_analisando_id' => $admin->pessoa_id, 'analise_assumida_em' => now()])->save();
        Sanctum::actingAs($admin, ['pessoa']);

        $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/solicitar-correcao")
            ->assertUnprocessable();
        $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/solicitar-correcao", [
            'motivo' => 'Documento ilegível.',
        ])->assertOk()->assertJsonPath('data.status_validacao', 'correcao_solicitada');

        $this->assertDatabaseHas('pessoa', [
            'pessoa_id' => $pessoa->pessoa_id,
            'admin_analisando_id' => null,
            'analise_assumida_em' => null,
        ]);
        $this->assertDatabaseHas('historico_validacao_pessoa', [
            'pessoa_id' => $pessoa->pessoa_id,
            'status_novo' => 'correcao_solicitada',
            'numero_tentativa' => null,
        ]);
        $this->assertDatabaseMissing('historico_validacao_pessoa', [
            'pessoa_id' => $pessoa->pessoa_id,
            'status_novo' => 'reprovado',
        ]);
    }

    public function test_tres_reprovacoes_em_30_dias_bloqueiam_reenvio_por_sete_dias(): void
    {
        Carbon::setTestNow('2026-10-09 10:00:00');
        $pessoa = $this->createPessoa('voluntario', ['status_validacao' => 'aguardando_validacao']);
        $admin = $this->admin('64');
        Sanctum::actingAs($admin, ['pessoa']);

        $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/reprovar")
            ->assertUnprocessable();

        for ($tentativa = 1; $tentativa <= 3; $tentativa++) {
            $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/assumir")->assertOk();
            $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/reprovar", [
                'motivo' => "Reprovação {$tentativa}",
            ])->assertOk();

            $pessoa->refresh();
            if ($tentativa < 3) {
                $this->assertNull($pessoa->bloqueado_ate);
                Sanctum::actingAs($pessoa, ['pessoa']);
                $this->postJson('/api/v1/pessoas/me/validacao/reenviar')->assertOk();
                Sanctum::actingAs($admin, ['pessoa']);
            }
        }

        $this->assertTrue($pessoa->refresh()->bloqueado_ate->equalTo(now()->addDays(7)));
        Sanctum::actingAs($pessoa, ['pessoa']);
        $this->postJson('/api/v1/pessoas/me/validacao/reenviar')->assertStatus(409);

        Carbon::setTestNow(now()->addDays(8));
        $this->postJson('/api/v1/pessoas/me/validacao/reenviar')
            ->assertOk()
            ->assertJsonPath('data.status_validacao', 'aguardando_validacao');
    }

    public function test_reprovacao_fora_da_janela_nao_aciona_bloqueio(): void
    {
        Carbon::setTestNow('2026-10-09 10:00:00');
        $pessoa = $this->createPessoa('voluntario', ['status_validacao' => 'aguardando_validacao']);
        $admin = $this->admin('65');
        $historicoAntigo = $pessoa->historicoValidacao()->create([
            'admin_id' => $admin->pessoa_id,
            'status_anterior' => 'aguardando_validacao',
            'status_novo' => 'reprovado',
            'motivo' => 'Antiga',
            'numero_tentativa' => 1,
            'concluida_em' => now()->subDays(31),
        ]);
        $historicoAntigo->forceFill([
            'created_at' => now()->subDays(31),
            'updated_at' => now()->subDays(31),
        ])->save();
        Sanctum::actingAs($admin, ['pessoa']);

        foreach ([1, 2] as $tentativa) {
            $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/assumir")->assertOk();
            $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/reprovar", [
                'motivo' => "Atual {$tentativa}",
            ])->assertOk();
            if ($tentativa === 1) {
                Sanctum::actingAs($pessoa, ['pessoa']);
                $this->postJson('/api/v1/pessoas/me/validacao/reenviar')->assertOk();
                Sanctum::actingAs($admin, ['pessoa']);
            }
        }

        $this->assertNull($pessoa->refresh()->bloqueado_ate);
    }

    public function test_aprovacao_exige_identidade_e_antecedentes_aprovados_e_reseta_ciclo(): void
    {
        $pessoa = $this->createPessoa('voluntario', ['status_validacao' => 'aguardando_validacao']);
        $admin = $this->admin('66');
        Sanctum::actingAs($admin, ['pessoa']);

        foreach ([1, 2] as $tentativa) {
            $historico = $pessoa->historicoValidacao()->create([
                'admin_id' => $admin->pessoa_id,
                'status_anterior' => 'em_analise',
                'status_novo' => 'reprovado',
                'motivo' => "Ciclo anterior {$tentativa}",
                'numero_tentativa' => $tentativa,
                'concluida_em' => now()->subDay(),
            ]);
            $historico->forceFill(['created_at' => now()->subDay(), 'updated_at' => now()->subDay()])->save();
        }

        $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/assumir")->assertOk();

        $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/aprovar")->assertStatus(409);

        foreach (['documento_identidade', 'antecedentes_criminais'] as $tipo) {
            DocumentoPessoa::create([
                'pessoa_id' => $pessoa->pessoa_id,
                'tipo' => $tipo,
                'storage_path' => "documentos/{$tipo}.pdf",
                'mime_type' => 'application/pdf',
                'size_bytes' => 100,
                'status' => 'aprovado',
                'ativo' => true,
                'uploaded_at' => now(),
                'validated_at' => now(),
                'validated_by_admin_id' => $admin->pessoa_id,
            ]);
        }

        $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/aprovar")
            ->assertOk()
            ->assertJsonPath('data.status_validacao', 'aprovado');
        $this->assertNotNull($pessoa->refresh()->aprovado_em);
        $this->assertNull($pessoa->admin_analisando_id);
        $this->assertNull($pessoa->bloqueado_ate);
        $this->assertSame(0, app(PessoaValidationService::class)->contarReprovacoesDoCiclo($pessoa->refresh()));
    }

    public function test_reprovacao_definitiva_e_exclusiva_de_master_e_impede_reenvio(): void
    {
        $pessoa = $this->createPessoa('voluntario', ['status_validacao' => 'aguardando_validacao']);
        $admin = $this->admin('67');
        $master = $this->admin('68', true);

        Sanctum::actingAs($admin, ['pessoa']);
        $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/reprovar-definitivamente", [
            'motivo' => 'Fraude confirmada.',
        ])->assertForbidden();

        Sanctum::actingAs($master, ['pessoa']);
        $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/reprovar-definitivamente")
            ->assertUnprocessable();
        $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/assumir")->assertOk();
        $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/reprovar-definitivamente", [
            'motivo' => 'Fraude confirmada.',
        ])->assertOk()->assertJsonPath('data.status_validacao', 'reprovado_definitivamente');

        Sanctum::actingAs($pessoa, ['pessoa']);
        $this->postJson('/api/v1/pessoas/me/validacao/reenviar')->assertStatus(409);
    }

    public function test_outro_admin_nao_aprova_analise_com_lock_ativo(): void
    {
        $pessoa = $this->createPessoa('voluntario', ['status_validacao' => 'aguardando_validacao']);
        $dono = $this->admin('69');
        $outro = $this->admin('70');

        Sanctum::actingAs($dono, ['pessoa']);
        $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/assumir")->assertOk();
        Sanctum::actingAs($outro, ['pessoa']);
        $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/aprovar")->assertStatus(409);
    }

    public function test_outro_admin_nao_reprova_analise_com_lock_ativo(): void
    {
        $pessoa = $this->createPessoa('voluntario', ['status_validacao' => 'aguardando_validacao']);
        $dono = $this->admin('71');
        $outro = $this->admin('72');

        Sanctum::actingAs($dono, ['pessoa']);
        $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/assumir")->assertOk();
        Sanctum::actingAs($outro, ['pessoa']);
        $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/reprovar", [
            'motivo' => 'Tentativa indevida.',
        ])->assertStatus(409);
    }

    public function test_outro_admin_nao_solicita_correcao_com_lock_ativo(): void
    {
        $pessoa = $this->createPessoa('voluntario', ['status_validacao' => 'aguardando_validacao']);
        $dono = $this->admin('73');
        $outro = $this->admin('74');

        Sanctum::actingAs($dono, ['pessoa']);
        $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/assumir")->assertOk();
        Sanctum::actingAs($outro, ['pessoa']);
        $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/solicitar-correcao", [
            'motivo' => 'Tentativa indevida.',
        ])->assertStatus(409);
    }

    public function test_admin_sem_assumir_validacao_aguardando_nao_consegue_aprovar(): void
    {
        $pessoa = $this->createPessoa('voluntario', ['status_validacao' => 'aguardando_validacao']);
        $admin = $this->admin('75');

        Sanctum::actingAs($admin, ['pessoa']);
        $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/aprovar")->assertStatus(409);
    }

    public function test_outro_master_nao_reprova_definitivamente_analise_com_lock_ativo(): void
    {
        $pessoa = $this->createPessoa('voluntario', ['status_validacao' => 'aguardando_validacao']);
        $dono = $this->admin('79', true);
        $outro = $this->admin('80', true);

        Sanctum::actingAs($dono, ['pessoa']);
        $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/assumir")->assertOk();
        Sanctum::actingAs($outro, ['pessoa']);
        $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/reprovar-definitivamente", [
            'motivo' => 'Tentativa indevida.',
        ])->assertStatus(409);
    }

    public function test_pessoa_aprovada_nao_pode_ser_reprovada_nem_aprovada_novamente(): void
    {
        $pessoa = $this->createPessoa('voluntario', ['status_validacao' => 'aprovado']);
        $admin = $this->admin('76');

        Sanctum::actingAs($admin, ['pessoa']);
        $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/reprovar", [
            'motivo' => 'Transição proibida.',
        ])->assertStatus(409);
        $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/solicitar-correcao", [
            'motivo' => 'Transição proibida.',
        ])->assertStatus(409);
        $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/aprovar")->assertStatus(409);
        $this->assertSame('aprovado', $pessoa->fresh()->status_validacao);
    }

    public function test_pessoa_definitivamente_reprovada_nao_pode_ser_aprovada_nem_voltar_ao_fluxo(): void
    {
        $pessoa = $this->createPessoa('voluntario', ['status_validacao' => 'reprovado_definitivamente']);
        $admin = $this->admin('77');

        Sanctum::actingAs($admin, ['pessoa']);
        $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/aprovar")->assertStatus(409);
        $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/reprovar", [
            'motivo' => 'Transição proibida.',
        ])->assertStatus(409);
        $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/solicitar-correcao", [
            'motivo' => 'Transição proibida.',
        ])->assertStatus(409);
        $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/assumir")->assertStatus(409);
        Sanctum::actingAs($pessoa, ['pessoa']);
        $this->postJson('/api/v1/pessoas/me/validacao/reenviar')->assertStatus(409);
        $this->assertSame('reprovado_definitivamente', $pessoa->fresh()->status_validacao);
    }
}
