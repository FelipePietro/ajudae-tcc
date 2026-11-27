<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('certificado')) {
            return;
        }

        // A migration de governança foi editada retroativamente. Bancos novos já
        // possuem este schema; bancos antigos tinham inscricao_id/codigo/arquivo_path.
        if (Schema::hasColumn('certificado', 'codigo_verificacao')) {
            foreach (['evento_id', 'pessoa_id', 'total_minutos', 'nome_participante', 'nome_evento', 'nome_responsavel', 'emitido_em'] as $column) {
                if (! Schema::hasColumn('certificado', $column)) {
                    throw new RuntimeException("Schema de certificado parcialmente atualizado: falta {$column}. Revisão manual necessária.");
                }
            }

            return;
        }

        foreach (['inscricao_id', 'codigo', 'arquivo_path', 'disponibilizado_em'] as $column) {
            if (! Schema::hasColumn('certificado', $column)) {
                throw new RuntimeException("Schema legado de certificado desconhecido: falta {$column}. Revisão manual necessária.");
            }
        }
        foreach (['evento_id', 'pessoa_id', 'total_minutos', 'nome_participante', 'nome_evento', 'nome_responsavel', 'emitido_em'] as $column) {
            if (Schema::hasColumn('certificado', $column)) {
                throw new RuntimeException("Schema de certificado parcialmente atualizado: {$column} já existe. Revisão manual necessária.");
            }
        }

        // O schema anterior não congelava nomes nem carga horária. Não se pode
        // fabricá-los a partir de cadastros/eventos atuais ou apagar certificados.
        if (DB::table('certificado')->exists()) {
            throw new RuntimeException('Há certificados legados. A evolução automática foi interrompida antes de qualquer DDL: preservar dados e definir migração histórica assistida.');
        }

        // MySQL pode fazer commit implícito em DDL. Só aplicar em tabela comprovadamente
        // vazia e após backup, nunca alegando rollback transacional destas mudanças.
        Schema::table('certificado', function (Blueprint $table) {
            $table->unsignedBigInteger('inscricao_id')->nullable()->change();
            $table->string('codigo', 64)->nullable()->change();
        });

        Schema::table('certificado', function (Blueprint $table) {
            $table->foreignId('evento_id')->constrained('evento', 'evento_id')->restrictOnDelete();
            $table->foreignId('pessoa_id')->constrained('pessoa', 'pessoa_id')->restrictOnDelete();
            $table->unique(['evento_id', 'pessoa_id']);
            $table->string('codigo_verificacao', 64)->unique();
            $table->unsignedInteger('total_minutos');
            $table->string('nome_participante', 255);
            $table->string('nome_evento', 64);
            $table->string('nome_responsavel', 255);
            $table->timestamp('emitido_em');
        });
    }

    public function down(): void
    {
        // Não remover colunas ou certificados: reversão exige backup e plano manual.
    }
};
