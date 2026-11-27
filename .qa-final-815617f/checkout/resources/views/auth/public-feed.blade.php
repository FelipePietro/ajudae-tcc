{{-- Public cards use the same feed grid and card treatment, with the public projection. --}}
@forelse($publicEvents as $event)
    <article data-public-event class="group flex min-h-[370px] flex-col overflow-hidden rounded-2xl border border-[#dedacf] bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
        <div class="flex h-[185px] items-center justify-center bg-gradient-to-br from-[#dbece2] to-[#f1eee5] text-5xl" aria-hidden="true">🌱</div>
        <div class="flex flex-1 flex-col p-4">
            @php
                $mode = match ($event->modalidade_evento) {
                    'presencial' => 'Presencial', 'online' => 'Online', 'hibrido' => 'Híbrido', default => null,
                };
                $location = implode(', ', array_filter([$event->cidade_evento, $event->uf_evento]));
            @endphp
            @if($mode)
                <div class="flex flex-wrap gap-2"><span class="rounded-full border border-[#dedacf] bg-[#faf9f5] px-3 py-1 font-poppins text-[10px]">{{ $mode }}</span></div>
            @endif
            <h2 class="mt-3 break-words font-poppins text-[15px] font-semibold leading-5">{{ $event->nm_evento }}</h2>
            <p class="mt-2 break-words text-sm text-[#56594f]">{{ $event->descricao_evento }}</p>
            @if($location)<p class="mt-3 font-poppins text-[11px] text-[#69645b]">📍 {{ $location }}</p>@endif
            <div class="mt-auto flex justify-end pt-5">
                <x-botao-primario :href="route('eventos.show', $event->getKey())" size="sm">Ver evento</x-botao-primario>
            </div>
        </div>
    </article>
@empty
    <div id="sem-resultados" role="status" class="rounded-2xl border border-[#dedacf] bg-white p-8 text-center md:col-span-2">
        <div class="text-3xl" aria-hidden="true">🔎</div>
        <p class="mt-3 font-poppins text-sm font-semibold">{{ $publishedAvailable ? 'Nenhum evento encontrado' : 'Nenhum evento publicado está disponível no momento.' }}</p>
        @if($publishedAvailable)<p class="mt-1 font-poppins text-xs text-[#56594f]">Tente alterar os filtros ou sua busca.</p>@endif
    </div>
@endforelse
