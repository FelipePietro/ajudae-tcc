<?php

namespace Tests\Feature;

use App\Models\AdminLog;
use App\Models\Ong;
use App\Models\Pessoa;
use App\Services\DocumentoStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

final class Bloco9WebOngRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        if (! config('identity.enabled')) {
            $this->markTestSkipped('Execute com AJUDAE_WEB_AUTH=true.');
        }
        config(['filesystems.document_private_disk' => 'local']);
        Storage::fake('local');
    }

    public function test_visitante_abre_o_formulario_institucional_multipart(): void
    {
        $this->get('/cadastro/ong')->assertOk()->assertViewIs('ong.cadastro_ong')
            ->assertHeaderContains('Cache-Control', 'no-store')
            ->assertSee('action="'.route('identity.ong.registration').'"', false)
            ->assertSee('data-web-registration', false)
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('name="documento_identidade"', false)
            ->assertSee('name="senha_ong_confirmation"', false)
            ->assertSee('name="aceitou_termos"', false)
            ->assertDontSee('data-demo-seed', false)
            ->assertDontSee('name="rg_responsavel_ong_link"', false);
    }

    public function test_contas_autenticadas_sao_redirecionadas_e_nao_podem_cadastrar_ong(): void
    {
        foreach ([$this->createPessoa(), $this->createOng()] as $account) {
            $this->sessionFor($account);

            $this->get('/cadastro/ong')->assertRedirect(route('conta'))
                ->assertHeaderContains('Cache-Control', 'no-store');
            $this->post('/cadastro/ong', $this->payload(), ['Accept' => 'application/json'])
                ->assertForbidden();
        }

        $this->assertDatabaseCount('ong', 1);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_campos_institucionais_invalidos_nao_criam_conta_nem_arquivo(): void
    {
        $invalid = array_replace($this->payload(), [
            'cnpj_ong' => '11111111111111',
            'cpf_responsavel_ong' => '11111111111',
            'documento_identidade' => UploadedFile::fake()->create('identidade.txt', 1, 'text/plain'),
        ]);
        $this->post('/cadastro/ong', $invalid, ['Accept' => 'application/json'])
            ->assertUnprocessable()->assertJsonValidationErrors(['cnpj_ong', 'cpf_responsavel_ong', 'documento_identidade']);
        $oversized = UploadedFile::fake()->createWithContent(
            'identidade.pdf', file_get_contents(resource_path('fixtures/admin-documents/certidao.pdf')),
        )->size(10241);
        $this->post('/cadastro/ong', array_replace($this->payload(), ['documento_identidade' => $oversized]), ['Accept' => 'application/json'])
            ->assertUnprocessable()->assertJsonValidationErrors('documento_identidade');
        $this->assertDatabaseEmpty('ong');
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_cadastro_pendente_com_documento_privado_e_aprovacao_auditada(): void
    {
        $this->post('/cadastro/ong', $this->payload(), ['Accept' => 'application/json'])
            ->assertCreated()->assertJsonPath('data.ong.status_ong', 'aguardando aprovação')
            ->assertJsonMissingPath('data.token')->assertJsonMissingPath('data.ong.rg_responsavel_ong_link')
            ->assertSessionMissing('web_identity');

        $registered = Ong::firstOrFail();
        $storedPath = $registered->rg_responsavel_ong_link;
        Storage::disk('local')->assertExists($storedPath);
        $this->assertSame('application/pdf', $registered->rg_responsavel_mime_type);
        $this->assertSame(hash('sha256', Storage::disk('local')->get($storedPath)), $registered->rg_responsavel_sha256);
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertDatabaseCount('assinatura', 1);
        $this->assertDatabaseHas('admin_log', ['acao' => 'ong.cadastrada', 'recurso_id' => $registered->getKey()]);

        $document = route('identity.ong.document', $registered);
        $queue = '/admin/validacoes-ong/dados';
        $status = '/admin/validacoes-ong/'.$registered->getKey().'/status';
        $this->getJson('/storage/'.$storedPath)->assertForbidden()->assertDontSee($storedPath);
        $this->getJson('/api/v1/ongs/'.$registered->getKey())->assertOk()->assertDontSee($storedPath);
        $this->getJson($document)->assertUnauthorized();

        $this->sessionFor($registered);
        $this->get($document)->assertDownload('identidade-responsavel.pdf')->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->patchJson($status, ['status_ong' => 'ativo'])->assertForbidden();

        $other = $this->createOng('ativo', [
            'login_ong' => 'outra.instituicao', 'email_ong' => 'outra@example.test', 'cnpj_ong' => '12345678000195',
        ]);
        $this->sessionFor($other);
        $this->getJson($document)->assertForbidden();

        $admin = $this->createPessoa('administrador');
        $this->sessionFor($admin);
        $this->getJson($queue.'?capacidade=ongs.validar')->assertForbidden();
        $this->getJson($document)->assertForbidden();

        $this->grantPermission($admin, 'ongs.validar');
        Auth::forgetGuards();
        $this->getJson($queue)->assertOk()->assertDontSee($storedPath)
            ->assertJsonPath('data.0.documento_identidade.mime_type', 'application/pdf');
        $this->get($document)->assertDownload('identidade-responsavel.pdf')->assertHeaderContains('Cache-Control', 'no-store');
        $this->patchJson($status, ['status_ong' => 'ativo'])->assertOk()->assertJsonPath('data.status_ong', 'ativo');
        $this->assertDatabaseHas('admin_log', ['acao' => 'ong.documento_identidade.download', 'pessoa_id' => $admin->getKey()]);
        $this->assertDatabaseHas('admin_log', ['acao' => 'ong.status_atualizado', 'pessoa_id' => $admin->getKey()]);

        $this->travel(30)->days();
        $this->sessionFor($admin->fresh());
        $this->getJson($document)->assertNotFound();
        Storage::disk('local')->assertExists($storedPath);
    }

    public function test_links_nao_substituem_documento_e_falha_parcial_remove_o_objeto(): void
    {
        $invalid = $this->ongPayload(['rg_responsavel_ong_link' => 'https://example.test/rg.pdf']);
        $this->postJson('/cadastro/ong', $invalid)->assertUnprocessable()
            ->assertJsonValidationErrors(['documento_identidade', 'rg_responsavel_ong_link']);
        $this->postJson('/api/v1/ongs/register', $invalid)->assertUnprocessable();
        $this->assertDatabaseEmpty('ong');
        $this->assertSame([], Storage::disk('local')->allFiles());

        AdminLog::creating(function () {
            throw new RuntimeException('Falha sintética na auditoria.');
        });
        $this->post('/cadastro/ong', $this->payload(), ['Accept' => 'application/json'])->assertServerError();
        $this->assertDatabaseEmpty('ong');
        $this->assertDatabaseEmpty('assinatura');
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_storage_indisponivel_nao_persiste_conta(): void
    {
        $this->mock(DocumentoStorage::class)->shouldReceive('store')->once()->andThrow(new RuntimeException);
        $this->post('/cadastro/ong', $this->payload(), ['Accept' => 'application/json'])->assertServiceUnavailable();
        $this->assertDatabaseEmpty('ong');
    }

    private function payload(): array
    {
        $payload = $this->ongPayload(['cnpj_ong' => '11.222.333/0001-81', 'cpf_responsavel_ong' => '111.444.777-35']);
        unset($payload['rg_responsavel_ong_link'], $payload['site_url_ong'], $payload['pfp_ong_link']);
        $payload['documento_identidade'] = UploadedFile::fake()->createWithContent(
            'identidade.pdf', file_get_contents(resource_path('fixtures/admin-documents/certidao.pdf')),
        );

        return $payload;
    }

    private function sessionFor(Pessoa|Ong $account): void
    {
        $account->refresh();
        $this->flushSession();
        Auth::forgetGuards();
        $type = $account instanceof Pessoa ? 'pessoa' : 'ong';
        $this->withSession([
            Auth::guard('web_'.$type)->getName() => $account->getKey(),
            'web_identity' => [
                'type' => $type, 'id' => $account->getKey(), 'version' => $account->auth_version,
                'started_at' => now()->timestamp, 'last_activity' => now()->timestamp,
            ],
        ]);
    }
}
