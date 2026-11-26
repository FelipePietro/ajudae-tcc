<?php

namespace Tests\Feature;

use App\Models\CatEvento;
use App\Models\Habilidade;
use App\Models\Ong;
use App\Models\Pessoa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class Bloco9WebAccountOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        if (! config('identity.enabled')) {
            $this->markTestSkipped('Execute com AJUDAE_WEB_AUTH=true.');
        }
    }

    public function test_autenticacao_web_limita_tentativas_e_encerra_a_sessao_apos_trocar_senha(): void
    {
        $person = $this->createPessoa();
        $organization = $this->createOng('ativo');
        $this->get('/conta')->assertRedirect('/login');

        $invalid = ['tipo_login' => 'pessoa', 'login' => 'credencial-inexistente', 'senha' => 'sintetica'];
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->from('/login')->post('/login', $invalid)->assertRedirect('/login')
                ->assertSessionHasErrors('login')->assertSessionMissing('web_identity');
        }
        $this->post('/login', $invalid)->assertTooManyRequests()->assertHeader('Retry-After');

        $this->post('/login', ['tipo_login' => 'pessoa', 'login' => $person->login_pessoa, 'senha' => 'senha-segura'])
            ->assertRedirect('/perfil')->assertSessionHas('web_identity.type', 'pessoa');
        $this->get('/conta')->assertOk();
        $newPassword = ['senha' => 'nova-senha-sintetica', 'senha_confirmation' => 'nova-senha-sintetica'];
        $this->from('/conta')->post('/conta/senha', $newPassword + ['senha_atual' => 'senha-incorreta'])
            ->assertRedirect('/conta')->assertSessionHasErrors('senha_atual')->assertSessionHas('web_identity.type', 'pessoa');
        $this->post('/conta/senha', $newPassword + ['senha_atual' => 'senha-segura'])
            ->assertRedirect('/login')->assertSessionMissing('web_identity');
        $this->get('/perfil')->assertRedirect('/login');

        $this->post('/login', ['tipo_login' => 'ong', 'login' => $organization->login_ong, 'senha' => 'senha-segura'])
            ->assertRedirect('/ong/painel')->assertSessionHas('web_identity.type', 'ong');
        $this->post('/logout')->assertRedirect('/login')->assertSessionMissing('web_identity');
        $this->get('/ong/painel')->assertRedirect('/login');
    }

    public function test_preferencias_e_dados_usam_a_identidade_da_sessao(): void
    {
        $person = $this->createPessoa();
        $other = $this->createPessoa(overrides: [
            'cpf_pessoa' => '11144477735', 'login_pessoa' => 'outra.pessoa', 'email_pessoa' => 'outra@example.test',
        ]);
        $skill = Habilidade::create(['nome_habilidade' => 'Acolhimento', 'descricao_habilidade' => 'Recepção de participantes.']);
        $body = ['habilidade_id' => $skill->getKey(), 'nivel_habilidade' => 2];

        $this->withToken($person->createToken('fixture', ['pessoa'])->plainTextToken)
            ->getJson('/conta/pessoa/dados')->assertUnauthorized();
        $this->sessionFor($person);
        $this->getJson('/conta/pessoa/dados?contexto=ong')->assertOk()->assertJsonPath('data.pessoa_id', $person->getKey());
        $this->postJson('/conta/pessoa/'.$other->getKey().'/habilidades', $body)->assertForbidden();
        $this->postJson('/conta/pessoa/'.$person->getKey().'/habilidades', $body)->assertCreated();
        $this->getJson('/conta/pessoa/preferencias')->assertOk()
            ->assertJsonPath('data.habilidades.0.pivot.nivel_habilidade', 2);
        $this->patchJson('/conta/pessoa/'.$person->getKey().'/dados', ['tele_pessoa' => '11911112222'])->assertOk();
        $this->assertSame('11911112222', $person->fresh()->tele_pessoa);

        $this->sessionFor($this->createOng('ativo'));
        $this->getJson('/conta/pessoa/dados')->assertForbidden();
        $this->getJson('/conta/ong/dados')->assertOk()->assertJsonMissingPath('data.rg_responsavel_ong_link');
    }

    public function test_documentos_e_reenvio_usam_o_titular_da_sessao_web(): void
    {
        config(['filesystems.document_private_disk' => 'local']);
        Storage::fake('local');
        $person = $this->createPessoa(overrides: ['status_validacao' => 'correcao_solicitada']);
        $other = $this->createPessoa(overrides: [
            'cpf_pessoa' => '11144477735', 'login_pessoa' => 'outra.pessoa', 'email_pessoa' => 'outra@example.test',
        ]);

        $this->getJson('/conta/pessoa/validacao')->assertUnauthorized();
        $this->sessionFor($person);
        $this->get('/perfil')->assertOk()
            ->assertSee('data-document-upload', false)
            ->assertSee('data-web-sponsorship', false)
            ->assertSee('data-web-promotion-form', false);
        $this->getJson('/conta/pessoa/validacao')->assertOk()
            ->assertJsonPath('data.status_validacao', 'correcao_solicitada')->assertJsonCount(0, 'data.documentos');

        $path = '/conta/pessoa/documentos/documento_identidade';
        $this->post($path, ['arquivo' => UploadedFile::fake()->create('sintetico.txt')], ['Accept' => 'application/json'])
            ->assertUnprocessable()->assertJsonValidationErrors('arquivo');
        $file = UploadedFile::fake()->createWithContent(
            'sintetico.pdf', file_get_contents(resource_path('fixtures/admin-documents/certidao.pdf')),
        );
        $id = $this->post($path, ['arquivo' => $file], ['Accept' => 'application/json'])
            ->assertCreated()->assertJsonPath('data.status', 'pendente')->assertJsonMissingPath('data.storage_path')
            ->json('data.documento_pessoa_id');
        $this->getJson('/conta/pessoa/validacao')->assertOk()->assertJsonPath('data.documentos.0.documento_pessoa_id', $id);
        $this->getJson('/conta/pessoa/antecedentes')->assertOk()->assertJsonPath('data.renovacao_necessaria', false);
        $download = "/conta/documentos/{$id}/download";
        $this->get($download)->assertOk()->assertHeaderContains('Cache-Control', 'no-store');
        $this->postJson('/conta/pessoa/validacao/reenviar')->assertOk()
            ->assertJsonPath('data.status_validacao', 'aguardando_validacao');
        $this->postJson('/conta/pessoa/validacao/reenviar')->assertConflict();

        $this->sessionFor($other);
        $this->getJson($download)->assertForbidden();
    }

    public function test_admin_web_separa_capacidades_e_audita_campanhas(): void
    {
        $admin = $this->createPessoa('administrador');
        $participant = $this->createPessoa(overrides: [
            'cpf_pessoa' => '11144477735', 'login_pessoa' => 'outra.pessoa', 'email_pessoa' => 'outra@example.test',
            'status_validacao' => 'aguardando_validacao',
        ]);
        $category = CatEvento::create(['nome_categoria' => 'Apoio']);
        $this->sessionFor($admin);
        $this->getJson('/admin/validacoes-pessoa/dados?capacidade=usuarios.validar')->assertForbidden();
        $this->getJson('/admin/solicitacoes-organizador/dados')->assertForbidden();
        $this->getJson('/admin/log-acoes/dados')->assertForbidden();
        $this->getJson('/admin/solicitacoes-lgpd/dados')->assertForbidden();

        foreach (['usuarios.validar', 'xp.campanhas.gerenciar', 'admin.logs.visualizar', 'lgpd.visualizar'] as $permission) {
            $this->grantPermission($admin, $permission);
        }
        $this->sessionFor($admin->fresh());
        $this->getJson('/admin/validacoes-pessoa/dados')->assertOk();
        $this->postJson('/admin/validacoes-pessoa/'.$participant->getKey().'/assumir')->assertOk()
            ->assertJsonPath('data.status_validacao', 'em_analise');
        $this->getJson('/admin/solicitacoes-lgpd/dados')->assertOk();
        $this->patchJson('/admin/solicitacoes-lgpd/1/status', ['status' => 'em_analise'])->assertForbidden();
        $this->getJson('/admin/solicitacoes-organizador/dados')->assertForbidden();

        $id = $this->postJson('/admin/campanhas-xp', [
            'nome' => 'Apoio comunitário', 'multiplicador' => 1.5, 'inicio' => '2026-12-01', 'fim' => '2026-12-31',
            'ativa' => true, 'cat_evento_id' => $category->getKey(),
        ])->assertCreated()->json('data.campanha_xp_id');
        $this->patchJson("/admin/campanhas-xp/{$id}/status", ['ativa' => false])->assertOk()->assertJsonPath('data.ativa', false);
        $this->getJson('/admin/log-acoes/dados?acao=xp.campanha_atualizada')->assertOk()
            ->assertJsonPath('data.total', 2)->assertJsonPath('data.data.0.pessoa_id', $admin->getKey())
            ->assertJsonMissingPath('data.data.0.detalhes');
    }

    private function sessionFor(Pessoa|Ong $account): void
    {
        $account->refresh();
        $this->flushHeaders()->flushSession();
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
