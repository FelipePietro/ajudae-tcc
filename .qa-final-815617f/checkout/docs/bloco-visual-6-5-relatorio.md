# Bloco Visual 6.5 — auditoria final e preparação da integração

Referência 19/11/2026, branch feat/ajustes-pre-deploy, HEAD 1c028b311edcff2869f1d299dbbff5924425b450. Trabalho local extenso preservado por baseline de hashes; HEAD antigo não representa o frontend. AGENTS.md integral e anexo6.5 lidos; não encontrado AGENTS mais específico/backlog de integração. PDF oficial acessível, extração textual de 15 páginas; SHA256 `cf36b76fd0061feaaac774a2f85ef9ddff70b3d9f1bf03f9d146b3a3d7117d48`. Não se afirma leitura visual integral do PDF. Fontes históricas e pendências extraídas em fontes-documentais.json; planos e achados atuais conferidos por leitura do código.

## Resultado e correções

Landing apresentava Aprendiz450/600, perfil público Aprendiz1000XP e zero até próximo nível. Corrigidos textos/faixas/progresso das identidades selecionadas conforme níveis aprovados, sem alterar saldo ou regra de backend. Perfil público tinha links de certificados apenas para#; cards preservados como informação e ação Verificar código encaminha à consulta pública existente, sem inventar código ou abrir certificados privados de terceiros. Convite em ONG vazia perdia IDs ao recarregar a URL atual, embora links já os transportassem: URL atual agora preserva apenas os mesmos IDs sintéticos permitidos 1–3, sem e-mail/login/texto. A prévia do ranking público também foi alinhada: os três exemplos acima da Pessoa agora têm XP superior ao dela. Testes de regressão adicionados.

Nenhuma rota nova. Alteradas apresentações de `/` e `/perfil`; impactadas variantes públicas de Pessoa e `/ong/organizadores` e links que transportam convites. Demais rotas são regressão. Backend de negócio, banco, autenticação, migrations e infraestrutura apenas lidos. Nenhum git add/commit/push/deploy, migração, seeder ou comando destrutivo. AGENTS.md preservado. Proposta futura: registrar auditoria 6.5, correções e plano coletivo após aceite humano.

## Evidências e cobertura real

Há 236 registros em route:list; 57 padrões GET web incluindo aliases. Cobertura HTTP observada nos logs: **57/57 (100.00%)**, não confundir com cobertura visual. 140 URLs absolutas observadas no conjunto de logs, incluindo variantes e repetições de contexto. Universo total de variantes/combinações não enumerado: porcentagem de todas as variantes/personas não calculável honestamente. Inventário informa evidência por rota e o que não foi visitado. Matriz cobre documentação das 34 RN (34/34), **não 34 RN homologadas**.

Navegador completo **Codex/IAB** usado: landing→cadastro vazio/erros; perfil privado→público; convite ONG com revisão/recarga; certificado geral→recarga→consulta pública; negativa administrativa e percursos adicionais registrados no diário interativo. Houve timeouts de navegação recuperados com observação do resultado. getScreenshot retornou Unable to capture screenshot; não há fotografia desse navegador nem alegação de inspeção fotográfica nele. AX/DOM prova interação/estado somente. Capturas verdadeiras complementares Edge/Playwright em 360/768/1280 px e ações/console/rede nos JSON; inspeção visual efetiva limitada às imagens nominalmente listadas em verificacoes-finais.json.

public/hot apontava Vite IPv6: recursos do IAB não correspondiam ao build. Arquivo preservado e temporariamente afastado para homologação, restaurado ao final. Rodadas iniciais com timeouts/ERR_ABORTED ficam registradas; repetições em pasta própria distinguem ambiente de defeito. Build não deve executar simultaneamente à rodada definitiva por substituir assets. Não mascarar falhas remanescentes: ver verificacoes-finais.json e seção abaixo.

Resultados automatizados estão em feature.txt, feature-final.txt, javascript-final.tap, build-final.txt, pint.txt, php-lint.txt, blade.txt e diff-check.txt. Seleção Feature por **caminhos explícitos** de classes visuais, SQLite :memory: e DB/HTTP bloqueados nos testes; sem filtros por nome capazes de alcançar testes backend como ocorreu 6.2. Testes backend com banco não executados. Runtime PHP 8.5.5, Node 22.14.0, Vite 8.2.1; nenhum resultado histórico foi somado como validação nova. Logs finais são fonte dos totais e limitações.

## Limites, decisões e riscos

