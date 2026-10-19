<?php

namespace Tests\Feature;

use App\Http\Resources\PessoaPublicResource;
use App\Models\Agenda;
use App\Models\AvaliacaoOng;
use App\Models\AvaliacaoVoluntario;
use App\Models\CatEvento;
use App\Models\Evento;
use App\Models\Inscricao;
use App\Models\Ong;
use App\Models\Pessoa;
use App\Models\VinculoOngOrganizador;
use App\Services\AvaliacaoService;
use App\Services\ConfiabilidadeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AvaliacaoConfiabilidadeTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 5000;

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

    public function test_responsavel_independente_avalia_uma_vez_por_pessoa_e_evento_sem_afetar_confiabilidade(): void
    {
        $owner = $this->pessoa('organizador');
        $evento = $this->evento($owner);
        $voluntario = $this->pessoa();
        $outro = $this->pessoa();
        $this->participacao($evento, $voluntario, now()->subDays(3));
        $this->participacao($evento, $voluntario, now()->subDays(2));
        $this->participacao($evento, $outro, now()->subDay());
        $resumoAntes = app(ConfiabilidadeService::class)->resumo($voluntario);
        Sanctum::actingAs($owner, ['pessoa']);

        $rota = "/api/v1/eventos/{$evento->evento_id}/avaliacoes/{$voluntario->pessoa_id}";
        $this->postJson($rota, ['nota' => 0])->assertUnprocessable();
        $this->postJson($rota, ['nota' => 6])->assertUnprocessable();
        $this->postJson($rota, ['nota' => 3.5])->assertUnprocessable();
        $this->postJson($rota, ['nota' => 1, 'comentario' => 'Texto não permitido'])
            ->assertUnprocessable()->assertJsonValidationErrors('comentario');
        $this->postJson($rota, ['nota' => 1])->assertCreated()->assertJsonPath('data.nota', 1);
        $this->postJson($rota, ['nota' => 5])->assertStatus(409);
        $this->postJson("/api/v1/eventos/{$evento->evento_id}/avaliacoes/{$outro->pessoa_id}", ['nota' => 5])
            ->assertCreated();

        $this->assertSame(2, AvaliacaoVoluntario::where('evento_id', $evento->evento_id)->count());
        $this->assertSame(1.0, app(AvaliacaoService::class)->mediaVoluntario($voluntario));
        $this->assertSame($resumoAntes, app(ConfiabilidadeService::class)->resumo($voluntario));
        $this->assertDatabaseHas('avaliacao_voluntario', [
            'evento_id' => $evento->evento_id, 'pessoa_id' => $voluntario->pessoa_id,
            'avaliador_tipo' => 'pessoa', 'avaliador_id' => $owner->pessoa_id,
        ]);
    }

    public function test_avaliacao_exige_participacao_confirmada_e_todas_agendas_terminais(): void
    {
        $owner = $this->pessoa('organizador');
        $evento = $this->evento($owner);
        $aprovado = $this->pessoa();
        $ausente = $this->pessoa();
        $participante = $this->pessoa();
        $agenda = $this->agenda($evento, 'ativo');
        $this->inscricao($agenda, $aprovado, 'aprovado');
        $this->inscricao($agenda, $ausente, 'não participou', now()->subHour());
        $this->inscricao($agenda, $participante, 'participou', now()->subHour());
        Sanctum::actingAs($owner, ['pessoa']);

        $this->postJson("/api/v1/eventos/{$evento->evento_id}/avaliacoes/{$participante->pessoa_id}", ['nota' => 5])
            ->assertStatus(409);
        $agenda->forceFill(['status_ativo' => 'finalizado'])->save();
        foreach ([$aprovado, $ausente, $this->pessoa()] as $invalido) {
            $this->postJson("/api/v1/eventos/{$evento->evento_id}/avaliacoes/{$invalido->pessoa_id}", ['nota' => 5])
                ->assertStatus(409);
        }
        $this->postJson("/api/v1/eventos/{$evento->evento_id}/avaliacoes/{$participante->pessoa_id}", ['nota' => 5])
            ->assertCreated();
    }

    public function test_evento_ong_respeita_responsavel_operacional_ou_conta_proprietaria(): void
    {
        $ong = $this->ong();
        $responsavel = $this->pessoa('organizador');
        $outro = $this->pessoa('organizador');
        foreach ([$responsavel, $outro] as $organizador) {
            VinculoOngOrganizador::create([
                'ong_id' => $ong->ong_id, 'pessoa_id' => $organizador->pessoa_id,
                'status' => 'ativo', 'convidado_em' => now(), 'respondido_em' => now(),
            ]);
        }
        $evento = $this->evento($ong, $responsavel);
        $voluntario = $this->pessoa();
        $this->participacao($evento, $voluntario, now()->subDay());
        $rota = "/api/v1/eventos/{$evento->evento_id}/avaliacoes/{$voluntario->pessoa_id}";
        foreach ([[$outro, ['pessoa']], [$this->pessoa('administrador'), ['pessoa']], [$voluntario, ['pessoa']], [$ong, ['ong']]] as [$ator, $abilities]) {
            Sanctum::actingAs($ator, $abilities);
            $this->postJson($rota, ['nota' => 4])->assertForbidden();
        }
        Sanctum::actingAs($responsavel, ['pessoa']);
        $this->postJson($rota, ['nota' => 4])->assertCreated();

        $semResponsavel = $this->evento($ong);
        $participante = $this->pessoa();
        $this->participacao($semResponsavel, $participante, now()->subDay());
        Sanctum::actingAs($ong, ['ong']);
        $this->postJson("/api/v1/eventos/{$semResponsavel->evento_id}/avaliacoes/{$participante->pessoa_id}", ['nota' => 5])
            ->assertCreated();
        $this->assertDatabaseHas('avaliacao_voluntario', [
            'evento_id' => $semResponsavel->evento_id, 'pessoa_id' => $participante->pessoa_id,
            'avaliador_tipo' => 'ong', 'avaliador_id' => $ong->ong_id,
        ]);
    }

    public function test_avaliacao_da_ong_e_opcional_unica_e_nao_altera_media_ou_confiabilidade(): void
    {
        $ong = $this->ong();
        $evento = $this->evento($ong);
        $voluntario = $this->pessoa();
        $this->participacao($evento, $voluntario, now()->subDay());
        $resumoAntes = app(ConfiabilidadeService::class)->resumo($voluntario);
        $this->assertNull(app(AvaliacaoService::class)->mediaVoluntario($voluntario));
        Sanctum::actingAs($voluntario, ['pessoa']);
        $rota = "/api/v1/eventos/{$evento->evento_id}/avaliacao-ong";
        $this->postJson($rota, ['nota' => 0])->assertUnprocessable();
        $this->postJson($rota, ['nota' => 6])->assertUnprocessable();
        $this->postJson($rota, ['nota' => 1, 'comentario' => 'não'])->assertUnprocessable();
        $this->postJson($rota, ['nota' => 1])->assertCreated();
        $this->postJson($rota, ['nota' => 5])->assertStatus(409);
        $this->assertSame(1, AvaliacaoOng::where('evento_id', $evento->evento_id)->count());
        $this->assertNull(app(AvaliacaoService::class)->mediaVoluntario($voluntario));
        $this->assertSame($resumoAntes, app(ConfiabilidadeService::class)->resumo($voluntario));
        $this->assertNull($voluntario->fresh()->bloqueado_ate);

        $solo = $this->evento($this->pessoa('organizador'));
        $this->participacao($solo, $voluntario, now()->subDay());
        $this->postJson("/api/v1/eventos/{$solo->evento_id}/avaliacao-ong", ['nota' => 5])->assertStatus(409);
        $outro = $this->pessoa();
        Sanctum::actingAs($outro, ['pessoa']);
        $this->postJson($rota, ['nota' => 5])->assertStatus(409);
    }

    public function test_media_vem_dos_registros_reais_e_ignora_legado_e_avaliacao_da_ong(): void
    {
        $pessoa = $this->pessoa();
        $pessoa->forceFill(['avaliacao_pessoa' => 5])->save();
        $service = app(AvaliacaoService::class);
        $this->assertNull($service->mediaVoluntario($pessoa));
        $owner = $this->pessoa('organizador');
        foreach ([5, 4, 3] as $nota) {
            $evento = $this->evento($owner);
            $this->participacao($evento, $pessoa, now()->subDay());
            Sanctum::actingAs($owner, ['pessoa']);
            $this->postJson("/api/v1/eventos/{$evento->evento_id}/avaliacoes/{$pessoa->pessoa_id}", ['nota' => $nota])
                ->assertCreated();
        }
        $this->assertSame(4.0, $service->mediaVoluntario($pessoa));
        $this->getJson("/api/v1/pessoas/{$pessoa->pessoa_id}")
            ->assertOk()->assertJsonPath('data.media_avaliacao', 4);
    }

    public function test_confiabilidade_usa_historico_cronologico_e_so_aparece_apos_tres_participacoes(): void
    {
        $pessoa = $this->pessoa();
        $evento = $this->evento($this->pessoa('organizador'));
        $service = app(ConfiabilidadeService::class);
        $this->assertSame('Em formação — 0 de 3', $service->resumo($pessoa)['estado']);
        $this->assertNull($service->resumo($pessoa)['score']);

        $this->fato($evento, $pessoa, 'não participou', now()->subDays(5));
        for ($i = 4; $i >= 2; $i--) {
            $this->fato($evento, $pessoa, 'participou', now()->subDays($i));
            $resumo = $service->resumo($pessoa);
            if ($i > 2) {
                $this->assertNull($resumo['score']);
                $this->assertSame(5 - $i, $resumo['participacoes_confirmadas']);
            }
        }
        $resumo = $service->resumo($pessoa);
        $this->assertTrue($resumo['formada']);
        $this->assertSame(89, $resumo['score']);

        $this->fato($evento, $pessoa, 'desistente', now()->subDay(), ['desistencia_tardia' => true]);
        $this->assertSame(79, $service->resumo($pessoa)['score']);
        $this->fato($evento, $pessoa, 'desistente', now()->subHours(20), [
            'desistencia_tardia' => true, 'desistencia_sem_penalidade' => true,
        ]);
        $this->assertSame(79, $service->resumo($pessoa)['score']);
        $this->fato($evento, $pessoa, 'desistente', now()->subHours(19), ['desistencia_tardia' => false]);
        $this->assertSame(74, $service->resumo($pessoa)['score']);
        $this->fato($evento, $pessoa, 'desistente', now()->subHours(18), [
            'desistencia_tardia' => false, 'desistencia_sem_penalidade' => true,
        ]);
        $this->assertSame(74, $service->resumo($pessoa)['score']);

        $cancelada = $this->fato($evento, $pessoa, 'não participou', now()->subHour());
        $cancelada->agenda->forceFill(['status_ativo' => 'cancelado'])->save();
        $this->fato($evento, $pessoa, 'pendente', now()->subHour());
        $this->fato($evento, $pessoa, 'reprovado', now()->subHour());
        $this->assertSame(74, $service->resumo($pessoa)['score']);
        $detalhe = $service->detalhe($pessoa);
        $this->assertSame('ausencia', $detalhe['ocorrencias'][0]['tipo']);
        $this->assertSame(-20, $detalhe['ocorrencias'][0]['delta']);
    }

    public function test_confiabilidade_respeita_limites_e_desempate_por_inscricao(): void
    {
        $pessoa = $this->pessoa();
        $evento = $this->evento($this->pessoa('organizador'));
        $quando = now()->subDay();
        for ($i = 0; $i < 6; $i++) {
            $this->fato($evento, $pessoa, 'não participou', $quando->copy()->subMinutes(10 - $i));
        }
        for ($i = 0; $i < 3; $i++) {
            $this->fato($evento, $pessoa, 'participou', $quando->copy()->addMinutes($i));
        }
        $this->assertSame(9, app(ConfiabilidadeService::class)->resumo($pessoa)['score']);

        $outra = $this->pessoa();
        for ($i = 0; $i < 5; $i++) {
            $this->fato($evento, $outra, 'participou', $quando->copy()->addMinutes($i));
        }
        $this->assertSame(100, app(ConfiabilidadeService::class)->resumo($outra)['score']);
    }

    public function test_publico_e_responsavel_veem_resumo_e_detalhe_fica_restrito_a_pessoa_e_admin(): void
    {
        $owner = $this->pessoa('organizador');
        $evento = $this->evento($owner);
        $candidato = $this->pessoa();
        $this->fato($evento, $candidato, 'participou', now()->subDays(3));
        $this->fato($evento, $candidato, 'participou', now()->subDays(2));
        $this->fato($evento, $candidato, 'participou', now()->subDay());
        $this->fato($evento, $candidato, 'não participou', now()->subHours(12));
        $agenda = $this->agenda($evento, 'ativo', now()->addDays(2));
        $this->inscricao($agenda, $candidato, 'pendente');

        $publico = $this->getJson("/api/v1/pessoas/{$candidato->pessoa_id}")->assertOk()->json('data');
        $this->assertSame(80, $publico['confiabilidade']['score']);
        $this->assertArrayNotHasKey('ocorrencias', $publico['confiabilidade']);
        $this->assertArrayNotHasKey('participacoes_confirmadas', $publico['confiabilidade']);
        $this->assertArrayNotHasKey('avaliacao_pessoa', $publico);
        Sanctum::actingAs($owner, ['pessoa']);
        $data = $this->getJson("/api/v1/agendas/{$agenda->agenda_id}/inscritos")->assertOk()->json('data.0');
        $this->assertSame(80, $data['confiabilidade']['score']);
        $this->assertSame($data['confiabilidade'], $data['pessoa']['confiabilidade']);
        $this->assertArrayNotHasKey('ocorrencias', $data['confiabilidade']);
        $this->assertArrayNotHasKey('participacoes_confirmadas', $data['confiabilidade']);
        $this->assertSame(0, $data['match']['total']);
        $this->getJson("/api/v1/pessoas/{$candidato->pessoa_id}/confiabilidade")->assertForbidden();
        Sanctum::actingAs($candidato, ['pessoa']);
        $this->getJson("/api/v1/pessoas/{$candidato->pessoa_id}/confiabilidade")
            ->assertOk()->assertJsonPath('data.score', 80)->assertJsonCount(4, 'data.ocorrencias');
        $admin = $this->pessoa('administrador');
        Sanctum::actingAs($admin, ['pessoa']);
        $this->getJson("/api/v1/pessoas/{$candidato->pessoa_id}/confiabilidade")->assertOk();
    }

    public function test_resource_serializa_metricas_preparadas_sem_consultar_banco(): void
    {
        $pessoa = $this->pessoa();
        $resumo = ['formada' => true, 'score' => 82];
        $pessoa->setAttribute('media_avaliacao_calculada', 4.5);
        $pessoa->setAttribute('confiabilidade_resumo', $resumo);
        DB::enableQueryLog();
        DB::flushQueryLog();
        try {
            $data = (new PessoaPublicResource($pessoa))->toArray(Request::create('/'));
            $this->assertSame([], DB::getQueryLog());
        } finally {
            DB::disableQueryLog();
        }
        $this->assertSame(4.5, $data['media_avaliacao']);
        $this->assertSame($resumo, $data['confiabilidade']);
        $this->assertArrayNotHasKey('email_pessoa', $data);
        $this->assertArrayNotHasKey('cpf_pessoa', $data);
    }

    public function test_colecao_publica_e_candidatos_preparam_metricas_em_lote(): void
    {
        $owner = $this->pessoa('organizador');
        $evento = $this->evento($owner);
        $a = $this->pessoa();
        $b = $this->pessoa();
        foreach ([$a, $b] as $pessoa) {
            $this->participacao($evento, $pessoa, now()->subDay());
            $this->avaliacaoDireta($evento, $pessoa, $owner, 4);
        }
        $agenda = $this->agenda($evento, 'ativo', now()->addDays(2));
        $this->inscricao($agenda, $a, 'pendente');
        $this->inscricao($agenda, $b, 'pendente');

        $publico = $this->getJson('/api/v1/pessoas')->assertOk()->json('data');
        $this->assertCount(2, $publico);
        foreach ($publico as $item) {
            $this->assertSame(4, $item['media_avaliacao']);
            $this->assertSame('Em formação — 1 de 3', $item['confiabilidade']['estado']);
            $this->assertArrayNotHasKey('email_pessoa', $item);
        }

        Sanctum::actingAs($owner, ['pessoa']);
        DB::enableQueryLog();
        DB::flushQueryLog();
        try {
            $data = $this->getJson("/api/v1/agendas/{$agenda->agenda_id}/inscritos")->assertOk()->json('data');
            $queries = collect(DB::getQueryLog())->pluck('query');
        } finally {
            DB::disableQueryLog();
        }
        $this->assertCount(2, $data);
        foreach ($data as $item) {
            $this->assertSame($item['confiabilidade'], $item['pessoa']['confiabilidade']);
            $this->assertSame(4, $item['pessoa']['media_avaliacao']);
            $this->assertSame(0, $item['match']['total']);
            $this->assertArrayNotHasKey('email_pessoa', $item['pessoa']);
        }
        $this->assertCount(1, $queries->filter(fn ($sql) => str_contains($sql, 'from `avaliacao_voluntario`')));
        $this->assertCount(1, $queries->filter(fn ($sql) => str_contains($sql, 'from `p_ag_inscreve`') && str_contains($sql, ' in (')));
    }

    public function test_promocao_exige_cinco_participacoes_score_e_media_reais_e_revalida_na_aprovacao(): void
    {
        $voluntario = $this->pessoa();
        $owner = $this->pessoa('organizador');
        $evento = $this->evento($owner);
        for ($i = 6; $i >= 2; $i--) {
            $this->fato($evento, $voluntario, 'participou', now()->subDays($i));
        }
        Sanctum::actingAs($voluntario, ['pessoa']);
        $this->postJson('/api/v1/pessoas/me/solicitacao-organizador', ['justificativa' => 'Experiência.'])->assertStatus(409);
        $voluntario->forceFill(['avaliacao_pessoa' => 5])->save();
        $this->postJson('/api/v1/pessoas/me/solicitacao-organizador', ['justificativa' => 'Experiência.'])->assertStatus(409);
        $this->avaliacaoDireta($evento, $voluntario, $owner, 4);

        $this->fato($evento, $voluntario, 'não participou', now()->subDay());
        $this->assertSame(80, app(ConfiabilidadeService::class)->resumo($voluntario)['score']);
        $id = $this->postJson('/api/v1/pessoas/me/solicitacao-organizador', ['justificativa' => 'Experiência.'])
            ->assertCreated()->json('data.solicitacao_organizador_id');

        $admin = $this->pessoa('administrador');
        $this->grantPermission($admin, 'organizadores.validar');
        Sanctum::actingAs($admin, ['pessoa']);
        $this->postJson("/api/v1/admin/solicitacoes-organizador/{$id}/iniciar-analise")->assertOk();
        $this->fato($evento, $voluntario, 'desistente', now(), ['desistencia_tardia' => false]);
        $this->assertSame(75, app(ConfiabilidadeService::class)->resumo($voluntario)['score']);
        $this->postJson("/api/v1/admin/solicitacoes-organizador/{$id}/aprovar")->assertStatus(409);
        $this->assertSame('voluntario', $voluntario->fresh()->role_pessoa);
    }

    public function test_promocao_recusa_score_79_e_aceita_quando_ultrapassa_80(): void
    {
        $voluntario = $this->pessoa();
        $owner = $this->pessoa('organizador');
        $evento = $this->evento($owner);
        $this->fato($evento, $voluntario, 'participou', now()->subDays(10));
        $this->fato($evento, $voluntario, 'participou', now()->subDays(9));
        $this->fato($evento, $voluntario, 'não participou', now()->subDays(8));
        $this->fato($evento, $voluntario, 'desistente', now()->subDays(7), ['desistencia_tardia' => true]);
        foreach ([6, 5, 4] as $dias) {
            $this->fato($evento, $voluntario, 'participou', now()->subDays($dias));
        }
        $this->avaliacaoDireta($evento, $voluntario, $owner, 4);
        $this->assertSame(79, app(ConfiabilidadeService::class)->resumo($voluntario)['score']);
        Sanctum::actingAs($voluntario, ['pessoa']);
        $this->postJson('/api/v1/pessoas/me/solicitacao-organizador', ['justificativa' => 'Experiência.'])
            ->assertStatus(409);

        $this->fato($evento, $voluntario, 'participou', now()->subDays(3));
        $this->assertSame(82, app(ConfiabilidadeService::class)->resumo($voluntario)['score']);
        $this->postJson('/api/v1/pessoas/me/solicitacao-organizador', ['justificativa' => 'Experiência.'])
            ->assertCreated();
    }

    public function test_media_399_nao_promove_e_media_400_promove(): void
    {
        $voluntario = $this->pessoa();
        $owner = $this->pessoa('organizador');
        for ($i = 0; $i < 100; $i++) {
            $evento = $this->evento($owner);
            $this->participacao($evento, $voluntario, now()->subDays(2));
            $this->avaliacaoDireta($evento, $voluntario, $owner, $i === 0 ? 3 : 4);
        }
        $this->assertSame(3.99, app(AvaliacaoService::class)->mediaVoluntario($voluntario));
        Sanctum::actingAs($voluntario, ['pessoa']);
        $this->postJson('/api/v1/pessoas/me/solicitacao-organizador', ['justificativa' => 'Experiência.'])
            ->assertStatus(409);

        $evento = $this->evento($owner);
        $this->participacao($evento, $voluntario, now()->subDay());
        $this->avaliacaoDireta($evento, $voluntario, $owner, 5);
        $this->assertSame(4.0, app(AvaliacaoService::class)->mediaVoluntario($voluntario));
        $this->postJson('/api/v1/pessoas/me/solicitacao-organizador', ['justificativa' => 'Experiência.'])
            ->assertCreated();
    }

    public function test_aprovacao_administrativa_revalida_media_real_apos_solicitacao(): void
    {
        $voluntario = $this->pessoa();
        $owner = $this->pessoa('organizador');
        $evento = $this->evento($owner);
        for ($i = 5; $i >= 1; $i--) {
            $this->fato($evento, $voluntario, 'participou', now()->subDays($i));
        }
        $this->avaliacaoDireta($evento, $voluntario, $owner, 5);
        Sanctum::actingAs($voluntario, ['pessoa']);
        $id = $this->postJson('/api/v1/pessoas/me/solicitacao-organizador', ['justificativa' => 'Experiência.'])
            ->assertCreated()->json('data.solicitacao_organizador_id');

        $admin = $this->pessoa('administrador');
        $this->grantPermission($admin, 'organizadores.validar');
        Sanctum::actingAs($admin, ['pessoa']);
        $this->postJson("/api/v1/admin/solicitacoes-organizador/{$id}/iniciar-analise")->assertOk();
        $outroEvento = $this->evento($owner);
        $this->participacao($outroEvento, $voluntario, now());
        $this->avaliacaoDireta($outroEvento, $voluntario, $owner, 1);
        $this->assertSame(3.0, app(AvaliacaoService::class)->mediaVoluntario($voluntario));
        $this->postJson("/api/v1/admin/solicitacoes-organizador/{$id}/aprovar")->assertStatus(409);
        $this->assertSame('voluntario', $voluntario->fresh()->role_pessoa);
    }

    public function test_avaliacoes_dos_dois_tipos_exigem_evento_ativo_sem_efeitos_colaterais(): void
    {
        $ong = $this->ong();
        $voluntario = $this->pessoa();

        foreach (['aguardando a confirmação', 'reprovado', 'cancelado', 'finalizado'] as $estado) {
            $evento = $this->evento($ong);
            $this->participacao($evento, $voluntario, now()->subDay());
            $evento->forceFill(['status_evento' => $estado])->save();
            $notificacoesAntes = $ong->notificacoes()->count() + $voluntario->notificacoes()->count();

            Sanctum::actingAs($ong, ['ong']);
            $this->postJson("/api/v1/eventos/{$evento->evento_id}/avaliacoes/{$voluntario->pessoa_id}", ['nota' => 1])
                ->assertStatus(409)->assertJsonPath('message', 'Somente evento ativo recebe avaliações.');

            Sanctum::actingAs($voluntario, ['pessoa']);
            $this->postJson("/api/v1/eventos/{$evento->evento_id}/avaliacao-ong", ['nota' => 2])
                ->assertStatus(409)->assertJsonPath('message', 'Somente evento ativo recebe avaliações.');

            $this->assertSame(0, AvaliacaoVoluntario::where('evento_id', $evento->evento_id)->count());
            $this->assertSame(0, AvaliacaoOng::where('evento_id', $evento->evento_id)->count());
            $this->assertSame($notificacoesAntes, $ong->notificacoes()->count() + $voluntario->notificacoes()->count());
            $this->assertSame($estado, $evento->fresh()->status_evento);
        }
    }

    public function test_evento_ativo_ainda_exige_agendas_terminais_e_presenca_para_ambas_avaliacoes(): void
    {
        $ong = $this->ong();
        $voluntario = $this->pessoa();
        $evento = $this->evento($ong);
        $agenda = $this->agenda($evento, 'ativo');
        $this->inscricao($agenda, $voluntario, 'participou');
        $avaliacaoVoluntario = "/api/v1/eventos/{$evento->evento_id}/avaliacoes/{$voluntario->pessoa_id}";
        $avaliacaoOng = "/api/v1/eventos/{$evento->evento_id}/avaliacao-ong";

        Sanctum::actingAs($ong, ['ong']);
        $this->postJson($avaliacaoVoluntario, ['nota' => 1])->assertStatus(409);
        Sanctum::actingAs($voluntario, ['pessoa']);
        $this->postJson($avaliacaoOng, ['nota' => 2])->assertStatus(409);

        $semPresenca = $this->evento($ong);
        $this->inscricao($this->agenda($semPresenca), $voluntario, 'não participou');
        Sanctum::actingAs($ong, ['ong']);
        $this->postJson("/api/v1/eventos/{$semPresenca->evento_id}/avaliacoes/{$voluntario->pessoa_id}", ['nota' => 1])
            ->assertStatus(409);
        Sanctum::actingAs($voluntario, ['pessoa']);
        $this->postJson("/api/v1/eventos/{$semPresenca->evento_id}/avaliacao-ong", ['nota' => 2])
            ->assertStatus(409);
        $this->assertDatabaseCount('avaliacao_voluntario', 0);
        $this->assertDatabaseCount('avaliacao_ong', 0);
    }

    public function test_avaliacoes_ativas_sao_historicas_apos_finalizacao_sem_nova_avaliacao_tardia(): void
    {
        $ong = $this->ong();
        $voluntario = $this->pessoa();
        $outro = $this->pessoa();
        $evento = $this->evento($ong);
        $agenda = $this->agenda($evento);
        $this->inscricao($agenda, $voluntario, 'participou');
        $this->inscricao($agenda, $outro, 'participou');

        Sanctum::actingAs($ong, ['ong']);
        foreach ([$voluntario, $outro] as $pessoa) {
            $this->postJson("/api/v1/eventos/{$evento->evento_id}/avaliacoes/{$pessoa->pessoa_id}", ['nota' => 1])
                ->assertCreated();
        }
        Sanctum::actingAs($voluntario, ['pessoa']);
        $this->postJson("/api/v1/eventos/{$evento->evento_id}/avaliacao-ong", ['nota' => 2])->assertCreated();

        Sanctum::actingAs($ong, ['ong']);
        $this->postJson("/api/v1/eventos/{$evento->evento_id}/finalizar")->assertOk();
        $notificacoesAntes = $ong->notificacoes()->count() + $voluntario->notificacoes()->count();
        foreach ([$voluntario, $outro] as $pessoa) {
            $this->postJson("/api/v1/eventos/{$evento->evento_id}/avaliacoes/{$pessoa->pessoa_id}", ['nota' => 5])
                ->assertStatus(409)->assertJsonPath('message', 'Somente evento ativo recebe avaliações.');
        }
        Sanctum::actingAs($outro, ['pessoa']);
        $this->postJson("/api/v1/eventos/{$evento->evento_id}/avaliacao-ong", ['nota' => 3])
            ->assertStatus(409)->assertJsonPath('message', 'Somente evento ativo recebe avaliações.');

        $this->assertSame(2, AvaliacaoVoluntario::where('evento_id', $evento->evento_id)->count());
        $this->assertSame(1, AvaliacaoOng::where('evento_id', $evento->evento_id)->count());
        $this->assertSame($notificacoesAntes, $ong->notificacoes()->count() + $voluntario->notificacoes()->count());
        $this->assertSame('finalizado', $evento->fresh()->status_evento);
    }

    private function pessoa(string $role = 'voluntario'): Pessoa
    {
        $id = $this->sequence++;

        return $this->createPessoa($role, [
            'cpf_pessoa' => str_pad((string) $id, 11, '0', STR_PAD_LEFT),
            'email_pessoa' => "pessoa{$id}@example.com", 'login_pessoa' => "pessoa{$id}",
        ]);
    }

    private function ong(): Ong
    {
        $id = $this->sequence++;

        return $this->createOng('ativo', [
            'cnpj_ong' => str_pad((string) $id, 14, '0', STR_PAD_LEFT),
            'cpf_responsavel_ong' => str_pad((string) ($id + 1000), 11, '0', STR_PAD_LEFT),
            'email_ong' => "ong{$id}@example.com", 'login_ong' => "ong{$id}",
        ]);
    }

    private function evento(Pessoa|Ong $owner, ?Pessoa $responsavel = null): Evento
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
            'responsavel_pessoa_id' => $responsavel?->pessoa_id ?? ($owner instanceof Pessoa ? $owner->pessoa_id : null),
        ])->save();

        return $evento;
    }

    private function agenda(Evento $evento, string $status = 'finalizado', ?Carbon $inicio = null): Agenda
    {
        $inicio ??= now()->subDays(2);

        return Agenda::create([
            'evento_id' => $evento->evento_id, 'status_ativo' => $status,
            'data_inicio' => $inicio, 'data_fim' => $inicio->copy()->addHour(), 'vagas_agenda' => 10,
        ]);
    }

    private function inscricao(Agenda $agenda, Pessoa $pessoa, string $status, ?Carbon $quando = null): Inscricao
    {
        $quando ??= now()->subHour();

        return Inscricao::create([
            'agenda_id' => $agenda->agenda_id, 'pessoa_id' => $pessoa->pessoa_id,
            'status_inscricao' => $status, 'dt_inscricao' => $quando->copy()->subDay(),
            'presenca' => match ($status) {
                'participou' => true,
                'não participou' => false,
                default => null,
            },
            'presenca_confirmada_em' => in_array($status, ['participou', 'não participou'], true) ? $quando : null,
        ]);
    }

    private function participacao(Evento $evento, Pessoa $pessoa, Carbon $quando): Inscricao
    {
        return $this->inscricao($this->agenda($evento), $pessoa, 'participou', $quando);
    }

    private function fato(Evento $evento, Pessoa $pessoa, string $status, Carbon $quando, array $flags = []): Inscricao
    {
        $agenda = $this->agenda($evento, $status === 'desistente' ? 'ativo' : 'finalizado');
        $inscricao = $this->inscricao($agenda, $pessoa, $status, $quando);
        if ($status === 'desistente') {
            $inscricao->forceFill([
                'desistiu_em' => $quando,
                'desistencia_tardia' => $flags['desistencia_tardia'] ?? false,
                'desistencia_sem_penalidade' => $flags['desistencia_sem_penalidade'] ?? false,
            ])->save();
        }

        return $inscricao;
    }

    private function avaliacaoDireta(Evento $evento, Pessoa $pessoa, Pessoa $owner, int $nota): void
    {
        AvaliacaoVoluntario::create([
            'evento_id' => $evento->evento_id, 'pessoa_id' => $pessoa->pessoa_id,
            'nota' => $nota, 'avaliador_tipo' => 'pessoa', 'avaliador_id' => $owner->pessoa_id,
        ]);
    }
}
