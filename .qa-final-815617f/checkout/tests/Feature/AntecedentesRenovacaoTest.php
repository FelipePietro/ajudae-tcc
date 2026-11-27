<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\CatEvento;
use App\Models\DocumentoPessoa;
use App\Models\Evento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AntecedentesRenovacaoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-13 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_validade_doze_meses_alerta_uma_vez_sem_bloquear_pessoa_ou_candidatura(): void
    {
        $pessoa = $this->createPessoa();
        $documento = $this->aprovado($pessoa->pessoa_id, now()->subMonths(12)->addSecond());
        Sanctum::actingAs($pessoa, ['pessoa']);
        $this->getJson('/api/v1/pessoas/me/antecedentes')
            ->assertOk()->assertJsonPath('data.ultima_aprovacao.status', 'aprovado')
            ->assertJsonPath('data.renovacao_necessaria', false);
        $this->assertSame(0, $pessoa->notificacoes()->where('tipo_notificacao', 'antecedentes.renovacao_necessaria')->count());

        Carbon::setTestNow($documento->expires_at);
        $this->getJson('/api/v1/pessoas/me/antecedentes')
            ->assertOk()->assertJsonPath('data.ultima_aprovacao.status', 'expirado')
            ->assertJsonPath('data.renovacao_necessaria', true);
        $this->getJson('/api/v1/pessoas/me/antecedentes')->assertOk();
        $this->artisan('antecedentes:alertar-renovacao')->assertExitCode(0);
        $this->assertSame(1, $pessoa->notificacoes()->where('tipo_notificacao', 'antecedentes.renovacao_necessaria')->count());
        $this->assertSame('aprovado', $pessoa->fresh()->status_validacao);

        $organizador = $this->createPessoa('organizador', [
            'cpf_pessoa' => '00000009101', 'email_pessoa' => 'organtecedente@example.com',
            'login_pessoa' => 'organtecedente',
        ]);
        $evento = new Evento([
            'nm_evento' => 'Ação', 'descricao_evento' => 'Ação voluntária',
            'vagas_evento' => 10, 'modalidade_evento' => 'online',
            'imagem_evento_link' => 'imagem.jpg',
            'cat_evento_id' => CatEvento::firstOrCreate(['nome_categoria' => 'Educação'])->cat_evento_id,
            'status_evento' => 'ativo',
        ]);
        $evento->forceFill(['pessoa_id' => $organizador->pessoa_id, 'responsavel_pessoa_id' => $organizador->pessoa_id])->save();
        $agenda = Agenda::create([
            'evento_id' => $evento->evento_id, 'status_ativo' => 'ativo',
            'data_inicio' => now()->addDays(2), 'data_fim' => now()->addDays(2)->addHour(),
            'vagas_agenda' => 10,
        ]);
        $this->postJson("/api/v1/agendas/{$agenda->agenda_id}/inscricoes")->assertCreated();
    }

    public function test_renovacao_pendente_preserva_ultima_aprovacao_e_decisao_manual(): void
    {
        Storage::fake('local');
        $pessoa = $this->createPessoa();
        $aprovadoEm = $pessoa->aprovado_em->copy();
        $antigo = $this->aprovado($pessoa->pessoa_id, now()->subMonths(11));
        Sanctum::actingAs($pessoa, ['pessoa']);
        $novo = $this->postJson('/api/v1/pessoas/me/documentos/antecedentes_criminais', [
            'arquivo' => UploadedFile::fake()->create('renovacao.pdf', 100, 'application/pdf'),
        ])->assertCreated()->json('data.documento_pessoa_id');
        $this->assertFalse($antigo->fresh()->ativo);
        $this->assertTrue($antigo->fresh()->delete_after->equalTo($antigo->validated_at->copy()->addDays(30)));
        $this->getJson('/api/v1/pessoas/me/antecedentes')->assertOk()
            ->assertJsonPath('data.ultima_aprovacao.documento_pessoa_id', $antigo->getKey())
            ->assertJsonPath('data.renovacao.documento_pessoa_id', $novo)
            ->assertJsonPath('data.renovacao.status', 'pendente');

        $adminSemPermissao = $this->createPessoa('administrador', [
            'cpf_pessoa' => '00000009102', 'email_pessoa' => 'semantecedente@example.com',
            'login_pessoa' => 'semantecedente',
        ]);
        Sanctum::actingAs($adminSemPermissao, ['pessoa']);
        $this->patchJson("/api/v1/admin/documentos/{$novo}/status", ['status' => 'aprovado'])->assertForbidden();

        $falsoAdmin = $this->createPessoa('voluntario', [
            'cpf_pessoa' => '00000009103', 'email_pessoa' => 'falsoantecedente@example.com',
            'login_pessoa' => 'falsoantecedente',
        ]);
        $this->grantPermission($falsoAdmin, 'usuarios.validar');
        Sanctum::actingAs($falsoAdmin, ['pessoa']);
        $this->patchJson("/api/v1/admin/documentos/{$novo}/status", ['status' => 'aprovado'])->assertForbidden();

        $admin = $this->createPessoa('administrador', [
            'cpf_pessoa' => '00000009104', 'email_pessoa' => 'antecedente@example.com',
            'login_pessoa' => 'antecedente',
        ]);
        $this->grantPermission($admin, 'usuarios.validar');
        Sanctum::actingAs($admin, ['pessoa']);
        $this->getJson('/api/v1/admin/antecedentes/renovacoes')->assertOk()
            ->assertJsonFragment(['documento_pessoa_id' => $novo]);
        $this->patchJson("/api/v1/admin/documentos/{$novo}/status", [
            'status' => 'rejeitado', 'motivo' => 'Arquivo ilegível.',
        ])->assertOk();
        $this->patchJson("/api/v1/admin/documentos/{$novo}/status", ['status' => 'aprovado'])->assertStatus(409);
        $this->assertDatabaseHas('documento_pessoa', [
            'documento_pessoa_id' => $novo, 'status' => 'rejeitado',
            'validated_by_admin_id' => $admin->pessoa_id, 'rejection_reason' => 'Arquivo ilegível.',
        ]);
        $this->assertSame('aprovado', $antigo->fresh()->status);
        $this->assertSame('aprovado', $pessoa->fresh()->status_validacao);
        $this->assertTrue($pessoa->fresh()->aprovado_em->equalTo($aprovadoEm));

        Sanctum::actingAs($pessoa, ['pessoa']);
        $maisNovo = $this->postJson('/api/v1/pessoas/me/documentos/antecedentes_criminais', [
            'arquivo' => UploadedFile::fake()->create('renovacao-2.pdf', 100, 'application/pdf'),
        ])->assertCreated()->json('data.documento_pessoa_id');
        $master = $this->createPessoa('administrador', [
            'cpf_pessoa' => '00000009105', 'email_pessoa' => 'masterantecedente@example.com',
            'login_pessoa' => 'masterantecedente', 'admin_master' => true,
        ]);
        Sanctum::actingAs($master, ['pessoa']);
        $this->patchJson("/api/v1/admin/documentos/{$maisNovo}/status", ['status' => 'aprovado'])->assertOk();
        $this->assertTrue(DocumentoPessoa::findOrFail($maisNovo)->delete_after
            ->equalTo(DocumentoPessoa::findOrFail($maisNovo)->validated_at->copy()->addDays(30)));
        $this->assertSame('aprovado', $pessoa->fresh()->status_validacao);
    }

    public function test_arquivo_e_metadados_sao_privados_e_upload_invalido_nao_cria_versao(): void
    {
        Storage::fake('local');
        $titular = $this->createPessoa();
        Sanctum::actingAs($titular, ['pessoa']);
        $id = $this->postJson('/api/v1/pessoas/me/documentos/antecedentes_criminais', [
            'arquivo' => UploadedFile::fake()->create('certidao.pdf', 100, 'application/pdf'),
        ])->assertCreated()->assertDontSee('storage_path')->json('data.documento_pessoa_id');
        $this->postJson('/api/v1/pessoas/me/documentos/antecedentes_criminais', [
            'arquivo' => UploadedFile::fake()->create('invalido.txt', 100, 'text/plain'),
        ])->assertUnprocessable();
        $this->assertSame(1, DocumentoPessoa::where('pessoa_id', $titular->pessoa_id)->count());
        $this->getJson('/api/v1/pessoas/me/antecedentes')->assertOk()->assertDontSee('storage_path');
        $this->get("/api/v1/documentos/{$id}/download")->assertOk();

        foreach (['voluntario', 'organizador'] as $role) {
            $outro = $this->createPessoa($role, [
                'cpf_pessoa' => $role === 'voluntario' ? '00000009106' : '00000009107',
                'email_pessoa' => "{$role}doc@example.com", 'login_pessoa' => "{$role}doc",
            ]);
            Sanctum::actingAs($outro, ['pessoa']);
            $this->getJson("/api/v1/documentos/{$id}/download")->assertForbidden();
            $this->getJson('/api/v1/pessoas/me/antecedentes')->assertOk()
                ->assertJsonPath('data.ultima_aprovacao', null);
        }
        Sanctum::actingAs($this->createOng('ativo'), ['ong']);
        $this->getJson("/api/v1/documentos/{$id}/download")->assertForbidden();
        $this->getJson('/api/v1/pessoas/me/antecedentes')->assertForbidden();
    }

    private function aprovado(int $pessoaId, Carbon $validadoEm): DocumentoPessoa
    {
        return DocumentoPessoa::create([
            'pessoa_id' => $pessoaId, 'tipo' => 'antecedentes_criminais',
            'storage_path' => 'documentos/privado.pdf', 'mime_type' => 'application/pdf',
            'size_bytes' => 100, 'status' => 'aprovado', 'ativo' => true,
            'uploaded_at' => $validadoEm->copy()->subDay(), 'validated_at' => $validadoEm,
            'expires_at' => $validadoEm->copy()->addMonths(12),
        ]);
    }
}