Não é E2E integrado: cadastro/Admin/Evento e demais domínios têm fixtures/escopos independentes. Nenhum efeito demonstrativo comprova autenticação, persistência entre dispositivos, e-mail, consentimento, exportação, certificado definitivo, LGPD ou segurança. Estado em memória/query/sessionStorage não é autorização. Transições legadas de promoção/atribuição/vínculo, além dos IDs de convites transportados, não têm ledger coletivo persistente; não afirmar que refresh reproduz todas as decisões. O plano futuro preserva essas lacunas, sem ativar integração para ocultá-las.

Aceite humano complementar 5.2 e homologação final 6.5 continuam pendentes. Sem leitor de tela real, auditoria WCAG integral, PDF/UA, concorrência do MySQL, storage/Railway ou produção. Não há declaração de sistema 100% funcional nem autorização de publicação. Plano coletivo separa seis frentes, dependências/arquivos compartilhados, sequência e critérios. Decisões abertas: avaliação ONG pós-Evento; sem Agendas; monitoria/revisões após fechamento; duração autoritativa; modalidade/revogação/emissão definitiva; documentos/anonimização; vagas informativasRN012; população ranking e contratos seguros de lookup/referral. Nenhuma delas foi decidida silenciosamente.

Ocorrência 6.2: teste ManagedImageTest iniciou migrations no MySQL local ajudae_tcc_testing e falhou em campanha_xp.fim. Não se afirma rollback nem esquema limpo. Nesta execução não houve preparação/alteração desse banco; apenas registro documental. Plano detalha pré-produção: URLs demonstrativas, linguagem, favicon vazio, contatos oficiais e certificados definitivos.

## Falhas observadas nos logs (inclui tentativas iniciais preservadas)

```json
[
  {
    "arquivo": "docs/evidencias/bloco-visual-6-5/domains/regressoes.json",
    "falhas": [
      "page.goto: Navigation to \"http://127.0.0.1:8000/admin/candidatos?contexto=administrador\" is interrupted by another navigation to \"http://127.0.0.1:8000/organizador?contexto=organizador&jornada=independente-eventos\"\nCall log:\n\u001b[2m  - navigating to \"http://127.0.0.1:8000/admin/candidatos?contexto=administrador\", waiting until \"networkidle\"\u001b[22m\n\n    at C:\\laragon\\www\\ajudae-tcc\\storage\\framework\\visual65-privacy.mjs:24:29"
    ],
    "erros_js": []
  },
  {
    "arquivo": "docs/evidencias/bloco-visual-6-5/domains/roteiros.json",
    "falhas": [
      "page.goto: net::ERR_ABORTED at http://127.0.0.1:8000/ong/notificacoes?contexto=ong\nCall log:\n\u001b[2m  - navigating to \"http://127.0.0.1:8000/ong/notificacoes?contexto=ong\", waiting until \"networkidle\"\u001b[22m\n\n    at go (C:\\laragon\\www\\ajudae-tcc\\storage\\framework\\visual65-domains.mjs:6:54)\n    at C:\\laragon\\www\\ajudae-tcc\\storage\\framework\\visual65-domains.mjs:16:9"
    ],
    "erros_js": []
  },
  {
    "arquivo": "docs/evidencias/bloco-visual-6-5/final/audit.json",
    "falhas": [
      {
        "width": 360,
        "path": "/ong/perfil?ong=2&contexto=visitante",
        "message": "page.goto: Timeout 30000ms exceeded.\nCall log:\n\u001b[2m  - navigating to \"http://127.0.0.1:8000/ong/perfil?ong=2&contexto=visitante\", waiting until \"load\"\u001b[22m\n"
      }
    ],
    "erros_js": []
  },
  {
    "arquivo": "docs/evidencias/bloco-visual-6-5/interacoes/navegador.json",
    "falhas": [
      {
        "width": 768,
        "url": "http://127.0.0.1:8000/meus-certificados?contexto=ong",
        "message": "page.goto: net::ERR_ABORTED at http://127.0.0.1:8000/candidatura?contexto=ong\nCall log:\n\u001b[2m  - navigating to \"http://127.0.0.1:8000/candidatura?contexto=ong\", waiting until \"load\"\u001b[22m\n",
        "stack": "page.goto: net::ERR_ABORTED at http://127.0.0.1:8000/candidatura?contexto=ong\nCall log:\n\u001b[2m  - navigating to \"http://127.0.0.1:8000/candidatura?contexto=ong\", waiting until \"load\"\u001b[22m\n\n    at go (C:\\laragon\\www\\ajudae-tcc\\storage\\framework\\visual65-accessibility.mjs:7:54)\n    at C:\\laragon\\www\\ajudae-tcc\\storage\\framework\\visual65-accessibility.mjs:48:9"
      }
    ],
    "erros_js": []
  },
  {
    "arquivo": "docs/evidencias/bloco-visual-6-5/tentativas-iniciais/correcoes.json",
    "falhas": [
      "AssertionError [ERR_ASSERTION]: /notificacoes?contexto=visitante\n\n403 !== 200\n\n    at file:///C:/laragon/www/ajudae-tcc/tests/Frontend/final-corrections-browser.mjs:42:41"
    ],
    "erros_js": []
  }
]
```

