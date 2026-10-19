<?php

namespace Tests\Feature;

use App\Models\DocumentoPessoa;
use App\Services\DocumentoStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RailwayStorageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('railway_private');
        Storage::fake('railway_images');
        foreach (['railway_private', 'railway_images'] as $disk) {
            foreach (['key' => 'synthetic-key', 'secret' => 'synthetic-secret', 'region' => 'synthetic-region',
                'bucket' => 'synthetic-bucket', 'endpoint' => 'https://storage.invalid'] as $field => $value) {
                config()->set("filesystems.disks.{$disk}.{$field}", $value);
            }
        }
    }

    public function test_s3_disks_are_private_and_separated_without_direct_urls(): void
    {
        $documents = config('filesystems.disks.railway_private');
        $images = config('filesystems.disks.railway_images');
        $this->assertSame('s3', $documents['driver']);
        $this->assertSame('private', $documents['root']);
        $this->assertSame('images', $images['root']);
        $this->assertSame('private', $documents['visibility']);
        $this->assertSame('private', $images['visibility']);
        $this->assertTrue($documents['throw']);
        $this->assertTrue($images['throw']);
        $this->assertArrayNotHasKey('url', $documents);
        $this->assertArrayNotHasKey('url', $images);
        $this->assertSame('local', (new DocumentoStorage(app(\App\Services\RailwayStorageConfiguration::class)))->diskFor(new DocumentoPessoa));
    }

    public function test_document_upload_and_owner_download_use_configured_private_fake(): void
    {
        config()->set('filesystems.document_private_disk', 'railway_private');
        $owner = $this->createPessoa();
        Sanctum::actingAs($owner, ['pessoa']);
        $upload = $this->postJson('/api/v1/pessoas/me/documentos/documento_identidade', [
            'arquivo' => UploadedFile::fake()->create('private.pdf', 1, 'application/pdf'),
        ])->assertCreated();
        $document = DocumentoPessoa::findOrFail($upload->json('data.documento_pessoa_id'));
        $this->assertSame('railway_private', $document->storage_disk);
        Storage::disk('railway_private')->assertExists($document->storage_path);
        Storage::disk('railway_images')->assertMissing($document->storage_path);
        $this->get("/api/v1/documentos/{$document->getKey()}/download")->assertOk();

        $route = "/api/v1/documentos/{$document->getKey()}/download";
        $other = $this->createPessoa('voluntario', [
            'cpf_pessoa' => '00000008111', 'email_pessoa' => 's3-other@example.test', 'login_pessoa' => 's3-other',
        ]);
        $this->grantPermission($other, 'usuarios.validar');
        Sanctum::actingAs($other, ['pessoa']);
        $this->getJson($route)->assertForbidden();
        Sanctum::actingAs($this->createOng('ativo'), ['ong']);
        $this->getJson($route)->assertForbidden();
        $this->app['auth']->forgetGuards();
        $this->getJson($route)->assertUnauthorized();

        $admin = $this->createPessoa('administrador', [
            'cpf_pessoa' => '00000008112', 'email_pessoa' => 's3-admin@example.test', 'login_pessoa' => 's3-admin',
        ]);
        $this->grantPermission($admin, 'usuarios.validar');
        Sanctum::actingAs($admin, ['pessoa']);
        $this->get($route)->assertOk();
        $master = $this->createPessoa('administrador', [
            'cpf_pessoa' => '00000008113', 'email_pessoa' => 's3-master@example.test',
            'login_pessoa' => 's3-master', 'admin_master' => true,
        ]);
        Sanctum::actingAs($master, ['pessoa']);
        $this->get($route)->assertOk();
    }

    public function test_missing_or_insecure_s3_configuration_fails_before_upload(): void
    {
        config()->set('filesystems.document_private_disk', 'railway_private');
        $owner = $this->createPessoa();
        Sanctum::actingAs($owner, ['pessoa']);
        config()->set('filesystems.disks.railway_private.bucket', null);
        $this->postJson('/api/v1/pessoas/me/documentos/documento_identidade', [
            'arquivo' => UploadedFile::fake()->create('private.pdf', 1, 'application/pdf'),
        ])->assertStatus(503)->assertDontSee('synthetic-secret');
        $this->assertSame([], Storage::disk('railway_private')->allFiles());
        config()->set('filesystems.disks.railway_private.bucket', 'synthetic-bucket');
        config()->set('filesystems.disks.railway_private.endpoint', 'http://storage.invalid');
        $this->postJson('/api/v1/pessoas/me/documentos/documento_identidade', [
            'arquivo' => UploadedFile::fake()->create('private.pdf', 1, 'application/pdf'),
        ])->assertStatus(503);
        $this->assertSame(0, DocumentoPessoa::count());
    }

    public function test_legacy_gcs_document_reference_is_not_silently_remapped(): void
    {
        $owner = $this->createPessoa();
        $document = DocumentoPessoa::create([
            'pessoa_id' => $owner->pessoa_id, 'tipo' => 'documento_identidade',
            'storage_disk' => 'firebase_private', 'storage_path' => 'documentos/legacy.pdf',
            'mime_type' => 'application/pdf', 'size_bytes' => 6,
            'status' => 'pendente', 'ativo' => true, 'uploaded_at' => now(),
        ]);
        Sanctum::actingAs($owner, ['pessoa']);
        $this->getJson("/api/v1/documentos/{$document->getKey()}/download")->assertStatus(503);
        $this->assertSame('firebase_private', $document->fresh()->storage_disk);
        $this->assertSame('documentos/legacy.pdf', $document->fresh()->storage_path);
        $this->assertSame([], Storage::disk('railway_private')->allFiles());
    }

    public function test_image_uses_separate_private_fake_and_only_api_url_is_public(): void
    {
        config()->set('filesystems.image_storage_disk', 'railway_images');
        $owner = $this->createPessoa();
        Sanctum::actingAs($owner, ['pessoa']);
        $url = "/api/v1/pessoas/{$owner->pessoa_id}/imagem";
        $first = $this->post($url, ['imagem' => UploadedFile::fake()->image('foto.png', 16, 16)],
            ['Accept' => 'application/json'])->assertCreated();
        $old = $owner->fresh()->managed_image_path;
        $this->assertSame('railway_images', $owner->fresh()->managed_image_disk);
        $this->assertMatchesRegularExpression('~^pessoas/[A-Za-z0-9]{40}\.png$~', $old);
        $this->assertStringContainsString($url.'?v=', $first->json('data.url'));
        $first->assertDontSee($old)->assertDontSee('synthetic-secret')->assertDontSee('storage.invalid');
        Storage::disk('railway_private')->assertMissing($old);
        $image = $this->get($url)->assertOk();
        $this->withHeader('If-None-Match', $image->headers->get('ETag'))->get($url)->assertStatus(304);
        $this->post($url, ['imagem' => UploadedFile::fake()->image('nova.png', 16, 16)],
            ['Accept' => 'application/json'])->assertOk();
        Storage::disk('railway_images')->assertMissing($old);
        $this->deleteJson($url)->assertOk();
        $this->get($url)->assertNotFound();
    }

    public function test_legacy_gcs_image_reference_is_preserved_if_bytes_are_unavailable(): void
    {
        $owner = $this->createPessoa();
        $owner->forceFill([
            'managed_image_disk' => 'firebase_images',
            'managed_image_path' => 'pessoas/'.str_repeat('A', 40).'.png',
            'managed_image_mime' => 'image/png', 'managed_image_size' => 100,
        ])->save();
        $this->getJson("/api/v1/pessoas/{$owner->pessoa_id}/imagem")->assertStatus(503);
        $this->assertSame('firebase_images', $owner->fresh()->managed_image_disk);
    }
}
