# Bloco 7.4 — regressão de segurança e fidelidade visual

Execução local de 20/11/2026 na branch `feat/ajustes-pre-deploy`, HEAD `1c028b311edcff2869f1d299dbbff5924425b450`. O inventário inicial registrou 84 entradas rastreadas com alterações e 4.671 não rastreadas. A comparação por hash e status está na [evidência de preservação](evidencias/bloco-7-4/preservacao.json). Foram consultados o PDF oficial RN001–RN034 por extração textual de 15 páginas, a versão Markdown, o backlog de 16/11, a auditoria 7.1, A1–A6, os relatórios 7.2–7.3 e as capturas 6.5. O catálogo executado confirma 20 rotas Web da aplicação e 162 entradas da API v1.

## Mudanças

- A navbar pública original voltou a distinguir as jornadas de Pessoa e ONG. O login mantém o formulário real. Páginas de cadastro apresentam o estado de indisponibilidade.
- A barra pessoal original voltou ao perfil e ao organizador. A ONG usa a sidebar institucional original. A administração usa seu shell, breadcrumb e sidebar de referência. O principal, o vínculo e as permissões vêm do servidor. As áreas operacionais ainda fechadas aparecem sem ações simuladas.
- A página Segurança da conta usa os cartões do projeto. O acompanhamento de exclusão exibe o prazo e a ação real de cancelamento. Termos, Privacidade, feed e detalhe receberam correções pontuais de apresentação.
- O logout interno de uma troca limpa o principal e o ViewModel imediatamente. Isso impede que uma resposta 429 renderize links do usuário anterior.
- O timeout ocioso passa a usar navegações privadas bem-sucedidas. Visitas públicas, prefetch e HEAD deixam intacto o relógio de inatividade. O teto de 12 h continua contado desde a autenticação.
- O fechamento da sidebar administrativa por backdrop foi corrigido no controle de acessibilidade compartilhado. O fallback móvel expõe a navegação quando o navegador desativa JavaScript.

## Resultado e limites

- **PHPUnit:** 21 testes focados com 217 asserções. 18 testes de referência com 983 asserções. Regressão transacional: 65 testes com 633 asserções. Os cinco grupos de integração MySQL passaram, incluindo bearer e sessão coexistentes.
- **Frontend:** 70 testes JavaScript, build Vite, Blade e checagens de sintaxe aprovadas. O Edge registrou 104 verificações aprovadas e capturou as páginas nas três larguras. O detalhe de Evento foi renderizado no Edge com dados SQLite porque o catálogo QA estava vazio.
- **Limitações:** a credencial da Pessoa aprovada foi recusada. O vínculo foi verificado em testes isolados, sem jornada autenticada de vinculado no Edge. Foram usados relógio controlado e sessões distintas, sem ensaio simultâneo de duas transações sobre o mesmo recurso. HTTPS, storage externo, teste integral de segurança e aceite visual humano estão fora da comprovação técnica.

Os testes do MySQL usaram o schema descartável verificado e com transações. As contas de QA existentes e as linhas monitoradas foram preservadas. Não houve alteração em rotas API, schema, migrations, seeders, `.env`, deploy, commit ou push. Atualização pontual sugerida para o AGENTS.md após o aceite humano: registrar o 7.4, os ajustes de sessão e as pendências de fixture e de integração.

## Entregas

- [Matriz de segurança e mapa de rotas](bloco-7-4-matriz-seguranca.md)
- [Comparação das capturas e componentes](bloco-7-4-auditoria-visual-6-5-vs-7-3.md)
- [Regressões e correções](bloco-7-4-matriz-regressoes.md)
- [Checklist em 127.0.0.1:8000](bloco-7-4-checklist-homologacao-manual.md)
- [Pendências F1–F6](bloco-7-4-pendencias-integracao.md)
- [Inventário de testes e limitações](bloco-7-4-inventario-testes.md)
