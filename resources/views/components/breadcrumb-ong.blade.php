@props([
    'itens' => [],
    'raizHref' => null,
    'raizLabel' => 'Dashboard'
])
@php
if ($raizHref === null && in_array(request()->query('contexto'), ['organizador','organizador-vinculado'], true)) {
    $raizHref = route('organizador.workspace', ['contexto'=>request()->query('contexto'),'jornada'=>request()->query('jornada')]);
    $raizLabel = 'Minha atuação';
}
@endphp

<nav
    aria-label="Breadcrumb"
    class="
        flex min-h-[48px] w-full
        items-center
        border-b border-[#DEDCD5]
        bg-white
        px-4
        sm:px-6
        lg:px-7
    "
>
    <ol
        class="
            flex min-w-0
            items-center
            gap-2.5
            font-poppins
            text-[13px]
        "
    >

        {{-- DASHBOARD SEMPRE COMO RAIZ --}}
        <li class="shrink-0">
            <a
                href="{{ $raizHref ?? route('ong.painel') }}"
                class="
                    text-[#777A72]
                    transition-colors
                    hover:text-[#17392A]
                "
            >
                {{ $raizLabel }}
            </a>
        </li>


        {{-- ITENS DA PÁGINA --}}
        @foreach($itens as $item)

            <li
                class="shrink-0 text-[#AAA9A3]"
                aria-hidden="true"
            >
                ›
            </li>

            <li
                class="
                    min-w-0
                    {{ $loop->last
                        ? 'font-semibold text-[#17392A]'
                        : 'text-[#777A72]'
                    }}
                "
            >

                @if(
                    !empty($item['route'])
                    && !$loop->last
                )

                    <a
                        href="{{ route(
                            $item['route'],
                            array_merge(array_filter(['contexto'=>request()->query('contexto'), 'jornada'=>is_string(request()->query('jornada')) && in_array(request()->query('jornada'), \App\Support\VisualThreeFixtures::SCENARIOS,true) ? request()->query('jornada') : null]), $item['params'] ?? [])
                        ) }}"
                        class="
                            transition-colors
                            hover:text-[#17392A]
                        "
                    >
                        {{ $item['label'] }}
                    </a>

                @else

                    <span class="truncate">
                        {{ $item['label'] }}
                    </span>

                @endif

            </li>

        @endforeach

    </ol>
</nav>