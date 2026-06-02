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
        Schema::create('cat_evento', function (Blueprint $table) {
            $table->id('cat_evento_id');
            $table->string('nome_categoria', 64);

            $table->unsignedBigInteger('evento_id');
            $table->foreign('evento_id')->references('evento_id')->on('evento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cat_evento');
    }
};
