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
            $table->id('causa_id');
            $table->string('nome_causa', 255);
            $table->string('descricao_causa', 255);
        });

            Schema::create('causa_pessoa', function (Blueprint $table) {
            $table->id('causa_pessoa_id');

            $table->unsignedBigInteger('causa_id');
            $table->foreign('causa_id')->references('causa_id')->on('causa') ->cascadeOnDelete();

            $table->unsignedBigInteger('pessoa_id');
            $table->foreign('pessoa_id')->references('pessoa_id')->on('pessoa') ->cascadeOnDelete();
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
