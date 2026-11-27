<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\CatEvento;
use App\Models\Evento;
use App\Models\Inscricao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AgendaInscricaoTest extends TestCase
{
    use RefreshDatabase;

    private function eventoDoOrganizador(int $vagas = 2): array
    {
        $organizador = $this->createPessoa('organizador');
        $categoria = CatEvento::create(['nome_categoria' => 'Educação']);
        $evento = new Evento([
            'nm_evento' => 'Mutirão',
            'descricao_evento' => 'Mutirão comunitário.',
            'vagas_evento' => $vagas,
            'modalidade_evento' => 'online',
            'imagem_evento_link' => 'evento.jpg',
            'cat_evento_id' => $categoria->cat_evento_id,
            'status_evento' => 'ativo',
        ]);
        $evento->forceFill([
            'pessoa_id' => $organizador->pessoa_id,
            'ong_id' => null,
        ])->save();

        return [$organizador, $evento];
    }

    public function test_only_owner_can_create_update_and_delete_agenda(): void
    {
        [$owner, $evento] = $this->eventoDoOrganizador();
        $other = $this->createPessoa('organizador', [
            'cpf_pessoa' => '11144477735',
            'email_pessoa' => 'other@example.com',
            'login_pessoa' => 'other',
        ]);

        $payload = ['data_inicio' => now()->addDays(3), 'data_fim' => now()->addDays(3)->addHours(2), 'vagas_agenda' => 2];
        Sanctum::actingAs($owner, ['pessoa']);
        $created = $this->postJson("/api/v1/eventos/{$evento->evento_id}/agendas", $payload)->assertCreated();
        $agendaId = $created->json('data.agenda_id');

        Sanctum::actingAs($other, ['pessoa']);
        $this->patchJson("/api/v1/agendas/{$agendaId}", ['data_fim' => now()->addDays(4)])->assertForbidden();
        $this->deleteJson("/api/v1/agendas/{$agendaId}")->assertForbidden();
    }

    public function test_agenda_end_must_be_after_start(): void
    {
        [$owner, $evento] = $this->eventoDoOrganizador();
        Sanctum::actingAs($owner, ['pessoa']);

        $this->postJson("/api/v1/eventos/{$evento->evento_id}/agendas", [
            'data_inicio' => now()->addDays(3),
            'data_fim' => now()->addDays(2),
            'vagas_agenda' => 2,
        ])->assertUnprocessable()->assertJsonValidationErrors('data_fim');
    }

    public function test_subscription_duplicate_capacity_presence_and_ownership_rules(): void
    {
        [$owner, $evento] = $this->eventoDoOrganizador(1);
        $agenda = Agenda::create([
            'evento_id' => $evento->evento_id,
            'status_ativo' => 'ativo',
            'data_inicio' => now()->addDays(2),
            'data_fim' => now()->addDays(2)->addHours(3),
            'vagas_agenda' => 1,
        ]);
        $volunteer = $this->createPessoa('voluntario', [
            'cpf_pessoa' => '11144477735',
            'email_pessoa' => 'volunteer@example.com',
            'login_pessoa' => 'volunteer',
        ]);
        $second = $this->createPessoa('voluntario', [
            'cpf_pessoa' => '93541134780',
            'email_pessoa' => 'second@example.com',
            'login_pessoa' => 'second',
        ]);

        Sanctum::actingAs($volunteer, ['pessoa']);
        $firstId = $this->postJson("/api/v1/agendas/{$agenda->agenda_id}/inscricoes", [])->assertCreated()->json('data.inscricao_id');
        $this->postJson("/api/v1/agendas/{$agenda->agenda_id}/inscricoes", [])->assertStatus(409);

        Sanctum::actingAs($second, ['pessoa']);
        $secondId = $this->postJson("/api/v1/agendas/{$agenda->agenda_id}/inscricoes", [])->assertCreated()->json('data.inscricao_id');
        $this->deleteJson("/api/v1/inscricoes/{$firstId}")->assertForbidden();

        Sanctum::actingAs($owner, ['pessoa']);
        $this->patchJson("/api/v1/inscricoes/{$firstId}/status", ['status_inscricao' => 'aprovado'])->assertOk();
        $this->patchJson("/api/v1/inscricoes/{$secondId}/status", ['status_inscricao' => 'aprovado'])
            ->assertStatus(409);

        $this->patchJson("/api/v1/inscricoes/{$firstId}/presenca", ['presenca' => true])->assertStatus(409);
        Carbon::setTestNow($agenda->data_inicio->copy()->addMinute());
        try {
            $this->patchJson("/api/v1/inscricoes/{$firstId}/presenca", ['presenca' => true])
                ->assertOk()->assertJsonPath('data.presenca', true)
                ->assertJsonPath('data.carga_horaria', '3.00');
        } finally {
            Carbon::setTestNow();
        }
        $this->assertFalse(Inscricao::findOrFail($firstId)->monitoria()->exists());
    }

    public function test_withdrawal_keeps_history_and_marks_late_withdrawal(): void
    {
        [$owner, $evento] = $this->eventoDoOrganizador();
        $volunteer = $this->createPessoa('voluntario', [
            'cpf_pessoa' => '11144477735',
            'email_pessoa' => 'volunteer@example.com',
            'login_pessoa' => 'volunteer',
        ]);

        foreach ([30 => false, 12 => true] as $hours => $late) {
            $agenda = Agenda::create([
                'evento_id' => $evento->evento_id,
                'status_ativo' => 'ativo',
                'data_inicio' => now()->addHours($hours),
                'data_fim' => now()->addHours($hours + 2),
                'vagas_agenda' => 2,
            ]);
            $inscricao = Inscricao::create([
                'agenda_id' => $agenda->agenda_id,
                'pessoa_id' => $volunteer->pessoa_id,
                'status_inscricao' => 'aprovado',
                'dt_inscricao' => now(),
            ]);

            Sanctum::actingAs($volunteer, ['pessoa']);
            $this->deleteJson("/api/v1/inscricoes/{$inscricao->inscricao_id}")
                ->assertOk()
                ->assertJsonPath('data.desistencia_tardia', $late);

            $this->assertDatabaseHas('p_ag_inscreve', [
                'inscricao_id' => $inscricao->inscricao_id,
                'status_inscricao' => 'desistente',
                'desistencia_tardia' => $late,
            ]);
        }
    }

    public function test_nova_candidatura_notifica_responsavel_operacional_ou_proprietario(): void
    {
        $ong = $this->createOng('ativo');
        $responsavel = $this->createPessoa('organizador');
        $independente = $this->createPessoa('organizador', [
            'cpf_pessoa' => '11144477735',
            'email_pessoa' => 'independente@example.com',
            'login_pessoa' => 'independente',
        ]);
        $voluntario = $this->createPessoa('voluntario', [
            'cpf_pessoa' => '93541134780',
            'email_pessoa' => 'voluntario@example.com',
            'login_pessoa' => 'voluntario',
        ]);
        $categoria = CatEvento::create(['nome_categoria' => 'Educação']);

        $cenarios = [
            ['ong_id' => $ong->ong_id, 'pessoa_id' => null, 'responsavel_pessoa_id' => null, 'destinatario' => $ong],
            ['ong_id' => $ong->ong_id, 'pessoa_id' => null, 'responsavel_pessoa_id' => $responsavel->pessoa_id, 'destinatario' => $responsavel],
            ['ong_id' => null, 'pessoa_id' => $independente->pessoa_id, 'responsavel_pessoa_id' => $independente->pessoa_id, 'destinatario' => $independente],
        ];

        foreach ($cenarios as $cenario) {
            $evento = new Evento([
                'nm_evento' => 'Mutirão',
                'descricao_evento' => 'Mutirão comunitário.',
                'vagas_evento' => 10,
                'modalidade_evento' => 'online',
                'imagem_evento_link' => 'evento.jpg',
                'cat_evento_id' => $categoria->cat_evento_id,
                'status_evento' => 'ativo',
            ]);
            $evento->forceFill([
                'ong_id' => $cenario['ong_id'],
                'pessoa_id' => $cenario['pessoa_id'],
                'responsavel_pessoa_id' => $cenario['responsavel_pessoa_id'],
            ])->save();
            $agenda = Agenda::create([
                'evento_id' => $evento->evento_id,
                'status_ativo' => 'ativo',
                'data_inicio' => now()->addDays(2),
                'data_fim' => now()->addDays(2)->addHours(2),
                'vagas_agenda' => 10,
            ]);

            Sanctum::actingAs($voluntario, ['pessoa']);
            $this->postJson("/api/v1/agendas/{$agenda->agenda_id}/inscricoes")
                ->assertCreated();
            $this->assertTrue($cenario['destinatario']->notificacoes()
                ->where('evento_id', $evento->evento_id)
                ->where('tipo_notificacao', 'inscricao.criada')
                ->exists());
            if ($cenario['responsavel_pessoa_id'] !== null && $cenario['ong_id'] !== null) {
                $this->assertFalse($ong->notificacoes()
                    ->where('evento_id', $evento->evento_id)
                    ->where('tipo_notificacao', 'inscricao.criada')
                    ->exists());
            }
        }
    }
}
