# CorreiosComercial

Sistema de gestão e consulta da documentação do **MANCAT** (Manual de Atendimento Comercial dos Correios), estruturado em banco de dados relacional normalizado.

## Stack

| Camada | Tecnologia |
|---|---|
| Framework | [CodeIgniter 4](https://codeigniter.com/) v4.7.3 |
| Banco de dados | MariaDB 10.4 (XAMPP) |
| Linguagem | PHP 8.2 |

## Configuração local

### Requisitos

- XAMPP com PHP 8.2 e MariaDB 10.4
- Composer 2.x
- Git

### Instalação

```bash
# 1. Clone o repositório
git clone https://github.com/ateuatado/correios.git
cd correios

# 2. Instale as dependências
composer install

# 3. Configure o ambiente
cp env .env
# Edite o .env com suas credenciais de banco

# 4. Crie o banco de dados
mysql -u root -e "CREATE DATABASE IF NOT EXISTS correioscomercial CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 5. Execute as migrations
php spark migrate
```

### Virtual Host (XAMPP / Windows)

Adicione ao `C:\xampp\apache\conf\extra\httpd-vhosts.conf`:

```apache
<VirtualHost *:443>
    DocumentRoot "C:/xampp/htdocs/correios/public"
    ServerName correios.test
    SSLEngine on
    SSLCertificateFile    "C:/xampp/apache/conf/certs/correios.test.pem"
    SSLCertificateKeyFile "C:/xampp/apache/conf/certs/correios.test-key.pem"
    <Directory "C:/xampp/htdocs/correios/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Adicione ao `C:\Windows\System32\drivers\etc\hosts`:

```
127.0.0.1   correios.test
```

Acesse: **https://correios.test**

## Estrutura do Projeto

```
correios/
├── app/
│   ├── Controllers/
│   ├── Models/
│   ├── Views/
│   └── Database/
│       └── Migrations/
├── public/
├── writable/
├── .env          ← não versionado
└── README.md
```

## Módulos do MANCAT

O sistema organiza os documentos do MANCAT em hierarquia:

```
MANCAT
  └─ Módulo (ex: Módulo 6 - Serviços Postais)
       └─ Capítulo (ex: Capítulo 1 - Procedimentos Gerais)
            └─ Anexo (ex: Anexo 1 - Fluxo do Subprocesso)
```

## Licença

Uso interno — Empresa Brasileira de Correios e Telégrafos (ECT)
