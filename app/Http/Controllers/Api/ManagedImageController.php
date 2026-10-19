<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ValidationConflictException;
use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Models\Ong;
use App\Models\Pessoa;
use App\Services\ManagedImageStorage;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Throwable;

class ManagedImageController extends Controller
{
    public function pessoaUpload(Request $request, int $id, ManagedImageStorage $images)
    {
        return $this->upload($request, Pessoa::findOrFail($id), $images);
    }

    public function ongUpload(Request $request, int $id, ManagedImageStorage $images)
    {
        return $this->upload($request, Ong::findOrFail($id), $images);
    }

    public function eventoUpload(Request $request, int $id, ManagedImageStorage $images)
    {
        return $this->upload($request, Evento::findOrFail($id), $images);
    }

    public function pessoaRemove(Request $request, int $id, ManagedImageStorage $images)
    {
        return $this->remove($request, Pessoa::findOrFail($id), $images);
    }

    public function ongRemove(Request $request, int $id, ManagedImageStorage $images)
    {
        return $this->remove($request, Ong::findOrFail($id), $images);
    }

    public function eventoRemove(Request $request, int $id, ManagedImageStorage $images)
    {
        return $this->remove($request, Evento::findOrFail($id), $images);
    }

    public function pessoaShow(int $id, ManagedImageStorage $images)
    {
        $pessoa = Pessoa::findOrFail($id);
        if ($pessoa->status_validacao !== 'aprovado'
            || ! in_array($pessoa->role_pessoa, ['voluntario', 'organizador'], true)) {
            throw new NotFoundHttpException;
        }

        return $this->show($pessoa, $images);
    }

    public function ongShow(int $id, ManagedImageStorage $images)
    {
        return $this->show(Ong::findOrFail($id), $images);
    }

    public function eventoShow(int $id, ManagedImageStorage $images)
    {
        $evento = Evento::findOrFail($id);
        if ($evento->status_evento !== 'ativo') {
            throw new NotFoundHttpException;
        }

        return $this->show($evento, $images);
    }

    private function upload(Request $request, Model $owner, ManagedImageStorage $images)
    {
        $this->authorizeOwner($request, $owner);
        $validated = $request->validate([
            'imagem' => ['required', 'file', 'max:5120'],
        ]);
        $prepared = $images->prepare($validated['imagem']);
        $hadImage = $owner->managed_image_path !== null;
        $updated = $images->replace($owner, $prepared['bytes'], $prepared['mime_type'], fn (Model $locked) => $this->authorizeOwner($request, $locked));

        return ApiResponse::success($this->metadata($updated, $images), 'Imagem salva.', $hadImage ? 200 : 201);
    }

    private function remove(Request $request, Model $owner, ManagedImageStorage $images)
    {
        $this->authorizeOwner($request, $owner);
        $updated = $images->remove($owner, fn (Model $locked) => $this->authorizeOwner($request, $locked));

        return ApiResponse::success($this->metadata($updated, $images), 'Imagem removida.');
    }

    private function authorizeOwner(Request $request, Model $owner): void
    {
        $user = $request->user();
        if ($owner instanceof Pessoa && (! $user instanceof Pessoa || $user->pessoa_id !== $owner->pessoa_id)) {
            abort(403);
        }
        if ($owner instanceof Ong && (! $user instanceof Ong || $user->ong_id !== $owner->ong_id)) {
            abort(403);
        }
        if ($owner instanceof Evento) {
            Gate::forUser($user)->authorize('update', $owner);
            if (in_array($owner->status_evento, ['cancelado', 'finalizado'], true)) {
                throw new ValidationConflictException('Evento terminal não pode ser alterado.');
            }
        }
    }

    private function show(Model $owner, ManagedImageStorage $images)
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
            'Cache-Control' => 'public, max-age=300',
            'Content-Security-Policy' => "default-src 'none'; sandbox",
            'ETag' => $etag,
        ];
        try {
            app(\App\Services\RailwayStorageConfiguration::class)->assertConfigured($owner->managed_image_disk);
            $disk = Storage::disk($owner->managed_image_disk);
            if (! $disk->exists($owner->managed_image_path)
                || $disk->size($owner->managed_image_path) > 5242880) {
                throw new NotFoundHttpException;
            }
            if (request()->header('If-None-Match') === $etag) {
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

    private function metadata(Model $owner, ManagedImageStorage $images): array
    {
        return [
            'url' => $images->publicUrl($owner),
            'mime_type' => $owner->managed_image_mime,
            'size_bytes' => $owner->managed_image_size,
        ];
    }
}
