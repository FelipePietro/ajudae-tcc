<?php

namespace App\Services;

use App\Exceptions\ValidationConflictException;
use App\Models\AdminLog;
use App\Models\Evento;
use App\Models\Pessoa;
use App\Support\AccountAccess;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

final class EventoModerationService
{
    public const AGUARDANDO = 'aguardando a confirmação';

    public static function regras(bool $exigirMotivo): array
    {
        return [
            'status_evento' => ['required', Rule::in(['ativo', 'reprovado'])],
            'motivo' => [$exigirMotivo ? 'required_if:status_evento,reprovado' : 'sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }

    public function decidir(Pessoa $admin, int $eventoId, string $status, ?string $motivo): Evento
    {
        return DB::transaction(function () use ($admin, $eventoId, $status, $motivo) {
            $atual = Pessoa::whereKey($admin->getKey())->lockForUpdate()->firstOrFail();
            abort_unless((int) $atual->auth_version === (int) $admin->auth_version && AccountAccess::canOperate($atual)
                && $atual->hasPermission('eventos.aprovar'), 403);

            $evento = Evento::whereKey($eventoId)->lockForUpdate()->firstOrFail();
            if ($evento->status_evento !== self::AGUARDANDO) {
                throw new ValidationConflictException('O evento não está aguardando aprovação.');
            }

            $evento->forceFill(['status_evento' => $status])->save();
            AdminLog::create([
                'pessoa_id' => $atual->getKey(),
                'acao' => $status === 'ativo' ? 'evento.aprovado' : 'evento.reprovado',
                'recurso_type' => Evento::class,
                'recurso_id' => $evento->getKey(),
                'detalhes' => [
                    'de' => self::AGUARDANDO,
                    'para' => $status,
                    ...($motivo !== null ? ['motivo' => $motivo] : []),
                ],
            ]);

            $responsavel = $evento->responsavelPessoa ?? $evento->ong ?? $evento->pessoa;
            $responsavel?->notificacoes()->create([
                'tipo_notificacao' => 'evento.status_atualizado',
                'mensagem_notificacao' => "O status do evento {$evento->nm_evento} foi atualizado.",
                'evento_id' => $evento->getKey(),
            ]);

            return $evento;
        }, 3);
    }
}
