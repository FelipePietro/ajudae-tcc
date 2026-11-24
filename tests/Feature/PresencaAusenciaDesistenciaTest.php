<?php

namespace Tests\Feature;

use App\Exceptions\ValidationConflictException;
use App\Models\Agenda;
use App\Models\CatEvento;
use App\Models\Evento;
use App\Models\Inscricao;
use App\Models\Ong;
use App\Models\Pessoa;
use App\Models\VinculoOngOrganizador;
use App\Services\MonitoriaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PresencaAusenciaDesistenciaTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 3000;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-13 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_reanalise_bloqueia_selecao_presenca_e_encerramento_preservando_consulta(): void
    {
        $owner = $this->pessoa('organizador');
        $evento = $this->evento($owner);
        $agenda = $this->agenda($evento, now()->subHours(2));
        $aprovada = $this->inscricao($agenda, $this->pessoa(), 'aprovado');
        $pendente = $this->inscricao($agenda, $this->pessoa(), 'pendente');
        $evento->forceFill(['status_evento' => 'aguardando a confirmação'])->save();
        Sanctum::actingAs($owner, ['pessoa']);

        $this->getJson("/api/v1/agendas/{$agenda->agenda_id}/inscritos")->assertOk();
        $this->patchJson("/api/v1/inscricoes/{$pendente->inscricao_id}/status", ['status_inscricao' => 'aprovado'])
            ->assertStatus(409);
        $presenca = "/api/v1/inscricoes/{$aprovada->inscricao_id}/presenca";
        $this->patchJson($presenca, ['presenca' => true, 'contexto' => 'ong'])->assertStatus(409);
        $this->patchJson("/api/v1/agendas/{$agenda->agenda_id}/finalizar")->assertStatus(409);
        $this->postJson("/api/v1/eventos/{$evento->evento_id}/finalizar")->assertStatus(409);
        $this->assertNull($aprovada->fresh()->presenca);
        $this->assertSame('pendente', $pendente->fresh()->status_inscricao);

        $evento->forceFill(['status_evento' => 'ativo'])->save();
        $this->patchJson($presenca, ['presenca' => true])->assertOk();
        $this->assertSame($owner->pessoa_id, $aprovada->fresh()->presenca_confirmada_por_id);
    }

    public function test_monitoria_pendente_permite_encerrar_e_regularizar_sem_alterar_presenca_ou_xp(): void
    {
        $owner = $this->pessoa('organizador');
        $outraPessoa = $this->pessoa();
        $estudante = $this->pessoa();
        $evento = $this->evento($owner);
        $agenda = $this->agenda($evento, now()->addHours(2));
        Sanctum::actingAs($estudante, ['pessoa']);
        $id = $this->postJson("/api/v1/agendas/{$agenda->agenda_id}/inscricoes", ['declarou_estudante' => true])
            ->assertCreated()->assertJsonPath('data.declarou_estudante', true)->json('data.inscricao_id');

        Sanctum::actingAs($owner, ['pessoa']);
        $this->patchJson("/api/v1/inscricoes/{$id}/status", ['status_inscricao' => 'aprovado'])->assertOk();
        Carbon::setTestNow($agenda->data_fim);
        $url = "/api/v1/inscricoes/{$id}/monitoria";
        $this->patchJson($url, ['confirmada' => true])->assertStatus(409);
        $this->patchJson("/api/v1/inscricoes/{$id}/presenca", ['presenca' => true])->assertOk();
        $this->getJson($url)->assertOk()->assertJsonPath('data.status', 'pendente');
        $this->patchJson("/api/v1/agendas/{$agenda->agenda_id}/finalizar")->assertOk();
        $this->assertDatabaseCount('xp_transacao', 1);
        $this->postJson("/api/v1/eventos/{$evento->evento_id}/avaliacoes/{$estudante->pessoa_id}", ['nota' => 5])
            ->assertCreated();
        $this->postJson("/api/v1/eventos/{$evento->evento_id}/finalizar")->assertOk();
        Sanctum::actingAs($estudante, ['pessoa']);
        $certificadoUrl = "/api/v1/eventos/{$evento->evento_id}/certificado";
        $this->postJson($certificadoUrl)->assertConflict();
        $service = app(MonitoriaService::class);
        try {
            $service->exigirValidacaoParaCertificado($evento, $estudante);
            $this->fail('O certificado deveria exigir a monitoria ainda pendente.');
        } catch (ValidationConflictException $exception) {
            $this->assertStringContainsString('monitoria estudantil', $exception->getMessage());
        }

        Sanctum::actingAs($outraPessoa, ['pessoa']);
        $this->getJson($url)->assertForbidden();
        $this->patchJson($url, ['confirmada' => true, 'justificativa' => 'Justificativa de teste.'])->assertForbidden();
        Sanctum::actingAs($owner, ['pessoa']);
        $this->patchJson($url, ['confirmada' => true])->assertUnprocessable();
        $dados = ['confirmada' => true, 'justificativa' => 'Conferência sintética da monitoria realizada.'];
        $this->patchJson($url, $dados)->assertOk()->assertJsonPath('data.status', 'validada')->assertJsonMissingPath('data.justificativa');
        $this->patchJson($url, $dados)->assertStatus(409);
        Sanctum::actingAs($estudante, ['pessoa']);
        $this->getJson($url)->assertOk()->assertJsonPath('data.status', 'validada');
        $service->exigirValidacaoParaCertificado($evento, $estudante);
        $codigo = $this->postJson($certificadoUrl)->assertOk()->json('data.codigo_verificacao');
        $this->postJson($certificadoUrl)->assertOk()->assertJsonPath('data.codigo_verificacao', $codigo);
        $this->assertDatabaseHas('monitoria', [
            'inscricao_id' => $id, 'confirmada' => true, 'validado_por_tipo' => 'pessoa',
            'validado_por_id' => $owner->pessoa_id, 'observacao' => $dados['justificativa'],
        ]);
        $this->assertDatabaseHas('p_ag_inscreve', ['inscricao_id' => $id, 'status_inscricao' => 'participou', 'presenca' => true]);
        $this->assertSame('finalizado', $agenda->fresh()->status_ativo);
        $this->assertDatabaseCount('xp_transacao', 1);
    }

    public function test_justificativa_privada_e_decisao_nao_apagam_ausencia(): void
    {
        $owner = $this->pessoa('organizador');
        $titular = $this->pessoa();
        $terceiro = $this->pessoa();
        $evento = $this->evento($owner);
        $agenda = $this->agenda($evento, now()->subHours(2));
        $inscricao = $this->inscricao($agenda, $titular, 'aprovado');
        $url = "/api/v1/inscricoes/{$inscricao->inscricao_id}/justificativa";
        Sanctum::actingAs($owner, ['pessoa']);
        $this->patchJson("/api/v1/inscricoes/{$inscricao->inscricao_id}/presenca", ['presenca' => false])->assertOk();
        $this->postJson($url, ['motivo' => 'Justificativa sintética.'])->assertForbidden();

        Sanctum::actingAs($titular, ['pessoa']);
        $this->postJson($url, ['motivo' => 'Justificativa sintética.'])->assertCreated()->assertJsonPath('data.status', 'pendente');
        $this->postJson($url, ['motivo' => 'Justificativa repetida.'])->assertStatus(409);
        $this->patchJson($url, ['deferida' => true, 'motivo' => 'Autodeferimento.'])->assertForbidden();
        Sanctum::actingAs($terceiro, ['pessoa']);
        $this->getJson($url)->assertForbidden();

        Sanctum::actingAs($owner, ['pessoa']);
        $this->getJson($url)->assertOk()->assertJsonPath('data.motivo', 'Justificativa sintética.');
        $dados = ['deferida' => true, 'motivo' => 'Revisão humana sintética concluída.'];
        $this->patchJson($url, $dados)->assertOk()->assertJsonPath('data.status', 'deferida');
        $this->patchJson($url, $dados)->assertStatus(409);
        $this->assertDatabaseHas('justificativa_participacao', [
            'inscricao_id' => $inscricao->inscricao_id, 'status' => 'deferida', 'decidido_por_id' => $owner->pessoa_id,
        ]);
        $this->assertSame('não participou', $inscricao->fresh()->status_inscricao);
        $this->assertFalse($inscricao->fresh()->presenca);
        $ocorrencia = app(\App\Services\ConfiabilidadeService::class)->detalhe($titular)['ocorrencias'][0];
        $this->assertSame('ausencia', $ocorrencia['tipo']);
        $this->assertSame(0, $ocorrencia['delta']);
        $this->assertTrue($ocorrencia['penalidade_revertida']);
    }

    public function test_presenca_e_ausencia_sao_fatos_unicos_com_duracao_da_agenda_sem_monitoria(): void
    {
        $owner = $this->pessoa('organizador');
        $evento = $this->evento($owner);
        Sanctum::actingAs($owner, ['pessoa']);

        foreach ([30 => '0.50', 45 => '0.75', 150 => '2.50'] as $minutos => $horas) {
            $agenda = $this->agenda($evento, now()->subHours(3), $minutos);
            $inscricao = $this->inscricao($agenda, $this->pessoa(), 'aprovado');
            $response = $this->patchJson("/api/v1/inscricoes/{$inscricao->inscricao_id}/presenca", [
                'presenca' => true,
            ])->assertOk()->assertJsonPath('data.status_inscricao', 'participou')
                ->assertJsonPath('data.presenca', true)
                ->assertJsonPath('data.carga_horaria', $horas);
            $this->assertNotNull($response->json('data.presenca_confirmada_em'));
            $this->assertSame('participou', $inscricao->fresh()->status_inscricao);
            $this->assertFalse($inscricao->monitoria()->exists());
            $this->patchJson("/api/v1/inscricoes/{$inscricao->inscricao_id}/presenca", ['presenca' => false])->assertStatus(409);
        }

        $agenda = $this->agenda($evento, now()->subHour());
        $inscricao = $this->inscricao($agenda, $this->pessoa(), 'participando');
        $this->patchJson("/api/v1/inscricoes/{$inscricao->inscricao_id}/presenca", ['presenca' => false])
            ->assertOk()->assertJsonPath('data.status_inscricao', 'não participou')
            ->assertJsonPath('data.presenca', false);
        $this->assertNotNull($inscricao->fresh()->presenca_confirmada_em);
        $this->assertNull($inscricao->fresh()->carga_horaria);
        $this->assertFalse($inscricao->monitoria()->exists());
        $this->patchJson("/api/v1/inscricoes/{$inscricao->inscricao_id}/presenca", ['presenca' => true])->assertStatus(409);
    }

    public function test_presenca_exige_inicio_agenda_ativa_e_inscricao_consolidada(): void
    {
        $owner = $this->pessoa('organizador');
        $evento = $this->evento($owner);
        Sanctum::actingAs($owner, ['pessoa']);

        $futura = $this->agenda($evento, now()->addHour());
        $aprovada = $this->inscricao($futura, $this->pessoa(), 'aprovado');
        $this->patchJson("/api/v1/inscricoes/{$aprovada->inscricao_id}/presenca", ['presenca' => true])->assertStatus(409);
        $this->assertNull($aprovada->fresh()->presenca_confirmada_em);

        foreach (['cancelado', 'finalizado'] as $status) {
            $agenda = $this->agenda($evento, now()->subHour());
            $inscricao = $this->inscricao($agenda, $this->pessoa(), 'aprovado');
            $agenda->forceFill(['status_ativo' => $status])->save();
            $this->patchJson("/api/v1/inscricoes/{$inscricao->inscricao_id}/presenca", ['presenca' => false])->assertStatus(409);
            $this->assertNull($inscricao->fresh()->presenca);
            $this->assertSame('aprovado', $inscricao->fresh()->status_inscricao);
        }

        $agenda = $this->agenda($evento, now()->subHour());
        foreach (['pendente', 'reprovado', 'desistente', 'participou', 'não participou'] as $status) {
            $inscricao = $this->inscricao($agenda, $this->pessoa(), $status);
            $this->patchJson("/api/v1/inscricoes/{$inscricao->inscricao_id}/presenca", ['presenca' => true])->assertStatus(409);
            $this->assertSame($status, $inscricao->fresh()->status_inscricao);
        }

        $confirmada = $this->inscricao($agenda, $this->pessoa(), 'aprovado');
        $confirmada->forceFill(['presenca_confirmada_em' => now()])->save();
        $this->patchJson("/api/v1/inscricoes/{$confirmada->inscricao_id}/presenca", ['presenca' => true])->assertStatus(409);
    }

    public function test_carga_horaria_arbitraria_e_resultado_direto_por_status_sao_rejeitados(): void
    {
        $owner = $this->pessoa('organizador');
        $agenda = $this->agenda($this->evento($owner), now()->subHour(), 45);
        $inscricao = $this->inscricao($agenda, $this->pessoa(), 'aprovado');
        Sanctum::actingAs($owner, ['pessoa']);

        $this->patchJson("/api/v1/inscricoes/{$inscricao->inscricao_id}/presenca", [
            'presenca' => true, 'carga_horaria' => 99,
        ])->assertUnprocessable()->assertJsonValidationErrors('carga_horaria');
        $this->patchJson("/api/v1/inscricoes/{$inscricao->inscricao_id}/status", [
            'status_inscricao' => 'participou',
        ])->assertUnprocessable();
        $this->patchJson("/api/v1/inscricoes/{$inscricao->inscricao_id}/status", [
            'status_inscricao' => 'não participou',
        ])->assertUnprocessable();
        $this->assertSame('aprovado', $inscricao->fresh()->status_inscricao);
        $this->assertNull($inscricao->fresh()->carga_horaria);
    }

    public function test_somente_responsavel_operacional_pode_confirmar_presenca(): void
    {
        $owner = $this->pessoa('organizador');
        $voluntario = $this->pessoa();
        $admin = $this->pessoa('administrador');
        $inscricao = $this->inscricao($this->agenda($this->evento($owner), now()->subHour()), $voluntario, 'aprovado');

        foreach ([$voluntario, $admin, $this->pessoa('organizador')] as $naoAutorizado) {
            Sanctum::actingAs($naoAutorizado, ['pessoa']);
            $this->patchJson("/api/v1/inscricoes/{$inscricao->inscricao_id}/presenca", ['presenca' => true])->assertForbidden();
        }
        Sanctum::actingAs($owner, ['pessoa']);
        $this->patchJson("/api/v1/inscricoes/{$inscricao->inscricao_id}/presenca", ['presenca' => true])->assertOk();

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
        $inscricaoOng = $this->inscricao($this->agenda($eventoOng, now()->subHour()), $this->pessoa(), 'aprovado');
        Sanctum::actingAs($outro, ['pessoa']);
        $this->patchJson("/api/v1/inscricoes/{$inscricaoOng->inscricao_id}/presenca", ['presenca' => false])->assertForbidden();
        Sanctum::actingAs($responsavel, ['pessoa']);
        $this->patchJson("/api/v1/inscricoes/{$inscricaoOng->inscricao_id}/presenca", ['presenca' => false])->assertOk();

        $inscricaoDaOng = $this->inscricao($this->agenda($eventoOng, now()->subHour()), $this->pessoa(), 'aprovado');
        Sanctum::actingAs($ong, ['ong']);
        $this->patchJson("/api/v1/inscricoes/{$inscricaoDaOng->inscricao_id}/presenca", ['presenca' => true])->assertForbidden();
    }

    public function test_desistencia_registra_limite_de_vinte_e_quatro_horas_sem_perder_historico_e_notifica_responsavel(): void
    {
        $owner = $this->pessoa('organizador');
        $evento = $this->evento($owner);
        foreach ([
            [now()->addHours(24), false],
            [now()->addHours(24)->subSecond(), true],
            [now()->addHours(5), true],
        ] as [$inicio, $tardia]) {
            $agenda = $this->agenda($evento, $inicio);
            $voluntario = $this->pessoa();
            $inscricao = $this->inscricao($agenda, $voluntario, 'aprovado');
            Sanctum::actingAs($voluntario, ['pessoa']);
            $this->deleteJson("/api/v1/inscricoes/{$inscricao->inscricao_id}")
                ->assertOk()->assertJsonPath('data.status_inscricao', 'desistente')
                ->assertJsonPath('data.desistencia_tardia', $tardia)
                ->assertJsonPath('data.desistencia_sem_penalidade', false);
            $this->assertNotNull($inscricao->fresh()->desistiu_em);
            $this->assertDatabaseHas('p_ag_inscreve', ['inscricao_id' => $inscricao->inscricao_id]);
            $this->assertSame(1, $owner->notificacoes()->where('agenda_id', $agenda->agenda_id)
                ->where('tipo_notificacao', 'inscricao.desistente')->count());
            $this->deleteJson("/api/v1/inscricoes/{$inscricao->inscricao_id}")->assertStatus(409);
        }
    }

    public function test_janela_especial_preserva_tardia_factual_e_nao_e_retroativa(): void
    {
        $owner = $this->pessoa('organizador');
        $agenda = $this->agenda($this->evento($owner), now()->addHours(5));
        $comDireito = $this->inscricao($agenda, $this->pessoa(), 'aprovado');
        $comDireito->forceFill(['desistencia_sem_penalidade_ate' => $agenda->data_inicio])->save();
        $semDireito = $this->inscricao($agenda, $this->pessoa(), 'pendente');
        foreach ([[$comDireito, true], [$semDireito, false]] as [$inscricao, $semPenalidade]) {
            Sanctum::actingAs($inscricao->pessoa, ['pessoa']);
            $this->deleteJson("/api/v1/inscricoes/{$inscricao->inscricao_id}")
                ->assertOk()->assertJsonPath('data.desistencia_tardia', true)
                ->assertJsonPath('data.desistencia_sem_penalidade', $semPenalidade);
        }
    }

    public function test_desistencia_exige_propriedade_estado_previo_e_inicio_futuro(): void
    {
        $owner = $this->pessoa('organizador');
        $evento = $this->evento($owner);
        $agenda = $this->agenda($evento, now()->addHours(5));
        $voluntario = $this->pessoa();
        $inscricao = $this->inscricao($agenda, $voluntario, 'aprovado');
        Sanctum::actingAs($this->pessoa(), ['pessoa']);
        $this->deleteJson("/api/v1/inscricoes/{$inscricao->inscricao_id}")->assertForbidden();

        foreach (['reprovado', 'participou', 'não participou'] as $status) {
            $inscricaoEstado = $this->inscricao($agenda, $this->pessoa(), $status);
            Sanctum::actingAs($inscricaoEstado->pessoa, ['pessoa']);
            $this->deleteJson("/api/v1/inscricoes/{$inscricaoEstado->inscricao_id}")->assertStatus(409);
            $this->assertSame($status, $inscricaoEstado->fresh()->status_inscricao);
        }

        Sanctum::actingAs($voluntario, ['pessoa']);
        Carbon::setTestNow($agenda->data_inicio);
        $this->deleteJson("/api/v1/inscricoes/{$inscricao->inscricao_id}")->assertStatus(409);
        $this->assertSame('aprovado', $inscricao->fresh()->status_inscricao);
    }

    public function test_desistencia_de_aprovado_libera_vaga_sem_aprovacao_automatica(): void
    {
        $owner = $this->pessoa('organizador');
        $agenda = $this->agenda($this->evento($owner), now()->addDays(2), 60, 1);
        $aprovado = $this->inscricao($agenda, $this->pessoa(), 'aprovado');
        $pendente = $this->inscricao($agenda, $this->pessoa(), 'pendente');
        Sanctum::actingAs($aprovado->pessoa, ['pessoa']);
        $this->deleteJson("/api/v1/inscricoes/{$aprovado->inscricao_id}")->assertOk();
        $this->assertSame('pendente', $pendente->fresh()->status_inscricao);
        $this->assertSame(1, $agenda->fresh()->vagas_agenda);

        $novo = $this->pessoa();
        Sanctum::actingAs($novo, ['pessoa']);
        $novoId = $this->postJson("/api/v1/agendas/{$agenda->agenda_id}/inscricoes")
            ->assertCreated()->json('data.inscricao_id');
        Sanctum::actingAs($owner, ['pessoa']);
        $this->patchJson("/api/v1/inscricoes/{$pendente->inscricao_id}/status", [
            'status_inscricao' => 'aprovado',
        ])->assertOk();
        $this->patchJson("/api/v1/inscricoes/{$novoId}/status", [
            'status_inscricao' => 'aprovado',
        ])->assertStatus(409);
    }

    public function test_cancelamento_preserva_inscricoes_sem_fabricar_ausencia(): void
    {
        $owner = $this->pessoa('organizador');
        $evento = $this->evento($owner);
        $agenda = $this->agenda($evento, now()->addHour());
        $inscricao = $this->inscricao($agenda, $this->pessoa(), 'aprovado');
        Sanctum::actingAs($owner, ['pessoa']);
        $this->postJson("/api/v1/agendas/{$agenda->agenda_id}/cancelar", ['motivo' => 'Sem local'])->assertOk();
        $this->assertDatabaseHas('p_ag_inscreve', [
            'inscricao_id' => $inscricao->inscricao_id, 'status_inscricao' => 'aprovado',
            'presenca' => null, 'presenca_confirmada_em' => null,
        ]);
        Carbon::setTestNow($agenda->data_inicio);
        $this->patchJson("/api/v1/inscricoes/{$inscricao->inscricao_id}/presenca", ['presenca' => false])->assertStatus(409);

        Carbon::setTestNow('2026-10-13 12:00:00');
        $outra = $this->agenda($evento, now()->addHour());
        $outraInscricao = $this->inscricao($outra, $this->pessoa(), 'aprovado');
        $this->postJson("/api/v1/eventos/{$evento->evento_id}/cancelar", ['motivo' => 'Imprevisto'])->assertOk();
        $this->assertSame('cancelado', $outra->fresh()->status_ativo);
        $this->assertNull($outraInscricao->fresh()->presenca);
        Carbon::setTestNow($outra->data_inicio);
        $this->patchJson("/api/v1/inscricoes/{$outraInscricao->inscricao_id}/presenca", ['presenca' => false])->assertStatus(409);
        $this->assertSame('aprovado', $outraInscricao->fresh()->status_inscricao);
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

    private function evento(Pessoa|Ong $owner): Evento
    {
        $evento = new Evento([
            'nm_evento' => 'Mutirão', 'descricao_evento' => 'Mutirão comunitário',
            'vagas_evento' => 10, 'modalidade_evento' => 'online',
            'imagem_evento_link' => 'evento.jpg',
            'cat_evento_id' => CatEvento::firstOrCreate(['nome_categoria' => 'Educação'])->cat_evento_id,
            'status_evento' => 'ativo',
        ]);
        $evento->forceFill([
            'ong_id' => $owner instanceof Ong ? $owner->ong_id : null,
            'pessoa_id' => $owner instanceof Pessoa ? $owner->pessoa_id : null,
            'responsavel_pessoa_id' => $owner instanceof Pessoa ? $owner->pessoa_id : null,
        ])->save();

        return $evento;
    }

    private function agenda(Evento $evento, Carbon $inicio, int $minutos = 60, int $vagas = 10): Agenda
    {
        return Agenda::create([
            'evento_id' => $evento->evento_id, 'status_ativo' => 'ativo',
            'data_inicio' => $inicio, 'data_fim' => $inicio->copy()->addMinutes($minutos),
            'vagas_agenda' => $vagas,
        ]);
    }

    private function inscricao(Agenda $agenda, Pessoa $pessoa, string $status): Inscricao
    {
        return Inscricao::create([
            'agenda_id' => $agenda->agenda_id, 'pessoa_id' => $pessoa->pessoa_id,
            'status_inscricao' => $status, 'dt_inscricao' => now(),
        ]);
    }
}
