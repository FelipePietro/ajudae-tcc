<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avaliacao_voluntario', function (Blueprint $table) {
            $table->id('avaliacao_voluntario_id');
            $table->foreignId('evento_id')->constrained('evento', 'evento_id')->restrictOnDelete();
            $table->foreignId('pessoa_id')->constrained('pessoa', 'pessoa_id')->restrictOnDelete();
            $table->unsignedTinyInteger('nota');
            $table->string('avaliador_tipo', 16);
            $table->unsignedBigInteger('avaliador_id');
            $table->timestamps();
            $table->unique(['evento_id', 'pessoa_id']);
        });
        DB::statement('ALTER TABLE avaliacao_voluntario ADD CONSTRAINT avaliacao_voluntario_nota_check CHECK (nota BETWEEN 1 AND 5)');

        Schema::create('avaliacao_ong', function (Blueprint $table) {
            $table->id('avaliacao_ong_id');
            $table->foreignId('evento_id')->constrained('evento', 'evento_id')->restrictOnDelete();
            $table->foreignId('pessoa_id')->constrained('pessoa', 'pessoa_id')->restrictOnDelete();
            $table->unsignedTinyInteger('nota');
            $table->timestamps();
            $table->unique(['evento_id', 'pessoa_id']);
        });
        DB::statement('ALTER TABLE avaliacao_ong ADD CONSTRAINT avaliacao_ong_nota_check CHECK (nota BETWEEN 1 AND 5)');
    }

    public function down(): void
    {
        Schema::dropIfExists('avaliacao_ong');
        Schema::dropIfExists('avaliacao_voluntario');
    }
};
