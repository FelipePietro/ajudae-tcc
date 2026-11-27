<?php

namespace App\Http\Responses;

use App\Services\ManagedImageStorage;
use App\Services\RailwayStorageConfiguration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Throwable;

final class ManagedImageResponse
{
    public function serve(Request $request, Model $owner, ManagedImageStorage $images, bool $private = false): Response
    {
        if ($owner->managed_image_disk === 'firebase_images') {
            throw new ServiceUnavailableHttpException(null, 'Imagem legada indisponível; referência preservada.');
        }
        if (! $images->safeReference($owner)) {
            throw new NotFoundHttpException;
        }
        $etag = '"'.hash('sha256', $owner->managed_image_path).'"';
        $headers = [
            'Content-Type' => $owner->managed_image_mime,
            'X-Content-Type-Options' => 'nosniff',
            'Content-Disposition' => 'inline',
            'Cache-Control' => $private ? 'private, no-store' : 'public, max-age=300',
            'Content-Security-Policy' => "default-src 'none'; sandbox",
            'ETag' => $etag,
        ];
        try {
            app(RailwayStorageConfiguration::class)->assertConfigured($owner->managed_image_disk);
            $disk = Storage::disk($owner->managed_image_disk);
            if (! $disk->exists($owner->managed_image_path)
                || $disk->size($owner->managed_image_path) > 5242880) {
                throw new NotFoundHttpException;
            }
            if ($request->header('If-None-Match') === $etag) {
                return response('', 304, $headers);
            }
            $bytes = $disk->get($owner->managed_image_path);
        } catch (NotFoundHttpException $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new ServiceUnavailableHttpException(null, 'Imagem temporariamente indisponível.');
        }
        if ($bytes === null || strlen($bytes) > 5242880) {
            throw new NotFoundHttpException;
        }

        return response($bytes, 200, $headers + ['Content-Length' => strlen($bytes)]);
    }
}
