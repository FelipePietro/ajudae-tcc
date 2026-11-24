<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\CatEvento;
use App\Models\Evento;
use App\Models\Inscricao;
use App\Models\Pessoa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class Bloco81WebParticipationTest extends TestCase
{
    use RefreshDatabase;

    public function test_session_history_and_ong_evaluation_share_the_deadline_and_identity(): void
    {
        if (! config('identity.enabled')) {
            $this->markTestSkipped('Executar com AJUDAE_WEB_AUTH=true.');
        }
        $pessoa = $this->createPessoa();
        $ong = $this->createOng('ativo');
        $evento = new Evento([
            'nm_evento' => 'Oficina de teste',
            'descricao_evento' => 'Atividade sintética',
            'vagas_evento' => 1,
            'modalidade_evento' => 'online',
            'imagem_evento_link' => 'teste.webp',
            'cat_evento_id' => CatEvento::create(['nome_categoria' => 'Teste Web 8.1'])->getKey(),
            'status_evento' => 'finalizado',
        ]);
        $evento->forceFill(['ong_id' => $ong->getKey(), 'finalizado_em' => now()->subDay()])->save();
        $agenda = Agenda::create([
            'evento_id' => $evento->getKey(),
            'status_ativo' => 'finalizado',
            'data_inicio' => now()->subDays(2),
            'data_fim' => now()->subDays(2)->addHour(),
            'vagas_agenda' => 1,
        ]);
        Inscricao::create([
            'agenda_id' => $agenda->getKey(),
            'pessoa_id' => $pessoa->getKey(),
            'dt_inscricao' => now()->subDays(3),
            'status_inscricao' => 'participou',
            'presenca' => true,
            'presenca_confirmada_em' => $agenda->data_fim,
        ]);
        $url = route('identity.participation.rate', $evento);
        $index = route('identity.participation.index');

        $this->getJson($index)->assertUnauthorized()->assertHeader('Cache-Control', 'no-store, private');
        $this->postJson($url, ['nota' => 5])->assertUnauthorized();
        $this->sessionFor($pessoa);
        $this->getJson($index)->assertOk()
            ->assertJsonPath('data.data.0.titulo', 'Oficina de teste')
            ->assertJsonPath('data.data.0.avaliacao_url', $url)
            ->assertJsonPath('data.data.0.agendas.0.situacao', 'Presença confirmada');
        $this->postJson($url, ['nota' => 3.5])->assertUnprocessable()->assertJsonValidationErrors('nota');
        $this->postJson($url, ['nota' => 4, 'pessoa_id' => 0])->assertCreated()->assertJsonPath('data.nota', 4);
        $this->assertDatabaseHas('avaliacao_ong', ['pessoa_id' => $pessoa->getKey(), 'evento_id' => $evento->getKey(), 'nota' => 4]);
        $this->postJson($url, ['nota' => 5])->assertConflict();

        $evento->forceFill(['finalizado_em' => now()->subDays(8)])->save();
        $this->getJson($index)->assertOk()
            ->assertJsonPath('data.data.0.nota', 4)
            ->assertJsonPath('data.data.0.avaliacao_url', null);
    }

    private function sessionFor(Pessoa $pessoa): void
    {
        $pessoa->refresh();
        $guard = Auth::guard('web_pessoa');
        $guard->setUser($pessoa);
        $this->withSession([
            $guard->getName() => $pessoa->getKey(),
            'web_identity' => [
                'type' => 'pessoa', 'id' => $pessoa->getKey(), 'version' => $pessoa->auth_version,
                'started_at' => now()->timestamp, 'last_activity' => now()->timestamp,
            ],
        ]);
    }
}
