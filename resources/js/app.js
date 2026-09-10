document.addEventListener('DOMContentLoaded', () => {

    const txt = (el) => el.textContent.toLowerCase();

    // Chips de filtro: destaca o clicado e filtra os cards/linhas
    const termoDoChip = (rotulo) => {
        const mapa = {
            'todos': '', 'todas': '', 'abertas': '',
            'ong': 'ong', 'individual': 'ind.',
            'voluntários': 'voluntário', 'organizadores': 'organizador', 'suspensos': 'suspenso',
            'pendentes': 'pendente', 'verificadas': 'verificada', 'recusadas': 'recusada',
            'aprovados': 'aprovado', 'negados': 'negado',
            'críticas': 'crítica', 'urgentes': '__vermelho__',
            'aprovações': 'aprovação', 'recusas': 'recusa', 'suspensões': 'suspensão',
            'concluídas': 'concluíd', 'resolvidas': 'resolvid',
        };
        const r = rotulo.trim().toLowerCase();
        return (r in mapa) ? mapa[r] : r;
    };

    document.querySelectorAll('.cand-toolbar').forEach((toolbar) => {
        const area   = toolbar.closest('.ong-main') || document;
        const cards  = area.querySelectorAll('.admin-fila .admin-card');
        const linhas = area.querySelectorAll('.admin-tabela tbody tr');
        const alvos  = cards.length ? [...cards] : [...linhas];
        const chips  = toolbar.querySelectorAll('.cand-chip');

        chips.forEach((chip) => {
            chip.addEventListener('click', () => {
                chips.forEach((c) => c.classList.remove('is-on'));
                chip.classList.add('is-on');
                const termo = termoDoChip(chip.textContent);
                alvos.forEach((el) => {
                    let mostra;
                    if (termo === '') mostra = true;
                    else if (termo === '__vermelho__') mostra = !!el.querySelector('.admin-tag-vermelho');
                    else mostra = [...el.querySelectorAll('.admin-tag')].map(txt).join(' ').includes(termo);
                    el.style.display = mostra ? '' : 'none';
                });
            });
        });
    });

    // Abas do Painel admin: cada aba é um atalho pra sua fila
    const rotaDaAba = {
        'eventos': 'fila-eventos',
        'ongs': 'cadastros-ong',
        'upgrades': 'upgrades-org',
        'denúncias': 'denuncias',
        'lgpd': 'solicitacoes-lgpd',
    };
    document.querySelectorAll('.admin-tabs span').forEach((aba) => {
        aba.style.cursor = 'pointer';
        aba.addEventListener('click', () => {
            const rotulo = (aba.firstChild ? aba.firstChild.textContent : aba.textContent).trim().toLowerCase();
            const rota = rotaDaAba[rotulo];
            if (rota) window.location.href = rota;
        });
    });

    // Busca ao vivo: filtra cards ou linhas pelo texto digitado
    document.querySelectorAll('.cand-busca input').forEach((input) => {
        const area   = input.closest('.ong-main') || document;
        const cards  = area.querySelectorAll('.admin-fila .admin-card');
        const linhas = area.querySelectorAll('.admin-tabela tbody tr');
        const alvos  = cards.length ? cards : linhas;
        input.addEventListener('input', () => {
            const termo = input.value.trim().toLowerCase();
            alvos.forEach((el) => {
                el.style.display = el.textContent.toLowerCase().includes(termo) ? '' : 'none';
            });
        });
    });

    // Links de demonstração que não pulam para o topo
    document.querySelectorAll('.ong-main a[href="#"]').forEach((a) => {
        a.addEventListener('click', (e) => e.preventDefault());
    });

});