<?php

namespace App\Services;

use App\Models\AdminLog;
use App\Models\Ong;
use App\Models\Pessoa;
use App\Support\AccountAccess;
use Illuminate\Support\Facades\DB;

final class OngValidationService
{
    public const STATUS_RULES = ['status_ong' => ['required', 'in:ativo,aguardando aprovação,reprovado']];

    public function update(Ong $ong, Pessoa $admin, string $status): Ong
    {
        abort_unless($admin->hasPermission('ongs.validar'), 403);

        return DB::transaction(function () use ($ong, $admin, $status) {
            $currentAdmin = Pessoa::whereKey($admin->getKey())->lockForUpdate()->firstOrFail();
            abort_unless(AccountAccess::canOperate($currentAdmin)
                && $currentAdmin->auth_version === $admin->auth_version
                && $currentAdmin->hasPermission('ongs.validar'), 403);
            $ong = Ong::whereKey($ong->getKey())->lockForUpdate()->firstOrFail();
            $previous = $ong->status_ong;
            $ong->forceFill(['status_ong' => $status]);
            if ($status !== 'aguardando aprovação' && $ong->rg_responsavel_storage_disk !== null
                && $ong->rg_responsavel_excluir_em === null) {
                $ong->forceFill(['rg_responsavel_excluir_em' => now()->addDays(30)]);
            }
            $ong->save();

            AdminLog::create([
                'pessoa_id' => $admin->getKey(),
                'acao' => 'ong.status_atualizado',
                'recurso_type' => Ong::class,
                'recurso_id' => $ong->getKey(),
                'detalhes' => ['de' => $previous, 'para' => $status],
            ]);

            return $ong;
        }, 3);
    }
}
