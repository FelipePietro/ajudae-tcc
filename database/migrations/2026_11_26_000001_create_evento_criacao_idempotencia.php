<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evento_criacao_idempotencia', function (Blueprint $table) {
            $table->enum('ator_tipo', ['ong', 'pessoa']);
            $table->unsignedBigInteger('ator_id');
            $table->char('chave_hash', 64);
            $table->char('dados_hash', 64);
            $table->foreignId('evento_id')->nullable()->constrained('evento', 'evento_id')->nullOnDelete();
            $table->timestamp('criado_em')->useCurrent();
            $table->primary(['ator_tipo', 'ator_id', 'chave_hash'], 'evento_criacao_idempotencia_pk');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evento_criacao_idempotencia');
    }
};
