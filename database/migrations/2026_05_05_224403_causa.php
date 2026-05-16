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
        Schema::create('causa', function (Blueprint $table) {
            $table->id('id_causa');
            $table->string('nome_causa', 255);
            $table->string('descricao_causa', 255);
        });

            Schema::create('causa_pessoa', function (Blueprint $table) {
            $table->id('id_causa_pessoa');

            $table->unsignedBigInteger('id_causa');
            $table->foreign('id_causa')->references('id_causa')->on('causa');

            $table->unsignedBigInteger('id_pessoa');
            $table->foreign('id_pessoa')->references('id_pessoa')->on('pessoa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('causa_pessoa');
        Schema::dropIfExists('causa');
        
    }
};
