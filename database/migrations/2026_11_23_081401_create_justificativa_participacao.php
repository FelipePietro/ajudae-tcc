<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('justificativa_participacao', function (Blueprint $table) {
            $table->id('justificativa_id');
            $table->foreignId('inscricao_id')->unique()->constrained('p_ag_inscreve', 'inscricao_id')->restrictOnDelete();
            $table->enum('status', ['pendente', 'deferida', 'indeferida'])->default('pendente');
            $table->text('motivo');
            $table->text('motivo_decisao')->nullable();
            $table->string('decidido_por_tipo', 30)->nullable();
            $table->unsignedBigInteger('decidido_por_id')->nullable();
            $table->timestamp('decidido_em')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('justificativa_participacao');
    }
};
