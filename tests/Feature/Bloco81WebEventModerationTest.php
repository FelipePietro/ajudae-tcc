<?php

namespace Tests\Feature;

use App\Models\AdminLog;
use App\Models\Agenda;
use App\Models\CatEvento;
use App\Models\Evento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class Bloco81WebEventModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_capacidade_real_e_exigida_e_evento_so_e_decidido_uma_vez(): void
    {
        if (! config('identity.enabled')) {
            $this->markTestSkipped('Executar com AJUDAE_WEB_AUTH=true.');
        }
        $this->withoutVite();
        $ong = $this->createOng('ativo');
        $admin = $this->createPessoa('administrador')->refresh();
        $evento = new Evento([
            'nm_evento' => 'Mutirão de teste',
            'descricao_evento' => 'Evento sintético',
            'vagas_evento' => 3,
            'modalidade_evento' => 'online',
            'imagem_evento_link' => 'teste.webp',
            'cat_evento_id' => CatEvento::create(['nome_categoria' => 'Categoria teste F3'])->getKey(),
            'status_evento' => 'aguardando a confirmação',
        ]);
        $evento->forceFill(['ong_id' => $ong->getKey()])->save();
        Agenda::create([
            'evento_id' => $evento->getKey(),
            'status_ativo' => 'ativo',
            'data_inicio' => now()->addDay(),
            'data_fim' => now()->addDay()->addHour(),
            'vagas_agenda' => 3,
        ]);
        $indice = route('identity.admin.events.index');
        $decisao = route('identity.admin.events.decide', $evento);
        $this->getJson($indice)->assertUnauthorized();
        $this->patchJson($decisao, ['status_evento' => 'ativo'])->assertUnauthorized();

        $guard = Auth::guard('web_pessoa');
        $guard->setUser($admin);
        $this->withSession([
            $guard->getName() => $admin->getKey(),
            'web_identity' => [
                'type' => 'pessoa', 'id' => $admin->getKey(), 'version' => $admin->auth_version,
                'started_at' => now()->timestamp, 'last_activity' => now()->timestamp,
            ],
        ]);
        $this->getJson($indice.'?papel=administrador&visao=master')->assertForbidden();
        $this->patchJson($decisao, ['status_evento' => 'ativo'])->assertForbidden();

        $this->grantPermission($admin, 'eventos.aprovar');
        $this->get(route('admin.fila-eventos'))->assertOk()->assertSee('data-event-moderation', false);
        $this->getJson($indice.'?q=mutirão')->assertOk()
            ->assertJsonPath('data.data.0.titulo', 'Mutirão de teste')
            ->assertJsonPath('data.data.0.agendas.0.vagas', 3);
        $this->getJson($indice.'?q=%25')->assertOk()->assertJsonCount(0, 'data.data');
        $this->patchJson($decisao, ['status_evento' => 'reprovado'])->assertUnprocessable()
            ->assertJsonValidationErrors('motivo');
        $this->patchJson($decisao, ['status_evento' => 'reprovado', 'motivo' => 'Ajustar o horário informado.'])
            ->assertOk()->assertJsonPath('data.status_evento', 'reprovado');
        $this->patchJson($decisao, ['status_evento' => 'ativo'])->assertConflict();
        $this->getJson($indice)->assertOk()->assertJsonCount(0, 'data.data');
        $log = AdminLog::where('recurso_id', $evento->getKey())->where('acao', 'evento.reprovado')->sole();
        $this->assertSame($admin->getKey(), $log->pessoa_id);
        $this->assertSame('Ajustar o horário informado.', $log->detalhes['motivo']);
        $this->assertSame(1, $ong->notificacoes()->where('evento_id', $evento->getKey())->count());
    }
}
