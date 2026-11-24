<nav aria-label="Navegação principal">
    @if($showName && $navigation->name && $vertical)
        <p class="mb-2 break-words px-3 text-sm font-semibold">{{ $navigation->name }}</p>
    @endif
    <ul @class(['flex items-center gap-1 text-sm', 'flex-col items-stretch' => $vertical, 'justify-end' => ! $vertical])>
        @foreach($links as $route => $label)
            <li>
                @if($route === $navigation->profile && $navigation->name && ! $vertical)
                    <a href="{{ route($route) }}" class="{{ $linkClass }} inline-flex items-center gap-2" @if(request()->routeIs($route)) aria-current="page" @endif>
                        {{ $label }} <span class="account-avatar !h-7 !w-7 !text-xs" aria-label="{{ $navigation->name }}">{{ mb_strtoupper(mb_substr($navigation->name, 0, 1)) }}</span>
                    </a>
                @else
                    <a href="{{ route($route) }}" class="{{ $linkClass }}" @if(request()->routeIs($route)) aria-current="page" @endif>{{ $label }}</a>
                @endif
            </li>
        @endforeach
        @if($navigation->signedIn)
            <li>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    @if($drawer)
                        <x-botao-dourado as="button" type="submit" size="sm" class="ml-2">Sair</x-botao-dourado>
                    @else
                        <x-botao-secundario as="button" type="submit" size="sm" class="ml-2">Sair</x-botao-secundario>
                    @endif
                </form>
            </li>
        @endif
    </ul>
</nav>
