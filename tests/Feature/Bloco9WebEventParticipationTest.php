<?php

namespace Tests\Feature;

use App\Models\AdminLog;
use App\Models\Agenda;
use App\Models\CatEvento;
use App\Models\Evento;
use App\Models\Inscricao;
use App\Models\Ong;
use App\Models\Pessoa;
use App\Models\VinculoOngOrganizador;
use App\Models\XpTransacao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class Bloco9WebEventParticipationTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 0;

    protected function setUp(): void
    {
        parent::setUp();

        if (! config('identity.enabled')) {
            $this->markTestSkipped('Executar com AJUDAE_WEB_AUTH=true.');
        }
    }

    public function test_operacao_de_participacao_preserva_autoria_e_aplica_xp_uma_vez(): void
    {
        $ong = $this->createOng('ativo');
        $responsavel = $this->pessoa('organizador');
        $this->vincular($ong, $responsavel);
        $evento = $this->evento($ong, $responsavel);
        $agenda = $this->agenda($evento);
        $voluntario = $this->pessoa();
        $presente = $this->inscricao($agenda, $voluntario, estudante: true);
        $ausente = $this->inscricao($agenda, $this->pessoa(), 'aprovado');
        $baseInscricao = "/gestao/inscricoes/{$presente->getKey()}";
        $baseAusente = "/gestao/inscricoes/{$ausente->getKey()}";
        $baseAgenda = "/gestao/agendas/{$agenda->getKey()}";
        $baseEvento = "/gestao/eventos/{$evento->getKey()}";

        $this->getJson("$baseAgenda/inscricoes")->assertUnauthorized();
        $this->sessionFor($responsavel);
        $this->getJson("$baseAgenda/inscricoes?status=pendente")->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.data.0.pessoa_id', $voluntario->getKey())
            ->assertJsonPath('data.data.0.carta_apresentacao', 'Apresentação sintética.');
        $this->patchJson("$baseInscricao/selecao", ['status_inscricao' => 'aprovado'])->assertOk()
            ->assertJsonPath('data.status_inscricao', 'aprovado');
        $this->assertDatabaseHas('admin_log', [
            'acao' => 'inscricao.selecao', 'recurso_id' => $presente->getKey(), 'pessoa_id' => $responsavel->getKey(),
        ]);
        $this->patchJson("$baseAgenda/finalizar")->assertConflict();
        $this->patchJson("$baseInscricao/presenca", ['presenca' => true])->assertOk()
            ->assertJsonPath('data.status_inscricao', 'participou');
        $this->assertSame($responsavel->getKey(), $presente->fresh()->presenca_confirmada_por_id);

        $this->sessionFor($ong);
        $this->patchJson("$baseAusente/presenca", ['presenca' => false])->assertOk()
            ->assertJsonPath('data.status_inscricao', 'não participou');
        $this->assertSame($ong->getKey(), $ausente->fresh()->presenca_confirmada_por_id);
        $this->patchJson("$baseAgenda/finalizar")->assertOk()->assertJsonPath('data.status_ativo', 'finalizado');
        $this->patchJson("$baseAgenda/finalizar")->assertConflict();
        $log = AdminLog::where('acao', 'agenda.finalizada')->where('recurso_id', $agenda->getKey())->sole();
        $this->assertSame($ong->getKey(), $log->detalhes['ator_id']);
        $this->assertSame('finalizado', $log->detalhes['para']);
        $this->assertSame(1, XpTransacao::where('agenda_id', $agenda->getKey())->count());
        $this->assertSame(100, (int) $voluntario->fresh()->xp_pessoa);
        $this->postJson("$baseEvento/finalizar")->assertConflict();
        $this->getJson("$baseEvento/avaliacoes")->assertOk()
            ->assertJsonPath('data.pode_avaliar', true)
            ->assertJsonCount(1, 'data.participantes.data')
            ->assertJsonPath('data.participantes.data.0.pessoa_id', $voluntario->getKey())
            ->assertJsonPath('data.participantes.data.0.nota', null);
        $this->getJson("$baseInscricao/monitoria")->assertOk()->assertJsonPath('data.status', 'pendente');
        $this->patchJson("$baseInscricao/monitoria", ['confirmada' => true])->assertUnprocessable()
            ->assertJsonValidationErrors('justificativa');
        $this->patchJson("$baseInscricao/monitoria", ['confirmada' => true, 'justificativa' => 'Documento sintético conferido.'])
            ->assertOk()->assertJsonPath('data.status', 'validada');

        $this->sessionFor($ausente->pessoa);
        $this->postJson("$baseAusente/justificativa", ['motivo' => 'Justificativa sintética.'])
            ->assertCreated()->assertJsonPath('data.status', 'pendente');
        $this->patchJson("$baseAusente/justificativa", ['deferida' => true, 'motivo' => 'Tentativa própria.'])
            ->assertForbidden();

        $this->sessionFor($responsavel);
        $this->getJson("$baseAusente/justificativa")->assertOk()->assertJsonPath('data.status', 'pendente');
        $this->patchJson("$baseAusente/justificativa", ['deferida' => true, 'motivo' => 'Ocorrência sintética conferida.'])
            ->assertOk()->assertJsonPath('data.status', 'deferida');
        $this->assertSame('não participou', $ausente->fresh()->status_inscricao);

        $this->sessionFor($ong);
        $this->postJson("$baseEvento/avaliacoes/{$voluntario->getKey()}", ['nota' => 4])->assertCreated();
        $this->postJson("$baseEvento/finalizar")->assertOk()->assertJsonPath('data.status_evento', 'finalizado');
        $this->assertSame('ong', $evento->fresh()->finalizado_por_tipo);
        $this->assertSame($ong->getKey(), $evento->fresh()->finalizado_por_id);
        $this->getJson("$baseEvento/avaliacoes")->assertOk()
            ->assertJsonPath('data.pode_avaliar', false)
            ->assertJsonPath('data.participantes.data.0.nota', 4);
        $this->postJson("$baseEvento/finalizar")->assertConflict();
    }

    public function test_gestao_exige_proprietario_ou_designacao_ativa_para_o_evento_especifico(): void
    {
        $ong = $this->createOng('ativo');
        $outraOng = $this->createOng('ativo', [
            'cnpj_ong' => '00000000000001', 'login_ong' => 'outro.ong', 'email_ong' => 'outro@example.test',
        ]);
        $responsavel = $this->pessoa('organizador');
        $outroResponsavel = $this->pessoa('organizador');
        $naoDesignado = $this->pessoa('organizador');
        $independente = $this->pessoa('organizador');
        $vinculo = $this->vincular($ong, $responsavel);
        $this->vincular($ong, $outroResponsavel);
        $this->vincular($ong, $naoDesignado);
        $this->evento($ong, $outroResponsavel);
        $evento = $this->evento($ong, $responsavel);
        $agenda = $this->agenda($evento);
        $voluntario = $this->pessoa();
        $inscricao = $this->inscricao($agenda, $voluntario);
        $admin = $this->pessoa('administrador');
        $listagem = "/gestao/agendas/{$agenda->getKey()}/inscricoes";
        $selecao = "/gestao/inscricoes/{$inscricao->getKey()}/selecao";

        foreach ([$outraOng, $outroResponsavel, $naoDesignado, $independente, $voluntario, $admin] as $ator) {
            $this->sessionFor($ator);
            $this->getJson($listagem)->assertForbidden();
            $this->patchJson($selecao, ['status_inscricao' => 'aprovado'])->assertForbidden();
        }

        $evento->forceFill(['status_evento' => 'aguardando a confirmação'])->save();
        $this->sessionFor($responsavel);
        $this->getJson($listagem)->assertOk();
        $this->patchJson($selecao, ['status_inscricao' => 'aprovado'])->assertConflict();
        $evento->forceFill(['status_evento' => 'ativo'])->save();
        $vinculo->forceFill(['status' => 'encerrado', 'encerrado_em' => now()])->save();
        $this->getJson($listagem)->assertForbidden();
        $this->patchJson($selecao, ['status_inscricao' => 'aprovado'])->assertForbidden();

        $this->sessionFor($ong);
        $this->patchJson($selecao, ['status_inscricao' => 'aprovado'])->assertOk();
        $eventoIndependente = $this->evento($independente);
        $agendaIndependente = $this->agenda($eventoIndependente);
        $fimAgendaIndependente = "/gestao/agendas/{$agendaIndependente->getKey()}/finalizar";
        $this->patchJson($fimAgendaIndependente)->assertForbidden();
        $this->sessionFor($independente);
        $this->patchJson($fimAgendaIndependente)->assertOk();
        $this->postJson("/gestao/eventos/{$eventoIndependente->getKey()}/finalizar")->assertOk();
        $this->assertSame($independente->getKey(), $eventoIndependente->fresh()->finalizado_por_id);
    }

    private function pessoa(string $role = 'voluntario'): Pessoa
    {
        $id = ++$this->sequence;

        return $this->createPessoa($role, [
            'cpf_pessoa' => str_pad((string) $id, 11, '0', STR_PAD_LEFT),
            'email_pessoa' => "f4-{$id}@example.test",
            'login_pessoa' => "f4-teste-{$id}",
        ]);
    }

    private function evento(Pessoa|Ong $dono, ?Pessoa $responsavel = null): Evento
    {
        $evento = new Evento([
            'nm_evento' => 'Atividade sintética F4',
            'descricao_evento' => 'Atividade de teste',
            'vagas_evento' => 1,
            'modalidade_evento' => 'online',
            'imagem_evento_link' => 'teste.webp',
            'cat_evento_id' => CatEvento::firstOrCreate(['nome_categoria' => 'Teste F4'])->getKey(),
            'status_evento' => 'ativo',
        ]);
        $evento->forceFill([
            'ong_id' => $dono instanceof Ong ? $dono->getKey() : null,
            'pessoa_id' => $dono instanceof Pessoa ? $dono->getKey() : null,
            'responsavel_pessoa_id' => $responsavel?->getKey(),
        ])->save();

        return $evento;
    }

    private function agenda(Evento $evento): Agenda
    {
        return $evento->agendas()->create([
            'data_inicio' => now()->subHours(2),
            'data_fim' => now()->subHour(),
            'vagas_agenda' => 2,
            'status_ativo' => 'ativo',
        ]);
    }

    private function inscricao(Agenda $agenda, Pessoa $pessoa, string $status = 'pendente', bool $estudante = false): Inscricao
    {
        return $agenda->inscricoes()->create([
            'pessoa_id' => $pessoa->getKey(),
            'dt_inscricao' => now()->subDay(),
            'status_inscricao' => $status,
            'declarou_estudante' => $estudante,
            'carta_apresentacao' => 'Apresentação sintética.',
        ]);
    }

    private function vincular(Ong $ong, Pessoa $organizador): VinculoOngOrganizador
    {
        return VinculoOngOrganizador::create([
            'ong_id' => $ong->getKey(),
            'pessoa_id' => $organizador->getKey(),
            'status' => 'ativo',
            'convidado_em' => now(),
            'respondido_em' => now(),
        ]);
    }

    private function sessionFor(Pessoa|Ong $ator): void
    {
        $ator->refresh();
        $this->flushSession();
        Auth::forgetGuards();
        $type = $ator instanceof Ong ? 'ong' : 'pessoa';
        $guard = Auth::guard('web_'.$type);
        $this->withSession([
            $guard->getName() => $ator->getKey(),
            'web_identity' => [
                'type' => $type,
                'id' => $ator->getKey(),
                'version' => $ator->auth_version,
                'started_at' => now()->timestamp,
                'last_activity' => now()->timestamp,
            ],
        ]);
    }
}
