@props([
    'ativo' => null,
    'nome' => 'Carlos Ferreira',
    'ong' => 'Instituto Esperança',
    'iniciais' => 'CF',
    'eventosPendentes' => 3,
    'candidatosPendentes' => 12,
    'notificacoes' => 4,
])

<div class="relative">

    {{-- ============================================================= --}}
    {{-- NAVBAR MOBILE --}}
    {{-- ============================================================= --}}

    <header
        class="sticky top-0 z-40
               flex h-16 w-full
               items-center justify-between
               border-b border-white/10
               bg-[#17392a]
               px-4 text-white
               shadow-sm
               lg:hidden"
    >

        {{-- LOGO --}}
        <a
            href="{{ route('ong.dashboard') }}"
            class="font-['Fraunces'] text-2xl font-semibold tracking-tight"
            style="font-family: 'Fraunces', serif;"
        >
            Ajud<span class="text-[#e3a62f]">ae</span>
        </a>


        <div class="flex items-center gap-2">

            {{-- NOTIFICAÇÕES --}}
            <a
                href="{{ route('notificacoes') }}"
                class="relative flex h-10 w-10
                       items-center justify-center
                       rounded-full
                       bg-white/10
                       transition
                       hover:bg-white/15"
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


                @if($notificacoes > 0)

                    <span
                        class="absolute -right-1 -top-1
                               flex h-5 min-w-5
                               items-center justify-center
                               rounded-full
                               bg-[#e3a62f]
                               px-1
                               text-[10px]
                               font-bold
                               text-[#17392a]"
                    >
                        {{ $notificacoes }}
                    </span>

                @endif

            </a>


            {{-- BOTÃO ABRIR --}}
            <button
                type="button"
                id="btn-abrir-menu-ong"
                class="flex h-10 w-10
                       items-center justify-center
                       rounded-xl
                       bg-white/10
                       transition
                       hover:bg-white/15"
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


    {{-- ============================================================= --}}
    {{-- OVERLAY MOBILE --}}
    {{-- ============================================================= --}}

    <div
        id="overlay-menu-ong"
        class="fixed inset-0 z-40
               hidden
               bg-black/45
               backdrop-blur-[2px]
               lg:hidden"
    ></div>


    {{-- ============================================================= --}}
    {{-- SIDEBAR --}}
    {{-- ============================================================= --}}

    <aside
        id="sidebar-ong"
        class="fixed left-0 top-0 z-50
               flex h-screen w-[280px]
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
               lg:shadow-none"
    >

        {{-- ========================================================= --}}
        {{-- CABEÇALHO --}}
        {{-- ========================================================= --}}

        <div
            class="border-b border-white/10
                   px-5 pb-5 pt-6"
        >

            <div
                class="flex items-start justify-between gap-4"
            >

                <div class="min-w-0">

                    {{-- LOGO --}}
                    <a
                        href="{{ route('ong.dashboard') }}"
                        class="text-[28px] font-semibold tracking-tight"
                        style="font-family: 'Fraunces', serif;"
                    >
                        Ajud<span class="text-[#e3a62f]">ae</span>
                    </a>


                    <div class="mt-3">

                        <span
                            class="inline-flex items-center gap-1.5
                                   rounded-full
                                   border border-white/10
                                   bg-white/[0.08]
                                   px-3 py-1.5
                                   font-poppins
                                   text-[11px]
                                   font-medium
                                   text-white/80"
                        >
                            <span class="text-[#e3a62f]">
                                ✦
                            </span>

                            Organizador
                        </span>

                    </div>

                </div>


                {{-- BOTÃO FECHAR --}}
                <button
                    type="button"
                    id="btn-fechar-menu-ong"
                    class="flex h-9 w-9 shrink-0
                           items-center justify-center
                           rounded-xl
                           bg-white/[0.08]
                           transition
                           hover:bg-white/[0.14]
                           lg:hidden"
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


        {{-- ========================================================= --}}
        {{-- MENU --}}
        {{-- ========================================================= --}}

        <nav
            class="sidebar-ong-scroll
                   flex-1
                   overflow-y-auto
                   px-3 py-5"
        >

            {{-- PRINCIPAL --}}
            <p
                class="mb-2 px-3
                       font-poppins
                       text-[10px]
                       font-semibold
                       uppercase
                       tracking-[0.18em]
                       text-white/35"
            >
                Principal
            </p>


            {{-- DASHBOARD --}}
            <a
                href="{{ route('ong.dashboard') }}"
                class="relative mb-1
                       flex items-center gap-3
                       rounded-xl
                       px-3 py-2.5
                       font-poppins text-sm
                       transition
                       {{ $ativo === 'dashboard'
                            ? 'bg-white/10 font-semibold text-white'
                            : 'text-white/65 hover:bg-white/[0.06] hover:text-white' }}"
            >

                @if($ativo === 'dashboard')

                    <span
                        class="absolute left-0 top-1/2
                               h-6 w-[3px]
                               -translate-y-1/2
                               rounded-r-full
                               bg-[#e3a62f]"
                    ></span>

                @endif


                <span
                    class="flex h-8 w-8
                           shrink-0
                           items-center justify-center"
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


            {{-- MEUS EVENTOS --}}
            <a
                href="{{ route('ong.eventos.index') }}"
                class="relative mb-1
                       flex items-center
                       justify-between gap-3
                       rounded-xl
                       px-3 py-2.5
                       font-poppins text-sm
                       transition
                       {{ $ativo === 'eventos'
                            ? 'bg-white/10 font-semibold text-white'
                            : 'text-white/65 hover:bg-white/[0.06] hover:text-white' }}"
            >

                @if($ativo === 'eventos')

                    <span
                        class="absolute left-0 top-1/2
                               h-6 w-[3px]
                               -translate-y-1/2
                               rounded-r-full
                               bg-[#e3a62f]"
                    ></span>

                @endif


                <span
                    class="flex min-w-0 items-center gap-3"
                >

                    <span
                        class="flex h-8 w-8
                               shrink-0
                               items-center justify-center"
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


                @if($eventosPendentes > 0)

                    <span
                        class="flex h-5 min-w-5
                               shrink-0
                               items-center justify-center
                               rounded-full
                               bg-[#e3a62f]
                               px-1
                               text-[10px]
                               font-bold
                               text-[#17392a]"
                    >
                        {{ $eventosPendentes }}
                    </span>

                @endif

            </a>


            {{-- CANDIDATOS --}}
            <a
                href="{{ route('ong.candidatos') }}"
                class="relative mb-1
                       flex items-center
                       justify-between gap-3
                       rounded-xl
                       px-3 py-2.5
                       font-poppins text-sm
                       transition
                       {{ $ativo === 'candidatos'
                            ? 'bg-white/10 font-semibold text-white'
                            : 'text-white/65 hover:bg-white/[0.06] hover:text-white' }}"
            >

                @if($ativo === 'candidatos')

                    <span
                        class="absolute left-0 top-1/2
                               h-6 w-[3px]
                               -translate-y-1/2
                               rounded-r-full
                               bg-[#e3a62f]"
                    ></span>

                @endif


                <span
                    class="flex min-w-0 items-center gap-3"
                >

                    <span
                        class="flex h-8 w-8
                               shrink-0
                               items-center justify-center"
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

                            <circle
                                cx="9"
                                cy="7"
                                r="4"
                            />

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


                @if($candidatosPendentes > 0)

                    <span
                        class="flex h-5 min-w-5
                               shrink-0
                               items-center justify-center
                               rounded-full
                               bg-[#e3a62f]
                               px-1
                               text-[10px]
                               font-bold
                               text-[#17392a]"
                    >
                        {{ $candidatosPendentes }}
                    </span>

                @endif

            </a>


            {{-- ONG --}}
            <p
                class="mb-2 mt-7 px-3
                       font-poppins
                       text-[10px]
                       font-semibold
                       uppercase
                       tracking-[0.18em]
                       text-white/35"
            >
                ONG
            </p>


            {{-- PAINEL ONG --}}
            <a
                href="{{ route('ong.painel') }}"
                class="relative mb-1
                       flex items-center gap-3
                       rounded-xl
                       px-3 py-2.5
                       font-poppins text-sm
                       transition
                       {{ $ativo === 'ong'
                            ? 'bg-white/10 font-semibold text-white'
                            : 'text-white/65 hover:bg-white/[0.06] hover:text-white' }}"
            >

                @if($ativo === 'ong')

                    <span
                        class="absolute left-0 top-1/2
                               h-6 w-[3px]
                               -translate-y-1/2
                               rounded-r-full
                               bg-[#e3a62f]"
                    ></span>

                @endif


                <span
                    class="flex h-8 w-8
                           shrink-0
                           items-center justify-center"
                >

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        class="h-[18px] w-[18px]"
                    >
                        <path d="M3 21h18" />
                        <path d="M6 21V7l6-4 6 4v14" />
                        <path d="M9 21v-6h6v6" />
                    </svg>

                </span>


                Painel da ONG

            </a>


            {{-- CRIAR EVENTO --}}
            <a
                href="{{ route('ong.eventos.criar') }}"
                class="relative mb-1
                       flex items-center gap-3
                       rounded-xl
                       px-3 py-2.5
                       font-poppins text-sm
                       transition
                       {{ $ativo === 'criar-evento'
                            ? 'bg-white/10 font-semibold text-white'
                            : 'text-white/65 hover:bg-white/[0.06] hover:text-white' }}"
            >

                @if($ativo === 'criar-evento')

                    <span
                        class="absolute left-0 top-1/2
                               h-6 w-[3px]
                               -translate-y-1/2
                               rounded-r-full
                               bg-[#e3a62f]"
                    ></span>

                @endif


                <span
                    class="flex h-8 w-8
                           shrink-0
                           items-center justify-center"
                >

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        class="h-[18px] w-[18px]"
                    >
                        <circle
                            cx="12"
                            cy="12"
                            r="9"
                        />

                        <path d="M12 8v8M8 12h8" />
                    </svg>

                </span>


                Criar evento

            </a>


            {{-- CONTA --}}
            <p
                class="mb-2 mt-7 px-3
                       font-poppins
                       text-[10px]
                       font-semibold
                       uppercase
                       tracking-[0.18em]
                       text-white/35"
            >
                Conta
            </p>


            {{-- PERFIL --}}
            <a
                href="{{ route('perfil') }}"
                class="mb-1 flex
                       items-center gap-3
                       rounded-xl
                       px-3 py-2.5
                       font-poppins text-sm
                       text-white/65
                       transition
                       hover:bg-white/[0.06]
                       hover:text-white"
            >

                <span
                    class="flex h-8 w-8
                           shrink-0
                           items-center justify-center"
                >

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        class="h-[18px] w-[18px]"
                    >
                        <circle
                            cx="12"
                            cy="8"
                            r="4"
                        />

                        <path
                            d="M4 21a8 8 0 0 1 16 0"
                        />
                    </svg>

                </span>


                Meu perfil

            </a>


            {{-- NOTIFICAÇÕES --}}
            <a
                href="{{ route('notificacoes') }}"
                class="mb-1 flex
                       items-center
                       justify-between gap-3
                       rounded-xl
                       px-3 py-2.5
                       font-poppins text-sm
                       text-white/65
                       transition
                       hover:bg-white/[0.06]
                       hover:text-white"
            >

                <span
                    class="flex min-w-0 items-center gap-3"
                >

                    <span
                        class="flex h-8 w-8
                               shrink-0
                               items-center justify-center"
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


                    Notificações

                </span>


                @if($notificacoes > 0)

                    <span
                        class="flex h-5 min-w-5
                               shrink-0
                               items-center justify-center
                               rounded-full
                               bg-[#e3a62f]
                               px-1
                               text-[10px]
                               font-bold
                               text-[#17392a]"
                    >
                        {{ $notificacoes }}
                    </span>

                @endif

            </a>


            {{-- CONFIGURAÇÕES --}}
            <a
                href="{{ route('configuracoes') }}"
                class="mb-1 flex
                       items-center gap-3
                       rounded-xl
                       px-3 py-2.5
                       font-poppins text-sm
                       text-white/65
                       transition
                       hover:bg-white/[0.06]
                       hover:text-white"
            >

                <span
                    class="flex h-8 w-8
                           shrink-0
                           items-center justify-center"
                >
                    ⚙
                </span>


                Configurações

            </a>

        </nav>


        {{-- ========================================================= --}}
        {{-- USUÁRIO --}}
        {{-- ========================================================= --}}

        <div
            class="border-t border-white/10
                   bg-black/[0.05]
                   p-4"
        >

            <a
                href="{{ route('perfil') }}"
                class="flex min-w-0
                       items-center gap-3
                       rounded-xl
                       p-2
                       transition
                       hover:bg-white/[0.06]"
            >

                <div
                    class="flex h-10 w-10
                           shrink-0
                           items-center justify-center
                           rounded-full
                           border border-white/10
                           bg-white/10
                           font-poppins
                           text-xs
                           font-semibold"
                >
                    {{ $iniciais }}
                </div>


                <div class="min-w-0 flex-1">

                    <p
                        class="truncate
                               font-poppins
                               text-sm
                               font-semibold"
                    >
                        {{ $nome }}
                    </p>

                    <p
                        class="mt-0.5 truncate
                               font-poppins
                               text-[11px]
                               text-white/45"
                    >
                        {{ $ong }}
                    </p>

                </div>

            </a>

        </div>

    </aside>


    {{-- ============================================================= --}}
    {{-- JAVASCRIPT --}}
    {{-- ============================================================= --}}

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const sidebar =
                document.getElementById('sidebar-ong');

            const overlay =
                document.getElementById('overlay-menu-ong');

            const btnAbrir =
                document.getElementById('btn-abrir-menu-ong');

            const btnFechar =
                document.getElementById('btn-fechar-menu-ong');


            if (
                !sidebar ||
                !overlay ||
                !btnAbrir ||
                !btnFechar
            ) {
                return;
            }


            function abrirMenu() {

                sidebar.classList.remove(
                    '-translate-x-full'
                );

                sidebar.classList.add(
                    'translate-x-0'
                );

                overlay.classList.remove(
                    'hidden'
                );

                btnAbrir.setAttribute(
                    'aria-expanded',
                    'true'
                );

                document.body.classList.add(
                    'overflow-hidden'
                );

            }


            function fecharMenu() {

                sidebar.classList.remove(
                    'translate-x-0'
                );

                sidebar.classList.add(
                    '-translate-x-full'
                );

                overlay.classList.add(
                    'hidden'
                );

                btnAbrir.setAttribute(
                    'aria-expanded',
                    'false'
                );

                document.body.classList.remove(
                    'overflow-hidden'
                );

            }


            btnAbrir.addEventListener(
                'click',
                abrirMenu
            );


            btnFechar.addEventListener(
                'click',
                fecharMenu
            );


            overlay.addEventListener(
                'click',
                fecharMenu
            );


            document.addEventListener(
                'keydown',
                function (event) {

                    if (event.key === 'Escape') {
                        fecharMenu();
                    }

                }
            );


            window.addEventListener(
                'resize',
                function () {

                    if (window.innerWidth >= 1024) {

                        overlay.classList.add(
                            'hidden'
                        );

                        document.body.classList.remove(
                            'overflow-hidden'
                        );

                        sidebar.classList.remove(
                            'translate-x-0'
                        );

                        sidebar.classList.add(
                            '-translate-x-full'
                        );

                        btnAbrir.setAttribute(
                            'aria-expanded',
                            'false'
                        );

                    }

                }
            );

        });
    </script>

</div>