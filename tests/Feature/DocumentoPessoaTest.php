<?php

namespace Tests\Feature;

use App\Models\DocumentoPessoa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DocumentoPessoaTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_privado_aceita_pdf_e_imagem_e_mantem_uma_versao_ativa(): void
    {
        Storage::fake('local');
        $pessoa = $this->createPessoa('voluntario', ['status_validacao' => 'aguardando_validacao']);
        Sanctum::actingAs($pessoa, ['pessoa']);

        $primeiro = $this->postJson('/api/v1/pessoas/me/documentos/documento_identidade', [
            'arquivo' => UploadedFile::fake()->create('identidade.pdf', 100, 'application/pdf'),
        ])->assertCreated()->json('data.documento_pessoa_id');

        $segundo = $this->postJson('/api/v1/pessoas/me/documentos/documento_identidade', [
            'arquivo' => UploadedFile::fake()->image('identidade.jpg'),
        ])->assertCreated()->json('data.documento_pessoa_id');

        $this->assertDatabaseHas('documento_pessoa', ['documento_pessoa_id' => $primeiro, 'ativo' => false]);
        $this->assertDatabaseHas('documento_pessoa', ['documento_pessoa_id' => $segundo, 'ativo' => true]);
        $this->assertNotNull(DocumentoPessoa::findOrFail($primeiro)->delete_after);
        $this->assertSame(1, DocumentoPessoa::where('pessoa_id', $pessoa->pessoa_id)
            ->where('tipo', 'documento_identidade')->where('ativo', true)->count());
    }

    public function test_upload_rejeita_tipo_formato_e_tamanho_invalidos(): void
    {
        Storage::fake('local');
        $pessoa = $this->createPessoa('voluntario', ['status_validacao' => 'aguardando_validacao']);
        Sanctum::actingAs($pessoa, ['pessoa']);

        $this->postJson('/api/v1/pessoas/me/documentos/tipo_inexistente', [
            'arquivo' => UploadedFile::fake()->create('arquivo.pdf', 10, 'application/pdf'),
        ])->assertUnprocessable();
        $this->postJson('/api/v1/pessoas/me/documentos/cnh', [
            'arquivo' => UploadedFile::fake()->create('arquivo.txt', 10, 'text/plain'),
        ])->assertUnprocessable()->assertJsonValidationErrors('arquivo');
        $this->postJson('/api/v1/pessoas/me/documentos/cnh', [
            'arquivo' => UploadedFile::fake()->create('grande.pdf', 10241, 'application/pdf'),
        ])->assertUnprocessable()->assertJsonValidationErrors('arquivo');
    }

    public function test_documento_so_pode_ser_baixado_pelo_dono_ou_admin_autorizado(): void
    {
        Storage::fake('local');
        $dono = $this->createPessoa();
        $outro = $this->createPessoa('voluntario', [
            'cpf_pessoa' => '00000000071', 'email_pessoa' => 'outrodoc@example.com', 'login_pessoa' => 'outrodoc',
        ]);
        Sanctum::actingAs($dono, ['pessoa']);
        $documentoId = $this->postJson('/api/v1/pessoas/me/documentos/documento_identidade', [
            'arquivo' => UploadedFile::fake()->create('identidade.pdf', 100, 'application/pdf'),
        ])->assertCreated()->json('data.documento_pessoa_id');

        $this->app['auth']->forgetGuards();
        $this->getJson("/api/v1/documentos/{$documentoId}/download")->assertUnauthorized();
        Sanctum::actingAs($dono, ['pessoa']);
        $this->get("/api/v1/documentos/{$documentoId}/download")->assertOk();
        Sanctum::actingAs($outro, ['pessoa']);
        $this->getJson("/api/v1/documentos/{$documentoId}/download")->assertForbidden();

        $admin = $this->createPessoa('administrador', [
            'cpf_pessoa' => '00000000072', 'email_pessoa' => 'admindoc@example.com', 'login_pessoa' => 'admindoc',
        ]);
        $this->grantPermission($admin, 'usuarios.validar');
        Sanctum::actingAs($admin, ['pessoa']);
        $this->get("/api/v1/documentos/{$documentoId}/download")->assertOk();

        $ong = $this->createOng('ativo');
        Sanctum::actingAs($ong, ['ong']);
        $this->getJson("/api/v1/documentos/{$documentoId}/download")->assertForbidden();
    }

    public function test_download_exige_papel_administrativo_para_terceiros_sem_bloquear_titulares(): void
    {
        Storage::fake('local');
        $sequence = 800;
        $pessoa = function (string $role = 'voluntario', bool $master = false) use (&$sequence) {
            $id = $sequence++;

            return $this->createPessoa($role, [
                'cpf_pessoa' => str_pad((string) $id, 11, '0', STR_PAD_LEFT),
                'email_pessoa' => "download{$id}@example.com",
                'login_pessoa' => "download{$id}",
                'admin_master' => $master,
            ]);
        };

        $titular = $pessoa();
        $organizadorTitular = $pessoa('organizador');
        $historico = $this->arquivoDe($titular->pessoa_id, 'documentos/pessoa/antecedentes-antigos.pdf', false);
        $atual = $this->arquivoDe($titular->pessoa_id, 'documentos/pessoa/antecedentes-atuais.pdf', true);
        $doOrganizador = $this->arquivoDe($organizadorTitular->pessoa_id, 'documentos/pessoa/organizador.pdf', true);
        $rota = "/api/v1/documentos/{$atual->getKey()}/download";
        $atributosAntes = $atual->fresh()->getAttributes();
        $totalDocumentos = DocumentoPessoa::count();

        $this->getJson($rota)->assertUnauthorized();
        Sanctum::actingAs($titular, ['pessoa']);
        $this->get($rota)->assertOk();
        $this->get("/api/v1/documentos/{$historico->getKey()}/download")->assertOk();
        Sanctum::actingAs($organizadorTitular, ['pessoa']);
        $this->get("/api/v1/documentos/{$doOrganizador->getKey()}/download")->assertOk();

        $voluntario = $pessoa();
        $organizador = $pessoa('organizador');
        $adminSemPermissao = $pessoa('administrador');
        $adminValidacao = $pessoa('administrador');
        $adminLgpd = $pessoa('administrador');
        $master = $pessoa('administrador', true);
        $this->grantPermission($adminValidacao, 'usuarios.validar');
        $this->grantPermission($adminLgpd, 'lgpd.visualizar');

        foreach ([$voluntario, $adminSemPermissao] as $ator) {
            Sanctum::actingAs($ator, ['pessoa']);
            $this->getJson($rota)->assertForbidden()->assertDontSee('documentos/pessoa/');
        }
        foreach (['usuarios.validar', 'lgpd.visualizar'] as $permissao) {
            $this->grantPermission($voluntario, $permissao);
            Sanctum::actingAs($voluntario, ['pessoa']);
            $this->getJson($rota)->assertForbidden()->assertDontSee('documentos/pessoa/');
            $this->grantPermission($organizador, $permissao);
            Sanctum::actingAs($organizador, ['pessoa']);
            $this->getJson($rota)->assertForbidden()->assertDontSee('documentos/pessoa/');
        }

        $ong = $this->createOng('ativo');
        Sanctum::actingAs($ong, ['ong']);
        $this->getJson($rota)->assertForbidden()->assertDontSee('documentos/pessoa/');

        foreach ([$adminValidacao, $adminLgpd, $master] as $admin) {
            Sanctum::actingAs($admin, ['pessoa']);
            $this->get($rota)->assertOk();
        }
        Sanctum::actingAs($titular, ['pessoa']);
        $this->getJson('/api/v1/documentos/999999999/download')->assertNotFound();
        $ausente = $this->arquivoDe($titular->pessoa_id, 'documentos/pessoa/ausente.pdf', false, false);
        $this->getJson("/api/v1/documentos/{$ausente->getKey()}/download")->assertNotFound();
        Sanctum::actingAs($adminValidacao, ['pessoa']);
        $this->getJson("/api/v1/documentos/{$ausente->getKey()}/download")->assertNotFound();

        $this->assertSame($atributosAntes, $atual->fresh()->getAttributes());
        $this->assertSame($totalDocumentos + 1, DocumentoPessoa::count());
        Storage::disk('local')->assertExists('documentos/pessoa/antecedentes-antigos.pdf');
        Storage::disk('local')->assertExists('documentos/pessoa/antecedentes-atuais.pdf');
        Storage::disk('local')->assertExists('documentos/pessoa/organizador.pdf');
        $this->assertSame(0, $titular->notificacoes()->count());
    }

    public function test_admin_valida_documentos_cnh_e_opcional_e_antecedente_recebe_validade(): void
    {
        Storage::fake('local');
        $pessoa = $this->createPessoa('voluntario', ['status_validacao' => 'aguardando_validacao']);
        $admin = $this->createPessoa('administrador', [
            'cpf_pessoa' => '00000000073', 'email_pessoa' => 'validador@example.com', 'login_pessoa' => 'validador',
        ]);
        $this->grantPermission($admin, 'usuarios.validar');

        Sanctum::actingAs($pessoa, ['pessoa']);
        $identidade = $this->postJson('/api/v1/pessoas/me/documentos/documento_identidade', [
            'arquivo' => UploadedFile::fake()->create('identidade.pdf', 100, 'application/pdf'),
        ])->assertCreated()->json('data.documento_pessoa_id');
        $antecedentes = $this->postJson('/api/v1/pessoas/me/documentos/antecedentes_criminais', [
            'arquivo' => UploadedFile::fake()->create('antecedentes.pdf', 100, 'application/pdf'),
        ])->assertCreated()->json('data.documento_pessoa_id');

        Sanctum::actingAs($admin, ['pessoa']);
        $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/assumir")->assertOk();
        foreach ([$identidade, $antecedentes] as $id) {
            $this->patchJson("/api/v1/admin/documentos/{$id}/status", ['status' => 'aprovado'])
                ->assertOk();
        }

        $antecedente = DocumentoPessoa::findOrFail($antecedentes);
        $this->assertTrue($antecedente->expires_at->equalTo($antecedente->validated_at->copy()->addMonths(12)));
        $this->assertTrue($antecedente->delete_after->equalTo($antecedente->validated_at->copy()->addDays(30)));

        $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/aprovar")
            ->assertOk()
            ->assertJsonPath('data.status_validacao', 'aprovado');
        $this->assertDatabaseMissing('documento_pessoa', ['pessoa_id' => $pessoa->pessoa_id, 'tipo' => 'cnh']);

        $antecedente->forceFill(['expires_at' => now()->subDay()])->save();
        $this->assertSame('aprovado', $pessoa->fresh()->status_validacao);
    }

    public function test_documento_rejeitado_impede_aprovacao(): void
    {
        $pessoa = $this->createPessoa('voluntario', ['status_validacao' => 'aguardando_validacao']);
        $admin = $this->createPessoa('administrador', [
            'cpf_pessoa' => '00000000074', 'email_pessoa' => 'rejeita@example.com', 'login_pessoa' => 'rejeita',
        ]);
        $this->grantPermission($admin, 'usuarios.validar');

        foreach (['documento_identidade' => 'rejeitado', 'antecedentes_criminais' => 'aprovado'] as $tipo => $status) {
            DocumentoPessoa::create([
                'pessoa_id' => $pessoa->pessoa_id, 'tipo' => $tipo,
                'storage_path' => "documentos/{$tipo}.pdf", 'mime_type' => 'application/pdf',
                'size_bytes' => 100, 'status' => $status, 'ativo' => true, 'uploaded_at' => now(),
            ]);
        }

        Sanctum::actingAs($admin, ['pessoa']);
        $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/assumir")->assertOk();
        $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/aprovar")->assertStatus(409);
    }

    public function test_admin_nao_valida_documento_historico_substituido(): void
    {
        Storage::fake('local');
        $pessoa = $this->createPessoa('voluntario', ['status_validacao' => 'aguardando_validacao']);
        Sanctum::actingAs($pessoa, ['pessoa']);

        $versaoA = $this->postJson('/api/v1/pessoas/me/documentos/documento_identidade', [
            'arquivo' => UploadedFile::fake()->create('identidade-a.pdf', 100, 'application/pdf'),
        ])->assertCreated()->json('data.documento_pessoa_id');
        $versaoB = $this->postJson('/api/v1/pessoas/me/documentos/documento_identidade', [
            'arquivo' => UploadedFile::fake()->create('identidade-b.pdf', 100, 'application/pdf'),
        ])->assertCreated()->json('data.documento_pessoa_id');

        $antes = DocumentoPessoa::findOrFail($versaoA)->getAttributes();
        $admin = $this->createPessoa('administrador', [
            'cpf_pessoa' => '00000000078', 'email_pessoa' => 'historicodoc@example.com', 'login_pessoa' => 'historicodoc',
        ]);
        $this->grantPermission($admin, 'usuarios.validar');
        Sanctum::actingAs($admin, ['pessoa']);

        $this->patchJson("/api/v1/admin/documentos/{$versaoA}/status", ['status' => 'aprovado'])
            ->assertStatus(409);
        $this->assertSame($antes, DocumentoPessoa::findOrFail($versaoA)->getAttributes());
        $this->assertFalse(DocumentoPessoa::findOrFail($versaoA)->ativo);

        $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/assumir")->assertOk();
        $this->patchJson("/api/v1/admin/documentos/{$versaoB}/status", ['status' => 'aprovado'])
            ->assertOk();
        $this->assertDatabaseHas('documento_pessoa', [
            'documento_pessoa_id' => $versaoB,
            'ativo' => true,
            'status' => 'aprovado',
        ]);
    }

    public function test_documento_nao_pode_ser_analisado_sem_assumir_pessoa_aguardando(): void
    {
        $pessoa = $this->createPessoa('voluntario', ['status_validacao' => 'aguardando_validacao']);
        $documento = $this->documentoAtivo($pessoa->pessoa_id);
        $admin = $this->adminAutorizado('92');
        Sanctum::actingAs($admin, ['pessoa']);

        $this->patchJson("/api/v1/admin/documentos/{$documento->documento_pessoa_id}/status", [
            'status' => 'aprovado',
        ])->assertStatus(409);
    }

    public function test_documento_respeita_dono_do_lock_de_analise(): void
    {
        $pessoa = $this->createPessoa('voluntario', ['status_validacao' => 'aguardando_validacao']);
        $documento = $this->documentoAtivo($pessoa->pessoa_id);
        $adminA = $this->adminAutorizado('93');
        $adminB = $this->adminAutorizado('94');

        Sanctum::actingAs($adminA, ['pessoa']);
        $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/assumir")->assertOk();

        Sanctum::actingAs($adminB, ['pessoa']);
        $this->patchJson("/api/v1/admin/documentos/{$documento->documento_pessoa_id}/status", [
            'status' => 'aprovado',
        ])->assertStatus(409);

        Sanctum::actingAs($adminA, ['pessoa']);
        $this->patchJson("/api/v1/admin/documentos/{$documento->documento_pessoa_id}/status", [
            'status' => 'aprovado',
        ])->assertOk();
    }

    public function test_lock_expirado_exige_nova_assuncao_para_analisar_documento(): void
    {
        Carbon::setTestNow('2026-10-12 10:00:00');
        $pessoa = $this->createPessoa('voluntario', ['status_validacao' => 'aguardando_validacao']);
        $documento = $this->documentoAtivo($pessoa->pessoa_id);
        $adminA = $this->adminAutorizado('95');
        $adminB = $this->adminAutorizado('96');

        Sanctum::actingAs($adminA, ['pessoa']);
        $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/assumir")->assertOk();

        Carbon::setTestNow(now()->addHours(25));
        $this->patchJson("/api/v1/admin/documentos/{$documento->documento_pessoa_id}/status", [
            'status' => 'aprovado',
        ])->assertStatus(409);

        Sanctum::actingAs($adminB, ['pessoa']);
        $this->postJson("/api/v1/admin/validacoes-pessoa/{$pessoa->pessoa_id}/assumir")->assertOk();
        $this->patchJson("/api/v1/admin/documentos/{$documento->documento_pessoa_id}/status", [
            'status' => 'aprovado',
        ])->assertOk();
    }

    public function test_pessoa_aprovada_pode_renovar_antecedentes_sem_reabrir_workflow(): void
    {
        Storage::fake('local');
        Carbon::setTestNow('2026-10-12 12:00:00');
        $pessoa = $this->createPessoa('voluntario', ['status_validacao' => 'aprovado']);
        $aprovadoEm = $pessoa->aprovado_em->copy();

        Sanctum::actingAs($pessoa, ['pessoa']);
        $documentoId = $this->postJson('/api/v1/pessoas/me/documentos/antecedentes_criminais', [
            'arquivo' => UploadedFile::fake()->create('antecedentes-renovados.pdf', 100, 'application/pdf'),
        ])->assertCreated()->json('data.documento_pessoa_id');

        $admin = $this->adminAutorizado('97');
        Sanctum::actingAs($admin, ['pessoa']);
        $this->patchJson("/api/v1/admin/documentos/{$documentoId}/status", [
            'status' => 'aprovado',
        ])->assertOk();

        $pessoa->refresh();
        $this->assertSame('aprovado', $pessoa->status_validacao);
        $this->assertTrue($pessoa->aprovado_em->equalTo($aprovadoEm));
        $this->assertNull($pessoa->admin_analisando_id);
        $this->assertNull($pessoa->analise_assumida_em);
    }

    public function test_comando_de_retencao_apaga_so_arquivo_vencido_e_preserva_metadata(): void
    {
        Storage::fake('local');
        $pessoa = $this->createPessoa();
        Storage::disk('local')->put('documentos/futuro.pdf', 'futuro');
        Storage::disk('local')->put('documentos/vencido.pdf', 'vencido');

        $futuro = DocumentoPessoa::create([
            'pessoa_id' => $pessoa->pessoa_id, 'tipo' => 'documento_identidade',
            'storage_path' => 'documentos/futuro.pdf', 'mime_type' => 'application/pdf', 'size_bytes' => 6,
            'status' => 'aprovado', 'ativo' => false, 'uploaded_at' => now(), 'validated_at' => now(),
            'delete_after' => now()->addDay(),
        ]);
        $vencido = DocumentoPessoa::create([
            'pessoa_id' => $pessoa->pessoa_id, 'tipo' => 'antecedentes_criminais',
            'storage_path' => 'documentos/vencido.pdf', 'mime_type' => 'application/pdf', 'size_bytes' => 7,
            'status' => 'aprovado', 'ativo' => true, 'uploaded_at' => now(), 'validated_at' => now()->subDays(31),
            'delete_after' => now()->subDay(),
        ]);

        $this->artisan('documentos:excluir-retidos')->assertSuccessful();
        Storage::disk('local')->assertExists('documentos/futuro.pdf');
        Storage::disk('local')->assertMissing('documentos/vencido.pdf');
        $this->assertNotNull($vencido->refresh()->arquivo_excluido_em);
        $this->assertNull($vencido->storage_path);
        $this->assertDatabaseHas('documento_pessoa', ['documento_pessoa_id' => $vencido->documento_pessoa_id]);
        $this->assertNotNull($futuro->refresh()->storage_path);

        $this->artisan('documentos:excluir-retidos')->assertSuccessful();
        $this->assertDatabaseHas('documento_pessoa', ['documento_pessoa_id' => $vencido->documento_pessoa_id]);
    }

    private function adminAutorizado(string $suffix)
    {
        $admin = $this->createPessoa('administrador', [
            'cpf_pessoa' => str_pad($suffix, 11, '0', STR_PAD_LEFT),
            'email_pessoa' => "admindoc{$suffix}@example.com",
            'login_pessoa' => "admindoc{$suffix}",
        ]);
        $this->grantPermission($admin, 'usuarios.validar');

        return $admin;
    }

    private function documentoAtivo(int $pessoaId): DocumentoPessoa
    {
        return DocumentoPessoa::create([
            'pessoa_id' => $pessoaId,
            'tipo' => 'documento_identidade',
            'storage_path' => "documentos/{$pessoaId}.pdf",
            'mime_type' => 'application/pdf',
            'size_bytes' => 100,
            'status' => 'pendente',
            'ativo' => true,
            'uploaded_at' => now(),
        ]);
    }

    private function arquivoDe(int $pessoaId, string $path, bool $ativo, bool $existe = true): DocumentoPessoa
    {
        if ($existe) {
            Storage::disk('local')->put($path, 'conteudo privado');
        }

        return DocumentoPessoa::create([
            'pessoa_id' => $pessoaId,
            'tipo' => 'antecedentes_criminais',
            'storage_path' => $path,
            'mime_type' => 'application/pdf',
            'size_bytes' => 16,
            'status' => 'aprovado',
            'ativo' => $ativo,
            'uploaded_at' => now(),
            'validated_at' => now(),
            'expires_at' => now()->addMonths(12),
        ]);
    }
}
