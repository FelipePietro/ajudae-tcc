<?php

namespace App\Services;

use App\Models\DocumentoPessoa;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class DocumentoStorage
{
    private const DISKS = ['local', 'railway_private'];

    public function __construct(private RailwayStorageConfiguration $configuration) {}

    public function selectedDisk(): string
    {
        $disk = config('filesystems.document_private_disk', 'local');
        if (! in_array($disk, self::DISKS, true)) {
            throw new RuntimeException('Disco de documentos privados inválido.');
        }

        $this->configuration->assertConfigured($disk);

        return $disk;
    }

    public function diskFor(DocumentoPessoa $documento): string
    {
        return $this->confirmedDisk($documento->storage_disk ?: 'local');
    }

    public function confirmedDisk(string $disk): string
    {
        if (! in_array($disk, self::DISKS, true)) {
            throw new RuntimeException('Disco do documento indisponível; referência legada preservada.');
        }

        $this->configuration->assertConfigured($disk);

        return $disk;
    }

    public function safePath(?string $path): bool
    {
        return $path !== null
            && str_starts_with($path, 'documentos/')
            && ! str_contains($path, '..')
            && ! str_contains($path, '\\')
            && ! str_contains($path, ':')
            && ! str_contains($path, "\0");
    }

    public function store(UploadedFile $arquivo): array
    {
        $mime = $arquivo->getMimeType();
        $extension = match ($mime) {
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            default => throw new RuntimeException('Formato de documento não permitido.'),
        };
        $disk = $this->selectedDisk();
        $path = 'documentos/'.Str::random(40).'.'.$extension;
        $hash = hash_file('sha256', $arquivo->getRealPath());
        $size = $arquivo->getSize();
        if ($hash === false || $size === false) {
            throw new RuntimeException('Não foi possível obter os metadados do documento.');
        }
        $stored = Storage::disk($disk)->putFileAs('documentos', $arquivo, basename($path), ['visibility' => 'private']);
        if ($stored !== $path) {
            throw new RuntimeException('Não foi possível armazenar o documento.');
        }

        return [
            'disk' => $disk,
            'path' => $path,
            'mime_type' => $mime,
            'size_bytes' => $size,
            'sha256' => $hash,
        ];
    }
}
