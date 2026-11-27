<?php

require __DIR__.'/guard.php';

use App\Models\AdminLog;
use App\Models\Agenda;
use App\Models\CampanhaXp;
use App\Models\ConclusaoPadrinho;
use App\Models\DocumentoPessoa;
use App\Models\Evento;
use App\Models\Inscricao;
use App\Models\Ong;
use App\Models\Pessoa;
use App\Models\SolicitacaoLgpd;
use App\Models\SolicitacaoOrganizador;
use App\Models\XpTransacao;
use Illuminate\Support\Facades\DB;

$read = static fn ($file) => json_decode(file_get_contents(__DIR__.'/'.$file), true, flags: JSON_THROW_ON_ERROR);
$f = $read('fixture-session.json');
$d = $read('doc-fixtures.json');
$candidate = Pessoa::findOrFail($f['pendingPerson']);
$subject = Pessoa::findOrFail($f['person']);
$reviewer = Pessoa::where('login_pessoa', 'qa.luiz.admin')->firstOrFail();
$ong = Ong::findOrFail($f['ong']);
$doc = DocumentoPessoa::findOrFail($d['renewal']);
$sponsor = ConclusaoPadrinho::findOrFail($f['rejection']);
$attendance = Inscricao::findOrFail($f['attendee']);
$absence = Inscricao::findOrFail($f['absence']);
$just = $absence->justificativaParticipacao()->first();
$agenda = Agenda::findOrFail($f['agenda']);
$event = Evento::findOrFail($f['event']);
$promo = SolicitacaoOrganizador::findOrFail($f['promotion']);
$campaign = CampanhaXp::where('nome', 'QA campanha local final')->first();
$privacy = SolicitacaoLgpd::findOrFail($f['privateCase']);
$downloads = AdminLog::where('acao', 'documento.download')->whereIn('recurso_id', [$d['registration'], $d['renewal']])->get();
$sponsorLog = AdminLog::where('acao', 'padrinho.conclusao_decidida')->where('recurso_id', $sponsor->getKey())->get();
$result = [
  'cadastro' => ['situacao' => $candidate->status_validacao, 'atribuidoAoOperador' => $candidate->admin_analisando_id === $reviewer->getKey(), 'dataDaAtribuicao' => $candidate->analise_assumida_em !== null],
  'renovacao' => ['situacao' => $doc->status, 'operadorRegistrado' => $doc->validated_by_admin_id === $reviewer->getKey(), 'dataDaDecisao' => $doc->validated_at !== null, 'titular' => $subject->status_validacao, 'janelaRetencaoDias' => $doc->validated_at?->diffInDays($doc->delete_after)],
  'download' => $downloads->map(fn ($log) => [
      'documento' => $log->recurso_id, 'atorCorreto' => $log->pessoa_id === $reviewer->getKey(), 'resultado' => $log->detalhes['resultado'],
  ])->all(),
  'apadrinhamento' => ['situacao' => $sponsor->status, 'motivoPersistiu' => filled($sponsor->motivo_decisao), 'autorRegistrado' => $sponsor->admin_id !== null, 'dataRegistrada' => $sponsor->decidida_em !== null, 'logs' => $sponsorLog->count(), 'logMesmoAutor' => $sponsorLog->isNotEmpty() && $sponsorLog->every(fn ($log) => $log->pessoa_id === $sponsor->admin_id), 'logComMotivo' => $sponsorLog->isNotEmpty() && $sponsorLog->every(fn ($log) => in_array($sponsor->motivo_decisao, $log->detalhes, true))],
  'presenca' => ['agenda' => $attendance->agenda_id, 'presente' => $attendance->presenca, 'data' => $attendance->presenca_confirmada_em !== null, 'monitoria' => ($attendance->monitoria?->confirmada === true && $attendance->monitoria?->validado_em !== null), 'monitoriaAutorONG' => $attendance->monitoria?->validado_por_id === $ong->getKey(), 'camposAutoria' => collect($attendance->getAttributes())->filter(fn ($v, $k) => str_contains($k, '_por_') && $v !== null)->keys()->all()],
  'justificativa' => ['status' => $just?->status, 'autorTipo' => $just?->decidido_por_tipo, 'autorONG' => $just?->decidido_por_id === $ong->getKey(), 'estadoOriginal' => $absence->status_inscricao, 'presencaOriginal' => $absence->presenca, 'motivoDecisao' => filled($just?->motivo_decisao)],
  'encerramento' => ['agenda' => $agenda->status_ativo, 'evento' => $event->status_evento, 'notaVoluntario' => DB::table('avaliacao_voluntario')->where('evento_id',$event->getKey())->value('nota'), 'xp' => XpTransacao::where('agenda_id',$agenda->getKey())->pluck('xp_final')->all()],
  'documentosCadastro' => ['rg' => DocumentoPessoa::findOrFail($d['registration'])->status, 'certidao' => DocumentoPessoa::findOrFail($d['registrationCertificate'])->status], 'moderacao' => ['eventoAprovado' => Evento::findOrFail($f['moderation'])->status_evento, 'ong' => Ong::findOrFail($f['pendingOng'])->status_ong, 'evento' => Evento::findOrFail($f['negativeEvent'])->status_evento, 'promocao' => $promo->status],
  'campanha' => ['presente' => $campaign !== null, 'multiplicador' => $campaign?->multiplicador, 'ativa' => $campaign?->ativa, 'logs' => $campaign === null ? 0 : AdminLog::where('recurso_type',CampanhaXp::class)->where('recurso_id',$campaign->getKey())->count()],
  'lgpd' => ['status' => $privacy->status, 'ator' => $privacy->respondida_por !== null, 'resposta' => filled($privacy->resposta), 'logs' => AdminLog::where('recurso_type',SolicitacaoLgpd::class)->where('recurso_id',$privacy->getKey())->where('acao','lgpd.status_atualizado')->count()],
];
$json = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL;
file_put_contents(__DIR__.'/persistencia-final.json', $json);
echo $json;
