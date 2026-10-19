<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\CatEvento;
use App\Models\Evento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PessoaValidationAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_cadastro_exige_dezoito_anos_reais(): void
    {
        Carbon::setTestNow('2026-10-09 12:00:00');

        $this->postJson('/api/v1/pessoas/register', $this->pessoaPayload([
            'dt_nasc' => '2008-10-10',
        ]))->assertUnprocessable()->assertJsonValidationErrors('dt_nasc');

        $this->postJson('/api/v1/pessoas/register', $this->pessoaPayload([
            'dt_nasc' => '2008-10-09',
        ]))->assertCreated()->assertJsonPath('data.user.status_validacao', 'aguardando_validacao');

        $this->postJson('/api/v1/pessoas/register', $this->pessoaPayload([
            'cpf_pessoa' => '11144477735',
            'email_pessoa' => 'adulta@example.com',
            'login_pessoa' => 'adulta',
            'dt_nasc' => '1990-01-01',
        ]))->assertCreated();
    }

    public function test_pessoa_pendente_pode_logar_e_acessar_area_privada(): void
    {
        $register = $this->postJson('/api/v1/pessoas/register', $this->pessoaPayload())
            ->assertCreated()
            ->assertJsonPath('data.user.status_validacao', 'aguardando_validacao');

        $this->withToken($register->json('data.token'))
            ->getJson('/api/v1/pessoas/me')
            ->assertOk()
            ->assertJsonPath('data.status_validacao', 'aguardando_validacao');

        $this->postJson('/api/v1/pessoas/login', [
            'login_pessoa' => 'maria.voluntaria',
            'senha_pessoa' => 'senha-segura',
        ])->assertOk();
    }

    public function test_apenas_pessoas_aprovadas_possuem_perfil_publico(): void
    {
        $pendente = $this->createPessoa('voluntario', ['status_validacao' => 'aguardando_validacao']);
        $aprovada = $this->createPessoa('voluntario', [
            'cpf_pessoa' => '11144477735',
            'email_pessoa' => 'aprovada@example.com',
            'login_pessoa' => 'aprovada',
        ]);
        $organizador = $this->createPessoa('organizador', [
            'cpf_pessoa' => '00000000081', 'email_pessoa' => 'orgpublico@example.com', 'login_pessoa' => 'orgpublico',
        ]);
        $administrador = $this->createPessoa('administrador', [
            'cpf_pessoa' => '00000000082', 'email_pessoa' => 'adminpublico@example.com', 'login_pessoa' => 'adminpublico',
        ]);

        $this->getJson('/api/v1/pessoas')
            ->assertOk()
            ->assertJsonMissing(['pessoa_id' => $pendente->pessoa_id])
            ->assertJsonMissing(['pessoa_id' => $organizador->pessoa_id])
            ->assertJsonMissing(['pessoa_id' => $administrador->pessoa_id])
            ->assertJsonFragment(['pessoa_id' => $aprovada->pessoa_id]);
        $this->getJson("/api/v1/pessoas/{$pendente->pessoa_id}")->assertNotFound();
        $this->getJson("/api/v1/pessoas/{$organizador->pessoa_id}")->assertNotFound();
        $this->getJson("/api/v1/pessoas/{$administrador->pessoa_id}")->assertNotFound();
        $this->getJson("/api/v1/pessoas/{$aprovada->pessoa_id}")->assertOk();

        Sanctum::actingAs($pendente, ['pessoa']);
        $this->getJson('/api/v1/pessoas/me')->assertOk();
    }

    public function test_dados_associativos_publicos_exigem_voluntario_aprovado(): void
    {
        $pessoas = [
            $this->createPessoa('voluntario', [
                'cpf_pessoa' => '00000000083', 'email_pessoa' => 'pendassoc@example.com',
                'login_pessoa' => 'pendassoc', 'status_validacao' => 'aguardando_validacao',
            ]),
            $this->createPessoa('voluntario', [
                'cpf_pessoa' => '00000000084', 'email_pessoa' => 'reprovassoc@example.com',
                'login_pessoa' => 'reprovassoc', 'status_validacao' => 'reprovado',
            ]),
            $this->createPessoa('organizador', [
                'cpf_pessoa' => '00000000085', 'email_pessoa' => 'orgassoc@example.com', 'login_pessoa' => 'orgassoc',
            ]),
            $this->createPessoa('administrador', [
                'cpf_pessoa' => '00000000086', 'email_pessoa' => 'adminassoc@example.com', 'login_pessoa' => 'adminassoc',
            ]),
        ];
        $aprovada = $this->createPessoa('voluntario', [
            'cpf_pessoa' => '00000000087', 'email_pessoa' => 'okassoc@example.com', 'login_pessoa' => 'okassoc',
        ]);

        foreach (['habilidades', 'causas', 'recursos'] as $associacao) {
            foreach ($pessoas as $pessoa) {
                $this->getJson("/api/v1/pessoas/{$pessoa->pessoa_id}/{$associacao}")->assertNotFound();
            }

            $this->getJson("/api/v1/pessoas/{$aprovada->pessoa_id}/{$associacao}")->assertOk();
        }
    }

    public function test_fila_admin_lista_so_voluntarios_e_acoes_recusam_outros_roles(): void
    {
        $voluntario = $this->createPessoa('voluntario', [
            'cpf_pessoa' => '00000000088', 'email_pessoa' => 'filavol@example.com',
            'login_pessoa' => 'filavol', 'status_validacao' => 'aguardando_validacao',
        ]);
        $organizador = $this->createPessoa('organizador', [
            'cpf_pessoa' => '00000000089', 'email_pessoa' => 'filaorg@example.com',
            'login_pessoa' => 'filaorg', 'status_validacao' => 'aguardando_validacao',
        ]);
        $administradorAlvo = $this->createPessoa('administrador', [
            'cpf_pessoa' => '00000000090', 'email_pessoa' => 'filaadm@example.com',
            'login_pessoa' => 'filaadm', 'status_validacao' => 'aguardando_validacao',
        ]);
        $admin = $this->createPessoa('administrador', [
            'cpf_pessoa' => '00000000091', 'email_pessoa' => 'fiscalfila@example.com', 'login_pessoa' => 'fiscalfila',
        ]);
        $this->grantPermission($admin, 'usuarios.validar');
        Sanctum::actingAs($admin, ['pessoa']);

        $this->getJson('/api/v1/admin/validacoes-pessoa')
            ->assertOk()
            ->assertJsonFragment(['pessoa_id' => $voluntario->pessoa_id])
            ->assertJsonMissing(['pessoa_id' => $organizador->pessoa_id])
            ->assertJsonMissing(['pessoa_id' => $administradorAlvo->pessoa_id]);

        $this->getJson("/api/v1/admin/validacoes-pessoa/{$organizador->pessoa_id}")->assertOk();
        $this->postJson("/api/v1/admin/validacoes-pessoa/{$organizador->pessoa_id}/assumir")->assertStatus(409);
        $this->postJson("/api/v1/admin/validacoes-pessoa/{$administradorAlvo->pessoa_id}/assumir")->assertStatus(409);
    }

    public function test_candidatura_exige_role_voluntario_e_status_aprovado(): void
    {
        $organizador = $this->createPessoa('organizador');
        $categoria = CatEvento::create(['nome_categoria' => 'Educação']);
        $evento = new Evento([
            'nm_evento' => 'Ação', 'descricao_evento' => 'Ação social', 'vagas_evento' => 20,
            'modalidade_evento' => 'online', 'imagem_evento_link' => 'evento.jpg',
            'cat_evento_id' => $categoria->cat_evento_id, 'status_evento' => 'ativo',
        ]);
        $evento->forceFill(['pessoa_id' => $organizador->pessoa_id, 'ong_id' => null])->save();
        $agenda = Agenda::create([
            'evento_id' => $evento->evento_id,
            'vagas_agenda' => 20,
            'data_inicio' => now()->addDays(2),
            'data_fim' => now()->addDays(2)->addHours(2),
            'status_ativo' => 'ativo',
        ]);

        foreach (['aguardando_validacao', 'em_analise', 'correcao_solicitada', 'reprovado', 'reprovado_definitivamente'] as $index => $status) {
            $pessoa = $this->createPessoa('voluntario', [
                'cpf_pessoa' => str_pad((string) ($index + 1), 11, '0', STR_PAD_LEFT),
                'email_pessoa' => "status{$index}@example.com",
                'login_pessoa' => "status{$index}",
                'status_validacao' => $status,
            ]);
            Sanctum::actingAs($pessoa, ['pessoa']);
            $this->postJson("/api/v1/agendas/{$agenda->agenda_id}/inscricoes")->assertForbidden();
        }

        foreach (['organizador', 'administrador'] as $index => $role) {
            $pessoa = $this->createPessoa($role, [
                'cpf_pessoa' => str_pad((string) ($index + 20), 11, '0', STR_PAD_LEFT),
                'email_pessoa' => "role{$index}@example.com",
                'login_pessoa' => "role{$index}",
            ]);
            Sanctum::actingAs($pessoa, ['pessoa']);
            $this->postJson("/api/v1/agendas/{$agenda->agenda_id}/inscricoes")->assertForbidden();
        }

        $aprovado = $this->createPessoa('voluntario', [
            'cpf_pessoa' => '00000000099', 'email_pessoa' => 'ok@example.com', 'login_pessoa' => 'ok',
        ]);
        Sanctum::actingAs($aprovado, ['pessoa']);
        $this->postJson("/api/v1/agendas/{$agenda->agenda_id}/inscricoes")->assertCreated();
    }

    public function test_permissoes_administrativas_respeitam_role_permission_e_master(): void
    {
        foreach (['voluntario', 'organizador'] as $index => $role) {
            $pessoa = $this->createPessoa($role, [
                'cpf_pessoa' => str_pad((string) ($index + 40), 11, '0', STR_PAD_LEFT),
                'email_pessoa' => "naoadmin{$index}@example.com",
                'login_pessoa' => "naoadmin{$index}",
            ]);
            $this->grantPermission($pessoa, 'usuarios.validar');
            Sanctum::actingAs($pessoa, ['pessoa']);
            $this->getJson('/api/v1/admin/validacoes-pessoa')->assertForbidden();
        }

        $semPermissao = $this->createPessoa('administrador', [
            'cpf_pessoa' => '00000000050', 'email_pessoa' => 'sem@example.com', 'login_pessoa' => 'sem',
        ]);
        Sanctum::actingAs($semPermissao, ['pessoa']);
        $this->getJson('/api/v1/admin/validacoes-pessoa')->assertForbidden();

        $comPermissao = $this->createPessoa('administrador', [
            'cpf_pessoa' => '00000000051', 'email_pessoa' => 'com@example.com', 'login_pessoa' => 'com',
        ]);
        $this->grantPermission($comPermissao, 'usuarios.validar');
        Sanctum::actingAs($comPermissao, ['pessoa']);
        $this->getJson('/api/v1/admin/validacoes-pessoa')->assertOk();

        $master = $this->createPessoa('administrador', [
            'cpf_pessoa' => '00000000052', 'email_pessoa' => 'master@example.com', 'login_pessoa' => 'master',
            'admin_master' => true,
        ]);
        Sanctum::actingAs($master, ['pessoa']);
        $this->getJson('/api/v1/admin/validacoes-pessoa')->assertOk();
    }
}
