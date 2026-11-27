<?php

namespace Tests\Feature;

use App\Models\AdminLog;
use App\Models\DocumentoPessoa;
use App\Models\Pessoa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class Bloco9WebDocumentReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! config('identity.enabled')) {
            $this->markTestSkipped('Executar com AJUDAE_WEB_AUTH=true.');
        }

        config(['filesystems.document_private_disk' => 'local']);
        Storage::fake('local');
    }

    public function test_renewal_operator_can_read_private_pdf_and_record_decision(): void
    {
        $person = $this->createPessoa();
        $admin = $this->reviewer('owner');
        $thirdParty = $this->reviewer('third', 'voluntario');
        $unprivileged = $this->reviewer('without', 'administrador', false);
        $pdf = file_get_contents(base_path('resources/fixtures/admin-documents/certidao.pdf'));
        $renewal = $this->document($person, 'antecedentes_criminais', $pdf);
        $identity = $this->document($person, 'documento_identidade', $pdf);
        $historical = $this->document($person, 'antecedentes_criminais', $pdf, ['ativo' => false]);
        $excludedPerson = $this->reviewer('excluded', 'voluntario', false);
        $excludedPerson->forceFill(['exclusao_pendente' => true])->save();
        $excludedDocument = $this->document($excludedPerson, 'antecedentes_criminais', $pdf);
        $path = '/conta/documentos/'.$renewal->getKey().'/download';

        $this->sessionFor($thirdParty);
        $this->getJson($path)->assertForbidden();
        $this->sessionFor($unprivileged);
        $this->getJson($path)->assertForbidden();
        $this->sessionFor($admin);
        foreach ([$identity, $historical, $excludedDocument] as $outsideScope) {
            $this->getJson('/conta/documentos/'.$outsideScope->getKey().'/download')->assertForbidden();
        }

        $this->postJson('/admin/validacoes-pessoa/'.$person->getKey().'/assumir')->assertConflict();
        $this->getJson('/admin/antecedentes/renovacoes')->assertOk()
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.documento_pessoa_id', $renewal->getKey());

        $download = $this->get($path)->assertOk()->assertDownload('antecedentes_criminais.pdf')
            ->assertHeader('Content-Type', 'application/pdf')->assertHeader('Cache-Control', 'no-store, private');
        $this->assertSame($pdf, $download->streamedContent());
        $log = AdminLog::where('acao', 'documento.download')->sole();
        $this->assertSame($admin->getKey(), $log->pessoa_id);
        $this->assertSame($renewal->getKey(), $log->recurso_id);
        $this->assertSame('entrega_iniciada', $log->detalhes['resultado']);
        $this->getJson('/admin/antecedentes/renovacoes')->assertOk()
            ->assertJsonPath('data.data.0.download_url', $path);

        $this->patchJson('/admin/documentos/'.$renewal->getKey().'/status', ['status' => 'aprovado'])->assertOk();
        $this->getJson('/admin/antecedentes/renovacoes')->assertOk()->assertJsonCount(0, 'data.data');
        $this->getJson($path)->assertForbidden();
        $this->assertDatabaseHas('documento_pessoa', [
            'documento_pessoa_id' => $renewal->getKey(), 'status' => 'aprovado',
            'validated_by_admin_id' => $admin->getKey(),
        ]);
        $this->assertSame('aprovado', $person->fresh()->status_validacao);
    }

    public function test_registration_download_is_projected_only_for_current_assignee(): void
    {
        $person = $this->createPessoa();
        $person->forceFill(['status_validacao' => 'aguardando_validacao', 'aprovado_em' => null])->save();
        $document = $this->document($person, 'documento_identidade',
            file_get_contents(base_path('resources/fixtures/admin-documents/certidao.pdf')));
        $admin = $this->reviewer('owner');
        $other = $this->reviewer('other');
        $detail = '/admin/validacoes-pessoa/'.$person->getKey();
        $path = '/conta/documentos/'.$document->getKey().'/download';

        $this->sessionFor($admin);
        $before = $this->getJson($detail)->assertOk();
        $this->getJson($path)->assertForbidden();
        $before->assertJsonPath('data.documentos.0.download_url', null)
            ->assertJsonPath('data.documentos.0.download_requires_assignment', true);
        $this->postJson($detail.'/assumir')->assertOk();
        $this->getJson($detail)->assertOk()->assertJsonPath('data.documentos.0.download_url', $path)
            ->assertJsonPath('data.documentos.0.download_requires_assignment', false);
        $this->get($path)->assertOk();

        $this->sessionFor($other);
        $this->getJson($detail)->assertOk()->assertJsonPath('data.documentos.0.download_url', null)
            ->assertJsonPath('data.documentos.0.download_requires_assignment', true);
        $this->getJson($path)->assertForbidden();

        $person->forceFill(['analise_assumida_em' => now()->subHours(24)])->save();
        $this->sessionFor($admin);
        $this->getJson($detail)->assertOk()->assertJsonPath('data.documentos.0.download_url', null)
            ->assertJsonPath('data.documentos.0.download_requires_assignment', true);
        $this->getJson($path)->assertForbidden();

        $this->sessionFor($other);
        $this->postJson($detail.'/assumir')->assertOk();
        $this->getJson($detail)->assertOk()->assertJsonPath('data.documentos.0.download_url', $path);
        $this->get($path)->assertOk();
    }

    private function document(Pessoa $person, string $type, string $body, array $attributes = []): DocumentoPessoa
    {
        $path = 'documentos/'.bin2hex(random_bytes(16)).'.pdf';
        Storage::disk('local')->put($path, $body);

        return DocumentoPessoa::create([
            'pessoa_id' => $person->getKey(), 'tipo' => $type, 'status' => 'pendente',
            'ativo' => true, 'uploaded_at' => now(), 'storage_disk' => 'local', 'storage_path' => $path,
            'mime_type' => 'application/pdf', 'size_bytes' => strlen($body), ...$attributes,
        ]);
    }

    private function reviewer(string $suffix, string $role = 'administrador', bool $grant = true): Pessoa
    {
        $person = $this->createPessoa($role, [
            'cpf_pessoa' => substr(preg_replace('/[^0-9]/', '', hash('sha256', $suffix)), 0, 11),
            'login_pessoa' => 'qa.revisao.'.$suffix, 'email_pessoa' => 'qa.'.$suffix.'@example.test',
        ]);
        if ($grant) {
            $this->grantPermission($person, 'usuarios.validar');
        }

        return $person;
    }

    private function sessionFor(Pessoa $person): void
    {
        $person->refresh();
        $this->flushSession();
        Auth::forgetGuards();
        $guard = Auth::guard('web_pessoa');

        $this->withSession([
            $guard->getName() => $person->getKey(),
            'web_identity' => [
                'type' => 'pessoa', 'id' => $person->getKey(), 'version' => $person->auth_version,
                'started_at' => now()->timestamp, 'last_activity' => now()->timestamp,
            ],
        ]);
    }
}
