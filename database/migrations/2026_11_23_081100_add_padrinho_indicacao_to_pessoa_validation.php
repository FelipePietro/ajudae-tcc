<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pessoa', function (Blueprint $table) {
            $table->timestamp('padrinho_exigido_em')->nullable();
            $table->foreignId('padrinho_exigido_por_id')->nullable()->constrained('pessoa', 'pessoa_id')->restrictOnDelete();
            $table->text('padrinho_exigido_motivo')->nullable();
        });

        Schema::create('codigo_indicacao_padrinho', function (Blueprint $table) {
            $table->id();
            $table->foreignId('padrinho_id')->constrained('pessoa', 'pessoa_id')->restrictOnDelete();
            $table->char('digest', 64)->unique();
            $table->text('codigo')->nullable();
            $table->timestamp('emitido_em');
            $table->timestamp('revogado_em')->nullable();
            $table->unsignedBigInteger('padrinho_ativo_id')->nullable()
                ->storedAs('CASE WHEN revogado_em IS NULL THEN padrinho_id ELSE NULL END');
            $table->unique('padrinho_ativo_id', 'codigo_indicacao_padrinho_ativo_unique');
        });

        Schema::table('solicitacao_padrinho', function (Blueprint $table) {
            $table->foreignId('codigo_indicacao_id')->nullable()->constrained('codigo_indicacao_padrinho')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('solicitacao_padrinho', function (Blueprint $table) {
            $table->dropConstrainedForeignId('codigo_indicacao_id');
        });
        Schema::dropIfExists('codigo_indicacao_padrinho');
        Schema::table('pessoa', function (Blueprint $table) {
            $table->dropConstrainedForeignId('padrinho_exigido_por_id');
            $table->dropColumn(['padrinho_exigido_em', 'padrinho_exigido_motivo']);
        });
    }
};
