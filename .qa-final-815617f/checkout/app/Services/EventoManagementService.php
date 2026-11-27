<?php

namespace App\Services;

use App\Exceptions\ValidationConflictException;
use App\Models\Evento;
use App\Models\Ong;
use App\Models\Pessoa;
use App\Support\AccountAccess;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RuntimeException;

final class EventoManagementService
{
    private const ASSOCIACOES = [
        'habilidades' => 'habilidade',
        'causas' => 'causa',
        'recursos' => 'recurso',
    ];

    private const ENDERECO_OBRIGATORIO = [
        'logradouro_evento', 'cep_evento', 'cidade_evento', 'bairro_evento', 'uf_evento',
    ];

    private const CAMPOS_DO_SERVIDOR = [
        'ong_id' => ['prohibited'],
        'pessoa_id' => ['prohibited'],
        'responsavel_pessoa_id' => ['prohibited'],
        'status_evento' => ['prohibited'],
    ];

    public function __construct(
        private EventoOperationalService $operations,
        private ManagedImageStorage $images,
    ) {}

    public static function regrasCriacao(bool $exigirLinkLegado, bool $online): array
    {
        return [
            ...self::regrasEvento(true, $online),
            ...($exigirLinkLegado ? [] : self::CAMPOS_DO_SERVIDOR),
            'imagem_evento_link' => $exigirLinkLegado ? ['required', 'string', 'max:255'] : ['prohibited'],
            'imagem_evento' => $exigirLinkLegado ? ['prohibited'] : ['sometimes', 'file', 'max:5120'],
            'agendas' => ['required', 'array', 'list', 'min:1'],
            'agendas.*' => ['required', 'array:data_inicio,data_fim,vagas_agenda'],
            ...AgendaManagementService::regras(true, 'agendas.*.'),
            ...self::regrasAssociacoes(),
        ];
    }

    public static function regrasEdicao(bool $online, bool $aceitarLinkLegado): array
    {
        return [
            ...self::regrasEvento(false, $online),
            ...($aceitarLinkLegado ? [] : self::CAMPOS_DO_SERVIDOR),
            'imagem_evento_link' => $aceitarLinkLegado ? ['sometimes', 'string', 'max:255'] : ['prohibited'],
            ...self::regrasAssociacoes(),
        ];
    }

    private static function regrasEvento(bool $criacao, bool $online): array
    {
        $presenca = $criacao ? 'required' : 'sometimes';
        $local = $criacao ? [Rule::requiredIf(! $online)] : ['sometimes', Rule::requiredIf(! $online)];

        return [
            'nm_evento' => [$presenca, 'string', 'max:64'],
            'descricao_evento' => [$presenca, 'string', 'max:256'],
            'vagas_evento' => [$presenca, 'integer', 'min:1', 'max:4294967295'],
            'cat_evento_id' => [$presenca, 'integer', 'exists:cat_evento,cat_evento_id'],
            'modalidade_evento' => [$presenca, Rule::in(['presencial', 'online', 'hibrido'])],
            'logradouro_evento' => [...$local, 'nullable', 'string', 'max:64'],
            'cep_evento' => [...$local, 'nullable', 'digits:8'],
            'cidade_evento' => [...$local, 'nullable', 'string', 'max:64'],
            'bairro_evento' => [...$local, 'nullable', 'string', 'max:64'],
            'uf_evento' => [...$local, 'nullable', 'string', 'size:2'],
            'compl_evento' => ['nullable', 'string', 'max:64'],
        ];
    }

    private static function regrasAssociacoes(): array
    {
        $regras = [];
        foreach (self::ASSOCIACOES as $relacao => $tabela) {
            $regras[$relacao] = ['sometimes', 'array', 'list'];
            $regras["{$relacao}.*"] = ['required', 'integer', 'distinct', "exists:{$tabela},{$tabela}_id"];
        }

        return $regras;
    }

