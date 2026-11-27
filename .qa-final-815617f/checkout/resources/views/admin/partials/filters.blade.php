<div class="admin-widget admin-multi-filters">
    <label class="admin-filter-search">Buscar registro <input type="search" class="cfg-input" data-admin-search></label>
    <fieldset>
        <legend>{{ $page === 'candidatos' ? 'Situação do cadastro ou acompanhamento' : 'Situação' }}</legend>
        <div class="admin-filter-options">
            @foreach($visibleStates as $code)
                <label><input type="checkbox" data-admin-status-filter value="{{ $code }}"> {{ $labels[$code] }}</label>
            @endforeach
        </div>
    </fieldset>
    <div class="admin-review-actions">
        <x-botao-secundario as="button" size="sm" texto="Limpar filtros" data-admin-clear />
        <p data-admin-results tabindex="-1" role="status"></p>
    </div>
</div>
