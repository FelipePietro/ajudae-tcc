<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitações LGPD — Ajudae</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="ong-body">
<div class="ong-shell">
            <x-admin-sidebar />

            <main class="ong-main admin-main">
                <div class="admin-topo">
        <x-breadcrumb-admin
            :itens="[
                [
                    'label' => 'Solicitações LGPD',
                ]
            ]"
        >
            <x-slot:acoes>
                <div class="admin-topo-acoes">
                    <time datetime="2025-05-27T22:31">
                        qua., 27 de mai. · 22:31
                    </time>

                    <a
                        href="{{ route('admin.log-acoes') }}"
                        class="btn-secundario admin-log-btn"
                    >
                        Ver log completo
                    </a>
                </div>
            </x-slot:acoes>
        </x-breadcrumb-admin>

        <header class="fila-cabecalho">
            <div>
                <h1>Solicitações LGPD</h1>
                <p>4 solicitações abertas · 2 vencem em menos de 48h</p>
            </div>
            <div class="fila-resumo">
                <div><strong>4</strong><span>Abertas</span></div>
                <div><strong>2</strong><span>Urgentes</span></div>
                <div><strong>28</strong><span>Concluídas</span></div>
            </div>
        </header>

        <div class="cand-toolbar">
            <label class="cand-busca">
                <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true">
                    <circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="1.8"/>
                    <path d="m20 20-3-3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
                <input type="search" placeholder="Buscar solicitante ou e-mail...">
            </label>
            <button type="button" class="cand-chip is-on">Abertas</button>
            <button type="button" class="cand-chip">Urgentes</button>
            <button type="button" class="cand-chip">Concluídas</button>
            <button type="button" class="cand-aprovar-lote">Ordenar: prazo mais curto</button>
        </div>

        <div class="admin-fila">
            <article class="admin-card">
                <header class="admin-card-topo">
                    <div>
                        <h2>Exportação de dados — Marina Souza</h2>
                        <div class="admin-tags">
                            <span class="admin-tag admin-tag-vermelho">Vence em 12h</span>
                            <span class="admin-tag admin-tag-cinza">Exportação</span>
                            <span class="admin-tag-txt">Direito à portabilidade (Art. 18)</span>
                        </div>
                    </div>
                    <div class="admin-card-links">
                        <small>solicitado há 2 dias</small>
                        <x-botao-secundario href="#" texto="Ver solicitação" />
                    </div>
                </header>
                <dl class="admin-meta">
                    <div>
                        <div><dt>Solicitante</dt><dd>Marina Souza</dd></div>
                        <div><dt>E-mail</dt><dd>marina.souza@email.com</dd></div>
                    </div>
                    <div>
                        <div><dt>Solicitado em</dt><dd>25 mai. 2025</dd></div>
                        <div><dt>Prazo legal</dt><dd>28 mai. 2025 (15 dias)</dd></div>
                    </div>
                </dl>
                <p class="admin-desc">O titular solicitou uma cópia de todos os dados pessoais armazenados na plataforma, incluindo histórico de eventos e candidaturas, em formato legível.</p>
                <label class="admin-motivo">
                    Justificativa (obrigatória ao recusar)
                    <textarea rows="2" placeholder="Ex: Identidade não confirmada, solicitar documento..."></textarea>
                </label>
                <footer class="admin-card-acoes">
                    <div>
                        <x-botao-primario href="#" texto="Gerar exportação" />
                        <x-botao-declined href="#" texto="Recusar com motivo" />
                    </div>
                    <x-botao-secundario href="#" texto="Ver histórico" />
                </footer>
            </article>

            <article class="admin-card">
                <header class="admin-card-topo">
                    <div>
                        <h2>Exclusão de conta — Bruno Tavares</h2>
                        <div class="admin-tags">
                            <span class="admin-tag admin-tag-vermelho">Vence em 40h</span>
                            <span class="admin-tag admin-tag-cinza">Exclusão</span>
                            <span class="admin-tag-txt">Direito à eliminação (Art. 18)</span>
                        </div>
                    </div>
                    <div class="admin-card-links">
                        <small>solicitado há 1 dia</small>
                        <x-botao-secundario href="#" texto="Ver solicitação" />
                    </div>
                </header>
                <dl class="admin-meta">
                    <div>
                        <div><dt>Solicitante</dt><dd>Bruno Tavares</dd></div>
                        <div><dt>E-mail</dt><dd>bruno.tavares@email.com</dd></div>
                    </div>
                    <div>
                        <div><dt>Solicitado em</dt><dd>26 mai. 2025</dd></div>
                        <div><dt>Prazo legal</dt><dd>29 mai. 2025 (15 dias)</dd></div>
                    </div>
                </dl>
                <p class="admin-desc">O titular pediu a exclusão definitiva da conta e a anonimização dos dados. Verificar se há obrigações legais de retenção antes de concluir.</p>
                <label class="admin-motivo">
                    Justificativa (obrigatória ao recusar)
                    <textarea rows="2" placeholder="Ex: Retenção obrigatória por 5 anos (fins fiscais)..."></textarea>
                </label>
                <footer class="admin-card-acoes">
                    <div>
                        <x-botao-primario href="#" texto="Confirmar exclusão" />
                        <x-botao-declined href="#" texto="Recusar com motivo" />
                    </div>
                    <x-botao-secundario href="#" texto="Ver histórico" />
                </footer>
            </article>

            <article class="admin-card">
                <header class="admin-card-topo">
                    <div>
                        <h2>Acesso aos dados — Helena Dias</h2>
                        <div class="admin-tags">
                            <span class="admin-tag admin-tag-ouro">Vence em 6 dias</span>
                            <span class="admin-tag admin-tag-cinza">Acesso</span>
                            <span class="admin-tag-txt">Direito de confirmação (Art. 18)</span>
                        </div>
                    </div>
                    <div class="admin-card-links">
                        <small>solicitado hoje</small>
                        <x-botao-secundario href="#" texto="Ver solicitação" />
                    </div>
                </header>
                <dl class="admin-meta">
                    <div>
                        <div><dt>Solicitante</dt><dd>Helena Dias</dd></div>
                        <div><dt>E-mail</dt><dd>helena.dias@email.com</dd></div>
                    </div>
                    <div>
                        <div><dt>Solicitado em</dt><dd>27 mai. 2025</dd></div>
                        <div><dt>Prazo legal</dt><dd>11 jun. 2025 (15 dias)</dd></div>
                    </div>
                </dl>
                <p class="admin-desc">A titular quer saber quais dados a plataforma trata a seu respeito e com quais finalidades. Preparar relatório de confirmação de tratamento.</p>
                <label class="admin-motivo">
                    Justificativa (obrigatória ao recusar)
                    <textarea rows="2" placeholder="Ex: Solicitação duplicada, já respondida em..."></textarea>
                </label>
                <footer class="admin-card-acoes">
                    <div>
                        <x-botao-primario href="#" texto="Enviar relatório" />
                        <x-botao-declined href="#" texto="Recusar com motivo" />
                    </div>
                    <x-botao-secundario href="#" texto="Ver histórico" />
                </footer>
            </article>
        </div>
    </main>
</div>
</body>
</html>