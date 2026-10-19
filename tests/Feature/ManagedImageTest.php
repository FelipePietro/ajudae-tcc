<?php

namespace Tests\Feature;

use App\Models\CatEvento;
use App\Models\Evento;
use App\Models\Pessoa;
use App\Models\VinculoOngOrganizador;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Tests\TestCase;

class ManagedImageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('filesystems.image_storage_disk', 'image_local');
        Storage::fake('image_local');
        Storage::fake('railway_private');
    }

    private function event(?int $ongId, ?int $pessoaId, string $status = 'ativo'): Evento
    {
        $category = CatEvento::firstOrCreate(['nome_categoria' => 'Educação']);
        $event = new Evento([
            'nm_evento' => 'Oficina', 'descricao_evento' => 'Oficina comunitária',
            'vagas_evento' => 10, 'modalidade_evento' => 'online',
            'imagem_evento_link' => 'https://example.test/banner.jpg',
            'cat_evento_id' => $category->cat_evento_id, 'status_evento' => $status,
        ]);
        $event->forceFill(['ong_id' => $ongId, 'pessoa_id' => $pessoaId])->save();

        return $event;
    }

    private function image(string $type = 'png'): UploadedFile
    {
        return UploadedFile::fake()->image("imagem.{$type}", 50, 50);
    }

    public function test_each_owner_uploads_valid_formats_and_public_response_hides_internal_keys(): void
    {
        $person = $this->createPessoa();
        $ong = $this->createOng('ativo', ['cnpj_ong' => '11222333000181']);
        $event = $this->event($ong->ong_id, null);
        foreach ([
            [$person, 'pessoa', "/api/v1/pessoas/{$person->pessoa_id}/imagem"],
            [$ong, 'ong', "/api/v1/ongs/{$ong->ong_id}/imagem"],
            [$ong, 'ong', "/api/v1/eventos/{$event->evento_id}/imagem"],
        ] as [$actor, $ability, $url]) {
            foreach (['jpg', 'png', 'webp'] as $index => $format) {
                Sanctum::actingAs($actor, [$ability]);
                $result = $this->post($url, ['imagem' => $this->image($format)], ['Accept' => 'application/json'])
                    ->assertStatus($index === 0 ? 201 : 200)->assertJsonPath('success', true);
                $this->assertStringContainsString($url.'?v=', $result->json('data.url'));
                $this->assertArrayNotHasKey('storage_path', $result->json('data'));
                $this->assertArrayNotHasKey('storage_disk', $result->json('data'));
                $this->get($url)->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff')
                    ->assertHeader('Content-Type', $result->json('data.mime_type'));
            }
        }
    }

    public function test_person_photo_rejects_anonymous_and_all_other_actor_types_before_storage(): void
    {
        $owner = $this->createPessoa();
        $url = "/api/v1/pessoas/{$owner->pessoa_id}/imagem";
        $this->post($url, ['imagem' => $this->image()], ['Accept' => 'application/json'])->assertUnauthorized();
        $other = $this->createPessoa('voluntario', [
            'cpf_pessoa' => '11144477735', 'email_pessoa' => 'outro@example.test', 'login_pessoa' => 'outro',
        ]);
        $this->grantPermission($other, 'usuarios.validar');
        $organizer = $this->createPessoa('organizador', [
            'cpf_pessoa' => '39053344705', 'email_pessoa' => 'org@example.test', 'login_pessoa' => 'org',
        ]);
        foreach ([$other, $organizer, $this->createOng('ativo')] as $actor) {
            Sanctum::actingAs($actor, $actor instanceof Pessoa ? ['pessoa'] : ['ong']);
            $this->post($url, ['imagem' => $this->image()], ['Accept' => 'application/json'])->assertForbidden();
        }
        $this->assertSame([], Storage::disk('image_local')->allFiles());
    }

    public function test_ong_and_event_authorization_and_terminal_states(): void
    {
        $ong = $this->createOng('ativo');
        $other = $this->createOng('ativo', [
            'cnpj_ong' => '12345678000195', 'login_ong' => 'outra', 'email_ong' => 'outra@example.test',
            'cpf_responsavel_ong' => '52998224725',
        ]);
        $person = $this->createPessoa('organizador');
        $event = $this->event($ong->ong_id, null);
        Sanctum::actingAs($other, ['ong']);
        $this->post("/api/v1/ongs/{$ong->ong_id}/imagem", ['imagem' => $this->image()], ['Accept' => 'application/json'])->assertForbidden();
        $this->post("/api/v1/eventos/{$event->evento_id}/imagem", ['imagem' => $this->image()], ['Accept' => 'application/json'])->assertForbidden();
        Sanctum::actingAs($person, ['pessoa']);
        $this->post("/api/v1/ongs/{$ong->ong_id}/imagem", ['imagem' => $this->image()], ['Accept' => 'application/json'])->assertForbidden();
        $this->post("/api/v1/eventos/{$event->evento_id}/imagem", ['imagem' => $this->image()], ['Accept' => 'application/json'])->assertForbidden();
        $own = $this->event(null, $person->pessoa_id);
        $this->post("/api/v1/eventos/{$own->evento_id}/imagem", ['imagem' => $this->image()], ['Accept' => 'application/json'])->assertCreated();
        Sanctum::actingAs($ong, ['ong']);
        $this->post("/api/v1/ongs/{$ong->ong_id}/imagem", ['imagem' => $this->image()], ['Accept' => 'application/json'])->assertCreated();
        foreach (['cancelado', 'finalizado'] as $status) {
            $event->forceFill(['status_evento' => $status])->save();
            $this->post("/api/v1/eventos/{$event->evento_id}/imagem", ['imagem' => $this->image()], ['Accept' => 'application/json'])->assertStatus(409);
        }
    }

    public function test_only_named_operational_organizer_can_change_ong_event_banner_without_reapproval(): void
    {
        $ong = $this->createOng('ativo');
        $manager = $this->createPessoa('organizador');
        $colleague = $this->createPessoa('organizador', [
            'cpf_pessoa' => '11144477735', 'email_pessoa' => 'colega@example.test', 'login_pessoa' => 'colega',
        ]);
        foreach ([$manager, $colleague] as $person) {
            VinculoOngOrganizador::create([
                'ong_id' => $ong->ong_id, 'pessoa_id' => $person->pessoa_id,
                'status' => 'ativo', 'convidado_em' => now(), 'respondido_em' => now(),
            ]);
        }
        $event = $this->event($ong->ong_id, null);
        $event->forceFill(['responsavel_pessoa_id' => $manager->pessoa_id])->save();
        $url = "/api/v1/eventos/{$event->evento_id}/imagem";
        Sanctum::actingAs($colleague, ['pessoa']);
        $this->post($url, ['imagem' => $this->image()], ['Accept' => 'application/json'])->assertForbidden();
        $this->assertSame([], Storage::disk('image_local')->allFiles());
        Sanctum::actingAs($manager, ['pessoa']);
        $this->post($url, ['imagem' => $this->image()], ['Accept' => 'application/json'])->assertCreated();
        $this->assertSame('ativo', $event->fresh()->status_evento);
    }

    public function test_invalid_or_missing_images_never_write(): void
    {
        $person = $this->createPessoa();
        Sanctum::actingAs($person, ['pessoa']);
        $url = "/api/v1/pessoas/{$person->pessoa_id}/imagem";
        $png = $this->image('png');
        foreach ([
            UploadedFile::fake()->createWithContent('fake.jpg', 'not an image'),
            UploadedFile::fake()->createWithContent('svg.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>'),
            UploadedFile::fake()->createWithContent('bad.png', 'GIF89a'),
            UploadedFile::fake()->image('wrong.gif'),
            UploadedFile::fake()->createWithContent('mismatch.jpg', file_get_contents($png->getRealPath())),
        ] as $file) {
            $this->post($url, ['imagem' => $file], ['Accept' => 'application/json'])->assertUnprocessable();
        }
        $this->post($url, [], ['Accept' => 'application/json'])->assertUnprocessable();
        $this->post($url, ['imagem' => UploadedFile::fake()->create('large.png', 5121, 'image/png')], ['Accept' => 'application/json'])->assertUnprocessable();
        $this->assertSame([], Storage::disk('image_local')->allFiles());
    }

    public function test_replacement_removal_and_legacy_link_behavior(): void
    {
        $person = $this->createPessoa();
        Sanctum::actingAs($person, ['pessoa']);
        $url = "/api/v1/pessoas/{$person->pessoa_id}/imagem";
        $this->getJson("/api/v1/pessoas/{$person->pessoa_id}")->assertJsonPath('data.pfp_pessoa_link', 'imagens/maria.jpg');
        $this->post($url, ['imagem' => $this->image()], ['Accept' => 'application/json'])->assertCreated();
        $old = $person->fresh()->managed_image_path;
        $this->assertNull($person->fresh()->pfp_pessoa_link);
        $this->post($url, ['imagem' => $this->image('jpg')], ['Accept' => 'application/json'])->assertOk();
        $new = $person->fresh()->managed_image_path;
        $this->assertNotSame($old, $new);
        Storage::disk('image_local')->assertMissing($old);
        Storage::disk('image_local')->assertExists($new);
        $this->deleteJson($url)->assertOk()->assertJsonPath('data.url', null);
        $this->deleteJson($url)->assertOk()->assertJsonPath('data.url', null);
        Storage::disk('image_local')->assertMissing($new);
        $this->get($url)->assertNotFound();
    }

    public function test_document_disk_is_never_used_for_images_and_private_file_is_not_readable(): void
    {
        $person = $this->createPessoa();
        Sanctum::actingAs($person, ['pessoa']);
        Storage::disk('railway_private')->put('documentos/secret.pdf', 'SECRET');
        $url = "/api/v1/pessoas/{$person->pessoa_id}/imagem";
        $this->post($url, ['imagem' => $this->image()], ['Accept' => 'application/json'])->assertCreated();
        $this->assertSame(['documentos/secret.pdf'], Storage::disk('railway_private')->allFiles());
        $this->assertStringNotContainsString('SECRET', $this->get($url)->assertOk()->getContent());
    }

    public function test_storage_failure_keeps_legacy_reference_and_database_unchanged(): void
    {
        $person = $this->createPessoa();
        Sanctum::actingAs($person, ['pessoa']);
        $disk = Mockery::mock(\Illuminate\Filesystem\FilesystemAdapter::class);
        $disk->shouldReceive('put')->once()->andReturn(false);
        Storage::shouldReceive('disk')->once()->with('image_local')->andReturn($disk);

        $this->post("/api/v1/pessoas/{$person->pessoa_id}/imagem", ['imagem' => $this->image()], ['Accept' => 'application/json'])
            ->assertStatus(500);
        $this->assertNull($person->fresh()->managed_image_path);
        $this->assertSame('imagens/maria.jpg', $person->fresh()->pfp_pessoa_link);
    }

    public function test_database_failure_attempts_to_remove_new_object(): void
    {
        $person = $this->createPessoa();
        Sanctum::actingAs($person, ['pessoa']);
        Pessoa::saving(function (Pessoa $model) {
            if ($model->managed_image_path !== null) {
                throw new \RuntimeException('Falha simulada no banco');
            }
        });

        $this->post("/api/v1/pessoas/{$person->pessoa_id}/imagem", ['imagem' => $this->image()], ['Accept' => 'application/json'])
            ->assertStatus(500);
        $this->assertNull($person->fresh()->managed_image_path);
        $this->assertSame([], Storage::disk('image_local')->allFiles());
        $this->assertSame(0, DB::table('pending_image_deletions')->count());
    }

    public function test_jpeg_exif_gps_is_removed_and_visual_orientation_is_preserved(): void
    {
        $person = $this->createPessoa();
        Sanctum::actingAs($person, ['pessoa']);
        $source = UploadedFile::fake()->image('orientada.jpg', 30, 10);
        $jpeg = file_get_contents($source->getRealPath());
        $tiff = 'II'.pack('vV', 42, 8).pack('v', 2)
            .pack('vvVV', 0x0112, 3, 1, 6)
            .pack('vvVV', 0x8825, 4, 1, 38)
            .pack('V', 0)
            .pack('v', 1).pack('vvV', 1, 2, 2)."N\0\0\0".pack('V', 0);
        $payload = "Exif\0\0".$tiff;
        $withExif = substr($jpeg, 0, 2)."\xff\xe1".pack('n', strlen($payload) + 2).$payload.substr($jpeg, 2);
        $file = UploadedFile::fake()->createWithContent('foto.jpg', $withExif);

        $url = "/api/v1/pessoas/{$person->pessoa_id}/imagem";
        $this->post($url, ['imagem' => $file], ['Accept' => 'application/json'])->assertCreated();
        $clean = $this->get($url)->assertOk()->getContent();
        $this->assertStringNotContainsString('Exif', $clean);
        $this->assertStringNotContainsString('GPS', $clean);
        $this->assertSame(10, getimagesizefromstring($clean)[0]);
        $this->assertSame(30, getimagesizefromstring($clean)[1]);
        $this->assertNotSame($withExif, $clean);
    }

    public function test_png_text_metadata_is_removed_without_losing_image(): void
    {
        $person = $this->createPessoa();
        Sanctum::actingAs($person, ['pessoa']);
        $source = $this->image('png');
        $png = file_get_contents($source->getRealPath());
        $data = "GPS\0synthetic-location";
        $chunk = pack('N', strlen($data)).'tEXt'.$data.hash('crc32b', 'tEXt'.$data, true);
        $withText = substr($png, 0, -12).$chunk.substr($png, -12);
        $url = "/api/v1/pessoas/{$person->pessoa_id}/imagem";
        $this->post($url, ['imagem' => UploadedFile::fake()->createWithContent('foto.png', $withText)], ['Accept' => 'application/json'])->assertCreated();
        $clean = $this->get($url)->assertOk()->getContent();
        $this->assertStringNotContainsString('synthetic-location', $clean);
        $this->assertSame('image/png', getimagesizefromstring($clean)['mime']);
    }

    public function test_webp_xmp_metadata_is_removed_without_losing_image(): void
    {
        $person = $this->createPessoa();
        Sanctum::actingAs($person, ['pessoa']);
        $source = $this->image('webp');
        $webp = file_get_contents($source->getRealPath());
        $metadata = '<x:xmpmeta>synthetic-location</x:xmpmeta>';
        $chunk = 'XMP '.pack('V', strlen($metadata)).$metadata.(strlen($metadata) % 2 ? "\0" : '');
        $withXmp = 'RIFF'.pack('V', strlen($webp) - 8 + strlen($chunk)).substr($webp, 8).$chunk;
        $url = "/api/v1/pessoas/{$person->pessoa_id}/imagem";

        $this->post($url, ['imagem' => UploadedFile::fake()->createWithContent('foto.webp', $withXmp)], ['Accept' => 'application/json'])->assertCreated();
        $clean = $this->get($url)->assertOk()->getContent();
        $this->assertStringNotContainsString('synthetic-location', $clean);
        $this->assertSame('image/webp', getimagesizefromstring($clean)['mime']);
    }

    public function test_image_over_dimension_limit_is_rejected_without_object_or_reference(): void
    {
        $person = $this->createPessoa();
        Sanctum::actingAs($person, ['pessoa']);
        $url = "/api/v1/pessoas/{$person->pessoa_id}/imagem";

        $this->post($url, ['imagem' => UploadedFile::fake()->image('wide.png', 8001, 1)], ['Accept' => 'application/json'])
            ->assertUnprocessable();
        $this->assertNull($person->fresh()->managed_image_path);
        $this->assertSame([], Storage::disk('image_local')->allFiles());
    }

    public function test_png_alpha_is_preserved_after_metadata_removal(): void
    {
        $person = $this->createPessoa();
        Sanctum::actingAs($person, ['pessoa']);
        $image = imagecreatetruecolor(2, 2);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefill($image, 0, 0, $transparent);
        ob_start();
        imagepng($image);
        $png = ob_get_clean();
        imagedestroy($image);
        $data = "Comment\0synthetic-location";
        $chunk = pack('N', strlen($data)).'tEXt'.$data.hash('crc32b', 'tEXt'.$data, true);
        $withText = substr($png, 0, -12).$chunk.substr($png, -12);

        $url = "/api/v1/pessoas/{$person->pessoa_id}/imagem";
        $this->post($url, ['imagem' => UploadedFile::fake()->createWithContent('alpha.png', $withText)], ['Accept' => 'application/json'])->assertCreated();
        $clean = $this->get($url)->assertOk()->getContent();
        $decoded = imagecreatefromstring($clean);
        $this->assertSame(127, imagecolorsforindex($decoded, imagecolorat($decoded, 0, 0))['alpha']);
        $this->assertStringNotContainsString('synthetic-location', $clean);
        imagedestroy($decoded);
    }

    public function test_public_image_etag_supports_304_and_changes_after_replacement(): void
    {
        $person = $this->createPessoa();
        Sanctum::actingAs($person, ['pessoa']);
        $url = "/api/v1/pessoas/{$person->pessoa_id}/imagem";
        $this->post($url, ['imagem' => $this->image()], ['Accept' => 'application/json'])->assertCreated();
        $first = $this->get($url)->assertOk();
        $this->assertStringContainsString('max-age=300', $first->headers->get('Cache-Control'));
        $this->assertStringContainsString('public', $first->headers->get('Cache-Control'));
        $etag = $first->headers->get('ETag');
        $this->assertNotEmpty($etag);
        $this->withHeader('If-None-Match', $etag)->get($url)->assertStatus(304);

        $this->post($url, ['imagem' => $this->image('jpg')], ['Accept' => 'application/json'])->assertOk();
        $second = $this->get($url)->assertOk();
        $this->assertNotSame($etag, $second->headers->get('ETag'));
    }

    public function test_reconciliation_never_deletes_referenced_image_and_can_retry_orphan(): void
    {
        $person = $this->createPessoa();
        Sanctum::actingAs($person, ['pessoa']);
        $url = "/api/v1/pessoas/{$person->pessoa_id}/imagem";
        $this->post($url, ['imagem' => $this->image()], ['Accept' => 'application/json'])->assertCreated();
        $path = $person->fresh()->managed_image_path;
        DB::table('pending_image_deletions')->insert([
            'storage_disk' => 'image_local', 'storage_path' => $path,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->artisan('imagens:reconciliar')->assertSuccessful();
        Storage::disk('image_local')->assertExists($path);
        $this->assertDatabaseHas('pending_image_deletions', ['storage_path' => $path]);

        $this->deleteJson($url)->assertOk();
        $this->artisan('imagens:reconciliar')->assertSuccessful();
        Storage::disk('image_local')->assertMissing($path);
        $this->assertDatabaseMissing('pending_image_deletions', ['storage_path' => $path]);
    }
}
