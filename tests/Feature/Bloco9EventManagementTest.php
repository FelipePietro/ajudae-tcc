<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\CatEvento;
use App\Models\Evento;
use App\Models\Ong;
use App\Models\Pessoa;
use App\Models\VinculoOngOrganizador;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use RuntimeException;
use Tests\TestCase;

class Bloco9EventManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_rejeita_evento_sem_agenda_e_preserva_a_criacao_conjunta(): void
    {
        $ong = $this->createOng('ativo');
        Sanctum::actingAs($ong, ['ong']);
        $dados = [...$this->evento(), 'imagem_evento_link' => 'exemplo.jpg'];
        $this->postJson('/api/v1/eventos', $dados)->assertUnprocessable()->assertJsonValidationErrors('agendas');
        $this->postJson('/api/v1/eventos', [...$dados, 'agendas' => []])->assertUnprocessable()->assertJsonValidationErrors('agendas');
        $this->assertDatabaseCount('evento', 0);

        $this->postJson('/api/v1/eventos', [...$dados, 'agendas' => [$this->agenda(50)]])
            ->assertCreated()
            ->assertJsonPath('data.ong_id', $ong->getKey())
            ->assertJsonPath('data.pessoa_id', null)
            ->assertJsonPath('data.agendas.0.vagas_agenda', 50)
            ->assertJsonPath('data.status_evento', 'aguardando a confirmação');
        $this->assertDatabaseCount('evento', 1);
        $this->assertDatabaseCount('agenda', 1);
    }

    public function test_web_cria_multiplas_agendas_com_vagas_independentes_e_imagem_gerenciada(): void
    {
        $this->webComo($ong = $this->createOng('ativo'));
        config()->set('filesystems.image_storage_disk', 'image_local');
        $disk = Storage::fake('image_local');
        $public = Storage::fake('public');

        $id = $this->post('/gestao/eventos', [
            ...$this->evento(),
            'agendas' => [$this->agenda(50), $this->agenda(60, 3)],
            'imagem_evento' => UploadedFile::fake()->image('banner.png', 64, 48),
        ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('data.ong_id', $ong->getKey())
            ->assertJsonPath('data.vagas_evento', 25)
            ->assertJsonPath('data.agendas.0.vagas_agenda', 50)
            ->assertJsonPath('data.agendas.1.vagas_agenda', 60)
            ->assertJsonMissingPath('data.managed_image_path')
            ->json('data.evento_id');

        $evento = Evento::findOrFail($id);
        $this->assertSame('image_local', $evento->managed_image_disk);
        $disk->assertExists($evento->managed_image_path);
        $public->assertMissing($evento->managed_image_path);
        $this->getJson("/gestao/eventos/{$id}")->assertOk()->assertJsonCount(2, 'data.agendas');
        $this->getJson('/gestao/eventos')->assertOk()->assertJsonPath('data.data.0.evento_id', $id);
        $this->get("/gestao/eventos/{$id}/imagem")->assertOk()->assertHeader('Content-Type', 'image/png')
            ->assertHeader('Cache-Control', 'no-store, private');
        $this->getJson("/api/v1/eventos/{$id}")->assertNotFound();
        $this->get("/api/v1/eventos/{$id}/imagem")->assertNotFound();
    }

    public function test_agenda_invalida_impede_qualquer_gravacao(): void
    {
        $this->webComo($this->createOng('ativo'));
        $invalida = $this->agenda(60, 3);
        $invalida['data_fim'] = now()->addDays(3)->addMinutes(29)->toISOString();

        $this->postJson('/gestao/eventos', [...$this->evento(), 'agendas' => [$this->agenda(50), $invalida]])
            ->assertUnprocessable()->assertJsonValidationErrors('agendas.1.data_fim');
        $this->assertDatabaseCount('evento', 0);
        $this->assertDatabaseCount('agenda', 0);
    }

    public function test_falha_na_segunda_gravacao_reverte_o_evento_as_agendas_e_a_imagem(): void
    {
        $this->webComo($this->createOng('ativo'));
        config()->set('filesystems.image_storage_disk', 'image_local');
        $disk = Storage::fake('image_local');
        Agenda::creating(function (Agenda $agenda) {
            if ($agenda->vagas_agenda === 61) {
                throw new RuntimeException('Falha sintética na segunda Agenda');
            }
        });

        $this->post('/gestao/eventos', [
            ...$this->evento(),
            'agendas' => [$this->agenda(50), $this->agenda(61, 3)],
            'imagem_evento' => UploadedFile::fake()->image('banner.png', 64, 48),
        ], ['Accept' => 'application/json'])->assertServerError();

        $this->assertDatabaseCount('evento', 0);
        $this->assertDatabaseCount('agenda', 0);
        $this->assertSame([], $disk->allFiles());
    }

    public function test_banner_invalido_identifica_o_campo_da_criacao_e_nao_grava_evento(): void
    {
        $this->webComo($this->createOng('ativo'));
        $disk = Storage::fake('image_local');

        $this->post('/gestao/eventos', [
            ...$this->evento(),
            'agendas' => [$this->agenda(50)],
            'imagem_evento' => UploadedFile::fake()->createWithContent('banner.png', 'Conteúdo inválido'),
        ], ['Accept' => 'application/json'])->assertUnprocessable()->assertJsonValidationErrors('imagem_evento');

        $this->assertDatabaseCount('evento', 0);
        $this->assertDatabaseCount('agenda', 0);
        $this->assertSame([], $disk->allFiles());
    }

    public function test_evento_institucional_exige_sessao_ong_ativa_e_titularidade_derivada(): void
    {
        if (! config('identity.enabled')) {
            $this->markTestSkipped('Executar com AJUDAE_WEB_AUTH=true.');
        }
        $dados = [...$this->evento(), 'agendas' => [$this->agenda(50)]];
        $this->postJson('/gestao/eventos', $dados)->assertUnauthorized();

        $this->webComo($ong = $this->createOng());
        $this->postJson('/gestao/eventos', $dados)->assertForbidden();
        $ong->forceFill(['status_ong' => 'ativo'])->save();
        $this->webComo($ong);
        $this->postJson('/gestao/eventos', [...$dados, 'ong_id' => 999999])
            ->assertUnprocessable()->assertJsonValidationErrors('ong_id');
        $this->assertDatabaseCount('evento', 0);
    }

    public function test_organizador_independente_cria_evento_e_agenda_no_proprio_escopo(): void
    {
        $this->webComo($ator = $this->createPessoa('organizador'));
        $id = $this->postJson('/gestao/eventos', [...$this->evento(), 'agendas' => [$this->agenda(50)]])
            ->assertCreated()
            ->assertJsonPath('data.pessoa_id', $ator->getKey())
            ->assertJsonPath('data.ong_id', null)
            ->assertJsonPath('data.responsavel_pessoa_id', $ator->getKey())
            ->json('data.evento_id');

        $this->postJson("/gestao/eventos/{$id}/agendas", $this->agenda(60, 3))->assertCreated()->assertJsonPath('data.evento_id', $id);
    }

    public function test_organizador_designado_edita_mas_nao_cria_evento_ou_agenda(): void
    {
        $ong = $this->createOng('ativo');
        $ator = $this->createPessoa('organizador');
        VinculoOngOrganizador::create([
            'ong_id' => $ong->getKey(),
            'pessoa_id' => $ator->getKey(),
            'status' => 'ativo',
            'convidado_em' => now(),
            'respondido_em' => now(),
        ]);
        Sanctum::actingAs($ong, ['ong']);
        $dados = [...$this->evento(), 'agendas' => [$this->agenda(50)], 'imagem_evento_link' => 'exemplo.jpg'];
        $id = $this->postJson('/api/v1/eventos', $dados)->assertCreated()->json('data.evento_id');
        $semDesignacao = $this->postJson('/api/v1/eventos', $dados)->assertCreated()->json('data.evento_id');
        $evento = Evento::findOrFail($id);
        $evento->forceFill(['responsavel_pessoa_id' => $ator->getKey()])->save();
        $agendaId = $evento->agendas()->sole()->getKey();
        $this->webComo($ator);

        $this->getJson("/gestao/eventos/{$semDesignacao}")->assertForbidden();
        $this->getJson('/gestao/eventos')->assertOk()->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.evento_id', $id);
        $this->postJson('/gestao/eventos', $dados)->assertForbidden();
        $this->postJson("/gestao/eventos/{$id}/agendas", $this->agenda(60, 3))->assertForbidden();
        $this->patchJson("/gestao/agendas/{$agendaId}", ['vagas_agenda' => 60])->assertOk();
        $this->patchJson("/gestao/eventos/{$id}", ['nm_evento' => 'Evento atualizado'])->assertOk();

        $legado = new Evento($this->evento());
        $legado->forceFill(['ong_id' => $ong->getKey(), 'responsavel_pessoa_id' => $ator->getKey()])->save();
        Sanctum::actingAs($ator, ['pessoa']);
        $this->deleteJson("/api/v1/eventos/{$legado->getKey()}")->assertForbidden();
        $this->assertModelExists($legado);
    }

    public function test_ultima_agenda_nao_e_excluida_e_evento_legado_orfao_nao_e_aprovado(): void
    {
        $this->webComo($ong = $this->createOng('ativo'));
        $id = $this->postJson('/gestao/eventos', [...$this->evento(), 'agendas' => [$this->agenda(50)]])
            ->assertCreated()->json('data.evento_id');
        $agendaId = Evento::findOrFail($id)->agendas()->sole()->getKey();
        $this->deleteJson("/gestao/agendas/{$agendaId}")->assertConflict();
        $this->assertDatabaseHas('agenda', ['agenda_id' => $agendaId, 'evento_id' => $id]);

        $legado = new Evento($this->evento());
        $legado->forceFill(['ong_id' => $ong->getKey()])->save();
        $admin = $this->createPessoa('administrador');
        $this->grantPermission($admin, 'eventos.aprovar');
        Sanctum::actingAs($admin, ['pessoa']);
        $this->patchJson("/api/v1/eventos/{$legado->getKey()}/status", ['status_evento' => 'ativo'])->assertConflict();
        $this->patchJson("/api/v1/eventos/{$id}/status", ['status_evento' => 'ativo'])->assertOk()
            ->assertJsonPath('data.status_evento', 'ativo');

        $legado->forceFill(['status_evento' => 'ativo'])->save();
        $this->getJson("/api/v1/eventos/{$legado->getKey()}")->assertNotFound();
        $this->getJson("/api/v1/eventos/{$legado->getKey()}/agendas")->assertNotFound();
        $this->getJson('/api/v1/eventos')->assertOk()->assertJsonMissing(['evento_id' => $legado->getKey()]);
        $this->getJson("/gestao/eventos/{$legado->getKey()}")->assertOk()->assertJsonCount(0, 'data.agendas');
    }

    private function webComo(Pessoa|Ong $conta): void
    {
        if (! config('identity.enabled')) {
            $this->markTestSkipped('Executar com AJUDAE_WEB_AUTH=true.');
        }
        $conta->refresh();
        $tipo = $conta instanceof Pessoa ? 'pessoa' : 'ong';
        $guard = Auth::guard("web_{$tipo}");
        $guard->setUser($conta);
        $this->withSession([
            $guard->getName() => $conta->getKey(),
            'web_identity' => [
                'type' => $tipo,
                'id' => $conta->getKey(),
                'version' => $conta->auth_version,
                'started_at' => now()->timestamp,
                'last_activity' => now()->timestamp,
            ],
        ]);
    }

    private function evento(): array
    {
        return [
            'nm_evento' => 'Evento sintético',
            'descricao_evento' => 'Atividade comunitária.',
            'vagas_evento' => 25,
            'modalidade_evento' => 'online',
            'cat_evento_id' => CatEvento::firstOrCreate(['nome_categoria' => 'Educação'])->getKey(),
        ];
    }

    private function agenda(int $vagas, int $dias = 2): array
    {
        $inicio = now()->addDays($dias);

        return [
            'data_inicio' => $inicio->toISOString(),
            'data_fim' => $inicio->copy()->addHour()->toISOString(),
            'vagas_agenda' => $vagas,
        ];
    }
}
