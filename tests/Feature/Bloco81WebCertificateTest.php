<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\AvaliacaoVoluntario;
use App\Models\CatEvento;
use App\Models\Evento;
use App\Models\Inscricao;
use App\Models\Pessoa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class Bloco81WebCertificateTest extends TestCase
{
    use RefreshDatabase;

    public function test_titular_emite_e_baixa_certificado_e_verificacao_web_minimiza_dados(): void
    {
        if (! config('identity.enabled')) {
            $this->markTestSkipped('Executar com AJUDAE_WEB_AUTH=true.');
        }

        $titular = $this->createPessoa();
        $outraPessoa = $this->createPessoa(overrides: [
            'cpf_pessoa' => '11144477735', 'email_pessoa' => 'outra@example.com', 'login_pessoa' => 'outra.pessoa',
        ]);
        $ong = $this->createOng('ativo');
        $evento = new Evento([
            'nm_evento' => 'Mutirão de teste',
            'descricao_evento' => 'Atividade sintética',
            'vagas_evento' => 3,
            'modalidade_evento' => 'online',
            'imagem_evento_link' => 'teste.webp',
            'cat_evento_id' => CatEvento::create(['nome_categoria' => 'Teste Certificado Web 8.1'])->getKey(),
            'status_evento' => 'finalizado',
        ]);
        $evento->forceFill(['ong_id' => $ong->getKey(), 'finalizado_em' => now()->subDay()])->save();
        $agenda = Agenda::create([
            'evento_id' => $evento->getKey(),
            'status_ativo' => 'finalizado',
            'data_inicio' => now()->subDays(2),
            'data_fim' => now()->subDays(2)->addMinutes(90),
            'vagas_agenda' => 1,
        ]);
        Inscricao::create([
            'agenda_id' => $agenda->getKey(),
            'pessoa_id' => $titular->getKey(),
            'dt_inscricao' => now()->subDays(3),
            'status_inscricao' => 'participou',
            'presenca' => true,
            'presenca_confirmada_em' => $agenda->data_fim,
        ]);
        AvaliacaoVoluntario::create([
            'evento_id' => $evento->getKey(), 'pessoa_id' => $titular->getKey(), 'nota' => 5,
            'avaliador_tipo' => 'ong', 'avaliador_id' => $ong->getKey(),
        ]);

        $lista = route('identity.certificates.index');
        $emitir = route('identity.certificates.issue', $evento);
        $this->getJson($lista)->assertUnauthorized();
        $this->sessionFor($titular);
        $this->getJson($lista)->assertOk()
            ->assertJsonPath('data.data.0.situacao', 'disponivel')
            ->assertJsonPath('data.data.0.emitir_url', $emitir);

        $certificado = $this->postJson($emitir)->assertOk()->assertJsonPath('data.total_minutos', 90)->json('data');
        $this->postJson($emitir)->assertOk()->assertJsonPath('data.certificado_id', $certificado['certificado_id']);
        $this->getJson($lista)->assertOk()
            ->assertJsonPath('data.data.0.situacao', 'emitido')
            ->assertJsonPath('data.data.0.certificado.pdf_url', $certificado['pdf_url']);
        $pdf = $this->get($certificado['pdf_url'])->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $pdf->getContent());

        $this->sessionFor($outraPessoa);
        $this->getJson($lista)->assertOk()->assertJsonCount(0, 'data.data');
        $this->postJson($emitir)->assertConflict();
        $this->getJson($certificado['pdf_url'])->assertForbidden();
        $this->getJson(route('identity.certificates.verify', ['codigo' => $certificado['codigo_verificacao']]))
            ->assertOk()->assertJsonPath('data.valido', true)->assertJsonPath('data.total_minutos', 90)
            ->assertJsonMissingPath('data.nome_participante')->assertJsonMissingPath('data.nome_responsavel')
            ->assertJsonMissingPath('data.pessoa_id');
    }

    private function sessionFor(Pessoa $pessoa): void
    {
        $pessoa->refresh();
        $guard = Auth::guard('web_pessoa');
        $guard->setUser($pessoa);
        $this->withSession([
            $guard->getName() => $pessoa->getKey(),
            'web_identity' => [
                'type' => 'pessoa', 'id' => $pessoa->getKey(), 'version' => $pessoa->auth_version,
                'started_at' => now()->timestamp, 'last_activity' => now()->timestamp,
            ],
        ]);
    }
}
