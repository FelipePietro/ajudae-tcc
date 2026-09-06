<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cadastro em análise | Ajudaê</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#f7f5f0] font-outfit text-[#183c2b]">

    <div class="min-h-screen flex flex-col">

        <header class="h-[64px] border-b border-[#e3ded3] flex items-center px-6 sm:px-10">

            <a
                href="{{ route('inicio') }}"
                class="font-fraunces text-[22px] font-bold no-underline">

                <span class="text-[#183c2b]">Ajud</span><span class="text-[#e8920a]">aê</span>

            </a>

        </header>


        <main class="relative flex-1 flex items-center justify-center px-4 py-12 overflow-hidden">

            <div
                class="absolute bottom-[-220px] left-1/2
                       h-[520px] w-[800px]
                       -translate-x-1/2 rounded-full
                       bg-[#dcefe4]/70 blur-[120px]
                       pointer-events-none">
            </div>


            <section
                class="relative z-10 w-full max-w-[560px]
                       rounded-[24px] border border-[#e2ddd2]
                       bg-white px-6 py-8 sm:px-10 sm:py-10
                       shadow-[0_18px_45px_rgba(24,60,43,0.10)]">

                <div class="text-center">

                    <div
                        class="mx-auto mb-6 flex h-[72px] w-[72px]
                               items-center justify-center rounded-full
                               bg-[#fff3d9] text-[34px]">
                        ⏳
                    </div>


                    <span
                        class="inline-flex rounded-full
                               bg-[#fff3d9]
                               px-3 py-1
                               text-[12px] font-semibold
                               text-[#a66a0c]">

                        Aguardando aprovação

                    </span>


                    <h1
                        class="mt-5 font-fraunces
                               text-[28px] font-bold
                               text-[#183c2b]">

                        Seu cadastro está em análise

                    </h1>


                    <p
                        class="mx-auto mt-3 max-w-[430px]
                               text-[14px] leading-6
                               text-[#69655d]">

                        Recebemos o cadastro da
                        <strong class="text-[#183c2b]">
                            {{ $ong['nome_fantasia'] }}
                        </strong>.

                        Agora nossa equipe administrativa irá analisar
                        as informações enviadas antes de liberar o acesso
                        completo à plataforma.

                    </p>

                </div>


                <div
                    class="mt-8 rounded-[16px]
                           border border-[#e7e1d5]
                           bg-[#faf9f6]
                           p-5">

                    <div
                        class="flex items-start gap-4">

                        <div
                            class="flex h-10 w-10 flex-shrink-0
                                   items-center justify-center
                                   rounded-full bg-[#e8f2ec]">

                            ✓

                        </div>

                        <div>

                            <p class="text-[14px] font-semibold">
                                Cadastro enviado com sucesso
                            </p>

                            <p class="mt-1 text-[13px] leading-5 text-[#777168]">
                                Seus dados foram recebidos e estão aguardando
                                a avaliação de um administrador.
                            </p>

                        </div>

                    </div>


                    <div class="my-4 h-px bg-[#e7e1d5]"></div>


                    <div
                        class="flex items-start gap-4">

                        <div
                            class="flex h-10 w-10 flex-shrink-0
                                   items-center justify-center
                                   rounded-full bg-[#fff3d9]">

                            ⏳

                        </div>

                        <div>

                            <p class="text-[14px] font-semibold">
                                Análise administrativa
                            </p>

                            <p class="mt-1 text-[13px] leading-5 text-[#777168]">
                                A equipe irá verificar os dados da organização
                                e a documentação enviada.
                            </p>

                        </div>

                    </div>


                    <div class="my-4 h-px bg-[#e7e1d5]"></div>


                    <div
                        class="flex items-start gap-4 opacity-60">

                        <div
                            class="flex h-10 w-10 flex-shrink-0
                                   items-center justify-center
                                   rounded-full bg-[#eceae5]">

                            🔒

                        </div>

                        <div>

                            <p class="text-[14px] font-semibold">
                                Acesso liberado
                            </p>

                            <p class="mt-1 text-[13px] leading-5 text-[#777168]">
                                Após a aprovação, sua ONG poderá criar eventos,
                                administrar candidatos e acessar o painel.
                            </p>

                        </div>

                    </div>

                </div>


                <div
                    class="mt-6 rounded-[14px]
                           border border-[#dce9e1]
                           bg-[#f0f7f3]
                           p-4">

                    <p class="text-[13px] leading-5 text-[#456653]">

                        <strong>Você não precisa enviar o cadastro novamente.</strong>

                        Quando a análise for concluída, o resultado poderá ser
                        informado no e-mail

                        <strong>
                            {{ $ong['email'] }}
                        </strong>.

                    </p>

                </div>


                <div class="mt-7 flex flex-col gap-3 sm:flex-row">

                    <a
                        href="{{ route('inicio') }}"
                        class="flex-1 rounded-full
                               border border-[#17442f]
                               px-5 py-3
                               text-center text-[14px]
                               font-semibold text-[#17442f]
                               no-underline
                               transition hover:bg-[#edf5f0]">

                        Voltar ao início

                    </a>


                    <a
                        href="{{ route('login') }}"
                        class="flex-1 rounded-full
                               bg-[#17442f]
                               px-5 py-3
                               text-center text-[14px]
                               font-semibold text-white
                               no-underline
                               transition hover:bg-[#103624]">

                        Ir para o login

                    </a>

                </div>


                <p
                    class="mt-6 text-center
                           text-[12px] leading-5
                           text-[#8a857b]">

                    Se precisar corrigir alguma informação,
                    entre em contato com o suporte antes da aprovação.

                </p>

            </section>

        </main>

    </div>

</body>

</html>