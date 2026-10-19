<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\Acompanhamento;
use App\Models\AvaliacaoVoluntario;
use App\Models\CatEvento;
use App\Models\Evento;
use App\Models\Inscricao;
use App\Models\Pessoa;
use App\Models\SolicitacaoPadrinho;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PadrinhoWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 70000;

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

    public function test_elegibilidade_tem_limites_de_dias_presencas_media_e_capacidade(): void
    {
        $padrinho = $this->pessoa();
        $padrinho->forceFill(['aprovado_em' => now()->subDays(60)])->save();
        $dono = $this->pessoa('organizador');
        $evento = $this->evento($dono);
        for ($i = 0; $i < 10; $i++) {
            $this->participacao($evento, $padrinho, now()->subDays(11 - $i));
        }
        $this->avaliacao($evento, $padrinho, $dono, 5);
        $outro = $this->evento($dono);
        $this->avaliacao($outro, $padrinho, $dono, 4);
        $afilhado = $this->pessoa();

        Sanctum::actingAs($afilhado, ['pessoa']);
        $this->getJson('/api/v1/padrinhos/elegiveis')->assertOk();
        $this->postJson('/api/v1/padrinhos/solicitacoes', ['padrinho_id' => $padrinho->pessoa_id])->assertCreated();
        $this->postJson('/api/v1/padrinhos/solicitacoes', ['padrinho_id' => $padrinho->pessoa_id])->assertStatus(409);
        $pedido = SolicitacaoPadrinho::firstOrFail();
        $this->postJson("/api/v1/padrinhos/solicitacoes/{$pedido->getKey()}/cancelar")->assertOk();
        $this->assertSame('cancelada', $pedido->fresh()->status);

        $padrinho->forceFill(['aprovado_em' => now()->subDays(60)->addSecond()])->save();
        $this->postJson('/api/v1/padrinhos/solicitacoes', ['padrinho_id' => $padrinho->pessoa_id])->assertStatus(409);
        $padrinho->forceFill(['aprovado_em' => now()->subDays(60)])->save();
        $avaliacao = AvaliacaoVoluntario::where('pessoa_id', $padrinho->pessoa_id)->where('nota', 5)->firstOrFail();
        $avaliacao->update(['nota' => 4]);
        $this->postJson('/api/v1/padrinhos/solicitacoes', ['padrinho_id' => $padrinho->pessoa_id])->assertStatus(409);
        $avaliacao->update(['nota' => 5]);
        $presenca = Inscricao::where('pessoa_id', $padrinho->pessoa_id)->firstOrFail();
        $presenca->update(['presenca' => false]);
        $this->postJson('/api/v1/padrinhos/solicitacoes', ['padrinho_id' => $padrinho->pessoa_id])->assertStatus(409);
        $presenca->update(['presenca' => true]);
        $this->participacao($evento, $afilhado, now()->subDays(4));
        for ($i = 0; $i < 4; $i++) {
            $this->participacao($evento, $afilhado, now()->subDays(3 - $i));
        }
        $this->postJson('/api/v1/padrinhos/solicitacoes', ['padrinho_id' => $padrinho->pessoa_id])->assertStatus(409);
    }

    public function test_aceite_revalida_capacidade_e_preserva_historico_de_recusa_e_encerramento(): void
    {
        [$padrinho] = $this->padrinhoElegivel();
        $afilhados = [$this->pessoa(), $this->pessoa(), $this->pessoa()];
        $pedidos = [];
        foreach ($afilhados as $afilhado) {
            Sanctum::actingAs($afilhado, ['pessoa']);
            $this->postJson('/api/v1/padrinhos/solicitacoes', ['padrinho_id' => $padrinho->pessoa_id])->assertCreated();
            $pedidos[] = SolicitacaoPadrinho::where('apadrinhado_id', $afilhado->pessoa_id)->firstOrFail();
        }
        Sanctum::actingAs($afilhados[0], ['pessoa']);
        $this->postJson("/api/v1/padrinhos/solicitacoes/{$pedidos[1]->getKey()}/aceitar")->assertNotFound();
        Sanctum::actingAs($padrinho, ['pessoa']);
        foreach ([0, 1] as $i) {
            $this->postJson("/api/v1/padrinhos/solicitacoes/{$pedidos[$i]->getKey()}/aceitar")->assertOk();
        }
        $this->postJson("/api/v1/padrinhos/solicitacoes/{$pedidos[0]->getKey()}/aceitar")->assertOk();
        $this->assertSame(2, Acompanhamento::where('status', 'ativo')->count());
        $this->postJson("/api/v1/padrinhos/solicitacoes/{$pedidos[2]->getKey()}/aceitar")->assertStatus(409);
        $vinculo = Acompanhamento::where('apadrinhado_id', $afilhados[0]->pessoa_id)->firstOrFail();
        $this->postJson("/api/v1/padrinhos/vinculos/{$vinculo->getKey()}/encerrar", [])->assertUnprocessable();
        $this->postJson("/api/v1/padrinhos/vinculos/{$vinculo->getKey()}/encerrar", ['motivo' => 'Encerramento antecipado'])->assertOk();
        $this->assertSame('encerrado', $vinculo->fresh()->status);
        $this->postJson("/api/v1/padrinhos/solicitacoes/{$pedidos[2]->getKey()}/aceitar")->assertOk();
        $this->assertSame(2, Acompanhamento::where('status', 'ativo')->count());
        $this->assertSame(3, Acompanhamento::count());
        $padrinho->forceFill(['aprovado_em' => now()->subDay()])->save();
        $this->assertSame(2, Acompanhamento::where('status', 'ativo')->count());
    }

    public function test_conclusao_exige_seis_meses_e_cinco_agendas_durante_vinculo_com_admin_autorizado(): void
    {
        [$padrinho, $evento] = $this->padrinhoElegivel();
        $afilhado = $this->pessoa();
        Sanctum::actingAs($afilhado, ['pessoa']);
        $this->postJson('/api/v1/padrinhos/solicitacoes', ['padrinho_id' => $padrinho->pessoa_id])->assertCreated();
        $pedido = SolicitacaoPadrinho::firstOrFail();
        Sanctum::actingAs($padrinho, ['pessoa']);
        $this->postJson("/api/v1/padrinhos/solicitacoes/{$pedido->getKey()}/aceitar")->assertOk();
        $vinculo = Acompanhamento::firstOrFail();
        $this->participacao($evento, $afilhado, now()->subDay());
        Carbon::setTestNow(now()->addMonthsNoOverflow(6)->subSecond());
        Sanctum::actingAs($afilhado, ['pessoa']);
        $url = "/api/v1/padrinhos/vinculos/{$vinculo->getKey()}/solicitar-conclusao";
        $this->postJson($url)->assertStatus(409);
        Carbon::setTestNow(now()->addSecond());
        $this->postJson($url)->assertStatus(409);
        for ($i = 0; $i < 5; $i++) {
            $this->participacao($evento, $afilhado, now()->subDays(5 - $i));
        }
        $this->postJson($url)->assertCreated();
        $this->assertSame('ativo', $vinculo->fresh()->status);
        $conclusao = $vinculo->conclusoes()->firstOrFail();
        $admin = $this->pessoa('administrador');
        Sanctum::actingAs($admin, ['pessoa']);
        $adminUrl = "/api/v1/admin/padrinhos/conclusoes/{$conclusao->getKey()}/aprovar";
        $this->postJson($adminUrl)->assertForbidden();
        $this->grantPermission($admin, 'padrinhos.validar_conclusao');
        $this->postJson($adminUrl)->assertOk();
        $this->assertSame('concluido', $vinculo->fresh()->status);
        $this->postJson($adminUrl)->assertStatus(409);
    }

    public function test_ausencias_e_desistencias_penalizaveis_respeitam_janela_de_noventa_dias(): void
    {
        [$padrinho, $evento] = $this->padrinhoElegivel();
        $afilhado = $this->pessoa();
        Sanctum::actingAs($afilhado, ['pessoa']);
        $url = '/api/v1/padrinhos/solicitacoes';
        $ausencia = $this->ocorrencia($evento, $padrinho, 'não participou', now()->subDays(90));
        $this->postJson($url, ['padrinho_id' => $padrinho->pessoa_id])->assertStatus(409);
        $ausencia->update(['presenca_confirmada_em' => now()->subDays(90)->subSecond()]);
        $primeira = $this->ocorrencia($evento, $padrinho, 'desistente', now()->subDays(90));
        $segunda = $this->ocorrencia($evento, $padrinho, 'desistente', now()->subDays(2));
        $this->postJson($url, ['padrinho_id' => $padrinho->pessoa_id])->assertStatus(409);
        $primeira->update(['desistencia_sem_penalidade' => true]);
        $this->postJson($url, ['padrinho_id' => $padrinho->pessoa_id])->assertCreated();
        $pedido = SolicitacaoPadrinho::firstOrFail();
        Sanctum::actingAs($padrinho, ['pessoa']);
        $this->postJson("/api/v1/padrinhos/solicitacoes/{$pedido->getKey()}/aceitar")->assertOk();
        $this->assertSame('ativo', Acompanhamento::firstOrFail()->status);
        $this->assertTrue($segunda->fresh()->desistencia_tardia);
    }

    public function test_rejeicao_administrativa_mantem_vinculo_e_permite_novo_pedido(): void
    {
        [$padrinho, $evento] = $this->padrinhoElegivel();
        $afilhado = $this->pessoa();
        Sanctum::actingAs($afilhado, ['pessoa']);
        $this->postJson('/api/v1/padrinhos/solicitacoes', ['padrinho_id' => $padrinho->pessoa_id])->assertCreated();
        $pedido = SolicitacaoPadrinho::firstOrFail();
        Sanctum::actingAs($padrinho, ['pessoa']);
        $this->postJson("/api/v1/padrinhos/solicitacoes/{$pedido->getKey()}/aceitar")->assertOk();
        $vinculo = Acompanhamento::firstOrFail();
        Carbon::setTestNow(now()->addMonthsNoOverflow(6));
        for ($i = 0; $i < 5; $i++) {
            $this->participacao($evento, $afilhado, now()->subDays($i + 1));
        }
        Sanctum::actingAs($afilhado, ['pessoa']);
        $url = "/api/v1/padrinhos/vinculos/{$vinculo->getKey()}/solicitar-conclusao";
        $this->postJson($url)->assertCreated();
        $conclusao = $vinculo->conclusoes()->firstOrFail();
        $admin = $this->pessoa('administrador');
        $this->grantPermission($admin, 'padrinhos.validar_conclusao');
        Sanctum::actingAs($admin, ['pessoa']);
        $this->postJson("/api/v1/admin/padrinhos/conclusoes/{$conclusao->getKey()}/reprovar", [
            'motivo' => 'Verificação adicional necessária',
        ])->assertOk();
        $this->assertSame('ativo', $vinculo->fresh()->status);
        $this->assertSame('rejeitada', $conclusao->fresh()->status);
        Sanctum::actingAs($afilhado, ['pessoa']);
        $this->postJson($url)->assertCreated();
        $this->assertSame(2, $vinculo->conclusoes()->count());
    }

    private function padrinhoElegivel(): array
    {
        $padrinho = $this->pessoa();
        $padrinho->forceFill(['aprovado_em' => now()->subDays(60)])->save();
        $dono = $this->pessoa('organizador');
        $evento = $this->evento($dono);
        for ($i = 0; $i < 10; $i++) {
            $this->participacao($evento, $padrinho, now()->subDays(11 - $i));
        }
        $this->avaliacao($evento, $padrinho, $dono, 5);
        $this->avaliacao($this->evento($dono), $padrinho, $dono, 4);

        return [$padrinho, $evento];
    }

    private function pessoa(string $role = 'voluntario'): Pessoa
    {
        $id = $this->seq++;

        return $this->createPessoa($role, [
            'cpf_pessoa' => str_pad((string) $id, 11, '0', STR_PAD_LEFT),
            'email_pessoa' => "p{$id}@example.com", 'login_pessoa' => "p{$id}",
        ]);
    }

    private function evento(Pessoa $dono): Evento
    {
        $evento = new Evento([
            'nm_evento' => 'Mutirão', 'descricao_evento' => 'Atividade comunitária',
            'vagas_evento' => 100, 'modalidade_evento' => 'online',
            'imagem_evento_link' => 'evento.jpg',
            'cat_evento_id' => CatEvento::firstOrCreate(['nome_categoria' => 'Educação'])->cat_evento_id,
            'status_evento' => 'ativo',
        ]);
        $evento->forceFill(['pessoa_id' => $dono->pessoa_id, 'responsavel_pessoa_id' => $dono->pessoa_id])->save();

        return $evento;
    }

    private function participacao(Evento $evento, Pessoa $pessoa, Carbon $quando): void
    {
        $agenda = Agenda::create([
            'evento_id' => $evento->evento_id, 'status_ativo' => 'finalizado',
            'data_inicio' => $quando, 'data_fim' => $quando->copy()->addHour(), 'vagas_agenda' => 100,
        ]);
        Inscricao::create([
            'agenda_id' => $agenda->agenda_id, 'pessoa_id' => $pessoa->pessoa_id,
            'status_inscricao' => 'participou', 'dt_inscricao' => $quando->copy()->subDay(),
            'presenca' => true, 'presenca_confirmada_em' => $quando->copy()->addHour(),
        ]);
    }

    private function avaliacao(Evento $evento, Pessoa $pessoa, Pessoa $dono, int $nota): void
    {
        AvaliacaoVoluntario::create([
            'evento_id' => $evento->evento_id, 'pessoa_id' => $pessoa->pessoa_id,
            'nota' => $nota, 'avaliador_tipo' => 'pessoa', 'avaliador_id' => $dono->pessoa_id,
        ]);
    }

    private function ocorrencia(Evento $evento, Pessoa $pessoa, string $status, Carbon $quando): Inscricao
    {
        $agenda = Agenda::create([
            'evento_id' => $evento->evento_id,
            'status_ativo' => $status === 'desistente' ? 'ativo' : 'finalizado',
            'data_inicio' => $quando, 'data_fim' => $quando->copy()->addHour(), 'vagas_agenda' => 100,
        ]);

        return Inscricao::create([
            'agenda_id' => $agenda->agenda_id, 'pessoa_id' => $pessoa->pessoa_id,
            'status_inscricao' => $status, 'dt_inscricao' => $quando->copy()->subDay(),
            'presenca' => $status === 'não participou' ? false : null,
            'presenca_confirmada_em' => $status === 'não participou' ? $quando : null,
            'desistiu_em' => $status === 'desistente' ? $quando : null,
            'desistencia_tardia' => $status === 'desistente',
            'desistencia_sem_penalidade' => false,
        ]);
    }
}
