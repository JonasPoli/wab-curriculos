# WAB Curriculos — Guia de Deploy em Producao

Sistema multi-tenant de cadastro de curriculos construido com Symfony 7.4, PHP 8.2+ e MySQL 8.

---

## Requisitos do Servidor

| Componente | Versao Minima |
|---|---|
| PHP | 8.2 (recomendado 8.4) |
| MySQL | 8.0 |
| Composer | 2.x |
| Node.js | Nao necessario (usa Asset Mapper) |
| Servidor web | Apache ou Nginx |

### Extensoes PHP obrigatorias

```
php-intl php-mbstring php-xml php-curl php-mysql php-zip php-gd php-fileinfo
```

---

## 1. Clonar e Instalar Dependencias

```bash
git clone <repo-url> /var/www/curriculos
cd /var/www/curriculos

composer install --no-dev --optimize-autoloader
```

---

## 2. Configurar Variaveis de Ambiente

Copie o `.env` e edite o `.env.local` com os dados reais:

```bash
cp .env .env.local
nano .env.local
```

Variaveis **obrigatorias** no `.env.local`:

```env
APP_ENV=prod
APP_SECRET=<gere_um_hash_unico_de_32_caracteres>

DATABASE_URL="mysql://USUARIO:SENHA@HOST:3306/NOME_DO_BANCO?serverVersion=8.0"

MAILER_DSN=smtp://usuario:senha@smtp.seuservidor.com:587

EMAIL_FROM=noreply@seudominio.com.br
EMAIL_CONTACT_TO=rh@seudominio.com.br
```

> Para gerar o `APP_SECRET`:
> ```bash
> php -r "echo bin2hex(random_bytes(16));"
> ```

---

## 3. Criar o Banco de Dados e Executar Migrations

```bash
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate --no-interaction
```

---

## 4. Compilar Assets (Asset Mapper)

```bash
php bin/console asset-map:compile
php bin/console importmap:install
```

---

## 5. Limpar e Aquecer o Cache

```bash
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod
```

---

## 6. Criar Diretorios de Upload

```bash
mkdir -p public/uploads/tenants/logo
mkdir -p public/uploads/tenants/dark-logo
mkdir -p public/uploads/tenants/favicon
mkdir -p var/uploads/resumes

# Permissoes (ajuste o usuario do servidor web)
chown -R www-data:www-data var/ public/uploads/
chmod -R 775 var/ public/uploads/
```

---

## 7. Configurar o Servidor Web

### Apache (com mod_rewrite)

O projeto ja inclui o `symfony/apache-pack` com o `.htaccess` pronto. Configure o VirtualHost:

```apache
<VirtualHost *:443>
    ServerName seudominio.com.br
    DocumentRoot /var/www/curriculos/public

    <Directory /var/www/curriculos/public>
        AllowOverride All
        Require all granted
    </Directory>

    SSLEngine on
    SSLCertificateFile /caminho/para/certificado.crt
    SSLCertificateKeyFile /caminho/para/chave.key
</VirtualHost>
```

### Nginx

```nginx
server {
    listen 443 ssl http2;
    server_name seudominio.com.br;
    root /var/www/curriculos/public;

    ssl_certificate /caminho/para/certificado.crt;
    ssl_certificate_key /caminho/para/chave.key;

    location / {
        try_files $uri /index.php$is_args$args;
    }

    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        internal;
    }

    location ~ \.php$ {
        return 404;
    }
}
```

---

## 8. Dados Iniciais (Seed)

Na **primeira instalacao**, popule o banco com o tenant e usuario admin:

```bash
php bin/console app:seed
```

> **IMPORTANTE:** Este comando usa o dominio `127.0.0.1` como tenant de teste. Apos rodar, acesse o painel Super Admin e altere o dominio do tenant para o dominio real de producao.

---

## 9. Multi-Tenancy — Como Funciona

O sistema identifica o tenant pelo **dominio** do request (campo `domain` da entidade `Tenant`). Cada cliente (empresa) tem seu proprio dominio apontando para o mesmo servidor.

**Para adicionar um novo tenant:**

1. Acesse `/superadmin` com o usuario Super Admin
2. Crie um novo Tenant preenchendo: nome, dominio, cores, logo, textos SEO
3. Configure o DNS do novo dominio para apontar para o IP do servidor
4. Adicione o ServerAlias ou novo VirtualHost no servidor web

---

## 10. Checklist Final de Deploy

```bash
# 1. Verificar se o ambiente esta correto
php bin/console about

# 2. Verificar se nao ha migrations pendentes
php bin/console doctrine:migrations:status

# 3. Verificar se as rotas estao corretas
php bin/console debug:router | head -30

# 4. Testar o envio de e-mail
php bin/console mailer:test email@teste.com
```

---

## 11. Estrutura de Diretorios Importante

```
config/
  packages/
    security.yaml       # Firewalls (admin + candidato)
    vich_uploader.yaml   # Config de upload de logos
public/
  uploads/              # Logos e favicons dos tenants
  images/               # Imagens estaticas do layout
var/
  uploads/resumes/      # PDFs de curriculos (privado)
src/
  Entity/               # Entidades Doctrine
  Controller/
    Admin/              # Controllers da area admin (/admin)
    Pub/                # Controllers da area publica (/trabalhe-conosco)
    SuperAdmin/         # Controllers do Super Admin (/superadmin)
  EventSubscriber/
    TenantSubscriber.php  # Resolve o tenant pelo dominio
templates/
  admin/                # Templates da area administrativa
  pub/                  # Templates da area publica
  superadmin/           # Templates do Super Admin
```

---

## 12. Atualizacoes Futuras

Para atualizar o sistema em producao:

```bash
cd /var/www/curriculos

# 1. Puxar as alteracoes
git pull origin main

# 2. Instalar dependencias (caso mudaram)
composer install --no-dev --optimize-autoloader

# 3. Executar migrations pendentes
php bin/console doctrine:migrations:migrate --no-interaction

# 4. Recompilar assets
php bin/console asset-map:compile
php bin/console importmap:install

# 5. Limpar cache
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod
```

---

## Usuarios Padrao (apos seed)

| Tipo | Login | Senha |
|---|---|---|
| Super Admin | `superadmin` | `superadmin` |
| Admin Tenant | `admin` | `admin` |

> **Troque as senhas imediatamente apos o primeiro deploy.**

---

## Suporte

Desenvolvido por [WAB](https://wab.com.br)