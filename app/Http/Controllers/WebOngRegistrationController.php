<?php

namespace App\Http\Controllers;

use App\Http\Requests\OngRegistrationRequest;
use App\Http\Resources\OngAdminResource;
use App\Http\Resources\OngPrivateResource;
use App\Models\AdminLog;
use App\Models\Ong;
use App\Models\Pessoa;
use App\Services\DocumentoStorage;
use App\Services\OngRegistrationService;
use App\Services\OngValidationService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class WebOngRegistrationController extends Controller
{
    public function store(OngRegistrationRequest $request, OngRegistrationService $registration)
    {
        [$ong] = $registration->register($request->validated(), $request->ip(), $request->userAgent());

        return ApiResponse::success(['ong' => new OngPrivateResource($ong)], 'ONG cadastrada com sucesso.', 201);
    }

    public function queue(Request $request)
    {
        $query = Ong::query()->where('status_ong', 'aguardando aprovação')->orderBy('ong_id');
        $filters = $request->validate(['per_page' => ['sometimes', 'integer', 'min:1', 'max:100']]);

        return ApiResponse::success(OngAdminResource::collection($query->paginate($filters['per_page'] ?? 20)));
    }

    public function decide(Request $request, Ong $ong, OngValidationService $validation)
    {
        $data = $request->validate(OngValidationService::STATUS_RULES);
        $ong = $validation->update($ong, $request->user(), $data['status_ong']);

        return ApiResponse::success(new OngAdminResource($ong), 'Status da ONG atualizado com sucesso.');
    }

    public function document(Request $request, Ong $ong, DocumentoStorage $documents)
    {
        $actor = $request->attributes->get('web_account');
        abort_unless(($actor instanceof Ong && $actor->is($ong))
            || ($actor instanceof Pessoa && $actor->hasPermission('ongs.validar')), 403);

        $path = $ong->rg_responsavel_ong_link;
        $diskName = $ong->rg_responsavel_storage_disk;
        if (! $diskName || ! $documents->safePath($path) || $ong->rg_responsavel_excluir_em?->lte(now())) {
            return ApiResponse::error('Arquivo não está mais disponível.', status: 404);
        }

        try {
            $disk = Storage::disk($documents->confirmedDisk($diskName));
            $response = $disk->exists($path) ? $disk->download(
                $path,
                'identidade-responsavel.'.pathinfo($path, PATHINFO_EXTENSION),
                [
                    'Content-Type' => $ong->rg_responsavel_mime_type,
                    'Cache-Control' => 'private, no-store',
                    'X-Content-Type-Options' => 'nosniff',
                ],
            ) : null;
        } catch (Throwable) {
            return ApiResponse::error('Arquivo temporariamente indisponível.', status: 503);
        }

        if ($response === null) {
            return ApiResponse::error('Arquivo não está mais disponível.', status: 404);
        }

        if ($actor instanceof Pessoa) {
            AdminLog::create([
                'pessoa_id' => $actor->getKey(),
                'acao' => 'ong.documento_identidade.download',
                'recurso_type' => Ong::class,
                'recurso_id' => $ong->getKey(),
            ]);
        }

        return $response;
    }
}
