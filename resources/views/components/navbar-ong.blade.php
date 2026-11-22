    @props([
        'ativo' => null,
        'webNavigation' => null,

        'nome' => 'Carlos Ferreira',
        'ong' => 'Instituto Esperança',
        'iniciais' => 'CF',

        'eventosPendentes' => 3,
        'candidatosPendentes' => 12,
        'notificacoes' => 4,
    ])

    @php
        $institutionalParams = $webNavigation ? [] : ['contexto' => 'ong'];
        $profileParams = $webNavigation ? [] : ['area' => 'privada', 'contexto' => 'ong'];
        $home = $webNavigation ? route('inicio') : \App\Support\NavigationContext::homeUrl();
        $settings = 'ong.configuracoes';
        $ativo = $webNavigation
            ? match (request()->route()?->getName()) {
                'ong.painel' => 'dashboard',
                'feed' => 'explorar',
                'ong.perfil' => 'perfil',
                'ong.configuracoes', 'conta' => 'configuracoes',
                'ong.privacidade' => 'privacidade',
                'ong.eventos.index' => 'eventos',
                'ong.eventos.criar' => 'criar-evento',
                'ong.candidatos' => 'candidatos',
                'ong.organizadores' => 'organizadores',
                'ong.notificacoes' => 'notificacoes',
                default => null,
            }
            : \App\Support\NavigationContext::ongActive();

        if ($webNavigation) {
            $nome = $webNavigation->name;
            $ong = 'Conta institucional';
            $iniciais = $webNavigation->initials;
        }
    @endphp
    @unless($webNavigation)
        <x-notification-context />
    @endunless

    <div class="relative">

        {{-- ========================================================= --}}
        {{-- NAVBAR MOBILE --}}
        {{-- ========================================================= --}}

        <header
            @if($webNavigation) data-integrated-ong-header @endif
            class="
                sticky top-0 z-40
                flex h-16 w-full
                items-center justify-between
                border-b border-white/10
                bg-[#17392a]
                px-4 text-white
                shadow-sm
                lg:hidden
            "
        >

            {{-- LOGO --}}
            <a
                aria-label="Ajudae — início" data-brand-home href="{{ $home }}"
                class="text-2xl font-semibold tracking-tight"
                style="font-family: 'Fraunces', serif;"
            >
                Ajud<span class="text-[#e3a62f]">ae</span>
            </a>


            <div class="flex items-center gap-2">


                {{-- NOTIFICAÇÕES --}}
                <a
                    @unless($webNavigation) data-notification-entry @endunless href="{{ route('ong.notificacoes', $institutionalParams) }}"
                    class="
                        relative
                        flex h-10 w-10
                        items-center justify-center
                        rounded-full
                        bg-white/10
                        transition
                        hover:bg-white/15
                    "
                    aria-label="Notificações"
                >

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        class="h-5 w-5"
                    >
                        <path
                            d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"
                        />

                        <path
                            d="M13.73 21a2 2 0 0 1-3.46 0"
                        />
                    </svg>


                    @if(false)

                        <span
                            class="
                                absolute -right-1 -top-1
                                flex h-5 min-w-5
                                items-center justify-center
                                rounded-full
                                bg-[#e3a62f]
                                px-1
                                text-[10px]
                                font-bold
                                text-[#17392a]
                            "
                        >
                            {{ $notificacoes }}
                        </span>

                    @endif

                </a>




                {{-- ABRIR MENU --}}
                <button
                    type="button"
                    id="btn-abrir-menu-ong"
                    aria-controls="sidebar-ong"
                    class="
                        flex h-10 w-10
                        items-center justify-center
                        rounded-xl
                        bg-white/10
                        transition
                        hover:bg-white/15
                    "
                    aria-label="Abrir menu"
                    aria-expanded="false"
                >

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-5 w-5"
                    >
                        <path d="M4 6h16" />
                        <path d="M4 12h16" />
                        <path d="M4 18h16" />
                    </svg>

                </button>

            </div>

        </header>


        {{-- ========================================================= --}}
        {{-- OVERLAY MOBILE --}}
        {{-- ========================================================= --}}

        <div
            id="overlay-menu-ong"
            class="
                fixed inset-0 z-40
                hidden
                bg-black/45
                backdrop-blur-[2px]
                lg:hidden
            "
        ></div>


        {{-- ========================================================= --}}
        {{-- SIDEBAR --}}
        {{-- ========================================================= --}}

        <aside
            id="sidebar-ong"
            aria-label="Minha organização"
            @if($webNavigation) data-integrated-ong-sidebar @endif
            class="
                fixed left-0 top-0 z-50
                flex h-dvh w-[min(280px,90vw)]
                -translate-x-full
                flex-col
                bg-[#17392a]
                text-white
                shadow-2xl
                transition-transform
                duration-300
                ease-out

                lg:z-40
                lg:w-[230px]
                lg:translate-x-0
                lg:shadow-none
            "
        >

            {{-- ===================================================== --}}
            {{-- CABEÇALHO --}}
            {{-- ===================================================== --}}

            <div
                class="
                    border-b border-white/10
                    px-5 pb-5 pt-6
                "
            >

                <div class="flex items-start justify-between gap-4">

                    <div class="min-w-0">

                        {{-- LOGO --}}
                        <a
                            aria-label="Ajudae — início" data-brand-home href="{{ $home }}"
                            class="text-[28px] font-semibold tracking-tight"
                            style="font-family: 'Fraunces', serif;"
                        >
                            Ajud<span class="text-[#e3a62f]">ae</span>
                        </a>


                        <div class="mt-3">

                            <span
                                class="
                                    inline-flex items-center gap-1.5
                                    rounded-full
                                    border border-white/10
                                    bg-white/[0.08]
                                    px-3 py-1.5
                                    font-poppins
                                    text-[11px]
                                    font-medium
                                    text-white/80
                                "
                            >
                                <span class="text-[#e3a62f]">
                                    ✦
                                </span>

                                Minha organização
                            </span>

                        </div>

                    </div>


                    {{-- FECHAR MENU --}}
                    <button
                        type="button"
                        id="btn-fechar-menu-ong"
                        class="
                            flex h-9 w-9 shrink-0
                            items-center justify-center
                            rounded-xl
                            bg-white/[0.08]
                            transition
                            hover:bg-white/[0.14]
                            lg:hidden
                        "
                        aria-label="Fechar menu"
                    >

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-4 w-4"
                        >
                            <path d="M18 6 6 18" />
                            <path d="m6 6 12 12" />
                        </svg>

                    </button>

                </div>

            </div>


            {{-- ===================================================== --}}
            {{-- MENU --}}
            {{-- ===================================================== --}}

            <nav
                class="
                    sidebar-ong-scroll
                    flex-1
                    overflow-y-auto
                    px-3 py-5
                "
            >


                {{-- ================================================= --}}
                {{-- PRINCIPAL --}}
                {{-- ================================================= --}}

                <p
                    class="
                        mb-2 px-3
                        font-poppins
                        text-[10px]
                        font-semibold
                        uppercase
                        tracking-[0.18em]
                        {{ $webNavigation ? 'text-white/70' : 'text-white/35' }}
                    "
                >
                    Principal
                </p>


                @if(! $webNavigation || $webNavigation->institutionalPanel)
                {{-- DASHBOARD --}}
                <a
                    href="{{ route('ong.painel', $institutionalParams) }}" data-sidebar-item="dashboard" @if($ativo === 'dashboard') aria-current="page" @endif
                    class="
                        relative mb-1
                        flex items-center gap-3
                        rounded-xl
                        px-3 py-2.5
                        font-poppins text-sm
                        transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#e3a62f]

                        {{
                            $ativo === 'dashboard'
                                ? 'bg-white/10 font-semibold text-white'
                                : 'text-white/65 hover:bg-white/[0.06] hover:text-white'
                        }}
                    "
                >

                    @if($ativo === 'dashboard')

                        <span
                            class="
                                absolute left-0 top-1/2
                                h-6 w-[3px]
                                -translate-y-1/2
                                rounded-r-full
                                bg-[#e3a62f]
                            "
                        ></span>

                    @endif


                    <span
                        class="
                            flex h-8 w-8
                            shrink-0
                            items-center justify-center
                        "
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            class="h-[18px] w-[18px]"
                        >
                            <rect x="3" y="3" width="7" height="7" rx="1" />
                            <rect x="14" y="3" width="7" height="7" rx="1" />
                            <rect x="3" y="14" width="7" height="7" rx="1" />
                            <rect x="14" y="14" width="7" height="7" rx="1" />
                        </svg>
                    </span>

                    Dashboard

                </a>


                @endif

                {{-- EXPLORAR EVENTOS --}}
                <a
                    href="{{ route('feed', $institutionalParams) }}" data-sidebar-item="explorar" @if($ativo === 'explorar') aria-current="page" @endif
                    class="
                        relative mb-1
                        flex items-center gap-3
                        rounded-xl
                        px-3 py-2.5
                        font-poppins text-sm
                        transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#e3a62f]

                        {{
                            $ativo === 'explorar'
                                ? 'bg-white/10 font-semibold text-white'
                                : 'text-white/65 hover:bg-white/[0.06] hover:text-white'
                        }}
                    "
                >

                    @if($ativo === 'explorar')

                        <span
                            class="
                                absolute left-0 top-1/2
                                h-6 w-[3px]
                                -translate-y-1/2
                                rounded-r-full
                                bg-[#e3a62f]
                            "
                        ></span>

                    @endif


                    <span
                        class="
                            flex h-8 w-8
                            shrink-0
                            items-center justify-center
                        "
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            class="h-[18px] w-[18px]"
                        >
                            <circle cx="12" cy="12" r="9" /><path d="m16 8-2 6-6 2 2-6 6-2Z" />
                        </svg>
                    </span>

                    Explorar eventos

                </a>


                @if(! $webNavigation || $webNavigation->institutionalPanel)
                {{-- MEUS EVENTOS --}}
                <a
                    href="{{ route('ong.eventos.index', $institutionalParams) }}" data-sidebar-item="eventos" @if($ativo === 'eventos') aria-current="page" @endif
                    class="
                        relative mb-1
                        flex items-center
                        justify-between gap-3
                        rounded-xl
                        px-3 py-2.5
                        font-poppins text-sm
                        transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#e3a62f]

                        {{
                            $ativo === 'eventos'
                                ? 'bg-white/10 font-semibold text-white'
                                : 'text-white/65 hover:bg-white/[0.06] hover:text-white'
                        }}
                    "
                >

                    @if($ativo === 'eventos')

                        <span
                            class="
                                absolute left-0 top-1/2
                                h-6 w-[3px]
                                -translate-y-1/2
                                rounded-r-full
                                bg-[#e3a62f]
                            "
                        ></span>

                    @endif


                    <span class="flex min-w-0 items-center gap-3">

                        <span
                            class="
                                flex h-8 w-8
                                shrink-0
                                items-center justify-center
                            "
                        >

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                class="h-[18px] w-[18px]"
                            >
                                <rect
                                    x="3"
                                    y="4"
                                    width="18"
                                    height="17"
                                    rx="2"
                                />

                                <path d="M16 2v4M8 2v4M3 10h18" />
                            </svg>

                        </span>

                        <span class="truncate">
                            Meus eventos
                        </span>

                    </span>


                    @if(! $webNavigation && $eventosPendentes > 0)

                        <span
                            class="
                                flex h-5 min-w-5
                                shrink-0
                                items-center justify-center
                                rounded-full
                                bg-[#e3a62f]
                                px-1
                                text-[10px]
                                font-bold
                                text-[#17392a]
                            "
                        >
                            {{ $eventosPendentes }}
                        </span>

                    @endif

                </a>


                {{-- CANDIDATOS --}}
                <a
                    href="{{ route('ong.candidatos', $institutionalParams) }}" data-sidebar-item="candidatos" @if($ativo === 'candidatos') aria-current="page" @endif
                    class="
                        relative mb-1
                        flex items-center
                        justify-between gap-3
                        rounded-xl
                        px-3 py-2.5
                        font-poppins text-sm
                        transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#e3a62f]

                        {{
                            $ativo === 'candidatos'
                                ? 'bg-white/10 font-semibold text-white'
                                : 'text-white/65 hover:bg-white/[0.06] hover:text-white'
                        }}
                    "
                >

                    @if($ativo === 'candidatos')

                        <span
                            class="
                                absolute left-0 top-1/2
                                h-6 w-[3px]
                                -translate-y-1/2
                                rounded-r-full
                                bg-[#e3a62f]
                            "
                        ></span>

                    @endif


                    <span class="flex min-w-0 items-center gap-3">

                        <span
                            class="
                                flex h-8 w-8
                                shrink-0
                                items-center justify-center
                            "
                        >

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                class="h-[18px] w-[18px]"
                            >
                                <path
                                    d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"
                                />

                                <circle cx="9" cy="7" r="4" />

                                <path
                                    d="M22 21v-2a4 4 0 0 0-3-3.87"
                                />

                                <path
                                    d="M16 3.13a4 4 0 0 1 0 7.75"
                                />
                            </svg>

                        </span>

                        <span class="truncate">
                            Candidatos
                        </span>

                    </span>


                    @if(! $webNavigation && $candidatosPendentes > 0)

                        <span
                            class="
                                flex h-5 min-w-5
                                shrink-0
                                items-center justify-center
                                rounded-full
                                bg-[#e3a62f]
                                px-1
                                text-[10px]
                                font-bold
                                text-[#17392a]
                            "
                        >
                            {{ $candidatosPendentes }}
                        </span>

                    @endif

                </a>


                {{-- ================================================= --}}
                {{-- ORGANIZADORES --}}
                <a
                    href="{{ route('ong.organizadores', $institutionalParams) }}" data-sidebar-item="organizadores" @if($ativo === 'organizadores') aria-current="page" @endif
                    class="
                        relative mb-1
                        flex items-center
                        justify-between gap-3
                        rounded-xl
                        px-3 py-2.5
                        font-poppins text-sm
                        transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#e3a62f]

                        {{
                            $ativo === 'organizadores'
                                ? 'bg-white/10 font-semibold text-white'
                                : 'text-white/65 hover:bg-white/[0.06] hover:text-white'
                        }}
                    "
                >

                    @if($ativo === 'organizadores')

                        <span
                            class="
                                absolute left-0 top-1/2
                                h-6 w-[3px]
                                -translate-y-1/2
                                rounded-r-full
                                bg-[#e3a62f]
                            "
                        ></span>

                    @endif


                    <span class="flex min-w-0 items-center gap-3">

                        <span
                            class="
                                flex h-8 w-8
                                shrink-0
                                items-center justify-center
                            "
                        >

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                class="h-[18px] w-[18px]"
                            >
                                <path
                                    d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"
                                />

                                <circle cx="9" cy="7" r="4" />

                                <path
                                    d="M22 21v-2a4 4 0 0 0-3-3.87"
                                />

                                <path
                                    d="M16 3.13a4 4 0 0 1 0 7.75"
                                />
                            </svg>

                        </span>

                        <span class="truncate">
                            Organizadores
                        </span>

                    </span>




                </a>


                {{-- ================================================= --}}

                {{-- CRIAR EVENTO --}}
                {{-- ================================================= --}}

                <a
                    href="{{ route('ong.eventos.criar', $institutionalParams) }}" data-sidebar-item="criar-evento" @if($ativo === 'criar-evento') aria-current="page" @endif
                    class="
                        relative mb-1
                        flex items-center gap-3
                        rounded-xl
                        px-3 py-2.5
                        font-poppins text-sm
                        transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#e3a62f]

                        {{
                            $ativo === 'criar-evento'
                                ? 'bg-white/10 font-semibold text-white'
                                : 'text-white/65 hover:bg-white/[0.06] hover:text-white'
                        }}
                    "
                >

                    @if($ativo === 'criar-evento')

                        <span
                            class="
                                absolute left-0 top-1/2
                                h-6 w-[3px]
                                -translate-y-1/2
                                rounded-r-full
                                bg-[#e3a62f]
                            "
                        ></span>

                    @endif


                    <span
                        class="
                            flex h-8 w-8
                            shrink-0
                            items-center justify-center
                        "
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            class="h-[18px] w-[18px]"
                        >
                            <circle cx="12" cy="12" r="9" />
                            <path d="M12 8v8M8 12h8" />
                        </svg>
                    </span>

                    Criar evento

                </a>


                {{-- ================================================= --}}

                @endif

                {{-- CONTA --}}
                {{-- ================================================= --}}

                <p
                    class="
                        mb-2 mt-7 px-3
                        font-poppins
                        text-[10px]
                        font-semibold
                        uppercase
                        tracking-[0.18em]
                        {{ $webNavigation ? 'text-white/70' : 'text-white/35' }}
                    "
                >
                    Conta
                </p>


                {{-- PERFIL ONG --}}
                <a
                    href="{{ route('ong.perfil', $profileParams) }}" data-sidebar-item="perfil" @if($ativo === 'perfil') aria-current="page" @endif
                    class="
                        relative mb-1
                        flex items-center gap-3
                        rounded-xl
                        px-3 py-2.5
                        font-poppins text-sm
                        transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#e3a62f]

                        {{
                            $ativo === 'perfil'
                                ? 'bg-white/10 font-semibold text-white'
                                : 'text-white/65 hover:bg-white/[0.06] hover:text-white'
                        }}
                    "
                >

                    @if($ativo === 'perfil')

                        <span
                            class="
                                absolute left-0 top-1/2
                                h-6 w-[3px]
                                -translate-y-1/2
                                rounded-r-full
                                bg-[#e3a62f]
                            "
                        ></span>

                    @endif


                    <span
                        class="
                            flex h-8 w-8
                            shrink-0
                            items-center justify-center
                        "
                    >

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            class="h-[18px] w-[18px]"
                        >
                            <circle cx="12" cy="8" r="4" />

                            <path
                                d="M4 21a8 8 0 0 1 16 0"
                            />
                        </svg>

                    </span>

                    Minha organização

                </a>



                {{-- NOTIFICAÇÕES --}}
                <a
                    @unless($webNavigation) data-notification-entry @endunless href="{{ route('ong.notificacoes', $institutionalParams) }}" data-sidebar-item="notificacoes" @if($ativo === 'notificacoes') aria-current="page" @endif
                    class="
                        relative mb-1
                        flex items-center
                        justify-between gap-3
                        rounded-xl
                        px-3 py-2.5
                        font-poppins text-sm
                        transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#e3a62f]

                        {{
                            $ativo === 'notificacoes'
                                ? 'bg-white/10 font-semibold text-white'
                                : 'text-white/65 hover:bg-white/[0.06] hover:text-white'
                        }}
                    "
                >

                    @if($ativo === 'notificacoes')

                        <span
                            class="
                                absolute left-0 top-1/2
                                h-6 w-[3px]
                                -translate-y-1/2
                                rounded-r-full
                                bg-[#e3a62f]
                            "
                        ></span>

                    @endif


                    <span class="flex min-w-0 items-center gap-3">

                        <span
                            class="
                                flex h-8 w-8
                                shrink-0
                                items-center justify-center
                            "
                        >

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                class="h-[18px] w-[18px]"
                            >
                                <path
                                    d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"
                                />

                                <path
                                    d="M13.73 21a2 2 0 0 1-3.46 0"
                                />
                            </svg>

                        </span>

                        <span class="truncate">
                            Notificações
                        </span>

                    </span>


                    @if(false)

                        <span
                            class="
                                flex h-5 min-w-5
                                shrink-0
                                items-center justify-center
                                rounded-full
                                bg-[#e3a62f]
                                px-1
                                text-[10px]
                                font-bold
                                text-[#17392a]
                            "
                        >
                            {{ $notificacoes }}
                        </span>

                    @endif

                </a>




                {{-- CONFIGURAÇÕES --}}
                <a
                    href="{{ route($settings, $institutionalParams) }}" data-sidebar-item="configuracoes" @if($ativo === 'configuracoes') aria-current="page" @endif
                    class="
                        relative mb-1
                        flex items-center gap-3
                        rounded-xl
                        px-3 py-2.5
                        font-poppins text-sm
                        transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#e3a62f]

                        {{
                            $ativo === 'configuracoes'
                                ? 'bg-white/10 font-semibold text-white'
                                : 'text-white/65 hover:bg-white/[0.06] hover:text-white'
                        }}
                    "
                >

                    @if($ativo === 'configuracoes')

                        <span
                            class="
                                absolute left-0 top-1/2
                                h-6 w-[3px]
                                -translate-y-1/2
                                rounded-r-full
                                bg-[#e3a62f]
                            "
                        ></span>

                    @endif


                    <span
                        class="
                            flex h-8 w-8
                            shrink-0
                            items-center justify-center
                        "
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            class="h-[18px] w-[18px]"
                        >
                            <circle cx="12" cy="12" r="3" />

                            <path
                                d="
                                    M19.4 15
                                    a1.7 1.7 0 0 0 .34 1.87
                                    l.06.06
                                    a2 2 0 1 1 -2.83 2.83
                                    l-.06-.06
                                    a1.7 1.7 0 0 0 -1.87-.34
                                    1.7 1.7 0 0 0 -1 1.55
                                    V21
                                    a2 2 0 1 1 -4 0
                                    v-.09
                                    a1.7 1.7 0 0 0 -1-1.55
                                    1.7 1.7 0 0 0 -1.87.34
                                    l-.06.06
                                    a2 2 0 1 1 -2.83-2.83
                                    l.06-.06
                                    a1.7 1.7 0 0 0 .34-1.87
                                    1.7 1.7 0 0 0 -1.55-1
                                    H3
                                    a2 2 0 1 1 0-4
                                    h.09
                                    a1.7 1.7 0 0 0 1.55-1
                                    1.7 1.7 0 0 0 -.34-1.87
                                    l-.06-.06
                                    a2 2 0 1 1 2.83-2.83
                                    l.06.06
                                    a1.7 1.7 0 0 0 1.87.34
                                    H9
                                    a1.7 1.7 0 0 0 1-1.55
                                    V3
                                    a2 2 0 1 1 4 0
                                    v.09
                                    a1.7 1.7 0 0 0 1 1.55
                                    1.7 1.7 0 0 0 1.87-.34
                                    l.06-.06
                                    a2 2 0 1 1 2.83 2.83
                                    l-.06.06
                                    a1.7 1.7 0 0 0 -.34 1.87
                                    V9
                                    a1.7 1.7 0 0 0 1.55 1
                                    H21
                                    a2 2 0 1 1 0 4
                                    h-.09
                                    a1.7 1.7 0 0 0 -1.55 1
                                    z
                                "
                            />
                        </svg>
                    </span>

                    Configurações

                </a>

                @if($webNavigation)
                    <a href="{{ route('ong.privacidade') }}" @class(['ong-sidebar-link','is-active' => $ativo === 'privacidade']) @if($ativo === 'privacidade') aria-current="page" @endif>Privacidade e dados</a>
                    <form method="POST" action="{{ route('logout') }}" class="mt-3 px-3">
                        @csrf
                        <x-botao-dourado as="button" type="submit" size="sm">Sair</x-botao-dourado>
                    </form>
                @endif
            </nav>


            {{-- ===================================================== --}}
            {{-- USUÁRIO / ONG --}}
            {{-- ===================================================== --}}

            <div
                class="
                    border-t border-white/10
                    bg-black/[0.05]
                    p-4
                "
            >

                <a
                    href="{{ route('ong.perfil', $profileParams) }}"
                    class="
                        flex min-w-0
                        items-center gap-3
                        rounded-xl
                        p-2
                        transition
                        hover:bg-white/[0.06]
                    "
                >

                    <div
                        class="
                            flex h-10 w-10
                            shrink-0
                            items-center justify-center
                            rounded-full
                            border border-white/10
                            bg-white/10
                            font-poppins
                            text-xs
                            font-semibold
                        "
                    >
                        {{ $iniciais }}
                    </div>


                    <div class="min-w-0 flex-1">

                        <p
                            class="
                                truncate
                                font-poppins
                                text-sm
                                font-semibold
                            "
                        >
                            {{ $nome }}
                        </p>


                        <p
                            class="
                                mt-0.5 truncate
                                font-poppins
                                text-[11px]
                                {{ $webNavigation ? 'text-white/70' : 'text-white/45' }}
                            "
                        >
                            {{ $ong }}
                        </p>

                    </div>

                </a>

            </div>

        </aside>


        {{-- ========================================================= --}}
        {{-- JAVASCRIPT MOBILE --}}
        {{-- ========================================================= --}}

        {{-- Menu keyboard behavior is shared in navigation-accessibility.js. --}}

    </div>
