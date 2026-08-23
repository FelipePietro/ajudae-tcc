<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evento', function (Blueprint $table) {
            $table->id('evento_id');

            $table->string('logradouro_evento', 64);
            $table->char('cep_evento', 8);
            $table->string('cidade_evento', 64);
            $table->string('bairro_evento', 64);
            $table->char('uf_evento', 2);
            $table->string('compl_evento', 64)->nullable();

            $table->string('nm_evento', 64);
            $table->string('descricao_evento', 256);
            $table->unsignedInteger('vagas_evento');

            $table->enum('modalidade_evento', [
                'presencial',
                'online',
                'hibrido'
            ]);

            $table->enum('status_evento', [
                'aguardando a confirmação',
                'ativo',
                'cancelado',
                'finalizado',
                'reprovado'
            ])->default('aguardando a confirmação');

            $table->string('imagem_evento_link', 255);

            $table->unsignedBigInteger('ong_id')->nullable();
            $table->foreign('ong_id')
                ->references('ong_id')
                ->on('ong');

            $table->unsignedBigInteger('pessoa_id')->nullable();
            $table->foreign('pessoa_id')
                ->references('pessoa_id')
                ->on('pessoa')
                -> restrictOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evento');
    }
};