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
            $table->id('recurso_id');
            $table->string('nome_recurso', 255);
            $table->string('descricao_recurso', 255);
        });
            Schema::create('pessoa_recurso', function (Blueprint $table) {
            $table->id('pessoa_recurso_id');

            $table->unsignedBigInteger('recurso_id');
            $table->foreign('recurso_id')->references('recurso_id')->on('recurso') ->cascadeOnDelete();

            $table->unsignedBigInteger('pessoa_id');
            $table->foreign('pessoa_id')->references('pessoa_id')->on('pessoa') ->cascadeOnDelete();
        

            $table->string('detalhes_recurso', 255);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pessoa_recurso');
        Schema::dropIfExists('recurso');
        
    }
};
