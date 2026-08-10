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
            $table->enum('status_ativo', ['ativo', 'finalizado']);
            $table->datetime('data_inicio');
            $table->datetime('data_fim');
            $table->unsignedBigInteger('evento_id');
            $table->timestamps();
        
        });

        Schema::create('p_ag_inscreve', function (Blueprint $table) {
            $table->id('inscricao_id');

            $table->unsignedBigInteger('agenda_id');
            $table->foreign('agenda_id')
                ->references('agenda_id')
                ->on('agenda')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('pessoa_id');
            $table->foreign('pessoa_id')
                ->references('pessoa_id')
                ->on('pessoa')
                ->cascadeOnDelete();

            $table->enum('status_inscricao', [
                'pendente',
                'aprovado',
                'reprovado',
                'participando',
                'participou',
                'não participou'
            ])->default('pendente');

            $table->datetime('dt_inscricao');

            // Impede inscrição duplicada na mesma agenda
            $table->unique([
                'agenda_id',
                'pessoa_id'
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
