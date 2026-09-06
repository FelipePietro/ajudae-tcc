<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Entrar | Ajudaê</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#f8f6f1] font-outfit text-[#183c2b]">

    <div class="min-h-screen flex flex-col">

        {{-- HEADER --}}
        <header class="h-[60px] border-b border-[#e4ded2] flex items-center px-6 sm:px-8">

            <a href="{{ route('inicio') }}"
               class="font-fraunces text-[22px] font-bold no-underline">
                <span class="text-[#183c2b]">Ajud</span><span class="text-[#e8920a]">aê</span>
            </a>

        </header>


        {{-- CONTEÚDO --}}
        <main
            class="relative flex-1 flex items-center justify-center px-4 py-10 overflow-hidden">

            {{-- EFEITO DE FUNDO --}}
            <div
                class="pointer-events-none absolute bottom-[-180px] left-1/2
                       h-[520px] w-[760px] -translate-x-1/2
                       rounded-full bg-[#dcefe4]/70 blur-[110px]">
            </div>


            {{-- CARD --}}
            <section
                class="relative z-10 w-full max-w-[420px]
                       overflow-hidden rounded-[24px]
                       border border-[#e4ded2]
                       bg-white shadow-[0_18px_45px_rgba(24,60,43,0.12)]">

                {{-- CABEÇALHO DO CARD --}}
                <div class="bg-[#173f2d] px-8 py-9 text-white">

                    <div class="font-fraunces text-[23px] font-bold">
                        <span>Ajud</span><span class="text-[#e8920a]">aê</span>
                    </div>

                    <h1
                        class="mt-4 font-fraunces text-[20px] italic font-semibold">
                        Boas-vindas de volta
                    </h1>

                </div>


                {{-- FORMULÁRIO --}}
                <div class="px-8 py-7">

                    <form id="loginForm" method="POST" action="#">

                        @csrf

                        {{-- TIPO DE LOGIN --}}
                        <div
                            class="relative mb-7 flex rounded-full
                                   border border-[#ddd6c9]
                                   bg-[#f8f6f1] p-1">

                            <button
                                type="button"
                                id="btnPessoa"
                                class="login-tab login-tab-active flex-1 rounded-full
                                       px-3 py-2 text-[13px] font-medium
                                       transition-all duration-200">
                                Voluntário / Organizador
                            </button>

                            <button
                                type="button"
                                id="btnOng"
                                class="login-tab flex-1 rounded-full
                                       px-3 py-2 text-[13px] font-medium
                                       text-[#756f65]
                                       transition-all duration-200">
                                ONG
                            </button>

                        </div>


                        <input
                            type="hidden"
                            name="tipo_login"
                            id="tipoLogin"
                            value="pessoa">


                        {{-- E-MAIL --}}
                        <div class="mb-5">

                            <label
                                for="email"
                                class="mb-2 block text-[14px] font-medium text-[#33382f]">
                                E-mail
                            </label>

                            <input
                                id="email"
                                name="email"
                                type="email"
                                autocomplete="email"
                                placeholder="seuemail@exemplo.com"
                                required
                                class="w-full rounded-[9px]
                                       border border-[#ddd6c9]
                                       bg-white px-4 py-3
                                       text-[14px] text-[#333]
                                       outline-none transition
                                       placeholder:text-[#9a968f]
                                       focus:border-[#17442f]
                                       focus:ring-2
                                       focus:ring-[#17442f]/10">

                        </div>


                        {{-- SENHA --}}
                        <div>

                            <label
                                for="senha"
                                class="mb-2 block text-[14px] font-medium text-[#33382f]">
                                Senha
                            </label>

                            <div class="relative">

                                <input
                                    id="senha"
                                    name="senha"
                                    type="password"
                                    autocomplete="current-password"
                                    placeholder="••••••••"
                                    required
                                    class="w-full rounded-[9px]
                                           border border-[#ddd6c9]
                                           bg-white px-4 py-3 pr-12
                                           text-[14px] text-[#333]
                                           outline-none transition
                                           placeholder:text-[#9a968f]
                                           focus:border-[#17442f]
                                           focus:ring-2
                                           focus:ring-[#17442f]/10">

                                <button
                                    type="button"
                                    id="toggleSenha"
                                    aria-label="Mostrar senha"
                                    class="absolute right-4 top-1/2
                                           -translate-y-1/2
                                           text-[#8b867c]
                                           hover:text-[#17442f]">

                                    <svg
                                        id="iconeOlho"
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="18"
                                        height="18"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round">

                                        <path d="M2.062 12.348a1 1 0 0 1 0-.696
                                                 10.75 10.75 0 0 1
                                                 19.876 0 1 1 0 0 1 0 .696
                                                 10.75 10.75 0 0 1
                                                 -19.876 0" />

                                        <circle cx="12" cy="12" r="3" />

                                    </svg>

                                </button>

                            </div>


                            <div class="mt-2 text-right">

                                <a
                                    href="#"
                                    class="text-[12px] text-[#797468]
                                           no-underline hover:text-[#17442f]">
                                    Esqueceu a senha?
                                </a>

                            </div>

                        </div>


                        {{-- BOTÃO LOGIN --}}
                        <button
                            type="submit"
                            class="mt-7 w-full rounded-full
                                   bg-[#17442f] px-5 py-3.5
                                   text-[14px] font-semibold text-white
                                   transition
                                   hover:bg-[#103624]
                                   focus:outline-none
                                   focus:ring-4
                                   focus:ring-[#17442f]/20">

                            Entrar na plataforma

                        </button>


                        {{-- DIVISOR --}}
                        <div class="my-6 flex items-center gap-4">

                            <div class="h-px flex-1 bg-[#e8e2d8]"></div>

                            <span class="text-[12px] text-[#8b867c]">
                                ou
                            </span>

                            <div class="h-px flex-1 bg-[#e8e2d8]"></div>

                        </div>


                        {{-- CADASTRO --}}
                        <p
                            class="text-center text-[13px]
                                   leading-5 text-[#4c4a44]">

                            Não tem conta?

                            <a
                                href="#"
                                class="font-medium text-[#17442f]
                                       no-underline hover:underline">
                                Cadastre-se como voluntário
                            </a>

                            <span class="text-[#8b867c]">•</span>

                            <a
                                href="{{ route('ong.cadastrar.form') }}"
                                class="font-medium text-[#17442f]
                                       no-underline hover:underline">
                                Cadastrar ONG
                            </a>

                        </p>

                    </form>

                </div>

            </section>

        </main>

    </div>


    <script>
        const btnPessoa = document.getElementById('btnPessoa');
        const btnOng = document.getElementById('btnOng');
        const tipoLogin = document.getElementById('tipoLogin');

        const loginForm = document.getElementById('loginForm');

        const senha = document.getElementById('senha');
        const toggleSenha = document.getElementById('toggleSenha');


        function selecionarTipo(tipo) {

            tipoLogin.value = tipo;

            btnPessoa.classList.remove('login-tab-active');
            btnOng.classList.remove('login-tab-active');

            btnPessoa.classList.add('text-[#756f65]');
            btnOng.classList.add('text-[#756f65]');


            if (tipo === 'pessoa') {

                btnPessoa.classList.add('login-tab-active');
                btnPessoa.classList.remove('text-[#756f65]');

            } else {

                btnOng.classList.add('login-tab-active');
                btnOng.classList.remove('text-[#756f65]');

            }
        }


        btnPessoa.addEventListener('click', () => {
            selecionarTipo('pessoa');
        });


        btnOng.addEventListener('click', () => {
            selecionarTipo('ong');
        });


        toggleSenha.addEventListener('click', () => {

            const mostrando =
                senha.type === 'text';

            senha.type =
                mostrando
                    ? 'password'
                    : 'text';

            toggleSenha.setAttribute(
                'aria-label',
                mostrando
                    ? 'Mostrar senha'
                    : 'Ocultar senha'
            );

        });


        /*
        |--------------------------------------------------------------------------
        | MOCK DE LOGIN
        |--------------------------------------------------------------------------
        |
        | Quando conectarmos a API, removemos este listener e o formulário
        | passa a enviar as credenciais para o endpoint correto.
        |
        */

        loginForm.addEventListener('submit', function (event) {

            event.preventDefault();

            if (tipoLogin.value === 'ong') {

                window.location.href =
                    "{{ route('ong.painel') }}";

                return;
            }

            window.location.href =
                "{{ route('feed') }}";

        });
    </script>

</body>

</html>