## Documentos e pacote

[Plano coletivo](bloco-visual-6-5-plano-integracao.md), [matriz RN](bloco-visual-6-5-matriz-regras.md), [checklist ordenado por jornada](bloco-visual-6-5-checklist-homologacao-manual.md) e [inventário](bloco-visual-6-5-inventario.json). Pacote incremental contra baseline desta sessão, não contra HEAD, com arquivos/manifesto/SHA256/CRC. Caminho/hash externo em entrega-6-5.json e na resposta final. Excluídos segredos/dependências/build/cache/projeto completo. Não iniciar módulo seguinte; homologar pelas jornadas do checklist.

Decisões para você responder agora: **Nenhuma bloqueante para a documentação; decisões de negócio abertas estão no plano para o grupo antes da integração.**

## Consolidação dos resultados finais

```json
{
  "feature": {
    "tool": "phpunit",
    "result": "passed",
    "tests": 102,
    "passed": 102,
    "assertions": 4295,
    "duration_ms": 38167
  },
  "feature_complementar_nao_somar": {
    "tool": "phpunit",
    "result": "passed",
    "tests": 12,
    "passed": 12,
    "assertions": 615,
    "duration_ms": 7062
  },
  "javascript": {
    "testes": 70,
    "passaram": 70,
    "falhas": 0,
    "evidencia": "javascript-final.tap"
  },
  "build": {
    "passou": true,
    "comando": "npm run build",
    "evidencia": "build-final.txt"
  },
  "pint": {
    "tool": "pint",
    "result": "passed"
  },
  "sintaxe_php": "php-lint.txt",
  "sintaxe_js": "js-lint.txt",
  "blade": "blade.txt",
  "diff_check": {
    "exit_code_observado": 0,
    "avisos": "CRLF preexistentes separados de whitespace inválido",
    "evidencia": "diff-check.txt"
  },
  "navegador_complementar_final": {
    "amostragem": {
      "arquivo": "repeticao/final/audit.json",
      "visitas": 141,
      "acoes": 0,
      "checks": 0,
      "falhas": [],
      "excecoes_js": []
    },
    "acessibilidade": {
      "arquivo": "repeticao/interacoes/navegador.json",
      "visitas": 68,
      "acoes": 48,
      "checks": 55,
      "falhas": [],
      "excecoes_js": []
    },
    "dominios": {
      "arquivo": "repeticao/domains/roteiros.json",
      "visitas": 40,
      "acoes": 48,
      "checks": 12,
      "falhas": [],
      "excecoes_js": []
    },
    "fechamento": {
      "arquivo": "feedback/encerramento-navegador.json",
      "visitas": 12,
      "acoes": 36,
      "checks": 12,
      "falhas": [],
      "excecoes_js": []
    },
    "regressao": {
      "arquivo": "repeticao/domains/regressoes.json",
      "visitas": 45,
      "acoes": 43,
      "checks": 0,
      "falhas": [],
      "excecoes_js": []
    },
    "formularios_candidatura": {
      "arquivo": "repeticao/feedback/navegador.json",
      "visitas": 126,
      "acoes": 45,
      "checks": 90,
      "falhas": [],
      "excecoes_js": []
    },
    "correcoes": {
      "arquivo": "correcoes/correcoes.json",
      "visitas": 72,
      "acoes": 6,
      "checks": 6,
      "falhas": [],
      "excecoes_js": []
    }
  },
  "capturas_inspecionadas_visualmente": [
    "correcoes/perfil-lucas-360.png",
    "correcoes/convite-refresh-768.png",
    "correcoes/landing-360.png",
    "correcoes/perfil-sara-768.png",
    "correcoes/perfil-lucas-1280.png",
    "correcoes/convite-refresh-360.png",
    "repeticao/interacoes/certificado-dialogo-1280.png"
  ],
  "navegador_codex": {
    "utilizado": true,
    "diario": "navegador-interativo.json",
    "fotografia_disponivel": false
  },
  "limites": [
    "Sem leitor de tela real/WCAG integral/PDF-UA",
    "Sem teste de API ou banco real",
    "Não somar visitas/repetições como telas únicas",
    "Aceite humano5.2 e6.5 pendente"
  ]
}
```

Homologações anteriores informadas pelo usuário foram preservadas; os ajustes desta execução aguardam aceite humano.
