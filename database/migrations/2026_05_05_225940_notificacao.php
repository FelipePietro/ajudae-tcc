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
        Schema::create('notificacao', function (Blueprint $table) {
            $table->id('notificacao_id');
            $table->enum('status_confirmacao', ['lido', 'não lido'])->default('não lido');
            $table->string('mensagem_notificacao', 255);
            $table->string('tipo_notificacao', 64)->nullable();

            $table->string('destinatario_type');
            $table->unsignedBigInteger('destinatario_id');
            $table->index(['destinatario_type', 'destinatario_id']);

            $table->unsignedBigInteger('agenda_id')->nullable();
            $table->foreign('agenda_id')->references('agenda_id')->on('agenda')->nullOnDelete();

            $table->unsignedBigInteger('evento_id')->nullable();
            $table->foreign('evento_id')->references('evento_id')->on('evento')->nullOnDelete();

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificacao');
    }
};
