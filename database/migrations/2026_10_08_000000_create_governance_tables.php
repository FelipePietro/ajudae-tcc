<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissao', function (Blueprint $table) {
            $table->id('permissao_id');
            $table->string('nome', 80)->unique();
            $table->string('descricao', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('permissao_pessoa', function (Blueprint $table) {
            $table->foreignId('pessoa_id')->constrained('pessoa', 'pessoa_id')->cascadeOnDelete();
            $table->foreignId('permissao_id')->constrained('permissao', 'permissao_id')->cascadeOnDelete();
            $table->primary(['pessoa_id', 'permissao_id']);
        });

        Schema::create('admin_log', function (Blueprint $table) {
            $table->id('admin_log_id');
            $table->foreignId('pessoa_id')->nullable()->constrained('pessoa', 'pessoa_id')->nullOnDelete();
            $table->string('acao', 80);
            $table->string('recurso_type', 120)->nullable();
            $table->unsignedBigInteger('recurso_id')->nullable();
            $table->json('detalhes')->nullable();
            $table->timestamps();
            $table->index(['recurso_type', 'recurso_id']);
        });

        Schema::create('historico_validacao_pessoa', function (Blueprint $table) {
            $table->id('historico_validacao_pessoa_id');
            $table->foreignId('pessoa_id')->constrained('pessoa', 'pessoa_id')->restrictOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('pessoa', 'pessoa_id')->nullOnDelete();
            $table->string('status_anterior', 32)->nullable();
            $table->string('status_novo', 32);
            $table->text('motivo')->nullable();
            $table->unsignedInteger('numero_tentativa')->nullable();
            $table->timestamp('iniciada_em')->nullable();
            $table->timestamp('concluida_em')->nullable();
            $table->timestamps();
            $table->index(['pessoa_id', 'created_at'], 'historico_validacao_pessoa_data_index');
            $table->index(['pessoa_id', 'status_novo', 'created_at'], 'historico_validacao_status_data_index');
        });

        Schema::create('documento_pessoa', function (Blueprint $table) {
            $table->id('documento_pessoa_id');
            $table->foreignId('pessoa_id')->constrained('pessoa', 'pessoa_id')->restrictOnDelete();
            $table->string('tipo', 40);
            $table->string('storage_path', 255)->nullable();
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->char('sha256', 64)->nullable();
            $table->string('status', 20)->default('pendente');
            $table->boolean('ativo')->default(true);
            $table->timestamp('uploaded_at');
            $table->timestamp('validated_at')->nullable();
            $table->foreignId('validated_by_admin_id')->nullable()->constrained('pessoa', 'pessoa_id')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('delete_after')->nullable();
            $table->timestamp('arquivo_excluido_em')->nullable();
            $table->timestamps();
            $table->index(['pessoa_id', 'tipo', 'ativo'], 'documento_pessoa_tipo_ativo_index');
            $table->index(['status', 'ativo'], 'documento_pessoa_status_ativo_index');
            $table->index('delete_after');
        });

        Schema::create('acompanhamento', function (Blueprint $table) {
            $table->id('acompanhamento_id');
            $table->foreignId('padrinho_id')->constrained('pessoa', 'pessoa_id')->restrictOnDelete();
            $table->foreignId('apadrinhado_id')->constrained('pessoa', 'pessoa_id')->restrictOnDelete();
            $table->string('status', 32)->default('ativo');
            $table->date('inicio');
            $table->date('fim')->nullable();
            $table->text('observacao')->nullable();
            $table->timestamps();
            $table->index(['padrinho_id', 'apadrinhado_id'], 'acompanhamento_dupla_index');
        });

        Schema::create('monitoria', function (Blueprint $table) {
            $table->id('monitoria_id');
            $table->foreignId('inscricao_id')->unique()->constrained('p_ag_inscreve', 'inscricao_id')->restrictOnDelete();
            $table->foreignId('monitor_id')->nullable()->constrained('pessoa', 'pessoa_id')->nullOnDelete();
            $table->boolean('presenca')->nullable();
            $table->decimal('carga_horaria', 6, 2)->nullable();
            $table->timestamp('validado_em')->nullable();
            $table->text('observacao')->nullable();
            $table->timestamps();
        });

        Schema::create('certificado', function (Blueprint $table) {
            $table->id('certificado_id');
            $table->foreignId('evento_id')->constrained('evento', 'evento_id')->restrictOnDelete();
            $table->foreignId('pessoa_id')->constrained('pessoa', 'pessoa_id')->restrictOnDelete();
            $table->unique(['evento_id', 'pessoa_id']);
            $table->string('codigo_verificacao', 64)->unique();
            $table->unsignedInteger('total_minutos');
            $table->string('nome_participante', 255);
            $table->string('nome_evento', 64);
            $table->string('nome_responsavel', 255);
            $table->timestamp('emitido_em');
            $table->timestamps();
        });

        Schema::create('solicitacao_lgpd', function (Blueprint $table) {
            $table->id('solicitacao_lgpd_id');
            $table->string('solicitante_type');
            $table->unsignedBigInteger('solicitante_id');
            $table->enum('tipo', ['acesso', 'correcao', 'exclusao', 'portabilidade', 'informacao']);
            $table->enum('status', ['pendente', 'em_analise', 'concluida', 'recusada'])->default('pendente');
            $table->text('descricao')->nullable();
            $table->text('resposta')->nullable();
            $table->foreignId('respondida_por')->nullable()->constrained('pessoa', 'pessoa_id')->nullOnDelete();
            $table->timestamp('respondida_em')->nullable();
            $table->timestamps();
            $table->index(['solicitante_type', 'solicitante_id']);
        });

        Schema::create('solicitacao_organizador', function (Blueprint $table) {
            $table->id('solicitacao_organizador_id');
            $table->foreignId('pessoa_id')->constrained('pessoa', 'pessoa_id')->restrictOnDelete();
            $table->enum('status', ['pendente', 'em_analise', 'aprovada', 'reprovada'])->default('pendente');
            $table->text('justificativa');
            $table->foreignId('admin_id')->nullable()->constrained('pessoa', 'pessoa_id')->nullOnDelete();
            $table->text('motivo_decisao')->nullable();
            $table->timestamp('solicitada_em');
            $table->timestamp('analise_iniciada_em')->nullable();
            $table->timestamp('decidida_em')->nullable();
            $table->timestamps();
            $table->index(['pessoa_id', 'status'], 'solicitacao_organizador_pessoa_status_index');
            $table->index(['status', 'solicitada_em'], 'solicitacao_organizador_fila_index');
            $table->index(['pessoa_id', 'decidida_em'], 'solicitacao_organizador_cooldown_index');
        });

        Schema::create('vinculo_ong_organizador', function (Blueprint $table) {
            $table->id('vinculo_ong_organizador_id');
            $table->foreignId('ong_id')->constrained('ong', 'ong_id')->restrictOnDelete();
            $table->foreignId('pessoa_id')->constrained('pessoa', 'pessoa_id')->restrictOnDelete();
            $table->enum('status', ['convite_pendente', 'ativo', 'recusado', 'encerrado'])->default('convite_pendente');
            $table->timestamp('convidado_em');
            $table->timestamp('respondido_em')->nullable();
            $table->timestamp('encerrado_em')->nullable();
            $table->timestamps();
            $table->index(['pessoa_id', 'status'], 'vinculo_organizador_status_index');
            $table->index(['ong_id', 'status'], 'vinculo_ong_status_index');
            $table->index(['ong_id', 'pessoa_id', 'status'], 'vinculo_ong_pessoa_status_index');
        });

        Schema::create('evento_causa', function (Blueprint $table) {
            $table->foreignId('evento_id')->constrained('evento', 'evento_id')->restrictOnDelete();
            $table->foreignId('causa_id')->constrained('causa', 'causa_id')->restrictOnDelete();
            $table->primary(['evento_id', 'causa_id']);
        });

        Schema::create('evento_recurso', function (Blueprint $table) {
            $table->foreignId('evento_id')->constrained('evento', 'evento_id')->restrictOnDelete();
            $table->foreignId('recurso_id')->constrained('recurso', 'recurso_id')->restrictOnDelete();
            $table->primary(['evento_id', 'recurso_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evento_recurso');
        Schema::dropIfExists('evento_causa');
        Schema::dropIfExists('vinculo_ong_organizador');
        Schema::dropIfExists('solicitacao_organizador');
        Schema::dropIfExists('solicitacao_lgpd');
        Schema::dropIfExists('certificado');
        Schema::dropIfExists('monitoria');
        Schema::dropIfExists('acompanhamento');
        Schema::dropIfExists('documento_pessoa');
        Schema::dropIfExists('historico_validacao_pessoa');
        Schema::dropIfExists('admin_log');
        Schema::dropIfExists('permissao_pessoa');
        Schema::dropIfExists('permissao');
    }
};
