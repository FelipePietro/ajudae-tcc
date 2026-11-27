<?php

namespace App\Console\Commands;

use App\Services\ManagedImageStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReconciliarImagensPendentes extends Command
{
    protected $signature = 'imagens:reconciliar {--limit=100}';

    protected $description = 'Tenta remover objetos de imagem órfãos, sem apagar referências ainda utilizadas';

    public function handle(ManagedImageStorage $images): int
    {
        $limit = min(max((int) $this->option('limit'), 1), 1000);
        $rows = DB::table('pending_image_deletions')->orderBy('id')->limit($limit)->get();
        foreach ($rows as $row) {
            $images->retryDeletion($row->storage_disk, $row->storage_path);
        }
        $this->info("Processadas {$rows->count()} referências pendentes.");

        return self::SUCCESS;
    }
}
