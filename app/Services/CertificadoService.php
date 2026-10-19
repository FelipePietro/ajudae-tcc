<?php

namespace App\Services;

use App\Exceptions\ValidationConflictException;
use App\Models\Certificado;
use App\Models\Evento;
use App\Models\Pessoa;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CertificadoService
{
    public function emitir(Evento $evento, Pessoa $pessoa): Certificado
    {
        return DB::transaction(function () use ($evento, $pessoa) {
            $evento = Evento::whereKey($evento->getKey())->lockForUpdate()->firstOrFail();
            if ($evento->status_evento !== 'finalizado') {
                throw new ValidationConflictException('O evento precisa estar finalizado para emitir certificado.');
            }

            $existente = Certificado::where('evento_id', $evento->evento_id)
                ->where('pessoa_id', $pessoa->pessoa_id)->first();
            if ($existente) {
                return $existente;
            }

            $agendas = DB::table('agenda')
                ->join('p_ag_inscreve as inscricao', 'inscricao.agenda_id', '=', 'agenda.agenda_id')
                ->where('agenda.evento_id', $evento->evento_id)
                ->where('agenda.status_ativo', 'finalizado')
                ->where('inscricao.pessoa_id', $pessoa->pessoa_id)
                ->where('inscricao.status_inscricao', 'participou')
                ->where('inscricao.presenca', true)
                ->whereNotNull('inscricao.presenca_confirmada_em')
                ->select('agenda.data_inicio', 'agenda.data_fim')->get();

            if ($agendas->isEmpty()) {
                throw new ValidationConflictException('A pessoa não possui participação confirmada neste evento.');
            }
            if (! DB::table('avaliacao_voluntario')
                ->where('evento_id', $evento->evento_id)
                ->where('pessoa_id', $pessoa->pessoa_id)
                ->whereBetween('nota', [1, 5])->exists()) {
                throw new ValidationConflictException('A avaliação da participação ainda não foi registrada.');
            }

            $segundos = $agendas->sum(fn ($agenda) =>
                Carbon::parse($agenda->data_fim)->getTimestamp() - Carbon::parse($agenda->data_inicio)->getTimestamp()
            );
            if ($segundos <= 0) {
                throw new ValidationConflictException('As agendas não possuem duração válida.');
            }

            $evento->load(['ong', 'pessoa']);
            $dados = [
                'evento_id' => $evento->evento_id,
                'pessoa_id' => $pessoa->pessoa_id,
                'total_minutos' => intdiv($segundos, 60),
                'nome_participante' => $pessoa->nm_pessoa,
                'nome_evento' => $evento->nm_evento,
                'nome_responsavel' => $evento->ong?->nome_fantasia ?? $evento->pessoa?->nm_pessoa,
                'emitido_em' => now(),
            ];

            for ($tentativa = 0; $tentativa < 5; $tentativa++) {
                $codigo = 'AJD-'.Str::upper(Str::random(32));
                if (Certificado::where('codigo_verificacao', $codigo)->exists()) {
                    continue;
                }
                try {
                    return Certificado::create($dados + ['codigo_verificacao' => $codigo]);
                } catch (QueryException $e) {
                    if (! Certificado::where('codigo_verificacao', $codigo)->exists()) {
                        throw $e;
                    }
                }
            }

            throw new ValidationConflictException('Não foi possível gerar um código de verificação único.');
        });
    }
}
