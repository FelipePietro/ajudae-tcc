<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campanha_xp', function (Blueprint $table) {
            $table->id('campanha_xp_id');
            $table->string('nome', 120);
            $table->decimal('multiplicador', 12, 6);
            $table->timestamp('inicio');
            $table->timestamp('fim');
            $table->boolean('ativa');
            $table->foreignId('cat_evento_id')->constrained('cat_evento', 'cat_evento_id')->restrictOnDelete();
            $table->timestamps();
            $table->index(['cat_evento_id', 'ativa', 'inicio', 'fim'], 'campanha_xp_aplicavel_index');
        });
        DB::statement('ALTER TABLE campanha_xp ADD CONSTRAINT campanha_xp_periodo_check CHECK (fim >= inicio)');
        DB::statement('ALTER TABLE campanha_xp ADD CONSTRAINT campanha_xp_multiplicador_check CHECK (multiplicador > 0)');

        Schema::create('xp_transacao', function (Blueprint $table) {
            $table->id('xp_transacao_id');
            $table->foreignId('pessoa_id')->constrained('pessoa', 'pessoa_id')->restrictOnDelete();
            $table->foreignId('agenda_id')->constrained('agenda', 'agenda_id')->restrictOnDelete();
            $table->foreignId('inscricao_id')->unique()->constrained('p_ag_inscreve', 'inscricao_id')->restrictOnDelete();
            $table->foreignId('evento_id')->constrained('evento', 'evento_id')->restrictOnDelete();
            $table->unsignedInteger('xp_base');
            $table->decimal('multiplicador_duracao', 12, 6);
            $table->decimal('multiplicador_tematico', 12, 6);
            $table->unsignedInteger('xp_final');
            $table->json('campanhas_aplicadas');
            $table->timestamp('concedido_em');
            $table->timestamps();
            $table->index(['pessoa_id', 'concedido_em']);
            $table->index('agenda_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('xp_transacao');
        Schema::dropIfExists('campanha_xp');
    }
};
