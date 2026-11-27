@php
    $integrated = isset($integratedAdmin);
    if (!$integrated) {
    $selected = request()->query('registro');
    $multi = in_array($page, ['fila-eventos', 'cadastros-ong', 'upgrades-org', 'candidatos'], true);
    $labels = \App\Support\VisualFourFixtures::STATUSES;
    $tones = \App\Support\VisualFourFixtures::TONES;
    $visibleStates = array_unique(array_merge(array_map([\App\Support\VisualFourFixtures::class, 'displayState'], $records), \App\Support\VisualFourFixtures::RESULTS[$page] ?? []));
    }
@endphp
<header class="fila-cabecalho">
    <div><h1>{{ $title }}</h1><p>{{ $description }}</p></div>
    @if($page === 'fila-eventos' && $variant !== 'falha' && !$integrated)
        <div class="fila-resumo" aria-label="Totais de Eventos na fila">
            <div><strong data-admin-event-total="initial">{{ count(array_filter($records, fn ($row) => \App\Support\VisualFourFixtures::displayState($row) === 'aguardando a confirmação')) }}</strong><span>Aguardando aprovação inicial</span></div>
            <div><strong data-admin-event-total="reanalysis">{{ count(array_filter($records, fn ($row) => \App\Support\VisualFourFixtures::displayState($row) === 'reanalise')) }}</strong><span>Aguardando reanálise</span></div>
        </div>
    @endif
</header>

@if($integrated && in_array($page, ['candidatos', 'cadastros-ong', 'upgrades-org', 'conclusoes-padrinho', 'campanhas-xp', 'solicitacoes-lgpd', 'responder-lgpd', 'log-acoes'], true))
    @include('admin.partials.web-operations')
@elseif($integrated)
    <div class="admin-widget admin-review-filters">
        <label>Buscar registro <input type="search" class="cfg-input" disabled></label>
        <label>Situação <select class="cfg-input" disabled><option>Todas</option></select></label>
    </div>
    <div class="admin-fila admin-review-queue">
        <section class="admin-card" role="status">
            <header class="admin-card-topo"><h2>{{ $queueLabel ?? $title }}</h2></header>
            <p class="admin-desc">{{ $queueMessage ?? 'A consulta dos registros e as decisões nesta fila ainda estão indisponíveis.' }}</p>
        </section>
    </div>
@elseif($variant === 'falha')
    <p role="alert" class="admin-widget">Não foi possível carregar os registros. <a href="{{ route('admin.'.$page, ['contexto' => 'administrador']) }}">Tentar novamente</a></p>
