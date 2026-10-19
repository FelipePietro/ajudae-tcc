<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\CatEvento;
use App\Models\Causa;
use App\Models\Evento;
use App\Models\Habilidade;
use App\Models\Inscricao;
use App\Models\Ong;
use App\Models\Pessoa;
use App\Models\Recurso;
use App\Models\VinculoOngOrganizador;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EventoOperacionalTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 1000;

    public function test_aprovacao_administrativa_e_reprovacao_respeitam_permissao_estado_e_auditoria(): void
    {
        $owner = $this->pessoa('organizador');
        Sanctum::actingAs($owner, ['pessoa']);
        $id = $this->postJson('/api/v1/pessoas/eventos', $this->payloadEvento())
            ->assertCreated()->assertJsonPath('data.status_evento', 'aguardando a confirmação')
            ->json('data.evento_id');
        $this->getJson('/api/v1/eventos')->assertJsonMissing(['evento_id' => $id]);
        $this->getJson("/api/v1/eventos/{$id}")->assertNotFound();

        $semPermissao = $this->pessoa('administrador');
        Sanctum::actingAs($semPermissao, ['pessoa']);
        $this->patchJson("/api/v1/eventos/{$id}/status", ['status_evento' => 'ativo'])->assertForbidden();
        $voluntario = $this->pessoa('voluntario');
        $this->grantPermission($voluntario, 'eventos.aprovar');
        Sanctum::actingAs($voluntario, ['pessoa']);
        $this->patchJson("/api/v1/eventos/{$id}/status", ['status_evento' => 'ativo'])->assertForbidden();

        $admin = $this->pessoa('administrador');
        $this->grantPermission($admin, 'eventos.aprovar');
        Sanctum::actingAs($admin, ['pessoa']);
        $this->getJson('/api/v1/eventos/admin/pendentes')->assertOk()->assertJsonFragment(['evento_id' => $id]);
        foreach (['cancelado', 'finalizado'] as $invalido) {
            $this->patchJson("/api/v1/eventos/{$id}/status", ['status_evento' => $invalido])->assertUnprocessable();
        }
        $this->patchJson("/api/v1/eventos/{$id}/status", ['status_evento' => 'ativo'])
            ->assertOk()->assertJsonPath('data.status_evento', 'ativo');
        $this->getJson("/api/v1/eventos/{$id}")->assertOk();
        $this->patchJson("/api/v1/eventos/{$id}/status", ['status_evento' => 'reprovado'])->assertStatus(409);
        $this->assertDatabaseHas('admin_log', ['recurso_id' => $id, 'acao' => 'evento.aprovado']);
        $this->assertTrue($owner->notificacoes()->where('evento_id', $id)->exists());

        Sanctum::actingAs($owner, ['pessoa']);
        $reprovadoId = $this->postJson('/api/v1/pessoas/eventos', $this->payloadEvento())
            ->assertCreated()->json('data.evento_id');
        $master = $this->pessoa('administrador');
        $master->forceFill(['admin_master' => true])->save();
        Sanctum::actingAs($master, ['pessoa']);
        $this->patchJson("/api/v1/eventos/{$reprovadoId}/status", ['status_evento' => 'reprovado'])
            ->assertOk()->assertJsonPath('data.status_evento', 'reprovado');
        $this->assertDatabaseHas('admin_log', ['recurso_id' => $reprovadoId, 'acao' => 'evento.reprovado']);
        $this->getJson("/api/v1/eventos/{$reprovadoId}")->assertNotFound();
        Sanctum::actingAs($owner, ['pessoa']);
        $masterId = $this->postJson('/api/v1/pessoas/eventos', $this->payloadEvento())
            ->assertCreated()->json('data.evento_id');
        Sanctum::actingAs($master, ['pessoa']);
        $this->patchJson("/api/v1/eventos/{$masterId}/status", ['status_evento' => 'ativo'])->assertOk();
    }

    public function test_alteracoes_de_evento_notificam_uma_vez_e_so_sensiveis_exigem_reaprovacao(): void
    {
        $owner = $this->pessoa('organizador');
        $voluntario = $this->pessoa();
        $reprovado = $this->pessoa();
        $evento = $this->evento($owner);
        $a = $this->agenda($evento);
        $b = $this->agenda($evento);
        $this->inscricao($a, $voluntario);
        $this->inscricao($b, $voluntario);
        $this->inscricao($a, $reprovado, 'reprovado');
        Sanctum::actingAs($owner, ['pessoa']);

        $this->patchJson("/api/v1/eventos/{$evento->evento_id}", ['nm_evento' => 'Novo título'])
            ->assertOk()->assertJsonPath('data.status_evento', 'ativo');
        $this->assertSame(1, $voluntario->notificacoes()->where('tipo_notificacao', 'evento.alterado')->count());
        $this->assertFalse($reprovado->notificacoes()->exists());
        $this->assertNull($this->inscricaoAtual($a, $voluntario)->desistencia_sem_penalidade_ate);

        $this->patchJson("/api/v1/eventos/{$evento->evento_id}", ['imagem_evento_link' => 'novo.jpg'])
            ->assertOk()->assertJsonPath('data.status_evento', 'ativo');
        $this->patchJson("/api/v1/eventos/{$evento->evento_id}", ['descricao_evento' => 'Descrição alterada'])
            ->assertOk()->assertJsonPath('data.status_evento', 'aguardando a confirmação');
        $this->assertSame(3, $voluntario->notificacoes()->where('tipo_notificacao', 'evento.alterado')->count());
        $this->assertSame(2, $evento->agendas()->count());
        $this->assertSame(3, Inscricao::whereHas('agenda', fn ($q) => $q->where('evento_id', $evento->evento_id))->count());
        $this->getJson("/api/v1/eventos/{$evento->evento_id}")->assertNotFound();
        $this->getJson("/api/v1/agendas/{$a->agenda_id}")->assertNotFound();
        $this->getJson('/api/v1/agendas')->assertJsonMissing(['agenda_id' => $a->agenda_id]);
        Sanctum::actingAs($this->pessoa(), ['pessoa']);
        $this->postJson("/api/v1/agendas/{$a->agenda_id}/inscricoes")->assertStatus(409);

        $admin = $this->pessoa('administrador');
        $this->grantPermission($admin, 'eventos.aprovar');
        Sanctum::actingAs($admin, ['pessoa']);
        $this->patchJson("/api/v1/eventos/{$evento->evento_id}/status", ['status_evento' => 'ativo'])->assertOk();
        Sanctum::actingAs($owner, ['pessoa']);
        $this->patchJson("/api/v1/eventos/{$evento->evento_id}", [
            'logradouro_evento' => 'Nova rua', 'cep_evento' => '01001000',
        ])->assertOk()->assertJsonPath('data.status_evento', 'aguardando a confirmação');
        $this->assertNotNull($this->inscricaoAtual($a, $voluntario)->desistencia_sem_penalidade_ate);
        $this->assertNotNull($this->inscricaoAtual($b, $voluntario)->desistencia_sem_penalidade_ate);
    }

    public function test_associacoes_e_responsavel_operacional_disparam_reaprovacao(): void
    {
        $ong = $this->ong();
        $organizador = $this->pessoa('organizador');
        VinculoOngOrganizador::create([
            'ong_id' => $ong->ong_id, 'pessoa_id' => $organizador->pessoa_id,
            'status' => 'ativo', 'convidado_em' => now(), 'respondido_em' => now(),
        ]);
        $evento = $this->evento($ong);
        Sanctum::actingAs($ong, ['ong']);
        $this->patchJson("/api/v1/ongs/me/eventos/{$evento->evento_id}/responsavel", [
            'pessoa_id' => $organizador->pessoa_id,
        ])->assertOk()->assertJsonPath('data.status_evento', 'aguardando a confirmação');

        $habilidade = Habilidade::create(['nome_habilidade' => 'Ensino', 'descricao_habilidade' => 'Ensino']);
        $causa = Causa::create(['nome_causa' => 'Educação', 'descricao_causa' => 'Educação']);
        $recurso = Recurso::create(['nome_recurso' => 'Computador', 'descricao_recurso' => 'Computador']);
        foreach ([
            ["/api/v1/eventos/{$evento->evento_id}/habilidades", 'habilidade_id', $habilidade->habilidade_id],
            ["/api/v1/eventos/{$evento->evento_id}/causas", 'causa_id', $causa->causa_id],
            ["/api/v1/eventos/{$evento->evento_id}/recursos", 'recurso_id', $recurso->recurso_id],
        ] as [$rota, $campo, $valor]) {
            Evento::whereKey($evento->evento_id)->update(['status_evento' => 'ativo']);
            $this->postJson($rota, [$campo => $valor])->assertCreated();
            $this->assertSame('aguardando a confirmação', $evento->fresh()->status_evento);
        }
        $this->assertDatabaseHas('evento_causa', ['evento_id' => $evento->evento_id, 'causa_id' => $causa->causa_id]);
        $this->assertDatabaseHas('evento_recurso', ['evento_id' => $evento->evento_id, 'recurso_id' => $recurso->recurso_id]);
    }

    public function test_agenda_exige_vagas_e_trinta_minutos_e_estrutura_dispara_reaprovacao(): void
    {
        $owner = $this->pessoa('organizador');
        $evento = $this->evento($owner);
        Sanctum::actingAs($owner, ['pessoa']);
        $inicio = now()->addDays(2);
        $base = ['data_inicio' => $inicio, 'data_fim' => $inicio->copy()->addMinutes(30)];
        $this->postJson("/api/v1/eventos/{$evento->evento_id}/agendas", $base)
            ->assertUnprocessable()->assertJsonValidationErrors('vagas_agenda');
        $this->postJson("/api/v1/eventos/{$evento->evento_id}/agendas", [
            ...$base, 'vagas_agenda' => 0,
        ])->assertUnprocessable();
        $this->postJson("/api/v1/eventos/{$evento->evento_id}/agendas", [
            'data_inicio' => $inicio, 'data_fim' => $inicio->copy()->addMinutes(29), 'vagas_agenda' => 2,
        ])->assertUnprocessable();
        $agendaId = $this->postJson("/api/v1/eventos/{$evento->evento_id}/agendas", [
            ...$base, 'vagas_agenda' => 2,
        ])->assertCreated()->json('data.agenda_id');
        $this->assertSame('aguardando a confirmação', $evento->fresh()->status_evento);

        Evento::whereKey($evento->evento_id)->update(['status_evento' => 'ativo']);
        $this->patchJson("/api/v1/agendas/{$agendaId}", ['data_inicio' => $inicio->copy()->addHour(), 'data_fim' => $inicio->copy()->addHours(2)])
            ->assertOk();
        $this->assertSame('aguardando a confirmação', $evento->fresh()->status_evento);
        Evento::whereKey($evento->evento_id)->update(['status_evento' => 'ativo']);
        $this->patchJson("/api/v1/agendas/{$agendaId}", ['data_fim' => $inicio->copy()->addHours(3)])->assertOk();
        $this->assertSame('aguardando a confirmação', $evento->fresh()->status_evento);
        Evento::whereKey($evento->evento_id)->update(['status_evento' => 'ativo']);
        $this->patchJson("/api/v1/agendas/{$agendaId}", ['vagas_agenda' => 3])->assertOk();
        $this->assertSame('aguardando a confirmação', $evento->fresh()->status_evento);
        Evento::whereKey($evento->evento_id)->update(['status_evento' => 'ativo']);
        $this->deleteJson("/api/v1/agendas/{$agendaId}")->assertNoContent();
        $this->assertSame('aguardando a confirmação', $evento->fresh()->status_evento);

        $outra = $this->agenda($evento);
        $this->inscricao($outra, $this->pessoa());
        $this->deleteJson("/api/v1/agendas/{$outra->agenda_id}")->assertStatus(409);
    }

    public function test_capacidade_da_agenda_pendentes_e_multiplas_agendas(): void
    {
        $owner = $this->pessoa('organizador');
        $evento = $this->evento($owner);
        $a = $this->agenda($evento, 3);
        $b = $this->agenda($evento, 1);
        $pessoas = [];
        for ($i = 0; $i < 11; $i++) {
            $pessoas[] = $this->pessoa();
            Sanctum::actingAs($pessoas[$i], ['pessoa']);
            $this->postJson("/api/v1/agendas/{$a->agenda_id}/inscricoes")->assertCreated();
        }
        $this->assertSame(11, $a->inscricoes()->where('status_inscricao', 'pendente')->count());
        Sanctum::actingAs($pessoas[0], ['pessoa']);
        $this->postJson("/api/v1/agendas/{$b->agenda_id}/inscricoes")->assertCreated();
        $this->postJson("/api/v1/agendas/{$b->agenda_id}/inscricoes")->assertStatus(409);

        Sanctum::actingAs($owner, ['pessoa']);
        $ids = $a->inscricoes()->orderBy('inscricao_id')->pluck('inscricao_id');
        $this->patchJson("/api/v1/inscricoes/{$ids[0]}/status", ['status_inscricao' => 'aprovado'])->assertOk();
        $this->patchJson("/api/v1/inscricoes/{$ids[1]}/status", ['status_inscricao' => 'aprovado'])->assertOk();
        $this->patchJson("/api/v1/agendas/{$a->agenda_id}", ['vagas_agenda' => 1])->assertStatus(409);
        $this->patchJson("/api/v1/agendas/{$a->agenda_id}", ['vagas_agenda' => 2])->assertOk();
        $this->assertSame(2, $a->fresh()->vagas_agenda);
        $this->patchJson("/api/v1/inscricoes/{$ids[2]}/status", ['status_inscricao' => 'aprovado'])->assertStatus(409);
        $this->patchJson("/api/v1/inscricoes/{$ids[2]}/status", ['status_inscricao' => 'reprovado'])->assertOk();

        $novo = $this->pessoa();
        Sanctum::actingAs($novo, ['pessoa']);
        Evento::whereKey($evento->evento_id)->update(['status_evento' => 'ativo']);
        $this->postJson("/api/v1/agendas/{$a->agenda_id}/inscricoes")->assertStatus(409);
        $this->postJson("/api/v1/agendas/{$b->agenda_id}/inscricoes")->assertCreated();
    }

    public function test_candidatura_fecha_exatamente_uma_hora_antes(): void
    {
        Carbon::setTestNow('2026-10-12 18:59:59');
        try {
            $evento = $this->evento($this->pessoa('organizador'));
            $agenda = $this->agenda($evento, 3, Carbon::parse('2026-10-12 20:00:00'));
            Sanctum::actingAs($this->pessoa(), ['pessoa']);
            $this->postJson("/api/v1/agendas/{$agenda->agenda_id}/inscricoes")->assertCreated();
            Carbon::setTestNow('2026-10-12 19:00:00');
            Sanctum::actingAs($this->pessoa(), ['pessoa']);
            $this->postJson("/api/v1/agendas/{$agenda->agenda_id}/inscricoes")->assertStatus(409);
            Carbon::setTestNow('2026-10-12 19:01:00');
            Sanctum::actingAs($this->pessoa(), ['pessoa']);
            $this->postJson("/api/v1/agendas/{$agenda->agenda_id}/inscricoes")->assertStatus(409);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_match_conta_apenas_ids_compativeis_e_nao_decide_candidatura(): void
    {
        $owner = $this->pessoa('organizador');
        $evento = $this->evento($owner);
        $agenda = $this->agenda($evento, 2);
        $pessoa = $this->pessoa();
        for ($i = 0; $i < 3; $i++) {
            $habilidade = Habilidade::create(['nome_habilidade' => "H{$i}", 'descricao_habilidade' => "H{$i}"]);
            $evento->habilidades()->attach($habilidade->habilidade_id);
            if ($i < 2) {
                $pessoa->habilidades()->attach($habilidade->habilidade_id, ['nivel_habilidade' => 1]);
            }
        }
        for ($i = 0; $i < 2; $i++) {
            $causa = Causa::create(['nome_causa' => "C{$i}", 'descricao_causa' => "C{$i}"]);
            $recurso = Recurso::create(['nome_recurso' => "R{$i}", 'descricao_recurso' => "R{$i}"]);
            $evento->causas()->attach($causa->causa_id);
            $evento->recursos()->attach($recurso->recurso_id);
            if ($i === 0) {
                $pessoa->causas()->attach($causa->causa_id);
            }
        }
        $pessoa->forceFill(['avaliacao_pessoa' => 5])->save();
        Sanctum::actingAs($pessoa, ['pessoa']);
        $this->postJson("/api/v1/agendas/{$agenda->agenda_id}/inscricoes")->assertCreated();
        $zero = $this->pessoa();
        Sanctum::actingAs($zero, ['pessoa']);
        $this->postJson("/api/v1/agendas/{$agenda->agenda_id}/inscricoes")->assertCreated();

        Sanctum::actingAs($owner, ['pessoa']);
        $response = $this->getJson("/api/v1/agendas/{$agenda->agenda_id}/inscritos")->assertOk();
        $candidatos = collect($response->json('data'))->keyBy('pessoa_id');
        $this->assertSame(['habilidades' => 2, 'causas' => 1, 'recursos' => 0, 'total' => 3], $candidatos[$pessoa->pessoa_id]['match']);
        $this->assertSame(['habilidades' => 0, 'causas' => 0, 'recursos' => 0, 'total' => 0], $candidatos[$zero->pessoa_id]['match']);
        $this->assertSame('pendente', $candidatos[$pessoa->pessoa_id]['status_inscricao']);
        $this->assertArrayNotHasKey('cpf_pessoa', $candidatos[$pessoa->pessoa_id]['pessoa']);
        $this->assertArrayNotHasKey('rg_pessoa', $candidatos[$pessoa->pessoa_id]['pessoa']);
    }

    public function test_mudanca_de_agenda_concede_desistencia_sem_penalidade_apenas_a_inscritos_anteriores(): void
    {
        $owner = $this->pessoa('organizador');
        $evento = $this->evento($owner);
        $agenda = $this->agenda($evento, 4, now()->addHours(12));
        $anterior = $this->pessoa();
        $inscricao = $this->inscricao($agenda, $anterior);
        Sanctum::actingAs($owner, ['pessoa']);
        $inicioNovo = now()->addHours(13);
        $this->patchJson("/api/v1/agendas/{$agenda->agenda_id}", [
            'data_inicio' => $inicioNovo,
            'data_fim' => $inicioNovo->copy()->addHours(2),
        ])->assertOk();
        $this->assertSame('aguardando a confirmação', $evento->fresh()->status_evento);
        $this->assertNotNull($inscricao->fresh()->desistencia_sem_penalidade_ate);
        $this->assertTrue($anterior->notificacoes()->where('tipo_notificacao', 'agenda.alterada')->exists());

        Evento::whereKey($evento->evento_id)->update(['status_evento' => 'ativo']);
        $posterior = $this->pessoa();
        Sanctum::actingAs($posterior, ['pessoa']);
        $novoId = $this->postJson("/api/v1/agendas/{$agenda->agenda_id}/inscricoes")
            ->assertCreated()->json('data.inscricao_id');
        $this->assertNull(Inscricao::findOrFail($novoId)->desistencia_sem_penalidade_ate);

        Sanctum::actingAs($anterior, ['pessoa']);
        $this->deleteJson("/api/v1/inscricoes/{$inscricao->inscricao_id}")
            ->assertOk()->assertJsonPath('data.desistencia_tardia', true)
            ->assertJsonPath('data.desistencia_sem_penalidade', true);
        $this->assertDatabaseHas('p_ag_inscreve', ['inscricao_id' => $inscricao->inscricao_id, 'status_inscricao' => 'desistente']);
    }

    public function test_cancelar_agenda_preserva_inscricoes_e_nao_exige_admin(): void
    {
        $owner = $this->pessoa('organizador');
        $outro = $this->pessoa('organizador');
        $voluntario = $this->pessoa();
        $evento = $this->evento($owner);
        $agenda = $this->agenda($evento);
        $inscricao = $this->inscricao($agenda, $voluntario);
        Sanctum::actingAs($outro, ['pessoa']);
        $this->postJson("/api/v1/agendas/{$agenda->agenda_id}/cancelar", ['motivo' => 'Imprevisto'])->assertForbidden();
        Sanctum::actingAs($owner, ['pessoa']);
        $this->postJson("/api/v1/agendas/{$agenda->agenda_id}/cancelar", [])->assertUnprocessable();
        $this->postJson("/api/v1/agendas/{$agenda->agenda_id}/cancelar", ['motivo' => 'Imprevisto'])
            ->assertOk()->assertJsonPath('data.status_ativo', 'cancelado');
        $this->assertSame('ativo', $evento->fresh()->status_evento);
        $this->assertDatabaseHas('p_ag_inscreve', ['inscricao_id' => $inscricao->inscricao_id]);
        $this->assertDatabaseHas('agenda', [
            'agenda_id' => $agenda->agenda_id, 'motivo_cancelamento' => 'Imprevisto',
            'cancelado_por_tipo' => 'pessoa', 'cancelado_por_id' => $owner->pessoa_id,
        ]);
        $this->assertTrue($voluntario->notificacoes()->where('tipo_notificacao', 'agenda.cancelada')->exists());
        $this->patchJson("/api/v1/inscricoes/{$inscricao->inscricao_id}/status", [
            'status_inscricao' => 'aprovado',
        ])->assertStatus(409);
        $this->patchJson("/api/v1/inscricoes/{$inscricao->inscricao_id}/presenca", [
            'presenca' => true,
        ])->assertStatus(409);
        Sanctum::actingAs($voluntario, ['pessoa']);
        $this->deleteJson("/api/v1/inscricoes/{$inscricao->inscricao_id}")
            ->assertOk()->assertJsonPath('data.desistencia_sem_penalidade', true);
        Sanctum::actingAs($this->pessoa(), ['pessoa']);
        $this->postJson("/api/v1/agendas/{$agenda->agenda_id}/inscricoes")->assertStatus(409);
    }

    public function test_cancelar_evento_preserva_historico_agendas_terminais_e_notifica_sem_duplicar(): void
    {
        $ong = $this->ong();
        $voluntario = $this->pessoa();
        $evento = $this->evento($ong);
        $a = $this->agenda($evento);
        $b = $this->agenda($evento);
        $finalizada = $this->agenda($evento);
        $finalizada->update(['status_ativo' => 'finalizado']);
        $this->inscricao($a, $voluntario);
        $this->inscricao($b, $voluntario);
        Sanctum::actingAs($ong, ['ong']);
        $this->postJson("/api/v1/eventos/{$evento->evento_id}/cancelar", [])->assertUnprocessable();
        $this->postJson("/api/v1/eventos/{$evento->evento_id}/cancelar", ['motivo' => 'Sem local'])
            ->assertOk()->assertJsonPath('data.status_evento', 'cancelado');
        $this->assertSame('cancelado', $a->fresh()->status_ativo);
        $this->assertSame('cancelado', $b->fresh()->status_ativo);
        $this->assertSame('finalizado', $finalizada->fresh()->status_ativo);
        $this->assertSame(2, Inscricao::whereHas('agenda', fn ($q) => $q->where('evento_id', $evento->evento_id))->count());
        $this->assertSame(1, $voluntario->notificacoes()->where('tipo_notificacao', 'evento.cancelado')->count());
        $this->assertDatabaseHas('evento', [
            'evento_id' => $evento->evento_id, 'motivo_cancelamento' => 'Sem local',
            'cancelado_por_tipo' => 'ong', 'cancelado_por_id' => $ong->ong_id,
        ]);
        $this->getJson("/api/v1/eventos/{$evento->evento_id}")->assertNotFound();
        Sanctum::actingAs($this->pessoa(), ['pessoa']);
        $this->postJson("/api/v1/agendas/{$a->agenda_id}/inscricoes")->assertStatus(409);
        $this->assertSame('cancelado', $evento->fresh()->status_evento);
    }

    public function test_organizador_independente_e_responsavel_podem_cancelar_mas_outro_nao(): void
    {
        $independente = $this->pessoa('organizador');
        $eventoProprio = $this->evento($independente);
        Sanctum::actingAs($independente, ['pessoa']);
        $this->postJson("/api/v1/eventos/{$eventoProprio->evento_id}/cancelar", ['motivo' => 'Imprevisto'])
            ->assertOk()->assertJsonPath('data.status_evento', 'cancelado');

        $ong = $this->ong();
        $responsavel = $this->pessoa('organizador');
        $outro = $this->pessoa('organizador');
        foreach ([$responsavel, $outro] as $organizador) {
            VinculoOngOrganizador::create([
                'ong_id' => $ong->ong_id, 'pessoa_id' => $organizador->pessoa_id,
                'status' => 'ativo', 'convidado_em' => now(), 'respondido_em' => now(),
            ]);
        }
        $eventoOng = $this->evento($ong);
        $eventoOng->forceFill(['responsavel_pessoa_id' => $responsavel->pessoa_id])->save();
        Sanctum::actingAs($outro, ['pessoa']);
        $this->postJson("/api/v1/eventos/{$eventoOng->evento_id}/cancelar", ['motivo' => 'Sem local'])
            ->assertForbidden();
        Sanctum::actingAs($responsavel, ['pessoa']);
        $this->postJson("/api/v1/eventos/{$eventoOng->evento_id}/cancelar", ['motivo' => 'Sem local'])
            ->assertOk()->assertJsonPath('data.status_evento', 'cancelado');
        $this->assertDatabaseHas('evento', [
            'evento_id' => $eventoOng->evento_id,
            'cancelado_por_tipo' => 'pessoa',
            'cancelado_por_id' => $responsavel->pessoa_id,
        ]);
    }

    public function test_evento_reprovado_e_reenviado_por_edicao_e_reaprovado_no_mesmo_registro(): void
    {
        $owner = $this->pessoa('organizador');
        $outro = $this->pessoa('organizador');
        $admin = $this->pessoa('administrador');
        $this->grantPermission($admin, 'eventos.aprovar');
        Sanctum::actingAs($owner, ['pessoa']);
        $id = $this->postJson('/api/v1/pessoas/eventos', $this->payloadEvento())
            ->assertCreated()->assertJsonPath('data.status_evento', 'aguardando a confirmação')
            ->json('data.evento_id');

        Sanctum::actingAs($admin, ['pessoa']);
        $this->patchJson("/api/v1/eventos/{$id}/status", ['status_evento' => 'reprovado'])->assertOk();
        $this->getJson("/api/v1/eventos/{$id}")->assertNotFound();
        Sanctum::actingAs($outro, ['pessoa']);
        $this->patchJson("/api/v1/eventos/{$id}", ['nm_evento' => 'Correção indevida'])->assertForbidden();

        Sanctum::actingAs($owner, ['pessoa']);
        $this->patchJson("/api/v1/eventos/{$id}", ['nm_evento' => 'Título corrigido'])
            ->assertOk()->assertJsonPath('data.status_evento', 'aguardando a confirmação');
        $this->assertSame($id, Evento::findOrFail($id)->evento_id);
        $this->getJson("/api/v1/eventos/{$id}")->assertNotFound();
        $this->getJson('/api/v1/eventos')->assertJsonMissing(['evento_id' => $id]);

        Sanctum::actingAs($admin, ['pessoa']);
        $this->patchJson("/api/v1/eventos/{$id}/status", ['status_evento' => 'ativo'])->assertOk();
        $this->getJson("/api/v1/eventos/{$id}")->assertOk();
        $this->assertSame(1, Evento::whereKey($id)->count());

        $outroReprovado = $this->evento($owner);
        $outroReprovado->forceFill(['status_evento' => 'reprovado'])->save();
        Sanctum::actingAs($owner, ['pessoa']);
        $this->patchJson("/api/v1/eventos/{$outroReprovado->evento_id}", ['imagem_evento_link' => 'banner-corrigido.jpg'])
            ->assertOk()->assertJsonPath('data.status_evento', 'aguardando a confirmação');
    }

    public function test_eventos_terminais_nao_podem_ser_alterados_excluidos_reaprovados_ou_receber_agenda_e_associacoes(): void
    {
        $owner = $this->pessoa('organizador');
        $admin = $this->pessoa('administrador');
        $this->grantPermission($admin, 'eventos.aprovar');
        $habilidade = Habilidade::create(['nome_habilidade' => 'Logística', 'descricao_habilidade' => 'Logística']);
        $causa = Causa::create(['nome_causa' => 'Saúde', 'descricao_causa' => 'Saúde']);
        $recurso = Recurso::create(['nome_recurso' => 'Ônibus', 'descricao_recurso' => 'Ônibus']);

        foreach (['cancelado', 'finalizado'] as $status) {
            $evento = $this->evento($owner);
            $evento->habilidades()->attach($habilidade->habilidade_id);
            $evento->causas()->attach($causa->causa_id);
            $evento->recursos()->attach($recurso->recurso_id);
            $evento->forceFill(['status_evento' => $status])->save();
            $id = $evento->evento_id;
            Sanctum::actingAs($owner, ['pessoa']);
            $this->patchJson("/api/v1/eventos/{$id}", ['nm_evento' => 'Mutação proibida'])->assertStatus(409);
            $this->patchJson("/api/v1/eventos/{$id}", ['vagas_evento' => 20])->assertStatus(409);
            $this->deleteJson("/api/v1/eventos/{$id}")->assertStatus(409);
            $inicio = now()->addDays(2);
            $this->postJson("/api/v1/eventos/{$id}/agendas", [
                'data_inicio' => $inicio, 'data_fim' => $inicio->copy()->addHour(), 'vagas_agenda' => 2,
            ])->assertStatus(409);
            foreach ([
                ['habilidades', 'habilidade_id', $habilidade->habilidade_id],
                ['causas', 'causa_id', $causa->causa_id],
                ['recursos', 'recurso_id', $recurso->recurso_id],
            ] as [$relacao, $campo, $valor]) {
                $this->postJson("/api/v1/eventos/{$id}/{$relacao}", [$campo => $valor])->assertStatus(409);
                $this->deleteJson("/api/v1/eventos/{$id}/{$relacao}/{$valor}")->assertStatus(409);
            }
            Sanctum::actingAs($admin, ['pessoa']);
            $this->patchJson("/api/v1/eventos/{$id}/status", ['status_evento' => 'ativo'])->assertStatus(409);
            $this->assertDatabaseHas('evento', ['evento_id' => $id, 'status_evento' => $status, 'nm_evento' => 'Mutirão']);
            $this->assertSame(0, $evento->agendas()->count());
            $this->assertSame(1, $evento->habilidades()->count());
            $this->assertSame(1, $evento->causas()->count());
            $this->assertSame(1, $evento->recursos()->count());
        }
    }

    public function test_evento_terminal_nao_pode_trocar_nem_remover_responsavel_operacional(): void
    {
        $ong = $this->ong();
        $responsavel = $this->pessoa('organizador');
        $substituto = $this->pessoa('organizador');
        foreach ([$responsavel, $substituto] as $pessoa) {
            VinculoOngOrganizador::create([
                'ong_id' => $ong->ong_id, 'pessoa_id' => $pessoa->pessoa_id,
                'status' => 'ativo', 'convidado_em' => now(), 'respondido_em' => now(),
            ]);
        }
        Sanctum::actingAs($ong, ['ong']);
        foreach (['cancelado', 'finalizado'] as $status) {
            $evento = $this->evento($ong);
            $evento->forceFill([
                'status_evento' => $status, 'responsavel_pessoa_id' => $responsavel->pessoa_id,
            ])->save();
            foreach ([$substituto->pessoa_id, null] as $novoResponsavel) {
                $this->patchJson("/api/v1/ongs/me/eventos/{$evento->evento_id}/responsavel", [
                    'pessoa_id' => $novoResponsavel,
                ])->assertStatus(409);
            }
            $this->assertSame($responsavel->pessoa_id, $evento->fresh()->responsavel_pessoa_id);
        }
    }

    public function test_agendas_terminais_nao_podem_ser_editadas_excluidas_ou_reativadas(): void
    {
        $owner = $this->pessoa('organizador');
        $evento = $this->evento($owner);
        Sanctum::actingAs($owner, ['pessoa']);
        foreach (['cancelado', 'finalizado'] as $status) {
            $agenda = $this->agenda($evento);
            $agenda->forceFill(['status_ativo' => $status])->save();
            $id = $agenda->agenda_id;
            $this->patchJson("/api/v1/agendas/{$id}", ['vagas_agenda' => 99])->assertStatus(409);
            $this->deleteJson("/api/v1/agendas/{$id}")->assertStatus(409);
            $this->patchJson("/api/v1/agendas/{$id}/ativar")->assertStatus(409);
            $this->assertDatabaseHas('agenda', ['agenda_id' => $id, 'status_ativo' => $status, 'vagas_agenda' => 10]);
        }
        $ativa = $this->agenda($evento);
        $this->patchJson("/api/v1/agendas/{$ativa->agenda_id}/ativar")->assertStatus(409);
        $this->assertSame('ativo', $ativa->fresh()->status_ativo);
    }

    public function test_evento_terminal_tambem_bloqueia_operacoes_em_agenda_ainda_ativa(): void
    {
        $owner = $this->pessoa('organizador');
        $evento = $this->evento($owner);
        $agenda = $this->agenda($evento);
        $evento->forceFill(['status_evento' => 'finalizado'])->save();
        Sanctum::actingAs($owner, ['pessoa']);

        $this->patchJson("/api/v1/agendas/{$agenda->agenda_id}", ['vagas_agenda' => 20])->assertStatus(409);
        $this->deleteJson("/api/v1/agendas/{$agenda->agenda_id}")->assertStatus(409);
        $this->patchJson("/api/v1/agendas/{$agenda->agenda_id}/finalizar")->assertStatus(409);
        $this->postJson("/api/v1/agendas/{$agenda->agenda_id}/cancelar", ['motivo' => 'Teste'])->assertStatus(409);
        $this->assertDatabaseHas('agenda', [
            'agenda_id' => $agenda->agenda_id, 'status_ativo' => 'ativo', 'vagas_agenda' => 10,
        ]);
    }

    public function test_edicao_estrutural_em_evento_reprovado_reenvia_sem_ativar(): void
    {
        $owner = $this->pessoa('organizador');
        $evento = $this->evento($owner);
        $evento->forceFill(['status_evento' => 'reprovado'])->save();
        Sanctum::actingAs($owner, ['pessoa']);
        $inicio = now()->addDays(2);
        $this->postJson("/api/v1/eventos/{$evento->evento_id}/agendas", [
            'data_inicio' => $inicio, 'data_fim' => $inicio->copy()->addHour(), 'vagas_agenda' => 2,
        ])->assertCreated();
        $this->assertSame('aguardando a confirmação', $evento->fresh()->status_evento);
        $this->getJson("/api/v1/eventos/{$evento->evento_id}")->assertNotFound();
    }

    public function test_vagas_evento_e_legado_sem_efeito_na_capacidade_ou_reaprovacao(): void
    {
        $owner = $this->pessoa('organizador');
        $evento = $this->evento($owner);
        $agenda = $this->agenda($evento, 2);
        Sanctum::actingAs($owner, ['pessoa']);
        $this->patchJson("/api/v1/eventos/{$evento->evento_id}", ['vagas_evento' => 1])
            ->assertOk()->assertJsonPath('data.status_evento', 'ativo');
        $this->assertSame(2, $agenda->fresh()->vagas_agenda);
        $this->assertSame('ativo', $evento->fresh()->status_evento);

        $ids = [];
        for ($i = 0; $i < 3; $i++) {
            $pessoa = $this->pessoa();
            Sanctum::actingAs($pessoa, ['pessoa']);
            $ids[] = $this->postJson("/api/v1/agendas/{$agenda->agenda_id}/inscricoes")
                ->assertCreated()->json('data.inscricao_id');
        }
        Sanctum::actingAs($owner, ['pessoa']);
        $this->patchJson("/api/v1/inscricoes/{$ids[0]}/status", ['status_inscricao' => 'aprovado'])->assertOk();
        $this->patchJson("/api/v1/inscricoes/{$ids[1]}/status", ['status_inscricao' => 'aprovado'])->assertOk();
        $this->patchJson("/api/v1/inscricoes/{$ids[2]}/status", ['status_inscricao' => 'aprovado'])->assertStatus(409);
        Sanctum::actingAs($this->pessoa(), ['pessoa']);
        $this->postJson("/api/v1/agendas/{$agenda->agenda_id}/inscricoes")->assertStatus(409);
        $this->assertSame('ativo', $evento->fresh()->status_evento);
        $this->assertSame(2, $agenda->fresh()->vagas_agenda);
    }

    private function pessoa(string $role = 'voluntario'): Pessoa
    {
        $id = $this->sequence++;

        return $this->createPessoa($role, [
            'cpf_pessoa' => str_pad((string) $id, 11, '0', STR_PAD_LEFT),
            'email_pessoa' => "pessoa{$id}@example.com",
            'login_pessoa' => "pessoa{$id}",
        ]);
    }

    private function ong(): Ong
    {
        $id = $this->sequence++;

        return $this->createOng('ativo', [
            'cnpj_ong' => str_pad((string) $id, 14, '0', STR_PAD_LEFT),
            'cpf_responsavel_ong' => str_pad((string) ($id + 1000), 11, '0', STR_PAD_LEFT),
            'email_ong' => "ong{$id}@example.com",
            'login_ong' => "ong{$id}",
        ]);
    }

    private function payloadEvento(): array
    {
        return [
            'nm_evento' => 'Mutirão', 'descricao_evento' => 'Mutirão comunitário',
            'vagas_evento' => 10, 'modalidade_evento' => 'online',
            'imagem_evento_link' => 'evento.jpg',
            'cat_evento_id' => CatEvento::firstOrCreate(['nome_categoria' => 'Educação'])->cat_evento_id,
        ];
    }

    private function evento(Pessoa|Ong $owner): Evento
    {
        $evento = new Evento([...$this->payloadEvento(), 'status_evento' => 'ativo']);
        $evento->forceFill([
            'ong_id' => $owner instanceof Ong ? $owner->ong_id : null,
            'pessoa_id' => $owner instanceof Pessoa ? $owner->pessoa_id : null,
            'responsavel_pessoa_id' => $owner instanceof Pessoa ? $owner->pessoa_id : null,
        ])->save();

        return $evento;
    }

    private function agenda(Evento $evento, int $vagas = 10, ?Carbon $inicio = null): Agenda
    {
        $inicio ??= now()->addDays(2);

        return Agenda::create([
            'evento_id' => $evento->evento_id, 'vagas_agenda' => $vagas,
            'data_inicio' => $inicio, 'data_fim' => $inicio->copy()->addHours(2),
            'status_ativo' => 'ativo',
        ]);
    }

    private function inscricao(Agenda $agenda, Pessoa $pessoa, string $status = 'pendente'): Inscricao
    {
        return Inscricao::create([
            'agenda_id' => $agenda->agenda_id, 'pessoa_id' => $pessoa->pessoa_id,
            'status_inscricao' => $status, 'dt_inscricao' => now(),
        ]);
    }

    private function inscricaoAtual(Agenda $agenda, Pessoa $pessoa): Inscricao
    {
        return Inscricao::where('agenda_id', $agenda->agenda_id)
            ->where('pessoa_id', $pessoa->pessoa_id)->firstOrFail();
    }
}
