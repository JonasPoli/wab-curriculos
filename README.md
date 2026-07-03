# WAB Ninjas — Symfony Starter Kit

> Base profissional para projetos Symfony da WAB Ninjas.
> Não comece do zero — parta daqui.

---

## Instalação

composer install

cp .env .env.local

** Edite .env.local com DATABASE_URL, MAILER_DSN e EMAIL_FROM **

php bin/console doctrine:database:create

php bin/console doctrine:migrations:migrate

php bin/console app:create-user

php bin/console tailwind:build --watch

** apenas para produção: ** php bin/console tailwind:build --minify 

symfony serve

** apague os arquivos de exemplo assim que não forem mais necessários **

---


### Campos de formulário (uso manual)

```html
<div class="wab-field">
    <label for="campo" class="wab-label">Rótulo do campo</label>

    <!-- Input com ícone posicionado -->
    <div style="position:relative">
        <i class="fa-solid fa-user"
           aria-hidden="true"
           style="position:absolute;top:50%;left:12px;transform:translateY(-50%);
                  color:var(--bm-text-muted);font-size:14px;pointer-events:none;z-index:2"></i>
        <input type="text"
               id="campo"
               class="wab-input"
               placeholder="Digite aqui"
               style="padding-left:38px">
    </div>

    <!-- Mensagem de erro -->
    <div class="wab-error">
        <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
        Este campo é obrigatório.
    </div>
</div>
```

### Cards

```html
<!-- Card padrão do admin -->
<div class="admin-card">
    <h2 class="admin-card-title">Título</h2>
    <p>Conteúdo</p>
</div>

<!-- Stat card (dashboard) -->
<div class="stat-card">
    <div class="stat-card-icon bg-primary/10 text-primary">
        <i class="fa-solid fa-users" aria-hidden="true"></i>
    </div>
    <div>
        <p class="stat-card-value">42</p>
        <p class="stat-card-label">Usuários</p>
    </div>
</div>
```

### Badges

```html
<span class="badge badge-success">Ativo</span> {# verde #}
<span class="badge badge-danger">Inativo</span>  {# vermelho #}
<span class="badge badge-warning">Pendente</span> {# amarelo #}
<span class="badge badge-info">Info</span>       {# ciano #}
<span class="badge badge-neutral">Neutro</span>  {# cinza #}
<span class="badge badge-accent">Destaque</span> {# azul #}
<span class="badge badge-admin">Admin</span>     {# roxo #}
```

### Page Header

```html
<div class="page-header">
    <h1 class="page-title">Título da Página</h1>
    <div class="page-header-actions"> {# botões à direita #}
        <a href="#" class="btn-primary">Novo</a>
    </div>
</div>
```

### Flash Messages

Cores automáticas por tipo (definidas em `.flash-success`, `.flash-error`, `.flash-warning`, `.flash-info`):

```php
// No controller:
$this->addFlash('success', $message);
$this->addFlash('error', $message);
$this->addFlash('warning', $message);
$this->addFlash('info', $message);
```

As mensagens aparecem automaticamente no topo do conteúdo via `admin/_partials/flash.html.twig`.

---

## Ícones (Font Awesome 6 Free)

### Como usar

O projeto usa **Font Awesome 6 Free** via CDN. A sintaxe é:

```html
<!-- Sólido (mais completo, ~1375 ícones) -->
<i class="fa-solid fa-NOME" aria-hidden="true"></i>

<!-- Regular (apenas ~157 ícones no free) -->
<i class="fa-regular fa-NOME" aria-hidden="true"></i>

<!-- Brands (logos de marcas) -->
<i class="fa-brands fa-NOME" aria-hidden="true"></i>
```

### Ícones em inputs

```html
<div style="position:relative">
    <i class="fa-solid fa-envelope"
       aria-hidden="true"
       style="position:absolute;top:50%;left:12px;transform:translateY(-50%);
              color:var(--bm-text-muted);font-size:14px;pointer-events:none;z-index:2"></i>
    <input class="wab-input" style="padding-left:38px" type="email" placeholder="Email">
</div>
```

### Acessibilidade em Ícones

Sempre implemente acessibilidade nos ícones:
  ```html
  <i class="fa-solid fa-user" aria-hidden="true"></i> Usuários
  ```

### Catálogo interativo

Acesse `/admin/icones` para ver e copiar todos os ícones disponíveis, com busca, filtro por variante e copy-to-clipboard.

---

## Dark / Light Mode

O modo é controlado via `localStorage.theme` ('dark' | 'light'). A classe `dark` é aplicada ao `<html>`.

### Anti-FOUC

Todas as páginas de autenticação incluem este script **antes do `</head>`** para evitar flash branco:

