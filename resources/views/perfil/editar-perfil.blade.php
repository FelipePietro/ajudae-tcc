<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Editar perfil | Ajudae</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>

<body class="min-h-screen bg-[#f7f5ef] font-outfit text-[#292820]">

    {{-- =========================================================
         NAVBAR
    ========================================================== --}}
    @include('/components/navbar-feed')


    {{-- =========================================================
         CONTEÚDO
    ========================================================== --}}
    <main class="px-4 py-8 sm:px-6 md:px-8 md:py-14">

        <div class="mx-auto w-full max-w-[760px]">

            {{-- =================================================
                 CABEÇALHO
            ================================================== --}}
            <div class="mb-6 md:mb-8">

                <span
                    class="inline-flex items-center rounded-full bg-[#f8ead4]
                           px-3 py-2 font-poppins text-xs font-semibold
                           text-[#c67a18] sm:px-4 sm:text-sm"
                >
                    👤 Perfil do voluntário
                </span>

                <h1
                    class="mt-4 break-words text-3xl font-bold leading-tight
                           text-[#292820] sm:text-4xl md:mt-5 md:text-[42px]"
                >
                    Editar meu perfil
                </h1>

                <p
                    class="mt-3 max-w-[680px] font-poppins text-sm
                           leading-6 text-[#817c70] sm:text-[15px] sm:leading-7"
                >
                    Atualize suas informações de contato, endereço e foto de perfil.
                    Algumas informações pessoais não podem ser alteradas diretamente.
                </p>

            </div>


            {{-- =================================================
                 RESUMO DA CONTA
            ================================================== --}}
            <div
                class="mb-6 flex min-w-0 items-start gap-3 rounded-xl
                       border border-[#cbdccf] bg-[#edf5ed]
                       px-4 py-4 sm:items-center sm:gap-4 sm:px-5 md:mb-8"
            >

                <div
                    class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full
                           bg-[#4c9b4b] sm:mt-0"
                ></div>

                <div class="min-w-0">

                    <p
                        class="font-poppins text-sm font-semibold
                               text-[#315c34]"
                    >
                        Perfil ativo
                    </p>

                    <p
                        class="mt-1 break-words font-poppins text-xs
                               leading-5 text-[#658064]"
                    >
                        Membro desde
                        {{ \Carbon\Carbon::parse($pessoa['created_at'])->format('d/m/Y') }}
                        · ID #{{ $pessoa['pessoa_id'] }}
                    </p>

                </div>

            </div>


            {{-- =================================================
                 FORMULÁRIO DO PERFIL
            ================================================== --}}
            <form id="form-editar-perfil">


                {{-- =============================================
                     IDENTIFICAÇÃO
                ============================================== --}}
                <section
                    class="rounded-2xl border border-[#e1ddd3]
                           bg-white p-4 sm:p-5 md:p-8"
                >

                    <h2
                        class="font-poppins text-xs font-semibold
                               uppercase tracking-wide text-[#777268] sm:text-sm"
                    >
                        Identificação
                    </h2>

                    <div class="my-4 h-px bg-[#e5e1d8] sm:my-5"></div>


                    <div class="rounded-xl bg-[#f8f7f2] p-4">

                        <p
                            class="font-poppins text-xs leading-5
                                   text-[#817c70]"
                        >
                            Os dados abaixo fazem parte da sua identificação
                            na plataforma e não podem ser alterados diretamente.
                        </p>

                    </div>


                    {{-- Nome --}}
                    <div class="mt-5 sm:mt-6">

                        <label
                            class="mb-2 block font-poppins
                                   text-sm font-semibold text-[#343229]"
                        >
                            Nome completo
                        </label>

                        <div
                            class="w-full break-words rounded-lg border
                                   border-[#dedacf] bg-[#f1efe8]
                                   px-4 py-3 font-poppins
                                   text-sm text-[#817c70]"
                        >
                            {{ $pessoa['nm_pessoa'] }}
                        </div>

                    </div>


                    {{-- CPF + nascimento --}}
                    <div
                        class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2"
                    >

                        <div class="min-w-0">

                            <label
                                class="mb-2 block font-poppins
                                       text-sm font-semibold text-[#343229]"
                            >
                                CPF
                            </label>

                            <div
                                class="w-full break-words rounded-lg border
                                       border-[#dedacf] bg-[#f1efe8]
                                       px-4 py-3 font-poppins
                                       text-sm text-[#817c70]"
                            >
                                {{ $pessoa['cpf_pessoa'] }}
                            </div>

                        </div>


                        <div class="min-w-0">

                            <label
                                class="mb-2 block font-poppins
                                       text-sm font-semibold text-[#343229]"
                            >
                                Data de nascimento
                            </label>

                            <div
                                class="w-full rounded-lg border
                                       border-[#dedacf] bg-[#f1efe8]
                                       px-4 py-3 font-poppins
                                       text-sm text-[#817c70]"
                            >
                                {{ \Carbon\Carbon::parse($pessoa['dt_nasc'])->format('d/m/Y') }}
                            </div>

                        </div>

                    </div>


                    {{-- Gênero --}}
                    <div class="mt-5">

                        <label
                            class="mb-2 block font-poppins
                                   text-sm font-semibold text-[#343229]"
                        >
                            Gênero
                        </label>

                        <div
                            class="w-full rounded-lg border
                                   border-[#dedacf] bg-[#f1efe8]
                                   px-4 py-3 font-poppins
                                   text-sm capitalize text-[#817c70]"
                        >
                            {{ $pessoa['genero_pessoa'] }}
                        </div>

                    </div>

                </section>


                {{-- =============================================
                     CONTATO
                ============================================== --}}
                <section
                    class="mt-5 rounded-2xl border border-[#e1ddd3]
                           bg-white p-4 sm:mt-6 sm:p-5 md:p-8"
                >

                    <h2
                        class="font-poppins text-xs font-semibold
                               uppercase tracking-wide text-[#777268] sm:text-sm"
                    >
                        Informações de contato
                    </h2>

                    <div class="my-4 h-px bg-[#e5e1d8] sm:my-5"></div>


                    <div
                        class="grid grid-cols-1 gap-5 md:grid-cols-2"
                    >

                        {{-- Email --}}
                        <div class="min-w-0">

                            <label
                                for="email_pessoa"
                                class="mb-2 block font-poppins
                                       text-sm font-semibold text-[#343229]"
                            >
                                E-mail
                                <span class="text-[#d28a27]">*</span>
                            </label>

                            <input
                                id="email_pessoa"
                                name="email_pessoa"
                                type="email"
                                value="{{ $pessoa['email_pessoa'] }}"
                                class="w-full min-w-0 rounded-lg border
                                       border-[#dedacf] bg-[#fbfaf7]
                                       px-4 py-3 font-poppins text-sm
                                       outline-none transition
                                       focus:border-[#17392a]"
                            >

                        </div>


                        {{-- Telefone --}}
                        <div class="min-w-0">

                            <label
                                for="tele_pessoa"
                                class="mb-2 block font-poppins
                                       text-sm font-semibold text-[#343229]"
                            >
                                Telefone
                                <span class="text-[#d28a27]">*</span>
                            </label>

                            <input
                                id="tele_pessoa"
                                name="tele_pessoa"
                                type="text"
                                value="{{ $pessoa['tele_pessoa'] }}"
                                class="w-full min-w-0 rounded-lg border
                                       border-[#dedacf] bg-[#fbfaf7]
                                       px-4 py-3 font-poppins text-sm
                                       outline-none transition
                                       focus:border-[#17392a]"
                            >

                        </div>

                    </div>

                </section>


                {{-- =============================================
                     ENDEREÇO
                ============================================== --}}
                <section
                    class="mt-5 rounded-2xl border border-[#e1ddd3]
                           bg-white p-4 sm:mt-6 sm:p-5 md:p-8"
                >

                    <h2
                        class="font-poppins text-xs font-semibold
                               uppercase tracking-wide text-[#777268] sm:text-sm"
                    >
                        Endereço
                    </h2>

                    <div class="my-4 h-px bg-[#e5e1d8] sm:my-5"></div>


                    {{-- CEP --}}
                    <div class="w-full sm:max-w-[280px]">

                        <label
                            for="cep_pessoa"
                            class="mb-2 block font-poppins
                                   text-sm font-semibold text-[#343229]"
                        >
                            CEP
                        </label>

                        <input
                            id="cep_pessoa"
                            name="cep_pessoa"
                            type="text"
                            value="{{ $pessoa['cep_pessoa'] }}"
                            class="w-full rounded-lg border
                                   border-[#dedacf] bg-[#fbfaf7]
                                   px-4 py-3 font-poppins text-sm
                                   outline-none transition
                                   focus:border-[#17392a]"
                        >

                    </div>


                    {{-- Logradouro --}}
                    <div class="mt-5">

                        <label
                            for="logradouro_pessoa"
                            class="mb-2 block font-poppins
                                   text-sm font-semibold text-[#343229]"
                        >
                            Logradouro
                        </label>

                        <input
                            id="logradouro_pessoa"
                            name="logradouro_pessoa"
                            type="text"
                            value="{{ $pessoa['logradouro_pessoa'] }}"
                            class="w-full min-w-0 rounded-lg border
                                   border-[#dedacf] bg-[#fbfaf7]
                                   px-4 py-3 font-poppins text-sm
                                   outline-none transition
                                   focus:border-[#17392a]"
                        >

                    </div>


                    {{-- Bairro + complemento --}}
                    <div
                        class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2"
                    >

                        <div class="min-w-0">

                            <label
                                for="bairro_pessoa"
                                class="mb-2 block font-poppins
                                       text-sm font-semibold text-[#343229]"
                            >
                                Bairro
                            </label>

                            <input
                                id="bairro_pessoa"
                                name="bairro_pessoa"
                                type="text"
                                value="{{ $pessoa['bairro_pessoa'] }}"
                                class="w-full min-w-0 rounded-lg border
                                       border-[#dedacf] bg-[#fbfaf7]
                                       px-4 py-3 font-poppins text-sm
                                       outline-none transition
                                       focus:border-[#17392a]"
                            >

                        </div>


                        <div class="min-w-0">

                            <label
                                for="compl_pessoa"
                                class="mb-2 block font-poppins
                                       text-sm font-semibold text-[#343229]"
                            >
                                Complemento
                            </label>

                            <input
                                id="compl_pessoa"
                                name="compl_pessoa"
                                type="text"
                                value="{{ $pessoa['compl_pessoa'] }}"
                                class="w-full min-w-0 rounded-lg border
                                       border-[#dedacf] bg-[#fbfaf7]
                                       px-4 py-3 font-poppins text-sm
                                       outline-none transition
                                       focus:border-[#17392a]"
                            >

                        </div>

                    </div>


                    {{-- Cidade + UF --}}
                    <div
                        class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-[minmax(0,1fr)_120px]"
                    >

                        <div class="min-w-0">

                            <label
                                for="cidade_pessoa"
                                class="mb-2 block font-poppins
                                       text-sm font-semibold text-[#343229]"
                            >
                                Cidade
                            </label>

                            <input
                                id="cidade_pessoa"
                                name="cidade_pessoa"
                                type="text"
                                value="{{ $pessoa['cidade_pessoa'] }}"
                                class="w-full min-w-0 rounded-lg border
                                       border-[#dedacf] bg-[#fbfaf7]
                                       px-4 py-3 font-poppins text-sm
                                       outline-none transition
                                       focus:border-[#17392a]"
                            >

                        </div>


                        <div class="min-w-0">

                            <label
                                for="uf_pessoa"
                                class="mb-2 block font-poppins
                                       text-sm font-semibold text-[#343229]"
                            >
                                Estado
                            </label>

                            <input
                                id="uf_pessoa"
                                name="uf_pessoa"
                                type="text"
                                maxlength="2"
                                value="{{ $pessoa['uf_pessoa'] }}"
                                class="w-full min-w-0 rounded-lg border
                                       border-[#dedacf] bg-[#fbfaf7]
                                       px-4 py-3 font-poppins
                                       text-sm uppercase outline-none
                                       transition focus:border-[#17392a]"
                            >

                        </div>

                    </div>

                </section>


                {{-- =============================================
                     FOTO DE PERFIL
                ============================================== --}}
                <section
                    class="mt-5 rounded-2xl border border-[#e1ddd3]
                           bg-white p-4 sm:mt-6 sm:p-5 md:p-8"
                >

                    <h2
                        class="font-poppins text-xs font-semibold
                               uppercase tracking-wide text-[#777268] sm:text-sm"
                    >
                        Foto de perfil
                    </h2>

                    <div class="my-4 h-px bg-[#e5e1d8] sm:my-5"></div>


                    <div
                        class="flex flex-col gap-5 rounded-xl
                               border border-dashed border-[#ddd8cc]
                               bg-[#fbfaf7] p-4
                               sm:flex-row sm:items-center sm:p-5"
                    >

                        <img
                            src="{{ $pessoa['pfp_pessoa_link'] }}"
                            alt="Foto atual do perfil"
                            class="h-20 w-20 shrink-0 rounded-full
                                   border border-[#dedacf] object-cover"
                        >


                        <div class="min-w-0 flex-1">

                            <p
                                class="font-poppins text-sm font-semibold
                                       text-[#343229]"
                            >
                                Foto atual
                            </p>

                            <p
                                class="mt-1 break-words font-poppins text-xs
                                       leading-5 text-[#8b867c]"
                            >
                                PNG ou JPG · Recomendado 400×400px · Máximo 5MB
                            </p>


                            <div
                                class="mt-4 flex flex-col gap-3
                                       sm:flex-row sm:flex-wrap sm:items-center"
                            >

                                <x-botao-secundario
                                    href="#"
                                    texto="Alterar imagem"
                                />

                                <button
                                    type="button"
                                    class="text-left font-poppins text-sm font-semibold
                                           text-[#ad554b] hover:underline sm:text-center"
                                >
                                    Remover
                                </button>

                            </div>

                        </div>

                    </div>

                </section>


                {{-- =============================================
                     BOTÕES DO PERFIL
                ============================================== --}}
                <div
                    class="mt-6 flex flex-col-reverse gap-3
                           sm:flex-row sm:justify-end"
                >

                    <x-botao-secundario
                        href="/perfil"
                        texto="Cancelar"
                    />

                    <x-botao-primario
                        href="#"
                        texto="Salvar alterações →"
                    />

                </div>

            </form>


            {{-- =================================================
                 SEGURANÇA
            ================================================== --}}
            <section
                class="mt-8 rounded-2xl border border-[#e1ddd3]
                       bg-white p-4 sm:mt-10 sm:p-5 md:p-8"
            >

                <h2
                    class="font-poppins text-xs font-semibold
                           uppercase tracking-wide text-[#777268] sm:text-sm"
                >
                    Segurança
                </h2>

                <div class="my-4 h-px bg-[#e5e1d8] sm:my-5"></div>


                <div>

                    <h3 class="text-base font-semibold text-[#292820]">
                        Alterar senha
                    </h3>

                    <p
                        class="mt-1 font-poppins text-xs leading-5
                               text-[#8b867c]"
                    >
                        Escolha uma senha segura e diferente das utilizadas
                        anteriormente.
                    </p>

                </div>


                <form
                    id="form-alterar-senha"
                    class="mt-5"
                >

                    <div
                        class="grid grid-cols-1 gap-5 md:grid-cols-2"
                    >

                        {{-- Nova senha --}}
                        <div class="min-w-0">

                            <label
                                for="senha_pessoa"
                                class="mb-2 block font-poppins
                                       text-sm font-semibold text-[#343229]"
                            >
                                Nova senha
                            </label>

                            <input
                                id="senha_pessoa"
                                name="senha_pessoa"
                                type="password"
                                placeholder="Digite a nova senha"
                                class="w-full min-w-0 rounded-lg border
                                       border-[#dedacf] bg-[#fbfaf7]
                                       px-4 py-3 font-poppins text-sm
                                       placeholder:text-[#aaa599]
                                       outline-none transition
                                       focus:border-[#17392a]"
                            >

                        </div>


                        {{-- Confirmação --}}
                        <div class="min-w-0">

                            <label
                                for="senha_confirmation"
                                class="mb-2 block font-poppins
                                       text-sm font-semibold text-[#343229]"
                            >
                                Confirmar nova senha
                            </label>

                            <input
                                id="senha_confirmation"
                                name="senha_confirmation"
                                type="password"
                                placeholder="Repita a nova senha"
                                class="w-full min-w-0 rounded-lg border
                                       border-[#dedacf] bg-[#fbfaf7]
                                       px-4 py-3 font-poppins text-sm
                                       placeholder:text-[#aaa599]
                                       outline-none transition
                                       focus:border-[#17392a]"
                            >

                        </div>

                    </div>


                    <div
                        class="mt-5 flex flex-col sm:flex-row sm:justify-end"
                    >

                        <x-botao-primario
                            href="#"
                            texto="Alterar senha"
                        />

                    </div>

                </form>

            </section>


            {{-- =================================================
                 ZONA DE PERIGO
            ================================================== --}}
            <section
                class="mt-5 rounded-2xl border border-red-200
                       bg-white p-4 sm:mt-6 sm:p-5 md:p-8"
            >

                <h2
                    class="font-poppins text-xs font-semibold
                           uppercase tracking-wide text-red-600 sm:text-sm"
                >
                    Zona de perigo
                </h2>

                <div class="my-4 h-px bg-red-100 sm:my-5"></div>


                <div
                    class="flex flex-col gap-5
                           sm:flex-row sm:items-center sm:justify-between"
                >

                    <div class="min-w-0">

                        <h3
                            class="text-base font-semibold text-[#292820]"
                        >
                            Excluir minha conta
                        </h3>

                        <p
                            class="mt-2 max-w-xl font-poppins
                                   text-xs leading-5 text-[#8b867c]"
                        >
                            Ao solicitar a exclusão, sua conta será
                            programada para remoção após o período
                            de segurança. Durante esse período,
                            você poderá cancelar a solicitação.
                        </p>

                    </div>


                    <a
                        href="/perfil/excluir"
                        class="w-full shrink-0 rounded-lg border
                               border-red-500 px-5 py-3 text-center
                               font-poppins text-sm font-semibold
                               text-red-600 transition
                               hover:bg-red-600 hover:text-white
                               sm:w-auto"
                    >
                        Excluir minha conta
                    </a>

                </div>

            </section>


            {{-- =================================================
                 AVISO
            ================================================== --}}
            <div
                class="mt-5 rounded-xl border border-[#edcf9f]
                       bg-[#fff0d8] px-4 py-5 sm:mt-6 sm:px-5 md:px-6"
            >

                <p
                    class="font-poppins text-sm font-semibold
                           text-[#b56e13]"
                >
                    ⚠ Antes de fazer alterações
                </p>

                <div
                    class="mt-3 space-y-2 font-poppins text-sm
                           leading-6 text-[#7e705d]"
                >

                    <p>
                        Alterações no e-mail podem exigir uma nova verificação.
                    </p>

                    <p>
                        Sua foto de perfil será atualizada após salvar as alterações.
                    </p>

                    <p>
                        A exclusão da conta não acontece imediatamente e poderá ser
                        cancelada durante o período de segurança.
                    </p>

                </div>

            </div>

        </div>

    </main>

</body>

</html>