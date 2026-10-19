<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\AvaliacaoVoluntario;
use App\Models\CatEvento;
use App\Models\Certificado;
use App\Models\Evento;
use App\Models\Inscricao;
use App\Models\Ong;
use App\Models\Pessoa;
use App\Models\VinculoOngOrganizador;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FinalizacaoEventoCertificadoTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 9000;

    public function test_pdf_sob_demanda_usa_dados_congelados_sem_persistir_arquivo_ou_duplicar_emissao(): void
    {
        Storage::fake('local');
        Storage::fake('railway_private');
        Storage::fake('railway_images');
        $owner = $this->pessoa('organizador');
        $titular = $this->pessoa();
        $terceiro = $this->pessoa();
        $evento = $this->evento($owner);
        $this->participar($this->agenda($evento), $titular);
        $this->avaliar($evento, $titular, $owner, 5);
        Sanctum::actingAs($owner, ['pessoa']);
        $this->postJson("/api/v1/eventos/{$evento->evento_id}/finalizar")->assertOk();
        Sanctum::actingAs($titular, ['pessoa']);
        $codigo = $this->postJson("/api/v1/eventos/{$evento->evento_id}/certificado")
            ->assertOk()->json('data.codigo_verificacao');
        $certificado = Certificado::where('codigo_verificacao', $codigo)->firstOrFail();
        $evento->forceFill(['nm_evento' => 'Nome atual diferente'])->save();
        $titular->forceFill(['nm_pessoa' => 'Pessoa atual diferente'])->save();
        $html = view('certificados.documento', ['certificado' => $certificado])->render();
        $this->assertStringContainsString('Mutirão', $html);
        $this->assertStringContainsString('Maria Voluntária', $html);
        $this->assertStringNotContainsString('Nome atual diferente', $html);
        $this->assertStringNotContainsString('Pessoa atual diferente', $html);

        $url = "/api/v1/certificados/{$certificado->getKey()}/download";
        foreach ([1, 2] as $_) {
            $response = $this->get($url)->assertOk()
                ->assertHeader('Content-Type', 'application/pdf');
            $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
            $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
            $this->assertStringStartsWith('%PDF', $response->getContent());
            $this->assertStringContainsString('attachment; filename="certificado-ajudae-'.$codigo.'.pdf"',
                $response->headers->get('Content-Disposition'));
        }
        $this->assertSame(1, Certificado::count());
        $this->assertSame($codigo, Certificado::firstOrFail()->codigo_verificacao);
        $this->assertDatabaseCount('xp_transacao', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
        $this->assertSame([], Storage::disk('railway_private')->allFiles());
        $this->assertSame([], Storage::disk('railway_images')->allFiles());

        Sanctum::actingAs($terceiro, ['pessoa']);
        $this->getJson($url)->assertForbidden();
        Sanctum::actingAs($owner, ['pessoa']);
        $this->getJson($url)->assertForbidden();
        Sanctum::actingAs($this->ong(), ['ong']);
        $this->getJson($url)->assertForbidden();
        $this->app['auth']->forgetGuards();
        $this->getJson($url)->assertUnauthorized();
        Sanctum::actingAs($titular, ['pessoa']);
        $this->getJson('/api/v1/certificados/999999/download')->assertNotFound();
        $this->getJson("/api/v1/certificados/verificar/{$codigo}")
            ->assertOk()->assertJsonMissingPath('data.cpf_pessoa');
    }

    public function test_responsaveis_operacionais_finalizam_sem_admin_e_sem_avaliacao_ong(): void
    {
        $ong = $this->ong();
        $organizador = $this->pessoa('organizador');
        VinculoOngOrganizador::create([
            'ong_id' => $ong->ong_id, 'pessoa_id' => $organizador->pessoa_id,
            'status' => 'ativo', 'convidado_em' => now(), 'respondido_em' => now(),
        ]);
        foreach ([[$ong, null, $ong, 'ong'], [$ong, $organizador, $organizador, 'pessoa'],
            [$organizador, null, $organizador, 'pessoa']] as [$owner, $responsavel, $ator, $ability]) {
            $evento = $this->evento($owner, $responsavel);
            $pessoa = $this->pessoa();
            $this->participar($this->agenda($evento), $pessoa);
            $this->avaliar($evento, $pessoa, $ator, 1);
            Sanctum::actingAs($ator, [$ability]);
            $this->postJson("/api/v1/eventos/{$evento->evento_id}/finalizar")
                ->assertOk()->assertJsonPath('data.status_evento', 'finalizado');
            $this->assertSame(0, DB::table('avaliacao_ong')->where('evento_id', $evento->evento_id)->count());
            $this->postJson("/api/v1/eventos/{$evento->evento_id}/finalizar")->assertStatus(409);
        }
    }

    public function test_finalizacao_conta_agendas_e_pessoas_distintas_sem_escrita_parcial(): void
    {
        $owner = $this->pessoa('organizador');
        $evento = $this->evento($owner);
        $a = $this->agenda($evento, 'ativo');
        $b = $this->agenda($evento, 'ativo');
        Sanctum::actingAs($owner, ['pessoa']);
        $url = "/api/v1/eventos/{$evento->evento_id}/finalizar";
        $this->postJson($url)->assertStatus(409)->assertSee('2 agendas pendentes');
        $a->forceFill(['status_ativo' => 'finalizado'])->save();
        $b->forceFill(['status_ativo' => 'cancelado'])->save();
        $participante = $this->pessoa();
        $this->participar($a, $participante);
        $this->participar($this->agenda($evento), $participante);
        $outro = $this->pessoa();
        $this->participar($this->agenda($evento), $outro);
        $this->postJson($url)->assertStatus(409)->assertSee('2 pessoas');
        $this->assertSame('ativo', $evento->fresh()->status_evento);
        $this->assertSame(0, Certificado::count());
        $this->avaliar($evento, $participante, $owner, 2);
        $this->postJson($url)->assertStatus(409)->assertSee('1 pessoa');
        $this->avaliar($evento, $outro, $owner, 5);
        $this->postJson($url)->assertOk();
        $this->assertSame(2, AvaliacaoVoluntario::where('evento_id', $evento->evento_id)->count());
    }

    public function test_nao_participantes_e_agenda_vazia_nao_bloqueiam_e_estados_inadequados_sao_conflito(): void
    {
        $owner = $this->pessoa('organizador');
        foreach (['aguardando a confirmação', 'reprovado', 'cancelado', 'finalizado'] as $status) {
            $evento = $this->evento($owner, null, $status);
            Sanctum::actingAs($owner, ['pessoa']);
            $this->postJson("/api/v1/eventos/{$evento->evento_id}/finalizar")->assertStatus(409);
        }
        $evento = $this->evento($owner);
        $agenda = $this->agenda($evento);
        foreach (['não participou', 'desistente', 'reprovado', 'pendente'] as $status) {
            $this->inscricao($agenda, $this->pessoa(), $status);
        }
        $this->postJson("/api/v1/eventos/{$evento->evento_id}/finalizar")->assertOk();
        $vazio = $this->evento($owner);
        $this->agenda($vazio);
        $this->postJson("/api/v1/eventos/{$vazio->evento_id}/finalizar")->assertOk();
    }

    public function test_outros_atores_nao_finalizam_e_evento_finalizado_nao_e_editavel(): void
    {
        $ong = $this->ong();
        $responsavel = $this->pessoa('organizador');
        VinculoOngOrganizador::create([
            'ong_id' => $ong->ong_id, 'pessoa_id' => $responsavel->pessoa_id,
            'status' => 'ativo', 'convidado_em' => now(), 'respondido_em' => now(),
        ]);
        $evento = $this->evento($ong, $responsavel);
        $this->agenda($evento);
        foreach ([$this->pessoa('organizador'), $this->pessoa(), $this->pessoa('administrador')] as $ator) {
            Sanctum::actingAs($ator, ['pessoa']);
            $this->postJson("/api/v1/eventos/{$evento->evento_id}/finalizar")->assertForbidden();
        }
        Sanctum::actingAs($ong, ['ong']);
        $this->postJson("/api/v1/eventos/{$evento->evento_id}/finalizar")->assertOk();
        $this->patchJson("/api/v1/eventos/{$evento->evento_id}", ['nm_evento' => 'Alterado'])->assertStatus(409);
        $this->assertSame('Mutirão', $evento->fresh()->nm_evento);
    }

    public function test_certificado_soma_minutos_exatos_exclui_canceladas_e_e_idempotente(): void
    {
        $owner = $this->pessoa('organizador');
        $pessoa = $this->pessoa();
        $evento = $this->evento($owner);
        $this->participar($this->agenda($evento, 'finalizado', 30), $pessoa);
        $this->participar($this->agenda($evento, 'finalizado', 150), $pessoa);
        $this->participar($this->agenda($evento, 'cancelado', 90), $pessoa);
        $this->inscricao($this->agenda($evento), $pessoa, 'não participou');
        $this->avaliar($evento, $pessoa, $owner, 1);
        Sanctum::actingAs($owner, ['pessoa']);
        $this->postJson("/api/v1/eventos/{$evento->evento_id}/finalizar")->assertOk();
        Sanctum::actingAs($pessoa, ['pessoa']);
        $url = "/api/v1/eventos/{$evento->evento_id}/certificado";
        $primeiro = $this->postJson($url)->assertOk()->assertJsonPath('data.total_minutos', 180)
            ->assertJsonPath('data.horas', 3)->json('data');
        $segundo = $this->postJson($url)->assertOk()->json('data');
        $this->assertSame($primeiro, $segundo);
        $this->assertSame(1, Certificado::count());
        $this->getJson($url)->assertOk()->assertJsonPath('data.codigo_verificacao', $primeiro['codigo_verificacao']);
        $this->assertMatchesRegularExpression('/^AJD-[A-Z0-9]{32}$/', $primeiro['codigo_verificacao']);
        $this->assertDatabaseCount('xp_transacao', 0);
        $this->assertDatabaseCount('avaliacao_ong', 0);
        $this->assertDatabaseHas('certificado', ['evento_id' => $evento->evento_id,
            'pessoa_id' => $pessoa->pessoa_id, 'total_minutos' => 180]);
        $this->assertDatabaseHas('p_ag_inscreve', ['pessoa_id' => $pessoa->pessoa_id, 'status_inscricao' => 'participou']);
        $pessoa->forceFill(['nm_pessoa' => 'Nome alterado'])->save();
        $this->getJson($url)->assertJsonPath('data.nome_participante', $primeiro['nome_participante']);
    }

    public function test_inelegibilidade_privacidade_e_verificacao_publica(): void
    {
        $owner = $this->pessoa('organizador');
        $pessoa = $this->pessoa();
        $outra = $this->pessoa();
        $evento = $this->evento($owner);
        $this->participar($this->agenda($evento), $pessoa);
        Sanctum::actingAs($pessoa, ['pessoa']);
        $url = "/api/v1/eventos/{$evento->evento_id}/certificado";
        $this->postJson($url)->assertStatus(409);
        $this->avaliar($evento, $pessoa, $owner, 5);
        $this->postJson($url)->assertStatus(409);
        Sanctum::actingAs($owner, ['pessoa']);
        $this->postJson("/api/v1/eventos/{$evento->evento_id}/finalizar")->assertOk();
        Sanctum::actingAs($outra, ['pessoa']);
        $this->postJson($url)->assertStatus(409);
        $this->getJson($url)->assertNotFound();
        Sanctum::actingAs($pessoa, ['pessoa']);
        $codigo = $this->postJson($url)->assertOk()->json('data.codigo_verificacao');
        $this->assertNotEquals($evento->evento_id, $codigo);
        $publico = $this->getJson("/api/v1/certificados/verificar/{$codigo}")->assertOk()->json('data');
        $this->assertTrue($publico['valido']);
        foreach (['cpf_pessoa', 'rg_pessoa', 'email_pessoa', 'tele_pessoa', 'status_validacao', 'pessoa_id', 'evento_id'] as $campo) {
            $this->assertArrayNotHasKey($campo, $publico);
        }
        $this->getJson('/api/v1/certificados/verificar/AJD-INEXISTENTE')->assertNotFound();
    }

    public function test_unicidade_no_banco_impede_segunda_emissao_logica(): void
    {
        $owner = $this->pessoa('organizador');
        $pessoa = $this->pessoa();
        $evento = $this->evento($owner);
        $this->participar($this->agenda($evento), $pessoa);
        $this->avaliar($evento, $pessoa, $owner, 3);
        Sanctum::actingAs($owner, ['pessoa']);
        $this->postJson("/api/v1/eventos/{$evento->evento_id}/finalizar")->assertOk();
        Sanctum::actingAs($pessoa, ['pessoa']);
        $this->postJson("/api/v1/eventos/{$evento->evento_id}/certificado")->assertOk();
        $this->expectException(\Illuminate\Database\QueryException::class);
        Certificado::create([
            'evento_id' => $evento->evento_id, 'pessoa_id' => $pessoa->pessoa_id,
            'codigo_verificacao' => 'AJD-OUTROCODIGO', 'total_minutos' => 30,
            'nome_participante' => $pessoa->nm_pessoa, 'nome_evento' => $evento->nm_evento,
            'nome_responsavel' => $owner->nm_pessoa, 'emitido_em' => now(),
        ]);
    }

    public function test_certificado_recusa_falta_de_avaliacao_ausencia_e_evento_cancelado(): void
    {
        $owner = $this->pessoa('organizador');
        $pessoa = $this->pessoa();
        $evento = $this->evento($owner);
        $this->participar($this->agenda($evento, 'finalizado', 45), $pessoa);
        $evento->forceFill(['status_evento' => 'finalizado'])->save();
        Sanctum::actingAs($pessoa, ['pessoa']);
        $url = "/api/v1/eventos/{$evento->evento_id}/certificado";
        $this->postJson($url)->assertStatus(409);
        $this->assertSame(0, Certificado::count());

        $ausente = $this->evento($owner, null, 'finalizado');
        $this->inscricao($this->agenda($ausente), $pessoa, 'não participou');
        $this->avaliar($ausente, $pessoa, $owner, 5);
        $this->postJson("/api/v1/eventos/{$ausente->evento_id}/certificado")->assertStatus(409);

        $cancelado = $this->evento($owner, null, 'cancelado');
        $this->participar($this->agenda($cancelado), $pessoa);
        $this->avaliar($cancelado, $pessoa, $owner, 5);
        $this->postJson("/api/v1/eventos/{$cancelado->evento_id}/certificado")->assertStatus(409);
        Sanctum::actingAs($owner, ['pessoa']);
        $this->postJson("/api/v1/eventos/{$cancelado->evento_id}/avaliacoes/{$pessoa->pessoa_id}", ['nota' => 5])
            ->assertStatus(409);

        $this->assertSame(0, Certificado::count());
    }

    private function pessoa(string $role = 'voluntario'): Pessoa
    {
        $id = $this->sequence++;
        return $this->createPessoa($role, [
            'cpf_pessoa' => str_pad((string) $id, 11, '0', STR_PAD_LEFT),
            'email_pessoa' => "cert{$id}@example.com", 'login_pessoa' => "cert{$id}",
        ]);
    }

    private function ong(): Ong
    {
        $id = $this->sequence++;
        return $this->createOng('ativo', [
            'cnpj_ong' => str_pad((string) $id, 14, '0', STR_PAD_LEFT),
            'cpf_responsavel_ong' => str_pad((string) ($id + 1000), 11, '0', STR_PAD_LEFT),
            'email_ong' => "cert{$id}@ong.test", 'login_ong' => "cert{$id}",
        ]);
    }

    private function evento(Pessoa|Ong $owner, ?Pessoa $responsavel = null, string $status = 'ativo'): Evento
    {
        $evento = new Evento([
            'nm_evento' => 'Mutirão', 'descricao_evento' => 'Ação comunitária',
            'vagas_evento' => 10, 'modalidade_evento' => 'online',
            'imagem_evento_link' => 'evento.jpg',
            'cat_evento_id' => CatEvento::firstOrCreate(['nome_categoria' => 'Educação'])->cat_evento_id,
            'status_evento' => $status,
        ]);
        $evento->forceFill([
            'ong_id' => $owner instanceof Ong ? $owner->ong_id : null,
            'pessoa_id' => $owner instanceof Pessoa ? $owner->pessoa_id : null,
            'responsavel_pessoa_id' => $responsavel?->pessoa_id ?? ($owner instanceof Pessoa ? $owner->pessoa_id : null),
        ])->save();
        return $evento;
    }

    private function agenda(Evento $evento, string $status = 'finalizado', int $minutos = 60): Agenda
    {
        $inicio = Carbon::parse('2026-10-10 10:00:00');
        return Agenda::create([
            'evento_id' => $evento->evento_id, 'status_ativo' => $status,
            'data_inicio' => $inicio, 'data_fim' => $inicio->copy()->addMinutes($minutos),
            'vagas_agenda' => 10,
        ]);
    }

    private function participar(Agenda $agenda, Pessoa $pessoa): Inscricao
    {
        return $this->inscricao($agenda, $pessoa, 'participou');
    }

    private function inscricao(Agenda $agenda, Pessoa $pessoa, string $status): Inscricao
    {
        return Inscricao::create([
            'agenda_id' => $agenda->agenda_id, 'pessoa_id' => $pessoa->pessoa_id,
            'status_inscricao' => $status, 'dt_inscricao' => now()->subDay(),
            'presenca' => $status === 'participou' ? true : ($status === 'não participou' ? false : null),
            'presenca_confirmada_em' => in_array($status, ['participou', 'não participou'], true) ? now() : null,
        ]);
    }

    private function avaliar(Evento $evento, Pessoa $pessoa, Pessoa|Ong $ator, int $nota): void
    {
        AvaliacaoVoluntario::create([
            'evento_id' => $evento->evento_id, 'pessoa_id' => $pessoa->pessoa_id,
            'nota' => $nota, 'avaliador_tipo' => $ator instanceof Ong ? 'ong' : 'pessoa',
            'avaliador_id' => $ator->getKey(),
        ]);
    }
}
