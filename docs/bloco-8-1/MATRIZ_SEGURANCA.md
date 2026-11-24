# Bloco 8.1 — Autorizações e dados

| Superfície | Controle no servidor | Dados disponíveis |
| --- | --- | --- |
| Candidatura e histórico | Pessoa voluntária aprovada para enviar. Histórico e desistência por titular. Escopo revalidado na transação. | Carta criptografada e devolvida apenas em consulta privada do titular ou responsável operacional. |
| Evento e seleção | Autoridade da ONG ou responsável conforme comando. Estado ativo para seleção. Administrador decide publicação sob capacidade. | Histórico operacional preservado durante reanálise. |
| Presença, avaliação e encerramento | Presença e avaliação da Pessoa seguem o responsável operacional. Finalização do Evento segue D05. Ator, papel e vínculo revalidados. | Dados de titulares no fluxo privado. |
| Padrinho | Somente padrinho elegível emite código. Aprovação exige análise humana e vínculo elegível quando marcado. | Código opaco e revogável. O público não recebe documentos, motivos ou códigos privados. |
| Monitoria e justificativa | Decisão por responsável e acesso pelo escopo da inscrição. Efeitos sobre certificados e confiança usam registros de domínio. | Justificativa e trilha não entram no ranking nem no certificado público. |
| Notificação e LGPD | Pessoa ou ONG da sessão como destinatário e titular. Leitura e resposta administrativa separadas por capacidade e caso. | Conteúdo de caso não atribuído tem projeção reduzida. |
| Certificado | Titular para emissão, consulta e PDF. Verificação Web por código, com campos mínimos. | PDF sob demanda. Contrato legado de consulta pública API permanece para revisão de compatibilidade e privacidade. |
| Sessão e CSRF | Modo Web usa cookies, sessão tipada e CSRF. Bearer continua separado. Query e corpo não atribuem papéis. | Páginas e dados privados usam `no-store`. |

A validação desta execução usou instância MySQL local descartável e dados sintéticos. Produção, Railway e expurgo de arquivos ficam fora do recorte.
