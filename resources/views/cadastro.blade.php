<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de voluntário | Ajudae</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>

<body class="flex min-h-screen flex-col bg-[#F7F5F0] text-[#272820]">

    @include('/components/navbar')

    <div class="flex-1 lg:grid lg:grid-cols-[260px_minmax(0,1fr)] lg:items-stretch">

        {{-- SIDEBAR DESKTOP --}}
        <aside class="hidden bg-[#173F2D] text-white lg:flex lg:flex-col lg:self-stretch lg:px-7 lg:py-10">
            <div class="mb-9 font-fraunces text-2xl font-bold">
                <span>Ajuda</span><span class="text-[#E9A11A]">ê</span>
            </div>

            <div id="cadastro-sidebar" class="space-y-1">
                @php
                    $steps = [
                        1 => ['Verificação de e-mail', 'Confirme seu endereço antes de continuar'],
                        2 => ['Dados pessoais e foto', 'Identificação, contato e endereço'],
                        3 => ['Habilidades e interesses', 'Tags que ajudam a definir seu perfil'],
                        4 => ['Assinatura do Termo', 'Leitura e aceite digital'],
                    ];
                @endphp

                @foreach ($steps as $numero => [$titulo, $subtitulo])
                    <button
                        type="button"
                        data-nav-step="{{ $numero }}"
                        class="cadastro-nav-step group relative grid w-full grid-cols-[22px_minmax(0,1fr)] gap-3 pb-7 text-left"
                    >
                        @if ($numero < 4)
                            <span class="absolute left-[10px] top-5 h-[calc(100%-10px)] w-px bg-white/15" data-step-line></span>
                        @endif

                        <span
                            class="cadastro-nav-dot relative z-10 flex h-[22px] w-[22px] items-center justify-center rounded-full border-2 border-white/20 bg-[#234C39] text-[10px] font-bold text-transparent"
                        >
                            {{ $numero }}
                        </span>

                        <span>
                            <span class="block font-poppins text-[10px] text-white/60">
                                Passo {{ $numero }}
                            </span>
                            <span class="mt-1 block text-sm font-semibold">
                                {{ $titulo }}
                            </span>
                            <span class="mt-1 block font-poppins text-[10px] leading-4 text-white/55">
                                {{ $subtitulo }}
                            </span>
                        </span>
                    </button>
                @endforeach
            </div>

            <div class="mt-8 rounded-xl border border-white/10 bg-white/[0.07] p-4 font-poppins text-[10px] leading-5 text-white/70">
                <strong class="block text-white">🔒 Seus dados importam</strong>
                As informações de identificação são utilizadas para segurança,
                organização das atividades e confirmação de participação.
            </div>
        </aside>

        {{-- PROGRESSO MOBILE --}}
        <div class="bg-[#173F2D] px-4 py-4 text-white lg:hidden">
            <div class="flex items-center justify-between gap-4 font-poppins text-[11px]">
                <span id="mobile-step-label">Passo 1 de 4</span>
                <strong id="mobile-step-title">Verificação de e-mail</strong>
            </div>
            <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-white/15">
                <div id="mobile-progress" class="h-full w-1/4 rounded-full bg-[#E9A11A] transition-all"></div>
            </div>
        </div>

        <main class="min-w-0">
            <div class="w-full max-w-[880px] px-4 pb-10 pt-10 sm:px-7 sm:pb-12 sm:pt-12 lg:px-11 lg:pb-14 lg:pt-14">

                @if ($errors->any())
                    <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 font-poppins text-sm text-red-700">
                        <strong>Não foi possível concluir o cadastro.</strong>
                        <ul class="mt-2 list-disc pl-5">
                            @foreach ($errors->all() as $erro)
                                <li>{{ $erro }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form
                    id="cadastro-form"
                    action="{{ route('cadastro.pessoa.finalizar') }}"
                    method="POST"
                    enctype="multipart/form-data"
                    novalidate
                >
                    @csrf

                    {{-- Campos previstos pelo backend atual. No mock, recebem URLs fictícias.
                         Depois, o upload real deve preencher estas URLs antes do register. --}}
                    <input type="hidden" name="pfp_pessoa_link" id="pfp_pessoa_link" value="{{ old('pfp_pessoa_link') }}">
                    <input type="hidden" name="rg_pessoa_link" id="rg_pessoa_link" value="{{ old('rg_pessoa_link', 'mock://rg-pendente') }}">
                    <input type="hidden" name="antecedentes_pessoa_link" id="antecedentes_pessoa_link" value="{{ old('antecedentes_pessoa_link', 'mock://antecedentes-pendente') }}">
                    <input type="hidden" name="cnh_pessoa_link" id="cnh_pessoa_link" value="{{ old('cnh_pessoa_link', 'nao-informado') }}">

                    {{-- =====================================================
                         PASSO 1 — VERIFICAÇÃO DE E-MAIL
                    ====================================================== --}}
                    <section data-step-panel="1">
                        <p class="font-poppins text-xs text-[#8C887E]">Passo 1 de 4</p>
                        <h1 class="mt-2 font-fraunces text-3xl font-bold text-[#20231E] sm:text-4xl">
                            Verifique seu e-mail
                        </h1>
                        <p class="mt-3 max-w-2xl font-poppins text-sm leading-6 text-[#68665F]">
                            Antes de criar seu perfil, confirme um endereço de e-mail válido.
                            Nesta fase a confirmação é simulada para a pré-banca.
                        </p>

                        <div class="mt-8 rounded-xl border border-[#E0DCD1] bg-white p-5 sm:p-6">
                            <label for="email_pessoa" class="font-poppins text-xs font-medium">
                                E-mail <span class="text-[#D78D08]">*</span>
                            </label>
                            <input
                                id="email_pessoa"
                                name="email_pessoa"
                                type="email"
                                value="{{ old('email_pessoa', $cadastroMock['email_pessoa'] ?? '') }}"
                                placeholder="voce@email.com"
                                class="mt-2 w-full rounded-lg border border-[#D9D4C9] bg-white px-4 py-3 font-poppins text-sm outline-none focus:border-[#2D6A4F]"
                            >
                            <p id="email-error" class="mt-2 hidden font-poppins text-[11px] text-red-700">
                                Informe um e-mail válido.
                            </p>

                            <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                                <button
                                    type="button"
                                    id="btn-enviar-email"
                                    class="rounded-full bg-[#173F2D] px-5 py-2.5 text-sm font-semibold text-white"
                                >
                                    Enviar link de verificação
                                </button>

                                <a
                                    href="{{ route('login') }}"
                                    class="rounded-full border border-[#CFC9BC] px-5 py-2.5 text-center text-sm font-semibold text-[#4A4942]"
                                >
                                    Já tenho uma conta
                                </a>
                            </div>

                            <div id="email-enviado" class="mt-5 hidden rounded-lg border border-[#CFE0D4] bg-[#EEF7F2] p-4 font-poppins text-xs text-[#24583E]">
                                <strong>✓ Link enviado.</strong>
                                <p class="mt-1">Clique abaixo para simular a confirmação recebida por e-mail.</p>
                                <button
                                    type="button"
                                    id="btn-confirmar-email"
                                    class="mt-3 rounded-full bg-[#173F2D] px-4 py-2 font-outfit text-xs font-semibold text-white"
                                >
                                    Confirmar e-mail (mock)
                                </button>
                            </div>

                            <div id="email-confirmado" class="mt-5 hidden rounded-lg border border-[#CFE0D4] bg-[#EEF7F2] p-4 font-poppins text-xs text-[#24583E]">
                                <strong>✓ E-mail confirmado com sucesso.</strong>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end">
                            <button
                                type="button"
                                data-next
                                disabled
                                class="rounded-full bg-[#173F2D] px-6 py-3 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-40"
                            >
                                Próximo: Dados pessoais →
                            </button>
                        </div>
                    </section>

                    {{-- =====================================================
                         PASSO 2 — DADOS PESSOAIS
                    ====================================================== --}}
                    <section data-step-panel="2" class="hidden">
                        <p class="font-poppins text-xs text-[#8C887E]">Passo 2 de 4</p>
                        <h1 class="mt-2 font-fraunces text-3xl font-bold text-[#20231E] sm:text-4xl">
                            Seus dados pessoais
                        </h1>
                        <p class="mt-3 max-w-2xl font-poppins text-sm leading-6 text-[#68665F]">
                            Preencha com atenção. Os nomes dos principais campos já acompanham
                            o cadastro de Pessoa do backend.
                        </p>

                        <div class="mt-8 grid gap-5 sm:grid-cols-2">
                            @php
                                $inputClass = 'mt-2 w-full rounded-lg border border-[#D9D4C9] bg-white px-4 py-3 font-poppins text-sm outline-none focus:border-[#2D6A4F]';
                                $labelClass = 'font-poppins text-xs font-medium';
                            @endphp

                            <div data-required-wrap>
                                <label class="{{ $labelClass }}" for="nm_pessoa">Nome completo *</label>
                                <input id="nm_pessoa" name="nm_pessoa" value="{{ old('nm_pessoa', $cadastroMock['nm_pessoa'] ?? '') }}" class="{{ $inputClass }}" placeholder="Lucas Pereira de Souza">
                            </div>

                            <div data-required-wrap>
                                <label class="{{ $labelClass }}" for="cpf_pessoa">CPF *</label>
                                <input id="cpf_pessoa" name="cpf_pessoa" value="{{ old('cpf_pessoa') }}" class="{{ $inputClass }}" placeholder="000.000.000-00" maxlength="14" inputmode="numeric">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="{{ $labelClass }}">E-mail</label>
                                <div class="mt-2 rounded-lg border border-[#CFE0D4] bg-[#EEF7F2] px-4 py-3 font-poppins text-xs text-[#24583E]">
                                    ✓ <span id="verified-email-text">{{ old('email_pessoa', $cadastroMock['email_pessoa'] ?? '') }}</span>
                                </div>
                            </div>

                            <div data-required-wrap>
                                <label class="{{ $labelClass }}" for="tele_pessoa">Telefone *</label>
                                <input id="tele_pessoa" name="tele_pessoa" value="{{ old('tele_pessoa') }}" class="{{ $inputClass }}" placeholder="(11) 99999-9999" maxlength="15" inputmode="numeric">
                            </div>

                            <div data-required-wrap>
                                <label class="{{ $labelClass }}" for="dt_nasc">Data de nascimento *</label>
                                <input id="dt_nasc" name="dt_nasc" type="date" value="{{ old('dt_nasc') }}" class="{{ $inputClass }}">
                            </div>

                            <div data-required-wrap>
                                <label class="{{ $labelClass }}" for="genero_pessoa">Gênero *</label>
                                <select id="genero_pessoa" name="genero_pessoa" class="{{ $inputClass }}">
                                    <option value="">Selecione</option>
                                    <option value="masculino">Masculino</option>
                                    <option value="feminino">Feminino</option>
                                    <option value="outro">Outro</option>
                                    <option value="prefiro não dizer">Prefiro não dizer</option>
                                </select>
                            </div>

                            <div data-required-wrap>
                                <label class="{{ $labelClass }}" for="rg_pessoa">RG *</label>
                                <input id="rg_pessoa" name="rg_pessoa" value="{{ old('rg_pessoa') }}" class="{{ $inputClass }}" placeholder="00.000.000-0">
                            </div>
                        </div>

                        <div class="my-7 h-px bg-[#DDD8CC]"></div>

                        <h2 class="text-lg font-semibold text-[#26332B]">Dados da conta</h2>
                        <div class="mt-5 grid gap-5 sm:grid-cols-2">
                            <div data-required-wrap class="sm:col-span-2">
                                <label class="{{ $labelClass }}" for="login_pessoa">Nome de usuário *</label>
                                <input id="login_pessoa" name="login_pessoa" value="{{ old('login_pessoa', $cadastroMock['login_pessoa'] ?? '') }}" class="{{ $inputClass }}" placeholder="lucaspereira">
                            </div>

                            <div data-required-wrap>
                                <label class="{{ $labelClass }}" for="senha_pessoa">Senha *</label>
                                <input id="senha_pessoa" name="senha_pessoa" type="password" class="{{ $inputClass }}" placeholder="Mínimo 8 caracteres">
                            </div>

                            <div data-required-wrap>
                                <label class="{{ $labelClass }}" for="senha_pessoa_confirmation">Confirmar senha *</label>
                                <input id="senha_pessoa_confirmation" name="senha_pessoa_confirmation" type="password" class="{{ $inputClass }}" placeholder="Repita a senha">
                            </div>
                        </div>

                        <div class="my-7 h-px bg-[#DDD8CC]"></div>

                        <h2 class="text-lg font-semibold text-[#26332B]">Endereço</h2>
                        <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-6">
                            <div data-required-wrap class="lg:col-span-2">
                                <label class="{{ $labelClass }}" for="cep_pessoa">CEP *</label>
                                <input id="cep_pessoa" name="cep_pessoa" value="{{ old('cep_pessoa') }}" class="{{ $inputClass }}" placeholder="00000-000" maxlength="9" inputmode="numeric">
                            </div>

                            <div data-required-wrap class="lg:col-span-2">
                                <label class="{{ $labelClass }}" for="cidade_pessoa">Cidade *</label>
                                <input id="cidade_pessoa" name="cidade_pessoa" value="{{ old('cidade_pessoa') }}" class="{{ $inputClass }}" placeholder="Após o CEP">
                            </div>

                            <div data-required-wrap class="lg:col-span-2">
                                <label class="{{ $labelClass }}" for="uf_pessoa">Estado *</label>
                                <input id="uf_pessoa" name="uf_pessoa" value="{{ old('uf_pessoa') }}" class="{{ $inputClass }}" placeholder="UF" maxlength="2">
                            </div>

                            <div data-required-wrap class="lg:col-span-3">
                                <label class="{{ $labelClass }}" for="logradouro_pessoa">Logradouro *</label>
                                <input id="logradouro_pessoa" name="logradouro_pessoa" value="{{ old('logradouro_pessoa') }}" class="{{ $inputClass }}" placeholder="Rua, avenida...">
                            </div>

                            <div data-required-wrap class="lg:col-span-3">
                                <label class="{{ $labelClass }}" for="bairro_pessoa">Bairro *</label>
                                <input id="bairro_pessoa" name="bairro_pessoa" value="{{ old('bairro_pessoa') }}" class="{{ $inputClass }}" placeholder="Seu bairro">
                            </div>

                            <div class="sm:col-span-2 lg:col-span-6">
                                <label class="{{ $labelClass }}" for="compl_pessoa">Complemento</label>
                                <input id="compl_pessoa" name="compl_pessoa" value="{{ old('compl_pessoa') }}" class="{{ $inputClass }}" placeholder="Apartamento, bloco, referência...">
                            </div>
                        </div>

                        <div class="mt-5">
                            <label class="{{ $labelClass }}" for="bio_pessoa">Bio (opcional)</label>
                            <textarea id="bio_pessoa" name="bio_pessoa" class="{{ $inputClass }} min-h-24 resize-y" placeholder="Conte sobre você, sua motivação e experiências anteriores...">{{ old('bio_pessoa') }}</textarea>
                        </div>

                        <div class="my-7 h-px bg-[#DDD8CC]"></div>

                        <div data-photo-wrap>
                            <label class="{{ $labelClass }}">Foto de perfil *</label>
                            <label class="relative mt-2 flex min-h-44 cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-[#CEC7B7] bg-[#FBFAF6] p-6 text-center">
                                <input id="foto_perfil" name="foto_perfil" type="file" accept="image/png,image/jpeg" class="absolute inset-0 h-full w-full cursor-pointer opacity-0">
                                <span class="text-2xl">📷</span>
                                <strong id="foto-title" class="mt-3 text-sm">Envie sua foto de perfil</strong>
                                <span class="mt-1 font-poppins text-[10px] text-[#817C72]">JPG ou PNG · Máximo 5 MB</span>
                                <span class="mt-2 font-poppins text-[11px] font-semibold text-[#173F2D] underline">Clique para selecionar</span>
                            </label>
                            <p id="foto-error" class="mt-2 hidden font-poppins text-[11px] text-red-700">Selecione uma foto.</p>
                        </div>

                        <div class="mt-4 border-l-2 border-[#E9A11A] bg-[#FFF9ED] p-4 font-poppins text-[11px] leading-5 text-[#5D5B54]">
                            <strong class="block">Por que a foto é obrigatória?</strong>
                            Organizadores precisam reconhecer visualmente voluntários aceitos em atividades presenciais.
                        </div>

                        <details class="mt-6 rounded-xl border border-[#E0DBCF] bg-white p-4">
                            <summary class="cursor-pointer text-sm font-semibold text-[#37443B]">
                                Documentos adicionais — integração futura
                            </summary>
                            <p class="mt-2 font-poppins text-[10px] leading-5 text-[#7B776E]">
                                O backend atual possui campos de link para RG, antecedentes, CNH e foto.
                                No mock, estes campos recebem URLs fictícias. Depois, um upload real deverá gerar essas URLs.
                            </p>

                            <div class="mt-4 grid gap-4 sm:grid-cols-3">
                                <div>
                                    <label class="{{ $labelClass }}">Arquivo do RG</label>
                                    <input type="file" data-link-target="rg_pessoa_link" class="{{ $inputClass }}">
                                </div>
                                <div>
                                    <label class="{{ $labelClass }}">Antecedentes</label>
                                    <input type="file" data-link-target="antecedentes_pessoa_link" class="{{ $inputClass }}">
                                </div>
                                <div>
                                    <label class="{{ $labelClass }}">CNH, se possuir</label>
                                    <input type="file" data-link-target="cnh_pessoa_link" class="{{ $inputClass }}">
                                </div>
                            </div>
                        </details>

                        <p id="step2-error" class="mt-4 hidden font-poppins text-[11px] text-red-700">
                            Preencha os campos obrigatórios antes de continuar.
                        </p>

                        <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-between">
                            <button type="button" data-prev class="rounded-full border border-[#CFC9BC] px-6 py-3 text-sm font-semibold">← Voltar</button>
                            <button type="button" data-next class="rounded-full bg-[#173F2D] px-6 py-3 text-sm font-semibold text-white">Próximo: Habilidades e interesses →</button>
                        </div>
                    </section>

                    {{-- =====================================================
                         PASSO 3 — HABILIDADES E INTERESSES
                    ====================================================== --}}
                    <section data-step-panel="3" class="hidden">
                        <p class="font-poppins text-xs text-[#8C887E]">Passo 3 de 4</p>
                        <h1 class="mt-2 font-fraunces text-3xl font-bold text-[#20231E] sm:text-4xl">
                            Habilidades e interesses
                        </h1>
                        <p class="mt-3 max-w-2xl font-poppins text-sm leading-6 text-[#68665F]">
                            Selecione suas habilidades, causas de interesse e, se quiser,
                            recursos que pode oferecer.
                        </p>

                        <h2 class="mt-8 text-lg font-semibold text-[#26332B]">Suas habilidades</h2>
                        <p class="mt-1 font-poppins text-xs text-[#858078]">Escolha pelo menos uma e informe seu nível.</p>

                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            @foreach ($habilidades as $habilidade)
                                <div class="rounded-xl border border-[#DDD8CC] bg-white p-4" data-skill-card>
                                    <label class="flex cursor-pointer items-start gap-3">
                                        <input
                                            type="checkbox"
                                            name="habilidades[]"
                                            value="{{ $habilidade['habilidade_id'] }}"
                                            class="mt-1 h-4 w-4 accent-[#173F2D]"
                                            data-skill-check
                                        >
                                        <span>
                                            <strong class="text-sm text-[#26372D]">{{ $habilidade['nome_habilidade'] }}</strong>
                                            <span class="mt-1 block font-poppins text-[10px] leading-4 text-[#77746C]">
                                                {{ $habilidade['descricao_habilidade'] }}
                                            </span>
                                        </span>
                                    </label>

                                    <div class="mt-3 hidden" data-skill-level>
                                        <label class="font-poppins text-[10px] font-medium">Seu nível</label>
                                        <select
                                            name="nivel_habilidade[{{ $habilidade['habilidade_id'] }}]"
                                            disabled
                                            class="mt-1 w-full rounded-lg border border-[#D9D4C9] bg-white px-3 py-2 font-poppins text-xs"
                                        >
                                            <option value="1">1 — Iniciante</option>
                                            <option value="2">2 — Básico</option>
                                            <option value="3" selected>3 — Intermediário</option>
                                            <option value="4">4 — Avançado</option>
                                            <option value="5">5 — Especialista</option>
                                        </select>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <p id="skills-error" class="mt-2 hidden font-poppins text-[11px] text-red-700">Selecione ao menos uma habilidade.</p>

                        <div class="my-7 h-px bg-[#DDD8CC]"></div>

                        <h2 class="text-lg font-semibold text-[#26332B]">Causas que te interessam</h2>
                        <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($causas as $causa)
                                <label class="cursor-pointer rounded-xl border border-[#DDD8CC] bg-white p-4">
                                    <div class="flex items-start gap-3">
                                        <input type="checkbox" name="causas[]" value="{{ $causa['causa_id'] }}" class="mt-1 h-4 w-4 accent-[#173F2D]">
                                        <span>
                                            <strong class="text-sm text-[#26372D]">{{ $causa['nome_causa'] }}</strong>
                                            <span class="mt-1 block font-poppins text-[10px] leading-4 text-[#77746C]">{{ $causa['descricao_causa'] }}</span>
                                        </span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        <p id="causas-error" class="mt-2 hidden font-poppins text-[11px] text-red-700">Selecione ao menos uma causa.</p>

                        <div class="my-7 h-px bg-[#DDD8CC]"></div>

                        <h2 class="text-lg font-semibold text-[#26332B]">Recursos que você pode oferecer</h2>
                        <p class="mt-1 font-poppins text-xs text-[#858078]">Opcional.</p>

                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            @foreach ($recursos as $recurso)
                                <label class="cursor-pointer rounded-xl border border-[#DDD8CC] bg-white p-4">
                                    <div class="flex items-start gap-3">
                                        <input type="checkbox" name="recursos[]" value="{{ $recurso['recurso_id'] }}" class="mt-1 h-4 w-4 accent-[#173F2D]">
                                        <span>
                                            <strong class="text-sm text-[#26372D]">{{ $recurso['nome_recurso'] }}</strong>
                                            <span class="mt-1 block font-poppins text-[10px] leading-4 text-[#77746C]">{{ $recurso['descricao_recurso'] }}</span>
                                        </span>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        <textarea
                            name="detalhes_recurso"
                            class="mt-4 min-h-24 w-full resize-y rounded-lg border border-[#D9D4C9] bg-white px-4 py-3 font-poppins text-sm outline-none focus:border-[#2D6A4F]"
                            placeholder="Detalhes opcionais. Ex.: tenho carro e posso ajudar com transporte."
                        ></textarea>

                        <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-between">
                            <button type="button" data-prev class="rounded-full border border-[#CFC9BC] px-6 py-3 text-sm font-semibold">← Voltar</button>
                            <button type="button" data-next class="rounded-full bg-[#173F2D] px-6 py-3 text-sm font-semibold text-white">Próximo: Assinatura do Termo →</button>
                        </div>
                    </section>

                    {{-- =====================================================
                         PASSO 4 — TERMO
                    ====================================================== --}}
                    <section data-step-panel="4" class="hidden">
                        <p class="font-poppins text-xs text-[#8C887E]">Passo 4 de 4</p>
                        <h1 class="mt-2 font-fraunces text-3xl font-bold text-[#20231E] sm:text-4xl">
                            Assinatura do Termo
                        </h1>
                        <p class="mt-3 max-w-2xl font-poppins text-sm leading-6 text-[#68665F]">
                            Leia o documento e confirme o aceite para finalizar seu cadastro.
                        </p>

                        <div class="mt-7 flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <strong class="text-sm text-[#2A372F]">{{ $termo['titulo'] }}</strong>
                                <p class="mt-1 font-poppins text-[10px] text-[#858078]">
                                    Versão {{ $termo['versao'] }} · {{ $termo['atualizado_em'] }}
                                </p>
                            </div>
                            <span class="rounded-full bg-[#EEF7F2] px-3 py-1.5 font-poppins text-[10px] font-semibold text-[#27563E]">
                                Documento do cadastro
                            </span>
                        </div>

                        <div class="mt-4 max-h-[360px] overflow-y-auto rounded-xl border border-[#DDD8CD] bg-white p-5 font-poppins text-xs leading-6 text-[#4D4C45]">
                            <h3 class="font-outfit text-sm font-bold text-[#24372C]">1. Sobre o Ajudae</h3>
                            <p class="mt-1">O Ajudae aproxima pessoas interessadas em voluntariado de organizações e responsáveis por ações sociais.</p>

                            <h3 class="mt-5 font-outfit text-sm font-bold text-[#24372C]">2. Dados e perfil</h3>
                            <p class="mt-1">O usuário declara que as informações fornecidas são verdadeiras e concorda com seu uso para segurança, organização de atividades e funcionamento da plataforma.</p>

                            <h3 class="mt-5 font-outfit text-sm font-bold text-[#24372C]">3. Candidaturas e participação</h3>
                            <p class="mt-1">A candidatura não garante participação automática. A aprovação pode depender do responsável pelo evento e das regras da atividade.</p>

                            <h3 class="mt-5 font-outfit text-sm font-bold text-[#24372C]">4. Conduta</h3>
                            <p class="mt-1">O voluntário deve manter postura respeitosa com organizações, participantes, comunidades atendidas e demais pessoas envolvidas.</p>

                            <h3 class="mt-5 font-outfit text-sm font-bold text-[#24372C]">5. Registros de participação</h3>
                            <p class="mt-1">A plataforma poderá registrar candidaturas, aprovações, presença, histórico, experiência, XP e certificados quando essas funcionalidades forem aplicáveis.</p>

                            <h3 class="mt-5 font-outfit text-sm font-bold text-[#24372C]">6. Aceite eletrônico</h3>
                            <p class="mt-1">Ao marcar a opção abaixo e finalizar, o usuário manifesta concordância com esta versão do termo e autoriza o registro dos metadados técnicos associados ao aceite.</p>
                        </div>

                        <div class="mt-5 rounded-xl border border-[#D4E1D8] bg-[#F0F6F2] p-4">
                            <div class="flex gap-3">
                                <span class="text-xl">✍️</span>
                                <div>
                                    <strong class="text-sm text-[#294232]">Assinatura eletrônica preparada para o backend</strong>
                                    <p class="mt-1 font-poppins text-[10px] leading-5 text-[#657068]">
                                        O servidor poderá registrar IP, user agent, plataforma/dispositivo,
                                        URL do documento, hash e momento do aceite.
                                    </p>
                                </div>
                            </div>

                            <div class="mt-3 rounded-lg bg-white/70 p-3 font-mono text-[10px] text-[#637068]">
                                Documento: {{ $termo['documento_url'] }}<br>
                                Hash mock: {{ \Illuminate\Support\Str::limit($termo['documento_hash'], 42) }}
                            </div>
                        </div>

                        <label class="mt-5 flex cursor-pointer gap-3 rounded-xl border border-[#DDD8CC] bg-white p-4">
                            <input id="aceitou_termos" name="aceitou_termos" value="1" type="checkbox" class="mt-1 h-4 w-4 accent-[#173F2D]">
                            <span>
                                <strong class="block text-sm text-[#2C362F]">
                                    Li e aceito os Termos de Uso e Participação Voluntária.
                                </strong>
                                <span class="mt-1 block font-poppins text-[10px] leading-5 text-[#77736B]">
                                    O campo é enviado como <code>aceitou_termos=1</code>.
                                </span>
                            </span>
                        </label>

                        <p id="termo-error" class="mt-2 hidden font-poppins text-[11px] text-red-700">
                            Você precisa aceitar o termo para concluir.
                        </p>

                        <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-between">
                            <button type="button" data-prev class="rounded-full border border-[#CFC9BC] px-6 py-3 text-sm font-semibold">← Voltar</button>
                            <button id="btn-finalizar" type="button" class="rounded-full bg-[#173F2D] px-6 py-3 text-sm font-semibold text-white">
                                ✓ Finalizar cadastro
                            </button>
                        </div>

                        <p class="mt-4 text-right font-poppins text-[10px] text-[#8C887F]">
                            Mock atual → depois pode ser substituído por POST /api/v1/pessoas/register.
                        </p>
                    </section>
                </form>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const panels = [...document.querySelectorAll('[data-step-panel]')];
            const navs = [...document.querySelectorAll('[data-nav-step]')];
            const stepNames = {
                1: 'Verificação de e-mail',
                2: 'Dados pessoais e foto',
                3: 'Habilidades e interesses',
                4: 'Assinatura do Termo',
            };

            let current = 1;
            let maxReached = 1;
            let emailVerified = false;

            const email = document.getElementById('email_pessoa');
            const emailError = document.getElementById('email-error');
            const emailSent = document.getElementById('email-enviado');
            const emailOk = document.getElementById('email-confirmado');
            const step1Next = document.querySelector('[data-step-panel="1"] [data-next]');

            const digits = value => String(value ?? '').replace(/\D/g, '');
            const validEmail = value => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);

            function showStep(step) {
                current = Number(step);

                panels.forEach(panel => {
                    panel.classList.toggle('hidden', Number(panel.dataset.stepPanel) !== current);
                });

                navs.forEach(nav => {
                    const n = Number(nav.dataset.navStep);
                    const dot = nav.querySelector('.cadastro-nav-dot');
                    const line = nav.querySelector('[data-step-line]');

                    nav.disabled = n > maxReached;
                    nav.classList.toggle('opacity-40', n > maxReached);
                    nav.classList.toggle('cursor-pointer', n <= maxReached);

                    dot.classList.remove('border-white', 'bg-white', 'text-[#173F2D]', 'border-[#E9A11A]', 'bg-[#E9A11A]');

                    if (n === current) {
                        dot.classList.add('border-white', 'bg-white', 'text-[#173F2D]');
                        dot.textContent = n;
                    } else if (n < current) {
                        dot.classList.add('border-[#E9A11A]', 'bg-[#E9A11A]', 'text-[#173F2D]');
                        dot.textContent = '✓';
                        line?.classList.add('!bg-[#E9A11A]');
                    } else {
                        dot.textContent = n;
                        line?.classList.remove('!bg-[#E9A11A]');
                    }
                });

                document.getElementById('mobile-step-label').textContent = `Passo ${current} de 4`;
                document.getElementById('mobile-step-title').textContent = stepNames[current];
                document.getElementById('mobile-progress').style.width = `${current * 25}%`;

                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            function validateStep1() {
                const ok = validEmail(email.value.trim());
                emailError.classList.toggle('hidden', ok);

                if (!ok) {
                    email.focus();
                    return false;
                }

                return emailVerified;
            }

            function validateStep2() {
                const required = [
                    'nm_pessoa', 'cpf_pessoa', 'tele_pessoa', 'dt_nasc',
                    'genero_pessoa', 'rg_pessoa', 'login_pessoa',
                    'senha_pessoa', 'senha_pessoa_confirmation',
                    'cep_pessoa', 'cidade_pessoa', 'uf_pessoa',
                    'logradouro_pessoa', 'bairro_pessoa'
                ];

                let ok = true;

                required.forEach(id => {
                    const el = document.getElementById(id);
                    let fieldOk = String(el.value ?? '').trim() !== '';

                    if (id === 'cpf_pessoa') fieldOk = digits(el.value).length === 11;
                    if (id === 'tele_pessoa') fieldOk = digits(el.value).length === 11;
                    if (id === 'cep_pessoa') fieldOk = digits(el.value).length === 8;
                    if (id === 'uf_pessoa') fieldOk = el.value.trim().length === 2;
                    if (id === 'senha_pessoa') fieldOk = el.value.length >= 8;
                    if (id === 'senha_pessoa_confirmation') {
                        fieldOk = el.value.length >= 8 && el.value === document.getElementById('senha_pessoa').value;
                    }

                    el.classList.toggle('border-red-500', !fieldOk);
                    if (!fieldOk) ok = false;
                });

                const photo = document.getElementById('foto_perfil');
                const hasPhoto = Boolean(photo.files?.length) || Boolean(document.getElementById('pfp_pessoa_link').value);
                document.getElementById('foto-error').classList.toggle('hidden', hasPhoto);

                if (!hasPhoto) ok = false;

                document.getElementById('step2-error').classList.toggle('hidden', ok);
                return ok;
            }

            function validateStep3() {
                const skills = document.querySelectorAll('input[name="habilidades[]"]:checked').length;
                const causes = document.querySelectorAll('input[name="causas[]"]:checked').length;

                document.getElementById('skills-error').classList.toggle('hidden', skills > 0);
                document.getElementById('causas-error').classList.toggle('hidden', causes > 0);

                return skills > 0 && causes > 0;
            }

            function validateCurrent() {
                if (current === 1) return validateStep1();
                if (current === 2) return validateStep2();
                if (current === 3) return validateStep3();
                return true;
            }

            document.getElementById('btn-enviar-email').addEventListener('click', () => {
                const ok = validEmail(email.value.trim());
                emailError.classList.toggle('hidden', ok);
                if (!ok) return email.focus();

                emailVerified = false;
                emailOk.classList.add('hidden');
                emailSent.classList.remove('hidden');
            });

            document.getElementById('btn-confirmar-email').addEventListener('click', () => {
                emailVerified = true;
                emailSent.classList.add('hidden');
                emailOk.classList.remove('hidden');
                step1Next.disabled = false;
                maxReached = Math.max(maxReached, 2);
                document.getElementById('verified-email-text').textContent = email.value.trim();
            });

            email.addEventListener('input', () => {
                if (emailVerified) {
                    emailVerified = false;
                    emailOk.classList.add('hidden');
                    step1Next.disabled = true;
                }
            });

            document.querySelectorAll('[data-next]').forEach(btn => {
                btn.addEventListener('click', () => {
                    if (!validateCurrent()) return;
                    maxReached = Math.max(maxReached, current + 1);
                    showStep(current + 1);
                });
            });

            document.querySelectorAll('[data-prev]').forEach(btn => {
                btn.addEventListener('click', () => showStep(Math.max(1, current - 1)));
            });

            navs.forEach(nav => {
                nav.addEventListener('click', () => {
                    const step = Number(nav.dataset.navStep);
                    if (step <= maxReached) showStep(step);
                });
            });

            document.getElementById('cpf_pessoa').addEventListener('input', e => {
                let v = digits(e.target.value).slice(0, 11);
                v = v.replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                e.target.value = v;
            });

            document.getElementById('tele_pessoa').addEventListener('input', e => {
                let v = digits(e.target.value).slice(0, 11);
                v = v.replace(/^(\d{2})(\d)/, '($1) $2').replace(/(\d{5})(\d{1,4})$/, '$1-$2');
                e.target.value = v;
            });

            document.getElementById('cep_pessoa').addEventListener('input', e => {
                let v = digits(e.target.value).slice(0, 8);
                if (v.length > 5) v = v.replace(/^(\d{5})(\d{1,3})$/, '$1-$2');
                e.target.value = v;

                const raw = digits(v);
                if (raw.length === 8) {
                    const map = @json($cepMock);
                    const endereco = map[raw] ?? map.default;
                    if (endereco) {
                        document.getElementById('cidade_pessoa').value = endereco.cidade ?? '';
                        document.getElementById('uf_pessoa').value = endereco.uf ?? '';
                        if (!document.getElementById('logradouro_pessoa').value) document.getElementById('logradouro_pessoa').value = endereco.logradouro ?? '';
                        if (!document.getElementById('bairro_pessoa').value) document.getElementById('bairro_pessoa').value = endereco.bairro ?? '';
                    }
                }
            });

            document.getElementById('uf_pessoa').addEventListener('input', e => {
                e.target.value = e.target.value.replace(/[^a-zA-Z]/g, '').toUpperCase().slice(0, 2);
            });

            document.getElementById('foto_perfil').addEventListener('change', e => {
                const file = e.target.files?.[0];
                if (!file) return;

                document.getElementById('foto-title').textContent = `Selecionado: ${file.name}`;
                document.getElementById('pfp_pessoa_link').value = `mock://foto/${encodeURIComponent(file.name)}`;
                document.getElementById('foto-error').classList.add('hidden');
            });

            document.querySelectorAll('[data-link-target]').forEach(input => {
                input.addEventListener('change', () => {
                    const file = input.files?.[0];
                    if (!file) return;
                    document.getElementById(input.dataset.linkTarget).value = `mock://documento/${encodeURIComponent(file.name)}`;
                });
            });

            document.querySelectorAll('[data-skill-check]').forEach(check => {
                check.addEventListener('change', () => {
                    const card = check.closest('[data-skill-card]');
                    const box = card.querySelector('[data-skill-level]');
                    const select = box.querySelector('select');

                    box.classList.toggle('hidden', !check.checked);
                    select.disabled = !check.checked;

                    card.classList.toggle('border-[#2D6A4F]', check.checked);
                    card.classList.toggle('bg-[#EEF7F2]', check.checked);
                });
            });

            document.getElementById('btn-finalizar').addEventListener('click', () => {
                const accepted = document.getElementById('aceitou_termos').checked;
                document.getElementById('termo-error').classList.toggle('hidden', accepted);

                if (!accepted) return;

                if (!validateStep2()) return showStep(2);
                if (!validateStep3()) return showStep(3);

                const button = document.getElementById('btn-finalizar');
                button.disabled = true;
                button.textContent = 'Finalizando...';

                document.getElementById('cadastro-form').submit();
            });

            @if ($errors->any())
                emailVerified = true;
                maxReached = 4;
                showStep(2);
            @else
                showStep(1);
            @endif
        });
    </script>
</body>
</html>
