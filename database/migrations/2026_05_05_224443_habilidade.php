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
        Schema::create('habilidade', function (Blueprint $table) {
            $table->id('habilidade_id');
            $table->string('nome_habilidade', 255);
            $table->string('descricao_habilidade', 255);

        });

        Schema::create('habilidade_pessoa', function (Blueprint $table) {
            $table->id('habilidade_pessoa_id');

            $table->unsignedBigInteger('habilidade_id');
            $table->foreign('habilidade_id')->references('habilidade_id')->on('habilidade') ->cascadeOnDelete();

            $table->unsignedBigInteger('pessoa_id');
            $table->foreign('pessoa_id')->references('pessoa_id')->on('pessoa') ->cascadeOnDelete();

            $table->integer('nivel_habilidade', );

            $table ->unique(['habilidade_id', 'pessoa_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('habilidade_pessoa');
        Schema::dropIfExists('habilidade');
        
    }
};
