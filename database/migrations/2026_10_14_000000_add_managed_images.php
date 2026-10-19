<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['pessoa' => 'pfp_pessoa_link', 'ong' => 'pfp_ong_link', 'evento' => 'imagem_evento_link'] as $tableName => $legacyColumn) {
            Schema::table($tableName, function (Blueprint $table) use ($legacyColumn) {
                $table->string($legacyColumn, 255)->nullable()->change();
                $table->string('managed_image_disk', 32)->nullable();
                $table->string('managed_image_path', 160)->nullable();
                $table->string('managed_image_mime', 32)->nullable();
                $table->unsignedInteger('managed_image_size')->nullable();
            });
        }

        Schema::create('pending_image_deletions', function (Blueprint $table) {
            $table->id();
            $table->string('storage_disk', 32);
            $table->string('storage_path', 160);
            $table->timestamps();
            $table->unique(['storage_disk', 'storage_path']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_image_deletions');
        foreach (['pessoa', 'ong', 'evento'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn(['managed_image_disk', 'managed_image_path', 'managed_image_mime', 'managed_image_size']);
            });
        }
        // Links legados podem ter sido removidos pela API; não restaurar NOT NULL sem dados.
    }
};
