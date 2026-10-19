<?php

namespace App\Services;

use App\Models\Evento;
use App\Models\Ong;
use App\Models\Pessoa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Throwable;

class ManagedImageStorage
{
    private const DISKS = ['image_local', 'railway_images'];

    private RailwayStorageConfiguration $configuration;

    public function __construct(?RailwayStorageConfiguration $configuration = null)
    {
        $this->configuration = $configuration ?? new RailwayStorageConfiguration;
    }

    public function selectedDisk(): string
    {
        $disk = config('filesystems.image_storage_disk', 'image_local');
        if (! in_array($disk, self::DISKS, true)) {
            throw new RuntimeException('Disco de imagens inválido.');
        }

        $this->configuration->assertConfigured($disk);

        return $disk;
    }

    public function publicUrl(Model $owner): ?string
    {
        if (! $owner->managed_image_path) {
            return $owner->{self::legacyColumn($owner)};
        }

        $type = match (true) {
            $owner instanceof Pessoa => 'pessoas',
            $owner instanceof Ong => 'ongs',
            $owner instanceof Evento => 'eventos',
        };

        return url("/api/v1/{$type}/{$owner->getKey()}/imagem")
            .'?v='.substr(hash('sha256', $owner->managed_image_path), 0, 12);
    }

    public function prepare(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $allowed = [
            'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png', 'webp' => 'image/webp',
        ];
        $mime = $file->getMimeType();
        $bytes = @file_get_contents($file->getRealPath());
        $info = $bytes === false ? false : @getimagesizefromstring($bytes);
        $dimensionsValid = $info !== false && $info[0] > 0 && $info[1] > 0
            && $info[0] <= 8000 && $info[1] <= 8000
            && $info[0] * $info[1] <= 25000000;
        if ($dimensionsValid) {
            $this->assertProcessingCapacity($info[0], $info[1], strlen($bytes));
        }
        $decoded = $dimensionsValid ? @imagecreatefromstring($bytes) : false;

        $valid = isset($allowed[$extension]) && $mime === $allowed[$extension]
            && $dimensionsValid && $decoded !== false
            && ($info['mime'] ?? null) === $mime
            && $this->hasExactEnding($bytes, $mime);
        if (! $valid) {
            if ($decoded !== false) {
                imagedestroy($decoded);
            }
            throw ValidationException::withMessages(['imagem' => 'Imagem inválida. Use JPEG, PNG ou WebP íntegro, até 8000px por lado e 25 megapixels.']);
        }

        try {
            $clean = match ($mime) {
                'image/jpeg' => $this->sanitizeJpeg($decoded, $file),
                'image/png' => $this->sanitizePng($bytes),
                'image/webp' => $this->sanitizeWebp($bytes),
            };
        } finally {
            imagedestroy($decoded);
        }
        if ($clean === false || strlen($clean) > 5242880) {
            throw ValidationException::withMessages(['imagem' => 'Não foi possível sanitizar a imagem dentro do limite de 5 MiB.']);
        }

        return ['mime_type' => $mime, 'bytes' => $clean, 'size_bytes' => strlen($clean)];
    }

    private function assertProcessingCapacity(int $width, int $height, int $inputBytes): void
    {
        $limit = ini_get('memory_limit');
        if ($limit === false || $limit === '-1') {
            return;
        }
        $unit = strtolower(substr($limit, -1));
        $multiplier = match ($unit) {
            'g' => 1024 ** 3, 'm' => 1024 ** 2, 'k' => 1024,
            default => 1,
        };
        $bytes = (int) $limit * $multiplier;
        $estimate = $width * $height * 16 + $inputBytes * 3 + 32 * 1024 * 1024;
        if ($bytes > 0 && memory_get_usage(true) + $estimate > $bytes) {
            throw new ServiceUnavailableHttpException(null, 'Memória insuficiente para processar imagem neste servidor.');
        }
    }

