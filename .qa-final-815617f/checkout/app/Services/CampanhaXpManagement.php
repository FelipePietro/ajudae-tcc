<?php

namespace App\Services;

use App\Models\AdminLog;
use App\Models\CampanhaXp;
use App\Models\Pessoa;
use App\Support\AccountAccess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class CampanhaXpManagement
{
    public function save(Pessoa $admin, array $fields, ?CampanhaXp $campanha = null, bool $statusOnly = false): CampanhaXp
    {
        return DB::transaction(function () use ($admin, $fields, $campanha, $statusOnly) {
            $currentAdmin = Pessoa::whereKey($admin->getKey())->lockForUpdate()->firstOrFail();
            abort_unless(AccountAccess::canOperate($currentAdmin)
                && $currentAdmin->auth_version === $admin->auth_version
                && $currentAdmin->hasPermission('xp.campanhas.gerenciar'), 403);

            $campaign = $campanha === null
                ? new CampanhaXp
                : CampanhaXp::whereKey($campanha->getKey())->lockForUpdate()->firstOrFail();
            $before = $campaign->exists ? $campaign->only($campaign->getFillable()) : null;
            $data = $statusOnly ? $fields : Validator::make(array_merge($before ?? [], $fields), [
                'nome' => ['required', 'string', 'max:120'],
                'multiplicador' => ['required', 'numeric', 'gt:0'],
                'inicio' => ['required', 'date'],
                'fim' => ['required', 'date', 'after_or_equal:inicio'],
                'ativa' => ['required', 'boolean'],
                'cat_evento_id' => ['required', 'integer', 'exists:cat_evento,cat_evento_id'],
            ])->validate();

            $campaign->fill($data);
            if ($campaign->isDirty()) {
                $campaign->save();
                AdminLog::create([
                    'pessoa_id' => $admin->getKey(),
                    'acao' => 'xp.campanha_atualizada',
                    'recurso_type' => CampanhaXp::class,
                    'recurso_id' => $campaign->getKey(),
                    'detalhes' => ['de' => $before, 'para' => $campaign->only($campaign->getFillable())],
                ]);
            }

            return $campaign->refresh();
        }, 3);
    }
}
