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
            $table->id('sq_notificacao');
            $table->enum('status_confirmacao', ['lido', 'não lido', 'confirmado', 'desconfirmado']);
            $table->string('mensagem_notificacao', 255);
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
