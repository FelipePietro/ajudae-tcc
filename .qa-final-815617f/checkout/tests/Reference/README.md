# Referência histórica de BFF

`WebFoundationTest.php` foi movido de `tests/Feature` sem alterar seus bytes.
Ele registra os testes do B0/B1 anterior e **não pertence às suítes atuais** de
`phpunit.xml`. Suas expectativas dependem do BFF e não descrevem o frontend mock.

Não executar esse arquivo na demonstração: ele usa `RefreshDatabase`, login real
e fixtures de banco. Sua eventual reativação pertence ao trabalho futuro de
integração do grupo, com rotas e ambiente de testes aprovados separadamente.

A regressão atual está em `tests/Feature/DemoFrontendTest.php`, que proíbe acesso
ao banco e chamadas de API. Os serviços/controller/middlewares BFF foram mantidos
como referência, sem registro em `routes/web.php` ou `routes/demo.php`.
