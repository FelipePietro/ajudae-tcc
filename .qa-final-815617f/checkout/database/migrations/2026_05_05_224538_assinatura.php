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
            $table->string('assinante_type');
            $table->unsignedBigInteger('assinante_id');
            $table->string('tipo_termo', 64);
            $table->string('versao_termo', 32);
            $table->timestamp('aceito_em')->useCurrent();
            $table->ipAddress('ip_assinatura')->nullable();
            $table->timestamps();
            $table->string('user_agent_assinatura', 255)->nullable();
            $table->char('documento_hash', 64)->nullable();
            $table->index(['assinante_type', 'assinante_id']);
            $table->unique(
                ['assinante_type', 'assinante_id', 'tipo_termo', 'versao_termo'],
                'assinatura_aceite_unique'
            );
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
