<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ong', function (Blueprint $table) {
            $table->timestamp('rg_responsavel_excluir_em')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('ong', function (Blueprint $table) {
            $table->dropIndex(['rg_responsavel_excluir_em']);
            $table->dropColumn('rg_responsavel_excluir_em');
        });
    }
};
