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
        Schema::create('evento', function (Blueprint $table) {
            $table->id('id_evento');
            $table->string('logradouro_evento', 64);
            $table->char('cep_evento', 8);
            $table->string('cidade_evento', 64);
            $table->string('bairro_evento', 64);
            $table->char('uf_evento', 2);
            $table->string('nm_evento', 64);
            $table->string('descricao_evento', 256);
            $table->enum('status_evento', ['cancelado', 'finalizado', 'aguardando a confirmação', 'reprovado']);
            $table->string('imagem_evento_link', 255);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evento');
    }
};
