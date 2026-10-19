<?php

namespace App\Services;

use App\Exceptions\ValidationConflictException;
use App\Models\AdminLog;
use App\Models\DocumentoPessoa;
use App\Models\Pessoa;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PessoaValidationService
{
    public function assumir(Pessoa $pessoa, Pessoa $admin): Pessoa
    {
        return DB::transaction(function () use ($pessoa, $admin) {
            $pessoa = Pessoa::whereKey($pessoa->getKey())->lockForUpdate()->firstOrFail();

            $this->garantirVoluntario($pessoa);

            $lockAtivo = $pessoa->admin_analisando_id !== null
                && $pessoa->analise_assumida_em?->greaterThan(now()->subHours(24));

            if ($pessoa->status_validacao === 'em_analise') {
                if ($lockAtivo && $pessoa->admin_analisando_id === $admin->pessoa_id) {
                    return $pessoa;
                }

                if ($lockAtivo) {
                    throw new ValidationConflictException('Esta análise já está sendo conduzida por outro administrador.');
                }
            } elseif ($pessoa->status_validacao !== 'aguardando_validacao') {
                throw new ValidationConflictException('Esta validação não pode ser assumida no estado atual.');
            }

            $anterior = $pessoa->status_validacao;
            $pessoa->forceFill([
                'status_validacao' => 'em_analise',
                'admin_analisando_id' => $admin->pessoa_id,
                'analise_assumida_em' => now(),
            ])->save();

            $this->registrarHistorico($pessoa, $admin, $anterior, 'em_analise', null, null, now(), null);

            return $pessoa->refresh();
        });
    }

    public function solicitarCorrecao(Pessoa $pessoa, Pessoa $admin, string $motivo): Pessoa
    {
        return $this->concluirAnalise($pessoa, $admin, 'correcao_solicitada', $motivo);
    }

    public function reprovar(Pessoa $pessoa, Pessoa $admin, string $motivo): Pessoa
    {
        return DB::transaction(function () use ($pessoa, $admin, $motivo) {
            $pessoa = Pessoa::whereKey($pessoa->getKey())->lockForUpdate()->firstOrFail();
            $this->garantirConclusaoPermitida($pessoa, $admin);
            $anterior = $pessoa->status_validacao;
            $tentativa = $this->contarReprovacoesDoCiclo($pessoa) + 1;

            $pessoa->forceFill([
                'status_validacao' => 'reprovado',
                'admin_analisando_id' => null,
                'analise_assumida_em' => null,
                'bloqueado_ate' => $tentativa >= 3 ? now()->addDays(7) : null,
            ])->save();

            $this->registrarHistorico($pessoa, $admin, $anterior, 'reprovado', $motivo, $tentativa, null, now());
            $this->registrarAdminLog($pessoa, $admin, 'pessoa.validacao_reprovada', $motivo);

            return $pessoa->refresh();
        });
    }

    public function aprovar(Pessoa $pessoa, Pessoa $admin): Pessoa
    {
        return DB::transaction(function () use ($pessoa, $admin) {
            $pessoa = Pessoa::whereKey($pessoa->getKey())->lockForUpdate()->firstOrFail();
            $this->garantirConclusaoPermitida($pessoa, $admin);
            $documentos = $pessoa->documentos()
                ->where('ativo', true)
                ->whereIn('tipo', ['documento_identidade', 'antecedentes_criminais'])
                ->get()
                ->keyBy('tipo');

            foreach (['documento_identidade', 'antecedentes_criminais'] as $tipo) {
                if (($documentos[$tipo]->status ?? null) !== 'aprovado') {
                    throw new ValidationConflictException('Os documentos obrigatórios precisam estar aprovados.');
                }
            }

            $anterior = $pessoa->status_validacao;
            $pessoa->forceFill([
                'status_validacao' => 'aprovado',
                'aprovado_em' => now(),
                'bloqueado_ate' => null,
                'admin_analisando_id' => null,
                'analise_assumida_em' => null,
            ])->save();

            $this->registrarHistorico($pessoa, $admin, $anterior, 'aprovado', null, null, null, now());
            $this->registrarAdminLog($pessoa, $admin, 'pessoa.validacao_aprovada');

            return $pessoa->refresh();
        });
    }

    public function reprovarDefinitivamente(Pessoa $pessoa, Pessoa $admin, string $motivo): Pessoa
    {
        if (! $admin->admin_master || $admin->role_pessoa !== 'administrador') {
            throw new AuthorizationException('Apenas o administrador Master pode executar esta ação.');
        }

        return DB::transaction(function () use ($pessoa, $admin, $motivo) {
            $pessoa = Pessoa::whereKey($pessoa->getKey())->lockForUpdate()->firstOrFail();
            $this->garantirConclusaoPermitida($pessoa, $admin);
            $anterior = $pessoa->status_validacao;
            $pessoa->forceFill([
                'status_validacao' => 'reprovado_definitivamente',
                'reprovado_definitivamente_em' => now(),
                'bloqueado_ate' => null,
                'admin_analisando_id' => null,
                'analise_assumida_em' => null,
            ])->save();

            $this->registrarHistorico($pessoa, $admin, $anterior, 'reprovado_definitivamente', $motivo, null, null, now());
            $this->registrarAdminLog($pessoa, $admin, 'pessoa.validacao_reprovada_definitivamente', $motivo);

            return $pessoa->refresh();
        });
    }

    public function reenviar(Pessoa $pessoa): Pessoa
    {
        return DB::transaction(function () use ($pessoa) {
            $pessoa = Pessoa::whereKey($pessoa->getKey())->lockForUpdate()->firstOrFail();

            $this->garantirVoluntario($pessoa);

            if ($pessoa->status_validacao === 'reprovado_definitivamente') {
                throw new ValidationConflictException('Esta validação foi reprovada definitivamente.');
            }

            if ($pessoa->bloqueado_ate?->isFuture()) {
                throw new ValidationConflictException('Uma nova submissão estará disponível após o fim do bloqueio.');
            }

            if (! in_array($pessoa->status_validacao, ['correcao_solicitada', 'reprovado'], true)) {
                throw new ValidationConflictException('A validação não pode ser reenviada no estado atual.');
            }

            $anterior = $pessoa->status_validacao;
            $pessoa->forceFill([
                'status_validacao' => 'aguardando_validacao',
                'bloqueado_ate' => null,
                'admin_analisando_id' => null,
                'analise_assumida_em' => null,
            ])->save();

            $this->registrarHistorico($pessoa, null, $anterior, 'aguardando_validacao');

            return $pessoa->refresh();
        });
    }

    public function validarDocumento(DocumentoPessoa $documento, Pessoa $admin, string $status, ?string $motivo = null): DocumentoPessoa
    {
        return DB::transaction(function () use ($documento, $admin, $status, $motivo) {
            $pessoa = Pessoa::whereKey($documento->pessoa_id)->lockForUpdate()->firstOrFail();
            $documento = DocumentoPessoa::whereKey($documento->getKey())->lockForUpdate()->firstOrFail();

            if (! $documento->ativo) {
                throw new ValidationConflictException('Documento histórico ou substituído não pode ser validado.');
            }

            $this->garantirVoluntario($pessoa);

            if ($documento->tipo === 'antecedentes_criminais' && $documento->status !== 'pendente') {
                throw new ValidationConflictException('Esta versão de antecedentes já possui decisão administrativa.');
            }

            if ($pessoa->status_validacao !== 'aprovado') {
                $this->garantirConclusaoPermitida($pessoa, $admin);
            }

            $agora = now();

            $documento->update([
                'status' => $status,
                'validated_at' => $agora,
                'validated_by_admin_id' => $admin->pessoa_id,
                'rejection_reason' => $status === 'rejeitado' ? $motivo : null,
                'expires_at' => $status === 'aprovado' && $documento->tipo === 'antecedentes_criminais'
                    ? $agora->copy()->addMonths(12)
                    : $documento->expires_at,
                'delete_after' => $agora->copy()->addDays(30),
            ]);

            return $documento->refresh();
        });
    }

    public function contarReprovacoesDoCiclo(Pessoa $pessoa): int
    {
        $desde = now()->subDays(30);
        $ultimaAprovacao = $pessoa->historicoValidacao()
            ->where('status_novo', 'aprovado')
            ->max('created_at');

        if ($ultimaAprovacao && Carbon::parse($ultimaAprovacao)->greaterThan($desde)) {
            $desde = Carbon::parse($ultimaAprovacao);
        }

        return $pessoa->historicoValidacao()
            ->where('status_novo', 'reprovado')
            ->where('created_at', '>=', $desde)
            ->count();
    }

    private function concluirAnalise(Pessoa $pessoa, Pessoa $admin, string $status, string $motivo): Pessoa
    {
        return DB::transaction(function () use ($pessoa, $admin, $status, $motivo) {
            $pessoa = Pessoa::whereKey($pessoa->getKey())->lockForUpdate()->firstOrFail();
            $this->garantirConclusaoPermitida($pessoa, $admin);
            $anterior = $pessoa->status_validacao;
            $pessoa->forceFill([
                'status_validacao' => $status,
                'admin_analisando_id' => null,
                'analise_assumida_em' => null,
            ])->save();

            $this->registrarHistorico($pessoa, $admin, $anterior, $status, $motivo, null, null, now());
            $this->registrarAdminLog($pessoa, $admin, 'pessoa.validacao_'.$status, $motivo);

            return $pessoa->refresh();
        });
    }

    private function garantirConclusaoPermitida(Pessoa $pessoa, Pessoa $admin): void
    {
        $this->garantirVoluntario($pessoa);

        if ($pessoa->status_validacao !== 'em_analise') {
            throw new ValidationConflictException('A validação precisa estar em análise antes de ser concluída.');
        }

        $lockAtivo = $pessoa->admin_analisando_id !== null
            && $pessoa->analise_assumida_em?->greaterThan(now()->subHours(24));

        if (! $lockAtivo || $pessoa->admin_analisando_id !== $admin->pessoa_id) {
            throw new ValidationConflictException('A análise deve ser assumida por este administrador antes da conclusão.');
        }
    }

    private function garantirVoluntario(Pessoa $pessoa): void
    {
        if ($pessoa->role_pessoa !== 'voluntario') {
            throw new ValidationConflictException('Somente pessoas voluntárias participam deste workflow de validação.');
        }
    }

    private function registrarHistorico(
        Pessoa $pessoa,
        ?Pessoa $admin,
        ?string $anterior,
        string $novo,
        ?string $motivo = null,
        ?int $tentativa = null,
        mixed $iniciadaEm = null,
        mixed $concluidaEm = null
    ): void {
        $pessoa->historicoValidacao()->create([
            'admin_id' => $admin?->pessoa_id,
            'status_anterior' => $anterior,
            'status_novo' => $novo,
            'motivo' => $motivo,
            'numero_tentativa' => $tentativa,
            'iniciada_em' => $iniciadaEm,
            'concluida_em' => $concluidaEm,
        ]);
    }

    private function registrarAdminLog(Pessoa $pessoa, Pessoa $admin, string $acao, ?string $motivo = null): void
    {
        AdminLog::create([
            'pessoa_id' => $admin->pessoa_id,
            'acao' => $acao,
            'recurso_type' => Pessoa::class,
            'recurso_id' => $pessoa->pessoa_id,
            'detalhes' => $motivo ? ['motivo' => $motivo] : null,
        ]);
    }
}
