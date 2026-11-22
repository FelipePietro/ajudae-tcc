@props(['demo'])
<div data-preferences>
    @foreach($demo['catalogs'] as $group => $options)
        <section class="account-preferences-group" data-preference-group>
            <h3>{{ ucfirst($group) }}</h3>
            <p class="account-muted">{{ ['habilidades' => 'Escolha uma habilidade e indique o nível de 1 a 5.', 'causas' => 'Selecione causas com as quais você se identifica.', 'recursos' => 'Recursos são opcionais. Indique apenas o que gostaria de disponibilizar.'][$group] }}</p>
            <p data-preference-empty @if(count($demo['preferences'][$group])) hidden @endif>Nenhuma informação adicionada.</p>
            <div class="account-preference-options">
                @foreach($options as $id => $label)
                    @php $selected = $group === 'habilidades' ? array_key_exists($id, $demo['preferences'][$group]) : in_array($id, $demo['preferences'][$group]); @endphp
                    <div class="account-preference-option">
                        <label><input type="checkbox" data-preference data-group="{{ $group }}" value="{{ $id }}" @checked($selected)> {{ $label }}</label>
                        @if($group === 'habilidades')<label class="account-level">Nível de {{ $label }} <select data-level aria-label="Nível de {{ $label }}" @disabled(! $selected)>@for($level=1;$level<=5;$level++)<option value="{{ $level }}" @selected(($demo['preferences'][$group][$id] ?? 1) === $level)>{{ $level }}</option>@endfor</select></label>@endif
                    </div>
                @endforeach
            </div>
            @if($group === 'recursos')<label class="account-field">Detalhes do recurso (opcional)<input type="text" maxlength="255" data-resource-details placeholder="Ex.: disponível aos fins de semana"></label>@endif
        </section>
    @endforeach
    <p class="account-hint" data-preference-feedback role="status">Adições e remoções afetam somente esta visualização.</p>
</div>
