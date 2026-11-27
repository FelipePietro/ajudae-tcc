<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\CatEvento;
use App\Models\Evento;
use App\Models\Inscricao;
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

    public function test_web_retem_intencao_na_sessao_ate_confirmacao_do_cliente(): void
    {
        $this->webComo($this->createOng('ativo'));
        $url = '/gestao/eventos/intencao';
        $this->getJson($url)->assertOk()->assertJsonPath('data.chave', null);
        $chave = $this->postJson($url)->assertOk()->json('data.chave');
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $chave);
        $this->postJson($url)->assertOk()->assertJsonPath('data.chave', $chave);

        $dados = [...$this->evento(), 'agendas' => [$this->agenda(2)]];
        $cabecalho = ['Idempotency-Key' => $chave];
        $id = $this->postJson('/gestao/eventos', $dados, $cabecalho)->assertCreated()->json('data.evento_id');

        $this->getJson($url)->assertOk()->assertJsonPath('data.chave', $chave);
        $this->postJson('/gestao/eventos', $dados, $cabecalho)->assertCreated()->assertJsonPath('data.evento_id', $id);
        $this->assertDatabaseCount('evento', 1);
        $this->assertDatabaseCount('agenda', 1);

        $outra = ($chave[0] === 'a' ? 'b' : 'a').substr($chave, 1);
        $this->deleteJson($url, ['chave' => $outra])->assertNoContent();
        $this->getJson($url)->assertOk()->assertJsonPath('data.chave', $chave);

        $this->deleteJson($url, ['chave' => $chave])->assertNoContent();
        $this->getJson($url)->assertOk()->assertJsonPath('data.chave', null);
        $nova = $this->postJson($url)->assertOk()->json('data.chave');
        $this->assertNotSame($chave, $nova);

        $this->deleteJson($url, ['chave' => $chave])->assertNoContent();
        $this->getJson($url)->assertOk()->assertJsonPath('data.chave', $nova);
    }

    public function test_intencao_web_exige_ator_autorizado_para_criar_evento(): void
    {
        if (! config('identity.enabled')) {
            $this->markTestSkipped('Executar com AJUDAE_WEB_AUTH=true.');
        }
        $url = '/gestao/eventos/intencao';
        $this->getJson($url)->assertUnauthorized();
        $this->webComo($this->createOng());
        $this->getJson($url)->assertForbidden();
        $this->postJson($url)->assertForbidden();
        $this->deleteJson($url, ['chave' => str_repeat('a', 32)])->assertForbidden();
    }

    public function test_web_reenvia_a_mesma_criacao_sem_duplicar_e_rejeita_dados_diferentes(): void
    {
        $this->webComo($this->createOng('ativo'));
        $cabecalho = ['Idempotency-Key' => 'criacao-json-1'];
        $agendas = [$this->agenda(2), $this->agenda(5, 3)];
        $dados = [...$this->evento(), 'agendas' => $agendas];

        $id = $this->postJson('/gestao/eventos', $dados, $cabecalho)->assertCreated()->json('data.evento_id');
        $this->postJson('/gestao/eventos', array_reverse([
            ...$dados,
            'agendas' => array_map(fn ($agenda) => array_reverse($agenda, true), $agendas),
        ], true), $cabecalho)->assertCreated()->assertJsonPath('data.evento_id', $id)
            ->assertJsonPath('data.agendas.0.vagas_agenda', 2)->assertJsonPath('data.agendas.1.vagas_agenda', 5);
        $this->postJson('/gestao/eventos', [...$dados, 'agendas' => array_reverse($agendas)], $cabecalho)
            ->assertConflict();
        $this->postJson('/gestao/eventos', $dados, ['Idempotency-Key' => 'chave inválida'])
            ->assertUnprocessable()->assertJsonValidationErrors('idempotency_key');
        $this->assertDatabaseCount('evento', 1);
        $this->assertDatabaseCount('agenda', 2);
        $this->assertDatabaseCount('evento_criacao_idempotencia', 1);

        $outro = $this->postJson('/gestao/eventos', $dados, ['Idempotency-Key' => 'criacao-json-2'])
            ->assertCreated()->json('data.evento_id');
        $this->assertNotSame($id, $outro);
        $this->assertDatabaseCount('evento', 2);
        $this->assertDatabaseCount('agenda', 4);
    }

    public function test_chave_web_respeita_o_ator_e_nao_recria_evento_excluido(): void
    {
        $ong = $this->createOng('ativo');
        $organizador = $this->createPessoa('organizador');
        $dados = [...$this->evento(), 'agendas' => [$this->agenda(2)]];
        $cabecalho = ['Idempotency-Key' => 'criacao-por-ator'];

        $this->webComo($ong);
        $id = $this->postJson('/gestao/eventos', $dados, $cabecalho)->assertCreated()->json('data.evento_id');

        $this->webComo($organizador);
        $outroId = $this->postJson('/gestao/eventos', $dados, $cabecalho)->assertCreated()
            ->assertJsonPath('data.pessoa_id', $organizador->getKey())->json('data.evento_id');
        $this->assertNotSame($id, $outroId);

        $evento = Evento::findOrFail($id);
        $evento->agendas()->delete();
        $evento->delete();
        $this->webComo($ong);
        $this->postJson('/gestao/eventos', $dados, $cabecalho)->assertConflict();
        $this->assertDatabaseCount('evento', 1);
        $this->assertDatabaseCount('evento_criacao_idempotencia', 2);
    }

    public function test_web_reenvia_multipart_sem_duplicar_objeto_e_detecta_banner_diferente(): void
    {
        $this->webComo($this->createOng('ativo'));
        config()->set('filesystems.image_storage_disk', 'image_local');
        $disk = Storage::fake('image_local');
        $dados = [...$this->evento(), 'agendas' => [$this->agenda(2)]];
        $cabecalho = ['Accept' => 'application/json', 'Idempotency-Key' => 'criacao-multipart'];

        $id = $this->post('/gestao/eventos', [
            ...$dados, 'imagem_evento' => UploadedFile::fake()->image('banner.png', 64, 48),
        ], $cabecalho)->assertCreated()->json('data.evento_id');
        $this->post('/gestao/eventos', [
            ...$dados, 'imagem_evento' => UploadedFile::fake()->image('mesmo-banner.png', 64, 48),
        ], $cabecalho)->assertCreated()->assertJsonPath('data.evento_id', $id);
        $this->post('/gestao/eventos', [
            ...$dados, 'imagem_evento' => UploadedFile::fake()->image('outro-banner.png', 48, 64),
        ], $cabecalho)->assertConflict();
        $this->assertCount(1, $disk->allFiles());
        $this->assertDatabaseCount('evento', 1);
    }

    public function test_capa_publica_prioriza_imagem_gerenciada_e_nao_expoe_imagem_pendente(): void
    {
        $this->withoutVite();
        $this->webComo($this->createOng('ativo'));
        config()->set('filesystems.image_storage_disk', 'image_local');
        Storage::fake('image_local');
        $id = $this->post('/gestao/eventos', [
            ...$this->evento(),
            'agendas' => [$this->agenda(2)],
            'imagem_evento' => UploadedFile::fake()->image('banner.png', 64, 48),
        ], ['Accept' => 'application/json'])->assertCreated()->json('data.evento_id');

        $evento = Evento::findOrFail($id);
        $evento->forceFill(['imagem_evento_link' => 'https://example.test/banner-legado.jpg'])->save();
        $this->get("/eventos/{$id}")->assertNotFound();
        $this->get("/api/v1/eventos/{$id}/imagem")->assertNotFound();

        $evento->forceFill(['status_evento' => 'ativo'])->save();
        $this->get("/eventos/{$id}")->assertOk()
            ->assertSee("/api/v1/eventos/{$id}/imagem?v=", false)
            ->assertDontSee('banner-legado.jpg')->assertDontSee($evento->managed_image_path);
        $this->get("/api/v1/eventos/{$id}/imagem")->assertOk()->assertHeader('Content-Type', 'image/png');
    }

    public function test_capa_publica_exibe_banner_legado_valido_e_omite_referencias_privadas_ou_ausentes(): void
    {
        $this->withoutVite();
        $this->webComo($this->createOng('ativo'));
        $id = $this->postJson('/gestao/eventos', [
            ...$this->evento(), 'agendas' => [$this->agenda(2)],
        ])->assertCreated()->json('data.evento_id');
        $evento = Evento::findOrFail($id);
        $evento->forceFill(['status_evento' => 'ativo'])->save();

        foreach ([
            'https://example.test/banner.jpg' => 'https://example.test/banner.jpg',
            'imagens/banner.jpg' => '/imagens/banner.jpg',
        ] as $link => $src) {
            $evento->forceFill(['imagem_evento_link' => $link])->save();
            $this->get("/eventos/{$id}")->assertOk()
                ->assertSee("data-public-event-image src=\"{$src}\"", false);
        }
        foreach (["/gestao/eventos/{$id}/imagem", '/storage/private/banner.png', null] as $link) {
            $evento->forceFill(['imagem_evento_link' => $link])->save();
            $this->get("/eventos/{$id}")->assertOk()->assertDontSee('data-public-event-image');
        }
    }

    public function test_detalhe_de_evento_publicado_mostra_a_disponibilidade_de_agendas_em_todos_os_contextos(): void
    {
        $this->withoutVite();
        $this->webComo($ong = $this->createOng('ativo'));

        $inicio = now()->addMinutes(30);
        $id = $this->postJson('/gestao/eventos', [
            ...$this->evento(),
            'agendas' => [
                $this->agenda(2),
                $this->agenda(1, 3),
                $this->agenda(3, 4),
                [
                    'data_inicio' => $inicio->toISOString(),
                    'data_fim' => $inicio->copy()->addHour()->toISOString(),
                    'vagas_agenda' => 7,
                ],
            ],
        ])->assertCreated()->json('data.evento_id');
        $evento = Evento::findOrFail($id);
        [$aberta, $lotada, $cancelada] = $evento->agendas()->orderBy('agenda_id')->get()->all();
        $cancelada->forceFill([
            'status_ativo' => 'cancelado', 'motivo_cancelamento' => 'Motivo sintético reservado',
        ])->save();

        $voluntario = $this->createPessoa();
        $outraPessoa = $this->createPessoa('voluntario', [
            'cpf_pessoa' => '00000000001', 'email_pessoa' => 'outro@example.test', 'login_pessoa' => 'outro',
        ]);
        $pendente = $this->createPessoa('voluntario', [
            'cpf_pessoa' => '00000000002', 'email_pessoa' => 'pendente@example.test', 'login_pessoa' => 'pendente',
            'status_validacao' => 'aguardando_validacao',
        ]);
        $organizador = $this->createPessoa('organizador', [
            'cpf_pessoa' => '00000000003', 'email_pessoa' => 'organizador@example.test', 'login_pessoa' => 'organizador',
        ]);

        foreach ([[$aberta, $voluntario, 'aprovado'], [$aberta, $outraPessoa, 'pendente'], [$lotada, $voluntario, 'aprovado']] as [$agenda, $pessoa, $status]) {
            Inscricao::create([
                'agenda_id' => $agenda->getKey(),
                'pessoa_id' => $pessoa->getKey(),
                'status_inscricao' => $status,
                'dt_inscricao' => now(),
                'carta_apresentacao' => 'Carta sintética reservada',
            ]);
        }

        $url = "/eventos/{$id}";
        $candidatura = "/candidatura/{$id}/agendas";
        $this->flushSession();
        Auth::forgetGuards();
        $this->get($url)->assertNotFound();
        $evento->forceFill(['status_evento' => 'ativo'])->save();

        $this->get($url)->assertOk()
            ->assertSee('1 vaga disponível')
            ->assertSee('A agenda já está lotada.')
            ->assertSee('O evento ou a agenda não está disponível para candidaturas.')
            ->assertSee('A candidatura fecha uma hora antes do início da agenda.')
            ->assertSee($aberta->data_inicio->toIso8601String())
            ->assertDontSee('Carta sintética reservada')
            ->assertDontSee('Motivo sintético reservado')
            ->assertDontSee('7 vagas disponíveis')
            ->assertDontSee('data-agenda-choice="available"', false)
            ->assertDontSee('data-personalized-agendas', false);
        $this->getJson($candidatura)->assertUnauthorized();

        foreach ([$ong, $pendente, $organizador] as $conta) {
            $this->webComo($conta);
            $this->get($url)->assertOk()->assertSee('1 vaga disponível')->assertDontSee('data-personalized-agendas', false);
            $this->getJson($candidatura)->assertForbidden();
        }

        $this->webComo($voluntario);
        $this->get($url)->assertOk()->assertSee('1 vaga disponível')->assertSee('data-personalized-agendas', false)
            ->assertSee('data-agenda-choice="unavailable" hidden="hidden"', false)
            ->assertDontSee('data-agenda-choice="available" hidden="hidden"', false)
            ->assertDontSee('Você já possui uma inscrição nesta agenda.');
        $this->getJson($candidatura)->assertOk()->assertJsonFragment([
            'id' => $aberta->getKey(), 'impedimento' => 'Você já possui uma inscrição nesta agenda.',
        ]);

        $aberta->update(['vagas_agenda' => 1]);
        $this->get($url)->assertOk()
            ->assertSee('data-agenda-choice="available" hidden="hidden"', false)
            ->assertDontSee('data-agenda-choice="unavailable" hidden="hidden"', false)
            ->assertSee('Nenhuma Agenda disponível para candidatura.');

        foreach (['aguardando a confirmação', 'cancelado'] as $status) {
            $evento->forceFill(['status_evento' => $status])->save();
            $this->get($url)->assertNotFound();
        }
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

        $dados = $this->evento();
        $cabecalho = ['Accept' => 'application/json', 'Idempotency-Key' => 'criacao-rollback'];
        $this->post('/gestao/eventos', [
            ...$dados,
            'agendas' => [$this->agenda(50), $this->agenda(61, 3)],
            'imagem_evento' => UploadedFile::fake()->image('banner.png', 64, 48),
        ], $cabecalho)->assertServerError();

        $this->assertDatabaseCount('evento', 0);
        $this->assertDatabaseCount('agenda', 0);
        $this->assertDatabaseCount('evento_criacao_idempotencia', 0);
        $this->assertSame([], $disk->allFiles());

        $this->post('/gestao/eventos', [
            ...$dados,
            'agendas' => [$this->agenda(50), $this->agenda(60, 3)],
            'imagem_evento' => UploadedFile::fake()->image('banner.png', 64, 48),
        ], $cabecalho)->assertCreated();
        $this->assertDatabaseCount('evento', 1);
        $this->assertDatabaseCount('agenda', 2);
        $this->assertDatabaseCount('evento_criacao_idempotencia', 1);
        $this->assertCount(1, $disk->allFiles());
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
        $this->flushSession();
        Auth::forgetGuards();
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
