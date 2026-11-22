@props(['demo'])
{{-- Development inspection control: no persistent status/identity mutation. --}}
@if(request()->boolean('inspecao'))
<details class="account-scenarios"><summary>Visualizar situação</summary><form method="GET"><input type="hidden" name="inspecao" value="1">@if(request()->query('area') === 'privada')<input type="hidden" name="area" value="privada">@endif<label for="demo-scenario">Situação</label><select id="demo-scenario" name="cenario">@foreach($demo['scenarios'] as $value => $label)<option value="{{ $value }}" @selected($value === $demo['scenario'])>{{ $label }}</option>@endforeach</select><x-botao-secundario as="button" :href="null" type="submit">Visualizar</x-botao-secundario></form></details>
@endif
