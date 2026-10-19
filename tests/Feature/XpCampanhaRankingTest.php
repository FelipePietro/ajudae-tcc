<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\CampanhaXp;
use App\Models\CatEvento;
use App\Models\Evento;
use App\Models\Inscricao;
use App\Models\Pessoa;
use App\Models\XpTransacao;
use App\Services\XpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class XpCampanhaRankingTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 10000;

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

    public function test_formula_duracao_campanhas_simultaneas_teto_e_agregado(): void
    {
        $voluntario = $this->pessoa();
        $dono = $this->pessoa('organizador');
        $casos = [
            ['Trinta', 30, [], 50, 0.5, 1.0],
            ['Quarenta e cinco', 45, [1.2], 90, 0.75, 1.2],
            ['Uma hora', 60, [1.2, 1.5], 180, 1.0, 1.8],
            ['Duas horas', 120, [], 110, 1.1, 1.0],
            ['Teto exato', 60, [1.5, 2], 300, 1.0, 3.0],
            ['Tres horas', 180, [2, 2], 360, 1.2, 3.0],
            ['Quatro e meia', 270, [1.5, 1.5, 1.5], 405, 1.35, 3.0],
        ];
        foreach ($casos as [$categoria, $minutos, $fatores, $xpEsperado, $duracao, $tematico]) {
            $evento = $this->evento($dono, $categoria);
            foreach ($fatores as $indice => $fator) {
                $this->campanha($evento, $fator, ['nome' => "Campanha {$categoria} {$indice}"]);
            }
            $agenda = $this->agenda($evento, 'finalizado', Carbon::parse('2026-10-10 10:00:00'), $minutos);
            $inscricao = $this->inscricao($agenda, $voluntario, 'participou');
            $this->assertSame(1, app(XpService::class)->concederAgenda($agenda));
            $transacao = XpTransacao::where('inscricao_id', $inscricao->inscricao_id)->firstOrFail();
            $this->assertSame(100, $transacao->xp_base);
            $this->assertEqualsWithDelta($duracao, (float) $transacao->multiplicador_duracao, 0.000001);
            $this->assertEqualsWithDelta($tematico, (float) $transacao->multiplicador_tematico, 0.000001);
            $this->assertSame($xpEsperado, $transacao->xp_final);
            $this->assertSame($voluntario->pessoa_id, $transacao->pessoa_id);
            $this->assertSame($agenda->agenda_id, $transacao->agenda_id);
            $this->assertSame($evento->evento_id, $transacao->evento_id);
            $this->assertCount(count($fatores), $transacao->campanhas_aplicadas);
        }
        $this->assertSame(1495, (int) XpTransacao::where('pessoa_id', $voluntario->pessoa_id)->sum('xp_final'));
        $this->assertSame(1495, $voluntario->fresh()->xp_pessoa);
    }

    public function test_vigencia_inclusiva_status_e_historico_congelado(): void
    {
        $dono = $this->pessoa('organizador');
        $voluntario = $this->pessoa();
        $evento = $this->evento($dono, 'Educação XP');
        $campanha = $this->campanha($evento, 1.5, [
            'inicio' => '2026-10-05 10:00:00', 'fim' => '2026-10-07 10:00:00',
        ]);
        $this->campanha($evento, 2, [
            'nome' => 'Inativa', 'ativa' => false,
            'inicio' => '2026-10-05 10:00:00', 'fim' => '2026-10-07 10:00:00',
        ]);
        foreach ([
            ['2026-10-05 09:59:59', 100],
            ['2026-10-05 10:00:00', 150],
            ['2026-10-06 10:00:00', 150],
            ['2026-10-07 10:00:00', 150],
            ['2026-10-07 10:00:01', 100],
        ] as [$inicio, $esperado]) {
            $agenda = $this->agenda($evento, 'finalizado', Carbon::parse($inicio), 60);
            $this->inscricao($agenda, $voluntario, 'participou');
            app(XpService::class)->concederAgenda($agenda);
            $this->assertSame($esperado, XpTransacao::where('agenda_id', $agenda->agenda_id)->value('xp_final'));
        }
        $historica = XpTransacao::where('xp_final', 150)->firstOrFail();
        $fatores = $historica->campanhas_aplicadas;
        $campanha->update(['multiplicador' => 2.5, 'inicio' => now()->addDay(), 'fim' => now()->addDays(2), 'ativa' => false]);
        $this->assertSame(150, $historica->fresh()->xp_final);
        $this->assertSame($fatores, $historica->fresh()->campanhas_aplicadas);
        $this->assertSame(650, $voluntario->fresh()->xp_pessoa);
    }

    public function test_finalizacao_concede_apenas_participacoes_confirmadas_e_e_idempotente(): void
    {
        $dono = $this->pessoa('organizador');
        $evento = $this->evento($dono, 'Social XP');
        $agenda = $this->agenda($evento, 'ativo', now()->subHours(2), 60);
        $participante = $this->pessoa();
        $inscricao = $this->inscricao($agenda, $participante, 'participou');
        foreach (['não participou', 'desistente', 'pendente'] as $status) {
            $this->inscricao($agenda, $this->pessoa(), $status);
        }
        $aprovada = $this->inscricao($agenda, $this->pessoa(), 'aprovado');
        Sanctum::actingAs($dono, ['pessoa']);
        $this->patchJson("/api/v1/inscricoes/{$aprovada->inscricao_id}/presenca", ['presenca' => false])
            ->assertOk();
        $this->patchJson("/api/v1/agendas/{$agenda->agenda_id}/finalizar")->assertOk();
        $this->assertSame(1, XpTransacao::where('agenda_id', $agenda->agenda_id)->count());
        $this->assertSame($inscricao->inscricao_id, XpTransacao::firstOrFail()->inscricao_id);
        $this->assertSame(100, $participante->fresh()->xp_pessoa);
        $this->postJson("/api/v1/agendas/{$agenda->agenda_id}/consolidar-xp")
            ->assertOk()->assertJsonPath('data.transacoes_criadas', 0);
        $this->assertSame(0, app(XpService::class)->concederAgenda($agenda));
        $this->assertSame(100, $participante->fresh()->xp_pessoa);
        $this->assertSame(1, XpTransacao::count());

        $segunda = $this->agenda($evento, 'ativo', now()->subHours(3), 60);
        $this->inscricao($segunda, $participante, 'participou');
        $this->patchJson("/api/v1/agendas/{$segunda->agenda_id}/finalizar")->assertOk();
        $this->assertSame(2, XpTransacao::where('pessoa_id', $participante->pessoa_id)->count());
        $this->assertSame(200, $participante->fresh()->xp_pessoa);

        $historicaIncoerente = $this->agenda($evento, 'finalizado', now()->subHours(4), 60);
        $semConfirmacao = $this->inscricao($historicaIncoerente, $this->pessoa(), 'participou');
        $semConfirmacao->forceFill(['presenca_confirmada_em' => null])->save();
        $this->assertSame(0, app(XpService::class)->concederAgenda($historicaIncoerente));
        $this->assertSame(2, XpTransacao::count());
    }

    public function test_agenda_futura_ou_cancelada_nao_concede_e_consolida_apos_fim(): void
    {
        $dono = $this->pessoa('organizador');
        $evento = $this->evento($dono, 'Futuro XP');
        $participante = $this->pessoa();
        $futura = $this->agenda($evento, 'ativo', now()->subMinutes(30), 60);
        $this->inscricao($futura, $participante, 'participou');
        Sanctum::actingAs($dono, ['pessoa']);
        $this->patchJson("/api/v1/agendas/{$futura->agenda_id}/finalizar")->assertStatus(409);
        $this->assertSame('ativo', $futura->fresh()->status_ativo);
        $this->assertSame(0, XpTransacao::count());

        $cancelada = $this->agenda($evento, 'cancelado', now()->subDay(), 60);
        $this->inscricao($cancelada, $participante, 'participou');
        $this->postJson("/api/v1/agendas/{$cancelada->agenda_id}/consolidar-xp")->assertStatus(409);
        Carbon::setTestNow(now()->addHour());
        $this->patchJson("/api/v1/agendas/{$futura->agenda_id}/finalizar")->assertOk();
        $this->assertSame(100, $participante->fresh()->xp_pessoa);
        $this->assertSame(1, XpTransacao::count());
    }

    public function test_finalizacao_bloqueia_uma_pendencia_sem_escrita_parcial(): void
    {
        $dono = $this->pessoa('organizador');
        $evento = $this->evento($dono, 'Guarda um');
        $agenda = $this->agenda($evento, 'ativo', now()->subHours(2), 60);
        $pessoa = $this->pessoa();
        $inscricao = $this->inscricao($agenda, $pessoa, 'aprovado');
        Sanctum::actingAs($dono, ['pessoa']);

        $this->patchJson("/api/v1/agendas/{$agenda->agenda_id}/finalizar")
            ->assertStatus(409)
            ->assertJsonPath('message', 'A Agenda possui 1 inscrição aguardando confirmação de presença ou ausência.');
        $this->assertSame('ativo', $agenda->fresh()->status_ativo);
        $this->assertSame('aprovado', $inscricao->fresh()->status_inscricao);
        $this->assertNull($inscricao->fresh()->presenca);
        $this->assertNull($inscricao->fresh()->presenca_confirmada_em);
        $this->assertSame(0, XpTransacao::count());
        $this->assertSame(0, $pessoa->fresh()->xp_pessoa);
    }

    public function test_finalizacao_exige_resultado_dos_dois_e_concede_xp_so_a_quem_participou(): void
    {
        $dono = $this->pessoa('organizador');
        $evento = $this->evento($dono, 'Guarda duas');
        $agenda = $this->agenda($evento, 'ativo', now()->subHours(2), 60);
        $presente = $this->pessoa();
        $ausente = $this->pessoa();
        $primeira = $this->inscricao($agenda, $presente, 'aprovado');
        $segunda = $this->inscricao($agenda, $ausente, 'participando');
        Sanctum::actingAs($dono, ['pessoa']);
        $rota = "/api/v1/agendas/{$agenda->agenda_id}/finalizar";

        $this->patchJson($rota)->assertStatus(409)
            ->assertJsonPath('message', 'A Agenda possui 2 inscrições aguardando confirmação de presença ou ausência.');
        $this->patchJson("/api/v1/inscricoes/{$primeira->inscricao_id}/presenca", ['presenca' => true])
            ->assertOk()->assertJsonPath('data.status_inscricao', 'participou');
        $this->patchJson($rota)->assertStatus(409)
            ->assertJsonPath('message', 'A Agenda possui 1 inscrição aguardando confirmação de presença ou ausência.');
        $this->assertSame('ativo', $agenda->fresh()->status_ativo);
        $this->assertSame('participando', $segunda->fresh()->status_inscricao);
        $this->assertSame(0, XpTransacao::count());
        $this->assertSame(0, $presente->fresh()->xp_pessoa);
        $this->assertSame(0, $ausente->fresh()->xp_pessoa);

        $this->patchJson("/api/v1/inscricoes/{$segunda->inscricao_id}/presenca", ['presenca' => false])
            ->assertOk()->assertJsonPath('data.status_inscricao', 'não participou');
        $this->patchJson($rota)->assertOk();
        $this->assertSame('finalizado', $agenda->fresh()->status_ativo);
        $this->assertSame(1, XpTransacao::count());
        $this->assertSame($primeira->inscricao_id, XpTransacao::firstOrFail()->inscricao_id);
        $this->assertSame(100, $presente->fresh()->xp_pessoa);
        $this->assertSame(0, $ausente->fresh()->xp_pessoa);
        $this->patchJson($rota)->assertStatus(409);
        $this->postJson("/api/v1/agendas/{$agenda->agenda_id}/consolidar-xp")
            ->assertOk()->assertJsonPath('data.transacoes_criadas', 0);
        $this->assertSame(1, XpTransacao::count());
        $this->assertSame(100, $presente->fresh()->xp_pessoa);
    }

    public function test_estados_nao_aprovados_e_agenda_vazia_nao_bloqueiam_finalizacao(): void
    {
        $dono = $this->pessoa('organizador');
        $evento = $this->evento($dono, 'Sem pendências');
        Sanctum::actingAs($dono, ['pessoa']);
        foreach (['pendente', 'reprovado', 'desistente', null] as $status) {
            $agenda = $this->agenda($evento, 'ativo', now()->subHours(2), 60);
            if ($status !== null) {
                $this->inscricao($agenda, $this->pessoa(), $status);
            }
            $this->patchJson("/api/v1/agendas/{$agenda->agenda_id}/finalizar")->assertOk();
            $this->assertSame('finalizado', $agenda->fresh()->status_ativo);
        }
        $this->assertSame(0, XpTransacao::count());
    }

    public function test_confirmadas_nao_bloqueiam_e_permissoes_tempo_e_estado_terminal_permanecem(): void
    {
        $dono = $this->pessoa('organizador');
        $evento = $this->evento($dono, 'Confirmadas');
        $agenda = $this->agenda($evento, 'ativo', now()->subHours(2), 60);
        $presente = $this->pessoa();
        $ausente = $this->pessoa();
        $this->inscricao($agenda, $presente, 'participou');
        $this->inscricao($agenda, $ausente, 'não participou');
        $outro = $this->pessoa('organizador');
        Sanctum::actingAs($outro, ['pessoa']);
        $this->patchJson("/api/v1/agendas/{$agenda->agenda_id}/finalizar")->assertForbidden();
        Sanctum::actingAs($dono, ['pessoa']);
        $this->patchJson("/api/v1/agendas/{$agenda->agenda_id}/finalizar")->assertOk();
        $this->assertSame(1, XpTransacao::count());
        $this->assertSame(100, $presente->fresh()->xp_pessoa);
        $this->assertSame(0, $ausente->fresh()->xp_pessoa);

        $futura = $this->agenda($evento, 'ativo', now()->subMinutes(30), 60);
        $this->patchJson("/api/v1/agendas/{$futura->agenda_id}/finalizar")->assertStatus(409);
        $cancelada = $this->agenda($evento, 'cancelado', now()->subHours(2), 60);
        $this->patchJson("/api/v1/agendas/{$cancelada->agenda_id}/finalizar")->assertStatus(409);
        $this->patchJson("/api/v1/agendas/{$agenda->agenda_id}/finalizar")->assertStatus(409);
        $this->assertSame(1, XpTransacao::count());
    }

    public function test_status_consolidado_sem_fato_coerente_nao_finaliza_automaticamente(): void
    {
        $dono = $this->pessoa('organizador');
        $evento = $this->evento($dono, 'Fatos incoerentes');
        $agenda = $this->agenda($evento, 'ativo', now()->subHours(2), 60);
        $presente = $this->pessoa();
        $ausente = $this->pessoa();
        $semConfirmacao = $this->inscricao($agenda, $presente, 'participou');
        $semConfirmacao->forceFill(['presenca_confirmada_em' => null])->save();
        $presencaContraditoria = $this->inscricao($agenda, $ausente, 'não participou');
        $presencaContraditoria->forceFill(['presenca' => true])->save();
        Sanctum::actingAs($dono, ['pessoa']);

        $this->patchJson("/api/v1/agendas/{$agenda->agenda_id}/finalizar")
            ->assertStatus(409)
            ->assertJsonPath('message', 'A Agenda possui 2 inscrições aguardando confirmação de presença ou ausência.');
        $this->assertSame('ativo', $agenda->fresh()->status_ativo);
        $this->assertNull($semConfirmacao->fresh()->presenca_confirmada_em);
        $this->assertTrue($presencaContraditoria->fresh()->presenca);
        $this->assertSame(0, XpTransacao::count());
        $this->assertSame(0, $presente->fresh()->xp_pessoa);
        $this->assertSame(0, $ausente->fresh()->xp_pessoa);
    }

    public function test_ranking_global_regional_visibilidade_desempate_e_paginacao(): void
    {
        $primeira = $this->pessoa('voluntario', ['cidade_pessoa' => 'Guarulhos']);
        $segunda = $this->pessoa('voluntario', ['cidade_pessoa' => 'São Paulo']);
        $terceira = $this->pessoa('voluntario', ['cidade_pessoa' => 'Guarulhos']);
        foreach ([[$primeira, 300], [$segunda, 300], [$terceira, 100]] as [$pessoa, $xp]) {
            $pessoa->forceFill(['xp_pessoa' => $xp])->save();
        }
        foreach ([
            $this->pessoa('voluntario', ['status_validacao' => 'aguardando_validacao']),
            $this->pessoa('voluntario', ['status_validacao' => 'reprovado']),
            $this->pessoa('administrador'),
            $this->pessoa('organizador'),
        ] as $invisivel) {
            $invisivel->forceFill(['xp_pessoa' => 999])->save();
        }
        $global = $this->getJson('/api/v1/ranking?per_page=2')->assertOk()->json('data.ranking');
        $this->assertSame(3, $global['total']);
        $this->assertSame([$primeira->pessoa_id, $segunda->pessoa_id], array_column(array_column($global['data'], 'pessoa'), 'pessoa_id'));
        $this->assertSame([1, 2], array_column($global['data'], 'posicao'));
        $pagina2 = $this->getJson('/api/v1/ranking?per_page=2&page=2')->assertOk()->json('data.ranking.data');
        $this->assertSame(3, $pagina2[0]['posicao']);
        $this->assertSame($terceira->pessoa_id, $pagina2[0]['pessoa']['pessoa_id']);
        $this->assertArrayNotHasKey('email_pessoa', $global['data'][0]['pessoa']);
        $this->assertArrayNotHasKey('cpf_pessoa', $global['data'][0]['pessoa']);

        $regional = $this->getJson('/api/v1/ranking?cidade=Guarulhos')->assertOk()->json('data');
        $this->assertSame('Guarulhos', $regional['cidade']);
        $this->assertSame(2, $regional['ranking']['total']);
        $this->assertSame([300, 100], array_column($regional['ranking']['data'], 'xp'));
        $terceira->forceFill(['cidade_pessoa' => 'São Paulo'])->save();
        $this->assertSame(1, $this->getJson('/api/v1/ranking?cidade=Guarulhos')->assertOk()->json('data.ranking.total'));
        $this->assertSame(100, $terceira->fresh()->xp_pessoa);
        $this->getJson('/api/v1/ranking?cidade=')->assertUnprocessable();
    }

    public function test_permissoes_validacao_e_status_de_campanha(): void
    {
        $categoria = CatEvento::firstOrCreate(['nome_categoria' => 'Campanha Admin']);
        $payload = [
            'nome' => 'Mutirão temático', 'multiplicador' => 1.2,
            'inicio' => '2026-10-01 10:00:00', 'fim' => '2026-10-31 10:00:00',
            'ativa' => true, 'cat_evento_id' => $categoria->cat_evento_id,
        ];
        $voluntario = $this->pessoa();
        $this->grantPermission($voluntario, 'xp.campanhas.gerenciar');
        Sanctum::actingAs($voluntario, ['pessoa']);
        $this->postJson('/api/v1/admin/campanhas-xp', $payload)->assertForbidden();
        $semPermissao = $this->pessoa('administrador');
        Sanctum::actingAs($semPermissao, ['pessoa']);
        $this->postJson('/api/v1/admin/campanhas-xp', $payload)->assertForbidden();
        $admin = $this->pessoa('administrador');
        $this->grantPermission($admin, 'xp.campanhas.gerenciar');
        Sanctum::actingAs($admin, ['pessoa']);
        foreach (['inicio', 'fim'] as $campo) {
            $invalido = $payload;
            unset($invalido[$campo]);
            $this->postJson('/api/v1/admin/campanhas-xp', $invalido)->assertUnprocessable();
        }
        foreach ([0, -1] as $valor) {
            $this->postJson('/api/v1/admin/campanhas-xp', array_merge($payload, ['multiplicador' => $valor]))->assertUnprocessable();
        }
        $this->postJson('/api/v1/admin/campanhas-xp', array_merge($payload, ['fim' => '2026-09-30']))->assertUnprocessable();
        $id = $this->postJson('/api/v1/admin/campanhas-xp', $payload)
            ->assertCreated()->json('data.campanha_xp_id');
        $this->patchJson("/api/v1/admin/campanhas-xp/{$id}", ['multiplicador' => 1.5])->assertOk();
        $this->patchJson("/api/v1/admin/campanhas-xp/{$id}/status", ['ativa' => false])->assertOk();
        $this->assertFalse(CampanhaXp::findOrFail($id)->ativa);
        $master = $this->pessoa('administrador', ['admin_master' => true]);
        Sanctum::actingAs($master, ['pessoa']);
        $this->patchJson("/api/v1/admin/campanhas-xp/{$id}/status", ['ativa' => true])->assertOk();
    }

    private function pessoa(string $role = 'voluntario', array $overrides = []): Pessoa
    {
        $id = $this->sequence++;

        return $this->createPessoa($role, array_merge([
            'cpf_pessoa' => str_pad((string) $id, 11, '0', STR_PAD_LEFT),
            'email_pessoa' => "xp{$id}@example.com", 'login_pessoa' => "xp{$id}",
        ], $overrides));
    }

    private function evento(Pessoa $dono, string $categoria): Evento
    {
        $categoriaId = CatEvento::firstOrCreate(['nome_categoria' => $categoria])->cat_evento_id;
        $evento = new Evento([
            'nm_evento' => 'Evento XP', 'descricao_evento' => 'Ação comunitária',
            'vagas_evento' => 20, 'modalidade_evento' => 'online',
            'imagem_evento_link' => 'evento.jpg', 'cat_evento_id' => $categoriaId,
            'status_evento' => 'ativo',
        ]);
        $evento->forceFill(['pessoa_id' => $dono->pessoa_id, 'responsavel_pessoa_id' => $dono->pessoa_id])->save();

        return $evento;
    }

    private function agenda(Evento $evento, string $status, Carbon $inicio, int $minutos): Agenda
    {
        return Agenda::create([
            'evento_id' => $evento->evento_id, 'status_ativo' => $status,
            'data_inicio' => $inicio, 'data_fim' => $inicio->copy()->addMinutes($minutos),
            'vagas_agenda' => 20,
        ]);
    }

    private function inscricao(Agenda $agenda, Pessoa $pessoa, string $status): Inscricao
    {
        return Inscricao::create([
            'agenda_id' => $agenda->agenda_id, 'pessoa_id' => $pessoa->pessoa_id,
            'status_inscricao' => $status, 'dt_inscricao' => $agenda->data_inicio->copy()->subDay(),
            'presenca' => $status === 'participou' ? true : ($status === 'não participou' ? false : null),
            'presenca_confirmada_em' => in_array($status, ['participou', 'não participou'], true) ? $agenda->data_inicio : null,
        ]);
    }

    private function campanha(Evento $evento, float $fator, array $overrides = []): CampanhaXp
    {
        return CampanhaXp::create(array_merge([
            'nome' => 'Campanha XP', 'multiplicador' => $fator,
            'inicio' => '2026-10-01 00:00:00', 'fim' => '2026-10-12 23:59:59',
            'ativa' => true, 'cat_evento_id' => $evento->cat_evento_id,
        ], $overrides));
    }
}
