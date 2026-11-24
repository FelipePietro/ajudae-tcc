<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('p_ag_inscreve', function (Blueprint $table) {
            $table->boolean('declarou_estudante')->default(false);
            $table->string('presenca_confirmada_por_tipo', 30)->nullable();
            $table->unsignedBigInteger('presenca_confirmada_por_id')->nullable();
        });

        Schema::table('monitoria', function (Blueprint $table) {
            $table->boolean('confirmada')->nullable();
            $table->string('validado_por_tipo', 30)->nullable();
            $table->unsignedBigInteger('validado_por_id')->nullable();
        });

        Schema::table('evento', function (Blueprint $table) {
            $table->timestamp('finalizado_em')->nullable();
            $table->string('finalizado_por_tipo', 30)->nullable();
            $table->unsignedBigInteger('finalizado_por_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('evento', fn (Blueprint $table) => $table->dropColumn(['finalizado_em', 'finalizado_por_tipo', 'finalizado_por_id']));
        Schema::table('monitoria', fn (Blueprint $table) => $table->dropColumn(['confirmada', 'validado_por_tipo', 'validado_por_id']));
        Schema::table('p_ag_inscreve', fn (Blueprint $table) => $table->dropColumn(['declarou_estudante', 'presenca_confirmada_por_tipo', 'presenca_confirmada_por_id']));
    }
};
