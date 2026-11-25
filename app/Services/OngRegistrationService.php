<?php

namespace App\Services;

use App\Models\AdminLog;
use App\Models\Ong;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Throwable;

final class OngRegistrationService
{
    public function __construct(private readonly DocumentoStorage $documents) {}

    public function register(array $validated, ?string $ip, ?string $userAgent, bool $issueToken = false): array
    {
        $file = $validated['documento_identidade'];
        unset($validated['aceitou_termos'], $validated['documento_identidade']);
        $validated['senha_ong'] = Hash::make($validated['senha_ong']);
        $validated['site_url_ong'] ??= '';
        $validated['pfp_ong_link'] ??= '';

        try {
            $stored = $this->documents->store($file);
        } catch (Throwable) {
            throw new ServiceUnavailableHttpException(null, 'Não foi possível armazenar o documento privado.');
        }

        try {
            return DB::transaction(function () use ($validated, $stored, $ip, $userAgent, $issueToken) {
                $ong = new Ong($validated);
                $ong->forceFill([
                    'status_ong' => 'aguardando aprovação',
                    'rg_responsavel_ong_link' => $stored['path'],
                    'rg_responsavel_storage_disk' => $stored['disk'],
                    'rg_responsavel_mime_type' => $stored['mime_type'],
                    'rg_responsavel_size_bytes' => $stored['size_bytes'],
                    'rg_responsavel_sha256' => $stored['sha256'],
                ])->save();

                $ong->assinaturas()->create([
                    'tipo_termo' => 'termos_de_uso',
                    'versao_termo' => '1.0',
                    'aceito_em' => now(),
                    'ip_assinatura' => $ip,
                    'user_agent_assinatura' => mb_substr((string) $userAgent, 0, 255),
                    'documento_hash' => hash('sha256', 'termos_de_uso:1.0'),
                ]);

                AdminLog::create([
                    'acao' => 'ong.cadastrada',
                    'recurso_type' => Ong::class,
                    'recurso_id' => $ong->getKey(),
                    'detalhes' => ['status' => $ong->status_ong, 'documento' => 'documento_identidade'],
                ]);

                return [$ong, $issueToken ? $ong->createToken('ong-token', ['ong'])->plainTextToken : null];
            }, 3);
        } catch (Throwable $exception) {
            try {
                if (! Storage::disk($stored['disk'])->delete($stored['path'])) {
                    Log::warning('Falha na compensação do upload institucional.');
                }
            } catch (Throwable) {
                Log::warning('Falha na compensação do upload institucional.');
            }

            if ($exception instanceof UniqueConstraintViolationException) {
                throw ValidationException::withMessages(['login_ong' => 'Confira a disponibilidade do login, CNPJ e e-mail institucional.']);
            }

            Log::error('Falha na persistência do cadastro institucional.', ['exception_type' => get_class($exception)]);

            throw new HttpException(500, 'Não foi possível concluir o cadastro institucional.');
        }
    }
}
