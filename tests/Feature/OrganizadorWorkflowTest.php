<?php

namespace Tests\Feature;

use App\Models\Acompanhamento;
use App\Models\Agenda;
use App\Models\AvaliacaoVoluntario;
use App\Models\CatEvento;
use App\Models\Evento;
use App\Models\Inscricao;
use App\Models\Pessoa;
use App\Models\SolicitacaoOrganizador;
use App\Models\VinculoOngOrganizador;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrganizadorWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 100;

    public function test_elegibilidade_basica_e_justificativa(): void
    {
        $elegivel = $this->pessoa();
        $this->tornarElegivel($elegivel);
        Sanctum::actingAs($elegivel, ['pessoa']);

        $this->postJson('/api/v1/pessoas/me/solicitacao-organizador', [])
            ->assertUnprocessable();
        $this->postJson('/api/v1/pessoas/me/solicitacao-organizador', ['justificativa' => '   '])
            ->assertUnprocessable();
        $this->postJson('/api/v1/pessoas/me/solicitacao-organizador', [
            'justificativa' => 'Quero organizar ações comunitárias.',
        ])->assertCreated()->assertJsonPath('data.status', 'pendente');

        $this->getJson('/api/v1/pessoas/me/solicitacao-organizador')
            ->assertOk()->assertJsonPath('data.status', 'pendente');
    }

    public function test_roles_e_status_invalidos_nao_solicitam_promocao(): void
    {
        foreach (['aguardando_validacao', 'em_analise', 'reprovado'] as $status) {
            $pessoa = $this->pessoa('voluntario', ['status_validacao' => $status]);
            Sanctum::actingAs($pessoa, ['pessoa']);
            $this->postJson('/api/v1/pessoas/me/solicitacao-organizador', [
                'justificativa' => 'Solicitação válida.',
            ])->assertForbidden();
        }

        foreach (['organizador', 'administrador'] as $role) {
            $pessoa = $this->pessoa($role);
            Sanctum::actingAs($pessoa, ['pessoa']);
            $this->postJson('/api/v1/pessoas/me/solicitacao-organizador', [
                'justificativa' => 'Solicitação válida.',
            ])->assertForbidden();
        }
    }

    public function test_participacoes_bloqueio_apadrinhado_e_solicitacao_aberta_impedem_nova_solicitacao(): void
    {
        $semParticipacoes = $this->pessoa();
        Sanctum::actingAs($semParticipacoes, ['pessoa']);
        $this->postJson('/api/v1/pessoas/me/solicitacao-organizador', [
            'justificativa' => 'Ainda não elegível.',
        ])->assertStatus(409);

        $bloqueado = $this->pessoa();
        $this->tornarElegivel($bloqueado);
        $bloqueado->forceFill(['bloqueado_ate' => now()->addDay()])->save();
        Sanctum::actingAs($bloqueado, ['pessoa']);
        $this->postJson('/api/v1/pessoas/me/solicitacao-organizador', [
            'justificativa' => 'Bloqueado.',
        ])->assertStatus(409);

        $padrinho = $this->pessoa();
        $apadrinhado = $this->pessoa();
        $this->tornarElegivel($padrinho);
        Acompanhamento::iniciar($padrinho->pessoa_id, $apadrinhado->pessoa_id);
        Sanctum::actingAs($padrinho, ['pessoa']);
        $this->postJson('/api/v1/pessoas/me/solicitacao-organizador', [
            'justificativa' => 'Possui apadrinhado.',
        ])->assertStatus(409);

        $aberta = $this->pessoa();
        $this->tornarElegivel($aberta);
        Sanctum::actingAs($aberta, ['pessoa']);
        $this->postJson('/api/v1/pessoas/me/solicitacao-organizador', [
            'justificativa' => 'Primeira.',
        ])->assertCreated();
        $this->postJson('/api/v1/pessoas/me/solicitacao-organizador', [
            'justificativa' => 'Segunda.',
        ])->assertStatus(409);
    }

    public function test_permissoes_administrativas_e_reprovacao(): void
    {
        $pessoa = $this->pessoa();
        $this->tornarElegivel($pessoa);
        $solicitacao = $this->solicitar($pessoa);

        $naoAdmin = $this->pessoa();
        $this->grantPermission($naoAdmin, 'organizadores.validar');
        Sanctum::actingAs($naoAdmin, ['pessoa']);
        $this->getJson('/api/v1/admin/solicitacoes-organizador')->assertForbidden();

        $semPermissao = $this->admin(false, false);
        Sanctum::actingAs($semPermissao, ['pessoa']);
        $this->getJson('/api/v1/admin/solicitacoes-organizador')->assertForbidden();

        $admin = $this->admin();
        Sanctum::actingAs($admin, ['pessoa']);
        $this->getJson('/api/v1/admin/solicitacoes-organizador')->assertOk();
        $this->postJson("/api/v1/admin/solicitacoes-organizador/{$solicitacao}/iniciar-analise")
            ->assertOk()->assertJsonPath('data.status', 'em_analise');
        $this->postJson("/api/v1/admin/solicitacoes-organizador/{$solicitacao}/reprovar", [])
            ->assertUnprocessable();
        $this->postJson("/api/v1/admin/solicitacoes-organizador/{$solicitacao}/reprovar", [
            'motivo' => 'Necessita mais experiência.',
        ])->assertOk()->assertJsonPath('data.status', 'reprovada');

        $this->assertSame('voluntario', $pessoa->fresh()->role_pessoa);
        $this->assertSame('aprovado', $pessoa->status_validacao);

        $master = $this->admin(true, false);
        Sanctum::actingAs($master, ['pessoa']);
        $this->getJson('/api/v1/admin/solicitacoes-organizador')->assertOk();
    }

    public function test_aprovacao_promove_preserva_dados_e_revalida_elegibilidade(): void
    {
        $pessoa = $this->pessoa();
        $pessoa->forceFill([
            'xp_pessoa' => 321,
            'avaliacao_pessoa' => 4.50,
        ])->save();
        $aprovadoEm = $pessoa->aprovado_em;
        $this->tornarElegivel($pessoa);
        $solicitacao = $this->solicitar($pessoa);
        $admin = $this->admin();
        Sanctum::actingAs($admin, ['pessoa']);
        $this->postJson("/api/v1/admin/solicitacoes-organizador/{$solicitacao}/iniciar-analise")->assertOk();
        $this->postJson("/api/v1/admin/solicitacoes-organizador/{$solicitacao}/aprovar")
            ->assertOk()->assertJsonPath('data.status', 'aprovada');

        $pessoa->refresh();
        $this->assertSame('organizador', $pessoa->role_pessoa);
        $this->assertSame('aprovado', $pessoa->status_validacao);
        $this->assertSame(321, $pessoa->xp_pessoa);
        $this->assertSame('4.50', $pessoa->avaliacao_pessoa);
        $this->assertSame(4.0, app(\App\Services\AvaliacaoService::class)->mediaVoluntario($pessoa));
        $this->assertTrue($aprovadoEm->equalTo($pessoa->aprovado_em));
        $this->assertSame(5, $pessoa->inscricoes()->count());

        $outro = $this->pessoa();
        $this->tornarElegivel($outro);
        $outraSolicitacao = $this->solicitar($outro);
        Sanctum::actingAs($admin, ['pessoa']);
        $this->postJson("/api/v1/admin/solicitacoes-organizador/{$outraSolicitacao}/iniciar-analise")->assertOk();
        $outro->forceFill(['bloqueado_ate' => now()->addDay()])->save();
        $this->postJson("/api/v1/admin/solicitacoes-organizador/{$outraSolicitacao}/aprovar")
            ->assertStatus(409);
        $this->assertSame('voluntario', $outro->fresh()->role_pessoa);
    }

    public function test_lock_exige_assuncao_e_impede_outro_admin_inclusive_master(): void
    {
        Carbon::setTestNow('2026-10-13 12:00:00');
        try {
            $pessoa = $this->pessoa();
            $this->tornarElegivel($pessoa);
            $id = $this->solicitar($pessoa);
            $a = $this->admin();
            $b = $this->admin();
            $master = $this->admin(true, false);
            $semPermissao = $this->admin(false, false);
            $base = "/api/v1/admin/solicitacoes-organizador/{$id}";

            Sanctum::actingAs($semPermissao, ['pessoa']);
            $this->postJson("{$base}/iniciar-analise")->assertForbidden();
            $this->postJson("{$base}/aprovar")->assertForbidden();
            $this->postJson("{$base}/reprovar", ['motivo' => 'Não aprovado.'])->assertForbidden();
            Sanctum::actingAs($a, ['pessoa']);
            $this->postJson("{$base}/aprovar")->assertStatus(409);
            $this->postJson("{$base}/reprovar", ['motivo' => 'Não aprovado.'])->assertStatus(409);
            $this->postJson("{$base}/iniciar-analise")->assertOk();
            $solicitacao = SolicitacaoOrganizador::findOrFail($id);
            $this->assertSame($a->pessoa_id, $solicitacao->admin_id);
            $this->assertTrue(now()->equalTo($solicitacao->analise_iniciada_em));
            $this->postJson("{$base}/iniciar-analise")->assertStatus(409);

            Sanctum::actingAs($b, ['pessoa']);
            $this->postJson("{$base}/iniciar-analise")->assertStatus(409);
            $this->postJson("{$base}/aprovar")->assertStatus(409);
            $this->postJson("{$base}/reprovar", ['motivo' => 'Não aprovado.'])->assertStatus(409);
            Sanctum::actingAs($master, ['pessoa']);
            $this->postJson("{$base}/aprovar")->assertStatus(409);
            $this->postJson("{$base}/reprovar", ['motivo' => 'Não aprovado.'])->assertStatus(409);

            Sanctum::actingAs($a, ['pessoa']);
            $this->postJson("{$base}/aprovar")->assertOk();
            $this->assertSame($a->pessoa_id, SolicitacaoOrganizador::findOrFail($id)->admin_id);
            $this->postJson("{$base}/iniciar-analise")->assertStatus(409);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_lock_expira_exatamente_em_24_horas_e_exige_reassuncao(): void
    {
        Carbon::setTestNow('2026-10-13 12:00:00');
        try {
            $pessoa = $this->pessoa();
            $this->tornarElegivel($pessoa);
            $id = $this->solicitar($pessoa);
            $a = $this->admin();
            $b = $this->admin();
            $base = "/api/v1/admin/solicitacoes-organizador/{$id}";

            Sanctum::actingAs($a, ['pessoa']);
            $this->postJson("{$base}/iniciar-analise")->assertOk();
            Carbon::setTestNow('2026-10-14 11:59:59');
            Sanctum::actingAs($b, ['pessoa']);
            $this->postJson("{$base}/iniciar-analise")->assertStatus(409);
            Carbon::setTestNow('2026-10-14 12:00:00');
            Sanctum::actingAs($a, ['pessoa']);
            $this->postJson("{$base}/aprovar")->assertStatus(409);
            $this->postJson("{$base}/reprovar", ['motivo' => 'Não aprovado.'])->assertStatus(409);
            Sanctum::actingAs($b, ['pessoa']);
            $this->postJson("{$base}/aprovar")->assertStatus(409);
            $this->postJson("{$base}/iniciar-analise")->assertOk();
            $solicitacao = SolicitacaoOrganizador::findOrFail($id);
            $this->assertSame($b->pessoa_id, $solicitacao->admin_id);
            $this->assertTrue(now()->equalTo($solicitacao->analise_iniciada_em));
            Sanctum::actingAs($a, ['pessoa']);
            $this->postJson("{$base}/aprovar")->assertStatus(409);
            Sanctum::actingAs($b, ['pessoa']);
            $this->postJson("{$base}/reprovar", ['motivo' => 'Não aprovado.'])->assertOk();
            $this->assertSame($b->pessoa_id, SolicitacaoOrganizador::findOrFail($id)->admin_id);
            $this->postJson("{$base}/iniciar-analise")->assertStatus(409);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_cooldown_de_trinta_dias_completos(): void
    {
        Carbon::setTestNow('2026-10-01 15:00:00');
        $pessoa = $this->pessoa();
        $this->tornarElegivel($pessoa);
        SolicitacaoOrganizador::create([
            'pessoa_id' => $pessoa->pessoa_id,
            'status' => 'reprovada',
            'justificativa' => 'Anterior.',
            'motivo_decisao' => 'Reprovada.',
            'solicitada_em' => now()->subDay(),
            'decidida_em' => now(),
        ]);
        Sanctum::actingAs($pessoa, ['pessoa']);

        $payload = ['justificativa' => 'Nova solicitação.'];
        $this->postJson('/api/v1/pessoas/me/solicitacao-organizador', $payload)->assertStatus(409);
        Carbon::setTestNow(now()->addDays(29));
        $this->postJson('/api/v1/pessoas/me/solicitacao-organizador', $payload)->assertStatus(409);
        Carbon::setTestNow('2026-10-31 15:00:00');
        $this->postJson('/api/v1/pessoas/me/solicitacao-organizador', $payload)->assertCreated();
    }

    public function test_promocao_remove_acesso_a_novas_candidaturas(): void
    {
        $pessoa = $this->pessoa();
        $this->tornarElegivel($pessoa);
        $solicitacao = $this->solicitar($pessoa);
        $admin = $this->admin();
        Sanctum::actingAs($admin, ['pessoa']);
        $this->postJson("/api/v1/admin/solicitacoes-organizador/{$solicitacao}/iniciar-analise")->assertOk();
        $this->postJson("/api/v1/admin/solicitacoes-organizador/{$solicitacao}/aprovar")->assertOk();

        $agenda = $this->agendaDisponivel();
        Sanctum::actingAs($pessoa->fresh(), ['pessoa']);
        $this->postJson("/api/v1/agendas/{$agenda->agenda_id}/inscricoes")->assertForbidden();
    }

    public function test_convite_so_aceita_organizador_aprovado_e_pendente_mantem_independencia(): void
    {
        $ong = $this->ong();
        $organizador = $this->pessoa('organizador');
        Sanctum::actingAs($ong, ['ong']);
        $convite = $this->postJson('/api/v1/ongs/me/convites-organizador', [
            'pessoa_id' => $organizador->pessoa_id,
        ])->assertCreated()->assertJsonPath('data.status', 'convite_pendente')
            ->json('data.vinculo_ong_organizador_id');

        foreach ([$this->pessoa('voluntario'), $this->pessoa('administrador')] as $invalida) {
            $this->postJson('/api/v1/ongs/me/convites-organizador', [
                'pessoa_id' => $invalida->pessoa_id,
            ])->assertStatus(409);
        }

        Sanctum::actingAs($organizador, ['pessoa']);
        $this->getJson('/api/v1/pessoas/me/convites-ong')->assertJsonFragment(['status' => 'convite_pendente']);
        $this->postJson('/api/v1/pessoas/eventos', $this->eventoPayload())
            ->assertCreated()
            ->assertJsonPath('data.pessoa_id', $organizador->pessoa_id);
        $evento = Evento::latest('evento_id')->firstOrFail();
        $this->assertSame($organizador->pessoa_id, $evento->pessoa_id);
        $this->assertNull($evento->ong_id);
        $this->assertSame($organizador->pessoa_id, $evento->responsavel_pessoa_id);
        $this->assertNotNull($convite);
    }

    public function test_recusa_e_aceite_de_convites_preservam_historico_e_exclusividade(): void
    {
        $ongA = $this->ong();
        $ongB = $this->ong();
        $organizador = $this->pessoa('organizador');

        Sanctum::actingAs($ongA, ['ong']);
        $recusado = $this->postJson('/api/v1/ongs/me/convites-organizador', ['pessoa_id' => $organizador->pessoa_id])
            ->assertCreated()->json('data.vinculo_ong_organizador_id');
        Sanctum::actingAs($organizador, ['pessoa']);
        $this->postJson("/api/v1/pessoas/me/convites-ong/{$recusado}/recusar")
            ->assertOk()->assertJsonPath('data.status', 'recusado');
        $this->assertFalse($organizador->vinculosOng()->where('status', 'ativo')->exists());

        Sanctum::actingAs($ongA, ['ong']);
        $primeiro = $this->postJson('/api/v1/ongs/me/convites-organizador', ['pessoa_id' => $organizador->pessoa_id])
            ->assertCreated()->json('data.vinculo_ong_organizador_id');
        Sanctum::actingAs($ongB, ['ong']);
        $segundo = $this->postJson('/api/v1/ongs/me/convites-organizador', ['pessoa_id' => $organizador->pessoa_id])
            ->assertCreated()->json('data.vinculo_ong_organizador_id');

        Sanctum::actingAs($organizador, ['pessoa']);
        $this->postJson("/api/v1/pessoas/me/convites-ong/{$primeiro}/aceitar")
            ->assertOk()->assertJsonPath('data.status', 'ativo');
        $this->postJson("/api/v1/pessoas/me/convites-ong/{$segundo}/aceitar")->assertStatus(409);

        Sanctum::actingAs($ongB, ['ong']);
        $this->postJson('/api/v1/ongs/me/convites-organizador', ['pessoa_id' => $organizador->pessoa_id])
            ->assertStatus(409);
    }

    public function test_ong_pode_ter_varios_organizadores(): void
    {
        $ong = $this->ong();
        foreach ([$this->pessoa('organizador'), $this->pessoa('organizador')] as $organizador) {
            Sanctum::actingAs($ong, ['ong']);
            $convite = $this->postJson('/api/v1/ongs/me/convites-organizador', ['pessoa_id' => $organizador->pessoa_id])
                ->assertCreated()->json('data.vinculo_ong_organizador_id');
            Sanctum::actingAs($organizador, ['pessoa']);
            $this->postJson("/api/v1/pessoas/me/convites-ong/{$convite}/aceitar")->assertOk();
        }

        $this->assertSame(2, $ong->vinculosOrganizadores()->where('status', 'ativo')->count());
    }

    public function test_criacao_de_evento_respeita_tipo_e_vinculo_do_organizador(): void
    {
        $independente = $this->pessoa('organizador');
        Sanctum::actingAs($independente, ['pessoa']);
        $this->postJson('/api/v1/pessoas/eventos', $this->eventoPayload())->assertCreated();
        $evento = Evento::latest('evento_id')->firstOrFail();
        $this->assertSame($independente->pessoa_id, $evento->pessoa_id);
        $this->assertNull($evento->ong_id);
        $this->assertSame($independente->pessoa_id, $evento->responsavel_pessoa_id);

        foreach ([$this->pessoa('voluntario'), $this->pessoa('administrador')] as $invalida) {
            Sanctum::actingAs($invalida, ['pessoa']);
            $this->postJson('/api/v1/pessoas/eventos', $this->eventoPayload())->assertForbidden();
        }

        $ong = $this->ong();
        $vinculado = $this->pessoa('organizador');
        $this->vinculoAtivo($ong->ong_id, $vinculado->pessoa_id);
        Sanctum::actingAs($vinculado, ['pessoa']);
        $this->postJson('/api/v1/pessoas/eventos', $this->eventoPayload())->assertCreated();
        $eventoVinculado = Evento::latest('evento_id')->firstOrFail();
        $this->assertSame($ong->ong_id, $eventoVinculado->ong_id);
        $this->assertNull($eventoVinculado->pessoa_id);
        $this->assertSame($vinculado->pessoa_id, $eventoVinculado->responsavel_pessoa_id);
    }

    public function test_ong_cria_evento_e_atribui_apenas_organizador_vinculado_antes_do_inicio(): void
    {
        $ong = $this->ong();
        Sanctum::actingAs($ong, ['ong']);
        $eventoId = $this->postJson('/api/v1/eventos', $this->eventoPayload())
            ->assertCreated()->json('data.evento_id');
        $this->assertNull(Evento::findOrFail($eventoId)->responsavel_pessoa_id);

        $vinculado = $this->pessoa('organizador');
        $this->vinculoAtivo($ong->ong_id, $vinculado->pessoa_id);
        $this->patchJson("/api/v1/ongs/me/eventos/{$eventoId}/responsavel", ['pessoa_id' => $vinculado->pessoa_id])
            ->assertOk()->assertJsonPath('data.responsavel_pessoa_id', $vinculado->pessoa_id);

        $independente = $this->pessoa('organizador');
        $voluntario = $this->pessoa('voluntario');
        $outraOng = $this->ong();
        $outraPessoa = $this->pessoa('organizador');
        $this->vinculoAtivo($outraOng->ong_id, $outraPessoa->pessoa_id);
        foreach ([$independente, $voluntario, $outraPessoa] as $invalida) {
            $this->patchJson("/api/v1/ongs/me/eventos/{$eventoId}/responsavel", ['pessoa_id' => $invalida->pessoa_id])
                ->assertStatus(409);
        }

        Sanctum::actingAs($outraOng, ['ong']);
        $this->patchJson("/api/v1/ongs/me/eventos/{$eventoId}/responsavel", ['pessoa_id' => null])
            ->assertForbidden();

        Agenda::create([
            'evento_id' => $eventoId,
            'vagas_agenda' => 20,
            'data_inicio' => now()->subMinute(),
            'data_fim' => now()->addHour(),
            'status_ativo' => 'ativo',
        ]);
        Sanctum::actingAs($ong, ['ong']);
        $this->patchJson("/api/v1/ongs/me/eventos/{$eventoId}/responsavel", ['pessoa_id' => null])
            ->assertStatus(409);
    }

    public function test_policy_restringe_evento_e_agenda_ao_responsavel_operacional(): void
    {
        $ong = $this->ong();
        $responsavel = $this->pessoa('organizador');
        $colega = $this->pessoa('organizador');
        $this->vinculoAtivo($ong->ong_id, $responsavel->pessoa_id);
        $this->vinculoAtivo($ong->ong_id, $colega->pessoa_id);
        $evento = $this->eventoOng($ong->ong_id, $responsavel->pessoa_id);
        $agenda = Agenda::create([
            'evento_id' => $evento->evento_id,
            'vagas_agenda' => 20,
            'data_inicio' => now()->addDays(2),
            'data_fim' => now()->addDays(2)->addHour(),
            'status_ativo' => 'ativo',
        ]);

        Sanctum::actingAs($responsavel, ['pessoa']);
        $this->patchJson("/api/v1/eventos/{$evento->evento_id}", ['nm_evento' => 'Atualizado'])
            ->assertOk();
        $this->patchJson("/api/v1/agendas/{$agenda->agenda_id}", ['data_fim' => now()->addDays(2)->addHours(2)])
            ->assertOk();

        Sanctum::actingAs($colega, ['pessoa']);
        $this->patchJson("/api/v1/eventos/{$evento->evento_id}", ['nm_evento' => 'Negado'])->assertForbidden();
        $this->patchJson("/api/v1/agendas/{$agenda->agenda_id}", ['status_ativo' => 'finalizado'])->assertForbidden();

        $outraOng = $this->ong();
        $outraPessoa = $this->pessoa('organizador');
        $this->vinculoAtivo($outraOng->ong_id, $outraPessoa->pessoa_id);
        Sanctum::actingAs($outraPessoa, ['pessoa']);
        $this->patchJson("/api/v1/eventos/{$evento->evento_id}", ['nm_evento' => 'Negado'])->assertForbidden();

        $independente = $this->pessoa('organizador');
        Sanctum::actingAs($independente, ['pessoa']);
        $this->patchJson("/api/v1/eventos/{$evento->evento_id}", ['nm_evento' => 'Negado'])->assertForbidden();

        Sanctum::actingAs($ong, ['ong']);
        $this->patchJson("/api/v1/eventos/{$evento->evento_id}", ['nm_evento' => 'ONG atualizou'])->assertOk();
    }

    public function test_encerramento_de_vinculo_respeita_eventos_nao_terminais_e_retorna_independencia(): void
    {
        $ong = $this->ong();
        $organizador = $this->pessoa('organizador');
        $vinculo = $this->vinculoAtivo($ong->ong_id, $organizador->pessoa_id);
        $evento = $this->eventoOng($ong->ong_id, $organizador->pessoa_id);

        Sanctum::actingAs($organizador, ['pessoa']);
        $this->deleteJson("/api/v1/pessoas/me/vinculos-ong/{$vinculo->vinculo_ong_organizador_id}")
            ->assertStatus(409);
        Sanctum::actingAs($ong, ['ong']);
        $this->deleteJson("/api/v1/ongs/me/organizadores/{$vinculo->vinculo_ong_organizador_id}")
            ->assertStatus(409);

        $evento->forceFill(['status_evento' => 'finalizado'])->save();
        $this->deleteJson("/api/v1/ongs/me/organizadores/{$vinculo->vinculo_ong_organizador_id}")
            ->assertOk()->assertJsonPath('data.status', 'encerrado');

        Sanctum::actingAs($organizador, ['pessoa']);
        $this->postJson('/api/v1/pessoas/eventos', $this->eventoPayload())->assertCreated();
        $novo = Evento::latest('evento_id')->firstOrFail();
        $this->assertSame($organizador->pessoa_id, $novo->pessoa_id);
        $this->assertNull($novo->ong_id);

        $ong2 = $this->ong();
        $organizador2 = $this->pessoa('organizador');
        $vinculo2 = $this->vinculoAtivo($ong2->ong_id, $organizador2->pessoa_id);
        Sanctum::actingAs($organizador2, ['pessoa']);
        $this->deleteJson("/api/v1/pessoas/me/vinculos-ong/{$vinculo2->vinculo_ong_organizador_id}")
            ->assertOk();
    }

    public function test_convite_nao_pode_ser_aceito_enquanto_evento_independente_nao_for_finalizado(): void
    {
        $organizador = $this->pessoa('organizador');
        $ong = $this->ong();
        Sanctum::actingAs($organizador, ['pessoa']);
        $eventoId = $this->postJson('/api/v1/pessoas/eventos', $this->eventoPayload())
            ->assertCreated()->json('data.evento_id');
        $antes = Evento::findOrFail($eventoId);
        $this->assertSame('aguardando a confirmação', $antes->status_evento);

        Sanctum::actingAs($ong, ['ong']);
        $conviteId = $this->postJson('/api/v1/ongs/me/convites-organizador', [
            'pessoa_id' => $organizador->pessoa_id,
        ])->assertCreated()->json('data.vinculo_ong_organizador_id');

        Sanctum::actingAs($organizador, ['pessoa']);
        $this->postJson("/api/v1/pessoas/me/convites-ong/{$conviteId}/aceitar")->assertStatus(409);
        $this->assertDatabaseHas('vinculo_ong_organizador', [
            'vinculo_ong_organizador_id' => $conviteId,
            'status' => 'convite_pendente',
            'respondido_em' => null,
        ]);
        $this->assertDatabaseHas('evento', [
            'evento_id' => $eventoId,
            'pessoa_id' => $organizador->pessoa_id,
            'ong_id' => null,
            'responsavel_pessoa_id' => $organizador->pessoa_id,
            'status_evento' => 'aguardando a confirmação',
        ]);
        $this->assertFalse($organizador->vinculoOngAtivo()->exists());

        $antes->forceFill(['status_evento' => 'finalizado'])->save();
        $this->postJson("/api/v1/pessoas/me/convites-ong/{$conviteId}/aceitar")
            ->assertOk()->assertJsonPath('data.status', 'ativo');
    }

    public function test_evento_independente_cancelado_permite_aceitar_convite(): void
    {
        $organizador = $this->pessoa('organizador');
        $ong = $this->ong();
        $evento = $this->eventoIndependente($organizador->pessoa_id);
        $evento->forceFill(['status_evento' => 'cancelado'])->save();

        Sanctum::actingAs($ong, ['ong']);
        $conviteId = $this->postJson('/api/v1/ongs/me/convites-organizador', [
            'pessoa_id' => $organizador->pessoa_id,
        ])->assertCreated()->json('data.vinculo_ong_organizador_id');

        Sanctum::actingAs($organizador, ['pessoa']);
        $this->postJson("/api/v1/pessoas/me/convites-ong/{$conviteId}/aceitar")
            ->assertOk()->assertJsonPath('data.status', 'ativo');
    }

    public function test_todos_os_eventos_independentes_devem_estar_terminais_para_aceitar_convite(): void
    {
        $organizador = $this->pessoa('organizador');
        $ong = $this->ong();
        $finalizado = $this->eventoIndependente($organizador->pessoa_id);
        $finalizado->forceFill(['status_evento' => 'finalizado'])->save();
        $ativo = $this->eventoIndependente($organizador->pessoa_id);

        Sanctum::actingAs($ong, ['ong']);
        $conviteId = $this->postJson('/api/v1/ongs/me/convites-organizador', [
            'pessoa_id' => $organizador->pessoa_id,
        ])->assertCreated()->json('data.vinculo_ong_organizador_id');

        Sanctum::actingAs($organizador, ['pessoa']);
        $this->postJson("/api/v1/pessoas/me/convites-ong/{$conviteId}/aceitar")->assertStatus(409);
        $this->assertSame('convite_pendente', VinculoOngOrganizador::findOrFail($conviteId)->status);
        $ativo->forceFill(['status_evento' => 'cancelado'])->save();
        $this->postJson("/api/v1/pessoas/me/convites-ong/{$conviteId}/aceitar")
            ->assertOk()->assertJsonPath('data.status', 'ativo');
    }

    public function test_criacao_de_eventos_acompanha_aceite_e_encerramento_sem_alterar_os_antigos(): void
    {
        $organizador = $this->pessoa('organizador');
        $ong = $this->ong();
        Sanctum::actingAs($organizador, ['pessoa']);
        $primeiroId = $this->postJson('/api/v1/pessoas/eventos', $this->eventoPayload())
            ->assertCreated()->json('data.evento_id');
        $this->assertDatabaseHas('evento', [
            'evento_id' => $primeiroId,
            'pessoa_id' => $organizador->pessoa_id,
            'ong_id' => null,
            'responsavel_pessoa_id' => $organizador->pessoa_id,
        ]);
        Evento::findOrFail($primeiroId)->forceFill(['status_evento' => 'finalizado'])->save();

        Sanctum::actingAs($ong, ['ong']);
        $conviteId = $this->postJson('/api/v1/ongs/me/convites-organizador', [
            'pessoa_id' => $organizador->pessoa_id,
        ])->assertCreated()->json('data.vinculo_ong_organizador_id');
        Sanctum::actingAs($organizador, ['pessoa']);
        $this->postJson("/api/v1/pessoas/me/convites-ong/{$conviteId}/aceitar")->assertOk();
        $segundoId = $this->postJson('/api/v1/pessoas/eventos', $this->eventoPayload())
            ->assertCreated()->json('data.evento_id');
        $this->assertDatabaseHas('evento', [
            'evento_id' => $segundoId,
            'pessoa_id' => null,
            'ong_id' => $ong->ong_id,
            'responsavel_pessoa_id' => $organizador->pessoa_id,
        ]);
        Evento::findOrFail($segundoId)->forceFill(['status_evento' => 'finalizado'])->save();

        $this->deleteJson("/api/v1/pessoas/me/vinculos-ong/{$conviteId}")->assertOk();
        $terceiroId = $this->postJson('/api/v1/pessoas/eventos', $this->eventoPayload())
            ->assertCreated()->json('data.evento_id');
        $this->assertDatabaseHas('evento', [
            'evento_id' => $terceiroId,
            'pessoa_id' => $organizador->pessoa_id,
            'ong_id' => null,
            'responsavel_pessoa_id' => $organizador->pessoa_id,
        ]);
        $this->assertSame($organizador->pessoa_id, Evento::findOrFail($primeiroId)->pessoa_id);
        $this->assertSame($ong->ong_id, Evento::findOrFail($segundoId)->ong_id);
    }

    public function test_criacao_de_evento_revalida_role_e_status_no_banco(): void
    {
        foreach ([
            ['role_pessoa' => 'voluntario'],
            ['status_validacao' => 'reprovado'],
        ] as $alteracao) {
            $organizador = $this->pessoa('organizador');
            Sanctum::actingAs($organizador, ['pessoa']);
            Pessoa::whereKey($organizador->pessoa_id)->update($alteracao);

            $this->postJson('/api/v1/pessoas/eventos', $this->eventoPayload())->assertForbidden();
            $this->assertFalse(Evento::where('pessoa_id', $organizador->pessoa_id)->exists());
        }
    }

    public function test_recurso_publico_expoe_responsavel_operacional_sem_dados_privados(): void
    {
        $ong = $this->ong();
        $organizador = $this->pessoa('organizador');
        $comResponsavel = $this->eventoOng($ong->ong_id, $organizador->pessoa_id);
        $semResponsavel = $this->eventoOng($ong->ong_id);
        $independente = $this->eventoIndependente($organizador->pessoa_id);

        foreach ([$comResponsavel, $independente] as $evento) {
            $response = $this->getJson("/api/v1/eventos/{$evento->evento_id}")
                ->assertOk()
                ->assertJsonPath('data.responsavel_pessoa_id', $organizador->pessoa_id)
                ->assertJsonPath('data.responsavel.pessoa_id', $organizador->pessoa_id);
            $publico = $response->json('data.responsavel');
            foreach (['cpf_pessoa', 'rg_pessoa', 'email_pessoa', 'tele_pessoa', 'status_validacao', 'permissoes', 'documentos'] as $campo) {
                $this->assertArrayNotHasKey($campo, $publico);
            }
        }

        $this->getJson("/api/v1/eventos/{$semResponsavel->evento_id}")
            ->assertOk()
            ->assertJsonPath('data.responsavel_pessoa_id', null)
            ->assertJsonPath('data.responsavel', null);
    }

    private function pessoa(string $role = 'voluntario', array $overrides = []): Pessoa
    {
        $id = $this->sequence++;

        return $this->createPessoa($role, array_merge([
            'cpf_pessoa' => str_pad((string) $id, 11, '0', STR_PAD_LEFT),
            'email_pessoa' => "pessoa{$id}@example.com",
            'login_pessoa' => "pessoa{$id}",
        ], $overrides));
    }

    private function admin(bool $master = false, bool $comPermissao = true): Pessoa
    {
        $admin = $this->pessoa('administrador', ['admin_master' => $master]);
        if ($comPermissao && ! $master) {
            $this->grantPermission($admin, 'organizadores.validar');
        }

        return $admin;
    }

    private function ong()
    {
        $id = $this->sequence++;

        return $this->createOng('ativo', [
            'cnpj_ong' => str_pad((string) $id, 14, '0', STR_PAD_LEFT),
            'cpf_responsavel_ong' => str_pad((string) ($id + 1000), 11, '0', STR_PAD_LEFT),
            'email_ong' => "ong{$id}@example.com",
            'login_ong' => "ong{$id}",
        ]);
    }

    private function tornarElegivel(Pessoa $pessoa): void
    {
        $organizador = $this->pessoa('organizador');
        $evento = $this->eventoIndependente($organizador->pessoa_id);
        for ($i = 0; $i < 5; $i++) {
            $agenda = Agenda::create([
                'evento_id' => $evento->evento_id,
                'vagas_agenda' => 20,
                'data_inicio' => now()->subDays(10 - $i),
                'data_fim' => now()->subDays(10 - $i)->addHour(),
                'status_ativo' => 'finalizado',
            ]);
            Inscricao::create([
                'agenda_id' => $agenda->agenda_id,
                'pessoa_id' => $pessoa->pessoa_id,
                'status_inscricao' => 'participou',
                'dt_inscricao' => now()->subDays(20),
                'presenca' => true,
                'presenca_confirmada_em' => now()->subDays(9 - $i),
            ]);
        }
        AvaliacaoVoluntario::create([
            'evento_id' => $evento->evento_id,
            'pessoa_id' => $pessoa->pessoa_id,
            'nota' => 4,
            'avaliador_tipo' => 'pessoa',
            'avaliador_id' => $organizador->pessoa_id,
        ]);
    }

    private function solicitar(Pessoa $pessoa): int
    {
        Sanctum::actingAs($pessoa, ['pessoa']);

        return $this->postJson('/api/v1/pessoas/me/solicitacao-organizador', [
            'justificativa' => 'Tenho experiência e quero organizar eventos.',
        ])->assertCreated()->json('data.solicitacao_organizador_id');
    }

    private function categoria(): CatEvento
    {
        return CatEvento::firstOrCreate(['nome_categoria' => 'Educação']);
    }

    private function eventoPayload(): array
    {
        return [
            'nm_evento' => 'Evento comunitário',
            'descricao_evento' => 'Descrição do evento comunitário.',
            'vagas_evento' => 20,
            'modalidade_evento' => 'online',
            'imagem_evento_link' => 'evento.jpg',
            'cat_evento_id' => $this->categoria()->cat_evento_id,
        ];
    }

    private function eventoIndependente(int $pessoaId): Evento
    {
        $evento = new Evento(array_merge($this->eventoPayload(), ['status_evento' => 'ativo']));
        $evento->forceFill([
            'pessoa_id' => $pessoaId,
            'ong_id' => null,
            'responsavel_pessoa_id' => $pessoaId,
        ])->save();

        return $evento;
    }

    private function eventoOng(int $ongId, ?int $responsavelId = null): Evento
    {
        $evento = new Evento(array_merge($this->eventoPayload(), ['status_evento' => 'ativo']));
        $evento->forceFill([
            'ong_id' => $ongId,
            'pessoa_id' => null,
            'responsavel_pessoa_id' => $responsavelId,
        ])->save();

        return $evento;
    }

    private function vinculoAtivo(int $ongId, int $pessoaId): VinculoOngOrganizador
    {
        return VinculoOngOrganizador::create([
            'ong_id' => $ongId,
            'pessoa_id' => $pessoaId,
            'status' => 'ativo',
            'convidado_em' => now()->subDay(),
            'respondido_em' => now(),
        ]);
    }

    private function agendaDisponivel(): Agenda
    {
        $evento = $this->eventoIndependente($this->pessoa('organizador')->pessoa_id);

        return Agenda::create([
            'evento_id' => $evento->evento_id,
            'vagas_agenda' => 20,
            'data_inicio' => now()->addDays(2),
            'data_fim' => now()->addDays(2)->addHours(2),
            'status_ativo' => 'ativo',
        ]);
    }
}
