@props(['work' => null, 'navigation' => null])
@if($navigation)
    <section class="account-card" id="atuacao-organizador" data-promotion-summary>
        <h2>Minha atuação como organizador</h2>
        @if($navigation->organizer)
            <p class="account-badge success">Condição de organizador aprovada</p>
            <p>Acesse os Eventos sob sua responsabilidade em Minha atuação.</p>
            <x-botao-primario :href="route('organizador.workspace')">Acessar minha atuação</x-botao-primario>
        @else
            <details data-web-promotion data-csrf="{{ csrf_token() }}" class="mt-3">
                <summary>Solicitação e critérios de elegibilidade</summary>
                <p class="account-hint">É preciso ter cadastro aprovado, cinco participações confirmadas, confiabilidade de pelo menos 80, média de 4,0 ou mais e estar sem bloqueios ou apadrinhados ativos.</p>
                <p>Após a aprovação, seu histórico continua acessível e novas candidaturas como voluntário são desabilitadas.</p>
                <p class="account-badge info" data-web-promotion-status hidden></p>
                <p class="account-hint" data-web-promotion-reason hidden></p>
                <p class="account-hint" role="status" tabindex="-1" data-web-promotion-feedback></p>
                <form method="post" data-web-promotion-form hidden class="account-field mt-3">
                    <label for="promotion-justification">Justificativa *</label>
                    <textarea id="promotion-justification" name="justificativa" required maxlength="2000" rows="4" aria-describedby="promotion-error"></textarea>
                    <p id="promotion-error" class="account-error" role="alert"></p>
                    <x-botao-primario as="button" type="submit" size="sm">Enviar solicitação</x-botao-primario>
                </form>
                <x-botao-secundario as="button" type="button" size="sm" data-web-promotion-refresh>Atualizar solicitação</x-botao-secundario>
            </details>
        @endif
    </section>
@else
@php
    $approved=$work['person']['role_pessoa']==='organizador';
    $status=$approved ? 'Condição de organizador aprovada' : (['pendente'=>'Aguardando análise','em_analise'=>'Em análise','reprovada'=>'Solicitação recusada'][$work['promotion']['status']] ?? ($work['eligible'] ? 'Você pode solicitar' : 'Ainda não elegível'));
@endphp
<section class="account-card" id="atuacao-organizador" data-promotion-summary>
    <h2>Minha atuação como organizador</h2><p class="account-badge {{ $approved ? 'success' : 'info' }}">{{ $status }}</p>
    @if($approved)
        <p>Sua identidade e seu histórico são preservados. Gerencie os Eventos sob sua responsabilidade.</p>
        <x-botao-primario :href="route('organizador.workspace',['contexto'=>$work['linked'] ? 'organizador-vinculado' : 'organizador','jornada'=>$work['scenario']==='aprovada' ? 'independente-vazio' : $work['scenario']])">Acessar minha atuação</x-botao-primario>
    @else
        @if(in_array($work['promotion']['status'],['pendente','em_analise']))<p>Já existe uma solicitação em aberto. Acompanhe a análise; não é necessário enviar novamente.</p>
        @else
            @if($work['promotion']['motivo_decisao'])<p>{{ $work['promotion']['motivo_decisao'] }}</p>@endif
            <p>A mudança é definitiva. Confira os critérios antes de solicitar.</p>
            <ul class="text-sm space-y-2 mb-4">@foreach($work['criteria'] as $criterion)<li>{{ $criterion['met'] ? '✓' : '○' }} {{ $criterion['label'] }} · {{ $criterion['value'] }}</li>@endforeach</ul>
        @endif
        <x-botao-secundario :href="route('organizador.solicitacao',['contexto'=>'pessoa','jornada'=>$work['scenario']])">{{ $work['eligible'] ? 'Solicitar condição de organizador' : 'Acompanhar critérios e solicitação' }}</x-botao-secundario>
    @endif
</section>
@endif
