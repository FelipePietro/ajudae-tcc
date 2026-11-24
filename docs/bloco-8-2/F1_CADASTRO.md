# F1 — Cadastros e identidade

## P0.1 — Cadastro de Pessoa

O modo integrado registra `GET /cadastro` em `routes/identity.php`. `WebPessoaRegistrationController::create` renderiza `cadastro.blade.php`, o componente `account-wizard` e as cinco etapas do Bloco 8.1. `layouts/account.blade.php` escolhe a entrada Vite integrada. O módulo `resources/js/modules/pessoa-registration.js` instala os listeners de avanço, volta e revisão. A tela só envia `POST /cadastro` quando confirmação e termos passam pelas validações.

Para reproduzir, abra `/cadastro` no modo integrado, preencha nome, nascimento de pessoa adulta, gênero, CPF numérico e RG sintéticos. Acione **Continuar** e observe o contador, o cabeçalho da etapa, as requisições Vite e o console.

Na entrada do bloco, `public/hot` apontava para `http://[::1]:5173/`. Uma consulta local a `/@vite/client` expirou. O bundle padrão existente, `assets/identity-d20K-wOU.js` de 22/11/2026, não continha os seletores `data-web-registration` nem `data-wizard-next`. O checkout já continha o inicializador. Sem o módulo adequado, o primeiro botão `type="button"` não instala o listener que avança a etapa. Recompilar as fontes e servir o bundle atual são ações necessárias para recuperar o avanço. O fluxo existente de sessão e confirmação permanece sob o contrato do Bloco 8.1.

**Registro original: FALHOU.** O QA independente confirmou em Edge que o bundle antigo foi carregado, todos os campos da etapa estavam válidos e o clique manteve **Passo 1 de 5**. A tentativa independente da F1 em outro servidor local expirou durante ambas as navegações e não acrescenta evidência de interação. A progressão no bundle atualizado e a classificação final constam do relatório de QA. Nenhum arquivo de fonte cadastral foi alterado pela F1. [Diagnóstico original do QA](evidencias/qa/browser-antes.json) · [Captura original](evidencias/qa/p01-baseline-bundle-antigo.png) · [Relatório de QA](QA_INDEPENDENTE.md)

O componente apresenta limite de 11 caracteres e padrão numérico, coerentes com a instrução visível de informar somente dígitos. O FormRequest normaliza pontuação no servidor. Essa diferença foi registrada e os campos válidos do ensaio excluem o CPF como causa do incidente reproduzido. O servidor continua responsável pelo dígito verificador, unicidade e idade.

## P0.2 — Cadastro de ONG

**Classificação do fluxo integrado: BLOQUEADO.** Para reproduzir, abra `GET /cadastro/ong` com identidade Web integrada, como visitante. O QA confirmou resposta 200 e ausência do wizard. `routes/identity.php` registra diretamente a view `auth.registration-unavailable`, que mostra “O cadastro está indisponível no momento” com link para o login de ONG. O bloqueio antecede os scripts e não há rota POST de cadastro institucional no modo integrado. O modo demonstrativo registra o mesmo GET em `routes/demo.php`, renderiza `ong.cadastro_ong` e o componente de seis etapas. O POST histórico `/ong/cadastrar` termina com aviso de revisão sem criação de conta ou envio de arquivos. Esse histórico confirma a interface disponível para integração posterior. [Captura original](evidencias/qa/p02-cadastro-ong-antes.png)

`POST /api/v1/ongs/register` existe em `routes/api.php`, é público com limite de cinco solicitações por minuto e chama `Api\OngController::register`. O backend normaliza e valida CPF e CNPJ, exige CNPJ, login e e-mail institucional únicos, senha confirmada com oito caracteres ou mais, dados institucionais, endereço, contato e responsável. `site_url_ong` é opcional e aceita HTTP ou HTTPS. `pfp_ong_link` e `rg_responsavel_ong_link` são strings obrigatórias e não são uploads. O aceite é obrigatório. O cadastro cria ONG inicialmente aguardando aprovação, grava a assinatura em transação e devolve 201 com `data.ong`, `data.token` e `data.token_type`. Entradas inválidas podem devolver 422 e excesso de chamadas, 429.

Para liberar o fluxo, a equipe de integração deve mapear os campos do wizard ao contrato, decidir como obter a referência de identidade do responsável em armazenamento privado antes do cadastro, conectar validações e erros, e homologar cadastro pendente, documentos e decisão administrativa sob `ongs.validar`. Os endpoints de documento privado encontrados são de Pessoa. Os endpoints de imagem da ONG tratam imagem de perfil e não resolvem identidade do responsável. Uma string de link, por si só, não prova armazenamento privado nem substitui a revisão humana. A atribuição sugerida é à frente de identidade com revisão de segurança e da administração. Não houve alterações na rota, no controller, nas regras RN009 ou no bloqueio.

## Outros apontamentos de identidade

- O login existente distingue os guards Web de Pessoa e ONG e o contrato Bearer API. O cadastro de Pessoa pode usar indicação opcional. A criação da conta mantém validação cadastral pendente.
- A recuperação de senha aparece como interação visual em `resources/views/login.blade.php` no modo demonstrativo. Não foi localizada rota de reset nas rotas Web integradas ou em `/api/v1`. `POST /conta/senha` é alteração autenticada. Registrar envio de recuperação como pendente, com dependência do fluxo servidor e canal de entrega.
- A integração posterior deve preservar as projeções públicas e as atribuições dos perfis já existentes. A edição de preferências, o upload pela página de perfil e a recuperação são independentes da correção de avanço.

Referências do checkout. [Relatório 6.5](../bloco-visual-6-5-relatorio.md) · [Relatório 7.5](../bloco-7-5-relatorio.md) · [Identidade 8.1](../bloco-8-1/F1_IDENTIDADE.md) · [Guia da API](../guia-integracao-api.md) · [Registro de decisões](../AJUDAE_BACKLOG_INTEGRACAO_V1.md)
