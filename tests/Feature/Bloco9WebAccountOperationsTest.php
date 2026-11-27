<?php

namespace Tests\Feature;

use App\Models\Acompanhamento;
use App\Models\AdminLog;
use App\Models\CatEvento;
use App\Models\Habilidade;
use App\Models\Ong;
use App\Models\Pessoa;
use Dom\HTMLDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
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

    public function test_conclusao_de_apadrinhamento_tem_menu_proprio_e_exige_motivo_na_reprovacao_web(): void
    {
        $admin = $this->createPessoa('administrador');
        $padrinho = $this->createPessoa(overrides: [
            'cpf_pessoa' => '00000000021', 'login_pessoa' => 'padrinho.qa', 'email_pessoa' => 'padrinho@example.test',
        ]);
        $apadrinhado = $this->createPessoa(overrides: [
            'cpf_pessoa' => '00000000022', 'login_pessoa' => 'apadrinhado.qa', 'email_pessoa' => 'apadrinhado@example.test',
        ]);
        $vinculo = Acompanhamento::iniciar($padrinho->getKey(), $apadrinhado->getKey());
        $pedido = $vinculo->conclusoes()->create([
            'apadrinhado_id' => $apadrinhado->getKey(), 'status' => 'pendente', 'solicitada_em' => now(),
        ]);
        $pagina = '/admin/conclusoes-padrinho';
        $reprovacao = '/admin/padrinhos/conclusoes/'.$pedido->getKey().'/reprovar';
        $motivo = ['motivo' => 'Documentação sintética pendente.'];

        $this->sessionFor($admin);
        $this->get($pagina)->assertForbidden();
        $this->postJson($reprovacao, $motivo)->assertForbidden();

        $this->grantPermission($admin, 'padrinhos.validar_conclusao');
        $this->sessionFor($admin);
        $html = $this->withoutVite()->get($pagina)->assertOk()->getContent();
        $sidebar = HTMLDocument::createFromString($html, LIBXML_NOERROR, 'UTF-8');
        $activeLink = $sidebar->querySelector('#admin-sidebar nav a[aria-current="page"]');
        $this->assertNotNull($activeLink);
        $this->assertSame(route('admin.conclusoes-padrinho'), $activeLink->getAttribute('href'));
        $this->assertTrue($activeLink->classList->contains('is-active'));
        $this->get('/admin/candidatos')->assertForbidden();
        $this->postJson($reprovacao)->assertUnprocessable()->assertJsonValidationErrors('motivo');
        $this->assertSame('pendente', $pedido->fresh()->status);
        $this->postJson($reprovacao, $motivo)->assertOk()->assertJsonPath('data.status', 'rejeitada');
        $this->assertDatabaseHas('conclusao_padrinho', [
            'conclusao_padrinho_id' => $pedido->getKey(), 'admin_id' => $admin->getKey(), 'motivo_decisao' => $motivo['motivo'],
        ]);
        $this->assertSame('ativo', $vinculo->fresh()->status);
        $this->postJson($reprovacao, $motivo)->assertConflict();
        $log = AdminLog::where('acao', 'padrinho.conclusao_decidida')->where('recurso_id', $pedido->getKey())->sole();
        $this->assertSame($admin->getKey(), $log->pessoa_id);
        $this->assertSame(['de' => 'pendente', 'para' => 'rejeitada', 'motivo' => $motivo['motivo']], $log->detalhes);
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
