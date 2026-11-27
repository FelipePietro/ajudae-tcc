@php
    $actor = request()->attributes->get('web_account');
    $permissions = \App\Support\WebNavigation::for(request())->adminPermissions;
    $canReadPrivacy = isset($permissions['lgpd.visualizar']);
    $canRespond = isset($permissions['lgpd.responder']);
    $kind = $page === 'responder-lgpd' ? 'solicitacoes-lgpd' : $page;
    $filters = match ($kind) {
        'candidatos' => [
            'aguardando_validacao' => 'Aguardando validação', 'em_analise' => 'Em análise',
            'correcao_solicitada' => 'Correção solicitada', 'aprovado' => 'Aprovado',
            'reprovado' => 'Reprovado', 'reprovado_definitivamente' => 'Reprovação definitiva',
        ],
        'upgrades-org' => ['pendente' => 'Pendente', 'em_analise' => 'Em análise', 'aprovada' => 'Aprovada', 'reprovada' => 'Reprovada'],
        'solicitacoes-lgpd' => $canReadPrivacy ? ['pendente' => 'Pendente', 'em_analise' => 'Em análise', 'concluida' => 'Concluída', 'recusada' => 'Recusada'] : [],
        default => [],
    };
@endphp
<section data-admin-operations="{{ $kind }}" data-base="{{ rtrim(url('/'), '/') }}" data-csrf="{{ csrf_token() }}"
    data-actor="{{ $actor->getKey() }}" data-master="{{ (int) $actor->admin_master }}"

    data-privacy-read="{{ (int) $canReadPrivacy }}" data-privacy-respond="{{ (int) $canRespond }}"
    class="grid gap-4">
    @if($filters || $kind === 'log-acoes')
        <form data-operation-filter class="admin-widget flex flex-wrap items-end gap-3">
            @if($filters)
                <label>Situação
                    <select name="status" class="cfg-input">
                        <option value="">{{ $kind === 'candidatos' ? 'Registros em validação' : 'Todas' }}</option>
                        @foreach($filters as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                    </select>
                </label>
            @else
                <label>Código da ação <input name="acao" class="cfg-input" maxlength="120" placeholder="Ex.: lgpd.status_atualizado"></label>
            @endif
            <x-botao-secundario as="button" type="submit" :href="null" size="sm">Filtrar</x-botao-secundario>
        </form>
    @endif
    <div data-operation-create></div>
    <div data-operation-list class="grid gap-4"></div>
    @if($kind === 'candidatos')
        <details class="admin-card" data-operation-renewals>
            <summary class="cursor-pointer font-semibold">Renovações de antecedentes</summary>
            <div class="mt-3 grid gap-3"></div>
        </details>
        @if(isset($permissions['padrinhos.validar_conclusao']))
            <div><x-botao-secundario :href="route('admin.conclusoes-padrinho')" size="sm">Conclusões de apadrinhamento</x-botao-secundario></div>
        @endif
    @endif
    <x-web-operations-controls />
</section>
