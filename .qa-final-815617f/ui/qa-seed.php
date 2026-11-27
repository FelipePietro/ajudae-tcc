<?php
require getcwd().'/vendor/autoload.php';
$app = require getcwd().'/bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\{Acompanhamento, Agenda, CatEvento, Evento, Inscricao, Ong, Permissao, Pessoa, SolicitacaoOrganizador};
use Illuminate\Support\Facades\{DB, Hash};

$identity = DB::selectOne('select database() as db, @@port as port, @@server_uuid as uuid');
if ($identity->db !== 'ajudae_qa_final_ui' || (int) $identity->port !== 34689
    || $identity->uuid !== getenv('QA_MYSQL_UUID')) {
    throw new RuntimeException('Conexão Laravel fora do MySQL isolado.');
}

$password = bin2hex(random_bytes(14));
$hash = Hash::make($password);
try {
$data = DB::transaction(static function () use ($hash): array {
    $createPerson = static function (int $n, string $login, string $role = 'voluntario', string $state = 'aprovado') use ($hash): Pessoa {
        $person = new Pessoa([
            'nm_pessoa' => 'Pessoa sintética '.str_replace('.', ' ', $login), 'genero_pessoa' => 'feminino',
            'cpf_pessoa' => '9010000'.str_pad((string) $n, 4, '0', STR_PAD_LEFT), 'dt_nasc' => '1995-05-10',
            'rg_pessoa' => '901000'.$n, 'email_pessoa' => $login.'@example.test', 'tele_pessoa' => '1100000000',
            'cep_pessoa' => '01001000', 'logradouro_pessoa' => 'Endereço de Teste', 'bairro_pessoa' => 'Centro',
            'cidade_pessoa' => 'São Paulo', 'uf_pessoa' => 'SP', 'login_pessoa' => $login, 'senha_pessoa' => $hash,
        ]);
        $person->forceFill(['role_pessoa' => $role, 'status_validacao' => $state,
            'aprovado_em' => $state === 'aprovado' ? now()->subYear() : null])->save();

        return $person;
    };
    $createOng = static function (int $n, string $login, string $state) use ($hash): Ong {
        $ong = new Ong([
            'nome_fantasia' => 'Organização sintética '.$n, 'login_ong' => $login, 'senha_ong' => $hash,
            'cnpj_ong' => '9010000000000'.$n, 'email_ong' => $login.'@example.test', 'tel_ong' => '1100000000',
            'rg_responsavel_ong_link' => 'documentos/identidade-sintetica.pdf',
            'site_url_ong' => 'https://example.test', 'pfp_ong_link' => '/images/homologacao.webp',
            'cep_ong' => '01001000', 'logradouro_ong' => 'Endereço de Teste', 'bairro_ong' => 'Centro',
            'cidade_ong' => 'São Paulo', 'uf_ong' => 'SP', 'descricao_ong' => 'Registro sintético de homologação.',
            'nome_responsavel_ong' => 'Responsável sintético', 'cpf_responsavel_ong' => '9010001000'.$n,
            'email_responsavel_ong' => 'responsavel.'.$login.'@example.test', 'tel_responsavel_ong' => '1100000000',
        ]);
        $ong->forceFill(['status_ong' => $state])->save();

        return $ong;
    };
    $person = $createPerson(1, 'qa.luiz.titular');
    $other = $createPerson(2, 'qa.luiz.outro');
    $sponsorAdmin = $createPerson(3, 'qa.luiz.padrinho', 'administrador');
    $generalAdmin = $createPerson(4, 'qa.luiz.admin', 'administrador');
    $pendingPerson = $createPerson(5, 'qa.luiz.pendente', 'voluntario', 'aguardando_validacao');
    $ong = $createOng(1, 'qa.luiz.ong', 'ativo');
    $pendingOng = $createOng(2, 'qa.luiz.ong.pendente', 'aguardando aprovação');
    $attach = static function (Pessoa $actor, array $names): void {
        $ids = array_map(static fn ($name) => Permissao::firstOrCreate(['nome' => $name])->getKey(), $names);
        $actor->permissoes()->sync($ids);
    };
    $attach($sponsorAdmin, ['padrinhos.validar_conclusao']);
    $attach($generalAdmin, ['usuarios.validar', 'ongs.validar', 'eventos.aprovar', 'organizadores.validar',
        'xp.campanhas.gerenciar', 'lgpd.visualizar', 'lgpd.responder', 'admin.logs.visualizar']);
    $category = CatEvento::create(['nome_categoria' => 'Homologação Luiz']);
    $createEvent = static function (string $name, string $state) use ($ong, $category): Evento {
        $event = new Evento(['nm_evento' => $name, 'descricao_evento' => 'Atividade sintética de homologação',
            'imagem_evento_link' => '/images/homologacao.webp', 'vagas_evento' => 3, 'modalidade_evento' => 'online', 'cat_evento_id' => $category->getKey(),
            'status_evento' => $state]);
        $event->forceFill(['ong_id' => $ong->getKey(), 'finalizado_em' => $state === 'finalizado' ? now()->subDay() : null])->save();

        return $event;
    };
    $createAgenda = static fn (Evento $event, $date, string $state = 'ativo') => Agenda::create([
        'evento_id' => $event->getKey(), 'status_ativo' => $state, 'data_inicio' => $date,
        'data_fim' => $date->copy()->addHour(), 'vagas_agenda' => 3,
    ]);
    $enroll = static function (Agenda $agenda, Pessoa $volunteer, string $status, array $fields = []): Inscricao {
        return Inscricao::create(['agenda_id' => $agenda->getKey(), 'pessoa_id' => $volunteer->getKey(),
            'status_inscricao' => $status, 'dt_inscricao' => now()->subDays(10), ...$fields]);
    };
    $absenceEvent = $createEvent('QA ausência e justificativa', 'ativo');
    $absence = $enroll($createAgenda($absenceEvent, now()->subDays(2)), $person, 'não participou', [
        'presenca' => false, 'presenca_confirmada_em' => now()->subDays(2)->addHour(),
    ]);
    $managed = $createEvent('QA presença e encerramento', 'ativo');
    $agenda = $createAgenda($managed, now()->subHours(4));
    $attendee = $enroll($agenda, $person, 'aprovado', ['declarou_estudante' => true]);
    $future = $createEvent('QA Agenda futura', 'ativo');
    $futureAttendee = $enroll($createAgenda($future, now()->addDays(3)), $person, 'aprovado');
    $legacyDone = $createEvent('QA Evento concluído', 'finalizado');
    for ($index = 0; $index < 5; $index++) {
        $past = $createAgenda($legacyDone, now()->subDays(3 + $index), 'finalizado');
        $enroll($past, $other, 'participou', ['presenca' => true, 'presenca_confirmada_em' => $past->data_fim]);
        if ($index === 0) $enroll($past, $person, 'participou', ['presenca' => true, 'presenca_confirmada_em' => $past->data_fim]);
    }
    $moderation = $createEvent('QA Evento para moderação', 'aguardando a confirmação');
    $createAgenda($moderation, now()->addDays(4));
    $promotion = SolicitacaoOrganizador::create(['pessoa_id' => $other->getKey(), 'status' => 'pendente',
        'justificativa' => 'Solicitação sintética de promoção.', 'solicitada_em' => now()]);
    $validDate = now()->subMonthsNoOverflow(7);
    $sponsorship = Acompanhamento::iniciar($person->getKey(), $other->getKey(), ['inicio' => $validDate, 'aceito_em' => $validDate]);
    $approval = $sponsorship->conclusoes()->create(['apadrinhado_id' => $other->getKey(), 'status' => 'pendente', 'solicitada_em' => now()]);
    $second = Acompanhamento::iniciar($other->getKey(), $person->getKey(), ['inicio' => $validDate, 'aceito_em' => $validDate]);
    $rejection = $second->conclusoes()->create(['apadrinhado_id' => $person->getKey(), 'status' => 'pendente', 'solicitada_em' => now()]);


    $viewer = $createPerson(6, 'qa.luiz.leitor', 'administrador');
    $responder = $createPerson(7, 'qa.luiz.atendente', 'administrador');
    $otherResponder = $createPerson(8, 'qa.luiz.atendente2', 'administrador');
    $master = $createPerson(9, 'qa.luiz.master', 'administrador');
    $master->forceFill(['admin_master' => true])->save();
    $independent = $createPerson(10, 'qa.luiz.independente', 'organizador');
    $designed = $createPerson(11, 'qa.luiz.designado', 'organizador');
    $revoked = $createPerson(12, 'qa.luiz.revogado', 'organizador');
    $empty = $createPerson(13, 'qa.luiz.vazio');
    $thirdOng = $createOng(3, 'qa.luiz.ong.terceira', 'ativo');
    $attach($viewer, ['lgpd.visualizar', 'usuarios.moderar']);
    $attach($responder, ['lgpd.responder']);
    $attach($otherResponder, ['lgpd.responder', 'eventos.aprovar']);
    foreach ([[$designed,'ativo'],[$revoked,'encerrado']] as [$organizer,$status]) {
        \App\Models\VinculoOngOrganizador::create(['ong_id'=>$ong->getKey(),'pessoa_id'=>$organizer->getKey(),'status'=>$status,'convidado_em'=>now()->subDays(2),'respondido_em'=>now()->subDay(),'encerrado_em'=>$status==='encerrado'?now():null]);
    }
    $managed->forceFill(['responsavel_pessoa_id'=>$designed->getKey()])->save();
    $revokedEvent = $createEvent('QA vínculo encerrado', 'ativo');
    $revokedEvent->forceFill(['responsavel_pessoa_id'=>$revoked->getKey()])->save();
    $revokedAgenda = $createAgenda($revokedEvent,now()->subHours(8));
    $revokedEnrollment = $enroll($revokedAgenda,$person,'aprovado');
    $independentEvent = $createEvent('QA gestão independente','ativo');
    $independentEvent->forceFill(['ong_id'=>null,'pessoa_id'=>$independent->getKey()])->save();
    $createAgenda($independentEvent, now()->subHours(6));
    $negativeEvent = $createEvent('QA Evento para reprovar','aguardando a confirmação');
    $createAgenda($negativeEvent,now()->addDays(6));
    $invalidEvent = $createEvent('QA Evento sem Agenda','aguardando a confirmação');
    $raceEvent = $createEvent('QA decisões concorrentes','aguardando a confirmação');
    $createAgenda($raceEvent,now()->addDays(8));
    $extraAbsence = $enroll($createAgenda($absenceEvent,now()->subDays(1)), $person, 'não participou', ['presenca'=>false,'presenca_confirmada_em'=>now()->subDay()->addHour()]);
    for ($index=1;$index<=12;$index++) {
        $hist = $createEvent('QA Paginação '.str_pad((string)$index,2,'0',STR_PAD_LEFT),'finalizado');
        $enroll($createAgenda($hist,now()->subDays(15+$index),'finalizado'),$person,'participou',['presenca'=>true,'presenca_confirmada_em'=>now()->subDays(15+$index)]);
    }
    $privateCase = \App\Models\SolicitacaoLgpd::create(['solicitante_type'=>Pessoa::class, 'solicitante_id'=>$person->getKey(),'tipo'=>'acesso','descricao'=>'Descrição sintética reservada.','status'=>'pendente']);
    $raceCase = \App\Models\SolicitacaoLgpd::create(['solicitante_type'=>Pessoa::class, 'solicitante_id'=>$other->getKey(),'tipo'=>'correcao','descricao'=>'Descrição sintética concorrente.','status'=>'pendente']);
    return ['other' => $other->getKey(), 'ong' => $ong->getKey(), 'extraAbsence' => $extraAbsence->getKey(), 'revokedEnrollment' => $revokedEnrollment->getKey(), 'negativeEvent' => $negativeEvent->getKey(), 'invalidEvent' => $invalidEvent->getKey(), 'raceEvent' => $raceEvent->getKey(), 'privateCase' => $privateCase->getKey(), 'raceCase' => $raceCase->getKey(), 'person' => $person->getKey(), 'absence' => $absence->getKey(), 'attendee' => $attendee->getKey(),
        'future' => $futureAttendee->getKey(), 'event' => $managed->getKey(), 'agenda' => $agenda->getKey(),
        'approval' => $approval->getKey(), 'rejection' => $rejection->getKey(), 'pendingPerson' => $pendingPerson->getKey(),
        'pendingOng' => $pendingOng->getKey(), 'moderation' => $moderation->getKey(), 'promotion' => $promotion->getKey(),
        'category' => $category->getKey()];
}, 3);
} catch (Throwable $error) {
    fwrite(STDERR, explode(' (Connection:', $error->getMessage(), 2)[0].PHP_EOL);
    exit(1);
}
file_put_contents(__DIR__.'/fixture-session.json', json_encode(['password' => $password, ...$data], JSON_THROW_ON_ERROR));
echo "Dados sintéticos criados na instância descartável.\n";
