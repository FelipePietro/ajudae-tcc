<?php

namespace Tests\Feature;

use App\Models\AdminLog;
use App\Models\DocumentoPessoa;
use App\Services\DocumentoStorage;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class DocumentoStorageLgpdTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_em_disk_configurado_usa_chave_opaca_e_sem_vazamento(): void
    {
        Storage::fake('railway_private');
        config()->set('filesystems.document_private_disk', 'railway_private');
        $this->configureRailwayDisk('railway_private');
        $pessoa = $this->createPessoa();
        Sanctum::actingAs($pessoa, ['pessoa']);

        $response = $this->postJson('/api/v1/pessoas/me/documentos/antecedentes_criminais', [
            'arquivo' => UploadedFile::fake()->create('cpf-52998224725.pdf', 10, 'application/pdf'),
        ])->assertCreated();
        $documento = DocumentoPessoa::findOrFail($response->json('data.documento_pessoa_id'));
        $this->assertSame('railway_private', $documento->storage_disk);
        $this->assertMatchesRegularExpression('/^documentos\/[A-Za-z0-9]{40}\.pdf$/', $documento->storage_path);
        $this->assertSame(64, strlen($documento->sha256));
        Storage::disk('railway_private')->assertExists($documento->storage_path);
        $response->assertDontSee($documento->storage_path)
            ->assertDontSee('52998224725')->assertDontSee('railway_private');

        $this->postJson('/api/v1/pessoas/me/documentos/antecedentes_criminais', [
            'arquivo' => UploadedFile::fake()->create('invalido.svg', 1, 'image/svg+xml'),
        ])->assertUnprocessable();
        $this->postJson('/api/v1/pessoas/me/documentos/antecedentes_criminais', [
            'arquivo' => UploadedFile::fake()->create('grande.pdf', 10241, 'application/pdf'),
        ])->assertUnprocessable();
        $this->assertSame(1, DocumentoPessoa::count());
        $this->assertCount(1, Storage::disk('railway_private')->allFiles());
    }

    public function test_falha_de_storage_nao_cria_metadado_e_falha_de_banco_limpa_apenas_objeto_novo(): void
    {
        $pessoa = $this->createPessoa();
        Sanctum::actingAs($pessoa, ['pessoa']);
        $disk = Mockery::mock(FilesystemAdapter::class);
        $disk->shouldReceive('putFileAs')->once()->andReturn(false);
        Storage::set('local', $disk);
        $this->postJson('/api/v1/pessoas/me/documentos/documento_identidade', [
            'arquivo' => UploadedFile::fake()->create('falha.pdf', 1, 'application/pdf'),
        ])->assertStatus(500);
        $this->assertSame(0, DocumentoPessoa::count());

        Storage::fake('local');
        Storage::disk('local')->put('documentos/historico.pdf', 'historico');
        $historico = DocumentoPessoa::create([
            'pessoa_id' => $pessoa->pessoa_id, 'tipo' => 'documento_identidade',
            'storage_path' => 'documentos/historico.pdf', 'mime_type' => 'application/pdf',
            'size_bytes' => 9, 'status' => 'aprovado', 'ativo' => true, 'uploaded_at' => now(),
        ]);
        DocumentoPessoa::creating(fn () => throw new RuntimeException('Falha de persistência simulada.'));
        $this->postJson('/api/v1/pessoas/me/documentos/documento_identidade', [
            'arquivo' => UploadedFile::fake()->create('novo.pdf', 1, 'application/pdf'),
        ])->assertStatus(500);
        $this->assertSame(1, DocumentoPessoa::count());
        $this->assertTrue($historico->fresh()->ativo);
        Storage::disk('local')->assertExists('documentos/historico.pdf');
        $this->assertSame(['documentos/historico.pdf'], Storage::disk('local')->allFiles());
    }

    public function test_expurgo_tem_dry_run_ignora_pendente_e_retem_referencia_se_storage_falha(): void
    {
        Storage::fake('local');
        $pessoa = $this->createPessoa();
        Storage::disk('local')->put('documentos/validado.pdf', 'validado');
        Storage::disk('local')->put('documentos/pendente.pdf', 'pendente');
        $validado = $this->documento($pessoa->pessoa_id, 'documentos/validado.pdf', 'aprovado');
        $pendente = $this->documento($pessoa->pessoa_id, 'documentos/pendente.pdf', 'pendente');

        $this->artisan('documentos:excluir-retidos --dry-run')->assertSuccessful();
        Storage::disk('local')->assertExists('documentos/validado.pdf');
        $this->assertNotNull($validado->fresh()->storage_path);

        $disk = Mockery::mock(FilesystemAdapter::class);
        $disk->shouldReceive('exists')->once()->with('documentos/validado.pdf')->andReturn(true);
        $disk->shouldReceive('delete')->once()->with('documentos/validado.pdf')->andReturn(false);
        Storage::set('local', $disk);
        $this->artisan('documentos:excluir-retidos')->assertFailed();
        $this->assertSame('documentos/validado.pdf', $validado->fresh()->storage_path);
        $this->assertSame('documentos/pendente.pdf', $pendente->fresh()->storage_path);

        Storage::fake('local');
        Storage::disk('local')->put('documentos/validado.pdf', 'validado');
        Storage::disk('local')->put('documentos/pendente.pdf', 'pendente');
        $this->artisan('documentos:excluir-retidos')->assertSuccessful();
        $this->artisan('documentos:excluir-retidos')->assertSuccessful();
        $this->assertNull($validado->fresh()->storage_path);
        $this->assertNotNull($validado->fresh()->arquivo_excluido_em);
        $this->assertSame('aprovado', $validado->fresh()->status);
        Storage::disk('local')->assertExists('documentos/pendente.pdf');
    }

    public function test_download_de_terceiro_audita_admin_sem_vazar_chave_e_nega_antes_do_storage(): void
    {
        Storage::fake('local');
        $titular = $this->createPessoa();
        $outro = $this->createPessoa('voluntario', [
            'cpf_pessoa' => '00000009011', 'email_pessoa' => 'outro-storage@example.com',
            'login_pessoa' => 'outro-storage',
        ]);
        $admin = $this->createPessoa('administrador', [
            'cpf_pessoa' => '00000009012', 'email_pessoa' => 'admin-storage@example.com',
            'login_pessoa' => 'admin-storage',
        ]);
        $this->grantPermission($admin, 'usuarios.validar');
        $this->grantPermission($outro, 'usuarios.validar');
        Storage::disk('local')->put('documentos/privado.pdf', 'conteudo');
        $documento = $this->documento($titular->pessoa_id, 'documentos/privado.pdf', 'aprovado');
        $rota = "/api/v1/documentos/{$documento->getKey()}/download";

        $disk = Mockery::mock(FilesystemAdapter::class);
        $disk->shouldNotReceive('exists');
        Storage::set('local', $disk);
        Sanctum::actingAs($outro, ['pessoa']);
        $this->getJson($rota)->assertForbidden()->assertDontSee('documentos/privado.pdf');
        $this->assertSame(0, AdminLog::count());

        Storage::fake('local');
        Storage::disk('local')->put('documentos/privado.pdf', 'conteudo');
        Sanctum::actingAs($admin, ['pessoa']);
        $response = $this->get($rota)->assertOk();
        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $log = AdminLog::firstOrFail();
        $this->assertSame('documento.download', $log->acao);
        $this->assertSame($admin->pessoa_id, $log->pessoa_id);
        $this->assertSame($documento->getKey(), $log->recurso_id);
        $this->assertSame('entrega_iniciada', $log->detalhes['resultado']);
        $this->assertStringNotContainsString('documentos/', json_encode($log->detalhes));
    }

    public function test_chaves_perigosas_sao_rejeitadas_sem_acesso_ao_storage(): void
    {
        $store = app(DocumentoStorage::class);
        $this->assertFalse($store->safePath('documentos/../.env'));
        $this->assertFalse($store->safePath('C:\\segredo.pdf'));
        $this->assertFalse($store->safePath('/documentos/segredo.pdf'));
        $this->assertTrue($store->safePath('documentos/arquivo.pdf'));
    }

    private function documento(int $pessoaId, string $path, string $status): DocumentoPessoa
    {
        return DocumentoPessoa::create([
            'pessoa_id' => $pessoaId, 'tipo' => 'antecedentes_criminais',
            'storage_path' => $path, 'mime_type' => 'application/pdf', 'size_bytes' => 8,
            'status' => $status, 'ativo' => true, 'uploaded_at' => now(),
            'validated_at' => $status === 'aprovado' ? now()->subDays(31) : null,
            'delete_after' => now()->subDay(),
        ]);
    }

    private function configureRailwayDisk(string $disk): void
    {
        foreach (['key' => 'synthetic-key', 'secret' => 'synthetic-secret', 'region' => 'synthetic-region',
            'bucket' => 'synthetic-bucket', 'endpoint' => 'https://storage.invalid'] as $field => $value) {
            config()->set("filesystems.disks.{$disk}.{$field}", $value);
        }
    }
}
