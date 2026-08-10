<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evento', function (Blueprint $table) {
            $table->unsignedBigInteger('cat_evento_id');

            $table->foreign('cat_evento_id')
                ->references('cat_evento_id')
                ->on('cat_evento');
        });
    }

    public function down(): void
    {
        Schema::table('evento', function (Blueprint $table) {
            $table->dropForeign(['cat_evento_id']);
            $table->dropColumn('cat_evento_id');
        });
    }
};