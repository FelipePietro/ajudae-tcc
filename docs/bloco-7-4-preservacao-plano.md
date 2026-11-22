# Plano de recuperação seletiva do frontend

As seis verificações funcionais e de segurança do pacote anterior foram aprovadas pelo responsável. A comparação desta execução preserva esse aceite e documenta as lacunas visuais por origem. A execução atual muda somente o atalho Segurança da conta dos menus principais. A recuperação de outras views fica como trabalho planejado.

## 1. Correções visuais imediatas

- Priorizar detalhe de Evento, ranking e notificações. São as divergências com maior impacto de composição. Extrair os componentes passivos do Blade visual e renderizar com estado real, indisponibilidade explícita ou ações inativas. Comparar cada região com as capturas 6.5 em 360, 768 e 1280.
- Manter abas, grid, breadcrumbs, cabeçalho, sidebar, cards e semântica dos botões de Pessoa, ONG, organizador e Administração. Recuperar a estrutura de filtros e listas por área. Reservar vazio para consulta operacional sem registros. Usar indisponibilidade para consulta ainda ausente.
- Comparar markup, ordem de foco, Tab, Escape, retorno de foco, rolagem, teclado e mudanças de papel. Provar a origem de cada componente pelo diff dos ZIPs ou pela evidência de fonte disponível.

## 2. Reconexões de rotas e componentes

1. Preservar os endpoints e guardas atuais. A cada página própria, levar o ramo visual a componentes que recebam dados explícitos e read-only por padrão, com identidade e autorização fornecidas no servidor. O ramo demo pode fornecer DTO sintético apenas atrás de DemoSafety.
2. Para ONG e Administração, preservar URLs, breadcrumbs e destaques do 7.4. Reaproveitar por área as tabelas, filtros e cards precedentes. Perda da capacidade deve impedir tanto menu quanto acesso direto. Verificar pessoa leitora, respondente e Master separadamente.
3. Para páginas com URL ausente no integrado (edição, detalhes públicos de perfil, certificados e presença), criar reconexão só depois de decidir a titularidade e o contrato de leitura. Manter /perfil e /ong/perfil para titulares. Os aliases antigos de compatibilidade só entram se houver link legado ou objetivo comprovado.
4. Provar negativo antes de ativar cada CTA. Verificar visitante, tipo incompatível, ONG pendente, exclusão pendente, administrador sem capacidade, vínculo encerrado, dados de terceiros e query simulada. Repetir autorização após hash/voltar, logout e duas abas. Fazer diff dos catálogos Web e bearer e revisão de privacidade por F6.

## 3. Integração por frente

| Frente | Entrega de conexão | Dependência e aceite |
|---|---|---|
| F1 Identidade | Cadastro, edição, documentos, recuperação, preferências e perfis públicos | ViewModels distintos de público e titular, validação e exclusão de campos privados. Revisar recovery e revogação. |
| F2 Eventos | Filtros completos, Agendas, gestão, candidatura, seleção e desistência | Evento publicado, escopo proprietário/responsável e concorrência por Agenda, carta opcional privada, efeitos RN012/RN014. |
| F3 Administração e organização | Filas, promoção, convites/vínculos, atribuição e apadrinhamento | Permissões reais, lookup sem enumeração, auditabilidade, vínculo e atribuição revalidados. |
| F4 Participação | Presença, monitoria, justificativas, avaliações e encerramento | Projeção por Agenda; decisões autorizadas e idempotentes. Aceitar regressão 5.2 em separado. |
| F5 Gamificação | Métricas de XP, confiabilidade, ranking, campanhas e certificados | Ledger e visibilidade, ranking por XP, dados mínimos, emissão e consulta verificáveis. |
| F6 Privacidade | Notificação, LGPD, histórico, relatório, retenção e auditoria | Leitura e resposta separadas, finalidade por caso, trilha, regras de expurgo e proteção de sessões. |

Sequência proposta: F1 para identidade/projeções, F2 e F3 por escopo, F4 após Eventos, F5 após participações, F6 transversal desde a primeira reconexão. A composição passiva pode avançar com testes de ausência de dados enquanto o contrato aguarda.

## 4. Arquivos compartilhados e riscos

- routes/web.php, routes/demo.php, routes/identity.php, ResolveWebAccount e WebNavigation afetam todas as personas. Uma alteração de registro pode ativar fixtures no integrado ou mudar cache/expiração. Coordenar por rota, guardar testes negativos, comparar antes/depois.
- SessionPagesController e PublicEventPagesController controlam projeções. Limitar campos na consulta e na view. Cópias de HTML 6.x podem executar scripts e ler sessionStorage de outro contexto. Manter setup de JS restrito por atributos e modo.
- Layouts account, admin e organizer, context-navbar, navbar-feed, navbar-ong, admin-sidebar, componentes de diálogo, privacy-demo e navigation-accessibility são compartilhados. Validar composição e teclado nos tamanhos previstos.
- Actions de alteração, Policies, Gates, fontes de Auth e API bearer ficam sob revisão específica. Não inferir backend a partir de screenshot ou fixture. Preservar a trilha e não incluir documentos privados ou ambiente real em evidência.

## 5. Decisões de produto antes de ligar operações

- F1, URLs e visibilidade de perfis públicos, exposição de nome/XP/certificados, recuperação de conta e validade do canal.
- F2, reconciliação documental RN012 com vagas de Evento informativas, campos da carta com privacidade e retenção.
- F3, lookup autorizado, perfis elegíveis ao diretório e capacidade por ação de configuração administrativa.
- F4/F5, avaliação após fechamento, fonte de duração/monitoria, emissão/revogação definitiva, população do ranking e eventual verificação pública.
- F6, exportação, trilha visível, retenção e processos irreversíveis.

[Comparação e proveniência](bloco-7-4-preservacao-matriz.md) · [Inventário de lacunas](bloco-7-4-preservacao-indisponibilidade.md) · [Proposta de baseline Git](bloco-7-4-preservacao-baseline.md).
