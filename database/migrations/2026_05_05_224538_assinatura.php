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
            $table->id('assinatura_id');
            $table->string('dispositivo', 100);
            $table->ipAddress('ip_assinatura');
            $table->timestamps();
            $table->string('user_agent_assinatura', 255);
            $table->string('documento_url', 255);
            $table->char('documento_hash', 64);
            $table->string('geoloc_assinatura', 100);
        });

        Schema::create('assinatura_pessoa', function (Blueprint $table) {
            $table->id('assinatura_pessoa_id');

            $table->unsignedBigInteger('assinatura_id');
            $table->foreign('assinatura_id')->references('assinatura_id')->on('assinatura');

            $table->unsignedBigInteger('pessoa_id');
            $table->foreign('pessoa_id')->references('pessoa_id')->on('pessoa');
        });

        Schema::create('aassinatura_ong', function (Blueprint $table) {
            $table->id('aassinatura_ong_id');

            $table->unsignedBigInteger('assinatura_id');
            $table->foreign('assinatura_id')->references('assinatura_id')->on('assinatura');

            $table->unsignedBigInteger('ong_id');
            $table->foreign('ong_id')->references('ong_id')->on('ong');
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
