<?php

namespace App\Services;

use App\Exceptions\ValidationConflictException;
use App\Models\Acompanhamento;
use App\Models\AdminLog;
use App\Models\Evento;
use App\Models\Ong;
use App\Models\Pessoa;
use App\Models\SolicitacaoOrganizador;
use App\Models\VinculoOngOrganizador;
use App\Support\AccountAccess;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class OrganizadorService
{
    public const PARTICIPACOES_MINIMAS = 5;

    public const COOLDOWN_DIAS = 30;

    public function __construct(
        private readonly ConfiabilidadeService $confiabilidade,
        private readonly AvaliacaoService $avaliacoes,
    ) {}

    public function solicitar(Pessoa $pessoa, string $justificativa): SolicitacaoOrganizador
    {
        return DB::transaction(function () use ($pessoa, $justificativa) {
            $pessoa = Pessoa::whereKey($pessoa->getKey())->lockForUpdate()->firstOrFail();
            abort_unless(AccountAccess::canOperate($pessoa), 403);
            $this->garantirElegivel($pessoa);

            return SolicitacaoOrganizador::create([
                'pessoa_id' => $pessoa->pessoa_id,
                'status' => 'pendente',
                'justificativa' => trim($justificativa),
                'solicitada_em' => now(),
            ]);
        });
    }

    public function iniciarAnalise(SolicitacaoOrganizador $solicitacao, Pessoa $admin): SolicitacaoOrganizador
    {
        return DB::transaction(function () use ($solicitacao, $admin) {
            $solicitacao = SolicitacaoOrganizador::whereKey($solicitacao->getKey())->lockForUpdate()->firstOrFail();

            if ($solicitacao->status !== 'pendente'
                && ! ($solicitacao->status === 'em_analise' && ! $this->lockValido($solicitacao))) {
                throw new ValidationConflictException('A solicitação não pode ser assumida no estado atual.');
            }

            $solicitacao->update([
                'status' => 'em_analise',
                'admin_id' => $admin->pessoa_id,
                'analise_iniciada_em' => now(),
            ]);

            return $solicitacao->refresh();
        });
    }

    public function aprovar(SolicitacaoOrganizador $solicitacao, Pessoa $admin): SolicitacaoOrganizador
    {
        return DB::transaction(function () use ($solicitacao, $admin) {
            $solicitacao = SolicitacaoOrganizador::whereKey($solicitacao->getKey())->lockForUpdate()->firstOrFail();

            $this->garantirDonoDoLock($solicitacao, $admin);

            $pessoa = Pessoa::whereKey($solicitacao->pessoa_id)->lockForUpdate()->firstOrFail();
            $this->garantirElegivel($pessoa, $solicitacao->solicitacao_organizador_id);

            $pessoa->forceFill(['role_pessoa' => 'organizador'])->save();
            $solicitacao->update([
                'status' => 'aprovada',
                'motivo_decisao' => null,
                'decidida_em' => now(),
            ]);
            $this->registrarDecisaoPromocao($solicitacao, $admin, $pessoa);

            return $solicitacao->refresh();
        });
    }

    public function reprovar(SolicitacaoOrganizador $solicitacao, Pessoa $admin, string $motivo): SolicitacaoOrganizador
    {
        return DB::transaction(function () use ($solicitacao, $admin, $motivo) {
            $solicitacao = SolicitacaoOrganizador::whereKey($solicitacao->getKey())->lockForUpdate()->firstOrFail();

            $this->garantirDonoDoLock($solicitacao, $admin);

            $solicitacao->update([
                'status' => 'reprovada',
                'motivo_decisao' => trim($motivo),
                'decidida_em' => now(),
            ]);
            $this->registrarDecisaoPromocao($solicitacao, $admin, $solicitacao->pessoa);

            return $solicitacao->refresh();
        });
    }

    private function garantirDonoDoLock(SolicitacaoOrganizador $solicitacao, Pessoa $admin): void
    {
        if ($solicitacao->status !== 'em_analise'
            || $solicitacao->admin_id === null
            || (int) $solicitacao->admin_id !== (int) $admin->pessoa_id
            || ! $this->lockValido($solicitacao)) {
            throw new ValidationConflictException('A análise deve ser concluída pelo Admin que possui o lock válido.');
        }
    }

    private function registrarDecisaoPromocao(SolicitacaoOrganizador $solicitacao, Pessoa $admin, Pessoa $pessoa): void
    {
        AdminLog::create([
            'pessoa_id' => $admin->getKey(),
            'acao' => 'organizador.promocao_decidida',
            'recurso_type' => $solicitacao->getMorphClass(),
            'recurso_id' => $solicitacao->getKey(),
            'detalhes' => ['de' => 'em_analise', 'para' => $solicitacao->status],
        ]);
        $pessoa->notificacoes()->create([
            'tipo_notificacao' => 'organizador.promocao_decidida',
            'mensagem_notificacao' => 'A análise do seu pedido para atuar como organizador foi concluída.',
        ]);
    }

    private function lockValido(SolicitacaoOrganizador $solicitacao): bool
    {
        return $solicitacao->analise_iniciada_em !== null
            && now()->lessThan($solicitacao->analise_iniciada_em->copy()->addHours(24));
    }

    public function garantirElegivel(Pessoa $pessoa, ?int $ignorarSolicitacao = null): void
    {
        if ($pessoa->role_pessoa !== 'voluntario' || $pessoa->status_validacao !== 'aprovado') {
            throw new AuthorizationException('Somente voluntários aprovados podem solicitar promoção.');
        }

        $resumo = $this->confiabilidade->resumo($pessoa);
        $participacoes = $resumo['participacoes_confirmadas'];

        if ($participacoes < self::PARTICIPACOES_MINIMAS) {
            throw new ValidationConflictException('São necessárias ao menos 5 participações confirmadas.');
        }

        if (! $resumo['formada'] || $resumo['score'] < 80) {
            throw new ValidationConflictException('A confiabilidade precisa ser de ao menos 80.');
        }

        $media = $this->avaliacoes->mediaVoluntario($pessoa);
        if ($media === null || $media < 4.0) {
            throw new ValidationConflictException('A média de avaliações precisa ser de ao menos 4,0.');
        }

        if ($pessoa->bloqueado_ate?->isFuture()) {
            throw new ValidationConflictException('A pessoa possui bloqueio ativo.');
        }

        if (Acompanhamento::where('padrinho_id', $pessoa->pessoa_id)->where('status', 'ativo')->exists()) {
            throw new ValidationConflictException('A pessoa possui apadrinhado ativo.');
        }

        $naoConcluida = SolicitacaoOrganizador::where('pessoa_id', $pessoa->pessoa_id)
            ->whereIn('status', ['pendente', 'em_analise'])
            ->when($ignorarSolicitacao, fn ($query) => $query->whereKeyNot($ignorarSolicitacao))
            ->exists();

        if ($naoConcluida) {
            throw new ValidationConflictException('Já existe uma solicitação não concluída.');
        }

        $ultimaReprovacao = SolicitacaoOrganizador::where('pessoa_id', $pessoa->pessoa_id)
            ->where('status', 'reprovada')
            ->whereNotNull('decidida_em')
            ->latest('decidida_em')
            ->first();

        if ($ultimaReprovacao?->decidida_em?->greaterThan(now()->subDays(self::COOLDOWN_DIAS))) {
            throw new ValidationConflictException('Nova solicitação disponível após 30 dias completos da reprovação.');
        }
    }

    public function convidar(Ong $ong, Pessoa $pessoa): VinculoOngOrganizador
    {
        return DB::transaction(function () use ($ong, $pessoa) {
            $pessoa = Pessoa::whereKey($pessoa->getKey())->lockForUpdate()->firstOrFail();
            $versao = $ong->auth_version;
            $ong = Ong::whereKey($ong->getKey())->lockForUpdate()->firstOrFail();

            if (! AccountAccess::activeOng($ong) || (int) $ong->auth_version !== (int) $versao) {
                throw new AuthorizationException('A ONG precisa estar ativa.');
            }

            if (! AccountAccess::canOperate($pessoa) || $pessoa->role_pessoa !== 'organizador' || $pessoa->status_validacao !== 'aprovado') {
                throw new ValidationConflictException('Somente organizadores aprovados podem ser convidados.');
            }

            if (VinculoOngOrganizador::where('pessoa_id', $pessoa->pessoa_id)->where('status', 'ativo')->exists()) {
                throw new ValidationConflictException('O organizador já possui vínculo ativo com uma ONG.');
            }

            if (VinculoOngOrganizador::where('ong_id', $ong->ong_id)
                ->where('pessoa_id', $pessoa->pessoa_id)
                ->where('status', 'convite_pendente')
                ->exists()) {
                throw new ValidationConflictException('Já existe convite pendente para este organizador.');
            }

            $vinculo = VinculoOngOrganizador::create([
                'ong_id' => $ong->ong_id,
                'pessoa_id' => $pessoa->pessoa_id,
                'status' => 'convite_pendente',
                'convidado_em' => now(),
            ]);
            $this->registrarVinculo($vinculo, $ong, null);
            $pessoa->notificacoes()->create([
                'tipo_notificacao' => 'organizador.convite_recebido',
                'mensagem_notificacao' => "Você recebeu um convite de vínculo de {$ong->nome_fantasia}.",
            ]);

            return $vinculo;
        }, 3);
    }

    public function convidarPorIdentificador(Ong $ong, string $identificador): void
    {
        abort_unless(AccountAccess::activeOng($ong), 403);
        $campo = filter_var($identificador, FILTER_VALIDATE_EMAIL) ? 'email_pessoa' : 'login_pessoa';
        $pessoa = Pessoa::where($campo, $identificador)
            ->where('role_pessoa', 'organizador')
            ->where('status_validacao', 'aprovado')
            ->where('exclusao_pendente', false)
            ->first();
        if ($pessoa === null) {
            return;
        }

        try {
            $this->convidar($ong, $pessoa);
        } catch (ValidationConflictException) {
            // The sender receives the same response when an invitation is unavailable.
        }
    }

    public function responderConvite(VinculoOngOrganizador $vinculo, Pessoa $pessoa, bool $aceitar): VinculoOngOrganizador
    {
        return DB::transaction(function () use ($vinculo, $pessoa, $aceitar) {
            $versao = $pessoa->auth_version;
            $pessoa = Pessoa::whereKey($pessoa->getKey())->lockForUpdate()->firstOrFail();
            $vinculo = VinculoOngOrganizador::whereKey($vinculo->getKey())->lockForUpdate()->firstOrFail();

            if ($vinculo->pessoa_id !== $pessoa->pessoa_id
                || ! AccountAccess::canOperate($pessoa) || $pessoa->role_pessoa !== 'organizador'
                || $pessoa->status_validacao !== 'aprovado' || (int) $pessoa->auth_version !== (int) $versao) {
                throw new AuthorizationException('Este convite pertence a outra pessoa.');
            }

            if ($vinculo->status !== 'convite_pendente') {
                throw new ValidationConflictException('Este convite já foi respondido.');
            }

            if ($aceitar) {
                $ong = Ong::whereKey($vinculo->ong_id)->lockForUpdate()->firstOrFail();
                if (! AccountAccess::activeOng($ong)) {
                    throw new ValidationConflictException('O convite não pode ser aceito no estado atual.');
                }

                if (VinculoOngOrganizador::where('pessoa_id', $pessoa->pessoa_id)
                    ->where('status', 'ativo')
                    ->whereKeyNot($vinculo->getKey())
                    ->exists()) {
                    throw new ValidationConflictException('O organizador já possui vínculo ativo com uma ONG.');
                }

                if (Evento::where('pessoa_id', $pessoa->pessoa_id)
                    ->whereNull('ong_id')
                    ->whereNotIn('status_evento', ['finalizado', 'cancelado', 'reprovado'])
                    ->exists()) {
                    throw new ValidationConflictException('O organizador possui evento independente não terminal.');
                }
            }

            $vinculo->update([
                'status' => $aceitar ? 'ativo' : 'recusado',
                'respondido_em' => now(),
            ]);
            $this->registrarVinculo($vinculo, $pessoa, 'convite_pendente');
            $vinculo->ong->notificacoes()->create([
                'tipo_notificacao' => 'organizador.convite_respondido',
                'mensagem_notificacao' => $aceitar ? 'Um convite de vínculo foi aceito.' : 'Um convite de vínculo foi recusado.',
            ]);

            return $vinculo->refresh();
        }, 3);
    }

    public function encerrarVinculo(VinculoOngOrganizador $vinculo, Pessoa|Ong $ator): VinculoOngOrganizador
    {
        return DB::transaction(function () use ($vinculo, $ator) {
            $pessoa = Pessoa::whereKey($vinculo->pessoa_id)->lockForUpdate()->firstOrFail();
            $ong = Ong::whereKey($vinculo->ong_id)->lockForUpdate()->firstOrFail();
            $vinculo = VinculoOngOrganizador::whereKey($vinculo->getKey())->lockForUpdate()->firstOrFail();

            $autorizado = $ator instanceof Ong
                ? $vinculo->ong_id === $ator->ong_id
                : $vinculo->pessoa_id === $ator->pessoa_id;

            if (! $autorizado) {
                throw new AuthorizationException('Este vínculo não pertence ao usuário autenticado.');
            }

            $current = $ator instanceof Ong ? $ong : $pessoa;
            abort_unless(AccountAccess::activeOng($ong) && AccountAccess::canOperate($current)
                && (int) $current->auth_version === (int) $ator->auth_version, 403);

            if ($vinculo->status !== 'ativo') {
                throw new ValidationConflictException('O vínculo não está ativo.');
            }

            $possuiEventoNaoTerminal = Evento::where('ong_id', $vinculo->ong_id)
                ->where('responsavel_pessoa_id', $vinculo->pessoa_id)
                ->whereNotIn('status_evento', ['finalizado', 'cancelado', 'reprovado'])
                ->exists();

            if ($possuiEventoNaoTerminal) {
                throw new ValidationConflictException('O organizador ainda é responsável por evento não terminal.');
            }

            $vinculo->update([
                'status' => 'encerrado',
                'encerrado_em' => now(),
            ]);
            $this->registrarVinculo($vinculo, $ator, 'ativo');
            $destinatario = $ator instanceof Ong ? $pessoa : $ong;
            $destinatario->notificacoes()->create([
                'tipo_notificacao' => 'organizador.vinculo_encerrado',
                'mensagem_notificacao' => 'Um vínculo entre organizador e ONG foi encerrado.',
            ]);

            return $vinculo->refresh();
        }, 3);
    }

    private function registrarVinculo(VinculoOngOrganizador $vinculo, Pessoa|Ong $ator, ?string $anterior): void
    {
        AdminLog::create([
            'pessoa_id' => $ator instanceof Pessoa ? $ator->getKey() : null,
            'acao' => match ($vinculo->status) {
                'convite_pendente' => 'organizador.convite_enviado',
                'encerrado' => 'organizador.vinculo_encerrado',
                default => 'organizador.convite_respondido',
            },
            'recurso_type' => $vinculo->getMorphClass(),
            'recurso_id' => $vinculo->getKey(),
            'detalhes' => ['ong_id' => $vinculo->ong_id, 'de' => $anterior, 'para' => $vinculo->status],
        ]);
    }

    public function atribuirResponsavel(Evento $evento, Ong $ong, ?Pessoa $pessoa, EventoOperationalService $operations): Evento
    {
        return DB::transaction(function () use ($evento, $ong, $pessoa, $operations) {
            $evento = Evento::whereKey($evento->getKey())->lockForUpdate()->firstOrFail();

            $currentOng = Ong::whereKey($ong->getKey())->lockForUpdate()->firstOrFail();
            if (! AccountAccess::activeOng($currentOng) || (int) $currentOng->auth_version !== (int) $ong->auth_version
                || $evento->ong_id !== $currentOng->ong_id) {
                throw new AuthorizationException('A ONG não é proprietária deste evento.');
            }

            if (in_array($evento->status_evento, ['cancelado', 'finalizado'], true)) {
                throw new ValidationConflictException('Evento terminal não pode alterar o responsável.');
            }

            if ($evento->agendas()->where('data_inicio', '<=', now())->exists()) {
                throw new ValidationConflictException('O responsável não pode ser alterado após o início do evento.');
            }

            if ($pessoa) {
                $pessoa = Pessoa::whereKey($pessoa->getKey())->lockForUpdate()->firstOrFail();
                $vinculoAtivo = VinculoOngOrganizador::where('ong_id', $ong->ong_id)
                    ->where('pessoa_id', $pessoa->pessoa_id)
                    ->where('status', 'ativo')
                    ->lockForUpdate()
                    ->first();

                if (! AccountAccess::canOperate($pessoa) || $pessoa->role_pessoa !== 'organizador' || $pessoa->status_validacao !== 'aprovado' || ! $vinculoAtivo) {
                    throw new ValidationConflictException('O responsável deve ser organizador aprovado e vinculado à ONG.');
                }
            }

            if ($evento->responsavel_pessoa_id !== $pessoa?->pessoa_id) {
                $previous = $evento->responsavel_pessoa_id;
                $evento->forceFill(['responsavel_pessoa_id' => $pessoa?->pessoa_id])->save();
                AdminLog::create([
                    'acao' => 'evento.responsavel_atualizado',
                    'recurso_type' => $evento->getMorphClass(),
                    'recurso_id' => $evento->getKey(),
                    'detalhes' => ['ong_id' => $ong->getKey(), 'de' => $previous, 'para' => $pessoa?->getKey()],
                ]);
                $operations->exigirReaprovacao($evento);
                $operations->notificarEvento($evento, 'evento.alterado', "O responsável do evento {$evento->nm_evento} foi alterado.");
            }

            return $evento->refresh();
        }, 3);
    }
}
