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
            $table->id('id_habilidade');
            $table->string('nome_habilidade', 255);
            $table->string('descricao_habilidade', 255);

        });

        Schema::create('habilidade_pessoa', function (Blueprint $table) {
            $table->id('id_habilidade_pessoa');

            $table->unsignedBigInteger('id_habilidade');
            $table->foreign('id_habilidade')->references('id_habilidade')->on('habilidade');

            $table->unsignedBigInteger('id_pessoa');
            $table->foreign('id_pessoa')->references('id_pessoa')->on('pessoa');

            $table->integer('nivel_habilidade', );

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
