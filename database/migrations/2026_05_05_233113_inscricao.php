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
        Schema::create('inscricao', function (Blueprint $table) {
            $table->id('id_inscricao');
            $table->enum('status_inscricao', ['inscrito', 'participando', 'participou', 'não participou']);
            $table->datetime('dt_inscricao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscricao');
    }
};
