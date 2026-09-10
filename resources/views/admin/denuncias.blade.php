<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Denúncias — Ajudae</title>
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
                    'label' => 'Denúncias'
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
                <h1>Denúncias</h1>
                <p>2 denúncias abertas · 1 crítica precisa de atenção</p>
            </div>
            <div class="fila-resumo">
                <div><strong>2</strong><span>Abertas</span></div>
                <div><strong>1</strong><span>Crítica</span></div>
                <div><strong>15</strong><span>Resolvidas</span></div>
            </div>
        </header>

        <div class="cand-toolbar">
            <label class="cand-busca">
                <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true">
                    <circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="1.8"/>
                    <path d="m20 20-3-3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
                <input type="search" placeholder="Buscar denúncia, evento ou usuário...">
            </label>
            <button type="button" class="cand-chip is-on">Abertas</button>
            <button type="button" class="cand-chip">Críticas</button>
            <button type="button" class="cand-chip">Resolvidas</button>
            <button type="button" class="cand-aprovar-lote">Ordenar: mais graves</button>
        </div>

        <div class="admin-fila">
            <article class="admin-card">
                <header class="admin-card-topo">
                    <div>
                        <h2>Evento: Feira de Adoção Relâmpago</h2>
                        <div class="admin-tags">
                            <span class="admin-tag admin-tag-vermelho">Crítica</span>
                            <span class="admin-tag admin-tag-cinza">Informação enganosa</span>
                            <span class="admin-tag-txt">3 denúncias sobre este evento</span>
                        </div>
                    </div>
                    <div class="admin-card-links">
                        <small>recebida há 1h</small>
                        <x-botao-secundario href="#" texto="Ver evento" />
                    </div>
                </header>
                <dl class="admin-meta">
                    <div>
                        <div><dt>Alvo</dt><dd>Evento público</dd></div>
                        <div><dt>Organizador</dt><dd>Pet Amigo (ONG)</dd></div>
                    </div>
                    <div>
                        <div><dt>Motivo</dt><dd>Local e horário divergentes do anunciado</dd></div>
                        <div><dt>Denunciado por</dt><dd>3 usuários</dd></div>
                    </div>
                </dl>
                <p class="admin-desc">Voluntários relataram que o endereço informado não existe e que o organizador não respondeu às mensagens. Possível evento falso ou desatualizado.</p>
                <label class="admin-motivo">
                    Nota da moderação (opcional)
                    <textarea rows="2" placeholder="Ex: Conteúdo removido, organizador notificado por e-mail..."></textarea>
                </label>
                <footer class="admin-card-acoes">
                    <div>
                        <x-botao-primario href="#" texto="Resolver denúncia" />
                        <x-botao-declined href="#" texto="Remover conteúdo" />
                    </div>
                    <x-botao-secundario href="#" texto="Ver detalhes" />
                </footer>
            </article>

            <article class="admin-card">
                <header class="admin-card-topo">
                    <div>
                        <h2>Usuário: Ricardo Alves</h2>
                        <div class="admin-tags">
                            <span class="admin-tag admin-tag-ouro">Moderada</span>
                            <span class="admin-tag admin-tag-cinza">Comportamento inadequado</span>
                            <span class="admin-tag-txt">1 denúncia</span>
                        </div>
                    </div>
                    <div class="admin-card-links">
                        <small>recebida há 8h</small>
                        <x-botao-secundario href="#" texto="Ver perfil" />
                    </div>
                </header>
                <dl class="admin-meta">
                    <div>
                        <div><dt>Alvo</dt><dd>Voluntário</dd></div>
                        <div><dt>Evento</dt><dd>Mutirão de Limpeza — Praça Central</dd></div>
                    </div>
                    <div>
                        <div><dt>Motivo</dt><dd>Linguagem ofensiva com outro voluntário</dd></div>
                        <div><dt>Denunciado por</dt><dd>Camila Rocha (organizadora)</dd></div>
                    </div>
                </dl>
                <p class="admin-desc">A organizadora relata troca de mensagens agressivas no chat do evento. Prints foram anexados à denúncia para análise.</p>
                <label class="admin-motivo">
                    Nota da moderação (opcional)
                    <textarea rows="2" placeholder="Ex: Advertência enviada, usuário notificado..."></textarea>
                </label>
                <footer class="admin-card-acoes">
                    <div>
                        <x-botao-primario href="#" texto="Resolver denúncia" />
                        <x-botao-declined href="#" texto="Suspender usuário" />
                    </div>
                    <x-botao-secundario href="#" texto="Ver detalhes" />
                </footer>
            </article>
        </div>
    </main>
</div>
</body>
</html> 