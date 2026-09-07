<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Política de Privacidade | Ajudaê</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>

<body class="min-h-screen bg-[#F7F5F0] text-[#24271F]">

    @include('/components/navbar')

    <main class="px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
        <div class="mx-auto grid w-full max-w-6xl gap-8 lg:grid-cols-[250px_minmax(0,1fr)]">

            {{-- SUMÁRIO --}}
            <aside class="lg:sticky lg:top-24 lg:self-start">
                <div class="rounded-2xl border border-[#DED9CE] bg-white p-5 shadow-sm">
                    <p class="font-poppins text-[11px] font-semibold uppercase tracking-[0.14em] text-[#918B80]">
                        Privacidade
                    </p>

                    <h2 class="mt-2 font-fraunces text-2xl font-bold text-[#173F2D]">
                        Política de Privacidade
                    </h2>

                    <div class="mt-4 rounded-xl bg-[#EEF7F2] p-3 font-poppins text-[11px] leading-5 text-[#315D47]">
                        <strong class="block">Versão 1.0</strong>
                        Vigência: 06/09/2026
                    </div>

                    <nav class="mt-5 space-y-1" aria-label="Sumário da Política de Privacidade">
                        @foreach ($secoes as $secao)
                            <a
                                href="#{{ $secao['id'] }}"
                                data-privacidade-link="{{ $secao['id'] }}"
                                class="privacidade-link block rounded-lg px-3 py-2 font-poppins text-xs text-[#6E6A62] transition hover:bg-[#F3F1EA] hover:text-[#173F2D]"
                            >
                                {{ $secao['numero'] }}. {{ $secao['titulo'] }}
                            </a>
                        @endforeach
                    </nav>
                </div>
            </aside>

            {{-- CONTEÚDO --}}
            <article class="min-w-0">
                <header class="rounded-2xl border border-[#DED9CE] bg-white p-6 shadow-sm sm:p-8">
                    <p class="font-poppins text-xs font-semibold uppercase tracking-[0.15em] text-[#D99110]">
                        Ajudaê
                    </p>

                    <h1 class="mt-2 font-fraunces text-3xl font-bold text-[#173F2D] sm:text-4xl">
                        Política de Privacidade
                    </h1>

                    <p class="mt-4 max-w-3xl font-poppins text-sm leading-7 text-[#66625B]">
                        Esta Política explica como o Ajudaê coleta, utiliza, armazena,
                        compartilha e protege dados pessoais dos usuários, além de apresentar
                        os direitos dos titulares e os canais de contato disponíveis.
                    </p>

                    <div class="mt-6 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-xl bg-[#F7F5F0] p-4">
                            <span class="font-poppins text-[10px] uppercase tracking-wide text-[#918B80]">
                                Versão
                            </span>
                            <strong class="mt-1 block text-sm text-[#27392F]">
                                1.0
                            </strong>
                        </div>

                        <div class="rounded-xl bg-[#F7F5F0] p-4">
                            <span class="font-poppins text-[10px] uppercase tracking-wide text-[#918B80]">
                                Vigência
                            </span>
                            <strong class="mt-1 block text-sm text-[#27392F]">
                                06/09/2026
                            </strong>
                        </div>

                        <div class="rounded-xl bg-[#FFF8E8] p-4">
                            <span class="font-poppins text-[10px] uppercase tracking-wide text-[#9A7A37]">
                                Faixa etária
                            </span>
                            <strong class="mt-1 block text-sm text-[#6C5220]">
                                Exclusivo para maiores de 18 anos
                            </strong>
                        </div>
                    </div>
                </header>

                <div class="mt-6 space-y-5">

                    <section id="controlador" data-privacidade-section class="scroll-mt-24 rounded-2xl border border-[#DED9CE] bg-white p-6 sm:p-8">
                        <h2 class="font-fraunces text-2xl font-bold text-[#173F2D]">
                            1. Quem é responsável pelos dados
                        </h2>

                        <div class="mt-4 space-y-4 font-poppins text-sm leading-7 text-[#55534D]">
                            <p>
                                Para fins desta Política, o Ajudaê é a plataforma responsável
                                pelas decisões relacionadas ao tratamento de dados pessoais
                                realizados diretamente em seu ambiente digital.
                            </p>

                            <p>
                                Enquanto o Ajudaê permanecer como projeto acadêmico, sua identificação
                                institucional poderá ser apresentada como:
                            </p>

                            <div class="rounded-xl bg-[#F7F5F0] p-4">
                                <strong class="block text-[#2B3D32]">Ajudaê</strong>
                                <span class="block">Projeto acadêmico — Técnico em Desenvolvimento de Sistemas</span>
                                <span class="block">Etec de Guarulhos</span>
                                <span class="block">E-mail: [definir e-mail oficial]</span>
                            </div>

                            <p>
                                Caso o projeto passe a operar comercialmente ou por uma pessoa jurídica,
                                esta seção deverá ser atualizada com a identificação correta do controlador.
                            </p>
                        </div>
                    </section>

                    <section id="dados-coletados" data-privacidade-section class="scroll-mt-24 rounded-2xl border border-[#DED9CE] bg-white p-6 sm:p-8">
                        <h2 class="font-fraunces text-2xl font-bold text-[#173F2D]">
                            2. Quais dados pessoais podem ser coletados
                        </h2>

                        <div class="mt-4 space-y-5 font-poppins text-sm leading-7 text-[#55534D]">
                            <div>
                                <h3 class="font-outfit text-base font-semibold text-[#2B3D32]">
                                    Dados fornecidos pelo usuário
                                </h3>

                                <ul class="mt-2 list-disc space-y-1 pl-5">
                                    <li>nome completo;</li>
                                    <li>CPF e RG;</li>
                                    <li>data de nascimento;</li>
                                    <li>gênero;</li>
                                    <li>e-mail e telefone;</li>
                                    <li>CEP, logradouro, bairro, cidade, estado e complemento;</li>
                                    <li>nome de usuário e credenciais de acesso;</li>
                                    <li>foto de perfil;</li>
                                    <li>documentos e links de documentos, quando exigidos;</li>
                                    <li>habilidades, interesses, causas e recursos disponíveis;</li>
                                    <li>informações inseridas em candidaturas ou perfil.</li>
                                </ul>
                            </div>

                            <div>
                                <h3 class="font-outfit text-base font-semibold text-[#2B3D32]">
                                    Dados gerados pelo uso da plataforma
                                </h3>

                                <ul class="mt-2 list-disc space-y-1 pl-5">
                                    <li>eventos visualizados ou organizados;</li>
                                    <li>candidaturas realizadas;</li>
                                    <li>aprovações, recusas e cancelamentos;</li>
                                    <li>histórico de participação;</li>
                                    <li>presença e ausência em atividades;</li>
                                    <li>XP, níveis, avaliações e certificados;</li>
                                    <li>registros de notificações;</li>
                                    <li>datas e horários de determinadas ações.</li>
                                </ul>
                            </div>

                            <div>
                                <h3 class="font-outfit text-base font-semibold text-[#2B3D32]">
                                    Dados técnicos
                                </h3>

                                <ul class="mt-2 list-disc space-y-1 pl-5">
                                    <li>endereço IP;</li>
                                    <li>user agent;</li>
                                    <li>navegador e plataforma/dispositivo;</li>
                                    <li>data e horário de acesso ou aceite;</li>
                                    <li>informações de sessão e autenticação;</li>
                                    <li>registros técnicos necessários à segurança e auditoria.</li>
                                </ul>
                            </div>
                        </div>
                    </section>

                    <section id="finalidades" data-privacidade-section class="scroll-mt-24 rounded-2xl border border-[#DED9CE] bg-white p-6 sm:p-8">
                        <h2 class="font-fraunces text-2xl font-bold text-[#173F2D]">
                            3. Para que os dados são utilizados
                        </h2>

                        <div class="mt-4 space-y-4 font-poppins text-sm leading-7 text-[#55534D]">
                            <p>Os dados poderão ser utilizados para:</p>

                            <ul class="list-disc space-y-1 pl-5">
                                <li>criar e manter contas de usuários;</li>
                                <li>autenticar acessos e proteger contas;</li>
                                <li>permitir candidaturas e participação em eventos;</li>
                                <li>permitir que ONGs e organizadores administrem eventos e candidatos;</li>
                                <li>recomendar oportunidades com base no perfil;</li>
                                <li>registrar histórico, presença, XP, níveis e certificados;</li>
                                <li>enviar notificações relacionadas ao funcionamento da plataforma;</li>
                                <li>prevenir fraude, abuso e uso indevido;</li>
                                <li>atender solicitações dos titulares;</li>
                                <li>cumprir obrigações legais e regulatórias aplicáveis;</li>
                                <li>melhorar a segurança, estabilidade e experiência da plataforma.</li>
                            </ul>
                        </div>
                    </section>

                    <section id="bases-legais" data-privacidade-section class="scroll-mt-24 rounded-2xl border border-[#DED9CE] bg-white p-6 sm:p-8">
                        <h2 class="font-fraunces text-2xl font-bold text-[#173F2D]">
                            4. Bases legais para o tratamento
                        </h2>

                        <div class="mt-4 space-y-4 font-poppins text-sm leading-7 text-[#55534D]">
                            <p>
                                O tratamento de dados pessoais poderá ocorrer com fundamento nas bases legais
                                previstas na legislação aplicável, conforme a finalidade específica.
                            </p>

                            <p>
                                Entre as bases que podem ser utilizadas estão o cumprimento de obrigação legal
                                ou regulatória, a execução de procedimentos relacionados ao uso da plataforma,
                                o legítimo interesse quando aplicável e o consentimento quando efetivamente
                                necessário.
                            </p>

                            <div class="rounded-xl border border-[#E0D6BD] bg-[#FFF9ED] p-4">
                                <strong class="block text-[#6C5220]">
                                    Importante
                                </strong>
                                <p class="mt-1 text-[#6E6250]">
                                    O simples aceite desta Política não significa que todo tratamento de dados
                                    seja baseado em consentimento. Cada operação deve possuir fundamento adequado
                                    à sua finalidade.
                                </p>
                            </div>
                        </div>
                    </section>

                    <section id="compartilhamento" data-privacidade-section class="scroll-mt-24 rounded-2xl border border-[#DED9CE] bg-white p-6 sm:p-8">
                        <h2 class="font-fraunces text-2xl font-bold text-[#173F2D]">
                            5. Compartilhamento de dados
                        </h2>

                        <div class="mt-4 space-y-4 font-poppins text-sm leading-7 text-[#55534D]">
                            <p>
                                Alguns dados poderão ser compartilhados com ONGs ou organizadores quando isso
                                for necessário para analisar candidaturas, administrar eventos ou identificar
                                participantes.
                            </p>

                            <p>
                                O Ajudaê deverá limitar esse compartilhamento ao mínimo necessário para a finalidade
                                correspondente. Dados como CPF, RG e endereço completo não devem ser disponibilizados
                                de forma indiscriminada a qualquer usuário da plataforma.
                            </p>

                            <p>
                                Dados também poderão ser tratados por prestadores de serviços tecnológicos
                                necessários ao funcionamento da plataforma, como hospedagem, infraestrutura,
                                armazenamento, envio de e-mails e ferramentas de segurança.
                            </p>

                            <p>
                                O Ajudaê poderá ainda compartilhar informações quando necessário para cumprir
                                obrigação legal, ordem judicial ou solicitação válida de autoridade competente.
                            </p>
                        </div>
                    </section>

                    <section id="ongs-organizadores" data-privacidade-section class="scroll-mt-24 rounded-2xl border border-[#DED9CE] bg-white p-6 sm:p-8">
                        <h2 class="font-fraunces text-2xl font-bold text-[#173F2D]">
                            6. ONGs e organizadores
                        </h2>

                        <div class="mt-4 space-y-4 font-poppins text-sm leading-7 text-[#55534D]">
                            <p>
                                ONGs e organizadores que recebam dados de voluntários por meio da plataforma
                                deverão utilizar essas informações exclusivamente para finalidades relacionadas
                                às atividades e candidaturas sob sua responsabilidade.
                            </p>

                            <p>
                                É vedado utilizar dados obtidos no Ajudaê para venda, publicidade não autorizada,
                                prospecção externa indevida, discriminação ou qualquer finalidade incompatível
                                com a atividade divulgada.
                            </p>

                            <p>
                                Quando uma organização tratar dados por conta própria fora da plataforma,
                                poderá possuir responsabilidades próprias relacionadas à proteção desses dados.
                            </p>
                        </div>
                    </section>

                    <section id="maiores" data-privacidade-section class="scroll-mt-24 rounded-2xl border border-[#E2C98E] bg-[#FFF9EB] p-6 sm:p-8">
                        <div class="flex gap-3">
                            <span class="text-2xl">🔞</span>
                            <div>
                                <h2 class="font-fraunces text-2xl font-bold text-[#173F2D]">
                                    7. Menores de idade
                                </h2>

                                <div class="mt-4 space-y-4 font-poppins text-sm leading-7 text-[#55534D]">
                                    <p>
                                        O Ajudaê é destinado exclusivamente a pessoas com
                                        <strong>18 anos ou mais</strong>.
                                    </p>

                                    <p>
                                        A plataforma não realiza intencionalmente o cadastro de crianças
                                        ou adolescentes. Caso seja identificada uma conta pertencente a pessoa
                                        menor de idade, poderão ser adotadas medidas para bloqueio ou exclusão
                                        da conta e tratamento adequado dos dados envolvidos.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section id="armazenamento" data-privacidade-section class="scroll-mt-24 rounded-2xl border border-[#DED9CE] bg-white p-6 sm:p-8">
                        <h2 class="font-fraunces text-2xl font-bold text-[#173F2D]">
                            8. Armazenamento e segurança
                        </h2>

                        <div class="mt-4 space-y-4 font-poppins text-sm leading-7 text-[#55534D]">
                            <p>
                                O Ajudaê poderá armazenar dados em servidores, bancos de dados e serviços de
                                infraestrutura utilizados para operar a plataforma.
                            </p>

                            <p>
                                Serão adotadas medidas técnicas e administrativas razoáveis para reduzir riscos
                                de acesso não autorizado, perda, alteração, divulgação ou uso indevido de dados.
                            </p>

                            <p>
                                Nenhum sistema é completamente imune a incidentes. Caso ocorra evento relevante
                                envolvendo dados pessoais, deverão ser avaliadas as medidas cabíveis conforme
                                a legislação aplicável.
                            </p>
                        </div>
                    </section>

                    <section id="retencao" data-privacidade-section class="scroll-mt-24 rounded-2xl border border-[#DED9CE] bg-white p-6 sm:p-8">
                        <h2 class="font-fraunces text-2xl font-bold text-[#173F2D]">
                            9. Retenção e exclusão de dados
                        </h2>

                        <div class="mt-4 space-y-4 font-poppins text-sm leading-7 text-[#55534D]">
                            <p>
                                Os dados serão mantidos pelo período necessário para cumprir as finalidades
                                informadas nesta Política, atender obrigações legais, proteger direitos ou
                                manter registros necessários à segurança da plataforma.
                            </p>

                            <p>
                                O usuário poderá solicitar a exclusão de sua conta. O Ajudaê poderá utilizar
                                período de processamento antes da exclusão definitiva, especialmente quando
                                houver atividades em andamento ou necessidade de preservação temporária de registros.
                            </p>

                            <p>
                                A exclusão da conta não implica necessariamente a eliminação imediata de todo
                                e qualquer registro quando a manutenção for permitida ou exigida por obrigação
                                legal, exercício regular de direitos, segurança ou outra hipótese legítima.
                            </p>
                        </div>
                    </section>

                    <section id="direitos" data-privacidade-section class="scroll-mt-24 rounded-2xl border border-[#DED9CE] bg-white p-6 sm:p-8">
                        <h2 class="font-fraunces text-2xl font-bold text-[#173F2D]">
                            10. Direitos dos titulares
                        </h2>

                        <div class="mt-4 space-y-4 font-poppins text-sm leading-7 text-[#55534D]">
                            <p>
                                Nos termos da legislação aplicável, o titular poderá exercer direitos relacionados
                                aos seus dados pessoais, observados os requisitos e limites legais.
                            </p>

                            <p>Esses direitos podem incluir, conforme o caso:</p>

                            <ul class="list-disc space-y-1 pl-5">
                                <li>confirmação da existência de tratamento;</li>
                                <li>acesso aos dados;</li>
                                <li>correção de dados incompletos, inexatos ou desatualizados;</li>
                                <li>informações sobre compartilhamento;</li>
                                <li>eliminação de dados tratados com consentimento, quando aplicável;</li>
                                <li>revogação de consentimento, quando essa for a base utilizada;</li>
                                <li>oposição ao tratamento em hipóteses previstas em lei;</li>
                                <li>outras solicitações asseguradas pela legislação.</li>
                            </ul>
                        </div>
                    </section>

                    <section id="solicitacoes" data-privacidade-section class="scroll-mt-24 rounded-2xl border border-[#DED9CE] bg-white p-6 sm:p-8">
                        <h2 class="font-fraunces text-2xl font-bold text-[#173F2D]">
                            11. Como exercer seus direitos
                        </h2>

                        <div class="mt-4 space-y-4 font-poppins text-sm leading-7 text-[#55534D]">
                            <p>
                                O titular poderá entrar em contato pelos canais oficiais do Ajudaê para realizar
                                solicitações relacionadas aos seus dados pessoais.
                            </p>

                            <div class="rounded-xl bg-[#F7F5F0] p-4">
                                <strong class="block text-[#2B3D32]">
                                    Canal de privacidade
                                </strong>
                                <span class="block">E-mail: [definir e-mail de privacidade]</span>
                            </div>

                            <p>
                                Para proteger a conta e os dados do próprio titular, o Ajudaê poderá solicitar
                                informações necessárias para confirmar a identidade antes de atender determinadas
                                solicitações.
                            </p>
                        </div>
                    </section>

                    <section id="cookies" data-privacidade-section class="scroll-mt-24 rounded-2xl border border-[#DED9CE] bg-white p-6 sm:p-8">
                        <h2 class="font-fraunces text-2xl font-bold text-[#173F2D]">
                            12. Cookies e tecnologias semelhantes
                        </h2>

                        <div class="mt-4 space-y-4 font-poppins text-sm leading-7 text-[#55534D]">
                            <p>
                                O Ajudaê poderá utilizar cookies ou tecnologias semelhantes quando necessários
                                para autenticação, manutenção de sessão, segurança e funcionamento da plataforma.
                            </p>

                            <p>
                                Caso futuramente sejam utilizados cookies analíticos, publicitários ou outras
                                tecnologias não essenciais, esta Política e os mecanismos de preferência deverão
                                ser atualizados conforme necessário.
                            </p>
                        </div>
                    </section>

                    <section id="alteracoes" data-privacidade-section class="scroll-mt-24 rounded-2xl border border-[#DED9CE] bg-white p-6 sm:p-8">
                        <h2 class="font-fraunces text-2xl font-bold text-[#173F2D]">
                            13. Alterações desta Política
                        </h2>

                        <div class="mt-4 space-y-4 font-poppins text-sm leading-7 text-[#55534D]">
                            <p>
                                Esta Política poderá ser atualizada para refletir mudanças na plataforma,
                                em suas funcionalidades, nos tratamentos realizados ou na legislação aplicável.
                            </p>

                            <p>
                                Cada versão deverá possuir identificação e data de vigência. Alterações relevantes
                                poderão ser comunicadas aos usuários por meio da própria plataforma ou por outros
                                canais adequados.
                            </p>
                        </div>
                    </section>

                    <section id="contato" data-privacidade-section class="scroll-mt-24 rounded-2xl border border-[#DED9CE] bg-white p-6 sm:p-8">
                        <h2 class="font-fraunces text-2xl font-bold text-[#173F2D]">
                            14. Contato
                        </h2>

                        <div class="mt-4 space-y-4 font-poppins text-sm leading-7 text-[#55534D]">
                            <p>
                                Dúvidas, solicitações ou questões relacionadas à privacidade podem ser encaminhadas
                                pelos canais oficiais disponibilizados pelo Ajudaê.
                            </p>

                            <div class="rounded-xl bg-[#F7F5F0] p-4">
                                <strong class="block text-[#2B3D32]">Ajudaê</strong>
                                <span class="block">Projeto acadêmico — Técnico em Desenvolvimento de Sistemas</span>
                                <span class="block">Etec de Guarulhos</span>
                                <span class="block">E-mail: [definir e-mail oficial]</span>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-2xl bg-[#173F2D] p-6 text-white sm:p-8">
                        <p class="font-poppins text-xs uppercase tracking-[0.14em] text-white/60">
                            Transparência
                        </p>

                        <h2 class="mt-2 font-fraunces text-2xl font-bold">
                            Política de Privacidade — versão 1.0
                        </h2>

                        <p class="mt-4 max-w-3xl font-poppins text-sm leading-7 text-white/75">
                            Ao utilizar o Ajudaê, o usuário declara ter tido acesso a esta Política
                            e estar ciente das práticas de tratamento de dados descritas neste documento.
                        </p>
                    </section>

                </div>
            </article>
        </div>
    </main>

    <footer class="mt-10 bg-[#173F2D] px-4 py-8 text-white">
        <div class="mx-auto flex max-w-6xl flex-col gap-4 font-poppins text-xs text-white/70 sm:flex-row sm:items-center sm:justify-between">
            <span>© 2026 Ajudaê</span>

            <div class="flex flex-wrap gap-4">
                <a href="{{ route('termos') }}" class="transition hover:text-white">
                    Termos de Uso
                </a>

                <a href="{{ route('privacidade') }}" class="transition hover:text-white">
                    Política de Privacidade
                </a>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sections = [...document.querySelectorAll('[data-privacidade-section]')];
            const links = [...document.querySelectorAll('[data-privacidade-link]')];

            const setActive = id => {
                links.forEach(link => {
                    const active = link.dataset.privacidadeLink === id;

                    link.classList.toggle('bg-[#EEF7F2]', active);
                    link.classList.toggle('text-[#173F2D]', active);
                    link.classList.toggle('font-semibold', active);
                });
            };

            const observer = new IntersectionObserver(
                entries => {
                    const visible = entries
                        .filter(entry => entry.isIntersecting)
                        .sort((a, b) => b.intersectionRatio - a.intersectionRatio);

                    if (visible.length) {
                        setActive(visible[0].target.id);
                    }
                },
                {
                    rootMargin: '-20% 0px -65% 0px',
                    threshold: [0.05, 0.25, 0.5]
                }
            );

            sections.forEach(section => observer.observe(section));

            if (sections.length) {
                setActive(sections[0].id);
            }
        });
    </script>

</body>
</html>