    private function sanitizeJpeg(\GdImage $image, UploadedFile $file): string|false
    {
        if (! extension_loaded('exif')) {
            throw new ServiceUnavailableHttpException(null, 'Extensão EXIF indisponível.');
        }
        $exif = @exif_read_data($file->getRealPath());
        $orientation = (int) ($exif['Orientation'] ?? 1);
        if (! in_array($orientation, range(1, 8), true)) {
            return false;
        }
        if (in_array($orientation, [2, 4], true)) {
            imageflip($image, $orientation === 2 ? IMG_FLIP_HORIZONTAL : IMG_FLIP_VERTICAL);
        } elseif (in_array($orientation, [3, 5, 6, 7, 8], true)) {
            $angle = match ($orientation) {
                3 => 180, 5, 8 => 90, 6, 7 => -90,
            };
            $rotated = imagerotate($image, $angle, 0);
            if ($rotated === false) {
                return false;
            }
            $image = $rotated;
            if (in_array($orientation, [5, 7], true)) {
                imageflip($image, IMG_FLIP_VERTICAL);
            }
        }
        ob_start();
        try {
            $ok = imagejpeg($image, null, 90);
            $output = ob_get_contents();
        } finally {
            ob_end_clean();
            if (isset($rotated)) {
                imagedestroy($rotated);
            }
        }

        return $ok ? $output : false;
    }

    private function sanitizePng(string $bytes): string|false
    {
        $out = substr($bytes, 0, 8);
        $offset = 8;
        $end = strlen($bytes);
        $keep = ['IHDR', 'PLTE', 'IDAT', 'tRNS', 'acTL', 'fcTL', 'fdAT', 'IEND'];
        while ($offset + 12 <= $end) {
            $length = unpack('N', substr($bytes, $offset, 4))[1];
            $type = substr($bytes, $offset + 4, 4);
            $next = $offset + 12 + $length;
            if ($length > 5242880 || $next > $end || preg_match('/^[A-Za-z]{4}$/', $type) !== 1) {
                return false;
            }
            $data = substr($bytes, $offset + 8, $length);
            if (hash('crc32b', $type.$data, true) !== substr($bytes, $offset + 8 + $length, 4)) {
                return false;
            }
            if (in_array($type, $keep, true)) {
                $out .= substr($bytes, $offset, $next - $offset);
            } elseif (ctype_upper($type[0])) {
                // Chunk crítico desconhecido: não transformar semanticamente.
                return false;
            }
            $offset = $next;
            if ($type === 'IEND') {
                return $offset === $end ? $out : false;
            }
        }

        return false;
    }

    private function sanitizeWebp(string $bytes): string|false
    {
        $offset = 12;
        $end = strlen($bytes);
        $chunks = '';
        $keep = ['VP8X', 'VP8 ', 'VP8L', 'ALPH', 'ANIM', 'ANMF'];
        while ($offset + 8 <= $end) {
            $type = substr($bytes, $offset, 4);
            $length = unpack('V', substr($bytes, $offset + 4, 4))[1];
            $next = $offset + 8 + $length + ($length % 2);
            if ($length > 5242880 || $next > $end) {
                return false;
            }
            if (in_array($type, $keep, true)) {
                $chunk = substr($bytes, $offset, $next - $offset);
                if ($type === 'VP8X') {
                    if ($length !== 10) {
                        return false;
                    }
                    // Limpa flags ICC/EXIF/XMP, mantendo alpha/animação.
                    $chunk[8] = chr(ord($chunk[8]) & 0xD3);
                }
                $chunks .= $chunk;
            } elseif (! in_array($type, ['ICCP', 'EXIF', 'XMP '], true)) {
                return false;
            }
            $offset = $next;
        }
        if ($offset !== $end) {
            return false;
        }

        return 'RIFF'.pack('V', strlen($chunks) + 4).'WEBP'.$chunks;
    }

    private function hasExactEnding(string $bytes, string $mime): bool
    {
        return match ($mime) {
            'image/jpeg' => str_starts_with($bytes, "\xff\xd8") && str_ends_with($bytes, "\xff\xd9"),
            'image/png' => str_starts_with($bytes, "\x89PNG\r\n\x1a\n")
                && str_ends_with($bytes, "\x00\x00\x00\x00IEND\xaeB`\x82"),
            'image/webp' => strlen($bytes) >= 12 && substr($bytes, 0, 4) === 'RIFF'
                && substr($bytes, 8, 4) === 'WEBP'
                && unpack('V', substr($bytes, 4, 4))[1] + 8 === strlen($bytes),
            default => false,
        };
    }

    public function safeReference(Model $owner): bool
    {
        $prefix = match (true) {
            $owner instanceof Pessoa => 'pessoas/',
            $owner instanceof Ong => 'ongs/',
            $owner instanceof Evento => 'eventos/',
        };

        $extension = match ($owner->managed_image_mime) {
            'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp',
            default => null,
        };

        return in_array($owner->managed_image_disk, self::DISKS, true)
            && is_string($owner->managed_image_path)
            && $extension !== null
            && preg_match('~^'.preg_quote($prefix, '~').'[A-Za-z0-9]{40}\.'.preg_quote($extension, '~').'$~', $owner->managed_image_path) === 1
            && is_numeric($owner->managed_image_size)
            && $owner->managed_image_size > 0 && $owner->managed_image_size <= 5242880;
    }

