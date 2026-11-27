<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ong', function (Blueprint $table) {
            $table->string('rg_responsavel_storage_disk', 32)->nullable();
            $table->string('rg_responsavel_mime_type', 100)->nullable();
            $table->unsignedInteger('rg_responsavel_size_bytes')->nullable();
            $table->char('rg_responsavel_sha256', 64)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('ong', function (Blueprint $table) {
            $table->dropColumn([
                'rg_responsavel_storage_disk', 'rg_responsavel_mime_type',
                'rg_responsavel_size_bytes', 'rg_responsavel_sha256',
            ]);
        });
    }
};