@elseif($records)
    @if($multi)
        @include('admin.partials.filters')
    @else
        <div class="admin-widget admin-review-filters">
            <label>Buscar registro <input type="search" class="cfg-input" data-admin-search></label>
            <label>Situação <select class="cfg-input" data-admin-filter>
                <option value="">Todas</option>
                @foreach($visibleStates as $code)
                    <option value="{{ $code }}">{{ $labels[$code] }}</option>
                @endforeach
            </select></label>
        </div>
    @endif
    <p data-admin-feedback role="status"></p>
    <p data-admin-empty class="admin-widget" hidden>Nenhum registro corresponde à busca.</p>
    <div class="admin-fila admin-review-queue">
        @foreach($records as $record)
            <article class="admin-card" data-admin-record="{{ $record['id'] }}" @if($record['id'] === \App\Support\SponsorshipFixtures::APPLICANT) hidden @endif>
                @php($state = \App\Support\VisualFourFixtures::displayState($record))
                <header class="admin-card-topo">
                    <div>
                        <h2>{{ $record['title'] }}</h2>
                        <span class="admin-tag admin-tag-{{ $tones[$state] ?? 'ouro' }}" data-admin-status tabindex="-1">{{ $labels[$state] }}</span>
                        @if($page === 'candidatos')
                            <span class="admin-tag-txt">{{ match($record['kind']) {'padrinho' => 'Conclusão de apadrinhamento', 'renovacao' => 'Certidão de antecedentes', default => 'Cadastro de Pessoa'} }}</span>
                        @endif
                    </div>
                </header>
                <details @if($selected === $record['id']) open @endif>
                    <summary>Ver detalhes</summary>
                    <p class="admin-desc">{{ $record['description'] }}</p>
                    @if($record['facts'])
                        <dl class="admin-review-facts">
                            @foreach($record['facts'] as $label => $value)
                                <div><dt>{{ $label }}</dt><dd>{{ $value }}</dd></div>
                            @endforeach
                        </dl>
                    @endif
                    @if(isset($record['ong']) && isset($pages['cadastros-ong']))
                        <p>Situação da organização <a href="{{ route('admin.cadastros-ong', $params + ['registro' => $record['ong']]) }}" data-admin-linked-status="{{ $record['ong'] }}">Ativo</a></p>
                    @endif
                    @if(isset($record['agendas']))
                        <h3>Agendas</h3>
                        <ul class="admin-review-list">
                            @foreach($record['agendas'] as $agenda)
                                <li>Agenda {{ $loop->iteration }} · {{ \Illuminate\Support\Carbon::parse($agenda['data_inicio'])->format('d/m/Y H:i') }}–{{ \Illuminate\Support\Carbon::parse($agenda['data_fim'])->format('H:i') }} · {{ $agenda['vagas_agenda'] }} vagas · {{ \App\Support\VisualFourFixtures::STATUSES[$agenda['status_ativo']] }}</li>
                            @endforeach
                        </ul>
                    @endif
                    @if($record['id'] === \App\Support\SponsorshipFixtures::APPLICANT)
                        <section class="admin-document" aria-label="Indicação de padrinho">
                            <h3>Apadrinhamento</h3>
                            <p data-admin-referral></p>
                            <p class="admin-tag-txt">O vínculo e a situação cadastral são avaliações distintas.</p>
                            <x-botao-secundario as="button" size="sm" data-admin-action="pedir-padrinho" texto="Solicitar indicação de padrinho" hidden />
                        </section>
                    @endif
                    @if(isset($record['documents']))
                        @include('admin.partials.documents')
                    @endif
                    <p data-admin-block role="status" @if(empty($record['blocked'])) hidden @endif>{{ $record['blocked'] ?? '' }}</p>
                    <p data-admin-reason @if(empty($record['reason'])) hidden @endif>{{ isset($record['reason']) ? ($record['kind'] === 'lgpd' ? 'Resposta: ' : 'Justificativa: ').$record['reason'] : '' }}</p>
                    @if($record['kind'] === 'pessoa')
                        <h3 class="admin-decision-heading" hidden>Decisão sobre o cadastro</h3>
                    @elseif($record['kind'] === 'padrinho')
                        <h3 class="admin-decision-heading" hidden>Decisão sobre o acompanhamento</h3>
                    @endif
                    <div class="admin-card-acoes admin-review-actions" data-admin-card-actions>
                        @if(isset($record['public']))
                            <x-botao-secundario :href="$record['public']" :texto="$record['kind'] === 'evento' ? 'Ver evento público' : 'Ver perfil público'" data-admin-public :hidden="!in_array($record['status'], ['ativo','publicado'])" />
                        @endif
                        @if($record['id'] === 'promocao-1')
                            <x-botao-secundario :href="route('organizador.solicitacao', ['contexto'=>'pessoa','jornada'=>'pendente'])" texto="Ver acompanhamento da solicitação" data-admin-promotion-link />
                        @endif
                        @if($record['kind'] === 'ong' && $record['id'] === 'ong-1')
                            <x-botao-secundario :href="route('admin.fila-eventos', $params + ['registro' => 'evento-7'])" texto="Ver evento associado" />
                        @endif
                        @if(in_array($record['kind'], ['pessoa','promocao','evento','ong','lgpd','padrinho']))
                            <x-botao-secundario as="button" data-admin-action="assumir" texto="Assumir análise" hidden />
                            <x-botao-primario as="button" data-admin-action="aprovar" :texto="match($record['kind']) {'evento'=>'Aprovar publicação','lgpd'=>'Concluir solicitação','padrinho'=>'Aprovar conclusão','pessoa'=>'Aprovar cadastro',default=>'Aprovar'}" hidden />
                            <x-botao-declined as="button" data-admin-action="reprovar" :texto="match($record['kind']) {'lgpd'=>'Recusar solicitação','pessoa'=>'Reprovar cadastro','padrinho'=>'Rejeitar conclusão',default=>'Reprovar'}" hidden />
                        @endif
                        @if($record['kind'] === 'ong')
                            <x-botao-secundario as="button" data-admin-action="reavaliar" texto="Retornar à fila" hidden />
                        @endif
                        @if($record['kind'] === 'pessoa')
                            <x-botao-secundario as="button" data-admin-action="corrigir" texto="Solicitar correção" hidden />
                            @unless($variant === 'revisao')
                                <x-botao-declined as="button" data-admin-action="definitiva" texto="Reprovar definitivamente o cadastro" hidden />
                            @endunless
                        @endif
                    </div>
                </details>
            </article>
        @endforeach
    </div>

    @if($page === 'candidatos')
        @include('admin.partials.document-dialog')
    @endif
    <dialog class="ajudae-dialog" data-admin-dialog aria-labelledby="admin-decision-title">
        <x-dialog-close />
        <h2 id="admin-decision-title" data-admin-decision-title></h2>
        <p data-admin-decision-subject></p>
        <form method="dialog" data-admin-decision>
            <label data-admin-motive hidden><span data-admin-motive-label>Motivo</span>
                <textarea class="cfg-input" rows="3" aria-describedby="admin-motive-help"></textarea>
                <small id="admin-motive-help">Use somente informações deste caso, sem dados pessoais.</small>
            </label>
            <x-botao-primario as="button" type="submit" texto="Confirmar" />
        </form>
    </dialog>
@else
    <p class="admin-widget">Nenhum registro disponível.</p>
@endif
@unless($integrated)@include('admin.partials.data')@endunless
