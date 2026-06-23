# CorreiosComercial

Plataforma de inteligência comercial dos Correios — estrutura e consulta do **MANCAT** (Manual de Atendimento Comercial) com extração automática de regras, comparativo de serviços, registro de ideias por eixo estratégico e base para futura IA.

---

## O que é este projeto

- **8.910 itens** do MANCAT em banco de dados relacional (MySQL)
- Árvore de navegação: Manuais → Módulos → Capítulos/Anexos → Itens
- **715+ regras extraídas automaticamente**: pesos, prazos, dimensões, restrições, elegibilidade
- **Comparativo de serviços** lado a lado (SEDEX × PAC × MALOTE × ...)
- **Sistema de ideias** por eixo estratégico — campos JSON flexíveis
- Pesquisa textual com log de consultas (base para futura IA/RAG)

---

## Stack

| Camada | Tecnologia |
|---|---|
| Backend | PHP 8.2 + CodeIgniter 4.7.3 |
| Banco | MySQL 8 (MariaDB compatível) |
| Frontend | Bootstrap 5 + Bootstrap Icons |
| Servidor local | XAMPP (Apache) |
| ETL de documentos | LibreOffice headless (conversão .doc → .txt) |

---

## Pré-requisitos

- PHP 8.1+
- MySQL 5.7+ / MariaDB 10.5+
- Composer
- LibreOffice (para reimportar documentos do MANCAT)
- Apache / Nginx (ou `php spark serve` para testes)

---

## Instalação em nova máquina

### 1. Clonar o repositório

```bash
git clone https://github.com/ateuatado/correios.git
cd correios
```

### 2. Instalar dependências PHP

```bash
composer install
```

### 3. Criar o banco de dados

```sql
CREATE DATABASE correioscomercial
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
```

### 4. Configurar o ambiente

Copie o arquivo de exemplo e edite com seus dados:

```bash
cp env .env
```

Edite `.env`:

```ini
CI_ENVIRONMENT = development

app.baseURL = 'http://correios.test/'

database.default.hostname = localhost
database.default.database = correioscomercial
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port     = 3306
```

> ⚠️ **O arquivo `.env` nunca vai para o Git** (`.gitignore`). Cada máquina tem o seu.

### 5. Criar as tabelas

```bash
php spark migrate
```

Cria as tabelas: `manuais`, `modulos`, `capitulos`, `anexos`, `itens`, `buscas`, `eixos`, `ideias`, `regras`

### 6. Popular os 8 eixos estratégicos

```bash
php spark db:seed EixosSeeder
```

---

## Importar o conteúdo do MANCAT

> ⚠️ **Os documentos Word originais NÃO estão no repositório** — são arquivos corporativos dos Correios.
> Você precisa ter acesso à pasta `C:\Correios\mancat` (ou equivalente na nova máquina).

### Pré-requisito: LibreOffice instalado

```bash
# Windows — verificar se soffice está disponível:
where soffice
```

### Ajustar o caminho base dos documentos

Em `app/Controllers/Manuais.php`, verifique/ajuste a constante:

```php
private const MANCAT_BASE = 'C:/Correios/';
```

### Rodar o ETL de importação

Via interface web em `/manuais/importar` ou pelo comando Spark equivalente.

Isso processa os 398 documentos e preenche a tabela `itens` com 8.910 linhas.

### Extrair regras automaticamente

Após a importação:

```bash
php spark regras:extract --reset
```

Gera ~715 regras estruturadas na tabela `regras`.

---

## O que está e o que NÃO está no repositório

| Item | No Git? | Como resolver |
|---|---|---|
| Código-fonte completo | ✅ Sim | — |
| Migrations (estrutura das tabelas) | ✅ Sim | `php spark migrate` |
| Seeder dos 8 eixos | ✅ Sim | `php spark db:seed EixosSeeder` |
| Assets (Bootstrap, CSS, JS) | ✅ Sim | — |
| `.env` (configurações locais) | `.env` | Criar manualmente com as credenciais do banco |
| `vendor/` | `composer install` |
| **Dados do banco** (8.910 itens) | ✅ Disponível em `sql/correioscomercial.sql` — restaurar com `mysql -u root correioscomercial < sql/correioscomercial.sql` |
| **Arquivos Word do MANCAT** | Copiar `C:\Correios\mancat` para a nova máquina (necessário apenas para reimportar) |

---

## Estrutura relevante

```
correios/
├── app/
│   ├── Commands/
│   │   └── RegrasExtract.php       ← extrator de regras (spark regras:extract)
│   ├── Controllers/
│   │   ├── Home.php
│   │   ├── Manuais.php             ← ETL + navegação do MANCAT
│   │   ├── Eixos.php               ← CRUD dos pilares estratégicos
│   │   ├── Ideias.php              ← CRUD das ideias
│   │   └── Inteligencia.php        ← regras extraídas + comparativo
│   ├── Models/
│   │   ├── EixoModel.php
│   │   ├── IdeiaModel.php
│   │   ├── RegraModel.php
│   │   └── BuscaModel.php
│   ├── Database/
│   │   ├── Migrations/             ← todas as tabelas
│   │   └── Seeds/EixosSeeder.php
│   └── Views/
│       ├── home/ | manuais/ | eixos/ | ideias/ | inteligencia/
├── public/assets/css/              ← Bootstrap + app.css + home.css
├── env                             ← template — copiar para .env
└── spark                           ← CLI do CodeIgniter
```

---

## Comandos úteis

```bash
php spark serve                     # servidor de desenvolvimento
php spark routes                    # listar todas as rotas
php spark migrate                   # criar/atualizar tabelas
php spark db:seed EixosSeeder       # popular os 8 eixos
php spark regras:extract --reset    # reextrar regras do MANCAT
php spark cache:clear               # limpar cache
```

---

## Rotas principais

| URL | Descrição |
|---|---|
| `/` | Home — 8 pilares + ideias |
| `/manuais` | Lista de manuais |
| `/manuais/arvore/{id}` | Árvore de navegação |
| `/manuais/buscar` | Pesquisa textual |
| `/inteligencia` | Dashboard de regras extraídas |
| `/inteligencia/regras` | Tabela filtrável de regras |
| `/inteligencia/comparar` | Comparativo lado a lado de serviços |
| `/inteligencia/servico/{nome}` | Ficha de um serviço |
| `/eixos` | Gerenciar pilares estratégicos |
| `/ideias/nova/{eixo_id}` | Registrar nova ideia |

---

## Próximos passos planejados

- [ ] RAG com Gemini API — perguntas em linguagem natural sobre o MANCAT
- [ ] App de campo para coleta de dados em visitas
- [ ] Deploy em VPS com Ollama local (LLM sem custo recorrente)
