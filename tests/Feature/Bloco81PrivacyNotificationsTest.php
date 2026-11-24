<?php

namespace Tests\Feature;

use App\Models\Ong;
use App\Models\Pessoa;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class Bloco81PrivacyNotificationsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null,
        ]);
        DB::purge('sqlite');

        Schema::create('pessoa', function (Blueprint $table): void {
            $table->id('pessoa_id');
            $table->string('role_pessoa')->default('voluntario');
            $table->string('status_validacao')->default('aprovado');
            $table->boolean('admin_master')->default(false);
            $table->unsignedInteger('auth_version')->default(1);
        });
        Schema::create('ong', function (Blueprint $table): void {
            $table->id('ong_id');
            $table->string('status_ong')->default('ativo');
            $table->unsignedInteger('auth_version')->default(1);
        });
        Schema::create('notificacao', function (Blueprint $table): void {
            $table->id('notificacao_id');
            $table->string('status_confirmacao')->default('não lido');
            $table->string('mensagem_notificacao');
            $table->string('tipo_notificacao')->nullable();
            $table->morphs('destinatario');
            $table->unsignedBigInteger('evento_id')->nullable();
            $table->unsignedBigInteger('agenda_id')->nullable();
            $table->timestamps();
        });
        Schema::create('solicitacao_lgpd', function (Blueprint $table): void {
            $table->id('solicitacao_lgpd_id');
            $table->morphs('solicitante');
            $table->string('tipo');
            $table->string('status')->default('pendente');
            $table->text('descricao')->nullable();
            $table->text('resposta')->nullable();
            $table->unsignedBigInteger('respondida_por')->nullable();
            $table->timestamp('respondida_em')->nullable();
            $table->timestamps();
        });
        Schema::create('admin_log', function (Blueprint $table): void {
            $table->id('admin_log_id');
            $table->unsignedBigInteger('pessoa_id')->nullable();
            $table->string('acao');
            $table->nullableMorphs('recurso');
            $table->json('detalhes')->nullable();
            $table->timestamps();
        });
        Schema::create('permissao', function (Blueprint $table): void {
            $table->id('permissao_id');
            $table->string('nome');
            $table->timestamps();
        });
        Schema::create('permissao_pessoa', function (Blueprint $table): void {
            $table->unsignedBigInteger('pessoa_id');
            $table->unsignedBigInteger('permissao_id');
        });
    }

    public function test_web_inbox_filters_and_marks_only_the_logged_in_recipient(): void
    {
        if (! config('identity.enabled')) {
            $this->markTestSkipped('Execute com AJUDAE_WEB_AUTH=true.');
        }

        $person = $this->person();
        DB::table('ong')->insert(['ong_id' => $person->getKey()]);
        $institution = Ong::findOrFail($person->getKey());
        $private = $institution->notificacoes()->create(['mensagem_notificacao' => 'Aviso privado da organização']);
        $first = $person->notificacoes()->create([
            'tipo_notificacao' => 'evento.alterado', 'mensagem_notificacao' => 'Encontro com 100% de confirmação',
        ]);
        $second = $person->notificacoes()->create([
            'tipo_notificacao' => 'agenda.cancelada', 'mensagem_notificacao' => 'Aviso de cancelamento',
        ]);

        $base = '/conta/notificacoes';
        $this->getJson($base.'/dados')->assertUnauthorized();
        $this->sessionFor($person);
        $this->getJson($base.'/dados?contexto=ong&busca=%25')->assertOk()
            ->assertHeaderContains('Cache-Control', 'no-store')
            ->assertJsonPath('data.total', 2)
            ->assertJsonPath('data.unread', 2)
            ->assertJsonPath('data.items.0.id', $first->getKey())
            ->assertJsonCount(1, 'data.items');
        $this->getJson($base.'/dados?tipo=agenda')->assertOk()
            ->assertJsonPath('data.items.0.id', $second->getKey())
            ->assertJsonCount(1, 'data.items');
        $this->getJson($base.'/dados?leitura[]=todas')->assertUnprocessable();
        $this->patchJson($base."/{$private->getKey()}/lida")->assertNotFound();
        $this->patchJson($base."/{$first->getKey()}/lida")->assertOk();
        $this->patchJson($base."/{$first->getKey()}/lida")->assertOk();
        $this->getJson($base.'/dados?leitura=lidas')->assertOk()
            ->assertJsonPath('data.unread', 1)->assertJsonCount(1, 'data.items');
        $this->patchJson($base.'/lidas')->assertOk();
        $this->getJson($base.'/dados?leitura=nao-lidas')->assertOk()
            ->assertJsonPath('data.unread', 0)->assertJsonCount(0, 'data.items');
        $this->assertSame('não lido', $private->fresh()->status_confirmacao);

        $this->flushSession();
        Auth::forgetGuards();
        $this->sessionFor($institution);
        $this->getJson($base.'/dados?contexto=pessoa')->assertOk()
            ->assertJsonPath('data.items.0.id', $private->getKey())->assertJsonCount(1, 'data.items');
        $this->patchJson($base."/{$second->getKey()}/lida")->assertNotFound();
    }

    public function test_privacy_case_identity_and_contents_are_scoped_and_logged_without_free_text(): void
    {
        $owner = $this->person();
        $reader = $this->person('administrador');
        $responder = $this->person('administrador');
        $other = $this->person('administrador');
        $this->grantPermission($reader, 'lgpd.visualizar');
        foreach ([$responder, $other] as $administrator) {
            $this->grantPermission($administrator, 'lgpd.visualizar');
            $this->grantPermission($administrator, 'lgpd.responder');
        }
        $this->authenticateBearer($owner);
        $caseId = $this->postJson('/api/v1/solicitacoes-lgpd', ['tipo' => 'acesso', 'descricao' => 'Conteúdo privado'])
            ->assertCreated()->json('data.solicitacao_lgpd_id');
        $admin = '/api/v1/admin/solicitacoes-lgpd';
        $status = $admin.'/'.$caseId.'/status';

        $this->authenticateBearer($reader);
        $this->getJson($admin)->assertOk()->assertJsonPath('data.data.0.descricao', null)
            ->assertJsonPath('data.data.0.solicitante_id', null)->assertDontSee('Conteúdo privado');
        $this->patchJson($status, ['status' => 'em_analise'])->assertForbidden();
        $this->postJson('/api/v1/solicitacoes-lgpd', ['tipo' => 'acesso'])->assertForbidden();

        $decision = ['status' => 'concluida', 'resposta' => 'Resposta privada'];
        $this->authenticateBearer($responder);
        $this->patchJson($status, $decision)->assertForbidden();
        $this->patchJson($status, ['status' => 'em_analise'])->assertOk();
        $this->getJson($admin)->assertOk()->assertJsonPath('data.data.0.descricao', 'Conteúdo privado');
        $this->patchJson($status, $decision)->assertOk()->assertJsonMissingPath('data.solicitante');
        $this->patchJson($status, $decision)->assertOk();

        $this->authenticateBearer($other);
        $this->getJson($admin)->assertOk()->assertJsonPath('data.data.0.resposta', null)
            ->assertDontSee('Resposta privada');
        $this->patchJson($status, $decision)->assertForbidden();
        $this->assertDatabaseCount('admin_log', 2);
        $this->assertDatabaseCount('notificacao', 2);
        $this->assertStringNotContainsString('privada', DB::table('admin_log')->value('detalhes'));
        $this->assertStringNotContainsString('Resposta privada', $owner->notificacoes()->latest()->value('mensagem_notificacao'));

        $this->authenticateBearer($owner);
        $this->getJson('/api/v1/minhas-solicitacoes-lgpd')->assertOk()
            ->assertJsonPath('data.data.0.resposta', 'Resposta privada');
    }

    public function test_web_privacy_registers_and_lists_only_the_logged_in_owner(): void
    {
        if (! config('identity.enabled')) {
            $this->markTestSkipped('Execute com AJUDAE_WEB_AUTH=true.');
        }

        $person = $this->person();
        DB::table('ong')->insert(['ong_id' => $person->getKey()]);
        $institution = Ong::findOrFail($person->getKey());
        $endpoint = '/conta/privacidade/solicitacoes';

        $this->getJson($endpoint)->assertUnauthorized();
        $this->sessionFor($person);
        $this->postJson($endpoint, ['tipo' => 'qualquer'])->assertUnprocessable();
        $id = $this->postJson($endpoint, ['tipo' => 'correcao', 'descricao' => 'Pedido privado da pessoa'])
            ->assertCreated()->json('data.id');
        $this->getJson($endpoint.'?status=pendente')->assertOk()
            ->assertHeaderContains('Cache-Control', 'no-store')
            ->assertJsonPath('data.items.0.id', $id)
            ->assertJsonPath('data.items.0.description', 'Pedido privado da pessoa');

        $this->flushSession();
        Auth::forgetGuards();
        $this->sessionFor($institution);
        $this->getJson($endpoint.'?contexto=pessoa')->assertOk()->assertJsonCount(0, 'data.items');
        $this->postJson($endpoint, ['tipo' => 'informacao', 'solicitante_id' => $person->getKey()])
            ->assertCreated();
        $this->getJson($endpoint)->assertOk()->assertJsonPath('data.items.0.type', 'informacao')
            ->assertJsonCount(1, 'data.items');

        $this->flushSession();
        Auth::forgetGuards();
        $this->sessionFor($this->person('administrador'));
        $this->getJson($endpoint.'?contexto=pessoa')->assertForbidden();
        $this->postJson($endpoint, ['tipo' => 'acesso'])->assertForbidden();
    }

    private function person(string $role = 'voluntario'): Pessoa
    {
        return Pessoa::findOrFail(DB::table('pessoa')->insertGetId(['role_pessoa' => $role], 'pessoa_id'));
    }

    private function authenticateBearer(Pessoa $person): void
    {
        Auth::forgetGuards();
        $token = (new PersonalAccessToken)->forceFill(['abilities' => ['pessoa']]);
        $this->actingAs($person->withAccessToken($token), 'sanctum');
    }

    private function sessionFor(Pessoa|Ong $account): void
    {
        $type = $account instanceof Pessoa ? 'pessoa' : 'ong';
        $guard = Auth::guard('web_'.$type);
        $guard->setUser($account);
        $this->withSession([
            $guard->getName() => $account->getKey(),
            'web_identity' => [
                'type' => $type, 'id' => $account->getKey(), 'version' => $account->auth_version,
                'started_at' => now()->timestamp, 'last_activity' => now()->timestamp,
            ],
        ]);
    }
}
