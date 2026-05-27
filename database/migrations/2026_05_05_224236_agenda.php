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
            $table->id('id_agenda');
            $table->enum('status_ativo', ['ativo', 'finalizado']);
            $table->datetime('data_inicio');
            $table->datetime('data_fim');
            $table->timestamps();

            $table->unsignedBigInteger('id_evento');
            $table->foreign('id_evento')->references('id_evento')->on('evento');

        });

        Schema::create('p_ag_inscreve', function (Blueprint $table) {
            $table->id('id_inscricao');

            $table->unsignedBigInteger('id_agenda');
            $table->foreign('id_agenda')->references('id_agenda')->on('agenda');

            $table->unsignedBigInteger('id_pessoa');
            $table->foreign('id_pessoa')->references('id_pessoa')->on('pessoa');

            $table->enum('status_inscricao', ['inscrito', 'participando', 'participou', 'não participou']);
            $table->datetime('dt_inscricao');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agenda');
    }
};
