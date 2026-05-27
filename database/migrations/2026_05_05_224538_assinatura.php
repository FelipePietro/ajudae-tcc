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

        Schema::create('assinatura_pessoa', function (Blueprint $table) {
            $table->id('id_assinatura_pessoa');

            $table->unsignedBigInteger('id_assinatura');
            $table->foreign('id_assinatura')->references('id_assinatura')->on('assinatura');

            $table->unsignedBigInteger('id_pessoa');
            $table->foreign('id_pessoa')->references('id_pessoa')->on('pessoa');
        });

        Schema::create('aassinatura_ong', function (Blueprint $table) {
            $table->id('id_aassinatura_ong');

            $table->unsignedBigInteger('id_assinatura');
            $table->foreign('id_assinatura')->references('id_assinatura')->on('assinatura');

            $table->unsignedBigInteger('id_ong');
            $table->foreign('id_ong')->references('id_ong')->on('ong');
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
