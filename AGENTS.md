# Ajudaê — instruções permanentes

## Pacote de auditoria obrigatório

Ao concluir cada bloco com alterações neste projeto, gerar ZIP de auditoria em
`%USERPROFILE%\Desktop\Zips` (criar a pasta se necessário), sempre fora do Git.
Esta regra também vale para frontend, páginas e componentes.

- Incluir todos os arquivos adicionados/modificados no bloco, com caminhos
  relativos ao repositório; acrescentar referências relevantes quando necessário.
- Incluir relatório técnico e `MANIFESTO.md`: lista e justificativa das mudanças,
  testes/resultados, referência do commit e SHA-256 dos arquivos incluídos.
- Registrar arquivos removidos sem restaurá-los automaticamente.
- Excluir `.env`, credenciais, tokens, chaves privadas, dumps, documentos pessoais,
  `vendor`, `node_modules`, `.git`, caches e arquivos desnecessários.
- Não incluir cofres de credenciais nem artefatos privados de homologação.
- Usar nome descritivo com data; verificar conteúdo e hashes do ZIP e informar
  caminho absoluto e SHA-256 final ao usuário. Não sobrescrever pacote anterior.

`scripts/New-AuditZip.ps1` aceita uma lista explícita de arquivos revisados.
Não arquivar recursivamente o repositório inteiro. A regra de auditoria não
autoriza commit, push, deploy ou testes mutáveis que não tenham sido aprovados.
