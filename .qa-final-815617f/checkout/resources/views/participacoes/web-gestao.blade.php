<section id="gestao-participacoes" class="mt-6 grid gap-4" data-participation-management
    data-base="{{ rtrim(url('/'), '/') }}" data-csrf="{{ csrf_token() }}" aria-labelledby="titulo-gestao-participacoes">
    <h2 id="titulo-gestao-participacoes" class="text-xl font-semibold text-[#17392a]">Gestão de participações</h2>
    <div data-managed-participation-list class="grid gap-4"></div>
    <x-web-operations-controls />
</section>
