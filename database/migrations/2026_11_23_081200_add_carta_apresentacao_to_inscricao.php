<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('p_ag_inscreve', function (Blueprint $table) {
            $table->text('carta_apresentacao')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('p_ag_inscreve', function (Blueprint $table) {
            $table->dropColumn('carta_apresentacao');
        });
    }
};
