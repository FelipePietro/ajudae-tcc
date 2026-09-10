<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastros ONG — Ajudaê</title>
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
                    'label' => 'Cadastros ONG',
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
</div>

        <header class="fila-cabecalho">
            <div>
                <h1>Cadastros de ONG</h1>
                <p>1 cadastro pendente de verificação · 38 ONGs verificadas</p>
            </div>
            <div class="fila-resumo">
                <div><strong>1</strong><span>Pendente</span></div>
                <div><strong>38</strong><span>Verificadas</span></div>
                <div><strong>2</strong><span>Recusadas</span></div>
            </div>
        </header>

        <div class="cand-toolbar">
            <label class="cand-busca">
                <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true">
                    <circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="1.8"/>
                    <path d="m20 20-3-3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
                <input type="search" placeholder="Buscar ONG, CNPJ ou responsável...">
            </label>
            <button type="button" class="cand-chip is-on">Pendentes</button>
            <button type="button" class="cand-chip">Verificadas</button>
            <button type="button" class="cand-chip">Recusadas</button>
            <button type="button" class="cand-aprovar-lote">Ordenar: mais recentes</button>
        </div>

        <div class="admin-fila">
            <article class="admin-card">
                <header class="admin-card-topo">
                    <div>
                        <h2>Instituto Semear</h2>
                        <div class="admin-tags">
                            <span class="admin-tag admin-tag-ouro">Pendente</span>
                            <span class="admin-tag-txt">CNPJ 12.345.678/0001-90</span>
                            <span class="admin-tag admin-tag-cinza">Educação</span>
                        </div>
                    </div>
                    <div class="admin-card-links">
                        <small>cadastrado há 6h</small>
                        <x-botao-secundario href="#" texto="Ver documentos" />
                    </div>
                </header>
                <dl class="admin-meta">
                    <div>
                        <div><dt>Responsável</dt><dd>Renata Prado</dd></div>
                        <div><dt>E-mail</dt><dd>contato@semear.org.br</dd></div>
                    </div>
                    <div>
                        <div><dt>Cidade</dt><dd>São Paulo, SP</dd></div>
                        <div><dt>Fundação</dt><dd>2018</dd></div>
                    </div>
                </dl>
                <p class="cand-hab-label">Documentos enviados</p>
                <div class="cand-habs">
                    <span class="cand-hab">📄 Estatuto.pdf</span>
                    <span class="cand-hab">📄 Ata de fundação.pdf</span>
                    <span class="cand-hab">📄 Cartão CNPJ.pdf</span>
                </div>
                <label class="admin-motivo">
                    Motivo da recusa (obrigatório ao recusar)
                    <textarea rows="2" placeholder="Ex: Documentação incompleta, reenviar cartão CNPJ..."></textarea>
                </label>
                <footer class="admin-card-acoes">
                    <div>
                        <x-botao-primario href="#" texto="Aprovar cadastro" />
                        <x-botao-declined href="#" texto="Recusar com motivo" />
                    </div>
                    <x-botao-secundario href="#" texto="Ver perfil completo" />
                </footer>
            </article>

            <article class="admin-card">
                <header class="admin-card-topo">
                    <div>
                        <h2>Mãos que Ajudam</h2>
                        <div class="admin-tags">
                            <span class="admin-tag admin-tag-verde">Verificada</span>
                            <span class="admin-tag-txt">CNPJ 98.765.432/0001-10</span>
                            <span class="admin-tag admin-tag-cinza">Assistência Social</span>
                        </div>
                    </div>
                    <div class="admin-card-links">
                        <small>verificada ontem</small>
                        <x-botao-secundario href="#" texto="Ver perfil completo" />
                    </div>
                </header>
                <dl class="admin-meta">
                    <div>
                        <div><dt>Responsável</dt><dd>Paulo Menezes</dd></div>
                        <div><dt>E-mail</dt><dd>contato@maosqueajudam.org</dd></div>
                    </div>
                    <div>
                        <div><dt>Cidade</dt><dd>Campinas, SP</dd></div>
                        <div><dt>Eventos ativos</dt><dd>4</dd></div>
                    </div>
                </dl>
            </article>
        </div>
    </main>
</div>
</body>
</html>