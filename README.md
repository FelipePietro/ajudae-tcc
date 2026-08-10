<div align="center">

# 🤝 Ajudaê API

### Conectando pessoas, organizações e oportunidades de voluntariado.

[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel&logoColor=white)](https://laravel.com/)
[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![REST API](https://img.shields.io/badge/API-REST-success)]()
[![Sanctum](https://img.shields.io/badge/Auth-Laravel%20Sanctum-blue)](https://laravel.com/docs/sanctum)
[![Status](https://img.shields.io/badge/Status-Backend%20Completo-brightgreen)]()

**Backend oficial do projeto Ajudaê, desenvolvido como Trabalho de Conclusão de Curso (TCC) da ETEC de Guarulhos.**

</div>

---

# 📖 Sobre o Projeto

O **Ajudaê** é uma plataforma desenvolvida para conectar pessoas interessadas em realizar trabalho voluntário com organizações sociais e organizadores de ações comunitárias.

A proposta do sistema é facilitar todo o processo de divulgação, inscrição e gerenciamento de eventos voluntários, oferecendo uma plataforma centralizada para aproximar voluntários e instituições.

O projeto foi desenvolvido utilizando arquitetura **REST**, separando completamente o Backend da interface Front-end, permitindo escalabilidade, manutenção simplificada e integração com diferentes plataformas.

---

# 🎯 Objetivo

O principal objetivo do Ajudaê é resolver problemas encontrados atualmente na organização de ações voluntárias, como:

- dificuldade para encontrar oportunidades de voluntariado;
- divulgação descentralizada dos eventos;
- dificuldade das organizações em encontrar voluntários;
- gerenciamento manual das inscrições;
- falta de um histórico de participação dos voluntários;
- ausência de uma plataforma única para conectar voluntários e organizações.

---

# 🏗 Arquitetura

A arquitetura do projeto segue o padrão RESTful.

```text
                 Front-end (Em desenvolvimento)
                          │
                          │ HTTP/HTTPS
                          ▼
                REST API (Laravel 11)
                          │
                          ▼
             Laravel Sanctum (Autenticação)
                          │
                          ▼
                      MySQL 8+
```

A API é responsável por toda a regra de negócio do sistema, autenticação, gerenciamento dos usuários, eventos, agendas, inscrições e permissões.

---

# 👥 Perfis do Sistema

Atualmente o sistema possui três perfis principais.

## 👤 Pessoa (Voluntário)

Representa qualquer usuário interessado em participar de ações voluntárias.

Pode:

- realizar cadastro;
- editar seus dados;
- cadastrar habilidades;
- cadastrar recursos;
- cadastrar causas de interesse;
- visualizar eventos;
- visualizar agendas;
- realizar inscrições;
- acompanhar suas inscrições;
- cancelar inscrições;
- solicitar exclusão da conta.

---

## 🧑‍💼 Organizador Solo

O Organizador Solo é uma Pessoa autorizada por um Administrador a organizar eventos independentemente de uma ONG.

Esse perfil permite que projetos sociais menores também utilizem a plataforma.

> **Status:** 🚧 Em desenvolvimento (estrutura parcialmente implementada).

---

## 🏢 ONG

Representa organizações sociais cadastradas na plataforma.

Pode:

- criar eventos;
- criar agendas;
- gerenciar inscrições;
- aprovar ou reprovar candidatos;
- administrar seus próprios eventos.

---

## 👨‍💻 Administrador

Perfil responsável pela administração geral do sistema.

Entre suas responsabilidades estão:

- aprovação de ONGs;
- aprovação de Organizadores Solo;
- gerenciamento de categorias;
- gerenciamento de recursos;
- gerenciamento de habilidades;
- gerenciamento de causas.

---

# 🚀 Funcionalidades

## ✅ Implementadas

### Autenticação

- Cadastro de Pessoas
- Cadastro de ONGs
- Login
- Logout
- Laravel Sanctum
- Autenticação por Bearer Token

---

### Pessoas

- Atualização de perfil
- Exclusão agendada
- Cancelamento da exclusão
- Cadastro de habilidades
- Cadastro de recursos
- Cadastro de causas

---

### ONGs

- Cadastro
- Atualização de dados
- Exclusão agendada
- Aprovação administrativa

---

### Eventos

- Criar evento
- Editar evento
- Listar eventos
- Buscar evento
- Alterar status
- Excluir evento

---

### Agendas

- Criar agenda
- Editar agenda
- Ativar agenda
- Finalizar agenda
- Excluir agenda

---

### Sistema de Inscrições

- Inscrição em agendas
- Bloqueio de inscrições duplicadas
- Aprovação de candidatos
- Reprovação de candidatos
- Cancelamento de inscrição
- Consulta de inscrições

---

### Cadastros Administrativos

- Recursos
- Habilidades
- Causas
- Categorias de Evento

---

### Segurança

- Controle de permissões
- Middleware por perfil
- Exclusão automática de contas
- Exclusão em 7 dias
- Bloqueio de exclusão para organizadores com eventos ativos

---

# 🚧 Funcionalidades Futuras

As funcionalidades abaixo fazem parte do roadmap do projeto.

- Sistema de notificações
- Recuperação de senha
- Confirmação de e-mail
- Dashboard administrativo
- Organizador Solo completo
- Histórico detalhado de participação
- Auditoria de ações
- Documentação Swagger/OpenAPI

---

# 🛠 Tecnologias Utilizadas

## Backend

- PHP 8+
- Laravel 11
- Laravel Sanctum
- Composer

## Banco de Dados

- MySQL

## Controle de Versão

- Git
- GitHub

## Ferramentas

- Postman
- Laragon
- Visual Studio Code

---

# 📂 Estrutura do Projeto

```text
app
├── Console
│   └── Commands
│
├── Http
│   ├── Controllers
│   └── Middleware
│
├── Models
│
database
├── migrations
│
routes
├── api.php
│
bootstrap
config
storage
public
```

---

# 🔄 Fluxo Geral da Aplicação

## Fluxo do Voluntário

```text
Cadastro
      │
      ▼
Login
      │
      ▼
Completar Perfil
      │
      ├── Recursos
      ├── Habilidades
      └── Causas
      │
      ▼
Visualizar Eventos
      │
      ▼
Selecionar Agenda
      │
      ▼
Realizar Inscrição
      │
      ▼
Aguardar Aprovação
      │
      ▼
Participar do Evento
```

---

## Fluxo da ONG

```text
Cadastro
      │
      ▼
Aprovação Administrativa
      │
      ▼
Login
      │
      ▼
Criar Evento
      │
      ▼
Criar Agenda
      │
      ▼
Receber Candidatos
      │
      ▼
Analisar Inscrições
      │
      ▼
Aprovar ou Reprovar
      │
      ▼
Gerenciar Evento
```

---

# ⚙️ Instalação

Clone o repositório

```bash
git clone https://github.com/SEU-USUARIO/ajudae-api.git
```

Entre na pasta

```bash
cd ajudae-api
```

Instale as dependências

```bash
composer install
```

Copie o arquivo de configuração

```bash
cp .env.example .env
```

Configure as credenciais do banco de dados no arquivo `.env`.

Execute as migrations

```bash
php artisan migrate
```

Gere a chave da aplicação

```bash
php artisan key:generate
```

Inicie o servidor

```bash
php artisan serve
```

---

# 🔐 Autenticação

A API utiliza **Laravel Sanctum** para autenticação.

Após realizar o login, será retornado um **Bearer Token**, que deverá ser enviado em todas as rotas protegidas.

Exemplo:

```http
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJh...
```

---

# 📱 Front-end

O Front-end da aplicação encontra-se atualmente **em desenvolvimento**.

Toda a comunicação será realizada através desta API REST.

---

# 📚 Documentação

A documentação completa dos endpoints encontra-se nas próximas seções deste README.

Cada endpoint possui:

- objetivo;
- autenticação necessária;
- parâmetros;
- corpo da requisição;
- respostas;
- códigos HTTP;
- regras de negócio.

---

# 📌 Estado Atual do Projeto

| Módulo | Status |
|---------|--------|
| Backend | ✅ Concluído |
| Banco de Dados | ✅ Concluído |
| API REST | ✅ Concluído |
| Autenticação | ✅ Concluída |
| Sistema de Eventos | ✅ Concluído |
| Sistema de Agendas | ✅ Concluído |
| Sistema de Inscrições | ✅ Concluído |
| Front-end | 🚧 Em desenvolvimento |
| Notificações | 🚧 Planejado |
| Dashboard Administrativo | 🚧 Planejado |

---

> **Próxima seção:** Documentação completa dos endpoints da API.
---

# 👤 Pessoas

Todos os endpoints relacionados ao gerenciamento de voluntários.

Base URL:

```http
/api/v1/pessoas
```

---

# POST /register

## Objetivo

Realiza o cadastro de um novo voluntário.

---

## Autenticação

❌ Não necessita autenticação.

---

## Body

```json
{
    "nm_pessoa": "João da Silva",
    "genero_pessoa": "masculino",
    "cpf_pessoa": "12345678901",
    "dt_nasc": "2000-05-20",
    "rg_pessoa": "123456789",

    "email_pessoa": "joao@email.com",
    "tele_pessoa": "11999999999",

    "cep_pessoa": "07000000",
    "logradouro_pessoa": "Rua das Flores",
    "compl_pessoa": "Casa",
    "cidade_pessoa": "Guarulhos",
    "bairro_pessoa": "Centro",
    "uf_pessoa": "SP",

    "login_pessoa": "joao123",
    "senha_pessoa": "12345678",
    "senha_pessoa_confirmation": "12345678",

    "antecedentes_pessoa_link": "https://...",
    "cnh_pessoa_link": "https://...",
    "rg_pessoa_link": "https://...",
    "pfp_pessoa_link": "https://...",

    "aceitou_termos": true
}
```

---

## Resposta

**201 Created**

```json
{
    "message": "Cadastro realizado com sucesso.",
    "user": {
        ...
    },
    "token": "...",
    "token_type": "Bearer"
}
```

---

## Possíveis respostas

| Código | Descrição |
|---------|-----------|
| 201 | Cadastro realizado |
| 422 | Dados inválidos |
| 422 | CPF já cadastrado |
| 422 | Email já cadastrado |
| 422 | Login já utilizado |

---

## Regras de negócio

- CPF deve ser único.
- Email deve ser único.
- Login deve ser único.
- Senha mínima de 8 caracteres.
- O usuário deve aceitar os termos de uso.
- A senha é armazenada criptografada utilizando Hash do Laravel.

---

# POST /login

## Objetivo

Realiza autenticação de um voluntário.

---

## Autenticação

❌ Não necessita autenticação.

---

## Body

```json
{
    "login_pessoa":"joao123",
    "senha_pessoa":"12345678"
}
```

---

## Resposta

```json
{
    "message":"Login realizado com sucesso.",
    "user":{...},
    "token":"...",
    "token_type":"Bearer"
}
```

---

## Possíveis respostas

| Código | Descrição |
|---------|-----------|
|200|Login realizado|
|401|Senha inválida|
|404|Usuário não encontrado|

---

# POST /logout

## Objetivo

Encerra a sessão do usuário.

---

## Autenticação

✅ Bearer Token obrigatório.

---

## Headers

```http
Authorization: Bearer TOKEN
```

---

## Resposta

```json
{
    "message":"Logout realizado com sucesso."
}
```

---

# GET /me

## Objetivo

Retorna os dados do usuário autenticado.

---

## Autenticação

✅ Bearer Token obrigatório.

---

## Resposta

```json
{
    "pessoa_id":1,
    "nm_pessoa":"João",
    "email_pessoa":"..."
}
```

---

# GET /{id}

## Objetivo

Busca uma pessoa pelo ID.

---

## Autenticação

✅ Bearer Token obrigatório.

---

## Exemplo

```http
GET /api/v1/pessoas/1
```

---

## Resposta

```json
{
    "pessoa_id":1,
    "nm_pessoa":"João",
    ...
}
```

---

# PUT /{id}

## Objetivo

Atualiza os dados permitidos do voluntário.

---

## Autenticação

✅ Bearer Token obrigatório.

---

## Campos permitidos

```text
email_pessoa
tele_pessoa
cep_pessoa
logradouro_pessoa
compl_pessoa
cidade_pessoa
bairro_pessoa
uf_pessoa
pfp_pessoa_link
senha_pessoa
```

---

## Campos NÃO editáveis

Por motivos de segurança e integridade dos dados, os seguintes campos não podem ser alterados após o cadastro:

```text
CPF
Nome
RG
Data de nascimento
Gênero
Login
Role
XP
Avaliação
```

---

## Resposta

```json
{
    "message":"Pessoa atualizada com sucesso.",
    "user":{...}
}
```

---

## Possíveis respostas

|Código|Descrição|
|------|---------|
|200|Atualização realizada|
|403|Tentativa de alterar outra conta|
|404|Pessoa não encontrada|
|422|Dados inválidos|

---

# DELETE /{id}

## Objetivo

Solicita a exclusão da conta.

A conta NÃO é apagada imediatamente.

É iniciado um período de carência de 7 dias.

---

## Autenticação

✅ Bearer Token obrigatório.

---

## Resposta

```json
{
    "message":"A exclusão da conta foi agendada para daqui a 7 dias.",
    "deletar_em":"2026-08-17T..."
}
```

---

## Regras de negócio

- A conta permanece ativa durante o período.
- O usuário pode cancelar a exclusão.
- Após sete dias a exclusão será realizada automaticamente.
- Usuários responsáveis por eventos ativos não podem ser excluídos.

---

# PATCH /{id}/cancelar-exclusao

## Objetivo

Cancela uma solicitação de exclusão.

---

## Autenticação

✅ Bearer Token obrigatório.

---

## Resposta

```json
{
    "message":"Exclusão cancelada com sucesso."
}
```

---

# 👥 Relacionamentos da Pessoa

A Pessoa pode possuir múltiplos recursos, habilidades e causas.

---

# GET /{id}/habilidades

Retorna todas as habilidades cadastradas.

---

# POST /{id}/habilidades

Associa uma nova habilidade.

## Body

```json
{
    "habilidade_id":3,
    "nivel_habilidade":"Intermediário"
}
```

---

# DELETE /{id}/habilidades/{habilidade_id}

Remove uma habilidade.

---

# GET /{id}/recursos

Lista todos os recursos cadastrados.

---

# POST /{id}/recursos

Associa um recurso.

## Body

```json
{
    "recurso_id":2,
    "detalhes_recurso":"Notebook próprio"
}
```

---

# DELETE /{id}/recursos/{recurso_id}

Remove um recurso.

---

# GET /{id}/causas

Lista todas as causas de interesse.

---

# POST /{id}/causas

Associa uma causa.

## Body

```json
{
    "causa_id":4
}
```

---

# DELETE /{id}/causas/{causa_id}

Remove uma causa.

---

## Regras de negócio

- Uma habilidade não pode ser cadastrada duas vezes para a mesma pessoa.
- Um recurso não pode ser duplicado.
- Uma causa não pode ser duplicada.
- Apenas o próprio usuário pode alterar seus relacionamentos.

---

# 🏢 ONGs

Todos os endpoints relacionados às organizações.

Base URL

```http
/api/v1/ongs
```

A estrutura dos endpoints segue o mesmo padrão das Pessoas.

Endpoints disponíveis:

| Método | Endpoint | Descrição |
|---------|----------|-----------|
|POST|/register|Cadastrar ONG|
|POST|/login|Realizar login|
|POST|/logout|Encerrar sessão|
|GET|/me|Usuário autenticado|
|GET|/{id}|Consultar ONG|
|PUT|/{id}|Atualizar ONG|
|DELETE|/{id}|Solicitar exclusão|
|PATCH|/{id}/cancelar-exclusao|Cancelar exclusão|
|PATCH|/{id}/status|Alterar status da ONG (Administrador)|

---

## Regras de negócio

- CNPJ deve ser único.
- Login deve ser único.
- CPF do responsável deve ser único.
- Apenas administradores podem aprovar ou reprovar ONGs.
- A exclusão segue a mesma regra de carência de sete dias aplicada às Pessoas.
- ONGs com eventos ativos não podem ser excluídas.

---

# 📚 Cadastros Administrativos

Os módulos abaixo possuem CRUD completo.

Todos exigem autenticação de Administrador.

- Recursos
- Habilidades
- Causas
- Categorias de Evento

Todos seguem o padrão REST:

| Método | Endpoint | Descrição |
|---------|----------|-----------|
|GET|/|Listar|
|GET|/{id}|Consultar|
|POST|/|Cadastrar|
|PUT|/{id}|Atualizar|
|DELETE|/{id}|Excluir|

---

# 🎯 Eventos

Os eventos representam as ações voluntárias organizadas por uma ONG ou por um Organizador Solo.

Todo evento deve possuir pelo menos uma agenda para que voluntários possam se inscrever.

Base URL

```http
/api/v1/eventos
```

---

# GET /

## Objetivo

Retorna todos os eventos cadastrados.

---

## Autenticação

❌ Não necessita autenticação.

---

## Resposta

```json
[
    {
        "evento_id":1,
        "nm_evento":"Mutirão de Limpeza",
        "descricao_evento":"...",
        "status_evento":"ativo"
    }
]
```

---

# GET /{id}

## Objetivo

Retorna um evento específico.

---

## Resposta

```json
{
    "evento_id":1,
    "nm_evento":"Mutirão",
    "descricao_evento":"...",
    "status_evento":"ativo"
}
```

---

# POST /

## Objetivo

Cadastrar um novo evento.

---

## Autenticação

✅ Bearer Token obrigatório.

Permissões:

- ONG
- Organizador Solo

---

## Body

```json
{
    "nm_evento":"Mutirão de Limpeza",
    "descricao_evento":"Limpeza da praça central.",
    "cat_evento_id":1,
    "logradouro_evento":"Rua X",
    "bairro_evento":"Centro",
    "cidade_evento":"Guarulhos",
    "uf_evento":"SP",
    "cep_evento":"07000000"
}
```

---

## Resposta

```json
{
    "message":"Evento criado com sucesso.",
    "evento":{ ... }
}
```

---

# PUT /{id}

## Objetivo

Atualizar um evento.

---

## Autenticação

✅ Bearer Token obrigatório.

Apenas o responsável pelo evento pode editá-lo.

---

## Resposta

```json
{
    "message":"Evento atualizado com sucesso."
}
```

---

# PATCH /{id}/status

## Objetivo

Alterar o status do evento.

---

## Autenticação

✅ Administrador.

---

## Body

```json
{
    "status_evento":"ativo"
}
```

---

## Status disponíveis

```text
aguardando aprovação

ativo

reprovado

cancelado

finalizado
```

---

# DELETE /{id}

## Objetivo

Excluir um evento.

---

## Autenticação

✅ Bearer Token obrigatório.

---

## Regras de negócio

- Apenas o responsável pode excluir.
- A exclusão remove automaticamente as agendas vinculadas.
- Consequentemente as inscrições também são removidas.

---

# 📅 Agendas

As agendas representam datas específicas nas quais um evento acontecerá.

Um evento pode possuir diversas agendas.

Base URL

```http
/api/v1/agendas
```

---

# GET /

Lista todas as agendas.

---

# GET /{id}

Retorna uma agenda específica.

---

# POST /

## Objetivo

Cadastrar uma agenda.

---

## Body

```json
{
    "evento_id":1,
    "status_ativo":"ativo",
    "data_inicio":"2026-09-20 08:00:00",
    "data_fim":"2026-09-20 12:00:00"
}
```

---

## Resposta

```json
{
    "agenda_id":1,
    "evento_id":1,
    "status_ativo":"ativo"
}
```

---

# PUT /{id}

Atualiza uma agenda.

---

# PATCH /{id}/ativar

Altera o status para:

```text
ativo
```

---

# PATCH /{id}/finalizar

Altera o status para:

```text
finalizado
```

---

# DELETE /{id}

Remove uma agenda.

---

## Regras

- Uma agenda pertence a apenas um evento.
- Um evento pode possuir várias agendas.
- A data final deve ser posterior à data inicial.
- Apenas o responsável pelo evento pode alterá-la.

---

# 📝 Sistema de Inscrições

Este é o principal fluxo do sistema.

Uma Pessoa pode realizar inscrição em uma Agenda.

A ONG analisa a candidatura.

Após aprovação, o voluntário passa a participar oficialmente daquela agenda.

---

# POST /api/v1/agendas/{agenda}/inscrever

## Objetivo

Realizar inscrição em uma agenda.

---

## Autenticação

✅ Pessoa

---

## Body

Não possui.

---

## Resposta

```json
{
    "message":"Inscrição realizada com sucesso."
}
```

---

## Regras

- Apenas Pessoas podem se inscrever.
- Não é permitido realizar inscrições duplicadas.
- Apenas agendas ativas recebem inscrições.
- O usuário deve estar autenticado.

---

# GET /api/v1/agendas/{agenda}/inscritos

## Objetivo

Lista todos os candidatos inscritos.

---

## Autenticação

✅ ONG

---

## Resposta

```json
[
    {
        "inscricao_id":1,
        "status_inscricao":"pendente",
        "pessoa":{
            ...
        }
    }
]
```

---

# PATCH /api/v1/inscricoes/{id}/status

## Objetivo

Aprovar ou reprovar um candidato.

---

## Body

```json
{
    "status_inscricao":"aprovado"
}
```

ou

```json
{
    "status_inscricao":"reprovado"
}
```

---

## Resposta

```json
{
    "message":"Status atualizado com sucesso."
}
```

---

# GET /api/v1/pessoas/{id}/inscricoes

Lista todas as inscrições do voluntário.

---

# DELETE /api/v1/inscricoes/{id}

Cancela uma inscrição.

---

## Fluxo completo das inscrições

```text
Pessoa

↓

Visualiza Evento

↓

Seleciona Agenda

↓

Realiza Inscrição

↓

Status = Pendente

↓

ONG analisa

↓

┌───────────────┐
│               │
▼               ▼

Aprovado    Reprovado

│               │

▼               ▼

Participa     Processo encerrado
```

---

# 📌 Regras de Negócio

## Eventos

- Um evento pertence a uma ONG ou a um Organizador Solo.
- Um evento pode possuir diversas agendas.
- Eventos precisam estar ativos para receber inscrições.

---

## Agendas

- Uma agenda pertence a um único evento.
- Um evento pode possuir várias agendas.
- Agendas finalizadas não recebem inscrições.

---

## Inscrições

- Uma pessoa pode participar de diversos eventos.
- Uma agenda possui diversos candidatos.
- Não é permitido realizar duas inscrições na mesma agenda.
- Apenas ONGs podem aprovar candidatos.
- Apenas o próprio voluntário pode cancelar sua inscrição.
- O status inicial de toda inscrição é **pendente**.

---

# 🔄 Relacionamentos

```text
Pessoa

↓

Inscrição

↓

Agenda

↓

Evento

↓

Categoria
```

---

```text
ONG

↓

Evento

↓

Agenda

↓

Inscrições

↓

Voluntários
```

---

# 📊 Modelo de Funcionamento

```text
Pessoa

↓

Cadastro

↓

Login

↓

Configura Perfil

↓

Visualiza Eventos

↓

Seleciona Agenda

↓

Realiza Inscrição

↓

Aguarda Aprovação

↓

Participa do Evento
```

---

```text
ONG

↓

Login

↓

Cria Evento

↓

Cria Agenda

↓

Recebe Inscrições

↓

Analisa Candidatos

↓

Aprova/Reprova

↓

Gerencia Evento
```

---
---

# 🔒 Segurança

A API utiliza autenticação baseada em **Laravel Sanctum**, fornecendo um **Bearer Token** para usuários autenticados.

Todas as rotas protegidas exigem o envio do token no cabeçalho da requisição.

Exemplo:

```http
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJh...
```

---

# 🛡 Controle de Permissões

O sistema possui controle de acesso baseado em perfis de usuário.

## Perfis

| Perfil | Permissões |
|----------|------------|
| Pessoa | Participação em eventos, gerenciamento do próprio perfil |
| Organizador Solo | Organização de eventos próprios *(em desenvolvimento)* |
| ONG | Gerenciamento de eventos e candidatos |
| Administrador | Administração completa do sistema |

---

## Middleware

Atualmente a API utiliza os seguintes middlewares:

| Middleware | Função |
|------------|--------|
| auth:sanctum | Verifica autenticação |
| abilities:pessoa | Permissões de voluntário |
| abilities:ong | Permissões de ONG |
| admin | Permissões administrativas |

---

# 📑 Códigos HTTP Utilizados

| Código | Significado |
|---------|-------------|
| 200 | Requisição realizada com sucesso |
| 201 | Recurso criado com sucesso |
| 204 | Operação realizada sem conteúdo de retorno |
| 400 | Requisição inválida |
| 401 | Usuário não autenticado |
| 403 | Acesso não autorizado |
| 404 | Recurso não encontrado |
| 409 | Conflito de dados |
| 422 | Erro de validação |

---

# ⚖ Principais Regras de Negócio

## Pessoas

- CPF único.
- Login único.
- Email único.
- Apenas o próprio usuário pode alterar seus dados.
- Dados pessoais sensíveis não podem ser alterados após o cadastro.
- A exclusão ocorre após um período de carência de sete dias.
- A exclusão pode ser cancelada durante esse período.

---

## ONGs

- CNPJ único.
- CPF do responsável único.
- Apenas administradores podem aprovar ou reprovar ONGs.
- ONGs com eventos ativos não podem ser excluídas.

---

## Eventos

- Todo evento pertence a uma ONG ou a um Organizador Solo.
- Um evento pode possuir diversas agendas.
- Eventos precisam estar ativos para receber inscrições.

---

## Agendas

- Toda agenda pertence a um único evento.
- Um evento pode possuir diversas agendas.
- Agendas finalizadas não recebem novas inscrições.

---

## Inscrições

- Apenas Pessoas podem se inscrever.
- Apenas ONGs podem aprovar ou reprovar candidatos.
- Não é permitido realizar duas inscrições para a mesma agenda.
- O status inicial de toda inscrição é **Pendente**.

---

# 📂 Banco de Dados

O banco foi modelado seguindo princípios relacionais, utilizando chaves estrangeiras para garantir integridade entre as entidades.

Principais entidades:

- Pessoa
- ONG
- Evento
- Agenda
- Categoria de Evento
- Recurso
- Habilidade
- Causa
- Inscrição
- Assinatura
- Notificação *(estrutura inicial)*

---

# 🚀 Comandos Artisan

Alguns comandos úteis durante o desenvolvimento.

## Executar servidor

```bash
php artisan serve
```

---

## Executar migrations

```bash
php artisan migrate
```

---

## Recriar banco de dados

```bash
php artisan migrate:fresh
```

---

## Limpar cache

```bash
php artisan optimize:clear
```

---

## Excluir contas pendentes

```bash
php artisan app:excluir-contas-pendentes
```

Este comando realiza a exclusão definitiva de contas cujo período de carência expirou.

---

# 📌 Roadmap

As funcionalidades abaixo fazem parte da evolução planejada do projeto.

## Funcionalidades

- Sistema de notificações
- Recuperação de senha
- Confirmação de e-mail
- Dashboard administrativo
- Organizador Solo completo
- Histórico completo de participação
- Auditoria de ações
- Documentação OpenAPI / Swagger

---

# 📈 Próximos Passos

Com a conclusão do Backend, o foco do projeto passa a ser:

- Desenvolvimento da interface Web;
- Integração Front-end com a API REST;
- Implementação das funcionalidades previstas no Roadmap;
- Testes integrados;
- Preparação para apresentação do Trabalho de Conclusão de Curso.

---

# 👨‍💻 Equipe

Projeto desenvolvido como Trabalho de Conclusão de Curso da **ETEC de Guarulhos**.

## Integrantes

- Felipe Pietro da Costa Luiz
- Enrico Yoshida de Oliveira
- Pedro Henrique Dias Roger
- Oliver Barbosa de Alcântara
- Luiz Miguel Leles dos Santos
- João Pedro Gomes

---

# 🙏 Agradecimentos

Agradecemos aos professores da ETEC de Guarulhos pelo acompanhamento e orientação durante o desenvolvimento deste projeto.

Também agradecemos a todos que contribuíram com sugestões, testes e validações ao longo da construção do Ajudaê.

---

# 📄 Licença

Este projeto foi desenvolvido exclusivamente para fins acadêmicos como Trabalho de Conclusão de Curso da **ETEC de Guarulhos**.

O código-fonte está disponível para consulta e aprendizado, sendo vedada sua utilização comercial sem autorização dos autores.

---

<div align="center">

### 🤝 Ajudaê

**Conectando pessoas, organizações e oportunidades para transformar a sociedade através do voluntariado.**

Desenvolvido com ❤️ utilizando Laravel.

</div>
