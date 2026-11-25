<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ManagedImageResponse;
use App\Models\Evento;
use App\Models\Ong;
use App\Models\Pessoa;
use App\Services\EventoManagementService;
use App\Services\ManagedImageStorage;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

    public function eventoUpload(Request $request, int $id, ManagedImageStorage $images, EventoManagementService $service)
    {
        $evento = Evento::findOrFail($id);
        $ator = $request->user();
        $service->autorizarEdicao($ator, $evento);
        $validated = $request->validate(['imagem' => ['required', 'file', 'max:5120']]);
        $hadImage = $evento->managed_image_path !== null;
        $updated = $service->salvarImagem($ator, $evento, $validated['imagem']);

        return ApiResponse::success($this->metadata($updated, $images), 'Imagem salva.', $hadImage ? 200 : 201);
    }

    public function pessoaRemove(Request $request, int $id, ManagedImageStorage $images)
    {
        return $this->remove($request, Pessoa::findOrFail($id), $images);
    }

    public function ongRemove(Request $request, int $id, ManagedImageStorage $images)
    {
        return $this->remove($request, Ong::findOrFail($id), $images);
    }

    public function eventoRemove(Request $request, int $id, ManagedImageStorage $images, EventoManagementService $service)
    {
        $evento = $service->removerImagem($request->user(), Evento::findOrFail($id));

        return ApiResponse::success($this->metadata($evento, $images), 'Imagem removida.');
    }

    public function pessoaShow(Request $request, int $id, ManagedImageStorage $images, ManagedImageResponse $response)
    {
        $pessoa = Pessoa::findOrFail($id);
        if ($pessoa->status_validacao !== 'aprovado'
            || ! in_array($pessoa->role_pessoa, ['voluntario', 'organizador'], true)) {
            throw new NotFoundHttpException;
        }

        return $response->serve($request, $pessoa, $images);
    }

    public function ongShow(Request $request, int $id, ManagedImageStorage $images, ManagedImageResponse $response)
    {
        return $response->serve($request, Ong::findOrFail($id), $images);
    }

    public function eventoShow(Request $request, int $id, ManagedImageStorage $images, ManagedImageResponse $response)
    {
        return $response->serve($request, Evento::publicado()->findOrFail($id), $images);
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
