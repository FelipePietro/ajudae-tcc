<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('agenda', function (Blueprint $table) {
            $table->id('agenda_id');
            $table->enum('status_ativo', ['ativo', 'finalizado', 'cancelado'])->default('ativo');
            $table->datetime('data_inicio');
            $table->datetime('data_fim');
            $table->unsignedInteger('vagas_agenda');
            $table->text('motivo_cancelamento')->nullable();
            $table->timestamp('cancelado_em')->nullable();
            $table->string('cancelado_por_tipo', 30)->nullable();
            $table->unsignedBigInteger('cancelado_por_id')->nullable();
            $table->unsignedBigInteger('evento_id');
            $table->timestamps();

        });

        Schema::create('p_ag_inscreve', function (Blueprint $table) {
            $table->id('inscricao_id');

            $table->unsignedBigInteger('agenda_id');
            $table->foreign('agenda_id')
                ->references('agenda_id')
                ->on('agenda')
                ->restrictOnDelete();

            $table->unsignedBigInteger('pessoa_id');
            $table->foreign('pessoa_id')
                ->references('pessoa_id')
                ->on('pessoa')
                ->restrictOnDelete();

            // String para preservar os estados legados e permitir evolução sem ENUM rígido.
            $table->string('status_inscricao', 32)->default('pendente');
            $table->datetime('dt_inscricao')->useCurrent();
            $table->timestamp('desistiu_em')->nullable();
            $table->boolean('desistencia_tardia')->default(false);
            $table->boolean('desistencia_sem_penalidade')->default(false);
            $table->timestamp('desistencia_sem_penalidade_ate')->nullable();
            $table->timestamp('presenca_confirmada_em')->nullable();
            $table->boolean('presenca')->nullable();
            $table->decimal('carga_horaria', 6, 2)->nullable();
            $table->timestamps();

            // Impede inscrição duplicada na mesma agenda
            $table->unique([
                'agenda_id',
                'pessoa_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('p_ag_inscreve');
        Schema::dropIfExists('agenda');
    }
};