    public function criar(Pessoa|Ong $ator, array $dados, ?UploadedFile $imagem = null): Evento
    {
        Gate::forUser($ator)->authorize('create', Evento::class);
        $agendas = $dados['agendas'] ?? [];
        if ($agendas === []) {
            throw ValidationException::withMessages(['agendas' => 'Informe pelo menos uma Agenda.']);
        }
        foreach ($agendas as $indice => $agenda) {
            AgendaManagementService::validarDuracao($agenda['data_inicio'], $agenda['data_fim'], "agendas.{$indice}.data_fim");
        }

        $persistir = function (array $metadados = []) use ($ator, $dados, $agendas): Evento {
            return DB::transaction(function () use ($ator, $dados, $agendas, $metadados) {
                Gate::forUser($ator)->authorize('create', [Evento::class, true]);
                $evento = new Evento($this->atributos($dados));
                if (! $evento->forceFill([
                    ...$metadados,
                    'status_evento' => EventoModerationService::AGUARDANDO,
                    'ong_id' => $ator instanceof Ong ? $ator->getKey() : null,
                    'pessoa_id' => $ator instanceof Pessoa ? $ator->getKey() : null,
                    'responsavel_pessoa_id' => $ator instanceof Pessoa ? $ator->getKey() : null,
                ])->save()) {
                    throw new RuntimeException('Não foi possível salvar o Evento.');
                }

                foreach ($agendas as $agenda) {
                    if (! $evento->agendas()->make(AgendaManagementService::atributos($agenda))->save()) {
                        throw new RuntimeException('Não foi possível salvar a Agenda.');
                    }
                }
                $this->sincronizarAssociacoes($evento, $dados);

                return $this->carregarDetalhes($evento->refresh());
            }, 3);
        };

        return $imagem === null ? $persistir() : $this->images->withNewEventImage($imagem, $persistir);
    }

    public function listar(Pessoa|Ong $ator, int $porPagina): Paginator
    {
        Gate::forUser($ator)->authorize('viewManagedAny', Evento::class);
        $eventos = Evento::query()->with(['categoria', 'agendas'])->orderByDesc('evento_id');
        if ($ator instanceof Ong) {
            return $eventos->where('ong_id', $ator->getKey())->simplePaginate($porPagina);
        }

        $vinculo = $ator->vinculoOngAtivo()->with('ong')->first();
        if ($vinculo !== null) {
            abort_unless($vinculo->ong !== null && AccountAccess::activeOng($vinculo->ong), 403);
            $eventos->where('ong_id', $vinculo->ong_id)->where('responsavel_pessoa_id', $ator->getKey());
        } else {
            $eventos->whereNull('ong_id')->where('pessoa_id', $ator->getKey())
                ->where(fn ($query) => $query->whereNull('responsavel_pessoa_id')->orWhere('responsavel_pessoa_id', $ator->getKey()));
        }

        return $eventos->simplePaginate($porPagina);
    }

    public function consultar(Pessoa|Ong $ator, Evento $evento): Evento
    {
        Gate::forUser($ator)->authorize('manage', $evento);

        return $this->carregarDetalhes($evento);
    }

    public function atualizar(Pessoa|Ong $ator, Evento $evento, array $dados): Evento
    {
        return DB::transaction(function () use ($ator, $evento, $dados) {
            $evento = Evento::whereKey($evento->getKey())->lockForUpdate()->firstOrFail();
            $this->autorizarEdicao($ator, $evento, true);
            $atributos = $this->atributos($dados);
            $modalidadeMudou = isset($atributos['modalidade_evento']) && $atributos['modalidade_evento'] !== $evento->modalidade_evento;
            $modalidade = $atributos['modalidade_evento'] ?? $evento->modalidade_evento;
            if ($modalidadeMudou && $modalidade === 'online') {
                $atributos += array_fill_keys([...self::ENDERECO_OBRIGATORIO, 'compl_evento'], null);
            }

            $evento->fill($atributos);
            if ($modalidade !== 'online' && ($modalidadeMudou || $evento->isDirty(self::ENDERECO_OBRIGATORIO))) {
                $erros = [];
                foreach (self::ENDERECO_OBRIGATORIO as $campo) {
                    if (blank($evento->{$campo})) {
                        $erros[$campo] = 'Informe o endereço do Evento.';
                    }
                }
                if ($erros !== []) {
                    throw ValidationException::withMessages($erros);
                }
            }

            $alterados = array_keys($evento->getDirty());
            $associacoesAlteradas = $this->sincronizarAssociacoes($evento, $dados);
            if ($alterados === [] && ! $associacoesAlteradas) {
                return $evento;
            }

            $localizacao = array_intersect($alterados, [...self::ENDERECO_OBRIGATORIO, 'compl_evento', 'modalidade_evento']) !== [];
            $sensivel = $localizacao || $associacoesAlteradas || in_array('descricao_evento', $alterados, true);
            $evento->save();
            if ($sensivel || $evento->status_evento === 'reprovado') {
                $this->operations->exigirReaprovacao($evento);
            }
            if ($localizacao) {
                foreach ($evento->agendas as $agenda) {
                    $this->operations->concederDesistenciaSemPenalidade($agenda);
                }
            }
            $this->operations->notificarEvento($evento, 'evento.alterado', "O evento {$evento->nm_evento} foi alterado.", $localizacao);

            return $evento;
        }, 3);
    }

