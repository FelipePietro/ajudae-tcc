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
            $table->enum('status_confirmacao', ['lido', 'não lido', 'confirmado', 'desconfirmado']);
            $table->string('mensagem_notificacao', 255);

            $table->unsignedBigInteger('agenda_id');
            $table->foreign('agenda_id')->references('agenda_id')->on('agenda') ->cascadeOnDelete();

            $table->unsignedBigInteger('pessoa_id');
            $table->foreign('pessoa_id')->references('pessoa_id')->on('pessoa') ->cascadeOnDelete();

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