```html
<script>
    (function () {
        var theme = localStorage.theme;
        var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (theme === 'dark' || (!theme && prefersDark)) {
            document.documentElement.classList.add('dark');
        }
    })();
</script>
```

### Toggle programático (JS)

```js
// Disponível globalmente via app.js:
darkMode();    // ativa dark
lightMode();   // ativa light
```

O ícone do botão de toggle atualiza automaticamente via `updateThemeIcons()`.

---


### Criando uma página admin

```twig
{# templates/admin/minha-secao/lista.html.twig #}
{% extends 'admin/base.html.twig' %}

{% block title %}Minha Seção · Admin{% endblock %}
{% block page_title %}Minha Seção{% endblock %}

{# Breadcrumbs (opcional) #}
{% block breadcrumbs %}
    {% set breadcrumbs = [
        { label: 'Dashboard', route: 'app_admin_dash' },
        { label: 'Minha Seção', route: null }
    ] %}
    {{ include('admin/_partials/breadcrumbs.html.twig', { breadcrumbs: breadcrumbs }) }}
{% endblock %}

{% block body %}

<div class="page-header">
    <h1 class="page-title">Minha Seção</h1>
    <a href="#" class="btn-primary">
        <i class="fa-solid fa-plus" aria-hidden="true"></i>
        Novo item
    </a>
</div>

<div class="admin-card">
    {# conteúdo #}
</div>

{% endblock %}
```

> O estado ativo é controlado pela classe `nav-link--active` + `aria-current="page"`.

### Blocos disponíveis em `admin/base.html.twig`

| Bloco | Uso |
|---|---|
| `title` | `<title>` da página |
| `page_title` | Título no topbar |
| `stylesheets` | CSS extra por página |
| `breadcrumbs` | Navegação estrutural |
| `body` | ⭐ Conteúdo principal |
| `javascripts` | JS extra por página |

---


### Classes aplicadas automaticamente pelo form_theme

| Elemento | Classe adicionada |
|---|---|
| Input (text, email, etc.) | `wab-input` |
| Textarea | `wab-input` |
| Select | `wab-input` |
| Password | `wab-input` |
| Label | `wab-label` |
| Erros | `wab-error` com ícone FA6 |
| Row container | `wab-field` |

---

## Breadcrumbs

```twig
{% block breadcrumbs %}
    {% set breadcrumbs = [
        { label: 'Dashboard',   route: 'app_admin_dash' },
        { label: 'Usuários',    route: 'app_admin_user_index' },
        { label: 'Editar João', route: null }   {# null = página atual #}
    ] %}
    {{ include('admin/_partials/breadcrumbs.html.twig', { breadcrumbs: breadcrumbs }) }}
{% endblock %}
```

---

## Twig Helpers


### `role_label` filter

```twig
{{ 'ROLE_ADMIN'|role_label }}  {# → "Administrador" #}
{{ 'ROLE_USER'|role_label }}   {# → "Usuário" #}
```

---

## Criando novo CRUD Admin

```bash
# 1. Crie a entidade
php bin/console make:entity Produto

# 2. Gere e rode a migration
php bin/console make:migration
php bin/console doctrine:migrations:migrate

# 3. Gere o CRUD
php bin/console make:custom-crud Produto
```


### Usuário admin via CLI

```bash
php bin/console app:create-user
# Interativo: pergunta nome, email e senha
```

### Impersonar usuário (switch_user)

Habilitado por padrão. Adicione `?_switch_user=email@usuario.com` à URL.
Para sair: `?_switch_user=_exit`.

---

## E-mail e redefinição de senha

### Fluxo completo

1. Usuário acessa `/esqueci-a-senha`
2. Digita o e-mail → sistema gera token de 64 chars e salva no `User`
3. E-mail enviado com link `/redefinir-senha/{token}` (válido por 1 hora)
4. Usuário clica → página de nova senha com indicador de força
5. Senha salva → token invalidado → redirect para login com flash de sucesso

---

## Referências rápidas

| O que | Onde |
|---|---|
| Design system (CSS vars + componentes) | `assets/styles/app.css` |
| Bootstrap JS (dark mode, Stimulus) | `assets/app.js` |
| Layout admin | `templates/admin/base.html.twig` |
| Tema de formulários | `templates/admin/form_theme.html.twig` |
| Catálogo de ícones | `https://127.0.0.1:8000/admin/icones` |
| Demo de formulários | `https://127.0.0.1:8000/admin/exemplo/form` |
| Demo de listagem | `https://127.0.0.1:8000/admin/exemplo` |

---

Desenvolvido por **WAB Ninjas** 🥷