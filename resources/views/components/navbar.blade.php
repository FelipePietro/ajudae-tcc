@php
    $integrated = config('identity.enabled');
    $webNavigation = $integrated ? \App\Support\WebNavigation::for(request()) : null;
    $publicHeaderNavigation = $integrated ? null : ($navigation ?? \App\Support\NavigationContext::resolve(request()->query('contexto'), 'visitante'));
@endphp
@if($webNavigation?->signedIn)
    @include('components.navbar-feed', ['webNavigation' => $webNavigation])
@elseif($publicHeaderNavigation && $publicHeaderNavigation['key'] !== 'visitante')
    @include('components.navbar-feed', ['navigation' => $publicHeaderNavigation])
@else
<nav
    aria-label="Navegação principal"
    @if($integrated) data-integrated-public-navbar @endif
    class="fixed left-0 top-0 z-50
           w-full border-b border-[#e7e2d8]
           bg-[#f8f6f0]/95 backdrop-blur"
>
    <div
        class="mx-auto flex h-[68px] w-full max-w-[1440px]
               items-center justify-between
               px-5 sm:px-8 lg:px-12"
    >

        {{-- LOGO --}}
        <a
            aria-label="Ajudae — início" data-brand-home href="{{ $integrated ? route('inicio') : \App\Support\NavigationContext::homeUrl() }}"
            class="text-[26px] font-semibold leading-none text-[#17392a]"
            style="font-family: 'Fraunces', serif;"
        >
            Ajud<span class="text-[#df9729]">ae</span>
        </a>


        {{-- DESKTOP --}}
        <div
            class="hidden items-center gap-7
                   font-poppins text-sm text-[#4d4a42]
                   md:flex"
        >

            <a
                href="{{ $integrated ? route('feed') : '/feed?contexto=visitante' }}"
                class="transition hover:text-[#17392a]"
            >
                Explorar eventos
            </a>

            <a
                href="/login"
                class="transition hover:text-[#17392a]"
            >
                Entrar
            </a>

            <a
                href="/cadastro/ong"
                class="rounded-full border border-[#17392a]
                       px-5 py-2.5 font-medium text-[#17392a]
                       transition
                       hover:bg-[#17392a]
                       hover:text-white"
            >
                Sou uma ONG
            </a>

            <a
                href="/cadastro"
                class="rounded-full bg-[#17392a]
                       px-5 py-2.5 font-medium text-white
                       transition hover:bg-[#0f2f22]"
            >
                Me voluntariar
            </a>

        </div>


        {{-- BOTÃO MOBILE --}}
        <button
            type="button"
            id="btn-menu-publico"
            class="flex h-10 w-10 items-center justify-center
                   rounded-xl border border-[#ddd8ce]
                   text-[#17392a]
                   transition hover:bg-[#eeece5]
                   md:hidden"
            aria-label="Abrir menu"
            aria-controls="menu-publico-mobile"
            aria-expanded="false"
        >
            <svg
                id="icone-menu-publico"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                class="h-5 w-5"
            >
                <path d="M4 6h16"/>
                <path d="M4 12h16"/>
                <path d="M4 18h16"/>
            </svg>
        </button>

    </div>


    {{-- MENU MOBILE --}}
    <div
        id="menu-publico-mobile"
        class="hidden border-t border-[#e7e2d8]
               bg-[#f8f6f0]
               px-5 pb-6 pt-4
               md:hidden"
    >

        <div
            class="mx-auto flex max-w-[1440px]
                   flex-col gap-2
                   font-poppins text-sm"
        >

            <a
                href="{{ $integrated ? route('feed') : '/feed?contexto=visitante' }}"
                class="rounded-xl px-4 py-3
                       text-[#4d4a42]
                       hover:bg-[#eeece5]"
            >
                Explorar eventos
            </a>

            <a
                href="/login"
                class="rounded-xl px-4 py-3
                       text-[#4d4a42]
                       hover:bg-[#eeece5]"
            >
                Entrar
            </a>


            <div class="my-2 h-px bg-[#e3dfd6]"></div>


            <a
                href="/cadastro/ong"
                class="flex items-center justify-center
                       rounded-full border border-[#17392a]
                       px-5 py-3 font-medium
                       text-[#17392a]"
            >
                Sou uma ONG
            </a>

            <a
                href="/cadastro"
                class="flex items-center justify-center
                       rounded-full bg-[#17392a]
                       px-5 py-3 font-medium text-white"
            >
                Me voluntariar
            </a>

        </div>

    </div>
</nav>


{{-- Menu keyboard behavior is shared in navigation-accessibility.js. --}}
@endif
