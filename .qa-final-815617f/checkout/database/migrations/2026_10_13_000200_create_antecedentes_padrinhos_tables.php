<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documento_pessoa', function (Blueprint $table) {
            $table->timestamp('alerta_renovacao_em')->nullable()->after('expires_at');
        });

        // A última análise aprovada precisa continuar verificável durante a renovação.
        DB::table('documento_pessoa')
            ->where('tipo', 'antecedentes_criminais')
            ->where('status', 'aprovado')
            ->update(['delete_after' => null]);

        Schema::table('acompanhamento', function (Blueprint $table) {
            $table->timestamp('aceito_em')->nullable();
            $table->timestamp('encerrado_em')->nullable();
            $table->foreignId('encerrado_por_id')->nullable()->constrained('pessoa', 'pessoa_id')->restrictOnDelete();
            $table->text('motivo_encerramento')->nullable();
            $table->timestamp('concluido_em')->nullable();
            $table->foreignId('concluido_por_id')->nullable()->constrained('pessoa', 'pessoa_id')->restrictOnDelete();
            $table->unsignedBigInteger('ativo_apadrinhado_id')->nullable()
                ->storedAs("CASE WHEN status = 'ativo' THEN apadrinhado_id ELSE NULL END");
            $table->unique('ativo_apadrinhado_id', 'acompanhamento_ativo_apadrinhado_unique');
            $table->index(['padrinho_id', 'status']);
            $table->index(['apadrinhado_id', 'status']);
        });

        Schema::create('solicitacao_padrinho', function (Blueprint $table) {
            $table->id('solicitacao_padrinho_id');
            $table->foreignId('padrinho_id')->constrained('pessoa', 'pessoa_id')->restrictOnDelete();
            $table->foreignId('apadrinhado_id')->constrained('pessoa', 'pessoa_id')->restrictOnDelete();
            $table->string('status', 24)->default('pendente');
            $table->timestamp('solicitada_em');
            $table->timestamp('decidida_em')->nullable();
            $table->foreignId('acompanhamento_id')->nullable()->constrained('acompanhamento', 'acompanhamento_id')->restrictOnDelete();
            $table->unsignedBigInteger('pendente_apadrinhado_id')->nullable()
                ->storedAs("CASE WHEN status = 'pendente' THEN apadrinhado_id ELSE NULL END");
            $table->unique('pendente_apadrinhado_id', 'solicitacao_padrinho_pendente_unique');
            $table->index(['padrinho_id', 'status', 'solicitada_em'], 'solicitacao_padrinho_recebidas_index');
            $table->index(['apadrinhado_id', 'solicitada_em'], 'solicitacao_padrinho_historico_index');
            $table->timestamps();
        });

        Schema::create('conclusao_padrinho', function (Blueprint $table) {
            $table->id('conclusao_padrinho_id');
            $table->foreignId('acompanhamento_id')->constrained('acompanhamento', 'acompanhamento_id')->restrictOnDelete();
            $table->foreignId('apadrinhado_id')->constrained('pessoa', 'pessoa_id')->restrictOnDelete();
            $table->string('status', 24)->default('pendente');
            $table->timestamp('solicitada_em');
            $table->foreignId('admin_id')->nullable()->constrained('pessoa', 'pessoa_id')->restrictOnDelete();
            $table->timestamp('decidida_em')->nullable();
            $table->text('motivo_decisao')->nullable();
            $table->unsignedBigInteger('pendente_acompanhamento_id')->nullable()
                ->storedAs("CASE WHEN status = 'pendente' THEN acompanhamento_id ELSE NULL END");
            $table->unique('pendente_acompanhamento_id', 'conclusao_padrinho_pendente_unique');
            $table->index(['status', 'solicitada_em'], 'conclusao_padrinho_fila_index');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conclusao_padrinho');
        Schema::dropIfExists('solicitacao_padrinho');
        Schema::table('acompanhamento', function (Blueprint $table) {
            $table->dropUnique('acompanhamento_ativo_apadrinhado_unique');
            $table->dropIndex(['padrinho_id', 'status']);
            $table->dropIndex(['apadrinhado_id', 'status']);
            $table->dropConstrainedForeignId('encerrado_por_id');
            $table->dropConstrainedForeignId('concluido_por_id');
            $table->dropColumn(['aceito_em', 'encerrado_em', 'motivo_encerramento', 'concluido_em', 'ativo_apadrinhado_id']);
        });
        Schema::table('documento_pessoa', fn (Blueprint $table) => $table->dropColumn('alerta_renovacao_em'));
    }
};
