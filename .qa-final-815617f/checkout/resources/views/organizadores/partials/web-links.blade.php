<div id="vinculos" class="space-y-5" data-web-organizer-links data-url="{{ route('identity.organizer-links.index') }}" data-csrf="{{ csrf_token() }}">
    @if($institution)
        <section class="panel">
            <h2 class="panel-title">Convidar organizador</h2>
            <p class="panel-sub">Informe o usuário ou e-mail que você recebeu do organizador.</p>
            <form action="{{ route('identity.organizer-links.invite') }}" method="post" data-web-organizer-invite class="mt-4">
                @csrf
                <label for="organizer-identity" class="block text-sm font-semibold">Nome de usuário ou e-mail</label>
                <div class="flex flex-wrap items-start gap-3 mt-2">
                    <input id="organizer-identity" name="identificador" required maxlength="128" autocomplete="off" aria-describedby="organizer-feedback" class="border border-[#dedbd1] rounded-xl p-3 w-full sm:max-w-md">
                    <x-botao-primario as="button" type="submit">Enviar convite</x-botao-primario>
                </div>
            </form>
        </section>
    @endif
    <p id="organizer-feedback" role="status" tabindex="-1"></p>
    <section class="panel">
        <h2 class="panel-title">{{ $institution ? 'Convites enviados' : 'Convites recebidos' }}</h2>
        <p class="panel-sub">{{ $institution ? 'A aceitação cabe à Pessoa convidada.' : 'Um organizador pode manter somente um vínculo ativo por vez.' }}</p>
        <ul data-link-status="convite_pendente" class="mt-4 space-y-3"></ul>
        <p data-status-empty hidden class="text-sm mt-4">Nenhum convite pendente.</p>
    </section>
    <section class="panel">
        <h2 class="panel-title">{{ $institution ? 'Organizadores vinculados' : 'Vínculo atual' }}</h2>
        <p class="panel-sub">O vínculo não concede responsabilidade automática pelos Eventos.</p>
        <ul data-link-status="ativo" class="mt-4 space-y-3"></ul>
        <p data-status-empty hidden class="text-sm mt-4">Nenhum vínculo ativo.</p>
    </section>
    <section class="panel">
        <details>
            <summary class="cursor-pointer font-semibold">Histórico de vínculos</summary>
            <ul data-link-status="historico" class="mt-4 space-y-3"></ul>
            <p data-status-empty hidden class="text-sm mt-4">Nenhum vínculo encerrado ou recusado.</p>
        </details>
    </section>
    <x-botao-secundario as="button" type="button" data-links-more hidden>Carregar mais</x-botao-secundario>
    <template>
        <li class="account-card">
            <strong data-link-name></strong>
            <p class="mt-1 text-sm" data-link-label></p>
            <div class="mt-3 flex flex-wrap gap-2" data-link-actions hidden>
                <x-botao-primario as="button" type="button" size="sm" data-link-accept hidden>Aceitar</x-botao-primario>
                <x-botao-secundario as="button" type="button" size="sm" data-link-decline hidden>Recusar</x-botao-secundario>
                <x-botao-declined as="button" type="button" size="sm" data-link-end hidden>Encerrar vínculo</x-botao-declined>
            </div>
        </li>
    </template>
    <dialog class="m-auto max-w-[calc(100%-2rem)] w-[448px] rounded-xl p-6 backdrop:bg-black/50" aria-labelledby="organizer-dialog-title">
        <h2 id="organizer-dialog-title" class="text-lg font-semibold"></h2>
        <p role="alert" data-dialog-feedback></p>
        <div class="mt-4 flex flex-wrap gap-2">
            <form method="dialog"><x-botao-secundario as="button" type="submit">Voltar</x-botao-secundario></form>
            <x-botao-primario as="button" type="button" data-link-confirm>Confirmar</x-botao-primario>
        </div>
    </dialog>
</div>
