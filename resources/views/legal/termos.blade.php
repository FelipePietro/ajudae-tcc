<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termos de Uso | Ajudae</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>
<body class="min-h-screen bg-[#F7F5F0] text-[#24271F]">

    @include('/components/navbar')

    <main class="px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
        <div class="mx-auto grid w-full max-w-6xl gap-8 lg:grid-cols-[250px_minmax(0,1fr)]">

            <aside class="lg:sticky lg:top-24 lg:self-start">
                <div class="rounded-2xl border border-[#DED9CE] bg-white p-5 shadow-sm">
                    <p class="font-poppins text-[11px] font-semibold uppercase tracking-[0.14em] text-[#918B80]">Documento legal</p>
                    <h2 class="mt-2 font-fraunces text-2xl font-bold text-[#173F2D]">Termos de Uso</h2>

                    <div class="mt-4 rounded-xl bg-[#EEF7F2] p-3 font-poppins text-[11px] leading-5 text-[#315D47]">
                        <strong class="block">Versão 1.0</strong>
                        Vigência: 06/09/2026
                    </div>

                    <nav class="mt-5 space-y-1" aria-label="Sumário dos Termos">
                        @foreach ($secoes as $secao)
                            <a href="#{{ $secao['id'] }}"
                               data-termo-link="{{ $secao['id'] }}"
                               class="termo-link block rounded-lg px-3 py-2 font-poppins text-xs text-[#6E6A62] transition hover:bg-[#F3F1EA] hover:text-[#173F2D]">
                                {{ $secao['numero'] }}. {{ $secao['titulo'] }}
                            </a>
                        @endforeach
                    </nav>
                </div>
            </aside>

            <article class="min-w-0">
                <header class="rounded-2xl border border-[#DED9CE] bg-white p-6 shadow-sm sm:p-8">
                    <p class="font-poppins text-xs font-semibold uppercase tracking-[0.15em] text-[#D99110]">Ajudae</p>
                    <h1 class="mt-2 font-fraunces text-3xl font-bold text-[#173F2D] sm:text-4xl">Termos de Uso da Plataforma Ajudae</h1>
                    <p class="mt-4 max-w-3xl font-poppins text-sm leading-7 text-[#66625B]">
                        Estes Termos de Uso regulam o acesso e a utilização da plataforma Ajudae. Ao criar uma conta ou utilizar a plataforma, o usuário declara ter lido, compreendido e concordado com as condições abaixo.
                    </p>
                </header>

                <div class="mt-6 space-y-5 font-poppins text-sm leading-7 text-[#55534D]">
                    <section id="sobre" data-termo-section class="scroll-mt-24 rounded-2xl border border-[#DED9CE] bg-white p-6 sm:p-8">
                        <h2 class="font-fraunces text-2xl font-bold text-[#173F2D]">1. Sobre o Ajudae</h2>
                        <p class="mt-4">O Ajudae é uma plataforma digital destinada a facilitar a conexão entre pessoas interessadas em realizar atividades voluntárias e organizações ou responsáveis por eventos, projetos e ações de interesse social.</p>
                        <p class="mt-3">A plataforma poderá disponibilizar criação e gerenciamento de perfis, divulgação de oportunidades, candidaturas, aprovação de participantes, histórico de atividades, avaliações, XP, certificados e notificações.</p>
                    </section>

                    <section id="usuarios" data-termo-section class="scroll-mt-24 rounded-2xl border border-[#DED9CE] bg-white p-6 sm:p-8">
                        <h2 class="font-fraunces text-2xl font-bold text-[#173F2D]">2. Tipos de usuários</h2>
                        <p class="mt-4"><strong>Voluntário:</strong> pessoa física cadastrada para participar de oportunidades de voluntariado.</p>
                        <p class="mt-2"><strong>Organizador:</strong> pessoa física autorizada a organizar ou gerenciar determinadas atividades.</p>
                        <p class="mt-2"><strong>ONG:</strong> organização cadastrada para divulgar, organizar e administrar ações voluntárias.</p>
                        <p class="mt-2"><strong>Administrador:</strong> usuário autorizado a executar atividades de gestão, moderação e administração da plataforma.</p>
                    </section>

                    <section id="idade" data-termo-section class="scroll-mt-24 rounded-2xl border border-[#E2C98E] bg-[#FFF9EB] p-6 sm:p-8">
                        <h2 class="font-fraunces text-2xl font-bold text-[#173F2D]">3. Requisito de idade</h2>
                        <p class="mt-4">O Ajudae é destinado exclusivamente a pessoas com <strong>18 anos de idade ou mais</strong>.</p>
                        <p class="mt-3">Ao criar uma conta, o usuário declara possuir pelo menos 18 anos completos na data do cadastro. Contas identificadas como pertencentes a menores de idade poderão ser bloqueadas ou excluídas.</p>
                        <p class="mt-3">Organizações e organizadores não poderão utilizar a plataforma para recrutar ou cadastrar menores de idade como voluntários.</p>
                    </section>

                    <section id="cadastro" data-termo-section class="scroll-mt-24 rounded-2xl border border-[#DED9CE] bg-white p-6 sm:p-8">
                        <h2 class="font-fraunces text-2xl font-bold text-[#173F2D]">4. Cadastro e informações fornecidas</h2>
                        <p class="mt-4">O usuário compromete-se a fornecer informações verdadeiras, completas e atualizadas e a não utilizar identidade, documentos ou dados de terceiros sem autorização.</p>
                        <p class="mt-3">O usuário é responsável por manter suas credenciais protegidas e por comunicar eventual uso não autorizado de sua conta.</p>
                    </section>

                    <section id="voluntariado" data-termo-section class="scroll-mt-24 rounded-2xl border border-[#DED9CE] bg-white p-6 sm:p-8">
                        <h2 class="font-fraunces text-2xl font-bold text-[#173F2D]">5. Serviço voluntário</h2>
                        <p class="mt-4">As atividades divulgadas na plataforma poderão possuir natureza voluntária e não remunerada.</p>
                        <p class="mt-3">Estes Termos de Uso regulam a relação entre o usuário e a plataforma Ajudae, mas não substituem eventual termo de adesão ao serviço voluntário celebrado entre o voluntário e a entidade responsável pela atividade.</p>
                    </section>

                    <section id="eventos" data-termo-section class="scroll-mt-24 rounded-2xl border border-[#DED9CE] bg-white p-6 sm:p-8">
                        <h2 class="font-fraunces text-2xl font-bold text-[#173F2D]">6. Eventos e oportunidades</h2>
                        <p class="mt-4">Organizações e organizadores são responsáveis pelas informações inseridas nos eventos que publicarem, incluindo descrição, local, horário, vagas, requisitos e demais condições de participação.</p>
                        <p class="mt-3">É proibida a publicação de atividades ilícitas, enganosas, fraudulentas ou discriminatórias.</p>
                    </section>

                    <section id="candidaturas" data-termo-section class="scroll-mt-24 rounded-2xl border border-[#DED9CE] bg-white p-6 sm:p-8">
                        <h2 class="font-fraunces text-2xl font-bold text-[#173F2D]">7. Candidaturas e participação</h2>
                        <p class="mt-4">A candidatura não garante participação automática. O responsável pela atividade poderá aprovar ou recusar candidatos de acordo com os critérios divulgados.</p>
                        <p class="mt-3">A presença poderá ser registrada e utilizada para histórico, XP, avaliações e emissão de certificados.</p>
                    </section>

                    <section id="gamificacao" data-termo-section class="scroll-mt-24 rounded-2xl border border-[#DED9CE] bg-white p-6 sm:p-8">
                        <h2 class="font-fraunces text-2xl font-bold text-[#173F2D]">8. XP, níveis e certificados</h2>
                        <p class="mt-4">XP, níveis, badges, rankings e mecanismos semelhantes possuem finalidade exclusivamente interna à plataforma e não representam moeda, remuneração, crédito financeiro ou direito patrimonial.</p>
                        <p class="mt-3">Certificados, quando disponibilizados, dependerão da confirmação de participação pelo responsável pelo evento.</p>
                    </section>

                    <section id="conduta" data-termo-section class="scroll-mt-24 rounded-2xl border border-[#DED9CE] bg-white p-6 sm:p-8">
                        <h2 class="font-fraunces text-2xl font-bold text-[#173F2D]">9. Conduta e uso proibido</h2>
                        <p class="mt-4">É proibido utilizar a plataforma para fraude, assédio, discriminação, violência, acesso indevido, manipulação de dados, exploração de vulnerabilidades ou interferência no funcionamento do sistema.</p>
                    </section>

                    <section id="responsabilidades" data-termo-section class="scroll-mt-24 rounded-2xl border border-[#DED9CE] bg-white p-6 sm:p-8">
                        <h2 class="font-fraunces text-2xl font-bold text-[#173F2D]">10. Responsabilidades</h2>
                        <p class="mt-4">Salvo quando expressamente indicado de forma diferente, o Ajudae atua como plataforma de intermediação e organização digital.</p>
                        <p class="mt-3">A organização responsável pelo evento permanece responsável pela realização, estrutura, segurança e obrigações relacionadas à atividade.</p>
                    </section>

                    <section id="privacidade" data-termo-section class="scroll-mt-24 rounded-2xl border border-[#DED9CE] bg-white p-6 sm:p-8">
                        <h2 class="font-fraunces text-2xl font-bold text-[#173F2D]">11. Privacidade e dados pessoais</h2>
                        <p class="mt-4">O Ajudae poderá tratar dados pessoais necessários ao funcionamento da plataforma, incluindo dados cadastrais, contato, identificação, histórico de participação e registros técnicos.</p>
                        <p class="mt-3">Informações detalhadas deverão constar na Política de Privacidade do Ajudae.</p>
                    </section>

                    <section id="aceite" data-termo-section class="scroll-mt-24 rounded-2xl border border-[#DED9CE] bg-white p-6 sm:p-8">
                        <h2 class="font-fraunces text-2xl font-bold text-[#173F2D]">12. Aceite eletrônico</h2>
                        <p class="mt-4">Ao selecionar a opção de aceite durante o cadastro, o usuário manifesta sua concordância com a versão vigente destes Termos.</p>
                        <p class="mt-3">Para fins de segurança e rastreabilidade, a plataforma poderá registrar identificação do usuário, data e horário, endereço IP, user agent, plataforma/dispositivo, versão do termo, URL do documento e hash correspondente.</p>
                    </section>

                    <section id="contas" data-termo-section class="scroll-mt-24 rounded-2xl border border-[#DED9CE] bg-white p-6 sm:p-8">
                        <h2 class="font-fraunces text-2xl font-bold text-[#173F2D]">13. Suspensão e exclusão de contas</h2>
                        <p class="mt-4">O Ajudae poderá advertir, restringir, suspender ou excluir contas que violem estes Termos, pratiquem fraude, utilizem informações falsas ou representem risco à segurança.</p>
                        <p class="mt-3">O usuário poderá solicitar a exclusão de sua própria conta, observadas as regras de processamento e eventuais pendências relacionadas a atividades em andamento.</p>
                    </section>

                    <section id="alteracoes" data-termo-section class="scroll-mt-24 rounded-2xl border border-[#DED9CE] bg-white p-6 sm:p-8">
                        <h2 class="font-fraunces text-2xl font-bold text-[#173F2D]">14. Alterações destes Termos</h2>
                        <p class="mt-4">O Ajudae poderá atualizar estes Termos para refletir mudanças legais, de segurança, operação ou novas funcionalidades.</p>
                        <p class="mt-3">Cada versão deverá possuir identificação e data de vigência. Quando necessário, poderá ser solicitado novo aceite.</p>
                    </section>

                    <section id="contato" data-termo-section class="scroll-mt-24 rounded-2xl border border-[#DED9CE] bg-white p-6 sm:p-8">
                        <h2 class="font-fraunces text-2xl font-bold text-[#173F2D]">15. Contato</h2>
                        <p class="mt-4">Dúvidas sobre estes Termos poderão ser encaminhadas pelos canais oficiais do Ajudae.</p>
                        <div class="mt-4 rounded-xl bg-[#F7F5F0] p-4">
                            <strong class="block text-[#2B3D32]">Ajudae</strong>
                            <span class="block">Projeto acadêmico — Técnico em Desenvolvimento de Sistemas</span>
                            <span class="block">Etec de Guarulhos</span>
                            <span class="block">E-mail: [definir e-mail oficial]</span>
                        </div>
                    </section>
                </div>
            </article>
        </div>
    </main>

    <footer class="mt-10 bg-[#173F2D] px-4 py-8 text-white">
        <div class="mx-auto flex max-w-6xl flex-col gap-4 font-poppins text-xs text-white/70 sm:flex-row sm:items-center sm:justify-between">
            <span>© 2026 Ajudae</span>
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('termos') }}" class="transition hover:text-white">Termos de Uso</a>
                <a href="#" class="transition hover:text-white">Política de Privacidade</a>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sections = [...document.querySelectorAll('[data-termo-section]')];
            const links = [...document.querySelectorAll('[data-termo-link]')];

            const activate = id => {
                links.forEach(link => {
                    const active = link.dataset.termoLink === id;
                    link.classList.toggle('bg-[#EEF7F2]', active);
                    link.classList.toggle('text-[#173F2D]', active);
                    link.classList.toggle('font-semibold', active);
                });
            };

            const observer = new IntersectionObserver(entries => {
                const visible = entries.filter(entry => entry.isIntersecting);
                if (visible.length) activate(visible[0].target.id);
            }, { rootMargin: '-20% 0px -65% 0px', threshold: 0.05 });

            sections.forEach(section => observer.observe(section));
            if (sections.length) activate(sections[0].id);
        });
    </script>
</body>
</html>