    public function cancelar(Pessoa|Ong $ator, Evento $evento, string $motivo): Evento
    {
        return DB::transaction(function () use ($ator, $evento, $motivo) {
            $evento = Evento::whereKey($evento->getKey())->lockForUpdate()->firstOrFail();
            Gate::forUser($ator)->authorize('manage', [$evento, true]);
            if (in_array($evento->status_evento, ['cancelado', 'finalizado'], true)) {
                throw new ValidationConflictException('O evento já está em estado terminal.');
            }

            $statusAnterior = $evento->status_evento;
            $canceladoEm = now();
            $iniciado = $evento->agendas()->where('status_ativo', '!=', 'cancelado')
                ->where('data_inicio', '<=', $canceladoEm)->exists();
            $metadados = [
                'motivo_cancelamento' => trim($motivo),
                'cancelado_em' => $canceladoEm,
                'cancelado_por_tipo' => $ator instanceof Ong ? 'ong' : 'pessoa',
                'cancelado_por_id' => $ator->getKey(),
            ];
            $evento->forceFill(['status_evento' => 'cancelado', ...$metadados])->save();
            $evento->agendas()->whereNotIn('status_ativo', ['finalizado', 'cancelado'])->update([
                'status_ativo' => 'cancelado', ...$metadados,
            ]);
            $this->operations->registrarCancelamento($evento, $ator, $statusAnterior, $iniciado);
            $this->operations->notificarEvento($evento, 'evento.cancelado', "O evento {$evento->nm_evento} foi cancelado. Sua participação não será penalizada.");

            return $evento;
        }, 3);
    }

    public static function regrasCancelamento(): array
    {
        return ['motivo' => ['required', 'string', 'max:2000', 'not_regex:/^\s*$/']];
    }

    public function autorizarEdicao(Pessoa|Ong $ator, Evento $evento, bool $lock = false): void
    {
        Gate::forUser($ator)->authorize('manage', [$evento, $lock]);
        if (in_array($evento->status_evento, ['cancelado', 'finalizado'], true)) {
            throw new ValidationConflictException('Evento terminal não pode ser alterado.');
        }
    }

    public function salvarImagem(Pessoa|Ong $ator, Evento $evento, UploadedFile $arquivo): Evento
    {
        $this->autorizarEdicao($ator, $evento);
        $imagem = $this->images->prepare($arquivo);

        return $this->images->replace(
            $evento,
            $imagem['bytes'],
            $imagem['mime_type'],
            fn (Evento $registrado) => $this->notificarAlteracaoImagem($ator, $registrado),
        );
    }

    public function removerImagem(Pessoa|Ong $ator, Evento $evento): Evento
    {
        $this->autorizarEdicao($ator, $evento);

        return $this->images->remove($evento, function (Evento $registrado) use ($ator) {
            $this->autorizarEdicao($ator, $registrado, true);
            if ($registrado->managed_image_path !== null || $registrado->imagem_evento_link !== null) {
                $this->notificarAlteracaoImagem($ator, $registrado);
            }
        });
    }

    private function notificarAlteracaoImagem(Pessoa|Ong $ator, Evento $evento): void
    {
        $this->autorizarEdicao($ator, $evento, true);
        if ($evento->status_evento === 'reprovado') {
            $this->operations->exigirReaprovacao($evento);
        }
        $this->operations->notificarEvento($evento, 'evento.alterado', "O evento {$evento->nm_evento} foi alterado.");
    }

    private function carregarDetalhes(Evento $evento): Evento
    {
        return $evento->load(['categoria', 'agendas', 'habilidades', 'causas', 'recursos']);
    }

    private function atributos(array $dados): array
    {
        return Arr::only($dados, [
            'nm_evento', 'descricao_evento', 'vagas_evento', 'modalidade_evento',
            'cat_evento_id', 'imagem_evento_link', ...self::ENDERECO_OBRIGATORIO, 'compl_evento',
        ]);
    }

    private function sincronizarAssociacoes(Evento $evento, array $dados): bool
    {
        $alterou = false;
        foreach (array_keys(self::ASSOCIACOES) as $relacao) {
            if (array_key_exists($relacao, $dados)) {
                $mudancas = $evento->{$relacao}()->sync($dados[$relacao]);
                $alterou = $alterou || $mudancas['attached'] !== [] || $mudancas['detached'] !== [];
            }
        }

        return $alterou;
    }
}
