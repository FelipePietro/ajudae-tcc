<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documento_pessoa', function (Blueprint $table) {
            $table->string('storage_disk', 32)->nullable()->after('storage_path');
        });

        // Rows predating disk selection remain on the historical local disk.
        // Preserve the decision metadata while scheduling approved antecedent bytes.
        DB::table('documento_pessoa')
            ->where('tipo', 'antecedentes_criminais')
            ->where('status', 'aprovado')
            ->whereNotNull('validated_at')
            ->whereNull('delete_after')
            ->update(['delete_after' => DB::raw('DATE_ADD(validated_at, INTERVAL 30 DAY)')]);
    }

    public function down(): void
    {
        Schema::table('documento_pessoa', fn (Blueprint $table) => $table->dropColumn('storage_disk'));
    }
};
