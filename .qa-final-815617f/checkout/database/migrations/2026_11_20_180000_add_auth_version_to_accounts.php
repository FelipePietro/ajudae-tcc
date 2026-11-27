<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['pessoa', 'ong'] as $name) {
            if (! Schema::hasColumn($name, 'auth_version')) {
                Schema::table($name, function (Blueprint $table) {
                    $table->unsignedBigInteger('auth_version')->default(0);
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['pessoa', 'ong'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropColumn('auth_version');
            });
        }
    }
};
