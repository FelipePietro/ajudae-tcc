<?php

namespace App\Services;

use App\Exceptions\ValidationConflictException;
use App\Models\Acompanhamento;
use App\Models\CodigoIndicacaoPadrinho;
use App\Models\ConclusaoPadrinho;
use App\Models\Pessoa;
use App\Models\SolicitacaoPadrinho;
use App\Support\AccountAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PadrinhoService
{
    public function candidatos(bool $comVaga = true): Builder
    {
        $limite60 = now()->subDays(60);
        $limite90 = now()->subDays(90);

        return Pessoa::query()
            ->select(['pessoa_id', 'nm_pessoa', 'cidade_pessoa', 'uf_pessoa'])
            ->where('role_pessoa', 'voluntario')
            ->where('status_validacao', 'aprovado')
            ->where('exclusao_pendente', false)
            ->where('aprovado_em', '<=', $limite60)
            ->where(fn ($query) => $query->whereNull('bloqueado_ate')->orWhere('bloqueado_ate', '<=', now()))
            ->whereRaw("(SELECT COUNT(DISTINCT a.agenda_id) FROM p_ag_inscreve i
                JOIN agenda a ON a.agenda_id = i.agenda_id
                JOIN evento e ON e.evento_id = a.evento_id
                WHERE i.pessoa_id = pessoa.pessoa_id AND i.status_inscricao = 'participou'
                AND i.presenca = 1 AND i.presenca_confirmada_em IS NOT NULL
                AND a.status_ativo = 'finalizado' AND e.status_evento <> 'cancelado') >= 10")
            ->whereRaw('(SELECT AVG(av.nota) FROM avaliacao_voluntario av WHERE av.pessoa_id = pessoa.pessoa_id) >= 4.5')
            ->whereRaw("(SELECT COUNT(*) FROM p_ag_inscreve i
                JOIN agenda a ON a.agenda_id = i.agenda_id
                JOIN evento e ON e.evento_id = a.evento_id
                WHERE i.pessoa_id = pessoa.pessoa_id AND i.status_inscricao = 'não participou'
                AND i.presenca = 0 AND i.presenca_confirmada_em >= ?
                AND NOT EXISTS (SELECT 1 FROM justificativa_participacao j
                    WHERE j.inscricao_id = i.inscricao_id AND j.status = 'deferida')
                AND a.status_ativo <> 'cancelado' AND e.status_evento <> 'cancelado') = 0", [$limite90])
            ->whereRaw("(SELECT COUNT(*) FROM p_ag_inscreve i
                JOIN agenda a ON a.agenda_id = i.agenda_id
                JOIN evento e ON e.evento_id = a.evento_id
                WHERE i.pessoa_id = pessoa.pessoa_id AND i.status_inscricao = 'desistente'
                AND i.desistencia_tardia = 1 AND i.desistencia_sem_penalidade = 0
                AND i.desistiu_em >= ? AND a.status_ativo <> 'cancelado'
                AND e.status_evento <> 'cancelado') <= 1", [$limite90])
            ->when($comVaga, fn (Builder $query) => $query->whereRaw("(SELECT COUNT(*) FROM acompanhamento ac
                WHERE ac.padrinho_id = pessoa.pessoa_id AND ac.status = 'ativo') < 2"))
            ->whereNotExists(fn ($query) => $query->selectRaw('1')->from('acompanhamento as ac')
                ->whereColumn('ac.apadrinhado_id', 'pessoa.pessoa_id')->where('ac.status', 'ativo'));
    }

    public function codigo(Pessoa $padrinho): ?CodigoIndicacaoPadrinho
    {
        $this->garantirPadrinho($padrinho, false);

        return $this->codigoAtivo($padrinho)->first();
    }

    public function emitirCodigo(Pessoa $padrinho): CodigoIndicacaoPadrinho
    {
        return DB::transaction(function () use ($padrinho) {
            $padrinho = $this->bloquearPessoas([$padrinho->getKey()])[$padrinho->getKey()];
            $codigoAtivo = $this->codigo($padrinho);
            if ($codigoAtivo) {
                return $codigoAtivo;
            }

            $this->garantirPadrinho($padrinho);
            $codigo = bin2hex(random_bytes(32));

            return CodigoIndicacaoPadrinho::create([
                'padrinho_id' => $padrinho->getKey(),
                'digest' => hash('sha256', $codigo),
                'codigo' => $codigo,
                'emitido_em' => now(),
            ]);
        }, 3);
    }

    public function revogarCodigo(Pessoa $padrinho): void
    {
        DB::transaction(function () use ($padrinho) {
            $padrinho = $this->bloquearPessoas([$padrinho->getKey()])[$padrinho->getKey()];
            abort_unless($padrinho->role_pessoa === 'voluntario' && AccountAccess::canOperate($padrinho), 403);

            $this->codigoAtivo($padrinho)->update(['revogado_em' => now(), 'codigo' => null]);
        }, 3);
    }

    public function indicar(Pessoa $afilhado, string $codigo, bool $cadastro = false): SolicitacaoPadrinho
    {
        $this->garantirPodeIndicar($afilhado, $cadastro);
        $digest = hash('sha256', strtolower($codigo));
        $padrinhoId = CodigoIndicacaoPadrinho::query()->where('digest', $digest)->value('padrinho_id');
        if ($padrinhoId === null || (int) $padrinhoId === $afilhado->pessoa_id) {
            throw $this->codigoIndisponivel();
        }

        return DB::transaction(function () use ($afilhado, $padrinhoId, $digest, $cadastro) {
            $pessoas = $this->bloquearPessoas([$afilhado->getKey(), $padrinhoId]);
            $afilhado = $pessoas[$afilhado->getKey()];
            $padrinho = $pessoas[$padrinhoId];
            $this->garantirPodeIndicar($afilhado, $cadastro);

            $indicacao = $this->codigoAtivo($padrinho)->where('digest', $digest)->lockForUpdate()->first();
            if ($indicacao === null) {
                throw $this->codigoIndisponivel();
            }

            $pedidoAnterior = SolicitacaoPadrinho::where('apadrinhado_id', $afilhado->getKey())
                ->where('status', 'pendente')->lockForUpdate()->first();
            if ($pedidoAnterior && $pedidoAnterior->codigo_indicacao_id === $indicacao->getKey()) {
                return $pedidoAnterior;
            }

            $this->garantirAfilhado($afilhado, false, $pedidoAnterior?->getKey(), true);
            try {
                $this->garantirPadrinho($padrinho);
            } catch (ValidationConflictException) {
                throw $this->codigoIndisponivel();
            }

            if ($pedidoAnterior) {
                $pedidoAnterior->update(['status' => 'cancelada', 'decidida_em' => now()]);
                $padrinhoAnterior = Pessoa::findOrFail($pedidoAnterior->padrinho_id);
                $this->notificar($padrinhoAnterior, 'padrinho.cancelada', 'Uma solicitação de apadrinhamento foi cancelada.');
            }

            return $this->criarPedido($afilhado, $padrinho, $indicacao->getKey());
        }, 3);
    }

    /**
     * A análise cadastral já deve ter bloqueado a Pessoa avaliada.
     */
    public function garantirVinculoParaAprovacao(Pessoa $afilhado): void
    {
        $vinculo = Acompanhamento::query()
            ->where('apadrinhado_id', $afilhado->getKey())
            ->where('status', 'ativo')
            ->whereNotNull('aceito_em')
            ->lockForUpdate()
            ->first();
        if ($vinculo === null) {
            throw new ValidationConflictException('É necessário um vínculo de apadrinhamento aceito e elegível para aprovar o cadastro.');
        }

        $padrinho = $this->bloquearPessoas([$vinculo->padrinho_id])[$vinculo->padrinho_id];
        $this->garantirPadrinho($padrinho, false);

        if (Acompanhamento::where('padrinho_id', $padrinho->getKey())->where('status', 'ativo')->count() > 2) {
            throw new ValidationConflictException('O vínculo de apadrinhamento excede a capacidade do padrinho.');
        }
    }

    public function solicitar(Pessoa $afilhado, int $padrinhoId): SolicitacaoPadrinho
    {
        if ($afilhado->pessoa_id === $padrinhoId) {
            throw new ValidationConflictException('A pessoa não pode apadrinhar a si mesma.');
        }

        return DB::transaction(function () use ($afilhado, $padrinhoId) {
            $pessoas = $this->bloquearPessoas([$afilhado->pessoa_id, $padrinhoId]);
            $afilhado = $pessoas[$afilhado->pessoa_id];
            $padrinho = $pessoas[$padrinhoId];
            abort_unless(AccountAccess::canOperate($afilhado) && AccountAccess::canOperate($padrinho), 403);
            $this->garantirAfilhado($afilhado, true);
            $this->garantirPadrinho($padrinho);

            return $this->criarPedido($afilhado, $padrinho);
        }, 3);
    }

    public function decidirPedido(SolicitacaoPadrinho $pedido, Pessoa $ator, bool $aceitar): SolicitacaoPadrinho
    {
        if ($pedido->padrinho_id !== $ator->pessoa_id) {
            abort(404);
        }

        return DB::transaction(function () use ($pedido, $aceitar) {
            $pessoas = $this->bloquearPessoas([$pedido->padrinho_id, $pedido->apadrinhado_id]);
            $pedido = SolicitacaoPadrinho::whereKey($pedido->getKey())->lockForUpdate()->firstOrFail();
            if ($pedido->status === 'aceita' && $aceitar) {
                return $pedido;
            }
            if ($pedido->status !== 'pendente') {
                throw new ValidationConflictException('Esta solicitação já foi decidida.');
            }

            if ($aceitar) {
                abort_unless(AccountAccess::canOperate($pessoas[$pedido->padrinho_id])
                    && AccountAccess::canOperate($pessoas[$pedido->apadrinhado_id]), 403);
                // Ao aceitar, cinco participações obtidas durante a espera não extinguem o pedido.
                $this->garantirAfilhado(
                    $pessoas[$pedido->apadrinhado_id], false, $pedido->getKey(), $pedido->codigo_indicacao_id !== null
                );
                $this->garantirPadrinho($pessoas[$pedido->padrinho_id]);
                $vinculo = Acompanhamento::create([
                    'padrinho_id' => $pedido->padrinho_id,
                    'apadrinhado_id' => $pedido->apadrinhado_id,
                    'status' => 'ativo',
                    'inicio' => now()->toDateString(),
                    'aceito_em' => now(),
                ]);
                $pedido->update([
                    'status' => 'aceita', 'decidida_em' => now(),
                    'acompanhamento_id' => $vinculo->getKey(),
                ]);
                $this->notificar($pessoas[$pedido->apadrinhado_id], 'padrinho.aceita', 'Sua solicitação de apadrinhamento foi aceita.');
            } else {
                $pedido->update(['status' => 'recusada', 'decidida_em' => now()]);
                $this->notificar($pessoas[$pedido->apadrinhado_id], 'padrinho.recusada', 'Sua solicitação de apadrinhamento foi recusada.');
            }

            return $pedido->refresh();
        }, 3);
    }

    public function cancelarPedido(SolicitacaoPadrinho $pedido, Pessoa $ator): SolicitacaoPadrinho
    {
        if ($pedido->apadrinhado_id !== $ator->pessoa_id) {
            abort(404);
        }

        return DB::transaction(function () use ($pedido) {
            $pessoas = $this->bloquearPessoas([$pedido->padrinho_id, $pedido->apadrinhado_id]);
            $pedido = SolicitacaoPadrinho::whereKey($pedido->getKey())->lockForUpdate()->firstOrFail();
            if ($pedido->status !== 'pendente') {
                throw new ValidationConflictException('Somente solicitação pendente pode ser cancelada.');
            }
            $pedido->update(['status' => 'cancelada', 'decidida_em' => now()]);
            $this->notificar($pessoas[$pedido->padrinho_id], 'padrinho.cancelada', 'Uma solicitação de apadrinhamento foi cancelada.');

            return $pedido->refresh();
        }, 3);
    }

    public function encerrar(Acompanhamento $vinculo, Pessoa $ator, string $motivo): Acompanhamento
    {
        if (! in_array($ator->pessoa_id, [$vinculo->padrinho_id, $vinculo->apadrinhado_id], true)) {
            abort(404);
        }

        return DB::transaction(function () use ($vinculo, $ator, $motivo) {
            $pessoas = $this->bloquearPessoas([$vinculo->padrinho_id, $vinculo->apadrinhado_id]);
            $vinculo = Acompanhamento::whereKey($vinculo->getKey())->lockForUpdate()->firstOrFail();
            if ($vinculo->status !== 'ativo') {
                throw new ValidationConflictException('Este vínculo não está ativo.');
            }
            $vinculo->conclusoes()->where('status', 'pendente')->update([
                'status' => 'cancelada', 'decidida_em' => now(),
                'motivo_decisao' => 'Vínculo encerrado antes da análise.',
            ]);
            $vinculo->update([
                'status' => 'encerrado', 'fim' => now()->toDateString(),
                'encerrado_em' => now(), 'encerrado_por_id' => $ator->pessoa_id,
                'motivo_encerramento' => trim($motivo),
            ]);
            $outroId = $ator->pessoa_id === $vinculo->padrinho_id ? $vinculo->apadrinhado_id : $vinculo->padrinho_id;
            $this->notificar($pessoas[$outroId], 'padrinho.encerrado', 'O vínculo de apadrinhamento foi encerrado.');

            return $vinculo->refresh();
        }, 3);
    }

    public function solicitarConclusao(Acompanhamento $vinculo, Pessoa $ator): ConclusaoPadrinho
    {
        if ($vinculo->apadrinhado_id !== $ator->pessoa_id) {
            abort(404);
        }

        return DB::transaction(function () use ($vinculo) {
            $pessoas = $this->bloquearPessoas([$vinculo->padrinho_id, $vinculo->apadrinhado_id]);
            $vinculo = Acompanhamento::whereKey($vinculo->getKey())->lockForUpdate()->firstOrFail();
            $this->garantirConclusao($vinculo);
            if ($vinculo->conclusoes()->where('status', 'pendente')->exists()) {
                throw new ValidationConflictException('Já existe pedido de conclusão pendente.');
            }
            $pedido = ConclusaoPadrinho::create([
                'acompanhamento_id' => $vinculo->getKey(),
                'apadrinhado_id' => $vinculo->apadrinhado_id,
                'status' => 'pendente', 'solicitada_em' => now(),
            ]);
            $this->notificar($pessoas[$vinculo->padrinho_id], 'padrinho.conclusao_solicitada', 'Foi solicitada a conclusão do apadrinhamento.');

            return $pedido;
        }, 3);
    }

    public function decidirConclusao(ConclusaoPadrinho $pedido, Pessoa $admin, bool $aprovar, ?string $motivo): ConclusaoPadrinho
    {
        return DB::transaction(function () use ($pedido, $admin, $aprovar, $motivo) {
            $vinculoBase = Acompanhamento::findOrFail($pedido->acompanhamento_id);
            $pessoas = $this->bloquearPessoas([$vinculoBase->padrinho_id, $vinculoBase->apadrinhado_id]);
            $vinculo = Acompanhamento::whereKey($vinculoBase->getKey())->lockForUpdate()->firstOrFail();
            $pedido = ConclusaoPadrinho::whereKey($pedido->getKey())->lockForUpdate()->firstOrFail();
            if ($pedido->status !== 'pendente') {
                throw new ValidationConflictException('Este pedido já foi decidido.');
            }
            if ($vinculo->status !== 'ativo') {
                throw new ValidationConflictException('O vínculo não está ativo.');
            }
            if ($aprovar) {
                $this->garantirConclusao($vinculo);
            }
            $pedido->update([
                'status' => $aprovar ? 'aprovada' : 'rejeitada',
                'admin_id' => $admin->pessoa_id,
                'decidida_em' => now(), 'motivo_decisao' => $motivo,
            ]);
            if ($aprovar) {
                $vinculo->update([
                    'status' => 'concluido', 'fim' => now()->toDateString(),
                    'concluido_em' => now(), 'concluido_por_id' => $admin->pessoa_id,
                ]);
            }
            foreach ($pessoas as $pessoa) {
                $this->notificar($pessoa, $aprovar ? 'padrinho.conclusao_aprovada' : 'padrinho.conclusao_rejeitada',
                    $aprovar ? 'A conclusão do apadrinhamento foi aprovada.' : 'O pedido de conclusão foi rejeitado.');
            }

            return $pedido->refresh();
        }, 3);
    }

    public function progresso(Acompanhamento $vinculo): array
    {
        $inicio = $vinculo->aceito_em;

        return [
            'aceito_em' => $inicio?->toISOString(),
            'elegivel_por_tempo_em' => $inicio?->copy()->addMonthsNoOverflow(6)->toISOString(),
            'participacoes_no_vinculo' => $inicio ? $this->participacoes(
                $vinculo->apadrinhado_id,
                $inicio,
                $vinculo->encerrado_em ?? $vinculo->concluido_em ?? now(),
            ) : 0,
            'participacoes_necessarias' => 5,
        ];
    }

    private function garantirAfilhado(
        Pessoa $pessoa,
        bool $novaSolicitacao,
        ?int $pedidoAtual = null,
        bool $porIndicacao = false
    ): void {
        $cadastroEmValidacao = $porIndicacao && $pessoa->role_pessoa === 'voluntario'
            && in_array($pessoa->status_validacao, ['aguardando_validacao', 'em_analise', 'correcao_solicitada'], true);
        if ((! $pessoa->isApprovedVolunteer() && ! $cadastroEmValidacao) || $pessoa->bloqueado_ate?->isFuture()) {
            throw new ValidationConflictException('A pessoa não está elegível ao apadrinhamento.');
        }
        if ($novaSolicitacao && $this->participacoes($pessoa->pessoa_id) >= 5) {
            throw new ValidationConflictException('A pessoa já possui cinco participações confirmadas.');
        }
        $vinculoAtivo = Acompanhamento::query()
            ->where('status', 'ativo')
            ->where(fn (Builder $query) => $query->where('apadrinhado_id', $pessoa->getKey())
                ->orWhere('padrinho_id', $pessoa->getKey()))
            ->lockForUpdate()->first(['acompanhamento_id']);
        if ($vinculoAtivo) {
            throw new ValidationConflictException('A pessoa já participa de um vínculo ativo.');
        }
        if (SolicitacaoPadrinho::where('apadrinhado_id', $pessoa->pessoa_id)->where('status', 'pendente')
            ->when($pedidoAtual, fn ($query) => $query->whereKeyNot($pedidoAtual))
            ->lockForUpdate()->first(['solicitacao_padrinho_id'])) {
            throw new ValidationConflictException('A pessoa já possui solicitação pendente.');
        }
    }

    private function garantirPadrinho(Pessoa $pessoa, bool $comVaga = true): void
    {
        if (! AccountAccess::canOperate($pessoa) || ! $this->candidatos(false)->whereKey($pessoa->pessoa_id)->exists()) {
            throw new ValidationConflictException('O padrinho não atende aos critérios de elegibilidade ou capacidade.');
        }

        if ($comVaga && Acompanhamento::where('padrinho_id', $pessoa->getKey())
            ->where('status', 'ativo')->lockForUpdate()->get(['acompanhamento_id'])->count() >= 2) {
            throw new ValidationConflictException('O padrinho não atende aos critérios de elegibilidade ou capacidade.');
        }
    }

    private function codigoAtivo(Pessoa $padrinho): Builder
    {
        return CodigoIndicacaoPadrinho::where('padrinho_ativo_id', $padrinho->getKey());
    }

    private function garantirPodeIndicar(Pessoa $afilhado, bool $cadastro): void
    {
        abort_unless($afilhado->role_pessoa === 'voluntario' && AccountAccess::canOperate($afilhado), 403);
        if ($afilhado->status_validacao !== ($cadastro ? 'aguardando_validacao' : 'correcao_solicitada')
            || $afilhado->bloqueado_ate?->isFuture()) {
            throw new ValidationConflictException('A indicação só pode ser alterada durante uma correção cadastral autorizada.');
        }
    }

    private function codigoIndisponivel(): ValidationException
    {
        return ValidationException::withMessages(['codigo_padrinho' => 'O código de indicação está indisponível.']);
    }

    private function criarPedido(Pessoa $afilhado, Pessoa $padrinho, ?int $indicacaoId = null): SolicitacaoPadrinho
    {
        $pedido = SolicitacaoPadrinho::create([
            'padrinho_id' => $padrinho->getKey(),
            'apadrinhado_id' => $afilhado->getKey(),
            'codigo_indicacao_id' => $indicacaoId,
            'status' => 'pendente',
            'solicitada_em' => now(),
        ]);
        $this->notificar($padrinho, 'padrinho.solicitacao', 'Você recebeu uma solicitação de apadrinhamento.');

        return $pedido;
    }

    private function garantirConclusao(Acompanhamento $vinculo): void
    {
        if ($vinculo->status !== 'ativo' || $vinculo->aceito_em === null) {
            throw new ValidationConflictException('O vínculo não possui aceite ativo auditável.');
        }
        if (now()->lessThan($vinculo->aceito_em->copy()->addMonthsNoOverflow(6))) {
            throw new ValidationConflictException('Ainda não decorreram seis meses completos de vínculo.');
        }
        if ($this->participacoes($vinculo->apadrinhado_id, $vinculo->aceito_em) < 5) {
            throw new ValidationConflictException('São necessárias cinco participações durante o vínculo.');
        }
    }

    private function participacoes(int $pessoaId, mixed $desde = null, mixed $ate = null): int
    {
        return DB::table('p_ag_inscreve as i')
            ->join('agenda as a', 'a.agenda_id', '=', 'i.agenda_id')
            ->join('evento as e', 'e.evento_id', '=', 'a.evento_id')
            ->where('i.pessoa_id', $pessoaId)
            ->where('i.status_inscricao', 'participou')
            ->where('i.presenca', true)->whereNotNull('i.presenca_confirmada_em')
            ->where('a.status_ativo', 'finalizado')
            ->where('e.status_evento', '!=', 'cancelado')
            ->when($desde, fn ($query) => $query->where('a.data_inicio', '>=', $desde)
                ->where('a.data_inicio', '<=', $ate ?? now()))
            ->distinct()->count('a.agenda_id');
    }

    private function bloquearPessoas(array $ids)
    {
        $pessoas = Pessoa::whereKey(array_unique($ids))->orderBy('pessoa_id')->lockForUpdate()->get()->keyBy('pessoa_id');
        foreach ($ids as $id) {
            if (! $pessoas->has($id)) {
                abort(404);
            }
        }

        return $pessoas;
    }

    private function notificar(Pessoa $pessoa, string $tipo, string $mensagem): void
    {
        $pessoa->notificacoes()->create(['tipo_notificacao' => $tipo, 'mensagem_notificacao' => $mensagem]);
    }
}
