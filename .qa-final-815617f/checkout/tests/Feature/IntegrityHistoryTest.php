<?php

namespace Tests\Feature;

use App\Models\Acompanhamento;
use App\Models\Agenda;
use App\Models\CatEvento;
use App\Models\Evento;
use App\Models\Inscricao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class IntegrityHistoryTest extends TestCase
{
    use RefreshDatabase;

    private function eventoDoOrganizador(): array
    {
        $organizador = $this->createPessoa('organizador');
        $categoria = CatEvento::create(['nome_categoria' => 'Educação']);
        $evento = new Evento([
            'nm_evento' => 'Mutirão comunitário',
            'descricao_evento' => 'Evento usado para validar a preservação do histórico.',
            'vagas_evento' => 10,
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

    public function test_evento_rejeita_updates_com_dois_ou_sem_responsavel(): void
    {
        [$organizador, $evento] = $this->eventoDoOrganizador();
        $ong = $this->createOng('ativo');

        try {
            $evento->forceFill(['ong_id' => $ong->ong_id])->save();
            $this->fail('O evento aceitou dois responsáveis.');
        } catch (\DomainException $exception) {
            $this->assertSame('Um evento deve possuir exatamente um responsável.', $exception->getMessage());
        }

        $evento->refresh();
        $evento->forceFill(['pessoa_id' => null]);

        $this->expectException(\DomainException::class);
        $evento->save();
    }

    public function test_update_api_nao_injeta_nem_troca_responsavel_do_evento(): void
    {
        [$organizador, $evento] = $this->eventoDoOrganizador();
        $ong = $this->createOng('ativo');
        Sanctum::actingAs($organizador, ['pessoa']);

        $this->patchJson("/api/v1/eventos/{$evento->evento_id}", [
            'nm_evento' => 'Nome atualizado',
            'ong_id' => $ong->ong_id,
            'pessoa_id' => null,
        ])->assertOk();

        $this->assertDatabaseHas('evento', [
            'evento_id' => $evento->evento_id,
            'nm_evento' => 'Nome atualizado',
            'pessoa_id' => $organizador->pessoa_id,
            'ong_id' => null,
        ]);
        $this->assertFalse($evento->isFillable('ong_id'));
        $this->assertFalse($evento->isFillable('pessoa_id'));
    }

    public function test_evento_e_agenda_com_inscricao_nao_sao_apagados_por_operacoes_comuns(): void
    {
        [$organizador, $evento] = $this->eventoDoOrganizador();
        $agenda = Agenda::create([
            'evento_id' => $evento->evento_id,
            'vagas_agenda' => 10,
            'status_ativo' => 'ativo',
            'data_inicio' => now()->addDays(2),
            'data_fim' => now()->addDays(2)->addHours(2),
        ]);
        $voluntario = $this->createPessoa('voluntario', [
            'cpf_pessoa' => '11144477735',
            'email_pessoa' => 'voluntario@example.com',
            'login_pessoa' => 'voluntario.historico',
        ]);
        $inscricao = Inscricao::create([
            'agenda_id' => $agenda->agenda_id,
            'pessoa_id' => $voluntario->pessoa_id,
            'status_inscricao' => 'desistente',
            'dt_inscricao' => now(),
            'desistiu_em' => now(),
        ]);
        Sanctum::actingAs($organizador, ['pessoa']);

        $this->deleteJson("/api/v1/agendas/{$agenda->agenda_id}")->assertStatus(409);
        $this->deleteJson("/api/v1/eventos/{$evento->evento_id}")->assertStatus(409);

        $this->assertDatabaseHas('evento', ['evento_id' => $evento->evento_id]);
        $this->assertDatabaseHas('agenda', ['agenda_id' => $agenda->agenda_id]);
        $this->assertDatabaseHas('p_ag_inscreve', ['inscricao_id' => $inscricao->inscricao_id]);
    }

    public function test_apenas_um_acompanhamento_ativo_e_historico_encerrado_pode_coexistir(): void
    {
        $padrinho = $this->createPessoa('voluntario');
        $apadrinhado = $this->createPessoa('voluntario', [
            'cpf_pessoa' => '11144477735',
            'email_pessoa' => 'apadrinhado@example.com',
            'login_pessoa' => 'apadrinhado',
        ]);

        $primeiro = Acompanhamento::iniciar($padrinho->pessoa_id, $apadrinhado->pessoa_id);

        try {
            Acompanhamento::iniciar($padrinho->pessoa_id, $apadrinhado->pessoa_id);
            $this->fail('A dupla aceitou dois acompanhamentos ativos.');
        } catch (\DomainException $exception) {
            $this->assertSame('Já existe um acompanhamento ativo para esta dupla.', $exception->getMessage());
        }

        $primeiro->update(['status' => 'encerrado', 'fim' => now()->toDateString()]);
        $segundo = Acompanhamento::iniciar($padrinho->pessoa_id, $apadrinhado->pessoa_id);
        $segundo->update(['status' => 'encerrado', 'fim' => now()->toDateString()]);

        $this->assertSame(2, Acompanhamento::where([
            'padrinho_id' => $padrinho->pessoa_id,
            'apadrinhado_id' => $apadrinhado->pessoa_id,
            'status' => 'encerrado',
        ])->count());
    }

    public function test_cpf_do_responsavel_pode_ser_usado_em_mais_de_uma_ong(): void
    {
        $primeira = $this->createOng('ativo');
        $segunda = $this->createOng('ativo', [
            'login_ong' => 'segunda.ong',
            'cnpj_ong' => '99888777000166',
            'email_ong' => 'segunda@ong.test',
        ]);

        $this->assertSame($primeira->cpf_responsavel_ong, $segunda->cpf_responsavel_ong);
        $this->assertSame(2, $primeira->newQuery()
            ->where('cpf_responsavel_ong', $primeira->cpf_responsavel_ong)
            ->count());
    }
}