    public function replace(Model $owner, string $bytes, string $mime, callable $authorize): Model
    {
        $disk = $this->selectedDisk();
        $prefix = match (true) {
            $owner instanceof Pessoa => 'pessoas',
            $owner instanceof Ong => 'ongs',
            $owner instanceof Evento => 'eventos',
        };
        $extension = match ($mime) {
            'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp',
        };
        $path = $prefix.'/'.Str::random(40).'.'.$extension;
        $stored = false;
        $old = null;
        try {
            $updated = DB::transaction(function () use ($owner, $bytes, $mime, $authorize, $disk, $path, &$stored, &$old) {
                $locked = $owner::query()->whereKey($owner->getKey())->lockForUpdate()->firstOrFail();
                $authorize($locked);
                $old = $this->oldReference($locked);
                $storedPath = Storage::disk($disk)->put($path, $bytes, ['visibility' => 'private']);
                if ($storedPath !== true) {
                    throw new RuntimeException('Falha ao armazenar a imagem.');
                }
                $stored = true;
                $locked->forceFill([
                    self::legacyColumn($locked) => null,
                    'managed_image_disk' => $disk,
                    'managed_image_path' => $path,
                    'managed_image_mime' => $mime,
                    'managed_image_size' => strlen($bytes),
                ])->save();
                if ($old) {
                    $this->queueDeletion(...$old);
                }

                return $locked;
            });
        } catch (Throwable $e) {
            if ($stored) {
                $this->deleteOrQueue($disk, $path);
            }
            throw $e;
        }
        if ($old) {
            $this->retryDeletion(...$old);
        }

        return $updated;
    }

    public function remove(Model $owner, callable $authorize): Model
    {
        $old = null;
        $updated = DB::transaction(function () use ($owner, $authorize, &$old) {
            $locked = $owner::query()->whereKey($owner->getKey())->lockForUpdate()->firstOrFail();
            $authorize($locked);
            $old = $this->oldReference($locked);
            $locked->forceFill([
                self::legacyColumn($locked) => null,
                'managed_image_disk' => null,
                'managed_image_path' => null,
                'managed_image_mime' => null,
                'managed_image_size' => null,
            ])->save();
            if ($old) {
                $this->queueDeletion(...$old);
            }

            return $locked;
        });
        if ($old) {
            $this->retryDeletion(...$old);
        }

        return $updated;
    }

    private static function legacyColumn(Model $owner): string
    {
        return match (true) {
            $owner instanceof Pessoa => 'pfp_pessoa_link',
            $owner instanceof Ong => 'pfp_ong_link',
            $owner instanceof Evento => 'imagem_evento_link',
        };
    }

    private function oldReference(Model $owner): ?array
    {
        return $this->safeReference($owner)
            ? [$owner->managed_image_disk, $owner->managed_image_path]
            : null;
    }

    private function queueDeletion(string $disk, string $path): void
    {
        DB::table('pending_image_deletions')->insertOrIgnore([
            'storage_disk' => $disk, 'storage_path' => $path,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function deleteOrQueue(string $disk, string $path): void
    {
        try {
            if (Storage::disk($disk)->delete($path)) {
                return;
            }
        } catch (Throwable) {
            // A referência órfã fica registrada para reconciliação posterior.
        }
        $this->queueDeletion($disk, $path);
    }

    public function retryDeletion(string $disk, string $path): void
    {
        if (! in_array($disk, self::DISKS, true)
            || preg_match('~^(pessoas|ongs|eventos)/[A-Za-z0-9]{40}\.(jpg|png|webp)$~', $path) !== 1) {
            return;
        }
        foreach (['pessoa', 'ong', 'evento'] as $table) {
            if (DB::table($table)->where('managed_image_disk', $disk)->where('managed_image_path', $path)->exists()) {
                return;
            }
        }
        try {
            $this->configuration->assertConfigured($disk);
            if (Storage::disk($disk)->delete($path) || ! Storage::disk($disk)->exists($path)) {
                DB::table('pending_image_deletions')->where('storage_disk', $disk)->where('storage_path', $path)->delete();
            }
        } catch (Throwable) {
            // Registro permanece para retry idempotente.
        }
    }
}
