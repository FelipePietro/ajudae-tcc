@props([
    'itens' => []
])

<div
    class="
        admin-breadcrumb-bar
        flex min-h-[48px]
        items-center justify-between
        gap-4
        border-b border-[#DEDCD5]
        bg-white
        px-4
        sm:px-6
        lg:px-7
    "
>

    <nav aria-label="Breadcrumb" class="min-w-0">
        <ol
            class="
                flex min-w-0
                items-center
                gap-2.5
                font-poppins
                text-[13px]
            "
        >

            <li class="shrink-0">
                <a
                    href="{{ route('admin.admin') }}"
                    class="
                        text-[#777A72]
                        transition-colors
                        hover:text-[#17392A]
                    "
                >
                    Dashboard
                </a>
            </li>

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
                                $item['params'] ?? []
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

    @if(isset($acoes))
        <div class="shrink-0">
            {{ $acoes }}
        </div>
    @endif

</div>