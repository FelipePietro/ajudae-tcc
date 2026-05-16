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
        Schema::create('recurso', function (Blueprint $table) {
            $table->id('id_recurso');
            $table->string('nome_recurso', 255);
            $table->string('descricao_recurso', 255);
        });
            Schema::create('pessoa_recurso', function (Blueprint $table) {
            $table->id('id_recurso_pessoa');

            $table->unsignedBigInteger('id_recurso');
            $table->foreign('id_recurso')->references('id_recurso')->on('recurso');

            $table->unsignedBigInteger('id_pessoa');
            $table->foreign('id_pessoa')->references('id_pessoa')->on('pessoa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurso_pessoa');
        Schema::dropIfExists('recurso');
        
    }
};
