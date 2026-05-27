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
        Schema::create('assinatura', function (Blueprint $table) {
            $table->id('id_assinatura');
            $table->string('dispositivo', 100);
            $table->ipAddress('ip_assinatura');
            $table->timestamps();
            $table->string('user_agent_assinatura', 255);
            $table->string('documento_url', 255);
            $table->char('documento_hash', 64);
            $table->string('geoloc_assinatura', 100);
        });

        Schema::create('causa_pessoa', function (Blueprint $table) {
            $table->id('id_causa_pessoa');

            $table->unsignedBigInteger('id_causa');
            $table->foreign('id_causa')->references('id_causa')->on('causa');

            $table->unsignedBigInteger('id_pessoa');
            $table->foreign('id_pessoa')->references('id_pessoa')->on('pessoa');
        });

        Schema::create('causa_pessoa', function (Blueprint $table) {
            $table->id('id_causa_pessoa');

            $table->unsignedBigInteger('id_causa');
            $table->foreign('id_causa')->references('id_causa')->on('causa');

            $table->unsignedBigInteger('id_pessoa');
            $table->foreign('id_pessoa')->references('id_pessoa')->on('pessoa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assinatura');
    }
};
